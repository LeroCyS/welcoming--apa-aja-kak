@extends('layouts.student', ['active' => 'selfhelp'])
@section('title', 'Ruang Self-Help')
@section('header')
<header class="flex flex-col gap-1 no-print">
    <span class="text-[15px] font-bold text-ink-soft">Bacaan singkat dari Guru BK sekolahmu</span>
    <h1 class="text-[32px] lg:text-[38px] leading-[1.15]">Ruang Self-Help</h1>
</header>
@endsection
@section('content')
<div x-data="{ loading: false }" class="flex flex-col gap-7">
    <form method="GET" class="flex flex-col gap-3" x-on:submit="loading = true">
        <div class="relative max-w-[560px]">
            <label for="sh-q" class="sr-only">Cari bacaan</label>
            <x-icon name="search" size="20" class="absolute left-5 top-1/2 -translate-y-1/2 text-ink-soft pointer-events-none" />
            <input id="sh-q" name="q" type="search" value="{{ $q }}" placeholder="Cari: susah tidur, grogi, ujian…" class="w-full h-14 pl-[52px] pr-5 rounded-full border-[1.5px] border-line bg-white text-[15px] text-ink shadow-xs transition focus:outline-none focus:border-ink-soft focus:shadow-soft">
            @if($category)<input type="hidden" name="kategori" value="{{ $category->slug }}">@endif
        </div>
        <div role="group" aria-label="Kategori" class="flex gap-2 overflow-x-auto -mx-5 px-5 lg:mx-0 lg:px-0 pb-1 [scrollbar-width:none]">
            <a href="{{ route('student.selfhelp.index', array_filter(['q' => $q])) }}" x-on:click="loading = true" aria-pressed="{{ $category ? 'false' : 'true' }}" class="flex-none inline-flex items-center h-10 px-4 rounded-full text-sm no-underline transition-colors {{ ! $category ? 'bg-ink text-cream font-extrabold' : 'text-ink-soft font-bold hover:bg-ink/5 hover:text-ink' }}">Semua</a>
            @foreach($categories as $c)
                <a href="{{ route('student.selfhelp.index', array_filter(['kategori' => $c->slug, 'q' => $q])) }}" x-on:click="loading = true" aria-pressed="{{ $category?->id === $c->id ? 'true' : 'false' }}" class="flex-none inline-flex items-center h-10 px-4 rounded-full text-sm no-underline whitespace-nowrap transition-colors {{ $category?->id === $c->id ? 'bg-ink text-cream font-extrabold' : 'text-ink-soft font-bold hover:bg-ink/5 hover:text-ink' }}">{{ $c->name }}</a>
            @endforeach
        </div>
    </form>

    <div x-show="loading" x-cloak><x-skeleton :rows="3" /></div>

    <div x-show="!loading" class="flex flex-col gap-7">
        @if($featured)
            <a href="{{ route('student.selfhelp.show', $featured) }}" class="group no-underline text-ink bg-white border border-line-soft rounded-[28px] p-2.5 grid md:grid-cols-[minmax(0,1.1fr)_minmax(0,1fr)] gap-2 shadow-soft lift">
                <div class="relative rounded-[22px] overflow-hidden">
                    <x-article-cover :article="$featured" height="h-[190px] md:h-full md:min-h-[260px]" :icon-size="120" class="transition-transform duration-500 group-hover:scale-[1.03]" />
                    <span class="absolute left-3.5 top-3.5 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/95 text-xs font-extrabold text-terracotta-ink shadow-xs"><x-icon name="heart" size="12" stroke="2.6" />Pilihan minggu ini</span>
                </div>
                <div class="p-4 md:p-6 flex flex-col justify-center gap-3">
                    <span class="text-xs font-extrabold text-ink-soft">{{ $featured->category->name }} · {{ $featured->read_minutes }} menit baca</span>
                    <span class="font-display text-[24px] lg:text-[30px] leading-[1.2] group-hover:text-terracotta-ink transition-colors">{{ $featured->title }}</span>
                    <span class="text-[15px] leading-relaxed text-ink-soft line-clamp-2">{{ $featured->excerpt }}</span>
                    <span class="flex items-center gap-2.5 mt-2">
                        <x-counselor-avatar :counselor="$featured->author" :size="32" :radius="10" />
                        <span class="flex-1 text-sm font-bold truncate">{{ $featured->author->displayName() }}</span>
                        <span class="w-11 h-11 flex-none rounded-full bg-terracotta-deep text-white flex items-center justify-center transition-transform group-hover:translate-x-0.5" aria-hidden="true"><x-icon name="arrow-right" size="18" /></span>
                    </span>
                </div>
            </a>
        @endif

        <section class="flex flex-col gap-4">
            <div class="flex justify-between items-baseline gap-3"><h2 class="!font-sans text-lg font-extrabold">{{ $q !== '' ? 'Hasil untuk “'.$q.'”' : ($category?->name ?? 'Semua bacaan') }}</h2><span class="text-sm font-bold text-ink-soft">{{ $articles->count() }} bacaan</span></div>
            @if($articles->isEmpty())
                <x-empty-state title="Belum ketemu bacaannya" text="Coba kata kunci lain, atau ceritakan langsung ke Guru BK, anonim kok." :cta-href="route('student.conversations.create')" cta-label="Mulai Cerita" />
            @else
                <div class="grid grid-cols-1 sm:grid-cols-[repeat(auto-fill,minmax(min(240px,100%),1fr))] gap-4">
                    @foreach($articles as $a)<x-article-card :article="$a" />@endforeach
                </div>
            @endif
        </section>
    </div>
</div>
@endsection
