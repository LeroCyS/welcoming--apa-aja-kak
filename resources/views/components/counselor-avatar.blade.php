@props(['counselor', 'size' => 48, 'radius' => 16, 'online' => null])
@php
    $profile = $counselor->counselorProfile;
    $palette = [['#E4EADF', '#46593B'], ['#F3E6D0', '#6F5229'], ['#F6DDD7', '#8E3E2F']];
    [$bg, $fg] = $palette[$counselor->id % 3];
@endphp
<span {{ $attributes->merge(['class' => 'relative inline-flex items-center justify-center flex-none font-display overflow-visible']) }} style="width: {{ $size }}px; height: {{ $size }}px; border-radius: {{ $radius }}px; background: {{ $bg }}; color: {{ $fg }}; font-size: {{ round($size * 0.42) }}px">
    @if($profile?->photoUrl())
        <img src="{{ $profile->photoUrl() }}" alt="" class="w-full h-full object-cover" style="border-radius: {{ $radius }}px">
    @else
        <span aria-hidden="true">{{ $counselor->initial() }}</span>
    @endif
    @if(! is_null($online))
        {{-- Di halaman murid, $store.presence memperbarui titik status tanpa refresh. --}}
        <span class="absolute -right-0.5 -bottom-0.5 w-3.5 h-3.5 rounded-full border-2 border-white {{ $online ? 'status-on' : 'status-off' }}" aria-label="{{ $online ? 'Online' : 'Offline' }}"
            x-data x-bind:class="{ 'status-on': $store.presence.is({{ $counselor->id }}, {{ $online ? 'true' : 'false' }}), 'status-off': !$store.presence.is({{ $counselor->id }}, {{ $online ? 'true' : 'false' }}) }"
            x-bind:aria-label="$store.presence.is({{ $counselor->id }}, {{ $online ? 'true' : 'false' }}) ? 'Online' : 'Offline'"></span>
    @endif
</span>
