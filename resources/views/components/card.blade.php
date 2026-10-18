@props(['padding' => 'p-5', 'as' => 'div'])
<{{ $as }} {{ $attributes->merge(['class' => "card $padding"]) }}>{{ $slot }}</{{ $as }}>
