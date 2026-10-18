<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\School;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $authors = User::counselors()->inSchool(School::current())->orderBy('id')->pluck('id')->all();

        $articles = [
            [
                'overthinking', 'article', 'Kenapa Pikiranku Nggak Bisa Berhenti? Mengenal Overthinking', true, ['diri-sendiri', 'belum-tahu'],
                'Overthinking itu wajar, tapi bisa melelahkan. Kenali tanda-tandanya dan coba tiga cara sederhana untuk menenangkan pikiran.',
                '<p>Pernah nggak, kamu rebahan mau tidur, tapi kepala malah memutar ulang obrolan tadi siang? "Tadi aku ngomong gitu, dia tersinggung nggak ya?" Lalu pikiran itu bercabang ke mana-mana sampai jam dua pagi. Kalau iya, kamu nggak sendirian. Itu namanya <strong>overthinking</strong>.</p>
<h2>Apa itu overthinking?</h2>
<p>Overthinking adalah kebiasaan memikirkan sesuatu secara berulang-ulang tanpa sampai pada keputusan atau jalan keluar. Bentuknya bisa berupa menyesali hal yang sudah lewat, atau mengkhawatirkan hal yang belum tentu terjadi.</p>
<h2>Tanda-tandanya</h2>
<ul><li>Susah tidur karena pikiran terus berputar.</li><li>Sering membayangkan skenario terburuk.</li><li>Sulit memutuskan hal kecil, misalnya mau pakai baju apa.</li><li>Merasa capek padahal belum melakukan apa-apa.</li></ul>
<h2>Tiga cara kecil yang bisa dicoba</h2>
<p><strong>1. Tulis, jangan cuma dipikir.</strong> Ambil kertas dan tulis semua yang ada di kepala. Pikiran yang sudah tertulis biasanya terasa lebih kecil.</p>
<p><strong>2. Tanya: "Ini bisa aku kendalikan atau nggak?"</strong> Kalau bisa, buat satu langkah kecil. Kalau nggak bisa, latih diri untuk melepasnya pelan-pelan.</p>
<p><strong>3. Kembali ke tubuh.</strong> Tarik napas 4 hitungan, tahan 4, buang 6. Ulangi lima kali. Napas yang pelan memberi sinyal aman ke otak.</p>
<blockquote>Pikiranmu bukan fakta. Kamu boleh memperhatikannya tanpa harus memercayai semuanya.</blockquote>
<p>Kalau overthinking sudah bikin kamu sulit tidur berhari-hari atau mengganggu sekolah, coba ceritakan ke Guru BK. Ngobrol sering kali membantu mengurai benang kusut di kepala.</p>',
            ],
            [
                'overthinking', 'infographic', 'Infografis: 5-4-3-2-1, Teknik Grounding Saat Cemas', false, ['diri-sendiri'],
                'Teknik sederhana untuk membawa pikiranmu kembali ke saat ini ketika rasa cemas datang tiba-tiba.',
                '<p>Saat rasa cemas datang, coba teknik <strong>5-4-3-2-1</strong> ini. Kamu bisa melakukannya di mana saja, bahkan di dalam kelas.</p>
<ol><li><strong>5 hal yang bisa kamu lihat</strong>: papan tulis, sepatu, jendela, langit, tanganmu.</li><li><strong>4 hal yang bisa kamu sentuh</strong>: meja, kain bajumu, rambut, pulpen.</li><li><strong>3 hal yang bisa kamu dengar</strong>: kipas angin, suara teman, burung di luar.</li><li><strong>2 hal yang bisa kamu cium</strong>: bau buku, udara pagi.</li><li><strong>1 hal yang bisa kamu rasakan di lidah</strong>: sisa rasa minuman atau permen.</li></ol>
<p>Teknik ini membantu otak berpindah dari "mode bahaya" ke "mode sekarang". Lakukan pelan-pelan, sambil bernapas panjang.</p>',
            ],
            [
                'stres-ujian', 'article', 'Ujian Sudah Dekat dan Aku Panik. Harus Mulai dari Mana?', true, ['akademik'],
                'Panik menjelang ujian itu manusiawi. Ini cara menyusun rencana belajar yang realistis tanpa harus begadang.',
                '<p>Kalender menunjukkan ujian tinggal dua minggu lagi, sementara catatanmu masih berantakan. Jantung berdebar, perut mulas, dan rasanya ingin kabur saja. Tenang dulu. Panik itu tanda kamu peduli, bukan tanda kamu gagal.</p>
<h2>Langkah 1: Petakan dulu, jangan langsung belajar</h2>
<p>Tulis semua mata pelajaran yang akan diujikan, lalu beri tanda: mana yang sudah lumayan paham, mana yang masih bingung. Peta ini membantumu tahu harus fokus ke mana.</p>
<h2>Langkah 2: Pecah jadi potongan kecil</h2>
<p>"Belajar Matematika" itu terlalu besar. Ganti dengan "mengerjakan 10 soal persamaan kuadrat". Target kecil lebih mudah dimulai dan memberi rasa berhasil.</p>
<h2>Langkah 3: Pakai teknik Pomodoro</h2>
<p>Belajar 25 menit, istirahat 5 menit. Setelah empat putaran, istirahat lebih lama. Otak bekerja lebih baik dengan jeda.</p>
<h2>Langkah 4: Jaga tidur</h2>
<p>Begadang justru membuat ingatan lebih buruk. Saat tidur, otak menyimpan apa yang kamu pelajari seharian. Usahakan tidur 7-8 jam, terutama malam sebelum ujian.</p>
<blockquote>Nilaimu penting, tapi kesehatanmu jauh lebih penting.</blockquote>
<p>Kalau tekanan ujian terasa terlalu berat, misalnya karena tuntutan orang tua atau takut mengecewakan, ceritakan saja. Guru BK bisa membantu mencari jalan tengah.</p>',
            ],
            [
                'stres-ujian', 'infographic', 'Infografis: Checklist Malam Sebelum Ujian', false, ['akademik'],
                'Daftar singkat yang bisa kamu cek malam sebelum ujian supaya besok lebih tenang.',
                '<p>Simpan checklist ini dan centang satu per satu malam sebelum ujian.</p>
<ul><li>☐ Kartu ujian, alat tulis, dan penghapus sudah di tas.</li><li>☐ Ulang ringkasan materi, bukan mempelajari materi baru.</li><li>☐ Makan malam yang cukup dan minum air putih.</li><li>☐ Matikan HP 30 menit sebelum tidur.</li><li>☐ Tidur paling lambat jam 22.00.</li><li>☐ Ingatkan diri: "Aku sudah berusaha. Itu sudah cukup."</li></ul>',
            ],
            [
                'manajemen-waktu', 'article', 'Tugas Numpuk, Waktu Sempit: Cara Bikin Prioritas', false, ['akademik', 'masa-depan'],
                'Matriks sederhana untuk memilah tugas mana yang dikerjakan duluan, supaya kamu nggak kewalahan.',
                '<p>Tugas Biologi, presentasi Bahasa Inggris, latihan ekskul, belum lagi les. Kalau semuanya terasa mendesak, biasanya kita malah nggak mengerjakan apa-apa. Yuk, coba pilah dengan <strong>matriks prioritas</strong>.</p>
<h2>Empat kotak prioritas</h2>
<ul><li><strong>Penting & mendesak</strong>: kerjakan sekarang. Contoh: tugas yang dikumpulkan besok.</li><li><strong>Penting tapi tidak mendesak</strong>: jadwalkan. Contoh: belajar untuk ujian bulan depan.</li><li><strong>Tidak penting tapi mendesak</strong>: kerjakan cepat atau minta bantuan.</li><li><strong>Tidak penting & tidak mendesak</strong>: kurangi. Contoh: scroll media sosial berjam-jam.</li></ul>
<h2>Tips tambahan</h2>
<p>Tulis semua tugas di satu tempat. Beri tenggat waktu sendiri satu hari lebih cepat dari tenggat aslinya. Dan jangan lupa, istirahat juga termasuk hal penting yang perlu dijadwalkan.</p>',
            ],
            [
                'manajemen-waktu', 'article', 'Rebahan Itu Boleh, Asal Kamu yang Pegang Kendali', false, ['diri-sendiri'],
                'Istirahat bukan kemalasan. Belajar membedakan istirahat yang memulihkan dan menunda-nunda yang bikin cemas.',
                '<p>Ada dua jenis rebahan. Yang pertama bikin segar kembali. Yang kedua justru bikin makin cemas karena ada tugas yang terus menghantui. Bedanya ada di <strong>niat dan batas waktu</strong>.</p>
<h2>Istirahat yang memulihkan</h2>
<p>Kamu memutuskan untuk istirahat, tahu berapa lama, dan menikmatinya tanpa rasa bersalah. Misalnya: "Aku istirahat 30 menit, lalu lanjut mengerjakan laporan."</p>
<h2>Menunda yang melelahkan</h2>
<p>Kamu bilang "nanti aja" berulang kali, sambil terus merasa bersalah. Waktunya habis, tapi tubuh dan pikiran tidak beristirahat.</p>
<p>Coba pasang alarm untuk waktu istirahatmu. Saat berbunyi, mulai dengan tugas yang paling kecil dulu, cukup lima menit. Biasanya, bagian tersulit adalah memulai.</p>',
            ],
            [
                'pertemanan', 'article', 'Ditinggal Circle: Saat Pertemanan Berubah', true, ['pertemanan'],
                'Kehilangan teman dekat bisa sesakit putus cinta. Ini cara merawat dirimu saat pertemanan berubah.',
                '<p>Kemarin masih makan bareng di kantin, sekarang mereka punya grup chat baru tanpa kamu. Rasanya bingung, sedih, dan mungkin juga malu. Perasaan itu valid. Pertemanan yang berubah memang bisa sangat menyakitkan.</p>
<h2>Kenapa pertemanan bisa berubah?</h2>
<p>Kadang karena salah paham. Kadang karena minat yang mulai berbeda, pindah kelas, atau kesibukan baru. Tidak selalu ada yang salah, dan tidak selalu karena kamu.</p>
<h2>Yang bisa kamu lakukan</h2>
<ul><li><strong>Akui perasaanmu.</strong> Kamu boleh sedih. Nggak perlu pura-pura baik-baik saja.</li><li><strong>Kalau memungkinkan, ajak bicara.</strong> Gunakan kalimat "aku merasa…" daripada "kalian jahat".</li><li><strong>Buka pintu baru.</strong> Ikut ekskul atau kegiatan yang kamu suka. Teman baru sering datang dari minat yang sama.</li><li><strong>Jaga hubungan dengan dirimu sendiri.</strong> Lakukan hal yang membuatmu nyaman.</li></ul>
<p>Kalau kamu merasa dikucilkan secara sengaja atau diejek terus-menerus, itu bisa termasuk perundungan. Kamu berhak cerita dan mendapat perlindungan.</p>',
            ],
            [
                'pertemanan', 'article', 'Cara Bilang "Nggak" ke Teman Tanpa Rasa Bersalah', false, ['pertemanan', 'diri-sendiri'],
                'Menolak bukan berarti jahat. Belajar menyampaikan batasan dengan sopan dan tegas.',
                '<p>Teman minta contekan, ajak bolos, atau pinjam uang lagi padahal yang kemarin belum dikembalikan. Kamu ingin menolak, tapi takut dijauhi. Padahal, teman yang baik akan menghargai batasanmu.</p>
<h2>Rumus menolak dengan hangat</h2>
<p><strong>Akui + tolak + tawarkan alternatif.</strong> Contoh: "Aku ngerti kamu lagi buru-buru (akui). Tapi aku nggak bisa kasih jawaban ujianku (tolak). Gimana kalau nanti sore kita belajar bareng? (alternatif)".</p>
<h2>Ingat</h2>
<ul><li>Kamu nggak wajib menjelaskan panjang lebar.</li><li>Nada suara yang tenang lebih kuat daripada marah-marah.</li><li>Kalau teman memaksa terus, itu tanda batasanmu perlu dijaga lebih ketat.</li></ul>',
            ],
            [
                'keluarga', 'article', 'Saat Rumah Terasa Nggak Nyaman', false, ['keluarga'],
                'Konflik di rumah bisa sangat menguras energi. Ini beberapa cara menjaga dirimu tetap kuat.',
                '<p>Orang tua yang sering bertengkar, tuntutan yang terasa berat, atau merasa tidak didengar. Rumah seharusnya tempat pulang yang aman, tapi kadang justru terasa paling melelahkan.</p>
<h2>Kamu tidak bertanggung jawab atas masalah orang dewasa</h2>
<p>Kalau orang tuamu bertengkar, itu bukan salahmu. Kamu juga tidak harus menjadi penengah atau penyelesai masalah mereka.</p>
<h2>Cara merawat diri</h2>
<ul><li>Cari satu sudut aman di rumah: kamar, teras, atau bahkan earphone dengan musik yang menenangkan.</li><li>Punya satu orang dewasa yang bisa dipercaya: saudara, guru, atau Guru BK.</li><li>Tulis perasaanmu di jurnal. Fitur Jurnal Mood di Welcoming hanya bisa dilihat olehmu.</li></ul>
<h2>Kapan harus segera mencari bantuan?</h2>
<p>Jika kamu mengalami kekerasan fisik, ancaman, atau merasa tidak aman berada di rumah, segera hubungi Guru BK atau buka halaman <strong>Bantuan Darurat</strong>. Keselamatanmu nomor satu.</p>',
            ],
            [
                'keluarga', 'article', 'Ngobrol Sama Orang Tua Tanpa Berakhir Debat', false, ['keluarga', 'masa-depan'],
                'Tips memilih waktu, kata, dan cara menyampaikan pendapat supaya orang tua lebih mudah mendengar.',
                '<p>Kamu ingin masuk jurusan seni, tapi orang tua ingin kamu jadi dokter. Setiap kali dibahas, ujungnya debat. Komunikasi dengan orang tua memang butuh strategi.</p>
<h2>Pilih waktu yang tepat</h2>
<p>Hindari membahas hal serius saat orang tua baru pulang kerja atau sedang lelah. Coba saat makan malam santai atau akhir pekan.</p>
<h2>Mulai dari kesamaan</h2>
<p>"Aku tahu Ayah dan Ibu ingin aku punya masa depan yang baik. Aku juga ingin itu." Kalimat ini membuat mereka merasa didengar dulu.</p>
<h2>Bawa data, bukan cuma perasaan</h2>
<p>Cari informasi tentang pilihanmu: prospek kerja, kampus, atau contoh orang yang sukses di bidang itu. Orang tua cenderung lebih tenang saat melihat kamu sudah memikirkannya matang-matang.</p>
<p>Butuh latihan dulu sebelum bicara dengan orang tua? Guru BK bisa jadi teman simulasi.</p>',
            ],
            [
                'percaya-diri', 'article', 'Membandingkan Diri di Media Sosial: Berhenti Sebentar, Yuk', true, ['diri-sendiri'],
                'Feed orang lain hanya potongan terbaik hidup mereka. Ini cara mengurangi kebiasaan membandingkan diri.',
                '<p>Temanmu liburan ke luar negeri, yang lain juara olimpiade, yang satu lagi glow up banget. Sementara kamu masih di kamar dengan tugas menumpuk. Rasanya hidupmu jauh tertinggal.</p>
<h2>Ingat: kamu membandingkan "belakang panggung"-mu dengan "panggung" orang lain</h2>
<p>Media sosial menampilkan momen terbaik. Kamu tidak melihat kegagalan, rasa malas, atau hari-hari buruk mereka.</p>
<h2>Langkah kecil</h2>
<ul><li>Bisukan akun yang membuatmu merasa buruk tentang diri sendiri.</li><li>Batasi waktu scroll, misalnya 30 menit sehari.</li><li>Tulis tiga hal yang kamu syukuri tentang dirimu setiap malam.</li><li>Bandingkan dirimu hari ini dengan dirimu setahun lalu, bukan dengan orang lain.</li></ul>
<blockquote>Kamu tidak terlambat. Kamu sedang berjalan di jalurmu sendiri.</blockquote>',
            ],
            [
                'percaya-diri', 'article', 'Takut Salah Saat Presentasi? Kamu Bisa Melatihnya', false, ['akademik', 'diri-sendiri'],
                'Grogi di depan kelas itu umum. Latihan kecil ini membantu kamu tampil lebih tenang.',
                '<p>Tangan dingin, suara bergetar, dan pikiran mendadak kosong. Grogi saat presentasi dialami hampir semua orang, termasuk guru-gurumu dulu.</p>
<h2>Sebelum presentasi</h2>
<ul><li>Latihan di depan cermin atau rekam suaramu.</li><li>Hafalkan kalimat pembuka saja. Sisanya cukup poin-poin.</li><li>Bayangkan presentasi berjalan lancar.</li></ul>
<h2>Saat presentasi</h2>
<ul><li>Tarik napas panjang sebelum mulai bicara.</li><li>Cari satu-dua wajah ramah di kelas dan tatap mereka.</li><li>Kalau lupa, berhenti sejenak dan lihat catatan. Jeda itu normal.</li></ul>
<p>Percaya diri bukan berarti tidak takut. Percaya diri adalah tetap melangkah meskipun takut.</p>',
            ],
        ];

        foreach ($articles as $i => [$cat, $type, $title, $featured, $topics, $excerpt, $body]) {
            $a = Article::updateOrCreate(['slug' => Str::slug($title)], [
                'school_id' => School::current()->id,
                'author_id' => $authors[$i % count($authors)],
                'article_category_id' => ArticleCategory::where('slug', $cat)->value('id'),
                'type' => $type,
                'title' => $title,
                'excerpt' => $excerpt,
                'body' => $body,
                'read_minutes' => Article::readMinutesFor($body),
                'status' => 'published',
                'is_featured' => $featured,
                'published_at' => now()->subDays(60 - $i * 4),
                'view_count' => 20 + ($i * 37) % 150,
            ]);
            $a->topics()->sync(Topic::whereIn('slug', $topics)->pluck('id'));
        }
    }
}
