@extends('layouts.public')
@section('full-title', 'Welcoming · Ceritamu didengar, bukan dihakimi')
@section('description', 'Ruang cerita anonim untuk murid dan Guru BK sekolahmu. Gratis, aman, dan tanpa dihakimi.')
@section('body-class', 'bg-none')
@section('content')
{{-- --nav-h = tinggi navbar (tombol 44px + padding 2 x 14px). Tiap section minimal setinggi layar dikurangi navbar. --}}
<div class="min-h-screen overflow-x-clip [--nav-h:80px]">
    {{-- Navbar: menempel di atas saat awal, lalu berubah jadi pil kaca melayang saat halaman di-scroll. Link section menyala sesuai bagian yang sedang dilihat. --}}
    @php $navLinks = ['kenapa' => 'Kenapa Welcoming', 'keamanan' => 'Privasi & Keamanan', 'fitur' => 'Fitur']; @endphp
    <div class="sticky top-0 z-40 container-wl pt-3 no-print transition-[padding] duration-500 ease-[cubic-bezier(.22,1,.36,1)]"
         x-data="{ scrolled: window.scrollY > 24, active: '', menu: false }"
         x-init="scrolled = window.scrollY > 24; const io = new IntersectionObserver((entries) => entries.forEach((e) => { if (e.isIntersecting) active = e.target.id }), { rootMargin: '-45% 0px -50% 0px' }); ['top', ...@js(array_keys($navLinks))].forEach((id) => { const el = document.getElementById(id); if (el) io.observe(el) })"
         x-on:scroll.window.passive="scrolled = window.scrollY > 24" x-on:keydown.escape.window="menu = false">
        <nav aria-label="Navigasi utama" class="mx-auto w-full max-w-[1760px] flex items-center justify-between gap-4 rounded-[28px] border border-transparent py-3 px-0 animate-[nav-in_.6s_cubic-bezier(.22,1,.36,1)_both] transition-[max-width,padding,background-color,border-color,box-shadow] duration-500 ease-[cubic-bezier(.22,1,.36,1)]" :class="scrolled && 'glass !border-line/70 shadow-lift !max-w-[1240px] !py-2 !px-3 sm:!px-4'">
            {{-- Logo berbentuk huruf W, jadi teksnya cukup "elcoming" yang menempel ke logo. --}}
            <a href="#top" aria-label="Welcoming" class="flex items-center no-underline text-ink flex-none">
                <img src="{{ asset('images/welcoming-mark.png') }}" alt="" class="h-auto transition-[width] duration-500" :class="scrolled ? 'w-11' : 'w-14'">
                <span class="font-display text-2xl -ml-0.5" aria-hidden="true">elcoming</span>
            </a>

            <div class="hidden lg:flex items-center gap-1">
                @foreach($navLinks as $id => $label)
                    <a href="#{{ $id }}" class="relative inline-flex items-center h-11 px-4 rounded-full no-underline font-bold text-[15px] transition-colors" :class="active === '{{ $id }}' ? 'bg-terracotta-tint text-terracotta-ink !font-extrabold' : 'text-ink hover:bg-ink/5'" :aria-current="active === '{{ $id }}' ? 'location' : null">{{ $label }}</a>
                @endforeach
            </div>

            <div class="flex items-center gap-1.5 sm:gap-2 flex-none">
                <a href="{{ route('emergency') }}" aria-label="Bantuan darurat" class="inline-flex items-center justify-center gap-1.5 h-11 w-11 sm:w-auto sm:px-4 rounded-full text-danger no-underline font-extrabold text-[15px] transition hover:bg-danger-tint"><x-icon name="phone" size="16" /><span class="hidden sm:inline">Darurat</span></a>
                @auth
                    <a href="{{ auth()->user()->homeRoute() }}" class="inline-flex items-center gap-2 h-11 px-5 rounded-full bg-ink text-cream no-underline font-extrabold text-[15px] shadow-[0_6px_16px_-4px_rgba(62,53,48,.45)] transition hover:bg-[#2E2723]">Buka Welcoming<x-icon name="arrow-right" size="16" /></a>
                @else
                    <a href="{{ route('login') }}" class="hidden sm:inline-flex items-center h-11 px-5 rounded-full border-[1.5px] border-ink/80 text-ink no-underline font-extrabold text-[15px] transition hover:bg-ink hover:text-cream">Masuk</a>
                    <a href="{{ route('register') }}" class="hidden sm:inline-flex items-center gap-2 h-11 px-5 rounded-full bg-terracotta-deep text-white no-underline font-extrabold text-[15px] shadow-[0_6px_16px_-4px_rgba(181,82,63,.5)] transition hover:bg-terracotta-ink">Daftar Gratis<x-icon name="arrow-right" size="16" /></a>
                @endauth
                {{-- Menu ☰ (layar kecil): link section + masuk/daftar --}}
                <div class="relative lg:hidden" x-on:click.outside="menu = false">
                    <button type="button" x-on:click="menu = !menu" :aria-expanded="menu" aria-controls="landing-menu" aria-label="Buka menu" class="head-btn" :class="menu && '!bg-cream-light'">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h10"/></svg>
                    </button>
                    <div id="landing-menu" x-show="menu" x-cloak x-transition.origin.top.right class="absolute right-0 top-[calc(100%+10px)] w-[min(280px,calc(100vw-32px))] bg-white border border-line rounded-[22px] shadow-modal p-2 flex flex-col">
                        @foreach($navLinks as $id => $label)
                            <a href="#{{ $id }}" x-on:click="menu = false" class="flex items-center h-11 px-3 rounded-[14px] no-underline font-bold text-[15px] transition-colors" :class="active === '{{ $id }}' ? 'bg-terracotta-tint text-terracotta-ink !font-extrabold' : 'text-ink hover:bg-cream-light'">{{ $label }}</a>
                        @endforeach
                        @guest
                            <span class="my-1.5 mx-3 h-px bg-line-soft"></span>
                            <div class="grid grid-cols-2 gap-2 p-1 sm:hidden">
                                <a href="{{ route('login') }}" class="inline-flex items-center justify-center h-11 rounded-full border-[1.5px] border-ink/80 text-ink no-underline font-extrabold text-sm">Masuk</a>
                                <a href="{{ route('register') }}" class="inline-flex items-center justify-center h-11 rounded-full bg-terracotta-deep text-white no-underline font-extrabold text-sm">Daftar</a>
                            </div>
                        @endguest
                    </div>
                </div>
            </div>
        </nav>
    </div>

    {{-- Hero: tanpa kartu, judul dengan satu kata tulisan tangan, dan kolase potongan kertas berwarna berisi ilustrasi murid & Guru BK, stiker bergaris tebal, serta panah doodle. --}}
    @php
        $ink = '#3E3530';
        // [warna kulit, warna rambut, gaya rambut, warna baju]
        $people = [
            'guru' => ['#C98E6B', '#2E2723', 'short', '#F5F1E4'],
            'murid' => ['#F1CBA8', '#3E3530', 'long', '#FBEDE9'],
            'teman' => ['#E2B08A', '#4A3A30', 'curly', '#E4EADF'],
        ];
    @endphp
    <header id="top" class="section-screen container-wl scroll-mt-0 relative isolate flex flex-col gap-6 lg:gap-8 !pb-6">
        {{-- Latar kolase: sobekan kertas krem berpola titik di belakang ilustrasi (desktop). --}}
        <div aria-hidden="true" class="pointer-events-none absolute inset-0 -z-10 overflow-hidden">
            <span class="hidden lg:block absolute right-[1%] top-[7%] w-[46%] h-[80%] bg-cream-deep/80 rotate-[2deg] animate-[fade-in_.8s_ease-out_both]" style="clip-path: polygon(2% 4%, 9% 1%, 17% 3%, 26% 0%, 35% 2%, 46% 0%, 55% 3%, 64% 1%, 74% 3%, 83% 0%, 92% 2%, 99% 5%, 97% 16%, 100% 28%, 98% 41%, 100% 53%, 97% 66%, 99% 78%, 97% 90%, 99% 98%, 88% 96%, 77% 99%, 66% 97%, 54% 100%, 43% 97%, 31% 99%, 20% 96%, 9% 99%, 1% 96%, 3% 84%, 0% 71%, 2% 58%, 0% 45%, 3% 32%, 0% 19%)"></span>
            <span class="hidden lg:block absolute right-[4%] top-[12%] w-[42%] h-[72%] [background-image:radial-gradient(#CFC4AA_1.3px,transparent_1.3px)] [background-size:24px_24px] opacity-70 rotate-[2deg] [mask-image:radial-gradient(closest-side,#000_55%,transparent)]"></span>
        </div>
        <div class="flex-1 grid content-center lg:grid-cols-[minmax(0,1.12fr)_minmax(0,1fr)] gap-8 lg:gap-10 items-center pt-6 lg:pt-12">
            <div class="@container flex flex-col gap-7 lg:gap-8 relative z-10">
                {{-- Ukuran huruf mengikuti lebar kolom (cqi) supaya selalu tepat dua baris di layar mana pun. --}}
                <h1 class="text-[clamp(30px,9cqi,104px)] leading-[1.02] tracking-[-0.025em] whitespace-nowrap">
                    <span class="hero-mask"><span class="inline-block animate-[reveal-up_.9s_cubic-bezier(.22,1,.36,1)_.1s_both]">Ceritamu <span class="font-hand text-terracotta-deep text-[1.22em] leading-none tracking-normal">didengar,</span></span></span><br><span class="hero-mask"><span class="inline-block animate-[reveal-up_.9s_cubic-bezier(.22,1,.36,1)_.25s_both]">bukan dihakimi.</span></span>
                </h1>
                <p class="text-[clamp(18px,1.55vw,24px)] leading-[1.6] text-ink-soft max-w-[620px] animate-[rise_.8s_cubic-bezier(.22,1,.36,1)_.5s_both]">Curhat ke Guru BK secara anonim, soal keluarga, teman, asmara, nilai, atau hal yang belum bisa kamu beri nama. Identitasmu baru terlihat kalau kamu sendiri yang membukanya.</p>
                {{-- Barisan kecil Guru BK + catatan privasi --}}
                <div class="flex items-center gap-4 flex-wrap animate-[rise_.8s_cubic-bezier(.22,1,.36,1)_.6s_both]">
                    <span class="flex -space-x-3" aria-hidden="true">
                        @foreach([['#E4EADF', '#46593B', 'R'], ['#F3E6D0', '#6F5229', 'A'], ['#F6DDD7', '#8E3E2F', 'D']] as [$bg, $fg, $ch])
                            <span class="w-12 h-12 lg:w-14 lg:h-14 rounded-full border-[3px] border-cream flex items-center justify-center font-display text-xl lg:text-2xl shadow-xs" style="background: {{ $bg }}; color: {{ $fg }}">{{ $ch }}</span>
                        @endforeach
                        <span class="w-12 h-12 lg:w-14 lg:h-14 rounded-full border-[3px] border-cream bg-ink text-cream flex items-center justify-center shadow-xs"><x-icon name="chat-heart" size="24" /></span>
                    </span>
                    <span class="flex flex-col gap-0.5 leading-tight">
                        <span class="text-[16px] lg:text-[18px] font-extrabold">Guru BK sekolahmu siap mendengarkan</span>
                        <span class="text-[14px] lg:text-[15px] font-bold text-ink-soft inline-flex items-center gap-1.5"><span class="relative flex w-2 h-2"><span class="absolute inset-0 rounded-full bg-sage-deep animate-ping opacity-60"></span><span class="relative w-2 h-2 rounded-full bg-sage-deep"></span></span>Namamu tetap tersembunyi</span>
                    </span>
                </div>
                <div class="flex gap-3 flex-wrap animate-[rise_.8s_cubic-bezier(.22,1,.36,1)_.75s_both]">
                    <a href="{{ route('register') }}" class="group btn btn-primary btn-lg !h-14 lg:!h-16 lg:!px-8 lg:!text-[18px] relative overflow-hidden"><span class="absolute inset-y-0 -left-1/2 w-1/3 skew-x-[-20deg] bg-white/25 animate-[shine_4.5s_ease-in-out_2s_infinite]" aria-hidden="true"></span>Daftar Gratis &amp; Anonim<x-icon name="arrow-right" size="18" class="transition-transform duration-200 group-hover:translate-x-1" /></a>
                    <a href="{{ route('login', ['peran' => 'student']) }}" class="btn btn-outline btn-lg !h-14 lg:!h-16 lg:!px-8 lg:!text-[18px]">Masuk sebagai Murid</a>
                </div>
            </div>

            {{-- Kolase (dekoratif). Urutan animasi: kertas muncul → panah tergambar satu per satu (murid → guru → teman → murid) → stiker muncul. Setelah itu hanya gerak halus. --}}
            <div aria-hidden="true" class="relative h-[clamp(360px,64svh,720px)] [--mx:0] [--my:0]"
                 x-data
                 x-on:mousemove="if (!matchMedia('(prefers-reduced-motion: reduce)').matches) { const r = $el.getBoundingClientRect(); $el.style.setProperty('--mx', ((event.clientX - r.left) / r.width - .5).toFixed(3)); $el.style.setProperty('--my', ((event.clientY - r.top) / r.height - .5).toFixed(3)); }"
                 x-on:mouseleave="$el.style.setProperty('--mx', 0); $el.style.setProperty('--my', 0)">

                {{-- Potongan kertas + ilustrasi orang --}}
                @foreach([
                    ['guru', 'left-[30%] top-[2%] w-[52%] h-[34%]', 'bg-sage', '-3deg', 'polygon(3% 10%, 97% 0%, 100% 86%, 64% 90%, 56% 100%, 52% 90%, 0% 96%)', '.2s', -8],
                    ['murid', 'left-[4%] top-[40%] w-[38%] h-[48%]', 'bg-terracotta', '2.5deg', 'polygon(8% 0%, 100% 5%, 95% 100%, 0% 93%)', '.35s', 10],
                    ['teman', 'left-[66%] top-[62%] w-[30%] h-[33%]', 'bg-sand', '-2deg', 'polygon(0% 6%, 94% 0%, 100% 95%, 5% 100%)', '.5s', -10],
                ] as [$who, $pos, $bg, $rot, $clip, $delay, $depth])
                    @php [$skin, $hair, $style, $shirt] = $people[$who]; @endphp
                    <div class="absolute {{ $pos }} transition-transform duration-700 ease-out" style="transform: translate(calc(var(--mx) * {{ $depth }}px), calc(var(--my) * {{ $depth }}px))">
                        <div class="w-full h-full" style="animation: paper-in .7s cubic-bezier(.22,1,.36,1) {{ $delay }} both">
                            <div class="relative w-full h-full {{ $bg }} overflow-hidden" style="clip-path: {{ $clip }}; transform: rotate({{ $rot }})">
                                <div class="absolute inset-0 opacity-25" style="background: repeating-conic-gradient(from 0deg at 50% 110%, rgba(255,255,255,.55) 0deg 6deg, transparent 6deg 18deg)"></div>
                                <svg class="absolute left-1/2 -translate-x-1/2 bottom-0 h-[88%] w-auto" viewBox="0 0 120 120" fill="none" stroke="{{ $ink }}" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
                                    @if($style === 'long')<path d="M30 54c-2-28 12-40 30-40s32 12 30 40l3 40c-7 5-16 5-21 0V56H48v38c-5 5-14 5-21 0z" fill="{{ $hair }}"/>@endif
                                    <path d="M12 122c4-28 23-40 48-40s44 12 48 40z" fill="{{ $shirt }}"/>
                                    <path d="M50 70v14c0 6 20 6 20 0V70" fill="{{ $skin }}"/>
                                    <ellipse cx="60" cy="50" rx="24" ry="27" fill="{{ $skin }}"/>
                                    @if($style === 'short')<path d="M35 46c-1-19 11-30 26-30 14 0 26 10 25 28-7-7-17-9-29-7-9 1-16 4-22 9z" fill="{{ $hair }}"/>@endif
                                    @if($style === 'long')<path d="M36 46c3-15 13-22 24-22s21 7 24 22c-11-2-19-7-24-13-5 6-13 11-24 13z" fill="{{ $hair }}"/>@endif
                                    @if($style === 'curly')<path d="M34 48c-6-6-4-16 3-19-1-8 7-14 14-11 4-6 14-6 18 0 7-3 15 3 14 11 7 3 9 13 3 19-4-7-12-10-19-8-6-4-14-4-20 0-6-2-11 1-13 8z" fill="{{ $hair }}"/>@endif
                                    <path d="M49 53q3.5-3.5 7 0M64 53q3.5-3.5 7 0"/>
                                    <path d="M51 62q9 9 18 0z" fill="#fff"/>
                                    <circle cx="45" cy="60" r="3.5" fill="#D4705F" stroke="none" opacity=".35"/><circle cx="75" cy="60" r="3.5" fill="#D4705F" stroke="none" opacity=".35"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- Panah doodle: tiap panah punya kotak & rasio sendiri supaya lengkungannya tidak gepeng di layar mana pun. --}}
                @foreach([
                    // [posisi kotak, viewBox, garis, kepala panah, jeda, tebal garis (dalam satuan viewBox)]
                    ['left-[41%] top-[36%] w-[18%] h-[20%]', '0 0 100 100', 'M8 90C14 70 30 58 44 56c14-2 14 14 2 14-12 0-8-20 6-30 12-9 24-18 38-28', 'M76 13.4L90 12l-5.7 12.7', 1.0, 3.4],
                    ['left-[82%] top-[28%] w-[14%] h-[32%]', '0 0 60 100', 'M18 4c24 16 25 56 15 90', 'M43.1 84.3L33 94l-3.3-13.6', 1.6, 2.6],
                    ['left-[44%] top-[72%] w-[22%] h-[16%]', '0 0 100 70', 'M94 18C80 30 66 40 56 40c-12 0-12-18-2-16 10 2 4 22-12 26-12 3-24-2-36-10', 'M12.2 52.5L6 40l14 .9', 2.2, 3],
                ] as [$box, $vb, $line, $head, $at, $sw])
                    <svg class="absolute {{ $box }} overflow-visible" viewBox="{{ $vb }}" preserveAspectRatio="xMidYMid meet" fill="none" stroke="{{ $ink }}" stroke-width="{{ $sw }}" stroke-linecap="round" stroke-linejoin="round">
                        <path pathLength="1" class="arrow-path" style="animation: draw-in .7s cubic-bezier(.65,0,.35,1) {{ $at }}s both" d="{{ $line }}"/>
                        <path pathLength="1" class="arrow-path" style="animation: draw-in .25s ease-out {{ $at + .65 }}s both" d="{{ $head }}"/>
                    </svg>
                @endforeach

                {{-- Stiker: muncul setelah panah selesai, lalu mengambang halus bersamaan --}}
                <span class="absolute left-[20%] top-[10%] w-[clamp(46px,5vw,64px)] animate-[pop-in_.45s_cubic-bezier(.34,1.5,.64,1)_2.9s_both]"><span class="block animate-[float_6s_ease-in-out_3.4s_infinite]"><svg class="w-full h-auto drop-shadow-[3px_3px_0_#3E3530]" viewBox="0 0 48 44" stroke="{{ $ink }}" stroke-width="2.6" stroke-linejoin="round"><path d="M24 3C12 3 3 10.5 3 20c0 5 2.5 9.4 6.6 12.5L7 41l10.5-5.4c2 .5 4.2.8 6.5.8 12 0 21-7.5 21-17S36 3 24 3z" fill="#D4705F"/><g fill="#fff" stroke="none"><circle cx="15" cy="20" r="3"/><circle cx="24" cy="20" r="3"/><circle cx="33" cy="20" r="3"/></g></svg></span></span>
                <span class="absolute right-[3%] top-[17%] animate-[pop-in_.45s_cubic-bezier(.34,1.5,.64,1)_3.05s_both]"><span class="block animate-[float_6s_ease-in-out_3.4s_infinite]"><span class="relative inline-flex items-center gap-1.5 h-9 px-3 rounded-lg bg-white border-[2.5px] border-ink text-ink text-sm font-extrabold tracking-wide shadow-[3px_3px_0_#3E3530] -rotate-3"><x-icon name="lock" size="14" stroke="2.8" />ANONIM</span><svg class="absolute -right-5 -top-5 w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="{{ $ink }}" stroke-width="2.6" stroke-linecap="round"><path d="M4 20l5-6M12 21l2-7M3 12l6 0"/></svg></span></span>
                <span class="absolute left-[-1%] top-[38%] w-[clamp(40px,4.2vw,54px)] animate-[pop-in_.45s_cubic-bezier(.34,1.5,.64,1)_3.2s_both]"><span class="block animate-[float_6s_ease-in-out_3.4s_infinite]"><svg class="w-full h-auto drop-shadow-[3px_3px_0_#3E3530]" viewBox="0 0 48 44" stroke="{{ $ink }}" stroke-width="2.6" stroke-linejoin="round"><path d="M24 40S5 29 5 15.5A9.5 9.5 0 0 1 24 10a9.5 9.5 0 0 1 19 5.5C43 29 24 40 24 40z" fill="#8FA382"/></svg></span></span>
                <span class="absolute left-[58%] top-[86%] w-[clamp(42px,4.4vw,56px)] animate-[pop-in_.45s_cubic-bezier(.34,1.5,.64,1)_3.35s_both]"><span class="block animate-[float_6s_ease-in-out_3.4s_infinite]"><svg class="w-full h-auto drop-shadow-[3px_3px_0_#3E3530]" viewBox="0 0 48 48" fill="none" stroke="{{ $ink }}" stroke-width="2.6" stroke-linecap="round"><circle cx="24" cy="24" r="20" fill="#F3E6D0"/><circle cx="17" cy="20" r="2.4" fill="{{ $ink }}" stroke="none"/><path d="M27 20q3.5-3 7 0"/><path d="M15 29q9 8 18 0"/></svg></span></span>

            </div>
        </div>

        {{-- Pita topik: selotip gelap bertepi sobek yang sedikit miring, berisi potongan kertas bertulisan tangan (topik yang biasa diceritakan murid). --}}
        @php
            $topics = ['Keluarga', 'Pertemanan', 'Asmara', 'Bullying', 'Akademik/Nilai', 'Diri Sendiri', 'Masa Depan/Karier', 'Stres ujian', 'Overthinking', 'Belum tahu / Campur aduk'];
            $papers = ['bg-cream-light', 'bg-terracotta-tint', 'bg-sage-tint', 'bg-sand-tint'];
            $tilts = ['-3deg', '2deg', '-1.5deg', '3deg', '-2.5deg', '1.5deg'];
        @endphp
        <div aria-hidden="true" class="relative w-screen ml-[calc(50%-50vw)] overflow-hidden py-5 animate-[rise_.8s_ease-out_1.4s_both]">
            <div class="relative -mx-[3%] -rotate-[1.2deg] bg-ink py-4" style="clip-path: polygon(0 8%, 3% 0, 6% 9%, 10% 2%, 14% 10%, 18% 1%, 22% 8%, 27% 0, 31% 9%, 35% 2%, 40% 10%, 44% 1%, 48% 8%, 53% 0, 57% 9%, 61% 2%, 66% 10%, 70% 1%, 74% 8%, 79% 0, 83% 9%, 87% 2%, 92% 10%, 96% 1%, 100% 7%, 100% 93%, 97% 100%, 93% 91%, 89% 99%, 85% 90%, 80% 98%, 76% 92%, 72% 100%, 67% 91%, 63% 99%, 59% 90%, 54% 98%, 50% 92%, 46% 100%, 41% 91%, 37% 99%, 33% 90%, 28% 98%, 24% 92%, 20% 100%, 15% 91%, 11% 99%, 7% 90%, 3% 98%, 0 92%)">
                <div class="flex w-max animate-[marquee_42s_linear_infinite] hover:[animation-play-state:paused]">
                    @foreach([1, 2] as $copy)
                        <div class="flex items-center gap-5 pr-5">
                            @foreach($topics as $i => $topic)
                                {{-- Bayangan di pembungkus luar, karena clip-path ikut memotong bayangan --}}
                                <span class="relative inline-flex drop-shadow-[2px_3px_0_rgba(0,0,0,.35)]" style="transform: rotate({{ $tilts[$i % 6] }})">
                                    <span class="inline-flex items-center px-4 pt-1.5 pb-1 {{ $papers[$i % 4] }} text-ink font-hand text-[26px] leading-none whitespace-nowrap" style="clip-path: polygon(0 3%, 100% 0, 98% 100%, 2% 97%)">{{ $topic }}</span>
                                </span>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </header>

    {{-- Section lanjutan memakai bahasa visual yang sama dengan hero: kertas sobek, kartu stiker bergaris tinta dengan bayangan tegas, tulisan tangan, dan panah doodle. Di layar lebar isinya meregang mengisi tinggi layar. --}}

    {{-- 2. Kenapa banyak yang memendam: tiga alasan sebagai papan petunjuk arah di taman. Saat section terlihat, tiang tumbuh lalu papan berputar masuk; papan berputar 360° di tiangnya saat disorot/diketuk, lalu bergoyang pelan. --}}
    <section id="kenapa" class="section-screen container-wl flex flex-col justify-center">
        <div class="lg:flex-1 grid lg:grid-cols-[7fr_5fr] gap-10 lg:gap-14 items-center">
            <div class="relative flex flex-col gap-6 lg:order-2">
                <span class="eyebrow text-sage-deep">Kenapa banyak yang memendam</span>
                <h2 class="text-[clamp(38px,5vw,80px)] leading-[1.02]">Mau cerita itu nggak gampang. <span class="font-hand text-terracotta-deep text-[1.18em] leading-none whitespace-nowrap">Kami paham.</span></h2>
                <p class="text-[18px] lg:text-[22px] leading-[1.6] text-ink-soft max-w-[520px]">Banyak murid memilih memendam karena tiga hal ini. Welcoming dirancang supaya ketiganya nggak jadi penghalang lagi.</p>
            </div>

            <div class="relative lg:h-full min-h-[620px] lg:min-h-[680px] flex flex-col justify-center lg:order-1"
                 x-data="{ shown: false }"
                 x-init="new IntersectionObserver(([e], o) => { if (e.isIntersecting) { shown = true; o.disconnect(); } }, { threshold: .25 }).observe($el)">

                {{-- Tiang kayu + rumput --}}
                <div aria-hidden="true" class="absolute left-1/2 -translate-x-1/2 top-[2%] bottom-[calc(2%+12px)] w-6 origin-bottom transition-transform duration-700 ease-[cubic-bezier(.22,1,.36,1)]" :class="shown ? 'scale-y-100' : 'scale-y-0'">
                    <span class="absolute -top-4 left-1/2 -translate-x-1/2 w-10 h-6 bg-terracotta border-[2.5px] border-ink rounded-t-full"></span>
                    <span class="block w-full h-full bg-sand-deep border-[2.5px] border-ink rounded-t-sm [background-image:repeating-linear-gradient(180deg,transparent_0_26px,rgba(62,53,48,.25)_26px_28px)]"></span>
                </div>
                {{-- Rumput di depan kaki tiang supaya tiang tampak tertanam --}}
                <svg aria-hidden="true" class="absolute z-[5] left-1/2 -translate-x-1/2 bottom-[2%] w-[46%] h-10" viewBox="0 0 200 40" fill="none" stroke="#3E3530" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M6 36c30-18 158-18 188 0z" fill="#8FA382"/><path d="M40 26l-4-12M46 25l2-13M150 25l-3-12M157 26l4-12M98 21l0-12M104 21l4-11"/></svg>

                {{-- Papan arah --}}
                <div class="relative z-10 flex flex-col gap-7 lg:gap-9 py-14 [perspective:1600px]">
                    @foreach([
                        ['Takut ketahuan', 'Khawatir teman atau wali kelas tahu kamu ke ruang BK. Di sini kamu bisa mulai tanpa nama.', '#F6DDD7', 'right', '-2deg', 0],
                        ['Bingung mulai dari mana', 'Perasaannya campur aduk dan susah dirangkai. Nggak perlu disusun rapi, pilih guru dan topik, lalu langsung ngobrol di chat.', '#F3E6D0', 'left', '1.5deg', 1],
                        ['BK dianggap tempat dihukum', 'Padahal Guru BK ada untuk mendengarkan. Kenali dulu mereka lewat profil yang hangat dan manusiawi.', '#E4EADF', 'right', '-1deg', 2],
                    ] as [$t, $d, $color, $dir, $tilt, $i])
                        @php $shape = $dir === 'right' ? 'M2 5H85L98 50L85 95H2Z' : 'M15 5H98V95H15L2 50Z'; @endphp
                        <div class="relative w-[94%] mx-auto [transform-style:preserve-3d] transition-[transform,opacity] duration-[900ms] ease-[cubic-bezier(.34,1.3,.64,1)]"
                             :style="(shown ? 'opacity: 1; transform: rotateY(0deg);' : 'opacity: 0; transform: rotateY({{ $dir === 'right' ? '-100' : '100' }}deg);') + ' transition-delay: {{ .45 + $i * .2 }}s'">
                            <div class="sign-sway" style="--tilt: {{ $tilt }}; animation-delay: {{ -$i * 1.7 }}s">
                                <div x-data="{ spin: false }" x-on:mouseenter="spin = true" x-on:click="spin = true" x-on:animationend.self="spin = false" :class="spin && 'sign-spin'" class="relative [transform-style:preserve-3d] cursor-pointer">
                                    {{-- penjepit di tiang --}}
                                    <span aria-hidden="true" class="absolute left-1/2 -translate-x-1/2 -top-2.5 w-8 h-5 bg-ink rounded-sm z-10 [transform:translateZ(1px)]"></span>
                                    <span aria-hidden="true" class="absolute left-1/2 -translate-x-1/2 -bottom-2.5 w-8 h-5 bg-ink rounded-sm z-10 [transform:translateZ(1px)]"></span>
                                    {{-- sisi depan --}}
                                    <div class="relative [backface-visibility:hidden]">
                                        <svg aria-hidden="true" class="absolute inset-0 w-full h-full translate-x-[6px] translate-y-[6px]" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="{{ $shape }}" fill="#3E3530"/></svg>
                                        <svg aria-hidden="true" class="absolute inset-0 w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="{{ $shape }}" fill="{{ $color }}" stroke="#3E3530" stroke-width="2.6" vector-effect="non-scaling-stroke" stroke-linejoin="round"/></svg>
                                        <div class="relative flex items-center gap-5 lg:gap-6 py-7 lg:py-8 {{ $dir === 'right' ? 'pl-7 lg:pl-9 pr-[16%]' : 'pl-[16%] pr-7 lg:pr-9' }}">
                                            <span class="font-hand text-[64px] lg:text-[80px] leading-[.8] text-ink/80 flex-none w-12 lg:w-16 text-center" aria-hidden="true">{{ $i + 1 }}</span>
                                            <span class="flex flex-col gap-1.5">
                                                <h3 class="text-[24px] lg:text-[30px] leading-tight">{{ $t }}</h3>
                                                <p class="text-[16px] lg:text-[18px] leading-relaxed text-ink/75">{{ $d }}</p>
                                            </span>
                                        </div>
                                    </div>
                                    {{-- sisi belakang: kayu polos --}}
                                    <div aria-hidden="true" class="absolute inset-0 [backface-visibility:hidden] [transform:rotateY(180deg)]">
                                        <svg class="absolute inset-0 w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="{{ $shape }}" fill="#D4B07F" stroke="#3E3530" stroke-width="2.6" vector-effect="non-scaling-stroke" stroke-linejoin="round"/></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- 3. Keamanan: lembar kertas hijau bertepi sobek, fitur keamanan sebagai gantungan label kunci --}}
    <section id="keamanan" class="section-screen container-wl flex flex-col justify-center">
        <div class="relative lg:flex-1 flex flex-col">
            <div class="torn-edge lg:flex-1 bg-sage-deep text-cream px-[clamp(24px,4.5vw,72px)] py-[clamp(44px,6vw,88px)] flex flex-col justify-center">
                <div class="lg:flex-1 grid lg:grid-cols-[4fr_7fr] gap-10 lg:gap-14 items-center">
                    <div class="flex flex-col gap-5">
                        <span class="eyebrow text-sage-tint">Keamanan & Anonimitas</span>
                        <h2 class="text-[clamp(34px,4.4vw,68px)] leading-[1.04]">Namamu tersembunyi sampai <span class="font-hand text-sand-tint text-[1.2em] leading-none whitespace-nowrap">kamu sendiri</span> yang membukanya.</h2>
                    </div>
                    {{-- Gantungan kunci: rel kayu dengan 4 kait; tiap fitur jadi label kunci bertali yang jatuh lalu berayun saat section terlihat, berayun lagi saat disorot/diketuk. --}}
                    <div class="relative pt-3"
                         x-data="{ shown: false }"
                         x-init="new IntersectionObserver(([e], o) => { if (e.isIntersecting) { shown = true; o.disconnect(); } }, { threshold: .3 }).observe($el)">
                        {{-- rel kayu --}}
                        <div aria-hidden="true" class="absolute inset-x-0 top-0 h-6 bg-sand-deep border-[2.5px] border-ink rounded-full shadow-[4px_4px_0_#2F3D28] origin-left transition-transform duration-700 ease-[cubic-bezier(.22,1,.36,1)]" :class="shown ? 'scale-x-100' : 'scale-x-0'">
                            <span class="absolute -left-2 top-1/2 -translate-y-1/2 w-5 h-5 rounded-full bg-terracotta border-[2.5px] border-ink"></span>
                            <span class="absolute -right-2 top-1/2 -translate-y-1/2 w-5 h-5 rounded-full bg-terracotta border-[2.5px] border-ink"></span>
                        </div>
                        <div class="relative grid grid-cols-2 lg:grid-cols-4 gap-x-4 lg:gap-x-5 gap-y-2">
                            @foreach([
                                ['Kamu pegang kuncinya', 'Identitas hanya terbuka lewat tombol “Buka Identitas” yang kamu tekan sendiri.', 'unlock', '#F6DDD7', 'h-8 lg:h-12', 0],
                                ['Riwayat terkunci PIN', 'HP dipinjam teman? Chat-mu tetap tertutup.', 'lock', '#F3E6D0', 'h-14 lg:h-32', 1],
                                ['Tombol Keluar Cepat', 'Satu ketukan (atau tekan Esc dua kali) langsung pindah ke halaman netral.', 'exit', '#E4EADF', 'h-8 lg:h-20', 2],
                                ['Laporan hanya agregat', 'Data di bawah 5 kasus disembunyikan supaya tidak bisa ditebak.', 'dashboard', '#FBF8EF', 'h-14 lg:h-40', 3],
                            ] as [$t, $d, $ic, $color, $string, $i])
                                <div class="flex flex-col items-center">
                                    {{-- kait --}}
                                    <svg aria-hidden="true" class="relative z-10 w-6 h-8 -mt-1" viewBox="0 0 24 32" fill="none" stroke="#3E3530" stroke-width="2.8" stroke-linecap="round"><path d="M12 2v14a6 6 0 1 1-6 6"/></svg>
                                    <div class="w-full flex flex-col items-center origin-top"
                                         :style="shown ? 'animation: key-drop 1.6s cubic-bezier(.3,0,.3,1) {{ .5 + $i * .18 }}s both' : 'opacity: 0'"
                                         x-on:mouseenter="$el.style.animation = 'none'; $el.offsetWidth; $el.style.animation = 'key-swing 1.4s ease-out'"
                                         x-on:click="$el.style.animation = 'none'; $el.offsetWidth; $el.style.animation = 'key-swing 1.4s ease-out'">
                                        {{-- tali --}}
                                        <span aria-hidden="true" class="block w-[3px] {{ $string }} bg-ink rounded-full"></span>
                                        {{-- label kunci --}}
                                        <div class="relative w-full -mt-1">
                                            <svg aria-hidden="true" class="absolute inset-0 w-full h-full translate-x-[5px] translate-y-[5px]" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M24 2H76L98 14V98H2V14Z" fill="#2F3D28"/></svg>
                                            <svg aria-hidden="true" class="absolute inset-0 w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M24 2H76L98 14V98H2V14Z" fill="{{ $color }}" stroke="#3E3530" stroke-width="2.6" vector-effect="non-scaling-stroke" stroke-linejoin="round"/></svg>
                                            <span aria-hidden="true" class="absolute left-1/2 -translate-x-1/2 top-2.5 w-4 h-4 rounded-full bg-sage-deep border-[2.5px] border-ink"></span>
                                            <div class="relative text-ink px-4 lg:px-5 pt-10 lg:pt-11 pb-5 lg:pb-7 flex flex-col items-center text-center gap-3">
                                                <span class="tile w-12 h-12 lg:w-14 lg:h-14 !rounded-full bg-white border-2 border-ink"><x-icon :name="$ic" size="24" /></span>
                                                <span class="font-extrabold text-[16px] lg:text-[19px] leading-tight">{{ $t }}</span>
                                                <span class="text-[13px] lg:text-[15px] leading-[1.5] text-ink-soft">{{ $d }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 4. Fitur: rak kayu berisi benda yang mewakili tiap fitur (toples mood, buku & teh, mikrofon bertopeng, kalender meja). Saat section terlihat benda jatuh ke rak; bergoyang saat disorot/diketuk. --}}
    <section id="fitur" class="section-screen container-wl flex flex-col justify-center gap-10 lg:gap-12">
        <div class="flex flex-col items-center text-center gap-3">
            <span class="eyebrow text-sage-deep">Fitur unggulan</span>
            <h2 class="text-[clamp(36px,4.8vw,76px)] leading-[1.04]">Lebih dari sekadar <span class="relative inline-block font-hand text-terracotta-deep text-[1.2em] leading-none">chat.<svg aria-hidden="true" class="absolute left-0 -bottom-2 w-full h-3" viewBox="0 0 100 12" preserveAspectRatio="none" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M2 8c20-6 40-6 58-2s28 2 38-2"/></svg></span></h2>
        </div>
        <div class="lg:flex-1 flex flex-col justify-center"
             x-data="{ shown: false }"
             x-init="new IntersectionObserver(([e], o) => { if (e.isIntersecting) { shown = true; o.disconnect(); } }, { threshold: .3 }).observe($el)"
             :class="shown && 'is-grown'">
            <div class="relative">
                <div class="relative z-10 grid grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-10 lg:gap-x-12 items-end px-2 lg:px-12">
                    <div class="obj-item flex flex-col items-center" style="--d: 0.30s">
                        <div class="obj w-full max-w-[220px] cursor-pointer" x-on:click="$el.classList.remove('is-wiggling'); $el.offsetWidth; $el.classList.add('is-wiggling')">
                            <svg aria-hidden="true" class="obj-body w-full h-auto overflow-visible" viewBox="0 0 200 200" fill="none" stroke="#3E3530" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M60 58H140Q152 58 152 70V176Q152 194 134 194H66Q48 194 48 176V70Q48 58 60 58Z" fill="#FBF8EF"/><g class="obj-bob" style="animation-delay: 0s"><circle cx="70" cy="174" r="18" fill="#E4EADF"/><circle cx="65" cy="171" r="1.7" fill="#3E3530" stroke="none"/><circle cx="75" cy="171" r="1.7" fill="#3E3530" stroke="none"/><path d="M65 177q5 4 10 0" stroke-width="2.2"/></g><g class="obj-bob" style="animation-delay: -.8s"><circle cx="106" cy="175" r="18" fill="#F6DDD7"/><circle cx="101" cy="172" r="1.7" fill="#3E3530" stroke="none"/><circle cx="111" cy="172" r="1.7" fill="#3E3530" stroke="none"/><path d="M101 179q2.5-2 5 0t5 0" stroke-width="2.2"/></g><g class="obj-bob" style="animation-delay: -1.6s"><circle cx="134" cy="170" r="18" fill="#F3E6D0"/><circle cx="129" cy="167" r="1.7" fill="#3E3530" stroke="none"/><circle cx="139" cy="167" r="1.7" fill="#3E3530" stroke="none"/><path d="M129 175q5-4 10 0" stroke-width="2.2"/></g><g class="obj-bob" style="animation-delay: -2.4s"><circle cx="88" cy="141" r="18" fill="#EDE5D0"/><circle cx="83" cy="138" r="1.7" fill="#3E3530" stroke="none"/><circle cx="93" cy="138" r="1.7" fill="#3E3530" stroke="none"/><path d="M83 145h10" stroke-width="2.2"/></g><g class="obj-bob" style="animation-delay: -1.2s"><circle cx="124" cy="140" r="18" fill="#F2CBC1"/><circle cx="119" cy="137" r="1.7" fill="#3E3530" stroke="none"/><circle cx="129" cy="137" r="1.7" fill="#3E3530" stroke="none"/><path d="M119 143q5 4 10 0" stroke-width="2.2"/></g><g class="obj-bob" style="animation-delay: -2s"><circle cx="104" cy="106" r="18" fill="#E4EADF"/><circle cx="99" cy="103" r="1.7" fill="#3E3530" stroke="none"/><circle cx="109" cy="103" r="1.7" fill="#3E3530" stroke="none"/><path d="M99 109q5 4 10 0" stroke-width="2.2"/></g><path d="M60 76v60" stroke="#FFFFFF" stroke-width="5" opacity=".9"/><rect x="62" y="28" width="76" height="24" rx="6" fill="#D4705F"/><path d="M72 40h56" stroke-width="2.2" opacity=".5"/></svg>
                        </div>
                        <span aria-hidden="true" class="lg:hidden block w-[80%] h-3 rounded-full bg-sand-deep border-[2.5px] border-ink"></span>
                        <div class="lg:hidden flex flex-col items-center text-center gap-1.5 mt-4">
                            <h3 class="text-[20px] leading-tight">Mood Tracker</h3>
                            <p class="text-[14px] leading-relaxed text-ink-soft">Check-in harian yang privat. Kirim catatan ke Guru BK hanya kalau kamu mau.</p>
                        </div>
                    </div>
                    <div class="obj-item flex flex-col items-center" style="--d: 0.48s">
                        <div class="obj w-full max-w-[220px] cursor-pointer" x-on:click="$el.classList.remove('is-wiggling'); $el.offsetWidth; $el.classList.add('is-wiggling')">
                            <svg aria-hidden="true" class="obj-body w-full h-auto overflow-visible" viewBox="0 0 200 200" fill="none" stroke="#3E3530" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><rect x="34" y="160" width="132" height="34" rx="5" fill="#8FA382"/><path d="M150 166v22" stroke-width="2.2"/><g transform="rotate(-3 102 143)"><rect x="46" y="126" width="112" height="34" rx="5" fill="#D4B07F"/><path d="M60 132v22" stroke-width="2.2"/></g><g transform="rotate(2 100 110)"><rect x="40" y="94" width="120" height="32" rx="5" fill="#D4705F"/><path d="M146 100v20" stroke-width="2.2"/><path d="M124 126v24l7-6 7 6v-24" fill="#F3E6D0"/></g><path d="M78 54h40v30a10 10 0 0 1-10 10H88a10 10 0 0 1-10-10z" fill="#FBF8EF"/><path d="M118 62h6a8 8 0 0 1 0 16h-6"/><path d="M84 66h28" stroke="#D4705F" stroke-width="2.2"/><g class="obj-steam"><path d="M90 44c-4-6 4-10 0-16"/><path d="M106 44c-4-6 4-10 0-16"/></g></svg>
                        </div>
                        <span aria-hidden="true" class="lg:hidden block w-[80%] h-3 rounded-full bg-sand-deep border-[2.5px] border-ink"></span>
                        <div class="lg:hidden flex flex-col items-center text-center gap-1.5 mt-4">
                            <h3 class="text-[20px] leading-tight">Self-Help</h3>
                            <p class="text-[14px] leading-relaxed text-ink-soft">Bacaan singkat dari Guru BK: overthinking, stres ujian, manajemen waktu, pertemanan.</p>
                        </div>
                    </div>
                    <div class="obj-item flex flex-col items-center" style="--d: 0.66s">
                        <div class="obj w-full max-w-[220px] cursor-pointer" x-on:click="$el.classList.remove('is-wiggling'); $el.offsetWidth; $el.classList.add('is-wiggling')">
                            <svg aria-hidden="true" class="obj-body w-full h-auto overflow-visible" viewBox="0 0 200 200" fill="none" stroke="#3E3530" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><g class="obj-wave"><path d="M146 72q10 12 0 24M158 64q18 20 0 40"/><path d="M54 72q-10 12 0 24M42 64q-18 20 0 40"/></g><rect x="70" y="182" width="60" height="12" rx="6" fill="#D4B07F"/><path d="M100 182v-48"/><path d="M74 98q0 34 26 34t26-34" /><rect x="78" y="30" width="44" height="78" rx="22" fill="#EDE5D0"/><path d="M84 46h32M82 92h36M84 100h32" stroke-width="2.2"/><path d="M62 64l10 2M128 66l10-2" stroke-width="2.6"/><path d="M70 62q30-10 60 0 4 16-12 20-10 2-18-6-8 8-18 6-16-4-12-20z" fill="#D4705F"/><ellipse cx="89" cy="70" rx="6" ry="4" fill="#3E3530" stroke="none"/><ellipse cx="111" cy="70" rx="6" ry="4" fill="#3E3530" stroke="none"/></svg>
                        </div>
                        <span aria-hidden="true" class="lg:hidden block w-[80%] h-3 rounded-full bg-sand-deep border-[2.5px] border-ink"></span>
                        <div class="lg:hidden flex flex-col items-center text-center gap-1.5 mt-4">
                            <h3 class="text-[20px] leading-tight">Pesan Suara Tersamar</h3>
                            <p class="text-[14px] leading-relaxed text-ink-soft">Lebih nyaman ngomong? Rekam suara dan samarkan nadanya langsung di HP-mu sebelum dikirim.</p>
                        </div>
                    </div>
                    <div class="obj-item flex flex-col items-center" style="--d: 0.84s">
                        <div class="obj w-full max-w-[220px] cursor-pointer" x-on:click="$el.classList.remove('is-wiggling'); $el.offsetWidth; $el.classList.add('is-wiggling')">
                            <svg aria-hidden="true" class="obj-body w-full h-auto overflow-visible" viewBox="0 0 200 200" fill="none" stroke="#3E3530" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M58 194L66 72h68l8 122" fill="#D4B07F"/><rect x="44" y="64" width="112" height="128" rx="9" fill="#FBF8EF"/><path d="M53 64H147Q156 64 156 73V98H44V73Q44 64 53 64Z" fill="#8FA382"/><rect x="66" y="52" width="12" height="24" rx="6" fill="#FBF8EF"/><rect x="122" y="52" width="12" height="24" rx="6" fill="#FBF8EF"/><path d="M60 114h12M80 114h12M108 114h12M128 114h12M60 176h12M128 176h12" stroke-width="2.2" opacity=".35"/><text x="100" y="164" text-anchor="middle" font-size="46" font-family="Young Serif, Georgia, serif" fill="#3E3530" stroke="none">12</text><ellipse class="obj-circle" pathLength="1" cx="100" cy="149" rx="32" ry="25" stroke="#D4705F" stroke-width="3.4" transform="rotate(-8 100 149)"/></svg>
                        </div>
                        <span aria-hidden="true" class="lg:hidden block w-[80%] h-3 rounded-full bg-sand-deep border-[2.5px] border-ink"></span>
                        <div class="lg:hidden flex flex-col items-center text-center gap-1.5 mt-4">
                            <h3 class="text-[20px] leading-tight">Booking Tatap Muka</h3>
                            <p class="text-[14px] leading-relaxed text-ink-soft">Pilih sendiri jadwal ngobrol langsung dengan Guru BK, kapan pun kamu siap.</p>
                        </div>
                    </div>
                </div>
                {{-- rak kayu (desktop) --}}
                <div aria-hidden="true" class="hidden lg:block relative -mt-2 h-7 bg-sand-deep border-[2.5px] border-ink rounded-lg shadow-[5px_5px_0_#3E3530]">
                    <span class="absolute left-[8%] top-full w-5 h-12 bg-sand-deep border-[2.5px] border-t-0 border-ink rounded-b-md"></span>
                    <span class="absolute right-[8%] top-full w-5 h-12 bg-sand-deep border-[2.5px] border-t-0 border-ink rounded-b-md"></span>
                </div>
            </div>
            <div class="hidden lg:grid grid-cols-4 gap-x-10 px-10 mt-16">
                    <div class="flex flex-col items-center text-center gap-2 px-2">
                        <h3 class="text-[22px] xl:text-[26px] leading-tight">Mood Tracker</h3>
                        <p class="text-[15px] xl:text-[16px] leading-relaxed text-ink-soft max-w-[300px]">Check-in harian yang privat. Kirim catatan ke Guru BK hanya kalau kamu mau.</p>
                    </div>
                    <div class="flex flex-col items-center text-center gap-2 px-2">
                        <h3 class="text-[22px] xl:text-[26px] leading-tight">Self-Help</h3>
                        <p class="text-[15px] xl:text-[16px] leading-relaxed text-ink-soft max-w-[300px]">Bacaan singkat dari Guru BK: overthinking, stres ujian, manajemen waktu, pertemanan.</p>
                    </div>
                    <div class="flex flex-col items-center text-center gap-2 px-2">
                        <h3 class="text-[22px] xl:text-[26px] leading-tight">Pesan Suara Tersamar</h3>
                        <p class="text-[15px] xl:text-[16px] leading-relaxed text-ink-soft max-w-[300px]">Lebih nyaman ngomong? Rekam suara dan samarkan nadanya langsung di HP-mu sebelum dikirim.</p>
                    </div>
                    <div class="flex flex-col items-center text-center gap-2 px-2">
                        <h3 class="text-[22px] xl:text-[26px] leading-tight">Booking Tatap Muka</h3>
                        <p class="text-[15px] xl:text-[16px] leading-relaxed text-ink-soft max-w-[300px]">Pilih sendiri jadwal ngobrol langsung dengan Guru BK, kapan pun kamu siap.</p>
                    </div>
            </div>
        </div>
    </section>

    {{-- 5. Ajakan penutup (mengikuti contohsection5.png): kartu putih tiga kolom simetris (judul + pengguna | logo | ajakan + tombol), garis pemisah, lalu pita kalimat penguat yang berjalan. --}}
    <section class="container-wl py-16 lg:py-24">
        <div class="bg-white rounded-[32px] lg:rounded-[40px] border border-line-soft px-[clamp(24px,5vw,80px)] pt-[clamp(36px,5vw,72px)] pb-[clamp(28px,3.5vw,48px)] flex flex-col gap-10 lg:gap-12">
            <div class="grid lg:grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] gap-10 lg:gap-16 items-center">
                <div class="flex flex-col gap-8 lg:max-w-[520px]">
                    <h2 class="text-[clamp(34px,3.6vw,58px)] leading-[1.06]">Nggak ada cerita yang terlalu kecil untuk <span class="font-hand text-terracotta-deep text-[1.2em] leading-none whitespace-nowrap">didengar.</span></h2>
                    <div class="flex items-center gap-4">
                        <span class="flex -space-x-3" aria-hidden="true">
                            {{-- Ilustrasi murid (bukan inisial), agar beda dari avatar Guru BK di atas. [latar, kulit, rambut, gaya rambut, baju] --}}
                            @foreach([['#E4EADF', '#F1CBA8', '#3E3530', 'long', '#FBEDE9'], ['#F3E6D0', '#C98E6B', '#2E2723', 'short', '#E4EADF'], ['#F6DDD7', '#E2B08A', '#4A3A30', 'curly', '#F3E6D0']] as [$bg, $skin, $hair, $style, $shirt])
                                <span class="w-12 h-12 rounded-full border-[3px] border-white overflow-hidden flex items-end justify-center" style="background: {{ $bg }}">
                                    <x-doodle-person :skin="$skin" :hair="$hair" :hair-style="$style" :shirt="$shirt" class="w-[120%] h-auto translate-y-[18%] flex-none" />
                                </span>
                            @endforeach
                            <span class="w-12 h-12 rounded-full border-[3px] border-white bg-sage-deep text-white flex items-center justify-center"><x-icon name="chat-heart" size="22" /></span>
                        </span>
                        <span class="flex flex-col leading-tight">
                            {{-- Angka contoh (data dummy) --}}
                            <a href="{{ route('register') }}" class="text-[16px] font-extrabold text-ink underline underline-offset-4 decoration-2 hover:text-terracotta-deep">2.500+ murid</a>
                            <span class="text-[15px] text-ink-soft">(sudah mulai bercerita)</span>
                        </span>
                    </div>
                </div>

                {{-- Tengah: logo Welcoming. Muncul memantul saat terlihat, lalu mengambang; miring 3D mengikuti mouse; tangan melambai, hati berdetak, dan melompat berkala (juga saat disorot/diketuk). --}}
                <div class="flex justify-center [perspective:900px]"
                     x-data="{ shown: false, rx: 0, ry: 0 }"
                     x-init="new IntersectionObserver(([e], o) => { if (e.isIntersecting) { shown = true; o.disconnect(); } }, { threshold: .4 }).observe($el)"
                     x-on:mousemove="if (!matchMedia('(prefers-reduced-motion: reduce)').matches) { const r = $el.getBoundingClientRect(); ry = ((event.clientX - r.left) / r.width - .5) * 22; rx = -((event.clientY - r.top) / r.height - .5) * 22; }"
                     x-on:mouseleave="rx = 0; ry = 0">
                    <div class="transition-transform duration-300 ease-out" :style="`transform: rotateX(${rx}deg) rotateY(${ry}deg)`">
                        <div class="logo-cta" :class="shown && 'is-in'" x-on:click="$el.classList.remove('is-hop'); $el.offsetWidth; $el.classList.add('is-hop')">
                            {{-- Logo dipisah per layer (public/images/logo-parts) supaya kedua tangan bisa melambai & hatinya berdetak --}}
                            <div role="img" aria-label="Logo Welcoming" class="logo-cta-img relative w-[clamp(170px,16vw,260px)] aspect-[598/361] cursor-pointer select-none">
                                <img src="{{ asset('images/logo-parts/bubble.png') }}" alt="" draggable="false" class="logo-part logo-bubble absolute inset-0 w-full h-full">
                                <img src="{{ asset('images/logo-parts/heart.png') }}" alt="" draggable="false" class="logo-part logo-heart absolute inset-0 w-full h-full">
                                <img src="{{ asset('images/logo-parts/arm-left.png') }}" alt="" draggable="false" class="logo-part logo-arm-left absolute inset-0 w-full h-full">
                                <img src="{{ asset('images/logo-parts/arm-right.png') }}" alt="" draggable="false" class="logo-part logo-arm-right absolute inset-0 w-full h-full">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col items-start gap-8 lg:max-w-[440px] lg:justify-self-end">
                    <p class="text-[17px] lg:text-[19px] leading-[1.7] text-ink-soft">Welcoming menghubungkan kamu dengan Guru BK di sekolahmu. Cerita secara anonim kapan pun kamu siap, dan buka identitas hanya kalau kamu sendiri mau.</p>
                    <div class="flex">
                        <a href="{{ route('login', ['peran' => 'student']) }}" class="group inline-flex items-center gap-3 h-16 px-9 rounded-full bg-ink text-cream no-underline font-extrabold text-[17px] transition hover:bg-[#2E2723]">Masuk sebagai Murid<x-icon name="arrow-right" size="18" class="transition-transform group-hover:translate-x-1" /></a>
                    </div>
                </div>
            </div>

            <hr class="border-line-soft">

            {{-- Pita kalimat penguat yang sering didengar di ruang BK (pengganti logo mitra) --}}
            <div aria-hidden="true" class="overflow-hidden">
                <div class="flex w-max animate-[marquee_46s_linear_infinite] hover:[animation-play-state:paused]">
                    @foreach([1, 2] as $copy)
                        <div class="flex items-center gap-14 lg:gap-20 pr-14 lg:pr-20">
                            @foreach(['Kamu nggak sendirian', 'Pelan-pelan aja', 'Ceritamu aman di sini', 'Nggak apa-apa merasa begitu', 'Kita cari jalannya bareng', 'Istirahat juga penting'] as $line)
                                <span class="text-ink/35 whitespace-nowrap transition-colors hover:text-ink/70">
                                    <span class="font-hand text-[32px] lg:text-[38px] leading-none">{{ $line }}</span>
                                </span>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Footer: kartu gelap bersudut atas membulat. Atas: tagline + tombol darurat dan kolom tautan. Bawah: hak cipta, lalu wordmark raksasa (logo W + "elcoming") selebar kartu yang naik saat terlihat. --}}
    <footer class="px-[clamp(8px,1.2vw,20px)] pt-6"
            x-data="{ shown: false }"
            x-init="new IntersectionObserver(([e], o) => { if (e.isIntersecting) { shown = true; o.disconnect(); } }, { threshold: .15 }).observe($el)">
        <div class="bg-[#2E2723] text-cream rounded-t-[32px] lg:rounded-t-[48px] overflow-hidden">
            <div class="px-[clamp(24px,5vw,80px)] pt-14 lg:pt-20 pb-10 grid gap-12 lg:grid-cols-[1.4fr_1fr_1fr_1fr]">
                <div class="flex flex-col gap-5 max-w-[420px]">
                    <p class="font-display text-[clamp(30px,2.9vw,46px)] leading-[1.12] text-white">Ruang cerita murid &amp; Guru BK, <span class="font-hand text-sand text-[1.2em] leading-none">tanpa takut dihakimi.</span></p>
                    <a href="{{ route('emergency') }}" class="self-start btn btn-danger btn-lg"><x-icon name="phone" size="18" />Bantuan Darurat</a>
                    <span class="text-[15px] text-cream/85 -mt-2">Bisa dibuka tanpa login.</span>
                </div>
                @foreach([
                    ['Jelajahi', [['Kenapa Welcoming', '#kenapa'], ['Keamanan', '#keamanan'], ['Fitur', '#fitur']]],
                    ['Akun', [['Masuk', route('login')], ['Daftar sebagai Murid', route('register')], ['Masuk sebagai Guru BK', route('login', ['peran' => 'counselor'])]]],
                    ['Bantuan', [['Bantuan Darurat', route('emergency')], ['Lupa password', route('password.request')]]],
                ] as [$heading, $links])
                    <nav aria-label="{{ $heading }}" class="flex flex-col gap-4">
                        <span class="text-[14px] font-extrabold tracking-[0.14em] uppercase text-sand">{{ $heading }}</span>
                        <ul class="flex flex-col gap-3.5">
                            @foreach($links as [$label, $href])
                                <li><a href="{{ $href }}" class="group inline-flex items-center gap-2 text-[18px] lg:text-[19px] font-bold text-cream no-underline transition-colors hover:text-sand"><span class="w-0 h-[2px] bg-terracotta transition-all duration-300 group-hover:w-4"></span>{{ $label }}</a></li>
                            @endforeach
                        </ul>
                    </nav>
                @endforeach
            </div>

            <div class="mx-[clamp(24px,5vw,80px)] py-5 border-t border-cream/25 flex flex-wrap items-center justify-between gap-3 text-[15px] text-cream/85">
                <span>© {{ now()->year }} Welcoming. Dibuat untuk murid Indonesia.</span>
                <div class="flex items-center gap-5">
                    <a href="{{ route('privacy') }}" class="text-cream/85 no-underline font-bold transition-colors hover:text-sand">Kebijakan Privasi</a>
                    <a href="{{ route('terms') }}" class="text-cream/85 no-underline font-bold transition-colors hover:text-sand">Syarat &amp; Ketentuan</a>
                </div>
            </div>

            <div class="px-[clamp(16px,3vw,48px)] pt-4 pb-[clamp(12px,2vw,28px)]">
                {{-- viewBox diberi ruang ekstra di kiri & atas supaya tangan kiri tidak terpotong saat melambai paling jauh. Wordmark: logo bersih dipecah per layer (tangan melambai & hati berdetak terus-menerus). Saat disorot: logo melompat, tangan melambai lebih semangat, hati berdetak cepat, warna sand menyapu tulisan dari kiri ke kanan. --}}
                <svg role="img" aria-label="Welcoming" class="footer-mark block w-full h-auto overflow-visible transition-[transform,opacity] duration-[1100ms] ease-[cubic-bezier(.22,1,.36,1)]" :class="shown ? 'translate-y-0 opacity-100' : 'translate-y-[25%] opacity-0'" viewBox="-36 -14 1040 254">
                    <g class="fm-logo">
                        <image class="fm-bubble" href="{{ asset('images/logo-parts/bubble-clean.png') }}" x="0" y="10" width="300" height="181"/>
                        <image class="fm-heart" href="{{ asset('images/logo-parts/heart-clean.png') }}" x="0" y="10" width="300" height="181"/>
                        <image class="fm-arm-left" href="{{ asset('images/logo-parts/arm-left-clean.png') }}" x="0" y="10" width="300" height="181"/>
                        <image class="fm-arm-right" href="{{ asset('images/logo-parts/arm-right-clean.png') }}" x="0" y="10" width="300" height="181"/>
                    </g>
                    <defs><clipPath id="fm-wipe"><rect class="fm-wipe" x="250" y="-14" width="760" height="254"/></clipPath></defs>
                    <text class="fm-text" x="268" y="180" textLength="728" lengthAdjust="spacingAndGlyphs" font-family="'Young Serif', Georgia, serif" font-size="212">elcoming</text>
                    <text class="fm-text-fill" clip-path="url(#fm-wipe)" x="268" y="180" textLength="728" lengthAdjust="spacingAndGlyphs" font-family="'Young Serif', Georgia, serif" font-size="212" aria-hidden="true">elcoming</text>
                </svg>
            </div>
        </div>
    </footer>
</div>
@endsection
