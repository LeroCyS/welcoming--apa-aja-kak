{{-- Isi halaman Bantuan Darurat, dipakai di tampilan publik maupun tampilan murid. --}}
<header class="flex flex-col gap-4 items-start">
    <span class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full bg-danger-tint text-danger-ink text-sm font-extrabold"><x-icon name="phone" size="16" />Bantuan Darurat{{ auth()->check() ? '' : ' · tanpa login' }}</span>
    <h1 class="text-[clamp(36px,4.6vw,60px)] leading-[1.06]">Kamu tidak sendirian. Kami di sini.</h1>
    <p class="text-lg leading-[1.65] text-[#5A4E47] max-w-[680px]">Tarik napas pelan-pelan. Kalau kamu atau temanmu dalam bahaya, hubungi layanan di bawah sekarang.</p>
</header>

<section aria-labelledby="h-hotline" class="flex flex-col gap-4">
    <h2 id="h-hotline" class="!font-sans text-lg font-extrabold">Kontak yang bisa dihubungi</h2>
    @include('partials.emergency-contacts', ['contacts' => $contacts])
    <span class="text-[13px] text-ink-soft">Daftar kontak ini dikelola oleh admin {{ $school->name }}.</span>
</section>

<section class="relative overflow-hidden bg-sage-deep text-white rounded-panel p-7 lg:p-9 flex items-center justify-between gap-5 flex-wrap shadow-[0_24px_48px_-18px_rgba(47,61,40,0.5)]">
    <div class="relative flex flex-col gap-1.5 max-w-[560px]">
        <span class="font-display text-[26px]">Mau bicara dengan Guru BK sekarang?</span>
        <span class="text-[15px] leading-relaxed text-[#EEF1EA]">Pesanmu langsung ke antrean teratas. Kamu tetap anonim.</span>
    </div>
    @auth
        @if(auth()->user()->isStudent())
            <form method="POST" action="{{ route('student.conversations.emergency') }}" class="relative">@csrf
                <button class="relative btn btn-lg !h-[54px] bg-white !text-sage-ink shadow-[0_8px_20px_-6px_rgba(0,0,0,.3)] hover:bg-cream-light">Hubungi Guru BK Sekarang</button>
            </form>
        @else
            <a href="{{ auth()->user()->homeRoute() }}" class="relative btn btn-lg !h-[54px] bg-white !text-sage-ink shadow-[0_8px_20px_-6px_rgba(0,0,0,.3)] hover:bg-cream-light">Kembali ke dashboard</a>
        @endif
    @else
        <a href="{{ route('login', ['intent' => 'darurat']) }}" class="relative btn btn-lg !h-[54px] bg-white !text-sage-ink shadow-[0_8px_20px_-6px_rgba(0,0,0,.3)] hover:bg-cream-light">Hubungi Guru BK Sekarang</a>
    @endauth
</section>

<section aria-labelledby="h-aman" class="flex flex-col gap-4">
    <h2 id="h-aman" class="text-[28px]">Kalau kamu sedang tidak aman sekarang</h2>
    <ol class="grid grid-cols-[repeat(auto-fit,minmax(min(260px,100%),1fr))] gap-4">
        @foreach([
            ['Pindah ke tempat yang lebih aman', 'Menjauh dari orang atau benda yang bisa menyakitimu. Cari ruang terbuka atau dekat orang dewasa yang kamu percaya.'],
            ['Hubungi satu orang', 'Telepon salah satu nomor di atas, atau kabari orang tua, saudara, guru, atau teman dekat.'],
            ['Bernapas pelan bersama kami', 'Tarik napas 4 hitungan, tahan 4, buang 6. Ulangi sampai badan terasa sedikit lebih tenang.'],
        ] as $i => [$t, $d])
            <li class="bg-white border border-line-soft rounded-card-l p-[22px] grid grid-rows-subgrid row-span-3 gap-y-2.5 shadow-soft"><span class="tile w-12 h-12 bg-danger-tint font-display text-[26px] text-danger leading-none">{{ $i + 1 }}</span><span class="font-extrabold text-[17px]">{{ $t }}</span><span class="text-[15px] leading-relaxed text-ink-soft">{{ $d }}</span></li>
        @endforeach
    </ol>
</section>
