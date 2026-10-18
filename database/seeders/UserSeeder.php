<?php

namespace Database\Seeders;

use App\Models\CounselorProfile;
use App\Models\CounselorServiceHour;
use App\Models\School;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::current();
        $password = Hash::make('password');

        User::updateOrCreate(['identifier' => 'admin'], [
            'school_id' => $school->id, 'role' => 'admin', 'name' => 'Pak Hendra', 'email' => 'admin@harapanbangsa.sch.id',
            'password' => $password, 'must_change_password' => false, 'is_active' => true,
        ]);

        $counselors = [
            ['guru1', 'Rina Wulandari', 'Bu Rina', 'female', 'Merajut & nonton drakor', 'Coto Makassar', 'Aku juga pernah ngerasa overthinking pas SMA, sampai susah tidur. Jadi aku nggak akan menganggap ceritamu sepele.', true],
            ['guru2', 'Dimas Prasetyo', 'Pak Dimas', 'male', 'Main futsal & gitar', 'Nasi goreng kampung', 'Dulu aku anak yang paling sering dipanggil ke BK. Sekarang aku yang duduk di sini, dan aku janji, kursinya nyaman.', false],
            ['guru3', 'Sari Handayani', 'Bu Sari', 'female', 'Berkebun & baca novel', 'Pisang epe', 'Aku suka dengerin cerita sambil minum teh. Mau soal masa depan, kuliah, atau sekadar bingung, ayo ngobrol.', true],
        ];
        foreach ($counselors as [$id, $name, $nick, $gender, $hobby, $food, $intro, $online]) {
            $u = User::updateOrCreate(['identifier' => $id], [
                'school_id' => $school->id, 'role' => 'counselor', 'name' => $name, 'email' => $id.'@harapanbangsa.sch.id',
                'password' => $password, 'must_change_password' => false, 'is_active' => true,
            ]);
            CounselorProfile::updateOrCreate(['user_id' => $u->id], [
                'nickname' => $nick, 'gender' => $gender, 'hobbies' => $hobby, 'favorite_food' => $food, 'intro_message' => $intro,
                'offline_auto_reply' => null, 'is_online' => $online, 'last_seen_at' => $online ? now() : now()->subDay(),
            ]);
            CounselorServiceHour::where('counselor_id', $u->id)->delete();
            foreach (range(1, 5) as $d) {
                CounselorServiceHour::create(['counselor_id' => $u->id, 'day_of_week' => $d, 'start_time' => '07:00:00', 'end_time' => '15:00:00']);
            }
        }

        // Murid demo utama, murid mendaftar sendiri dengan email; nama/kelas diisi sendiri di profil.
        $alya = User::updateOrCreate(['email' => 'murid1@contoh.id'], [
            'school_id' => $school->id, 'role' => 'student', 'name' => 'Alya Putri Ramadhani', 'identifier' => null,
            'password' => $password, 'must_change_password' => false, 'is_active' => true,
        ]);
        StudentProfile::updateOrCreate(['user_id' => $alya->id], ['class_name' => 'XI IPS 2', 'grade_level' => 11, 'onboarded_at' => now(), 'tour_completed_at' => now()]);

        $faker = fake('id_ID');
        $faker->seed(2026);
        $classes = [10 => ['X IPA 1', 'X IPA 2', 'X IPS 1'], 11 => ['XI IPA 1', 'XI IPA 2', 'XI IPS 1', 'XI IPS 2'], 12 => ['XII IPA 1', 'XII IPA 2', 'XII IPS 1']];
        for ($i = 1; $i <= 39; $i++) {
            $grade = [10, 11, 12][$i % 3];
            // Sebagian murid tidak mengisi identitas sama sekali (anonim penuh).
            $filled = $i % 4 !== 0;
            $u = User::updateOrCreate(['email' => 'murid'.($i + 1).'@contoh.id'], [
                'school_id' => $school->id, 'role' => 'student', 'name' => $filled ? $faker->firstName().' '.$faker->lastName() : null,
                'identifier' => null, 'password' => $password, 'must_change_password' => false, 'is_active' => true,
            ]);
            StudentProfile::updateOrCreate(['user_id' => $u->id], ['class_name' => $filled ? $faker->randomElement($classes[$grade]) : null, 'grade_level' => $grade, 'onboarded_at' => now(), 'tour_completed_at' => now()]);
        }
    }
}
