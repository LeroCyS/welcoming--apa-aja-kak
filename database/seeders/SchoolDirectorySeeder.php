<?php

namespace Database\Seeders;

use App\Actions\ProvisionSchoolAction;
use App\Models\CounselorProfile;
use App\Models\CounselorServiceHour;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Sekolah demo untuk jenjang SD, SMP, dan SMK (SMA sudah ada: SMA Harapan Bangsa), masing-masing
 * dengan admin dan 2 Guru BK, supaya murid bisa memilih sekolah dari jenjang mana pun saat onboarding.
 * Semua password `password`.
 */
class SchoolDirectorySeeder extends Seeder
{
    /** @var list<array{level: string, name: string, address: string, domain: string, timezone: string, start: string, end: string, counselors: list<array{0: string, 1: string, 2: string, 3: string, 4: string, 5: string}>}> */
    public const SCHOOLS = [
        ['level' => 'sd', 'name' => 'SD Negeri Tunas Bangsa', 'address' => 'Jl. Melati No. 4, Makassar', 'domain' => 'sdtunasbangsa', 'timezone' => 'Asia/Makassar', 'start' => '07:00:00', 'end' => '12:00:00', 'counselors' => [
            ['Wulan Sari', 'Bu Wulan', 'female', 'Mendongeng & menggambar', 'Pisang goreng', 'Aku suka dengar cerita apa pun, dari soal teman sebangku sampai PR yang bikin pusing. Nggak ada cerita yang terlalu kecil.'],
            ['Agus Salim', 'Pak Agus', 'male', 'Main catur & bersepeda', 'Bakso', 'Kalau lagi sedih atau takut, boleh banget cerita ke aku. Kita cari jalan keluarnya bareng-bareng, pelan-pelan.'],
        ]],
        ['level' => 'sd', 'name' => 'SD Pelita Harapan Nusantara', 'address' => 'Jl. Kenanga No. 21, Maros', 'domain' => 'sdpelita', 'timezone' => 'Asia/Makassar', 'start' => '07:00:00', 'end' => '12:00:00', 'counselors' => [
            ['Fitri Ramadhani', 'Bu Fitri', 'female', 'Merawat tanaman', 'Es buah', 'Di sini kamu boleh cerita tanpa takut dimarahi. Aku akan mendengarkan dulu sebelum memberi saran.'],
            ['Yusuf Hakim', 'Pak Yusuf', 'male', 'Main musik angklung', 'Nasi kuning', 'Dulu aku juga pemalu waktu SD. Ceritakan saja apa yang kamu rasakan, aku siap menemani.'],
        ]],
        ['level' => 'smp', 'name' => 'SMP Negeri 3 Nusantara', 'address' => 'Jl. Cendana No. 9, Makassar', 'domain' => 'smpn3nusantara', 'timezone' => 'Asia/Makassar', 'start' => '07:00:00', 'end' => '14:00:00', 'counselors' => [
            ['Maya Lestari', 'Bu Maya', 'female', 'Yoga & baca komik', 'Mi titi', 'Masa SMP itu penuh perubahan, dan bingung itu wajar. Yuk cerita, aku nggak akan menghakimi.'],
            ['Rizky Pratama', 'Pak Rizky', 'male', 'Basket & main game', 'Sop saudara', 'Mau soal teman, keluarga, atau nilai, aku siap dengar. Kamu bisa mulai tanpa nama dulu.'],
        ]],
        ['level' => 'smp', 'name' => 'SMP Bina Insani', 'address' => 'Jl. Anggrek No. 15, Gowa', 'domain' => 'smpbinainsani', 'timezone' => 'Asia/Makassar', 'start' => '07:00:00', 'end' => '14:00:00', 'counselors' => [
            ['Dewi Anggraini', 'Bu Dewi', 'female', 'Memasak & menulis jurnal', 'Pallubasa', 'Aku percaya setiap perasaan punya alasan. Ceritakan pelan-pelan, aku menemanimu.'],
            ['Hendro Saputra', 'Pak Hendro', 'male', 'Fotografi & hiking', 'Konro', 'Kadang yang kita butuhkan cuma didengar. Aku di sini untuk itu.'],
        ]],
        ['level' => 'smk', 'name' => 'SMK Teknologi Mandiri', 'address' => 'Jl. Industri No. 2, Makassar', 'domain' => 'smkteknologimandiri', 'timezone' => 'Asia/Makassar', 'start' => '07:00:00', 'end' => '15:00:00', 'counselors' => [
            ['Lina Marlina', 'Bu Lina', 'female', 'Desain grafis & kopi', 'Jalangkote', 'Bingung soal PKL, kerja, atau kuliah? Atau sekadar capek? Ayo ngobrol, aku dengarkan.'],
            ['Bayu Nugroho', 'Pak Bayu', 'male', 'Otomotif & memancing', 'Coto Makassar', 'Aku dulu juga anak SMK. Apa pun ceritamu, kita hadapi bareng.'],
        ]],
        ['level' => 'smk', 'name' => 'SMK Bakti Nusa', 'address' => 'Jl. Pelabuhan No. 30, Parepare', 'domain' => 'smkbaktinusa', 'timezone' => 'Asia/Makassar', 'start' => '07:00:00', 'end' => '15:00:00', 'counselors' => [
            ['Nur Aisyah', 'Bu Aisyah', 'female', 'Menjahit & podcast', 'Barongko', 'Tempat ini aman untuk cerita apa saja. Kamu yang menentukan kapan mau membuka identitas.'],
            ['Arif Setiawan', 'Pak Arif', 'male', 'Main futsal & elektronika', 'Mie kering', 'Kalau ada yang mengganjal, jangan dipendam sendiri. Aku siap mendengarkan kapan pun di jam layanan.'],
        ]],
    ];

    public function run(ProvisionSchoolAction $provision): void
    {
        $password = Hash::make('password');

        foreach (self::SCHOOLS as $s) {
            $school = School::updateOrCreate(['name' => $s['name']], [
                'level' => $s['level'], 'address' => $s['address'], 'timezone' => $s['timezone'],
                'default_service_start' => $s['start'], 'default_service_end' => $s['end'],
            ]);
            $provision->execute($school);

            User::updateOrCreate(['email' => 'admin@'.$s['domain'].'.sch.id'], [
                'school_id' => $school->id, 'school_joined_at' => now(), 'role' => User::ROLE_ADMIN, 'name' => 'Admin '.$s['name'],
                'identifier' => 'admin.'.$s['domain'], 'password' => $password, 'must_change_password' => false, 'is_active' => true,
            ]);

            foreach ($s['counselors'] as $i => [$name, $nick, $gender, $hobby, $food, $intro]) {
                $handle = 'guru'.($i + 1).'.'.$s['domain'];
                $u = User::updateOrCreate(['email' => $handle.'@'.$s['domain'].'.sch.id'], [
                    'school_id' => $school->id, 'school_joined_at' => now(), 'role' => User::ROLE_COUNSELOR, 'name' => $name,
                    'identifier' => $handle, 'password' => $password, 'must_change_password' => false, 'is_active' => true,
                ]);
                CounselorProfile::updateOrCreate(['user_id' => $u->id], [
                    'nickname' => $nick, 'gender' => $gender, 'hobbies' => $hobby, 'favorite_food' => $food, 'intro_message' => $intro,
                    'offline_auto_reply' => null, 'is_online' => false, 'last_seen_at' => null,
                ]);
                CounselorServiceHour::where('counselor_id', $u->id)->delete();
                foreach (range(1, 5) as $day) {
                    CounselorServiceHour::create(['counselor_id' => $u->id, 'day_of_week' => $day, 'start_time' => $s['start'], 'end_time' => $s['end']]);
                }
            }
        }
    }
}
