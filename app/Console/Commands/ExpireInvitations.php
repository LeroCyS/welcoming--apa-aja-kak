<?php

namespace App\Console\Commands;

use App\Models\SessionInvitation;
use App\Support\Realtime;
use Illuminate\Console\Command;

class ExpireInvitations extends Command
{
    protected $signature = 'welcoming:expire-invitations';

    protected $description = 'Ubah undangan sesi pending yang lewat expires_at menjadi expired';

    public function handle(): int
    {
        $items = SessionInvitation::with('conversation')->where('status', 'pending')->where('expires_at', '<', now())->get();
        foreach ($items as $inv) {
            $inv->update(['status' => 'expired']);
            Realtime::conversation($inv->conversation->uuid, 'ConversationUpdated', ['invitation' => $inv->id]);
        }
        $this->info($items->count().' undangan kedaluwarsa.');

        return self::SUCCESS;
    }
}
