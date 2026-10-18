<?php

namespace App\Http\Controllers\Counselor;

use App\Http\Controllers\Controller;
use App\Models\Topic;
use App\Services\InboxService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InboxController extends Controller
{
    public function __construct(protected InboxService $inbox) {}

    protected function filters(Request $request): array
    {
        return $request->only(['q', 'topic', 'urgency', 'status', 'identity']);
    }

    public function index(Request $request): View
    {
        $items = $this->inbox->present($this->inbox->list($request->user(), $this->filters($request)), $request);
        $all = $this->inbox->list($request->user());

        return view('counselor.inbox', [
            'items' => $items,
            'filters' => $this->filters($request),
            'topics' => Topic::active()->get(),
            'counts' => [
                'all' => $all->count(),
                'urgent' => $all->where('urgency', 'urgent')->count(),
                'important' => $all->where('urgency', 'important')->count(),
                'normal' => $all->where('urgency', 'normal')->count(),
                'unassessed' => $all->whereNull('urgency')->count(),
                'unread' => $all->sum('unread_count'),
            ],
        ]);
    }

    /** Polling inbox (15 dtk) / setelah event realtime. */
    public function feed(Request $request): JsonResponse
    {
        $items = $this->inbox->present($this->inbox->list($request->user(), $this->filters($request)), $request);
        $compact = $request->boolean('compact');

        return response()->json([
            'html' => view($compact ? 'counselor.partials.inbox-compact' : 'counselor.partials.inbox-list', ['items' => $items, 'current' => $request->query('current')])->render(),
            'count' => count($items),
        ]);
    }
}
