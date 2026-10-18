@props(['status'])
@php
    $map = ['new' => ['terracotta', 'Baru'], 'monitoring' => ['sand', 'Dalam Pemantauan'], 'referred_parent' => ['neutral', 'Rujuk ke Orang Tua'], 'resolved' => ['sage', 'Selesai']];
    [$tone, $label] = $map[$status] ?? ['neutral', $status];
@endphp
<x-badge :tone="$tone" {{ $attributes }}>{{ $label }}</x-badge>
