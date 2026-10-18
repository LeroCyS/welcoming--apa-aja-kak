@props(['title', 'text' => null, 'ctaHref' => null, 'ctaLabel' => null, 'variant' => 'primary'])
<div {{ $attributes->merge(['class' => 'flex flex-col items-center text-center gap-3 py-10 px-6']) }}>
    <span class="w-40 h-40 rounded-full bg-cream-deep flex items-center justify-center">
        <img src="{{ asset('images/welcoming-mark.png') }}" alt="" class="relative w-28 h-auto opacity-90">
    </span>
    <h2 class="text-[22px] mt-2">{{ $title }}</h2>
    @if($text)<p class="text-sm leading-relaxed text-ink-soft max-w-sm">{{ $text }}</p>@endif
    {{ $slot }}
    @if($ctaHref)<x-button :href="$ctaHref" :variant="$variant" size="sm" class="mt-1">{{ $ctaLabel }}</x-button>@endif
</div>
