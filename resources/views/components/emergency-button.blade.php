@props(['compact' => false])
@if($compact)
    <a href="{{ route('emergency') }}" aria-label="Butuh Bantuan Sekarang" {{ $attributes->merge(['class' => 'head-btn !border-danger-line !text-danger hover:!bg-danger-tint']) }}><x-icon name="phone" size="20" /></a>
@else
    <a href="{{ route('emergency') }}" {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 h-11 px-3.5 rounded-full border-[1.5px] border-danger-line bg-white text-danger no-underline text-sm font-extrabold whitespace-nowrap shadow-xs transition hover:bg-danger-tint']) }}><x-icon name="phone" size="18" />Butuh Bantuan</a>
@endif
