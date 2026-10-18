@extends('layouts.student', ['active' => 'cerita', 'fullScreen' => true])
@section('title', 'Cerita')
@section('header')
<h1 class="sr-only">Cerita</h1>
@endsection
@section('content')
{{-- Ukuran sama dengan ruang chat: satu kartu besar setinggi layar. --}}
<div class="flex-1 min-h-0 flex bg-white border border-line-soft rounded-[28px] shadow-soft overflow-hidden">
    <aside aria-label="Daftar percakapan" class="w-full lg:w-[300px] xl:w-[340px] flex-none flex flex-col bg-sage-tint lg:border-r border-sage/30">
        <div class="h-[72px] flex-none px-4 flex items-center gap-2 border-b border-sage/30">
            <span class="flex-1 font-display text-[22px] leading-none pl-1">Cerita</span>
            <a href="{{ route('student.conversations.create') }}" class="inline-flex items-center gap-1.5 h-10 px-4 rounded-full bg-terracotta-deep text-white no-underline text-sm font-extrabold transition hover:bg-terracotta-ink"><x-icon name="plus" size="16" />Baru</a>
        </div>
        <div class="flex-1 overflow-y-auto p-2.5">
            @if($conversations->isEmpty())
                <p class="px-3 py-6 text-sm text-center text-ink-soft">Belum ada cerita.</p>
            @else
                <div x-data="storyList(@js(route('student.conversations.feed')))">@include('student.conversations._list')</div>
            @endif
        </div>
        @if(auth()->user()->studentProfile?->pin_enabled)<span class="flex-none flex items-center gap-1.5 px-5 py-3 border-t border-sage/30 text-xs font-bold text-ink-soft"><x-icon name="lock" size="13" />Riwayat terkunci PIN</span>@endif
    </aside>
    <section class="chat-canvas hidden lg:flex flex-1 min-w-0 items-center justify-center p-8">
        <div class="max-w-[360px] flex flex-col items-center gap-4 text-center">
            <span class="w-20 h-20 rounded-[26px] bg-terracotta-tint text-terracotta-deep flex items-center justify-center -rotate-3 shadow-soft"><x-icon name="chat-heart" size="40" /></span>
            <span class="font-display text-[26px] leading-tight">{{ $conversations->isEmpty() ? 'Belum ada cerita' : 'Pilih cerita di kiri' }}</span>
            <span class="text-[15px] text-ink-soft">{{ $conversations->isEmpty() ? 'Kapan pun kamu siap, mulai sebagai anonim.' : 'Atau mulai cerita baru, anonim kok.' }}</span>
            <a href="{{ route('student.conversations.create') }}" class="btn btn-primary !h-12 mt-1"><x-icon name="plus" size="18" />Mulai Cerita</a>
        </div>
    </section>
</div>
@endsection
