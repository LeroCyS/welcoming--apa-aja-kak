<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CounselingSession;
use App\Models\CounselingSlot;
use App\Models\School;
use App\Services\ConversationMessenger;
use App\Support\Notify;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public const MAX_RESCHEDULE = 2;

    public function index(Request $request): View
    {
        $sessions = CounselingSession::with(['slot', 'counselor.counselorProfile', 'conversation'])
            ->where('student_id', $request->user()->id)->get()
            ->sortBy(fn ($s) => $s->slot->start_at);
        $upcoming = $sessions->filter(fn ($s) => $s->status === 'scheduled' && $s->slot->end_at->isFuture())->values();
        $history = $sessions->reject(fn ($s) => $s->status === 'scheduled' && $s->slot->end_at->isFuture())->sortByDesc(fn ($s) => $s->slot->start_at)->values();

        return view('student.schedule.index', [
            'tab' => $request->query('tab') === 'riwayat' ? 'riwayat' : 'mendatang',
            'upcoming' => $upcoming,
            'history' => $history,
            'tz' => School::current()->timezone,
            'max' => self::MAX_RESCHEDULE,
        ]);
    }

    /** Slot available lain milik guru yang sama (≥ 2 jam dari sekarang). */
    public function slots(Request $request, CounselingSession $session): JsonResponse
    {
        $this->authorize('reschedule', $session);
        $tz = School::current()->timezone;

        return response()->json(CounselingSlot::where('counselor_id', $session->counselor_id)->where('status', 'available')
            ->where('start_at', '>=', now()->addHours(2))->orderBy('start_at')->limit(30)->get()
            ->map(fn ($s) => ['id' => $s->id, 'label' => $s->start_at->timezone($tz)->translatedFormat('l, j M').' · '.$s->start_at->timezone($tz)->format('H.i').'-'.$s->end_at->timezone($tz)->format('H.i')]));
    }

    public function reschedule(Request $request, CounselingSession $session, ConversationMessenger $messenger): RedirectResponse
    {
        $this->authorize('reschedule', $session);
        $data = $request->validate(['slot_id' => ['required', 'integer']], ['slot_id.required' => 'Pilih jadwal baru dulu, ya.']);

        DB::transaction(function () use ($session, $data) {
            $s = CounselingSession::whereKey($session->id)->lockForUpdate()->first();
            $old = CounselingSlot::whereKey($s->counseling_slot_id)->lockForUpdate()->first();
            if ($s->status !== 'scheduled') {
                throw ValidationException::withMessages(['slot_id' => 'Sesi ini sudah tidak aktif.']);
            }
            if ($s->reschedule_count >= self::MAX_RESCHEDULE) {
                throw ValidationException::withMessages(['slot_id' => 'Jadwal sudah diubah 2 kali. Hubungi Guru BK lewat chat untuk mengatur ulang.']);
            }
            if ($old->start_at->lt(now()->addHours(2))) {
                throw ValidationException::withMessages(['slot_id' => 'Jadwal hanya bisa diubah paling lambat 2 jam sebelum sesi dimulai.']);
            }
            $new = CounselingSlot::whereKey($data['slot_id'])->lockForUpdate()->first();
            if (! $new || $new->counselor_id !== $s->counselor_id || $new->status !== 'available' || $new->start_at->lt(now()->addHours(2))) {
                throw ValidationException::withMessages(['slot_id' => 'Jadwal itu sudah tidak tersedia. Pilih waktu lain, ya.']);
            }
            $old->update(['status' => 'available']);
            $new->update(['status' => 'booked']);
            $s->update(['counseling_slot_id' => $new->id, 'reschedule_count' => $s->reschedule_count + 1]);
        });

        $session->refresh()->load('slot', 'conversation');
        $messenger->system($session->conversation, 'Jadwal ngobrol langsung diubah ke '.$session->slot->label().'.');
        Notify::user($session->counselor, 'session_rescheduled', 'Jadwal sesi diubah murid: '.$session->slot->label(), route('counselor.schedule'));

        return redirect()->route('student.schedule.index')->with('status', 'Jadwal berhasil diubah.');
    }
}
