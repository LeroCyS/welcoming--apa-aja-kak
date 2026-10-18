@props(['title', 'subtitle' => null])
<header {{ $attributes->merge(['class' => 'flex items-center justify-between gap-4 flex-wrap']) }}>
    <div class="flex flex-col gap-1 min-w-0">
        <h1 class="text-[28px] lg:text-[34px] leading-tight">{{ $title }}</h1>
        @if($subtitle)<p class="text-[15px] lg:text-base text-ink-soft">{{ $subtitle }}</p>@endif
    </div>
    {{ $slot }}
</header>
