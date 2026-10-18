{{-- Sidebar Guru BK / Admin. Desktop: bisa diciutkan jadi ikon saja (kelas `sb-mini` di <html>, disimpan per perangkat). Layar sempit: menu lipat. --}}
@props(['role' => 'counselor', 'active' => 'dashboard', 'badge' => 0])
@php
    if ($role === 'counselor') {
        $items = [
            ['dashboard', 'Dashboard', 'counselor.dashboard', 'dashboard'],
            ['inbox', 'Inbox', 'counselor.inbox', 'chat'],
            ['kasus', 'Manajemen Kasus', 'counselor.cases', 'kanban'],
            ['jadwal', 'Jadwal', 'counselor.schedule', 'calendar'],
            ['selfhelp', 'Self-Help', 'counselor.selfhelp.index', 'book'],
            ['profil', 'Profil', 'counselor.profile', 'person'],
        ];
        $on = '!bg-sage-tint !text-sage-ink !font-extrabold';
        $tagline = 'RUANG GURU BK'; $taglineClass = 'text-sage-ink2'; $home = route('counselor.dashboard');
    } else {
        $items = [
            ['dashboard', 'Dashboard', 'admin.dashboard', 'dashboard'],
            ['akun', 'Kelola Akun', 'admin.accounts', 'people'],
            ['pengaturan', 'Pengaturan Sekolah', 'admin.settings', 'settings'],
        ];
        $on = '!bg-sand-tint !text-sand-ink2 !font-extrabold';
        $tagline = 'ADMIN SEKOLAH'; $taglineClass = 'text-sand-ink'; $home = route('admin.dashboard');
    }
@endphp
<nav aria-label="{{ $role === 'counselor' ? 'Navigasi Guru BK' : 'Navigasi Admin Sekolah' }}" class="lg:h-full flex flex-col gap-5" x-data="{ menu: false }">
    <div class="flex items-center justify-between gap-2 sb-head">
        <x-logo :href="$home" :tagline="$tagline" :taglineClass="$taglineClass" :size="40" class="sb-logo !px-1" />
        <button type="button" class="hidden lg:inline-flex icon-btn !w-8 !h-8 !border-transparent hover:!bg-ink/5" onclick="window.wlToggleSidebar()" aria-label="Ciutkan / lebarkan menu" title="Ciutkan / lebarkan menu"><x-icon name="sidebar" size="18" /></button>
        <button type="button" class="lg:hidden icon-btn" x-on:click="menu = !menu" :aria-expanded="menu" aria-label="Buka menu"><x-icon name="more" size="18" /></button>
    </div>
    <div class="flex-col gap-0.5 lg:flex" :class="menu ? 'flex' : 'hidden'">
        @foreach($items as [$id, $label, $route, $icon])
            <a href="{{ route($route) }}" title="{{ $label }}" aria-current="{{ $id === $active ? 'page' : 'false' }}" class="nav-item relative {{ $id === $active ? $on : '' }}">
                <x-icon :name="$icon" size="19" /><span class="flex-1 sb-label">{{ $label }}</span>
                @if($id === 'inbox' && $badge > 0)<span class="sb-badge min-w-5 h-5 px-1.5 rounded-full bg-terracotta-deep text-white text-[11px] font-extrabold flex items-center justify-center shadow-xs leading-none pt-px">{{ $badge }}</span>@endif
            </a>
        @endforeach
        <form method="POST" action="{{ route('logout') }}" data-logout class="lg:hidden">@csrf<button class="nav-item w-full"><x-icon name="logout" size="19" /><span>Keluar Akun</span></button></form>
    </div>
    <div class="mt-auto hidden lg:flex flex-col gap-0.5 pt-3 border-t border-line-soft">
        @if($role === 'counselor')
            <a href="{{ route('emergency') }}" title="Kontak darurat sekolah" class="nav-item !text-danger-ink hover:!bg-danger-tint/60"><x-icon name="phone" size="19" /><span class="sb-label">Kontak darurat</span></a>
        @else
            <span class="sb-label px-3 pb-2 text-xs leading-normal text-ink-soft">Admin tidak bisa membuka isi percakapan murid.</span>
        @endif
        <form method="POST" action="{{ route('logout') }}" data-logout>@csrf<button class="nav-item w-full text-ink-soft" title="Keluar Akun"><x-icon name="logout" size="19" /><span class="sb-label">Keluar Akun</span></button></form>
    </div>
</nav>
