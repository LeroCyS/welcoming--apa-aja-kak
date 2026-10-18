@props(['active' => 'beranda'])
@php
    $items = [
        ['beranda', 'Beranda', 'student.home', 'home'],
        ['cerita', 'Cerita', 'student.conversations.index', 'chat'],
        ['mood', 'Mood', 'student.mood.index', 'mood'],
        ['selfhelp', 'Self-Help', 'student.selfhelp.index', 'book'],
        ['profil', 'Profil', 'student.profile', 'person'],
    ];
@endphp
<nav aria-label="Navigasi murid" class="lg:hidden fixed inset-x-3 bottom-[max(10px,env(safe-area-inset-bottom))] z-40 glass border border-line/80 rounded-[26px] p-1.5 grid grid-cols-5 gap-0.5 shadow-lift no-print">
    @foreach($items as [$id, $label, $route, $icon])
        @php $on = $id === $active; @endphp
        <a href="{{ route($route) }}" data-tour="nav-{{ $id }}" aria-current="{{ $on ? 'page' : 'false' }}" class="group h-[58px] flex flex-col items-center justify-center gap-1 no-underline text-[11px] rounded-[20px] transition-colors {{ $on ? 'text-terracotta-ink font-extrabold bg-terracotta-tint' : 'text-ink-soft font-bold hover:text-ink active:bg-ink/5' }}">
            <span class="transition-transform duration-200 {{ $on ? '-translate-y-px' : 'group-active:scale-90' }}"><x-icon :name="$icon" size="22" /></span>
            <span class="leading-none">{{ $label }}</span>
        </a>
    @endforeach
</nav>
