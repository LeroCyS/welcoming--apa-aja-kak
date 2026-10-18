@props(['mood' => 'happy', 'size' => 30, 'stroke' => 2, 'color' => '#3E3530'])
@php
    $faces = [
        'happy' => ['', 'M14 24q6 5 12 0'],
        'neutral' => ['', 'M14 26h12'],
        'sad' => ['', 'M14 28q6-4 12 0'],
        'anxious' => ['M12 13l4-1.5M28 13l-4-1.5', 'M13 27q1.75-2 3.5 0t3.5 0t3.5 0t3.5 0'],
        'angry' => ['M12 11.5l4 2M28 11.5l-4 2', 'M14 28q6-3 12 0'],
    ];
    [$brow, $mouth] = $faces[$mood] ?? $faces['neutral'];
@endphp
<svg {{ $attributes->merge(['aria-hidden' => 'true', 'class' => 'flex-none']) }} width="{{ $size }}" height="{{ $size }}" viewBox="0 0 40 40" fill="none" stroke="{{ $color }}" stroke-width="{{ $stroke }}" stroke-linecap="round"><circle cx="20" cy="20" r="16"/><circle cx="14" cy="17" r="1.4" fill="{{ $color }}"/><circle cx="26" cy="17" r="1.4" fill="{{ $color }}"/>@if($brow)<path d="{{ $brow }}"/>@endif<path d="{{ $mouth }}"/></svg>
