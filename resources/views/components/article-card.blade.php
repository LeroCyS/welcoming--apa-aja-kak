@props(['article', 'compact' => false])
@php
    $inks = ['stres-ujian' => '#6F5229', 'overthinking' => '#46593B', 'pertemanan' => '#8E3E2F', 'keluarga' => '#8E3E2F', 'manajemen-waktu' => '#6F5229', 'percaya-diri' => '#46593B'];
    $ink = $inks[$article->category?->slug] ?? '#6F5229';
@endphp
<a href="{{ route('student.selfhelp.show', $article) }}" {{ $attributes->merge(['class' => 'group no-underline text-ink bg-white rounded-[24px] p-2 shadow-soft border border-line-soft flex flex-col lift']) }}>
    <div class="relative overflow-hidden rounded-[18px]">
        <x-article-cover :article="$article" :height="$compact ? 'h-[104px]' : 'h-[136px]'" :icon-size="$compact ? 52 : 64" class="transition-transform duration-500 group-hover:scale-[1.05]" />
        @if($article->type === 'infographic')<span class="absolute left-2.5 top-2.5 px-2.5 py-1 rounded-full bg-white/95 shadow-xs text-[11px] font-extrabold whitespace-nowrap">Infografis</span>@endif
    </div>
    <div class="{{ $compact ? 'px-2 pt-2.5 pb-1.5' : 'px-2.5 pt-3 pb-2' }} flex flex-col gap-1.5 flex-1">
        <span class="font-extrabold {{ $compact ? 'text-[15px]' : 'text-base' }} leading-snug group-hover:text-terracotta-ink transition-colors line-clamp-2">{{ $article->title }}</span>
        <span class="flex items-center gap-1.5 text-xs font-bold text-ink-soft mt-auto pt-1"><span class="w-1.5 h-1.5 rounded-full" style="background: {{ $ink }}"></span>{{ $article->category?->name }} · {{ $article->read_minutes }} mnt</span>
    </div>
</a>
