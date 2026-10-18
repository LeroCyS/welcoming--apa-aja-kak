{{-- Modal: overlay cokelat 48%, panel cream radius 28-32. Buka via $dispatch('open-modal', 'nama'). --}}
@props(['name', 'title' => null, 'maxWidth' => 'max-w-lg', 'show' => false])
<div x-data="{ open: @js($show) }" x-on:open-modal.window="if ($event.detail === '{{ $name }}') open = true" x-on:close-modal.window="if ($event.detail === '{{ $name }}' || $event.detail === '*') open = false" x-on:keydown.escape.window="open = false" x-show="open" x-cloak class="scrim fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-6" x-transition.opacity.duration.200ms>
    <section role="dialog" aria-modal="true" @if($title) aria-labelledby="modal-{{ $name }}-title" @endif x-on:click.outside="open = false" x-trap.noscroll="open" {{ $attributes->merge(['class' => "relative w-full $maxWidth bg-cream rounded-t-[28px] sm:rounded-[28px] shadow-modal border border-white/70 p-5 sm:p-7 max-h-[92vh] overflow-y-auto animate-pop"]) }}>
        <button type="button" x-on:click="open = false" aria-label="Tutup" class="absolute right-4 top-4 w-9 h-9 rounded-full bg-white border border-line flex items-center justify-center text-ink shadow-xs transition hover:bg-cream-light"><x-icon name="x" size="20" /></button>
        @if($title)<h2 id="modal-{{ $name }}-title" class="text-[22px] leading-tight pr-12 mb-4">{{ $title }}</h2>@endif
        {{ $slot }}
    </section>
</div>
