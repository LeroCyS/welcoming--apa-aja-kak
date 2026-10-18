@extends('layouts.public')
@section('title', 'Buat Password Baru')
@section('content')
<div class="relative min-h-screen flex items-center justify-center px-5 py-12 overflow-hidden">
    <main class="relative w-full max-w-[480px] flex flex-col gap-6">
        <a href="{{ route('landing') }}" aria-label="Welcoming" class="flex items-center no-underline text-ink self-center">
            <img src="{{ asset('images/welcoming-mark.png') }}" alt="" class="w-[60px] h-auto">
            <span class="font-display text-2xl -ml-0.5" aria-hidden="true">elcoming</span>
        </a>
        <div class="bg-white border border-line-soft rounded-panel p-6 sm:p-9 flex flex-col gap-5 shadow-lift animate-rise">
            <h1 class="text-[30px] leading-tight">Buat password baru</h1>
            <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-4" novalidate>
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <x-input name="email" type="email" label="Email" :value="$email" autocomplete="email" required />
                <x-input name="password" type="password" label="Password baru" hint="Minimal 8 karakter." autocomplete="new-password" required />
                <x-input name="password_confirmation" type="password" label="Ulangi password baru" autocomplete="new-password" required />
                <button type="submit" class="btn btn-primary btn-lg w-full">Simpan password</button>
            </form>
        </div>
    </main>
</div>
@endsection
