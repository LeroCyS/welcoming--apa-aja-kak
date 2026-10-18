{{-- Kartu lagu YouTube/Spotify. Pemutar baru dimuat saat tombol Putar ditekan (hemat kuota & privasi). --}}
@props(['song', 'caption' => null])
<div x-data="{ playing: false }" {{ $attributes->merge(['class' => 'bg-white border border-line rounded-card overflow-hidden flex flex-col']) }}>
    <template x-if="playing">
        <iframe src="{{ \App\Services\ChatMedia::embedUrl($song) }}" title="Pemutar lagu" class="w-full border-0 {{ $song['provider'] === 'youtube' ? 'aspect-video' : (in_array($song['kind'], ['track', 'episode'], true) ? 'h-[152px]' : 'h-[352px]') }}" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>
    </template>
    <div x-show="!playing" class="flex gap-3 items-center p-3">
        <span class="relative w-14 h-14 flex-none rounded-2xl overflow-hidden bg-cream-deep flex items-center justify-center">
            @if(! empty($song['thumbnail']))<img src="{{ $song['thumbnail'] }}" alt="" loading="lazy" referrerpolicy="no-referrer" class="w-full h-full object-cover">@else<x-icon name="play" size="24" />@endif
        </span>
        <span class="flex-1 min-w-0 flex flex-col gap-0.5">
            <span class="text-[11px] font-extrabold tracking-[0.06em] uppercase {{ $song['provider'] === 'youtube' ? 'text-terracotta-ink' : 'text-sage-ink2' }}">{{ $song['provider'] === 'youtube' ? 'YouTube' : 'Spotify' }}@if($caption) · {{ $caption }}@endif</span>
            <span class="font-extrabold text-sm leading-tight line-clamp-2">{{ $song['title'] ?? 'Lagu' }}</span>
            @if(! empty($song['author']))<span class="text-xs text-ink-soft truncate">{{ $song['author'] }}</span>@endif
        </span>
        <button type="button" x-on:click="playing = true" class="w-11 h-11 flex-none rounded-full bg-sage-deep text-white flex items-center justify-center" aria-label="Putar lagu {{ $song['title'] ?? '' }}"><x-icon name="play" size="18" /></button>
    </div>
    <a href="{{ $song['url'] }}" target="_blank" rel="noopener noreferrer" class="px-3 py-2 border-t border-line-soft text-xs font-extrabold text-ink-soft no-underline inline-flex items-center gap-1.5"><x-icon name="link" size="13" />Buka di {{ $song['provider'] === 'youtube' ? 'YouTube' : 'Spotify' }}</a>
</div>
