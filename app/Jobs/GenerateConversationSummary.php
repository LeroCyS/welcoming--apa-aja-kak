<?php

namespace App\Jobs;

use App\Contracts\Summarizer;
use App\Models\ConversationSummary;
use App\Services\AiPayloadSanitizer;
use App\Support\Notify;
use App\Support\Realtime;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class GenerateConversationSummary implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 150;

    public function __construct(public int $summaryId) {}

    public function handle(Summarizer $summarizer, AiPayloadSanitizer $sanitizer): void
    {
        $summary = ConversationSummary::with('conversation.topics')->find($this->summaryId);
        if (! $summary || $summary->status !== 'pending') {
            return;
        }
        $c = $summary->conversation;
        $messages = $c->messages()->with('voiceNote')->get();

        try {
            $payload = $sanitizer->build($c, $messages);
            $content = $summarizer->summarize($payload['messages'], $payload['context']);
            $summary->update([
                'status' => 'done',
                'content' => $content,
                'last_message_id' => $messages->last()?->id,
                'message_count' => $messages->count(),
                'driver' => $summarizer->name(),
            ]);
        } catch (Throwable $e) {
            Log::warning('Ringkasan AI gagal: '.$e->getMessage());
            $summary->update(['status' => 'failed', 'error' => 'Ringkasan belum bisa dibuat saat ini. Coba lagi nanti.', 'driver' => $summarizer->name()]);
        }

        Realtime::counselor($summary->requested_by, 'SummaryReady', ['summary' => $summary->id]);
        if ($summary->status === 'done') {
            Notify::user($summary->requester, 'summary_ready', 'Ringkasan AI selesai untuk '.$c->displayNameFor($summary->requester), route('counselor.conversations.show', $c).'?tab=ringkasan');
        }
    }
}
