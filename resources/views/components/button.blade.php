@props(['variant' => 'primary', 'size' => null, 'href' => null, 'type' => 'button', 'icon' => null, 'iconRight' => null])
@php
    $classes = 'btn btn-'.$variant.($size ? ' btn-'.$size : '');
@endphp
@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>@if($icon)<x-icon :name="$icon" size="18" />@endif{{ $slot }}@if($iconRight)<x-icon :name="$iconRight" size="16" />@endif</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>@if($icon)<x-icon :name="$icon" size="18" />@endif{{ $slot }}@if($iconRight)<x-icon :name="$iconRight" size="16" />@endif</button>
@endif
