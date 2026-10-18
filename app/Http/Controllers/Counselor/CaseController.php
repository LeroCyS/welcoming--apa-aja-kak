<?php

namespace App\Http\Controllers\Counselor;

use App\Actions\TransferCaseAction;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\CaseBoardColumn;
use App\Models\Conversation;
use App\Models\ConversationFlag;
use App\Models\Topic;
use App\Models\User;
use App\Services\CaseStatusService;
use App\Services\InboxService;
use App\Services\PriorityCalculator;
use App\Support\Realtime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CaseController extends Controller
{
    public function index(Request $request, InboxService $inbox): View
    {
        $filters = $request->only(['q', 'topic', 'urgency', 'status', 'identity']);
        $list = $inbox->list($request->user(), $filters)->load('followUps');
        $items = collect($inbox->present($list, $request))->map(function ($vm) use ($list) {
            $c = $list->firstWhere('uuid', $vm['uuid']);
            $next = $c->followUps->whereNull('sent_at')->whereNull('cancelled_at')->sortBy('scheduled_at')->first();
            $vm['next_follow_up'] = $next?->scheduled_at;
            $vm['board_column_id'] = $c->board_column_id;

            return $vm;
        });

        return view('counselor.cases', [
            'view' => $request->query('tampilan') === 'tabel' ? 'tabel' : 'kanban',
            'items' => $items,
            'filters' => $filters,
            'topics' => Topic::active()->get(),
            'boardColumns' => CaseBoardColumn::where('counselor_id', $request->user()->id)->orderBy('position')->get(),
            'colleagues' => User::counselors()->inSchool($request->user()->school_id)->active()->where('id', '!=', $request->user()->id)->with('counselorProfile')->get(),
        ]);
    }

    public function updateStatus(Request $request, Conversation $conversation, CaseStatusService $service): JsonResponse|RedirectResponse
    {
        $this->authorize('counsel', $conversation);
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Conversation::STATUS_LABELS))],
            'note' => ['nullable', 'string', 'max:255'],
        ]);
        $service->change($conversation, $data['status'], $request->user()->id, $data['note'] ?? null);

        $warning = $data['status'] === 'referred_parent' && $conversation->isAnonymousToCurrentCounselor()
            ? 'Untuk merujuk ke orang tua, minta murid membuka identitas terlebih dahulu.' : null;
        $msg = 'Status diubah ke “'.$conversation->statusLabel().'”.'.($data['status'] === 'resolved' ? ' Follow-up otomatis dijadwalkan '.config('welcoming.follow_up_days').' hari lagi.' : '');

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'status' => $conversation->status, 'label' => $conversation->statusLabel(), 'warning' => $warning, 'message' => $msg]);
        }

        return back()->with('status', $warning ? $msg.' '.$warning : $msg);
    }

    public function transfer(Request $request, Conversation $conversation, TransferCaseAction $transfer): RedirectResponse
    {
        $this->authorize('counsel', $conversation);
        $user = $request->user();
        $data = $request->validate([
            'to_counselor_id' => ['required', Rule::exists('users', 'id')->where('role', 'counselor')->where('is_active', true)->where('school_id', $conversation->school_id), Rule::notIn([$user->id])],
            'handover_note' => ['nullable', 'string', 'max:2000'],
        ], ['to_counselor_id.required' => 'Pilih guru tujuan.'], ['to_counselor_id' => 'guru tujuan']);

        $to = User::findOrFail($data['to_counselor_id']);
        $transfer->execute($conversation, $user, $to, $data['handover_note'] ?? null);

        return redirect()->route('counselor.inbox')->with('status', 'Kasus diserahkan ke '.$to->displayName().'. Catatan privatmu tidak ikut berpindah.');
    }

    /**
     * Guru menandai cerita sebagai iseng/palsu (bisa dibatalkan). Admin sekolah lalu bisa mengeluarkan
     * murid tersebut dari sekolah tanpa pernah melihat isi cerita atau identitasnya.
     */
    public function togglePrank(Request $request, Conversation $conversation): RedirectResponse
    {
        $this->authorize('counsel', $conversation);
        $reported = $conversation->prank_reported_at === null;
        $conversation->forceFill([
            'prank_reported_at' => $reported ? now() : null,
            'prank_reported_by' => $reported ? $request->user()->id : null,
        ])->save();
        AuditLog::record($reported ? 'prank_reported' : 'prank_report_cancelled', $conversation);

        return back()->with('status', $reported
            ? 'Cerita ditandai iseng dan dilaporkan ke admin sekolah. Kalau ternyata keliru, kamu bisa membatalkannya.'
            : 'Tanda iseng dibatalkan.');
    }

    /** Guru yang menangani menilai urgensi kasus. Murid tidak pernah melihat nilai ini. */
    public function updateUrgency(Request $request, Conversation $conversation, PriorityCalculator $priority): JsonResponse|RedirectResponse
    {
        $this->authorize('counsel', $conversation);
        $data = $request->validate([
            'urgency' => ['required', Rule::in(array_keys(Conversation::URGENCY_LABELS))],
            'note' => ['nullable', 'string', 'max:255'],
        ], ['urgency.required' => 'Pilih tingkat urgensi.', 'urgency.in' => 'Pilih tingkat urgensi.']);

        $from = $conversation->urgency;
        $conversation->forceFill(['urgency' => $data['urgency'], 'urgency_assessed_at' => now()])->save();
        $priority->refresh($conversation);
        AuditLog::record('urgency_assessed', $conversation, array_filter(['from' => $from, 'to' => $data['urgency'], 'note' => $data['note'] ?? null]));
        Realtime::counselor($request->user()->id, 'InboxUpdated', ['conversation' => $conversation->uuid]);

        $msg = 'Urgensi dinilai “'.$conversation->urgencyLabel().'”.';

        return $request->expectsJson()
            ? response()->json(['ok' => true, 'urgency' => $conversation->urgency, 'label' => $conversation->urgencyLabel(), 'priority' => $conversation->priority_score, 'message' => $msg])
            : back()->with('status', $msg);
    }

    public function reviewFlag(Request $request, ConversationFlag $flag, PriorityCalculator $priority): RedirectResponse|JsonResponse
    {
        $this->authorize('counsel', $flag->conversation);
        $flag->update(['reviewed_at' => now(), 'reviewed_by' => $request->user()->id]);
        $priority->refresh($flag->conversation);
        Realtime::counselor($request->user()->id, 'InboxUpdated', ['conversation' => $flag->conversation->uuid]);

        return $request->expectsJson() ? response()->json(['ok' => true]) : back()->with('status', 'Flag ditandai sudah ditinjau.');
    }
}
