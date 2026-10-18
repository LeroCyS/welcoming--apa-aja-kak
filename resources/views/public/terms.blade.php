@extends('layouts.public')
@section('title', 'Syarat & Ketentuan')
@section('description', 'Aturan memakai Welcoming untuk murid dan Guru BK.')
@section('content')
<x-legal-page heading="Syarat & Ketentuan" updated="17 Oktober 2026">
    <p>Dengan mendaftar atau memakai Welcoming, kamu menyetujui syarat berikut. Bacalah bersama <a href="{{ route('privacy') }}">Kebijakan Privasi</a> yang menjelaskan cara kami menjaga datamu.</p>

    <h2>1. Tentang Welcoming</h2>
    <p>Welcoming menghubungkan murid dengan Guru BK di sekolahnya untuk bercerita, check-in mood, membaca artikel self-help, dan menjadwalkan sesi tatap muka. Welcoming <strong>bukan layanan darurat</strong> dan tidak menggantikan bantuan medis atau psikolog profesional.</p>
    <blockquote>Kalau kamu atau orang lain dalam bahaya, jangan menunggu balasan chat. Buka Bantuan Darurat dan hubungi nomor yang tersedia.</blockquote>

    <h2>2. Akun</h2>
    <ul>
        <li>Murid mendaftar sendiri dengan email dan password, atau dengan Google, lalu memilih sekolahnya. Sekolah tidak bisa diganti sendiri; hubungi admin sekolah bila salah pilih.</li>
        <li>Akun Guru BK dan admin dibuat oleh sekolah.</li>
        <li>Jaga kerahasiaan password dan Kunci PIN. Kamu bertanggung jawab atas aktivitas di akunmu.</li>
        <li>Satu akun untuk satu orang. Jangan memakai akun orang lain atau berpura-pura menjadi orang lain.</li>
    </ul>

    <h2>3. Aturan untuk murid</h2>
    <ul>
        <li>Bercerita dengan jujur dan saling menghormati.</li>
        <li>Jangan mengirim konten yang melecehkan, mengancam, mengandung kekerasan seksual, SARA, atau melanggar hukum.</li>
        <li>Jangan membagikan data pribadi orang lain, termasuk foto, tanpa izin.</li>
        <li>Jangan menyalahgunakan tombol darurat atau membuat cerita palsu berulang kali.</li>
        <li>Tangkapan layar percakapan sebaiknya tidak disebarkan, demi menjaga privasimu sendiri dan Guru BK.</li>
    </ul>

    <h2>4. Aturan untuk Guru BK</h2>
    <ul>
        <li>Menjaga kerahasiaan cerita murid sesuai kode etik bimbingan dan konseling.</li>
        <li>Tidak berusaha mencari tahu identitas murid yang memilih anonim.</li>
        <li>Memakai ringkasan AI hanya sebagai bantuan, bukan pengganti penilaian profesional.</li>
        <li>Menindaklanjuti tanda bahaya sesuai prosedur sekolah.</li>
    </ul>

    <h2>5. Anonimitas dan batasnya</h2>
    <p>Kamu bisa bercerita tanpa nama, dan identitasmu baru terbuka bila kamu sendiri yang memilih. Namun, bila ada risiko serius terhadap keselamatanmu atau orang lain, Guru BK dapat mengambil langkah perlindungan sesuai aturan sekolah dan hukum yang berlaku.</p>

    <h2>6. Konten yang kamu kirim</h2>
    <p>Cerita, pesan, voice note, dan foto tetap milikmu. Kamu memberi izin kepada Welcoming untuk menyimpan dan menampilkannya kepada Guru BK yang berwenang agar layanan bisa berjalan. Konten yang melanggar aturan dapat disembunyikan, dan akun yang melanggar berulang kali dapat dibatasi oleh sekolah.</p>

    <h2>7. Ketersediaan layanan</h2>
    <p>Kami berusaha agar Welcoming selalu bisa dipakai, tetapi bisa ada gangguan atau perawatan. Guru BK membalas sesuai jam layanannya, sehingga balasan tidak selalu langsung.</p>

    <h2>8. Batas tanggung jawab</h2>
    <p>Welcoming adalah sarana komunikasi. Saran dan tindak lanjut dari Guru BK menjadi tanggung jawab sekolah dan Guru BK yang bersangkutan. Welcoming tidak bertanggung jawab atas kerugian akibat penyalahgunaan akun atau keputusan yang diambil di luar aplikasi.</p>

    <h2>9. Perubahan syarat</h2>
    <p>Syarat ini bisa diperbarui sewaktu-waktu. Dengan tetap memakai Welcoming setelah perubahan, kamu dianggap menyetujui syarat terbaru.</p>

    <h2>10. Hukum yang berlaku</h2>
    <p>Syarat ini tunduk pada hukum Republik Indonesia, termasuk Undang-Undang Pelindungan Data Pribadi.</p>

    <h2>11. Hubungi kami</h2>
    <p>Pertanyaan tentang syarat ini bisa disampaikan ke Guru BK atau admin sekolahmu.</p>
</x-legal-page>
@endsection
