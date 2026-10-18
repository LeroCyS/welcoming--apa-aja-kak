@props(['active' => 'beranda', 'unread' => 0])
@php
    $main = [
        ['beranda', 'Beranda', 'student.home', 'home'],
        ['cerita', 'Cerita', 'student.conversations.index', 'chat'],
        ['mood', 'Mood', 'student.mood.index', 'mood'],
        ['selfhelp', 'Self-Help', 'student.selfhelp.index', 'book'],
        ['profil', 'Profil', 'student.profile', 'person'],
    ];
    $extra = [
        ['teman', 'Cerita Teman', 'student.stories.index', 'people'],
        ['jadwal', 'Jadwal Saya', 'student.schedule.index', 'calendar'],
        ['guru', 'Kenalan Guru BK', 'student.counselors', 'people-heart'],
    ];
@endphp
<nav aria-label="Navigasi murid" class="min-h-full flex flex-col gap-6">
    <x-logo :href="route('student.home')" tagline="RUANG CERITAMU" />
    <a href="{{ route('student.conversations.create') }}" class="btn btn-primary w-full !text-[15px]"><x-icon name="plus" size="18" />Mulai Cerita</a>
    <div class="flex flex-col gap-1">
        @foreach($main as [$id, $label, $route, $icon])
            @php $on = $id === $active; @endphp
            <a href="{{ route($route) }}" data-tour="nav-{{ $id }}" aria-current="{{ $on ? 'page' : 'false' }}" class="nav-item {{ $on ? '!bg-terracotta-tint !text-terracotta-ink !font-extrabold' : '' }}">
                <x-icon :name="$icon" size="20" /><span class="flex-1">{{ $label }}</span>
                @if($id === 'cerita' && $unread > 0)<span class="min-w-[22px] h-[22px] px-1.5 rounded-full bg-terracotta-deep text-white text-xs font-extrabold flex items-center justify-center leading-none pt-px">{{ $unread }}</span>@endif
            </a>
        @endforeach
    </div>
    <div class="flex flex-col gap-1">
        <span class="px-3 pb-1 text-[11px] font-extrabold tracking-[0.1em] uppercase text-ink-soft">Lainnya</span>
        @foreach($extra as [$id, $label, $route, $icon])
            @php $on = $id === $active; @endphp
            <a href="{{ route($route) }}" data-tour="nav-{{ $id }}" aria-current="{{ $on ? 'page' : 'false' }}" class="nav-item {{ $on ? '!bg-terracotta-tint !text-terracotta-ink !font-extrabold' : '' }}"><x-icon :name="$icon" size="20" /><span class="flex-1">{{ $label }}</span></a>
        @endforeach
    </div>
    <div class="flex flex-col gap-1">
        <span class="px-3 pb-1 text-[11px] font-extrabold tracking-[0.1em] uppercase text-ink-soft">Akun</span>
        <form method="POST" action="{{ route('logout') }}" data-logout>@csrf<button class="nav-item w-full text-ink-soft"><x-icon name="logout" size="20" /><span class="flex-1 text-left">Keluar Akun</span></button></form>
        <p class="mx-1 px-3 py-2.5 rounded-xl bg-cream/80 border border-line-soft text-xs leading-snug text-ink-soft">Ada yang melihat layarmu? Pakai <b>Keluar Cepat</b> di kanan atas, layar langsung berganti dan akunmu terkunci.</p>
    </div>
    <a href="{{ route('emergency') }}" class="mt-auto no-underline text-ink p-4 rounded-card bg-white border-[1.5px] border-danger-line flex gap-3 items-start lift">
        <span class="tile w-9 h-9 !rounded-xl bg-danger-tint text-danger"><x-icon name="phone" size="18" /></span>
        <span class="flex flex-col gap-0.5">
            <span class="font-extrabold text-sm text-danger-ink">Butuh bantuan sekarang?</span>
            <span class="text-[13px] leading-snug text-ink-soft">Kontak darurat & Guru BK, kapan pun.</span>
        </span>
    </a>
</nav>
