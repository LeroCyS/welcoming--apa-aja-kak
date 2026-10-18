{{-- Ilustrasi sampul artikel (line art per kategori) bila tidak ada cover. --}}
@props(['article', 'height' => 'h-[110px]', 'iconSize' => 56])
@php
    $slug = $article->category?->slug;
    $map = [
        'stres-ujian' => ['#F3E6D0', '#8A6A3B', '#6F5229', '<circle cx="24" cy="26" r="12"/><path d="M24 20v6l4 3"/><path d="M18 9h12M24 9v5"/>'],
        'overthinking' => ['#E4EADF', '#5C7350', '#46593B', '<path d="M10 30c4-8 10-12 14-12s10 4 14 12"/><path d="M14 24c-2-4-2-8 0-11M34 24c2-4 2-8 0-11"/><circle cx="24" cy="34" r="3"/>'],
        'pertemanan' => ['#F6DDD7', '#B5523F', '#8E3E2F', '<circle cx="17" cy="20" r="6"/><circle cx="31" cy="20" r="6"/><path d="M8 38c1.5-6 5-9 9-9s7.5 3 9 9M22 38c1.5-6 5-9 9-9s7.5 3 9 9"/>'],
        'keluarga' => ['#F6DDD7', '#B5523F', '#8E3E2F', '<path d="M8 22L24 10l16 12v16H8z"/><path d="M20 38v-9h8v9"/>'],
        'manajemen-waktu' => ['#EDE5D0', '#8A6A3B', '#6F5229', '<rect x="9" y="11" width="30" height="28" rx="6"/><path d="M9 19h30M17 7v7M31 7v7M18 28l4 4 8-8"/>'],
        'percaya-diri' => ['#E4EADF', '#5C7350', '#46593B', '<path d="M24 8l4.5 9.5L39 19l-7.5 7 2 10.5L24 31.5 14.5 36.5l2-10.5L9 19l10.5-1.5z"/>'],
    ];
    [$bg, $stroke, $ink, $svg] = $map[$slug] ?? ['#EDE5D0', '#8A6A3B', '#6F5229', '<path d="M24 14c-4-3-10-4-15-3v24c5-1 11 0 15 3 4-3 10-4 15-3V11c-5-1-11 0-15 3z"/><path d="M24 14v24"/>'];
@endphp
@if($article->coverUrl())
    <img src="{{ $article->coverUrl() }}" alt="" {{ $attributes->merge(['class' => "$height w-full object-cover"]) }}>
@else
    <div {{ $attributes->merge(['class' => "$height w-full flex items-center justify-center"]) }} style="background: {{ $bg }}"><svg width="{{ $iconSize }}" height="{{ $iconSize }}" viewBox="0 0 48 48" fill="none" stroke="{{ $stroke }}" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $svg !!}</svg></div>
@endif
