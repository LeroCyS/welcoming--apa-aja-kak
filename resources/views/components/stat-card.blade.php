@props(['label', 'value', 'hint' => null, 'tone' => 'sage', 'delta' => null])
<div {{ $attributes->merge(['class' => 'card p-5 flex flex-col gap-2']) }}>
    <span class="text-[13px] font-extrabold text-ink-soft">{{ $label }}</span>
    <span class="font-display text-[34px] leading-none">{{ $value }}</span>
    @if(! is_null($delta))
        <span class="text-[13px] font-bold {{ $delta > 0 ? 'text-terracotta-ink' : 'text-sage-ink2' }}">{{ $delta > 0 ? '▲' : ($delta < 0 ? '▼' : '•') }} {{ $hint }}</span>
    @elseif($hint)
        <span class="text-[13px] font-bold text-ink-soft">{{ $hint }}</span>
    @endif
</div>
