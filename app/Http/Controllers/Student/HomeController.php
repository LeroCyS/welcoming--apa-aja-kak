<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Conversation;
use App\Models\MoodPromptLog;
use App\Models\School;
use App\Models\SessionInvitation;
use App\Models\User;
use App\Services\AvailabilityService;
use App\Services\MoodService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request, MoodService $mood, AvailabilityService $availability): View
    {
        $user = $request->user()->load('studentProfile');
        $today = $mood->todayEntry($user);

        $conversations = Conversation::forStudent($user)->with(['counselor.counselorProfile', 'counselor.serviceHours', 'latestMessage', 'topics'])
            ->withCount(['messages as unread_count' => fn ($q) => $q->where('sender_type', '!=', 'student')->whereNull('read_at')])
            ->orderByDesc('last_message_at')->limit(2)->get();

        $invitation = SessionInvitation::whereIn('conversation_id', Conversation::forStudent($user)->select('id'))
            ->where('status', 'pending')->where('expires_at', '>', now())->with(['counselor.counselorProfile', 'slots', 'conversation'])->latest()->first();

        return view('student.home', [
            'user' => $user,
            'today' => $today,
            'week' => $mood->week($user),
            'prompt' => $mood->consumePrompt($user),
            'showTour' => $user->studentProfile?->tour_completed_at === null,
            'conversations' => $conversations,
            'invitation' => $invitation,
            'articles' => $this->recommended($today?->mood),
            'counselors' => User::counselors()->inSchool($user->school_id)->active()->with('counselorProfile')->get(),
            'availability' => $availability,
            'replies' => MoodService::REPLIES,
            'tints' => MoodService::TINTS,
        ]);
    }

    /** "Bacaan untukmu": mood negatif → kategori dari mood_article_map; selain itu unggulan + terbaru. */
    public static function recommended(?string $mood): Collection
    {
        $map = config('welcoming.mood_article_map');
        $query = Article::published()->where('school_id', School::current()->id)->with('category');
        if ($mood && isset($map[$mood])) {
            $ids = ArticleCategory::whereIn('slug', $map[$mood])->pluck('id');
            $list = (clone $query)->whereIn('article_category_id', $ids)->inRandomOrder()->limit(3)->get();
            if ($list->count() >= 3) {
                return $list;
            }
        }

        return $query->orderByDesc('is_featured')->orderByDesc('published_at')->limit(3)->get();
    }

    public function respondPrompt(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate(['response' => ['required', Rule::in(['accepted', 'dismissed'])]]);
        MoodPromptLog::where('student_id', $request->user()->id)->whereNull('response')->latest('shown_at')->first()?->update(['response' => $data['response']]);

        if ($data['response'] === 'accepted') {
            return redirect()->route('student.conversations.create');
        }

        return $request->expectsJson() ? response()->json(['ok' => true]) : back();
    }
}
