{{-- Topbar murid: menu utama sebagai pill (desktop), tombol keamanan selalu terlihat, menu lainnya di laci hamburger. HP tetap memakai bottom nav. --}}
@props(['active' => 'beranda', 'unread' => 0])
@php
    $user = auth()->user();
    $main = [
        ['beranda', 'Beranda', 'student.home', 'home'],
        ['cerita', 'Cerita', 'student.conversations.index', 'chat'],
        ['mood', 'Mood', 'student.mood.index', 'mood'],
        ['selfhelp', 'Self-Help', 'student.selfhelp.index', 'book'],
    ];
    // Laci hanya berisi yang tidak ada di topbar / bottom nav.
    $extra = [
        ['teman', 'Cerita Teman', 'student.stories.index', 'people'],
        ['jadwal', 'Jadwal Saya', 'student.schedule.index', 'calendar'],
        ['guru', 'Kenalan Guru BK', 'student.counselors', 'people-heart'],
    ];
@endphp
{{-- Sama seperti navbar publik: menempel di atas saat awal, lalu berubah jadi pil kaca melayang saat halaman di-scroll. --}}
<div x-data="{ menu: false, scrolled: window.scrollY > 24 }" x-init="scrolled = window.scrollY > 24" x-on:scroll.window.passive="scrolled = window.scrollY > 24" x-on:keydown.escape.window="menu = false" {{ $attributes->merge(['class' => 'sticky top-0 z-40 no-print container-wl pt-3']) }}>
    <div class="h-16 w-full max-w-[1760px] mx-auto flex items-center gap-2 rounded-[28px] border border-transparent px-0 animate-[nav-in_.6s_cubic-bezier(.22,1,.36,1)_both] transition-[max-width,padding,background-color,border-color,box-shadow] duration-500 ease-[cubic-bezier(.22,1,.36,1)]" :class="scrolled && 'glass !border-line/70 shadow-lift !max-w-[1280px] !px-3 sm:!px-4'">
        <a href="{{ route('student.home') }}" class="flex items-center no-underline text-ink flex-none" aria-label="Welcoming, ke Beranda">
            <img src="{{ asset('images/welcoming-mark.png') }}" alt="" class="w-10 h-auto">
            <span class="hidden sm:inline font-display text-xl -ml-0.5" aria-hidden="true">elcoming</span>
        </a>

        <nav aria-label="Navigasi murid" class="hidden lg:flex items-center gap-1 ml-6">
            @foreach($main as [$id, $label, $route, $icon])
                <a href="{{ route($route) }}" data-tour="nav-{{ $id }}" aria-current="{{ $id === $active ? 'page' : 'false' }}" class="relative inline-flex items-center gap-2 h-10 px-4 rounded-full no-underline text-[15px] font-bold transition-colors {{ $id === $active ? 'bg-terracotta-tint text-terracotta-ink !font-extrabold' : 'text-ink-soft hover:text-ink hover:bg-ink/5' }}">
                    <x-icon :name="$icon" size="17" />{{ $label }}
                    @if($id === 'cerita' && $unread > 0)<span class="min-w-5 h-5 px-1.5 rounded-full bg-terracotta-deep text-white text-[11px] font-extrabold flex items-center justify-center leading-none pt-px">{{ $unread }}</span>@endif
                </a>
            @endforeach
        </nav>

        <div class="ml-auto flex items-center gap-2">
            <x-student-actions />
            <a href="{{ route('student.profile') }}" data-tour="nav-profil" aria-label="Profil" title="Profil" class="hidden lg:flex w-11 h-11 rounded-full bg-terracotta-tint border-2 border-white shadow-xs items-center justify-center font-display text-lg text-terracotta-ink no-underline transition hover:-translate-y-0.5">{{ $user->initial() }}</a>
            <div class="relative" x-on:click.outside="menu = false">
                <button type="button" data-tour="menu" x-on:click="menu = !menu" :aria-expanded="menu" aria-controls="student-menu" aria-label="Menu lainnya" class="head-btn" :class="menu && '!bg-cream-light !border-ink-soft/50'">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h10"/></svg>
                </button>
                <div id="student-menu" x-show="menu" x-cloak x-transition:enter="transition duration-200 ease-[cubic-bezier(.22,1,.36,1)]" x-transition:enter-start="opacity-0 scale-95 -translate-y-1" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 top-[calc(100%+10px)] w-[min(260px,calc(100vw-32px))] origin-top-right bg-white border border-line rounded-[22px] shadow-modal p-2 flex flex-col">
                    <span class="px-2.5 pt-1.5 pb-2 text-[11px] font-extrabold tracking-[0.08em] uppercase text-ink-soft">Lainnya</span>
                    @foreach($extra as [$id, $label, $route, $icon])
                        <a href="{{ route($route) }}" aria-current="{{ $id === $active ? 'page' : 'false' }}" class="flex items-center gap-3 h-11 px-2.5 rounded-[14px] no-underline text-[15px] font-bold transition-colors {{ $id === $active ? 'bg-terracotta-tint text-terracotta-ink !font-extrabold' : 'text-ink hover:bg-cream-light' }}">
                            <span class="w-8 h-8 flex-none rounded-[10px] flex items-center justify-center {{ $id === $active ? 'bg-white/70' : 'bg-cream-light' }}"><x-icon :name="$icon" size="17" /></span>{{ $label }}
                        </a>
                    @endforeach
                    <span class="my-1.5 mx-2.5 h-px bg-line-soft"></span>
                    <form method="POST" action="{{ route('logout') }}" data-logout>@csrf<button class="w-full flex items-center gap-3 h-11 px-2.5 rounded-[14px] no-underline text-[15px] font-bold transition-colors text-ink-soft hover:bg-cream-light"><span class="w-8 h-8 flex-none rounded-[10px] flex items-center justify-center"><x-icon name="logout" size="17" /></span>Keluar Akun</button></form>
                </div>
            </div>
        </div>
    </div>
</div>
