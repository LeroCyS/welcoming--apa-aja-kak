<?php

namespace Database\Seeders;

use App\Actions\ProvisionSchoolAction;
use App\Models\ArticleCategory;
use App\Models\ReplyTemplate;
use App\Models\School;
use App\Models\Topic;
use Illuminate\Database\Seeder;

class ReferenceSeeder extends Seeder
{
    public function run(ProvisionSchoolAction $provision): void
    {
        $topics = [
            ['keluarga', 'Keluarga', 'home', 'terracotta'],
            ['pertemanan', 'Pertemanan', 'people', 'sage'],
            ['asmara', 'Asmara', 'heart', 'terracotta'],
            ['bullying', 'Bullying', 'shield', 'sand'],
            ['akademik', 'Akademik/Nilai', 'book', 'sage'],
            ['diri-sendiri', 'Diri Sendiri', 'person', 'sand'],
            ['masa-depan', 'Masa Depan/Karier', 'compass', 'sage'],
            ['belum-tahu', 'Belum tahu / Campur aduk', 'cloud', 'sand'],
        ];
        foreach ($topics as $i => [$slug, $name, $icon, $color]) {
            Topic::updateOrCreate(['slug' => $slug], ['name' => $name, 'icon' => $icon, 'color' => $color, 'sort_order' => $i + 1, 'is_active' => true]);
        }

        $cats = [
            ['Overthinking', 'overthinking', 'cloud'],
            ['Stres Ujian', 'stres-ujian', 'book'],
            ['Manajemen Waktu', 'manajemen-waktu', 'clock'],
            ['Pertemanan', 'pertemanan', 'people'],
            ['Keluarga', 'keluarga', 'home'],
            ['Percaya Diri', 'percaya-diri', 'sparkle'],
        ];
        foreach ($cats as $i => [$name, $slug, $icon]) {
            ArticleCategory::updateOrCreate(['slug' => $slug], ['name' => $name, 'icon' => $icon, 'sort_order' => $i + 1]);
        }

        $templates = [
            'keluarga' => [
                ['Menyambut cerita keluarga', 'Hai {sapaan}, makasih sudah mau cerita soal keluargamu. Itu nggak gampang, dan aku senang kamu percaya sama aku. Boleh ceritakan apa yang paling bikin kamu kepikiran akhir-akhir ini?'],
                ['Validasi perasaan', 'Wajar banget kalau kamu merasa begitu, {sapaan}. Situasi di rumah memang bisa bikin capek hati. Kamu nggak sendirian kok.'],
            ],
            'pertemanan' => [
                ['Menyambut cerita pertemanan', 'Hai {sapaan}, makasih sudah cerita. Masalah pertemanan memang bisa kerasa berat banget. Boleh ceritakan kejadiannya pelan-pelan?'],
                ['Menggali perasaan', '{sapaan}, waktu itu terjadi, apa yang paling kamu rasakan? Sedih, kesal, atau bingung? Semua perasaan itu valid.'],
            ],
            'asmara' => [
                ['Menyambut cerita asmara', 'Hai {sapaan}, makasih sudah mau berbagi. Urusan hati memang sering bikin bingung. Aku dengerin ya.'],
                ['Relasi sehat', '{sapaan}, dalam hubungan yang sehat, kamu berhak merasa aman dan dihargai. Menurutmu, apa yang kamu butuhkan sekarang?'],
            ],
            'bullying' => [
                ['Menyambut cerita perundungan', 'Hai {sapaan}, terima kasih sudah berani cerita. Apa yang kamu alami bukan salahmu, dan kamu berhak merasa aman di sekolah.'],
                ['Keamanan dulu', '{sapaan}, apakah sekarang kamu merasa aman? Kalau ada yang mengancammu, kita bisa cari cara supaya kamu terlindungi.'],
            ],
            'akademik' => [
                ['Menyambut cerita akademik', 'Hai {sapaan}, makasih sudah cerita. Tekanan nilai dan tugas memang bisa numpuk. Mata pelajaran mana yang paling bikin kepikiran?'],
                ['Pecah jadi langkah kecil', '{sapaan}, gimana kalau kita pecah tugasnya jadi langkah-langkah kecil? Satu per satu pasti lebih ringan.'],
            ],
            'diri-sendiri' => [
                ['Menyambut cerita diri', 'Hai {sapaan}, makasih sudah mau terbuka soal dirimu. Itu langkah yang berani. Aku dengerin tanpa menghakimi.'],
                ['Apresiasi diri', '{sapaan}, coba sebutkan satu hal kecil yang berhasil kamu lakukan minggu ini. Sekecil apa pun, itu berarti.'],
            ],
            'masa-depan' => [
                ['Menyambut cerita masa depan', 'Hai {sapaan}, bingung soal masa depan itu wajar banget, apalagi di usiamu. Yuk kita obrolin pelan-pelan.'],
                ['Eksplorasi minat', '{sapaan}, kegiatan apa yang bikin kamu lupa waktu saking asyiknya? Dari situ kita bisa mulai melihat minatmu.'],
            ],
            'belum-tahu' => [
                ['Menyambut perasaan campur aduk', 'Hai {sapaan}, nggak apa-apa kalau belum tahu harus mulai dari mana. Ceritakan saja apa pun yang terlintas.'],
                ['Satu per satu', '{sapaan}, kalau semuanya terasa campur aduk, kita bisa urai satu per satu. Yang paling terasa berat sekarang apa?'],
            ],
        ];
        foreach ($templates as $slug => $list) {
            $topicId = Topic::where('slug', $slug)->value('id');
            foreach ($list as [$title, $body]) {
                ReplyTemplate::updateOrCreate(['counselor_id' => null, 'title' => $title], ['topic_id' => $topicId, 'body' => $body]);
            }
        }

        // Kata kunci sensitif & kontak darurat dimiliki per sekolah.
        foreach (School::all() as $school) {
            $provision->execute($school);
        }
    }
}
