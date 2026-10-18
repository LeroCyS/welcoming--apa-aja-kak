<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StoryPost;
use App\Models\StoryReaction;
use App\Models\User;
use App\Services\ChatMedia;
use App\Services\KeywordScanner;
use App\Support\IdentityHasher;
use App\Support\Notify;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Cerita Teman: murid berbagi cerita tanpa nama untuk dibaca teman satu sekolah.
 * Semua cerita ditinjau Guru BK dulu, dan murid lain hanya bisa memberi reaksi dukungan (tanpa komentar).
 */
class StoryController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $hash = IdentityHasher::hash($user->id);
        $tab = $request->query('tab') === 'ceritaku' ? 'ceritaku' : 'semua';

        $query = $tab === 'ceritaku'
            ? StoryPost::byAuthor($user)->latest()
            : StoryPost::approved()->where('school_id', $user->school_id)->latest('moderated_at');

        $posts = $query->withCount([
            'reactions as peluk_count' => fn ($q) => $q->where('type', 'peluk'),
            'reactions as kuat_count' => fn ($q) => $q->where('type', 'kuat'),
            'reactions as sama_count' => fn ($q) => $q->where('type', 'sama'),
        ])->paginate(10)->withQueryString();

        $mine = StoryReaction::whereIn('story_post_id', $posts->pluck('id'))->where('reactor_hash', $hash)->get()
            ->groupBy('story_post_id')->map(fn ($r) => $r->pluck('type')->all());

        return view('student.stories.index', [
            'tab' => $tab,
            'posts' => $posts,
            'myReactions' => $mine,
            'myPending' => StoryPost::byAuthor($user)->where('status', 'pending')->count(),
            'hash' => $hash,
        ]);
    }

    public function create(Request $request): View
    {
        return view('student.stories.create', [
            'grade' => $request->user()->studentProfile?->grade_level,
            'limited' => $this->reachedDailyLimit($request->user()),
        ]);
    }

    public function store(Request $request, KeywordScanner $scanner, ChatMedia $media): RedirectResponse
    {
        $user = $request->user();
        if ($this->reachedDailyLimit($user)) {
            return back()->withInput()->withErrors(['body' => 'Kamu sudah membagikan '.StoryPost::MAX_PER_DAY.' cerita hari ini. Coba lagi besok, ya.']);
        }
        $grade = $user->studentProfile?->grade_level;
        $data = $request->validate([
            // Boleh sesingkat apa pun; batas atas hanya pengaman teknis, tidak ditampilkan ke murid.
            'body' => ['required', 'string', 'max:5000'],
            'song_url' => ['nullable', 'string', 'max:500'],
            'label' => ['required', Rule::in(['grade', 'anon'])],
            'consent' => ['accepted'],
        ], [
            'body.required' => 'Tulis ceritamu dulu, ya.',
            'body.max' => 'Ceritanya terlalu panjang. Coba bagi menjadi dua cerita, ya.',
            'consent.accepted' => 'Centang persetujuan supaya ceritamu bisa ditinjau dan ditampilkan.',
        ]);

        $song = $media->songFromInput($data['song_url'] ?? null);

        StoryPost::create([
            'school_id' => $user->school_id,
            'author_hash' => IdentityHasher::hash($user->id),
            'display_label' => $data['label'] === 'grade' && $grade ? "Murid kelas {$grade}" : 'Seorang teman',
            'body' => trim($data['body']),
            'song' => $song,
            'status' => 'pending',
        ]);

        User::counselors()->inSchool($user->school_id)->active()->get()
            ->each(fn (User $counselor) => Notify::user($counselor, 'story_pending', 'Cerita Teman baru menunggu tinjauan', route('counselor.selfhelp.index', ['tab' => 'cerita-teman'])));

        // Cerita tetap ditinjau, tapi bila isinya menandakan bahaya, murid langsung diarahkan ke bantuan.
        $critical = $scanner->scan($data['body'], $user->school_id)->max('severity') >= 3;

        return redirect()->route('student.stories.index', ['tab' => 'ceritaku'])
            ->with('status', 'Terima kasih sudah berbagi! Ceritamu akan ditinjau Guru BK dulu sebelum tampil tanpa nama.')
            ->with('story_critical', $critical);
    }

    /** Beri/batalkan reaksi dukungan. Murid tidak bisa memberi reaksi ke ceritanya sendiri. */
    public function react(Request $request, StoryPost $story): JsonResponse
    {
        $user = $request->user();
        abort_unless($story->status === 'approved' && (int) $story->school_id === (int) $user->school_id, 404);
        abort_if($story->isWrittenBy($user), 403, 'Ini ceritamu sendiri.');
        $data = $request->validate(['type' => ['required', Rule::in(array_keys(StoryPost::REACTIONS))]]);

        $attrs = ['story_post_id' => $story->id, 'reactor_hash' => IdentityHasher::hash($user->id), 'type' => $data['type']];
        $existing = StoryReaction::where($attrs)->first();
        if ($existing) {
            $existing->delete();
        } else {
            // Ketukan ganda bersamaan: permintaan kedua bertabrakan dengan indeks unik, hasilnya tetap "aktif".
            try {
                StoryReaction::create($attrs);
            } catch (UniqueConstraintViolationException) {
            }
        }

        return response()->json([
            'active' => ! $existing,
            'count' => $story->reactions()->where('type', $data['type'])->count(),
        ]);
    }

    public function destroy(Request $request, StoryPost $story): RedirectResponse
    {
        abort_unless($story->isWrittenBy($request->user()), 404);
        $story->delete();

        return redirect()->route('student.stories.index', ['tab' => 'ceritaku'])->with('status', 'Ceritamu sudah dihapus.');
    }

    protected function reachedDailyLimit(User $user): bool
    {
        return StoryPost::byAuthor($user)->where('created_at', '>=', now()->subDay())->count() >= StoryPost::MAX_PER_DAY;
    }
}
