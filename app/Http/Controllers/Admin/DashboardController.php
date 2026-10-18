<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Conversation;
use App\Models\School;
use App\Models\StudentProfile;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\View\View;

/** Admin hanya melihat statistik penggunaan. Percakapan hanya berupa hitungan per bulan. */
class DashboardController extends Controller
{
    public function index(): View
    {
        $school = School::current();
        $tz = $school->timezone;
        $start = CarbonImmutable::now($tz)->startOfMonth()->subMonths(5);
        $months = collect(range(0, 5))->map(fn ($i) => $start->addMonths($i));

        $logins = AuditLog::where('action', 'login')->whereIn('user_id', User::inSchool($school)->select('id'))->where('created_at', '>=', $start->utc())
            ->selectRaw("DATE_FORMAT(CONVERT_TZ(created_at, '+00:00', ?), '%Y-%m') as ym, COUNT(*) as c", [$start->format('P')])
            ->groupBy('ym')->pluck('c', 'ym');
        $convs = Conversation::inSchool($school)->where('created_at', '>=', $start->utc())
            ->selectRaw("DATE_FORMAT(CONVERT_TZ(created_at, '+00:00', ?), '%Y-%m') as ym, COUNT(*) as c", [$start->format('P')])
            ->groupBy('ym')->pluck('c', 'ym');

        $profiles = fn () => StudentProfile::query()->whereIn('user_id', User::students()->inSchool($school)->select('id'));
        $mk = fn ($data) => $months->map(fn ($m) => ['month' => $m->translatedFormat('M'), 'full' => $m->translatedFormat('F Y'), 'value' => (int) ($data[$m->format('Y-m')] ?? 0)])->all();

        $total = User::inSchool($school)->count();
        $active = User::inSchool($school)->where('is_active', true)->count();

        return view('admin.dashboard', [
            'school' => $school,
            'stats' => [
                'students' => User::students()->inSchool($school)->count(),
                'grades' => $profiles()->whereNotNull('grade_level')->distinct()->orderBy('grade_level')->pluck('grade_level'),
                'classes' => $profiles()->distinct()->count('class_name'),
                'counselors' => User::counselors()->inSchool($school)->count(),
                'counselors_active' => User::counselors()->inSchool($school)->where('is_active', true)->count(),
                'active' => $active,
                'active_pct' => $total ? round($active / $total * 100) : 0,
                'pending' => User::inSchool($school)->where('must_change_password', true)->count(),
            ],
            'charts' => [
                ['title' => 'Login per bulan', 'sub' => 'Murid & Guru BK, '.$months->first()->translatedFormat('M').'-'.$months->last()->translatedFormat('M Y'), 'color' => '#5C7350', 'unit' => 'login', 'bars' => $mk($logins)],
                ['title' => 'Percakapan baru per bulan', 'sub' => 'Hanya hitungan, agregat semua Guru BK', 'color' => '#B5523F', 'unit' => 'percakapan', 'bars' => $mk($convs)],
            ],
        ]);
    }
}
