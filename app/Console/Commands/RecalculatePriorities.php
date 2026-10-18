<?php

namespace App\Console\Commands;

use App\Models\Conversation;
use App\Services\PriorityCalculator;
use App\Support\Realtime;
use Illuminate\Console\Command;

class RecalculatePriorities extends Command
{
    protected $signature = 'welcoming:recalculate-priorities {--all : Semua percakapan, bukan hanya yang menunggu balasan}';

    protected $description = 'Hitung ulang priority_score (komponen waktu tunggu)';

    public function handle(PriorityCalculator $calc): int
    {
        $q = Conversation::where('status', '!=', 'resolved');
        if (! $this->option('all')) {
            $q->whereNotNull('last_student_message_at')
                ->where(fn ($w) => $w->whereNull('last_counselor_message_at')->orWhereColumn('last_student_message_at', '>', 'last_counselor_message_at'));
        }
        $changed = [];
        $q->chunkById(200, function ($items) use ($calc, &$changed) {
            foreach ($items as $c) {
                $before = $c->priority_score;
                if ($calc->refresh($c) !== $before) {
                    $changed[$c->counselor_id] = true;
                }
            }
        });
        foreach (array_keys($changed) as $counselorId) {
            Realtime::counselor($counselorId, 'InboxUpdated');
        }
        $this->info('Prioritas diperbarui.');

        return self::SUCCESS;
    }
}
