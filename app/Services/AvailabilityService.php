<?php

namespace App\Services;

use App\Events\PresenceChanged;
use App\Models\CounselorServiceHour;
use App\Models\School;
use App\Models\User;
use App\Support\Realtime;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;

class AvailabilityService
{
    public function now(): CarbonImmutable
    {
        return CarbonImmutable::now(School::current()->timezone);
    }

    public function inServiceHours(User $counselor, ?CarbonImmutable $at = null): bool
    {
        $at = ($at ?? $this->now())->setTimezone(School::current()->timezone);
        $time = $at->format('H:i:s');

        return $counselor->serviceHours
            ->where('day_of_week', $at->dayOfWeek)
            ->contains(fn (CounselorServiceHour $h) => $time >= $h->start_time && $time < $h->end_time);
    }

    /**
     * Satu-satunya sumber status online: guru menyalakan "Online" dan heartbeat-nya masih baru.
     * Jam layanan hanya informasi, guru yang sengaja online di luar jam layanan tetap terlihat online oleh murid.
     */
    public function isOnline(User $counselor): bool
    {
        $profile = $counselor->counselorProfile;

        return (bool) $profile?->is_online
            && $profile->last_seen_at !== null
            && $profile->last_seen_at->gte(now()->subSeconds(config('welcoming.presence_timeout_seconds')));
    }

    /**
     * Kabarkan status online guru ke semua murid di sekolahnya, hanya bila berubah sejak kabar terakhir.
     * Dipanggil saat guru menekan tombol Online/Offline, saat heartbeat, dan tiap menit oleh scheduler
     * (untuk guru yang menutup tab tanpa mematikan Online).
     */
    public function announce(User $counselor): bool
    {
        $online = $this->isOnline($counselor);
        $key = 'presence:announced:'.$counselor->id;
        if (Cache::get($key) !== $online) {
            Cache::put($key, $online, now()->addDay());
            if ($counselor->school_id) {
                Realtime::send(new PresenceChanged('school.'.$counselor->school_id, ['counselor_id' => $counselor->id, 'online' => $online]));
            }
        }

        return $online;
    }

    public function isAvailable(User $counselor): bool
    {
        return $this->isOnline($counselor);
    }

    public function todayHoursLabel(User $counselor): ?string
    {
        $hours = $counselor->serviceHours->where('day_of_week', $this->now()->dayOfWeek)->sortBy('start_time');

        return $hours->isEmpty() ? null : $hours->map->label()->implode(', ');
    }

    /** @return array<int, array{day: string, hours: ?string, today: bool}> */
    public function weekSchedule(User $counselor): array
    {
        $today = $this->now()->dayOfWeek;
        $out = [];
        foreach ([1, 2, 3, 4, 5, 6, 0] as $d) {
            $h = $counselor->serviceHours->where('day_of_week', $d)->sortBy('start_time');
            $out[] = [
                'day' => CounselorServiceHour::DAY_NAMES[$d],
                'hours' => $h->isEmpty() ? null : $h->map->label()->implode(', '),
                'today' => $d === $today,
            ];
        }

        return $out;
    }

    /**
     * Jam layanan berikutnya untuk auto-reply, mis. "hari ini, 13.00-15.00", "besok (Selasa), 07.00-15.00",
     * atau "Senin, 07.00-15.00". Null bila guru tidak punya jam layanan.
     */
    public function nextServiceLabel(User $counselor, ?CarbonImmutable $at = null): ?string
    {
        $at = ($at ?? $this->now())->setTimezone(School::current()->timezone);
        for ($offset = 0; $offset <= 7; $offset++) {
            $day = $at->addDays($offset);
            $window = $counselor->serviceHours->where('day_of_week', $day->dayOfWeek)->sortBy('start_time')
                ->first(fn (CounselorServiceHour $h) => $offset > 0 || $h->end_time > $at->format('H:i:s'));
            if (! $window) {
                continue;
            }
            $start = $offset === 0 && $window->start_time < $at->format('H:i:s') ? $at->format('H:i') : substr($window->start_time, 0, 5);
            $hours = str_replace(':', '.', $start).'-'.str_replace(':', '.', substr($window->end_time, 0, 5));
            $dayName = CounselorServiceHour::DAY_NAMES[$day->dayOfWeek];

            return match ($offset) {
                0 => 'hari ini, '.$hours,
                1 => 'besok ('.$dayName.'), '.$hours,
                default => $dayName.', '.$hours,
            };
        }

        return null;
    }

    /** Status untuk ditampilkan ke murid. */
    public function statusFor(User $counselor): array
    {
        $available = $this->isOnline($counselor);

        return [
            'online' => $available,
            'label' => $available ? 'Online' : 'Sedang offline',
            'hours' => $this->todayHoursLabel($counselor),
        ];
    }

    /** Ringkasan jam layanan mingguan, mis. "Senin-Jumat, 07.00-15.00". */
    public function summary(User $counselor): string
    {
        $groups = [];
        foreach ([1, 2, 3, 4, 5, 6, 0] as $d) {
            $h = $counselor->serviceHours->where('day_of_week', $d)->sortBy('start_time')->map(fn ($x) => str_replace(':', '.', $x->label()))->implode(', ');
            if ($h === '') {
                continue;
            }
            $last = count($groups) - 1;
            if ($last >= 0 && $groups[$last]['hours'] === $h && $groups[$last]['to_idx'] === array_search($d, [1, 2, 3, 4, 5, 6, 0]) - 1) {
                $groups[$last]['to'] = $d;
                $groups[$last]['to_idx']++;
            } else {
                $groups[] = ['from' => $d, 'to' => $d, 'to_idx' => array_search($d, [1, 2, 3, 4, 5, 6, 0]), 'hours' => $h];
            }
        }
        if (! $groups) {
            return 'Belum ada jam layanan';
        }

        return collect($groups)->map(function ($g) {
            $days = CounselorServiceHour::DAY_NAMES[$g['from']].($g['from'] !== $g['to'] ? '-'.CounselorServiceHour::DAY_NAMES[$g['to']] : '');

            return $days.', '.$g['hours'];
        })->implode(' · ');
    }
}
