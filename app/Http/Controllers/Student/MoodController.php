<?php

namespace App\Http\Controllers\Student;

use App\Actions\ShareMoodAction;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\MoodEntry;
use App\Services\ChatMedia;
use App\Services\MoodService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MoodController extends Controller
{
    public function __construct(protected MoodService $mood, protected ChatMedia $media) {}

    /** Check-in cepat dari Beranda (tidak dikunci PIN karena tidak menampilkan isi jurnal). */
    public function checkin(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'mood' => ['required', Rule::in(MoodEntry::MOODS)],
            'note' => ['nullable', 'string', 'max:500'],
            'song_url' => ['nullable', 'string', 'max:500'],
        ]);
        $user = $request->user();
        $song = $request->has('song_url') ? $this->media->songFromInput($data['song_url'] ?? null) : null;
        $entry = $this->mood->checkin($user, $data['mood'], $data['note'] ?? null, $request->has('note'), $song, $request->has('song_url'));
        $prompt = $this->mood->consumePrompt($user);

        if ($request->expectsJson()) {
            return response()->json([
                'mood' => $entry->mood,
                'label' => $entry->label(),
                'reply' => MoodService::REPLIES[$entry->mood],
                'note' => $entry->note,
                'song' => $entry->song,
                'prompt' => (bool) $prompt,
                'prompt_days' => $prompt?->map(fn ($e) => ['mood' => $e->mood, 'day' => $e->entry_date->translatedFormat('D')])->values(),
            ]);
        }

        return back()->with('status', 'Terima kasih sudah check-in hari ini.');
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $today = $this->mood->today();
        try {
            $month = $request->filled('bulan') ? CarbonImmutable::createFromFormat('Y-m', $request->query('bulan'), $today->timezone)->startOfMonth() : $today->startOfMonth();
        } catch (\Throwable) {
            $month = $today->startOfMonth();
        }
        if ($month->greaterThan($today)) {
            $month = $today->startOfMonth();
        }
        $calendar = $this->mood->calendar($user, $month);

        $counts = collect(MoodEntry::MOODS)->mapWithKeys(fn ($m) => [$m => $calendar['entries']->where('mood', $m)->count()]);

        return view('student.mood', [
            'month' => $month,
            'today' => $today,
            'calendar' => $calendar['cells'],
            'counts' => $counts,
            'checkins' => $calendar['entries']->count(),
            'todayEntry' => $this->mood->todayEntry($user),
            'notes' => MoodEntry::where('student_id', $user->id)->where(fn ($q) => $q->whereNotNull('note')->orWhereNotNull('song'))->with('sharedConversation.counselor.counselorProfile')->orderByDesc('entry_date')->limit(30)->get(),
            'trend' => $this->mood->trend($user),
            'conversations' => Conversation::forStudent($user)->active()->with('counselor.counselorProfile')->orderByDesc('last_message_at')->get(),
            'tints' => MoodService::TINTS,
            'prev' => $month->subMonth()->format('Y-m'),
            'next' => $month->addMonth()->lessThanOrEqualTo($today) ? $month->addMonth()->format('Y-m') : null,
        ]);
    }

    public function upsert(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'mood' => ['required', Rule::in(MoodEntry::MOODS)],
            'note' => ['nullable', 'string', 'max:500'],
            'song_url' => ['nullable', 'string', 'max:500'],
        ], ['note.max' => 'Catatan maksimal 500 karakter.']);
        $song = $this->media->songFromInput($data['song_url'] ?? null);
        $this->mood->checkin($request->user(), $data['mood'], $data['note'] ?? null, true, $song, true);

        return redirect()->route('student.mood.index')->with('status', 'Catatan hari ini tersimpan. Hanya kamu yang bisa melihatnya.');
    }

    public function share(Request $request, MoodEntry $moodEntry, ShareMoodAction $share): RedirectResponse
    {
        $this->authorize('share', $moodEntry);
        $data = $request->validate(['target' => ['required', 'string']]);

        if ($data['target'] === 'new') {
            return redirect()->route('student.conversations.create', ['bagikan_mood' => $moodEntry->id]);
        }

        $conversation = Conversation::forStudent($request->user())->where('uuid', $data['target'])->firstOrFail();
        $share->execute($moodEntry, $conversation);

        return redirect()->route('student.conversations.show', $conversation)->with('status', 'Catatan mood dibagikan ke '.$conversation->counselor->displayName().'.');
    }
}
