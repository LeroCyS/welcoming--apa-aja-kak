<?php

namespace App\Http\Controllers\Student;

use App\Actions\ShareMoodAction;
use App\Actions\StartConversationAction;
use App\Actions\StartEmergencyConversationAction;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\MoodEntry;
use App\Models\Topic;
use App\Models\User;
use App\Services\AvailabilityService;
use App\Services\ChatPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ConversationController extends Controller
{
    public function __construct(protected AvailabilityService $availability, protected ChatPresenter $presenter) {}

    protected function list(User $user)
    {
        return Conversation::forStudent($user)
            ->with(['counselor.counselorProfile', 'counselor.serviceHours', 'latestMessage', 'topics'])
            ->withCount(['messages as unread_count' => fn ($q) => $q->where('sender_type', '!=', 'student')->whereNull('read_at')])
            ->orderByRaw("status = 'resolved'")->orderByDesc('last_message_at')->get();
    }

    public function index(Request $request): View
    {
        return view('student.conversations.index', [
            'conversations' => $this->list($request->user()),
            'availability' => $this->availability,
        ]);
    }

    /** Potongan HTML daftar cerita untuk refresh otomatis (unread, pesan terakhir, status guru). */
    public function feed(Request $request): JsonResponse
    {
        $conversations = $this->list($request->user());

        return response()->json(['html' => view('student.conversations._list', [
            'conversations' => $conversations,
            'availability' => $this->availability,
            'c' => $conversations->firstWhere('id', $request->integer('current')),
        ])->render()]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        $counselors = User::counselors()->inSchool($user->school_id)->active()->with(['counselorProfile', 'serviceHours'])->orderBy('id')->get();
        $preselect = $request->integer('guru') ?: null;
        $mood = $request->integer('bagikan_mood') ? MoodEntry::where('student_id', $user->id)->find($request->integer('bagikan_mood')) : null;

        return view('student.conversations.create', [
            'counselors' => $counselors->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->displayName(),
                'full' => $u->name,
                'initial' => $u->initial(),
                'palette' => [['#E4EADF', '#46593B'], ['#F3E6D0', '#6F5229'], ['#F6DDD7', '#8E3E2F']][$u->id % 3],
                'photo' => $u->counselorProfile?->photoUrl(),
                'online' => $this->availability->isAvailable($u),
                'note' => ($this->availability->isAvailable($u) ? 'Online' : 'Offline').' · '.($this->availability->todayHoursLabel($u) ? 'layanan hari ini '.str_replace(':', '.', $this->availability->todayHoursLabel($u)) : 'tidak ada layanan hari ini'),
            ])->values(),
            'topics' => Topic::active()->get(['id', 'slug', 'name']),
            'preselect' => $preselect && $counselors->contains('id', $preselect) ? $preselect : null,
            'mood' => $mood,
            'student' => $user->load('studentProfile'),
        ]);
    }

    public function store(Request $request, StartConversationAction $start, ShareMoodAction $share): RedirectResponse
    {
        $data = $request->validate([
            'counselor_id' => ['nullable', Rule::exists('users', 'id')->where('role', 'counselor')->where('is_active', true)->where('school_id', $request->user()->school_id)],
            'topics' => ['required', 'array', 'min:1'],
            'topics.*' => ['integer', Rule::exists('topics', 'id')->where('is_active', true)],
            'is_anonymous' => ['required', 'boolean'],
            'mood_entry_id' => ['nullable', 'integer'],
        ], [
            'topics.required' => 'Pilih minimal satu topik, ya.',
        ]);
        // Murid tidak memilih urgensi (sulit menilai sendiri); Guru BK yang menilainya. Sampai itu, "Belum dinilai".
        $data['urgency'] = null;
        $data['is_anonymous'] = (bool) $data['is_anonymous'];

        $user = $request->user();
        if (! $data['is_anonymous'] && ! $user->hasFilledIdentity()) {
            return back()->withInput()->withErrors(['is_anonymous' => 'Isi namamu dulu di Profil untuk bercerita dengan nama. Atau kirim sebagai anonim.']);
        }
        $conversation = $start->execute($user, $data);

        if (! empty($data['mood_entry_id']) && ($entry = MoodEntry::where('student_id', $user->id)->find($data['mood_entry_id']))) {
            $share->execute($entry, $conversation);
        }

        return redirect()->route('student.conversations.show', $conversation)->with('status', 'Ruang chat sudah siap. Ketik apa saja untuk mulai bercerita, pelan-pelan saja.');
    }

    public function show(Request $request, Conversation $conversation): View
    {
        $this->authorize('own', $conversation);
        $user = $request->user();
        $this->presenter->markRead($conversation, $user);
        $conversation->load(['counselor.counselorProfile', 'counselor.serviceHours', 'topics']);
        $messages = $conversation->messages()->with('voiceNote')->visibleTo($user)->get();

        return view('student.conversations.show', [
            'c' => $conversation,
            'conversations' => $this->list($user),
            'messages' => $this->presenter->render($conversation, $messages, $user),
            'lastId' => (int) $messages->max('id'),
            'version' => $this->presenter->stateVersion($conversation),
            'status' => $this->availability->statusFor($conversation->counselor),
            'hours' => $this->availability->summary($conversation->counselor),
            'availability' => $this->availability,
            'student' => $user->load('studentProfile'),
            'hasStarted' => $messages->contains('sender_type', 'student'),
            'readOnly' => $user->cannot('reply', $conversation),
            'pendingRequest' => $conversation->identityRequests()->where('status', 'pending')->latest()->first(),
        ]);
    }

    public function emergency(Request $request, StartEmergencyConversationAction $emergency): RedirectResponse
    {
        $conversation = $emergency->execute($request->user());

        return redirect()->route('student.conversations.show', $conversation)
            ->with('status', 'Guru BK sudah diberi tahu. Kalau kamu dalam bahaya sekarang, hubungi kontak darurat di bawah.');
    }
}
