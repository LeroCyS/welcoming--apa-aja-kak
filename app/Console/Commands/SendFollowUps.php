<?php

namespace App\Console\Commands;

use App\Models\FollowUp;
use App\Services\ConversationMessenger;
use App\Support\Notify;
use Illuminate\Console\Command;

class SendFollowUps extends Command
{
    protected $signature = 'welcoming:send-follow-ups {--now : Kirim semua follow-up yang belum terkirim tanpa menunggu tanggal jatuh tempo (untuk demo)}';

    protected $description = 'Kirim follow-up otomatis yang sudah jatuh tempo ke murid';

    public function handle(ConversationMessenger $messenger): int
    {
        $query = FollowUp::with('conversation')->whereNull('sent_at')->whereNull('cancelled_at');
        if (! $this->option('now')) {
            $query->where('scheduled_at', '<=', now());
        }

        $sent = 0;
        foreach ($query->get() as $fu) {
            $c = $fu->conversation;
            if (! $c || $c->status !== 'resolved') {
                $fu->update(['cancelled_at' => now()]);

                continue;
            }
            $messenger->system($c, 'Hai, gimana kabarmu sekarang?', 'follow_up', ['follow_up_id' => $fu->id]);
            $fu->update(['sent_at' => now()]);
            Notify::student($c, 'follow_up', 'Ada pesan baru untukmu');
            $sent++;
        }
        $this->info("{$sent} follow-up terkirim.");

        return self::SUCCESS;
    }
}
