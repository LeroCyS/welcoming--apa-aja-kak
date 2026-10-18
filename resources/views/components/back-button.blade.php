{{-- Tombol kembali. Dengan href: ke halaman induk. Tanpa href: ke halaman sebelumnya di Welcoming, atau ke $fallback bila dibuka langsung. --}}
@props(['href' => null, 'fallback' => null, 'label' => 'Kembali'])
@if($href)
    <a href="{{ $href }}" aria-label="{{ $label }}" title="{{ $label }}" {{ $attributes->merge(['class' => 'head-btn !w-10 !h-10']) }}><x-icon name="arrow-left" size="18" /></a>
@else
    <button type="button" aria-label="{{ $label }}" title="{{ $label }}"
            x-data x-on:click="document.referrer.startsWith(location.origin) && history.length > 1 ? history.back() : (location.href = @js($fallback ?? url('/')))"
            {{ $attributes->merge(['class' => 'head-btn !w-10 !h-10']) }}><x-icon name="arrow-left" size="18" /></button>
@endif
