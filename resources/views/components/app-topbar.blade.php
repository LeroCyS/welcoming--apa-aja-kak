{{-- Topbar Guru BK / Admin: menu utama sebagai pill di desktop, menu lain di dropdown kecil (hamburger). --}}
@props(['role' => 'counselor', 'active' => 'dashboard', 'badge' => 0])
@php
    if ($role === 'counselor') {
        $items = [
            ['dashboard', 'Dashboard', 'counselor.dashboard', 'dashboard', 'Ringkasan & analitik sekolah'],
            ['inbox', 'Inbox', 'counselor.inbox', 'chat', 'Cerita murid, diurutkan dari yang paling mendesak'],
            ['kasus', 'Kasus', 'counselor.cases', 'kanban', 'Kelola status & follow-up kasus'],
            ['jadwal', 'Jadwal', 'counselor.schedule', 'calendar', 'Slot & sesi tatap muka'],
            ['selfhelp', 'Self-Help', 'counselor.selfhelp.index', 'book', 'Tulis & kelola artikel untuk murid'],
            ['profil', 'Profil', 'counselor.profile', 'person', 'Profil yang dilihat murid'],
        ];
        $primary = ['dashboard', 'inbox', 'kasus', 'jadwal'];
        $on = 'bg-sage-tint text-sage-ink';
        $tagline = 'RUANG GURU BK'; $taglineClass = 'text-sage-ink2'; $home = route('counselor.dashboard');
    } else {
        $items = [
            ['dashboard', 'Dashboard', 'admin.dashboard', 'dashboard', 'Ringkasan sekolah'],
            ['akun', 'Kelola Akun', 'admin.accounts', 'people', 'Akun Guru BK & murid'],
            ['pengaturan', 'Pengaturan', 'admin.settings', 'settings', 'Pengaturan sekolah'],
        ];
        $primary = ['dashboard', 'akun', 'pengaturan'];
        $on = 'bg-sand-tint text-sand-ink2';
        $tagline = 'ADMIN SEKOLAH'; $taglineClass = 'text-sand-ink'; $home = route('admin.dashboard');
    }
    $user = auth()->user();
@endphp
{{-- Sama seperti navbar publik: menempel di atas saat awal, lalu berubah jadi pil kaca melayang saat halaman di-scroll. --}}
<div x-data="{ menu: false, scrolled: window.scrollY > 24 }" x-init="scrolled = window.scrollY > 24" x-on:scroll.window.passive="scrolled = window.scrollY > 24" x-on:keydown.escape.window="menu = false" class="topbar sticky top-0 z-40 no-print container-wl pt-3">
    <div class="h-16 w-full max-w-[1760px] mx-auto flex items-center gap-3 rounded-[28px] border border-transparent px-0 animate-[nav-in_.6s_cubic-bezier(.22,1,.36,1)_both] transition-[max-width,padding,background-color,border-color,box-shadow] duration-500 ease-[cubic-bezier(.22,1,.36,1)]" :class="scrolled && 'glass !border-line/70 shadow-lift !max-w-[1280px] !px-3 sm:!px-4'">
        <x-logo :href="$home" :tagline="$tagline" :taglineClass="$taglineClass" :size="40" class="!px-0 flex-none" />

        <nav aria-label="{{ $role === 'counselor' ? 'Navigasi Guru BK' : 'Navigasi Admin Sekolah' }}" class="hidden lg:flex items-center gap-1 ml-6">
            @foreach($items as [$id, $label, $route, $icon])
                @continue(! in_array($id, $primary))
                <a href="{{ route($route) }}" aria-current="{{ $id === $active ? 'page' : 'false' }}" class="relative inline-flex items-center gap-2 h-10 px-4 rounded-full no-underline text-[15px] font-bold transition-colors {{ $id === $active ? $on.' !font-extrabold' : 'text-ink-soft hover:text-ink hover:bg-ink/5' }}">
                    <x-icon :name="$icon" size="17" />{{ $label }}
                    @if($id === 'inbox' && $badge > 0)<span class="min-w-5 h-5 px-1.5 rounded-full bg-terracotta-deep text-white text-[11px] font-extrabold flex items-center justify-center leading-none pt-px">{{ $badge }}</span>@endif
                </a>
            @endforeach
        </nav>

        <div class="ml-auto flex items-center gap-2">
            @if($role === 'counselor')
                <x-counselor-status />
            @else
                {{-- Hanya nama orangnya: keterangan jabatan dalam kurung, mis. "(Admin TU)", tidak ditampilkan. --}}
                <span class="hidden sm:inline font-extrabold text-sm">{{ trim(preg_replace('/\s*\(.*?\)/', '', $user->name)) }}</span>
                <span class="w-9 h-9 rounded-xl bg-sand-tint border border-white shadow-xs flex items-center justify-center font-display text-[19px] text-sand-ink">{{ $user->initial() }}</span>
            @endif
            <div class="relative" x-on:click.outside="menu = false">
                <button type="button" x-on:click="menu = !menu" :aria-expanded="menu" aria-controls="app-menu" aria-label="Buka menu" class="relative head-btn" :class="menu && '!bg-cream-light !border-ink-soft/50'">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h10"/></svg>
                    @if($badge > 0)<span class="lg:hidden absolute -top-0.5 -right-0.5 w-3 h-3 rounded-full bg-terracotta-deep border-2 border-white"></span>@endif
                </button>
                <div id="app-menu" x-show="menu" x-cloak x-transition:enter="transition duration-200 ease-[cubic-bezier(.22,1,.36,1)]" x-transition:enter-start="opacity-0 scale-95 -translate-y-1" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 top-[calc(100%+10px)] w-[min(260px,calc(100vw-32px))] origin-top-right bg-white border border-line rounded-[22px] shadow-modal p-2 flex flex-col">
                    @foreach($items as [$id, $label, $route, $icon])
                        {{-- Menu utama sudah ada di topbar desktop, jadi di sini hanya muncul pada layar kecil. --}}
                        <a href="{{ route($route) }}" aria-current="{{ $id === $active ? 'page' : 'false' }}" class="{{ in_array($id, $primary) ? 'lg:hidden' : '' }} flex items-center gap-3 h-11 px-2.5 rounded-[14px] no-underline text-[15px] font-bold transition-colors {{ $id === $active ? $on.' !font-extrabold' : 'text-ink hover:bg-cream-light' }}">
                            <span class="w-8 h-8 flex-none rounded-[10px] flex items-center justify-center {{ $id === $active ? 'bg-white/70' : 'bg-cream-light' }}"><x-icon :name="$icon" size="17" /></span><span class="flex-1">{{ $label }}</span>
                            @if($id === 'inbox' && $badge > 0)<span class="min-w-5 h-5 px-1.5 rounded-full bg-terracotta-deep text-white text-[11px] font-extrabold flex items-center justify-center leading-none pt-px">{{ $badge }}</span>@endif
                        </a>
                    @endforeach
                    <span class="my-1.5 mx-2.5 h-px bg-line-soft"></span>
                    @if($role === 'counselor')
                        <a href="{{ route('emergency') }}" class="flex items-center gap-3 h-11 px-2.5 rounded-[14px] no-underline text-[15px] font-bold transition-colors text-danger-ink hover:bg-danger-tint/60"><span class="w-8 h-8 flex-none rounded-[10px] flex items-center justify-center bg-danger-tint"><x-icon name="phone" size="17" /></span>Kontak darurat</a>
                    @else
                        <span class="px-2.5 py-1.5 text-xs leading-snug text-ink-soft">Admin tidak bisa membuka isi percakapan murid.</span>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" data-logout>@csrf<button class="w-full flex items-center gap-3 h-11 px-2.5 rounded-[14px] no-underline text-[15px] font-bold transition-colors text-ink-soft hover:bg-cream-light"><span class="w-8 h-8 flex-none rounded-[10px] flex items-center justify-center"><x-icon name="logout" size="17" /></span>Keluar Akun</button></form>
                </div>
            </div>
        </div>
    </div>
</div>
