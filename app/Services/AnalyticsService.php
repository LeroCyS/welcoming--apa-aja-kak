<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\CounselingSession;
use App\Models\MoodEntry;
use App\Models\School;
use App\Models\Topic;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Analitik agregat seluruh sekolah untuk dashboard Guru BK.
 * Ambang anonimitas: nilai per angkatan / per topik < analytics_min_count ditampilkan sebagai "<5".
 */
class AnalyticsService
{
    public const PERIODS = ['minggu' => 'Minggu ini', 'bulan' => 'Bulan ini', 'semester' => 'Semester ini'];

    public function min(): int
    {
        return (int) config('welcoming.analytics_min_count', 5);
    }

    /** Nilai aman untuk ditampilkan: angka bila ≥ ambang, selain itu null (ditampilkan "<5"). */
    public function mask(int $value): ?int
    {
        return $value >= $this->min() ? $value : null;
    }

    /** @return array{0: CarbonImmutable, 1: CarbonImmutable, 2: CarbonImmutable, 3: CarbonImmutable, 4: string} */
    public function ranges(string $period): array
    {
        $now = CarbonImmutable::now(School::current()->timezone);
        switch ($period) {
            case 'minggu':
                $start = $now->startOfWeek();

                return [$start, $now, $start->subWeek(), $start, 'minggu lalu'];
            case 'semester':
                $start = $now->month >= 7 ? $now->setDate($now->year, 7, 1)->startOfDay() : $now->setDate($now->year, 1, 1)->startOfDay();

                return [$start, $now, $start->subMonths(6), $start, 'semester lalu'];
            default:
                $start = $now->startOfMonth();

                return [$start, $now, $start->subMonth(), $start, 'bulan lalu'];
        }
    }

    public function dashboard(string $period): array
    {
        $period = array_key_exists($period, self::PERIODS) ? $period : 'bulan';

        $d = Cache::remember('analytics:'.School::current()->id.':'.$period.':'.now()->format('YmdH').floor(now()->minute / 10), now()->addMinutes(10),
            fn () => json_decode(json_encode($this->compute($period)), true));
        $d['trend'] = collect($d['trend']);
        $d['mood'] = collect($d['mood']);

        return $d;
    }

    protected function compute(string $period): array
    {
        [$from, $to, $prevFrom, $prevTo, $prevLabel] = $this->ranges($period);
        $school = School::current();
        $tz = $school->timezone;
        $u = fn (CarbonImmutable $d) => $d->utc();

        $inPeriod = Conversation::inSchool($school)->whereBetween('created_at', [$u($from), $u($to)]);
        $newCount = (clone $inPeriod)->count();
        $prevNew = Conversation::inSchool($school)->whereBetween('created_at', [$u($prevFrom), $u($prevTo)])->count();

        // Data yang waktu balasan pertamanya lebih awal dari waktu dibuat (data rusak/impor) diabaikan agar rata-rata tidak negatif.
        $avgResponse = (clone $inPeriod)->whereNotNull('first_response_at')->whereColumn('first_response_at', '>=', 'created_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, first_response_at)) as m')->value('m');

        $cards = [
            'active' => Conversation::inSchool($school)->where('status', '!=', 'resolved')->count(),
            'new' => $newCount,
            'new_prev' => $prevNew,
            'urgent' => Conversation::inSchool($school)->where('status', '!=', 'resolved')->where(fn ($q) => $q->where('urgency', 'urgent')->orWhere('priority_score', '>=', 60))->count(),
            'avg_response' => $avgResponse !== null ? $this->humanMinutes((int) round($avgResponse)) : '-',
            'sessions' => CounselingSession::whereIn('counselor_id', User::counselors()->inSchool($school)->select('id'))->where('status', '!=', 'cancelled')->whereHas('slot', fn ($q) => $q->whereBetween('start_at', [$u($from), $u($to)]))->count(),
            'counselors' => User::counselors()->inSchool($school)->active()->count(),
        ];

        // Tren 6 bulan
        $start6 = CarbonImmutable::now($tz)->startOfMonth()->subMonths(5);
        $monthly = Conversation::inSchool($school)->where('created_at', '>=', $u($start6))
            ->selectRaw("DATE_FORMAT(CONVERT_TZ(created_at, '+00:00', ?), '%Y-%m') ym, COUNT(*) c", [$start6->format('P')])
            ->groupBy('ym')->pluck('c', 'ym');
        $trend = collect(range(0, 5))->map(fn ($i) => $start6->addMonths($i))->map(fn ($m) => ['label' => $m->translatedFormat('M'), 'full' => $m->translatedFormat('F Y'), 'value' => (int) ($monthly[$m->format('Y-m')] ?? 0)])->values();

        // Topik (periode & sebelumnya)
        $topics = Topic::active()->get();
        $topicCounts = $this->topicCounts($school->id, $u($from), $u($to));
        $prevTopicCounts = $this->topicCounts($school->id, $u($prevFrom), $u($prevTo));
        $maxTopic = max(1, $topicCounts->max() ?? 1);
        $topicBars = $topics->map(fn ($t) => [
            'name' => $t->name,
            'slug' => $t->slug,
            'value' => $this->mask((int) ($topicCounts[$t->id] ?? 0)),
            'pct' => round(((int) ($topicCounts[$t->id] ?? 0)) / $maxTopic * 100),
        ])->sortByDesc(fn ($b) => $b['value'] ?? -1)->values();

        // Heatmap angkatan × topik
        $heatRaw = DB::table('conversations')->join('conversation_topic', 'conversation_topic.conversation_id', '=', 'conversations.id')
            ->where('conversations.school_id', $school->id)
            ->whereBetween('conversations.created_at', [$u($from), $u($to)])
            ->selectRaw('conversations.grade_level g, conversation_topic.topic_id t, COUNT(*) c')->groupBy('g', 't')->get();
        $grades = DB::table('student_profiles')->join('users', 'users.id', '=', 'student_profiles.user_id')->where('users.school_id', $school->id)
            ->whereNotNull('grade_level')->distinct()->orderBy('grade_level')->pluck('grade_level')->all();
        $heat = $topics->map(fn ($t) => [
            'topic' => $t->name,
            'cells' => collect($grades)->map(function ($g) use ($heatRaw, $t) {
                $v = (int) ($heatRaw->first(fn ($r) => (int) $r->g === (int) $g && (int) $r->t === $t->id)->c ?? 0);

                return ['grade' => $g, 'value' => $this->mask($v)];
            })->all(),
        ])->values();
        $heatMax = max(1, $heatRaw->max('c') ?? 1);

        // Distribusi mood per angkatan
        $moodRaw = DB::table('mood_entries')->join('student_profiles', 'student_profiles.user_id', '=', 'mood_entries.student_id')
            ->join('users', 'users.id', '=', 'mood_entries.student_id')->where('users.school_id', $school->id)
            ->whereBetween('mood_entries.entry_date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('student_profiles.grade_level g, mood_entries.mood m, COUNT(*) c')->groupBy('g', 'm')->get();
        $moodRows = collect($grades)->map(function ($g) use ($moodRaw) {
            $rows = $moodRaw->where('g', $g);
            $total = (int) $rows->sum('c');

            return [
                'label' => 'Kelas '.$g,
                'total' => $total,
                'visible' => $total >= $this->min(),
                'segments' => collect(MoodEntry::MOODS)->map(function ($m) use ($rows, $total) {
                    $c = (int) ($rows->firstWhere('m', $m)->c ?? 0);

                    return ['mood' => $m, 'label' => MoodEntry::LABELS[$m], 'color' => MoodEntry::COLORS[$m], 'value' => $this->mask($c), 'pct' => $total ? round($c / $total * 100) : 0, 'raw_zero' => $c === 0];
                })->all(),
            ];
        })->values();
        $allMood = $moodRaw->groupBy('m')->map->sum('c');
        $allTotal = (int) $allMood->sum();

        // Insight otomatis
        $insights = [];
        $programs = config('welcoming.topic_programs');
        foreach ($topics as $t) {
            $cur = (int) ($topicCounts[$t->id] ?? 0);
            $prev = (int) ($prevTopicCounts[$t->id] ?? 0);
            if ($cur < $this->min()) {
                continue;
            }
            $growth = $prev > 0 ? ($cur - $prev) / $prev : null;
            if ($growth === null || $growth >= 0.3) {
                $insights[] = [
                    'topic' => $t->name,
                    'pct' => $growth === null ? null : (int) round($growth * 100),
                    'program' => $programs[$t->slug] ?? null,
                    'text' => 'Topik '.$t->name.($growth === null ? ' muncul cukup banyak ('.$cur.' cerita) '.mb_strtolower(self::PERIODS[$period]).', padahal sebelumnya hampir tidak ada.' : ' naik '.(int) round($growth * 100).'% '.mb_strtolower(self::PERIODS[$period]).' dibanding '.$prevLabel.'.'),
                ];
            }
        }

        return [
            'period' => $period,
            'range' => $from->translatedFormat('j M').' - '.$to->translatedFormat('j M Y'),
            'prev_label' => $prevLabel,
            'cards' => $cards,
            'trend' => $trend,
            'topicBars' => $topicBars,
            'heat' => $heat,
            'heatMax' => $heatMax,
            'grades' => $grades,
            'mood' => $moodRows,
            'moodAll' => ['total' => $allTotal, 'visible' => $allTotal >= $this->min(), 'segments' => collect(MoodEntry::MOODS)->map(fn ($m) => ['mood' => $m, 'label' => MoodEntry::LABELS[$m], 'color' => MoodEntry::COLORS[$m], 'value' => $this->mask((int) ($allMood[$m] ?? 0)), 'pct' => $allTotal ? round(($allMood[$m] ?? 0) / $allTotal * 100) : 0])->all()],
            'insights' => $insights,
            'min' => $this->min(),
        ];
    }

    protected function topicCounts(int $schoolId, $from, $to)
    {
        return DB::table('conversation_topic')->join('conversations', 'conversations.id', '=', 'conversation_topic.conversation_id')
            ->where('conversations.school_id', $schoolId)
            ->whereBetween('conversations.created_at', [$from, $to])
            ->selectRaw('conversation_topic.topic_id t, COUNT(*) c')->groupBy('t')->pluck('c', 't');
    }

    protected function humanMinutes(int $m): string
    {
        if ($m < 60) {
            return $m.'m';
        }
        if ($m < 1440) {
            return intdiv($m, 60).'j '.($m % 60).'m';
        }

        return intdiv($m, 1440).'h '.intdiv($m % 1440, 60).'j';
    }
}
