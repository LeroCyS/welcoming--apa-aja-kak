<?php

namespace Database\Seeders;

use App\Models\CounselorProfile;
use App\Models\CounselorServiceHour;
use App\Models\MoodEntry;
use App\Models\MoodPromptLog;
use App\Models\School;
use App\Models\StudentProfile;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Akun khusus pengujian manual (lihat TEST_CASES.md). Aman dijalankan berulang:
 * php artisan db:seed --class=QaAccountSeeder
 */
class QaAccountSeeder extends Seeder
{
    public const PASSWORD = 'ujicoba123';

    public const PIN = '123456';

    public function run(): void
    {
        $school = School::current();
        $password = Hash::make(self::PASSWORD);
        $base = ['school_id' => $school->id, 'password' => $password, 'must_change_password' => false, 'is_active' => true];

        User::updateOrCreate(['identifier' => 'qa.admin'], $base + ['role' => 'admin', 'name' => 'Admin Uji', 'email' => null]);

        $counselors = [
            ['qa.guru1', 'Ani Lestari', 'Bu Ani', 'female', true],
            ['qa.guru2', 'Budi Santoso', 'Pak Budi', 'male', false],
            ['qa.guru3', 'Uji Rahayu', 'Bu Uji', 'female', true],
            ['qa.guru4', 'Dewi Anggraini', 'Bu Dewi', 'female', true],
        ];
        foreach ($counselors as [$id, $name, $nick, $gender, $online]) {
            $u = User::updateOrCreate(['identifier' => $id], $base + ['role' => 'counselor', 'name' => $name, 'email' => null]);
            CounselorProfile::updateOrCreate(['user_id' => $u->id], [
                'nickname' => $nick, 'gender' => $gender, 'hobbies' => 'Membaca', 'favorite_food' => 'Soto',
                'intro_message' => 'Akun uji coba Guru BK.', 'offline_auto_reply' => null,
                'is_online' => $online, 'last_seen_at' => now(),
            ]);
            CounselorServiceHour::where('counselor_id', $u->id)->delete();
            foreach ([0, 1, 2, 3, 4, 5, 6] as $d) {
                // qa.guru1, qa.guru3 & qa.guru4 melayani setiap hari 00.00-23.59 agar selalu "tersedia" saat online; qa.guru2 tidak punya jam layanan (memicu auto-reply).
                if ($id !== 'qa.guru2') {
                    CounselorServiceHour::create(['counselor_id' => $u->id, 'day_of_week' => $d, 'start_time' => '00:00:00', 'end_time' => '23:59:00']);
                }
            }
        }

        $students = [
            ['qa.murid1', 'Murid Uji Satu', 'X IPA 1', 10, []],
            ['qa.murid2', 'Murid Uji Dua', 'XI IPS 1', 11, ['mood' => true]],
            ['qa.murid3', 'Murid Uji Tiga', 'XII IPA 2', 12, ['pin' => true]],
            ['qa.murid4', null, null, null, []],
            ['qa.murid5', 'Murid Uji Lima', 'XI IPA 2', 11, ['inactive' => true]],
        ];
        foreach ($students as [$id, $name, $class, $grade, $flags]) {
            $u = User::updateOrCreate(['email' => $id.'@welcoming.test'], array_merge($base, [
                'role' => 'student', 'name' => $name, 'identifier' => null,
                'is_active' => empty($flags['inactive']),
            ]));
            StudentProfile::updateOrCreate(['user_id' => $u->id], [
                'class_name' => $class, 'grade_level' => $grade,
                'pin_enabled' => ! empty($flags['pin']),
                'pin_hash' => ! empty($flags['pin']) ? Hash::make(self::PIN) : null,
                'pin_failed_attempts' => 0, 'pin_locked_until' => null,
                'notifications_enabled' => true, 'mood_prompt_enabled' => true, 'mood_signal_enabled' => true,
                'onboarded_at' => now(), 'tour_completed_at' => now(),
            ]);

            if (! empty($flags['mood'])) {
                $today = CarbonImmutable::now($school->timezone)->startOfDay();
                foreach ([1 => 'sad', 2 => 'anxious', 3 => 'angry'] as $daysAgo => $mood) {
                    MoodEntry::updateOrCreate(
                        ['student_id' => $u->id, 'entry_date' => $today->subDays($daysAgo)->toDateString()],
                        ['mood' => $mood, 'note' => 'Catatan uji hari ke-'.$daysAgo],
                    );
                }
                $u->moodEntries()->whereDate('entry_date', $today->toDateString())->delete();
                MoodPromptLog::where('student_id', $u->id)->delete();
            }
        }
    }
}
