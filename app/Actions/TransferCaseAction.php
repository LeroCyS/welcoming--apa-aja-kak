<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\CaseTransfer;
use App\Models\Conversation;
use App\Models\User;
use App\Services\ConversationMessenger;
use App\Support\Notify;
use App\Support\Realtime;
use Illuminate\Support\Facades\DB;

class TransferCaseAction
{
    public function __construct(protected ConversationMessenger $messenger) {}

    public function execute(Conversation $c, User $from, User $to, ?string $note): void
    {
        DB::transaction(function () use ($c, $from, $to, $note) {
            CaseTransfer::create([
                'conversation_id' => $c->id,
                'from_counselor_id' => $from->id,
                'to_counselor_id' => $to->id,
                'handover_note' => $note,
            ]);
            $c->forceFill(['counselor_id' => $to->id, 'board_column_id' => null])->save();
            // Permintaan identitas yang masih pending dari guru lama tidak berlaku lagi.
            $c->identityRequests()->where('status', 'pending')->update(['status' => 'declined', 'responded_at' => now()]);

            $nickname = $to->counselorProfile?->nickname ?? $to->name;
            $text = "Ceritamu sekarang ditemani oleh {$nickname}.";
            if ($c->is_anonymous) {
                $text .= ' Kalau mau, kamu bisa membuka identitasmu lagi.';
            }
            $this->messenger->system($c, $text);
            AuditLog::record('case_transferred', $c, ['from' => $from->id, 'to' => $to->id], $from->id);
        });

        $c->refresh();
        $this->messenger->refreshPriority($c);
        Notify::user($to, 'case_transfer', 'Serah terima kasus: '.$c->displayNameFor($to).' dari '.$from->displayName(), route('counselor.conversations.show', $c));
        Realtime::counselor($from->id, 'InboxUpdated', ['conversation' => $c->uuid]);
        Realtime::counselor($to->id, 'NewConversationAssigned', ['conversation' => $c->uuid]);
    }
}
