@props(['on' => false, 'dashed' => false])
<span {{ $attributes->merge(['class' => 'chip '.($on ? 'chip-on' : '').($dashed && ! $on ? ' chip-dashed' : '')]) }}>{{ $slot }}</span>
