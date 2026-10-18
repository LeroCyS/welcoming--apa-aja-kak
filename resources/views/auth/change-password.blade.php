@extends('layouts.public')
@section('title', 'Ganti Password')
@section('content')
<div class="relative min-h-screen flex items-center justify-center px-5 py-12 overflow-hidden">
    <main class="relative w-full max-w-[480px] flex flex-col gap-6">
        <a href="{{ route('landing') }}" aria-label="Welcoming" class="flex items-center no-underline text-ink self-center">
            <img src="{{ asset('images/welcoming-mark.png') }}" alt="" class="w-[60px] h-auto">
            <span class="font-display text-2xl -ml-0.5" aria-hidden="true">elcoming</span>
        </a>
        <div class="bg-white border border-line-soft rounded-panel p-6 sm:p-9 flex flex-col gap-5 shadow-lift animate-rise">
            <div class="flex flex-col gap-1.5">
                <h1 class="text-[30px] leading-tight">{{ $forced ? 'Buat password barumu dulu, ya' : 'Ganti password' }}</h1>
                <p class="text-[15px] leading-relaxed text-ink-soft">
                    @if($forced)
                        Ini pertama kalinya kamu masuk (atau password-mu baru saja direset). Supaya akunmu aman, ganti password bawaan dengan yang hanya kamu tahu.
                    @else
                        Pilih password yang mudah kamu ingat tapi sulit ditebak orang lain.
                    @endif
                </p>
            </div>
            <form method="POST" action="{{ route('password.change.update') }}" class="flex flex-col gap-4">
                @csrf
                @unless($forced)
                    <x-input name="current_password" type="password" label="Password lama" autocomplete="current-password" required />
                @endunless
                <x-input name="password" type="password" label="Password baru" hint="Minimal 8 karakter dan tidak sama dengan NIP/kode akun." autocomplete="new-password" required />
                <x-input name="password_confirmation" type="password" label="Ulangi password baru" autocomplete="new-password" required />
                <div class="flex gap-3 p-4 rounded-2xl bg-sage-tint/70 border border-sage-tint text-sm leading-relaxed text-sage-ink">
                    <x-icon name="lock" size="20" class="mt-px" />
                    <span>Jangan bagikan password ke siapa pun. Guru BK dan admin tidak akan memintanya.</span>
                </div>
                @php $btn = ['student' => 'btn-primary', 'counselor' => 'btn-sage', 'admin' => 'btn-sand'][auth()->user()->role]; @endphp
                <button type="submit" class="btn {{ $btn }} btn-lg w-full">Simpan password baru</button>
            </form>
        </div>
        <form method="POST" action="{{ route('logout') }}" data-logout class="text-center">@csrf<button class="text-sm font-bold text-ink-soft underline">Keluar akun</button></form>
    </main>
</div>
@endsection
