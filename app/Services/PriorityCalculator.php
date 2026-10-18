<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\MoodEntry;
use App\Models\School;
use App\Models\User;
use Carbon\CarbonImmutable;

class PriorityCalculator
{
    public function __construct(protected StudentResolver $resolver) {}

    public function calculate(Conversation $c): int
    {
        return $this->breakdown($c)['score'];
    }

    /** Hitung dan simpan ke priority_score. */
    public function refresh(Conversation $c): int
    {
        $score = $this->calculate($c);
        if ($c->priority_score !== $score) {
            $c->forceFill(['priority_score' => $score])->saveQuietly();
        }

        return $score;
    }

    /** @return array{score: int, reasons: list<string>} */
    public function breakdown(Conversation $c): array
    {
        if ($c->status === 'resolved') {
            return ['score' => 0, 'reasons' => []];
        }

        $score = 0;
        $reasons = [];

        // Urgensi dinilai Guru BK. Kasus yang belum dinilai sedikit dinaikkan agar cepat ditriase.
        $score += ['normal' => 0, 'important' => 20, 'urgent' => 50][$c->urgency] ?? 10;
        if ($c->urgency === null) {
            $reasons[] = Conversation::UNASSESSED_LABEL;
        } elseif ($c->urgency !== 'normal') {
            $reasons[] = $c->urgencyLabel();
        }

        $maxSeverity = (int) $c->unreviewedFlags()->max('severity');
        if ($maxSeverity > 0) {
            $score += [1 => 15, 2 => 30, 3 => 60][$maxSeverity] ?? 0;
            $reasons[] = 'Kata sensitif';
        }

        $streak = $this->negativeMoodStreak($c);
        if ($streak >= 5) {
            $score += 20;
            $reasons[] = 'Tren suasana hati menurun';
        } elseif ($streak >= 3) {
            $score += 10;
            $reasons[] = 'Tren suasana hati menurun';
        }

        $waiting = $this->waitingMinutes($c);
        if ($waiting !== null) {
            $score += min(20, intdiv($waiting, 10));
            if ($waiting >= 10) {
                $reasons[] = 'Menunggu '.$this->humanMinutes($waiting);
            }
        }

        // Sinyal otomatis menaikkan skor, tetapi tidak pernah mengubah urgensi pilihan guru.
        if ($c->followUps()->whereNotNull('responded_at')->latest('responded_at')->value('response') === 'worse') {
            $score += 20;
            $reasons[] = 'Follow-up: makin berat';
        }

        if ($c->is_emergency) {
            $score += 40;
            $reasons[] = 'Tombol darurat';
        }

        return ['score' => $score, 'reasons' => $reasons];
    }

    /** Menit sejak pesan murid terakhir yang belum dibalas, null jika sudah dibalas. */
    public function waitingMinutes(Conversation $c): ?int
    {
        if (! $c->last_student_message_at) {
            return null;
        }
        if ($c->last_counselor_message_at && $c->last_counselor_message_at->gte($c->last_student_message_at)) {
            return null;
        }

        return (int) $c->last_student_message_at->diffInMinutes(now());
    }

    public function negativeMoodStreak(Conversation $c): int
    {
        $student = $this->resolver->resolve($c);
        if (! $student || ! ($student->studentProfile?->mood_signal_enabled ?? false)) {
            return 0;
        }

        return static::streakFor($student);
    }

    /** Jumlah hari kalender berturut-turut mood negatif, mundur dari hari ini atau kemarin. */
    public static function streakFor(User $student): int
    {
        $tz = School::current()->timezone;
        $today = CarbonImmutable::now($tz)->startOfDay();
        $entries = MoodEntry::where('student_id', $student->id)
            ->where('entry_date', '>=', $today->subDays(30)->toDateString())
            ->orderByDesc('entry_date')
            ->get(['entry_date', 'mood'])
            ->keyBy(fn ($e) => $e->entry_date->toDateString());

        $cursor = $entries->has($today->toDateString()) ? $today : $today->subDay();
        $streak = 0;
        while (($e = $entries->get($cursor->toDateString())) && $e->isNegative()) {
            $streak++;
            $cursor = $cursor->subDay();
        }

        return $streak;
    }

    protected function humanMinutes(int $minutes): string
    {
        if ($minutes < 60) {
            return $minutes.' menit';
        }
        if ($minutes < 1440) {
            return intdiv($minutes, 60).' jam';
        }

        return intdiv($minutes, 1440).' hari';
    }
}
