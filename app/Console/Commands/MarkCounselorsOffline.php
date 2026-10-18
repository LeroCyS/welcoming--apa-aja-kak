<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\AvailabilityService;
use Illuminate\Console\Command;

/**
 * Guru yang menutup tab tanpa menekan Offline dianggap offline setelah heartbeat-nya basi
 * (welcoming.presence_timeout_seconds). Command ini mengabarkan perubahan itu ke murid secara realtime.
 * Tombol Online milik guru tidak diubah, jadi begitu guru membuka Welcoming lagi ia otomatis online.
 */
class MarkCounselorsOffline extends Command
{
    protected $signature = 'welcoming:mark-offline';

    protected $description = 'Kabarkan ke murid guru yang offline karena heartbeat-nya sudah basi';

    public function handle(AvailabilityService $availability): int
    {
        $changed = 0;
        User::counselors()->active()->with('counselorProfile')
            ->whereHas('counselorProfile', fn ($q) => $q->where('is_online', true))
            ->each(function (User $counselor) use ($availability, &$changed) {
                if (! $availability->announce($counselor)) {
                    $changed++;
                }
            });
        $this->info("{$changed} guru tercatat offline.");

        return self::SUCCESS;
    }
}
