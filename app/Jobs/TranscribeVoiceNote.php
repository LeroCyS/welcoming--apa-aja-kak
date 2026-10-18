<?php

namespace App\Jobs;

use App\Contracts\Transcriber;
use App\Models\VoiceNote;
use App\Services\ConversationMessenger;
use App\Support\Realtime;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class TranscribeVoiceNote implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(public int $voiceNoteId) {}

    public function handle(Transcriber $transcriber, ConversationMessenger $messenger): void
    {
        $vn = VoiceNote::with('conversation', 'message')->find($this->voiceNoteId);
        if (! $vn || $vn->transcript_status !== 'pending') {
            return;
        }

        try {
            $text = $transcriber->transcribe(Storage::disk('local')->path($vn->file_path));
            $vn->update(['transcript' => $text, 'transcript_status' => $transcriber->isFake() ? 'skipped' : 'done']);
            if (! $transcriber->isFake() && filled($text)) {
                $messenger->scan($vn->conversation, $text, $vn->message);
            }
        } catch (Throwable $e) {
            Log::warning('Transkripsi gagal: '.$e->getMessage());
            $vn->update(['transcript_status' => 'failed']);
        }

        $messenger->refreshPriority($vn->conversation);
        Realtime::conversation($vn->conversation->uuid, 'ConversationUpdated', ['transcript' => $vn->id]);
        Realtime::counselor($vn->conversation->counselor_id, 'InboxUpdated', ['conversation' => $vn->conversation->uuid]);
    }
}
