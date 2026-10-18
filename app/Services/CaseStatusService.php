<?php

namespace App\Services;

use App\Models\CaseStatusLog;
use App\Models\Conversation;
use App\Models\FollowUp;
use App\Support\Realtime;

class CaseStatusService
{
    public function __construct(protected ConversationMessenger $messenger) {}

    public function change(Conversation $c, string $to, ?int $by, ?string $note = null): void
    {
        $from = $c->status;
        if ($from === $to) {
            return;
        }

        $c->status = $to;
        if ($to === 'resolved') {
            $c->resolved_at = now();
            // Kasus selesai keluar dari kolom kanban pribadi, kembali ke kolom "Selesai".
            $c->board_column_id = null;
        } elseif ($from === 'resolved') {
            $c->resolved_at = null;
        }
        $c->save();

        CaseStatusLog::create([
            'conversation_id' => $c->id, 'changed_by' => $by, 'from_status' => $from, 'to_status' => $to,
            'note' => $note, 'created_at' => now(),
        ]);

        if ($to === 'resolved') {
            $this->cancelPendingFollowUps($c);
            FollowUp::create(['conversation_id' => $c->id, 'scheduled_at' => now()->addDays(config('welcoming.follow_up_days'))]);
            $this->messenger->system($c, 'Ceritamu ditandai selesai. Kalau butuh lagi, kamu bisa kirim pesan kapan saja ya.');
        } elseif ($from === 'resolved') {
            $this->cancelPendingFollowUps($c);
        }

        $this->messenger->refreshPriority($c);
        Realtime::conversation($c->uuid, 'ConversationUpdated', ['status' => $c->studentStatusLabel()]);
        Realtime::counselor($c->counselor_id, 'InboxUpdated', ['conversation' => $c->uuid]);
    }

    public function cancelPendingFollowUps(Conversation $c): void
    {
        $c->followUps()->whereNull('sent_at')->whereNull('cancelled_at')->update(['cancelled_at' => now()]);
    }
}
