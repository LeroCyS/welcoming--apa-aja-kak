<?php

namespace App\Http\Controllers\Counselor;

use App\Actions\SendCounselorMessageAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\CounselorConversationResource;
use App\Models\Article;
use App\Models\AuditLog;
use App\Models\Conversation;
use App\Models\CounselingSlot;
use App\Models\IdentityRequest;
use App\Models\ReplyTemplate;
use App\Models\School;
use App\Models\User;
use App\Services\ChatPresenter;
use App\Services\InboxService;
use App\Services\PriorityCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConversationController extends Controller
{
    public function __construct(protected ChatPresenter $presenter) {}

    public function show(Request $request, Conversation $conversation, InboxService $inbox, PriorityCalculator $priority): View
    {
        $this->authorize('counsel', $conversation);
        $user = $request->user();
        $this->presenter->markRead($conversation, $user);
        $conversation->load(['topics', 'revealedStudent.studentProfile', 'counselor.counselorProfile']);
        $messages = $conversation->messages()->with('voiceNote')->visibleTo($user)->get();
        $topicIds = $conversation->topics->pluck('id');
        $tz = School::current()->timezone;

        $templates = ReplyTemplate::with('topic')->where(fn ($q) => $q->whereNull('counselor_id')->orWhere('counselor_id', $user->id))->orderBy('title')->get();
        $articles = Article::published()->where('school_id', $user->school_id)->with(['category', 'topics'])->orderByDesc('published_at')->get();

        return view('counselor.conversation', [
            'c' => $conversation,
            'vm' => (new CounselorConversationResource($conversation->loadCount('unreviewedFlags')))->resolve($request),
            'list' => $inbox->present($inbox->list($user)->take(30), $request),
            'messages' => $this->presenter->render($conversation, $messages, $user),
            'lastId' => (int) $messages->max('id'),
            'version' => $this->presenter->stateVersion($conversation),
            'anonToMe' => $conversation->isAnonymousToCurrentCounselor(),
            'pendingIdentity' => $conversation->identityRequests()->where('status', 'pending')->where('counselor_id', $user->id)->exists(),
            'breakdown' => $priority->breakdown($conversation),
            'flags' => $conversation->flags()->with(['keyword', 'message', 'reviewer.counselorProfile'])->latest()->get(),
            'logs' => $conversation->statusLogs()->with('changer.counselorProfile')->limit(20)->get(),
            'notes' => $conversation->caseNotes()->get(),
            'transfers' => $conversation->transfers()->with(['fromCounselor.counselorProfile', 'toCounselor.counselorProfile'])->get(),
            'followUps' => $conversation->followUps()->latest('scheduled_at')->get(),
            'invitations' => $conversation->invitations()->with(['slots', 'session.slot'])->latest()->get(),
            'sessions' => $conversation->sessions()->with('slot')->latest()->get(),
            'summary' => $conversation->latestSummary,
            'nonSystemCount' => $messages->where('sender_type', '!=', 'system')->count(),
            'sharedMoods' => $messages->where('type', 'mood_share'),
            'templates' => $templates->map(fn ($t) => ['id' => $t->id, 'title' => $t->title, 'shortcut' => $t->shortcut, 'body' => $t->body, 'topic_id' => $t->topic_id, 'topic' => $t->topic?->name ?? 'Umum', 'mine' => $t->counselor_id === $user->id])->values(),
            'articles' => $articles->map(fn ($a) => ['id' => $a->id, 'title' => $a->title, 'category' => $a->category?->name, 'meta' => $a->category?->name.' · '.($a->type === 'infographic' ? 'infografis' : $a->read_minutes.' menit').' · dibaca '.$a->view_count.'×', 'suggested' => $a->topics->pluck('id')->intersect($topicIds)->isNotEmpty(), 'slug' => $a->category?->slug])
                ->sortByDesc('suggested')->values(),
            'slots' => CounselingSlot::where('counselor_id', $user->id)->where('status', 'available')->where('start_at', '>=', now()->addHours(2))->orderBy('start_at')->limit(40)->get()
                ->map(fn ($s) => ['id' => $s->id, 'label' => $s->start_at->timezone($tz)->translatedFormat('D, j M').' · '.$s->start_at->timezone($tz)->format('H.i').'-'.$s->end_at->timezone($tz)->format('H.i').' · '.$s->location]),
            'colleagues' => User::counselors()->inSchool($user->school_id)->active()->where('id', '!=', $user->id)->with('counselorProfile')->get(),
            'sapaan' => $conversation->displayNameFor($user),
            'tz' => $tz,
        ]);
    }

    public function messages(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('counsel', $conversation);

        return response()->json($this->payload($request, $conversation));
    }

    public function sendMessage(Request $request, Conversation $conversation, SendCounselorMessageAction $send): JsonResponse
    {
        $this->authorize('reply', $conversation);
        $data = $request->validate(['body' => ['required', 'string', 'max:4000']], ['body.required' => 'Balasannya masih kosong.']);
        $send->execute($conversation, $request->user(), 'text', trim($data['body']));

        return response()->json($this->payload($request, $conversation->fresh()));
    }

    public function sendArticle(Request $request, Conversation $conversation, SendCounselorMessageAction $send): JsonResponse|RedirectResponse
    {
        $this->authorize('reply', $conversation);
        $data = $request->validate([
            'article_id' => ['required', 'integer'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);
        $article = Article::published()->where('school_id', $conversation->school_id)->findOrFail($data['article_id']);
        $send->execute($conversation, $request->user(), 'article', $data['note'] ?? null, ['article_id' => $article->id], $request->user()->displayName().' mengirim bacaan untukmu');

        return $request->expectsJson()
            ? response()->json($this->payload($request, $conversation->fresh()))
            : back()->with('status', 'Artikel terkirim.');
    }

    public function requestIdentity(Request $request, Conversation $conversation, SendCounselorMessageAction $send): RedirectResponse
    {
        $this->authorize('reply', $conversation);
        $user = $request->user();
        if (! $conversation->isAnonymousToCurrentCounselor()) {
            return back()->withErrors(['general' => 'Identitas murid ini sudah terlihat olehmu.']);
        }
        if ($conversation->identityRequests()->where('status', 'pending')->where('counselor_id', $user->id)->exists()) {
            return back()->withErrors(['general' => 'Masih ada permintaan yang menunggu jawaban murid.']);
        }
        $data = $request->validate(['message' => ['nullable', 'string', 'max:255']]);

        $req = IdentityRequest::create([
            'conversation_id' => $conversation->id,
            'counselor_id' => $user->id,
            'message' => $data['message'] ?? null,
            'status' => 'pending',
        ]);
        $send->execute($conversation, $user, 'identity_request', $req->message, ['identity_request_id' => $req->id], 'Ada pertanyaan untukmu', 'identity_request');
        AuditLog::record('identity_request_sent', $conversation);

        return back()->with('status', 'Permintaan terkirim. Murid bebas memilih, tidak ada konsekuensi bila ia tetap anonim.');
    }

    public function payload(Request $request, Conversation $c): array
    {
        return $this->presenter->payload($request, $c);
    }

    /** Guru sedang mengetik: murid melihat animasi "sedang mengetik…" agar tahu gurunya hadir. */
    public function typing(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('reply', $conversation);
        $this->presenter->typing($conversation, 'counselor');

        return response()->json(['ok' => true]);
    }
}
