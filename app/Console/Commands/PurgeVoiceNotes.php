<?php

namespace App\Console\Commands;

use App\Models\VoiceNote;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PurgeVoiceNotes extends Command
{
    protected $signature = 'welcoming:purge-voice-notes';

    protected $description = 'Hapus file audio voice note yang melewati masa retensi (transkrip tetap disimpan)';

    public function handle(): int
    {
        $days = (int) config('welcoming.voice_retention_days', 90);
        $n = 0;
        VoiceNote::whereNull('audio_deleted_at')->where('created_at', '<', now()->subDays($days))->chunkById(100, function ($items) use (&$n) {
            foreach ($items as $vn) {
                Storage::disk('local')->delete($vn->file_path);
                $vn->update(['audio_deleted_at' => now()]);
                $n++;
            }
        });
        $this->info("{$n} file audio dihapus (retensi {$days} hari).");

        return self::SUCCESS;
    }
}
