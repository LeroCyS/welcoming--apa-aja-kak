@extends('layouts.public')
@section('title', 'Kebijakan Privasi')
@section('description', 'Data apa yang dikumpulkan Welcoming, untuk apa dipakai, siapa yang bisa melihatnya, dan hak kamu atas datamu.')
@section('content')
<x-legal-page heading="Kebijakan Privasi" updated="17 Oktober 2026">
    <p>Welcoming adalah ruang cerita antara murid dan Guru BK di sekolahnya. Karena yang diceritakan di sini sering kali hal pribadi, kami berusaha mengumpulkan data sesedikit mungkin dan menjaga agar ceritamu hanya dibaca oleh Guru BK yang kamu pilih.</p>
    <blockquote>Singkatnya: Guru BK dan admin sekolah tidak bisa melihat email atau akun Google kamu. Admin sekolah tidak bisa membaca percakapan. Namamu baru terlihat oleh Guru BK kalau kamu sendiri yang memilih untuk membukanya.</blockquote>

    <h2>1. Data yang kami kumpulkan</h2>
    <h3>Saat kamu mendaftar</h3>
    <ul>
        <li><strong>Email dan password</strong> bila kamu mendaftar dengan email. Email diverifikasi dengan kode sekali pakai (OTP), dan password disimpan dalam bentuk hash, bukan teks asli.</li>
        <li>Bila kamu masuk dengan Google, kami hanya menerima <strong>penanda unik akun Google</strong>, yang langsung disimpan dalam bentuk terenkripsi. Email, nama, dan foto Google tidak kami terima.</li>
        <li><strong>Jenjang dan sekolah</strong> yang kamu pilih saat onboarding.</li>
    </ul>
    <h3>Yang kamu isi sendiri (opsional)</h3>
    <ul>
        <li>Nama, kelas, dan tingkat di Profil. Semua boleh dikosongkan.</li>
        <li>Kunci PIN (disimpan dalam bentuk hash) dan pengaturan notifikasi.</li>
    </ul>
    <h3>Saat kamu memakai Welcoming</h3>
    <ul>
        <li><strong>Isi cerita dan chat</strong>: pesan teks, voice note, foto, link lagu, dan topik yang kamu pilih.</li>
        <li><strong>Check-in mood</strong> harian, balasan follow-up, dan jadwal sesi tatap muka.</li>
        <li><strong>Catatan Guru BK</strong> tentang kasus yang ia tangani.</li>
        <li><strong>Data teknis</strong> yang dibutuhkan agar aplikasi berjalan: sesi login, langganan notifikasi perangkat, dan log aktivitas penting (misalnya serah terima kasus atau pembukaan identitas) untuk keperluan keamanan.</li>
    </ul>
    <p>Kami tidak meminta NISN, nomor telepon, alamat, atau lokasimu.</p>

    <h2>2. Cara kami menjaga anonimitas</h2>
    <ul>
        <li>Cerita anonim tidak menyimpan ID akunmu secara langsung. Yang tersimpan hanya penanda terenkripsi, sehingga Guru BK hanya melihat alias seperti "Kucing Oranye #482".</li>
        <li>Isi pesan dan catatan dienkripsi di database.</li>
        <li>Voice note disamarkan suaranya (nada diubah) di perangkatmu sebelum diunggah. Gaya bicara tetap mungkin dikenali, jadi pertimbangkan hal ini sebelum mengirim.</li>
        <li>Foto diproses ulang sebelum disimpan sehingga data EXIF, termasuk lokasi, terhapus.</li>
        <li>Notifikasi di perangkat hanya bertuliskan "Ada kabar baru untukmu" tanpa nama guru atau isi pesan, karena bisa terlihat di layar kunci.</li>
        <li>Fitur Keluar Cepat dan Kunci PIN membantu kalau ada orang lain di dekatmu.</li>
    </ul>

    <h2>3. Siapa yang bisa melihat datamu</h2>
    <ul>
        <li><strong>Guru BK yang kamu pilih</strong> bisa membaca cerita dan chat yang kamu kirim kepadanya. Ia melihat alias, bukan nama, kecuali kamu membuka identitas.</li>
        <li><strong>Guru BK penerima serah terima</strong>: bila kasus dipindahkan ke Guru BK lain, guru baru bisa membaca percakapan dan catatan serah terima. Catatan privat guru lama tidak ikut berpindah, dan terhadap guru baru kamu kembali anonim.</li>
        <li><strong>Admin sekolah</strong> hanya melihat "Murid #nomor" beserta status akun. Admin tidak bisa membaca percakapan, tidak melihat email, dan tidak bisa mereset password murid.</li>
        <li><strong>Statistik sekolah</strong> hanya ditampilkan dalam bentuk gabungan dan disembunyikan bila jumlahnya terlalu sedikit sehingga bisa menunjuk ke murid tertentu.</li>
    </ul>
    <p>Kami tidak menjual datamu dan tidak memakainya untuk iklan.</p>

    <h2>4. Penggunaan AI</h2>
    <p>Bila sekolah mengaktifkannya, Welcoming bisa memakai layanan AI pihak ketiga untuk membuat ringkasan percakapan bagi Guru BK dan mengubah voice note menjadi teks. Sebelum dikirim, nama, kelas, nomor telepon, email, dan alias diganti menjadi <em>[disamarkan]</em>. Fitur ini hanya aktif setelah sekolah menyetujuinya.</p>

    <h2>5. Layanan pihak ketiga</h2>
    <ul>
        <li><strong>Google</strong>, bila kamu memilih masuk dengan Google.</li>
        <li><strong>YouTube dan Spotify</strong>, bila kamu atau Guru BK membagikan link lagu. Pemutar baru dimuat saat tombol Putar ditekan, dan YouTube memakai mode tanpa cookie.</li>
        <li><strong>Layanan notifikasi browser</strong> untuk mengirim notifikasi perangkat bila kamu mengaktifkannya.</li>
        <li><strong>Penyedia AI</strong> seperti dijelaskan di bagian 4.</li>
    </ul>

    <h2>6. Kapan kerahasiaan bisa dibuka</h2>
    <p>Guru BK menjaga ceritamu, tetapi ada keadaan ketika keselamatan lebih penting. Bila ada tanda bahaya serius bagi dirimu atau orang lain, misalnya keinginan menyakiti diri, kekerasan, atau pelecehan, Guru BK dapat mengambil langkah perlindungan sesuai aturan sekolah dan hukum yang berlaku. Sistem juga bisa menandai kata kunci sensitif agar ceritamu lebih cepat ditanggapi.</p>

    <h2>7. Berapa lama data disimpan</h2>
    <ul>
        <li>Rekaman voice note dihapus otomatis setelah {{ config('welcoming.voice_retention_days') }} hari.</li>
        <li>Data lain disimpan selama akunmu aktif dan dibutuhkan untuk layanan bimbingan di sekolahmu.</li>
    </ul>

    <h2>8. Hak kamu</h2>
    <ul>
        <li>Mengubah atau mengosongkan nama dan kelas kapan saja di Profil.</li>
        <li>Memilih tetap anonim, atau membuka identitas hanya kepada Guru BK tertentu.</li>
        <li>Mematikan notifikasi atau mencabut langganan notifikasi perangkat dengan Keluar Akun.</li>
        <li>Meminta penghapusan akun atau salinan datamu melalui Guru BK atau admin sekolah.</li>
    </ul>

    <h2>9. Untuk murid di bawah umur</h2>
    <p>Sebagian besar pengguna Welcoming masih di bawah 18 tahun. Welcoming dipakai di bawah pengelolaan sekolah sebagai bagian dari layanan bimbingan dan konseling. Kalau kamu ragu, ajak orang tua atau wali membaca halaman ini bersama.</p>

    <h2>10. Perubahan kebijakan</h2>
    <p>Kebijakan ini bisa diperbarui bila ada fitur baru atau perubahan aturan. Tanggal di atas akan diperbarui, dan perubahan penting akan diberitahukan di aplikasi.</p>

    <h2>11. Hubungi kami</h2>
    <p>Pertanyaan tentang privasi bisa disampaikan ke Guru BK atau admin sekolahmu. Dalam keadaan darurat, buka halaman <a href="{{ route('emergency') }}">Bantuan Darurat</a>.</p>
</x-legal-page>
@endsection
