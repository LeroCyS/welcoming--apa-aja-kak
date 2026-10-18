@props(['tone' => 'neutral', 'dot' => false, 'icon' => null])
@php
    $tones = [
        'neutral' => 'bg-cream text-ink',
        'sage' => 'bg-sage-tint text-sage-ink2',
        'sand' => 'bg-sand-tint text-sand-ink',
        'terracotta' => 'bg-terracotta-tint text-terracotta-ink',
        'danger' => 'bg-danger-tint text-danger-ink',
        'dark' => 'bg-ink text-cream',
        'white' => 'bg-white border border-line text-ink',
    ];
    $dots = ['sage' => 'bg-sage-deep', 'sand' => 'bg-sand-dot', 'terracotta' => 'bg-terracotta-deep', 'danger' => 'bg-danger', 'neutral' => 'bg-ink-soft', 'white' => 'bg-sage-deep', 'dark' => 'bg-cream'];
@endphp
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[13px] font-extrabold whitespace-nowrap '.($tones[$tone] ?? $tones['neutral'])]) }}>
    @if($dot)<span class="w-2 h-2 rounded-full {{ $dots[$tone] ?? 'bg-ink-soft' }}"></span>@endif
    @if($icon)<x-icon :name="$icon" size="14" />@endif
    {{ $slot }}
</span>
