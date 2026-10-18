@props(['score' => 0])
@if($score >= 60)
    <span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-extrabold bg-danger-tint text-danger-ink']) }}><x-icon name="alert" size="13" />Kritis · {{ $score }}</span>
@elseif($score >= 30)
    <span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-extrabold bg-terracotta-tint text-terracotta-ink']) }}>Tinggi · {{ $score }}</span>
@endif
