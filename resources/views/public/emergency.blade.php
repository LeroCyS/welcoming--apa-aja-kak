@extends('layouts.public')
@section('title', 'Bantuan Darurat')
@section('content')
<div class="min-h-screen" data-quick-exit-shortcut>
    <div class="h-1.5 bg-danger"></div>
    <nav aria-label="Navigasi" class="container-wl py-[18px] flex items-center justify-between gap-3 flex-wrap">
        <div class="flex items-center gap-3">
            <x-back-button :fallback="auth()->check() ? auth()->user()->homeRoute() : route('landing')" />
            <a href="{{ auth()->check() ? auth()->user()->homeRoute() : route('landing') }}" aria-label="Welcoming" class="flex items-center no-underline text-ink">
                <img src="{{ asset('images/welcoming-mark.png') }}" alt="" class="w-[52px] h-auto">
                <span class="font-display text-[22px] -ml-0.5" aria-hidden="true">elcoming</span>
            </a>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            @guest
                <a href="{{ route('login') }}" class="inline-flex items-center h-11 px-[22px] rounded-full bg-ink text-cream no-underline font-extrabold text-[15px]">Masuk</a>
            @else
                <a href="{{ auth()->user()->homeRoute() }}" class="inline-flex items-center h-11 px-[22px] rounded-full border-[1.5px] border-line bg-white text-ink no-underline font-extrabold text-[15px]">Beranda</a>
            @endguest
            <x-quick-exit class="!px-4" />
        </div>
    </nav>

    <main class="container-wl pt-4 pb-16 flex flex-col gap-9">
        @include('public.partials.emergency-content')
    </main>
</div>
@endsection
