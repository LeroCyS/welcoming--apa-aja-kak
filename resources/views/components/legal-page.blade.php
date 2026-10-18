{{-- Kerangka halaman legal publik (Kebijakan Privasi & Syarat Ketentuan): navbar sama dengan Bantuan Darurat, isi memakai .prose-wl. --}}
@props(['heading', 'updated'])
<div class="min-h-screen">
    <nav aria-label="Navigasi" class="container-wl py-[18px] flex items-center justify-between gap-3 flex-wrap">
        <div class="flex items-center gap-3">
            <x-back-button :fallback="auth()->check() ? auth()->user()->homeRoute() : route('landing')" />
            <a href="{{ route('landing') }}" aria-label="Welcoming" class="flex items-center no-underline text-ink">
                <img src="{{ asset('images/welcoming-mark.png') }}" alt="" class="w-[52px] h-auto">
                <span class="font-display text-[22px] -ml-0.5" aria-hidden="true">elcoming</span>
            </a>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('emergency') }}" class="inline-flex items-center gap-1.5 h-11 px-4 rounded-full text-danger no-underline font-extrabold text-[15px] transition hover:bg-danger-tint"><x-icon name="phone" size="16" />Darurat</a>
            @guest
                <a href="{{ route('login') }}" class="inline-flex items-center h-11 px-[22px] rounded-full bg-ink text-cream no-underline font-extrabold text-[15px]">Masuk</a>
            @else
                <a href="{{ auth()->user()->homeRoute() }}" class="inline-flex items-center h-11 px-[22px] rounded-full border-[1.5px] border-line bg-white text-ink no-underline font-extrabold text-[15px]">Beranda</a>
            @endguest
        </div>
    </nav>

    <main class="container-wl pt-4 pb-16">
        <article class="max-w-[760px] mx-auto">
            <header class="mb-8">
                <h1 class="font-display text-[clamp(32px,4vw,46px)] leading-tight">{{ $heading }}</h1>
                <p class="mt-2 text-[15px] text-ink-soft">Terakhir diperbarui: {{ $updated }}</p>
                <div class="mt-5 flex gap-2 flex-wrap text-[15px] font-extrabold">
                    <a href="{{ route('privacy') }}" @class(['inline-flex items-center h-10 px-4 rounded-full no-underline', 'bg-terracotta-tint text-terracotta-ink' => request()->routeIs('privacy'), 'border-[1.5px] border-line bg-white text-ink' => ! request()->routeIs('privacy')]) @if(request()->routeIs('privacy')) aria-current="page" @endif>Kebijakan Privasi</a>
                    <a href="{{ route('terms') }}" @class(['inline-flex items-center h-10 px-4 rounded-full no-underline', 'bg-terracotta-tint text-terracotta-ink' => request()->routeIs('terms'), 'border-[1.5px] border-line bg-white text-ink' => ! request()->routeIs('terms')]) @if(request()->routeIs('terms')) aria-current="page" @endif>Syarat &amp; Ketentuan</a>
                </div>
            </header>
            <div class="card p-[clamp(20px,4vw,40px)] prose-wl">
                {{ $slot }}
            </div>
        </article>
    </main>
</div>
