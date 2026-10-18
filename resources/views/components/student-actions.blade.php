<div {{ $attributes->merge(['class' => 'flex gap-2 justify-end items-center no-print']) }}>
    {{-- Ulangi walkthrough: di Beranda langsung dimulai, dari halaman lain pindah ke Beranda lalu dimulai. --}}
    @if(request()->routeIs('student.home'))
        <button type="button" data-tour="ulang-tur" x-data x-on:click="$dispatch('tour-start')" aria-label="Ulangi tur aplikasi" title="Ulangi tur aplikasi" class="head-btn"><x-icon name="help" size="20" /></button>
    @elseif(request()->routeIs('student.*'))
        <a href="{{ route('student.home', ['tur' => 1]) }}" aria-label="Ulangi tur aplikasi" title="Ulangi tur aplikasi" class="head-btn"><x-icon name="help" size="20" /></a>
    @endif
    @auth<div data-tour="notif" class="rounded-full"><x-notification-bell /></div>@endauth
    <div data-tour="darurat" class="rounded-full"><x-emergency-button compact /></div>
    <div data-tour="keluar-cepat" class="rounded-full"><x-quick-exit /></div>
</div>
