@props(['show' => 'open'])
<div x-show="{{ $show }}" x-cloak class="scrim fixed inset-0 z-50 flex items-end lg:items-center justify-center lg:p-6" x-transition.opacity.duration.200ms>
    <section role="dialog" aria-modal="true" {{ $attributes->merge(['class' => 'relative w-full max-w-[520px] bg-cream rounded-t-[32px] lg:rounded-[32px] shadow-modal border border-white/70 px-6 pt-3 pb-8 lg:p-8 animate-pop']) }}>
        <div class="lg:hidden mx-auto mb-4 w-12 h-1.5 rounded-full bg-line"></div>
        {{ $slot }}
    </section>
</div>
