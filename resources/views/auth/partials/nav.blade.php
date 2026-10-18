{{-- Navbar halaman masuk/daftar: kembali, logo, dan bantuan darurat. --}}
    <nav aria-label="Navigasi" class="container-wl py-4 flex items-center justify-between gap-3 animate-[nav-in_.6s_cubic-bezier(.22,1,.36,1)_both]">
        <div class="flex items-center gap-2 sm:gap-3">
            <a href="{{ route('landing') }}" aria-label="Kembali ke beranda" title="Kembali ke beranda" class="w-11 h-11 flex-none inline-flex items-center justify-center text-ink no-underline transition-transform hover:-translate-x-1"><x-icon name="arrow-left" size="20" /></a>
            <a href="{{ route('landing') }}" aria-label="Welcoming" class="flex items-center no-underline text-ink">
                <img src="{{ asset('images/welcoming-mark.png') }}" alt="" class="w-12 h-auto">
                <span class="font-display text-[22px] -ml-0.5" aria-hidden="true">elcoming</span>
            </a>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('emergency') }}" class="inline-flex items-center gap-1.5 h-11 px-4 rounded-full text-danger no-underline font-extrabold text-[15px] transition hover:bg-danger-tint"><x-icon name="phone" size="16" /><span class="hidden sm:inline">Bantuan Darurat</span><span class="sm:hidden">Darurat</span></a>
        </div>
    </nav>
