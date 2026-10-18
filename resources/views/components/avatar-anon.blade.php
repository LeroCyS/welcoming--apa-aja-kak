@props(['avatar' => 'kucing--oranye', 'size' => 44, 'radius' => 16, 'name' => null])
@php
    if ($name) {
        $bg = '#E4EADF'; $fg = '#46593B';
        $initial = mb_strtoupper(mb_substr(preg_replace('/^(Bu|Pak)\s+/i', '', $name), 0, 1));
    } else {
        [$bg, $fg] = \App\Support\AliasGenerator::colorFor($avatar);
        $initial = \App\Support\AliasGenerator::initialFor($avatar);
    }
@endphp
<span {{ $attributes->merge(['class' => 'relative inline-flex items-center justify-center flex-none font-display']) }} style="width: {{ $size }}px; height: {{ $size }}px; border-radius: {{ $radius }}px; background: {{ $bg }}; color: {{ $fg }}; font-size: {{ round($size * 0.43) }}px" aria-hidden="true">{{ $initial }}{{ $slot }}</span>
