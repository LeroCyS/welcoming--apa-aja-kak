<?php

namespace App\Http\Controllers\Counselor;

use App\Http\Controllers\Controller;
use App\Models\CounselingSession;
use App\Models\CounselingSlot;
use App\Models\School;
use App\Services\ConversationMessenger;
use App\Support\Notify;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $tz = School::current()->timezone;
        $user = $request->user();
        try {
            $start = $request->filled('minggu') ? CarbonImmutable::parse($request->query('minggu'), $tz)->startOfWeek() : CarbonImmutable::now($tz)->startOfWeek();
        } catch (\Throwable) {
            $start = CarbonImmutable::now($tz)->startOfWeek();
        }
        $end = $start->addDays(7);

        $slots = CounselingSlot::with(['session.student', 'session.conversation'])->where('counselor_id', $user->id)
            ->whereBetween('start_at', [$start->utc(), $end->utc()])->orderBy('start_at')->get();
        $days = collect(range(0, 6))->map(fn ($i) => $start->addDays($i))
            ->filter(fn ($d) => $d->isWeekday() || $slots->contains(fn ($s) => $s->start_at->timezone($tz)->isSameDay($d)))->values();

        $sessions = CounselingSession::with(['slot', 'student.studentProfile', 'conversation'])->where('counselor_id', $user->id)
            ->whereHas('slot', fn ($q) => $q->where('end_at', '>=', now()->subDays(7)))->get()->sortBy(fn ($s) => $s->slot->start_at)->values();

        return view('counselor.schedule', [
            'start' => $start, 'days' => $days, 'slots' => $slots, 'tz' => $tz, 'sessions' => $sessions,
            'hours' => range(7, 16),
            'stats' => ['booked' => $slots->where('status', 'booked')->count(), 'available' => $slots->where('status', 'available')->count()],
        ]);
    }

    public function storeSlot(Request $request): RedirectResponse
    {
        $tz = School::current()->timezone;
        $data = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'start_time' => ['required', 'date_format:H:i'],
            'duration' => ['required', Rule::in([30, 45, 60])],
            'location' => ['required', 'string', 'max:100'],
            'repeat_weeks' => ['nullable', 'integer', 'min:1', 'max:8'],
        ], [], ['date' => 'tanggal', 'start_time' => 'jam mulai', 'duration' => 'durasi', 'location' => 'lokasi', 'repeat_weeks' => 'jumlah minggu']);

        $weeks = (int) ($data['repeat_weeks'] ?? 1);
        $first = CarbonImmutable::createFromFormat('Y-m-d H:i', $data['date'].' '.$data['start_time'], $tz);
        if ($first->isPast()) {
            throw ValidationException::withMessages(['date' => 'Waktu slot sudah lewat.']);
        }
        $user = $request->user();
        $created = 0;
        $skipped = [];
        DB::transaction(function () use ($weeks, $first, $data, $user, &$created, &$skipped, $tz) {
            for ($w = 0; $w < $weeks; $w++) {
                $s = $first->addWeeks($w);
                $e = $s->addMinutes((int) $data['duration']);
                $overlap = CounselingSlot::where('counselor_id', $user->id)
                    ->where('start_at', '<', $e->utc())->where('end_at', '>', $s->utc())->lockForUpdate()->exists();
                if ($overlap) {
                    $skipped[] = $s->timezone($tz)->translatedFormat('j M H.i');

                    continue;
                }
                CounselingSlot::create(['counselor_id' => $user->id, 'start_at' => $s->utc(), 'end_at' => $e->utc(), 'location' => $data['location'], 'status' => 'available']);
                $created++;
            }
        });
        if ($created === 0) {
            throw ValidationException::withMessages(['start_time' => 'Slot bertabrakan dengan slot lain milikmu.']);
        }
        $msg = "{$created} slot dibuat.".($skipped ? ' Dilewati karena bertabrakan: '.implode(', ', $skipped).'.' : '');

        return redirect()->route('counselor.schedule', ['minggu' => $first->toDateString()])->with('status', $msg);
    }

    public function toggleBlock(Request $request, CounselingSlot $slot): RedirectResponse
    {
        abort_unless($slot->counselor_id === $request->user()->id, 403);
        abort_if($slot->status === 'booked', 422, 'Slot yang sudah terisi tidak bisa diblokir.');
        $slot->update(['status' => $slot->status === 'blocked' ? 'available' : 'blocked']);

        return back()->with('status', $slot->status === 'blocked' ? 'Slot diblokir.' : 'Slot dibuka kembali.');
    }

    public function destroySlot(Request $request, CounselingSlot $slot): RedirectResponse
    {
        abort_unless($slot->counselor_id === $request->user()->id, 403);
        if ($slot->status === 'booked' || $slot->invitations()->where('status', 'pending')->exists()) {
            return back()->withErrors(['general' => 'Slot ini sedang dipakai (terisi atau ditawarkan di undangan aktif). Blokir saja jika perlu.']);
        }
        $slot->delete();

        return back()->with('status', 'Slot dihapus.');
    }

    public function updateSession(Request $request, CounselingSession $session, ConversationMessenger $messenger): RedirectResponse
    {
        $this->authorize('manage', $session);
        $data = $request->validate(['status' => ['required', Rule::in(['completed', 'no_show', 'cancelled'])]]);
        abort_unless($session->status === 'scheduled', 422, 'Sesi ini sudah diperbarui.');

        $session->update(['status' => $data['status']]);
        if ($data['status'] === 'cancelled') {
            $session->slot->update(['status' => 'available']);
            $messenger->system($session->conversation, 'Jadwal ngobrol langsung pada '.$session->slot->label().' dibatalkan. Kamu bisa menunggu undangan baru atau lanjut cerita di sini.');
            Notify::user($session->student, 'session_cancelled', 'Ada perubahan jadwal untukmu', route('student.schedule.index'));
        }

        return back()->with('status', 'Status sesi diperbarui: '.$session->statusLabel().'.');
    }
}
