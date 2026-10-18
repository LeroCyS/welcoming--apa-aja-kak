@extends('layouts.student', ['active' => 'selfhelp'])
@section('title', $article->title)
@section('header')
<header class="flex items-center justify-between gap-4 flex-wrap no-print">
    <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-[15px] font-bold min-w-0">
        <a href="{{ route('student.selfhelp.index') }}" class="inline-flex items-center gap-1.5 text-ink-soft no-underline"><x-icon name="arrow-left" size="18" /><span class="hidden sm:inline">Ruang Self-Help</span><span class="sm:hidden">Kembali</span></a>
        <span class="hidden sm:inline text-[#B9AFA0]">/</span><span class="hidden sm:inline truncate">{{ $article->category->name }}</span>
    </nav>
</header>
@endsection
@section('content')
<div class="grid gap-10 items-start lg:grid-cols-[minmax(0,1fr)_340px] 2xl:grid-cols-[minmax(0,1fr)_400px]">
    <article class="min-w-0 flex flex-col gap-5">
        <div class="rounded-[32px] overflow-hidden"><x-article-cover :article="$article" height="h-[200px] sm:h-[300px] 2xl:h-[360px]" :icon-size="160" /></div>
        <div class="flex gap-2.5 flex-wrap items-center">
            <span class="px-3.5 py-1.5 rounded-full bg-sand-tint text-sand-ink text-[13px] font-extrabold whitespace-nowrap">{{ $article->category->name }}</span>
            @if($article->type === 'infographic')<span class="px-3.5 py-1.5 rounded-full bg-sage-tint text-sage-ink2 text-[13px] font-extrabold whitespace-nowrap">Infografis</span>@endif
            <span class="text-sm text-ink-soft">{{ $article->read_minutes }} menit baca · {{ $article->published_at->translatedFormat('j M Y') }}</span>
        </div>
        <h1 class="text-[32px] sm:text-[44px] leading-[1.12] text-balance">{{ $article->title }}</h1>
        {{-- Body sudah disanitasi HTML Purifier saat disimpan --}}
        <div class="prose-wl sm:text-[19px] max-w-[960px]">{!! $article->body !!}</div>
        <a href="{{ route('student.conversations.create') }}" class="mt-2 no-underline text-white bg-terracotta-deep rounded-panel px-6 sm:px-8 py-6 sm:py-7 flex items-center justify-between gap-5 flex-wrap">
            <span class="flex flex-col gap-1.5"><span class="font-display text-2xl sm:text-[26px] leading-tight">Masih kepikiran? Ceritakan ke Guru BK</span><span class="text-[15px] text-[#FBE9E5]">Anonim, dan kamu bisa berhenti kapan saja.</span></span>
            <span class="inline-flex items-center h-[50px] px-6 rounded-full bg-white text-terracotta-ink text-base font-extrabold whitespace-nowrap">Mulai Cerita</span>
        </a>
    </article>

    <aside aria-label="Info artikel" class="flex flex-col gap-[18px] lg:sticky lg:top-6">
        <div class="bg-white rounded-card-l p-5 flex flex-col gap-3 shadow-soft">
            <span class="text-xs font-extrabold tracking-[0.08em] uppercase text-ink-soft">Ditulis oleh</span>
            <span class="flex items-center gap-3"><x-counselor-avatar :counselor="$article->author" :size="48" /><span class="flex flex-col"><span class="font-extrabold text-[15px]">{{ $article->author->displayName() }}</span><span class="text-[13px] text-ink-soft">Guru BK{{ $article->author->counselorProfile?->hobbies ? ' · suka '.mb_strtolower(explode(' & ', $article->author->counselorProfile->hobbies)[0]) : '' }}</span></span></span>
            <a href="{{ route('student.counselors') }}" class="text-sm font-extrabold text-sage-ink2">Kenalan dengan {{ $article->author->displayName() }}</a>
        </div>
        @if($related->isNotEmpty())
            <div class="flex flex-col gap-2.5">
                <span class="text-base font-extrabold">Bacaan terkait</span>
                @foreach($related as $r)
                    <a href="{{ route('student.selfhelp.show', $r) }}" class="no-underline text-ink bg-white rounded-card p-3 flex gap-3 items-center">
                        <span class="w-[60px] h-[60px] flex-none rounded-2xl overflow-hidden"><x-article-cover :article="$r" height="h-[60px]" :icon-size="34" /></span>
                        <span class="flex flex-col gap-1"><span class="text-[11px] font-extrabold uppercase tracking-[0.06em] text-[#5A4E47]">{{ $r->category->name }}</span><span class="font-extrabold text-sm leading-tight">{{ $r->title }}</span></span>
                    </a>
                @endforeach
            </div>
        @endif
    </aside>
</div>
@endsection
