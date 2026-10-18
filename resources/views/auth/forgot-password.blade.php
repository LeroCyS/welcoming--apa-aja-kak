@extends('layouts.public')
@section('title', 'Lupa Password')
@section('content')
<div class="relative min-h-screen flex items-center justify-center px-5 py-12 overflow-hidden">
    <main class="relative w-full max-w-[480px] flex flex-col gap-6">
        <a href="{{ route('landing') }}" aria-label="Welcoming" class="flex items-center no-underline text-ink self-center">
            <img src="{{ asset('images/welcoming-mark.png') }}" alt="" class="w-[60px] h-auto">
            <span class="font-display text-2xl -ml-0.5" aria-hidden="true">elcoming</span>
        </a>
        <div class="bg-white border border-line-soft rounded-panel p-6 sm:p-9 flex flex-col gap-5 shadow-lift animate-rise">
            <div class="flex flex-col gap-1.5">
                <h1 class="text-[30px] leading-tight">Lupa password?</h1>
                <p class="text-[15px] leading-relaxed text-ink-soft">Masukkan email akun murid-mu. Kami kirim tautan untuk membuat password baru.</p>
            </div>
            <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-4" novalidate>
                @csrf
                <x-input name="email" type="email" label="Email" autocomplete="email" required autofocus />
                <button type="submit" class="btn btn-primary btn-lg w-full">Kirim tautan</button>
            </form>
            <p class="text-sm text-ink-soft leading-relaxed">Guru BK atau admin? Minta admin sekolah untuk mereset password-mu.</p>
        </div>
        <a href="{{ route('login') }}" class="text-center text-[15px] font-extrabold text-terracotta-deep no-underline">Kembali ke halaman masuk</a>
    </main>
</div>
@endsection
