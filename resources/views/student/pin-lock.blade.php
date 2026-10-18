@extends('layouts.public')
@section('title', 'Terkunci')
@section('content')
{{-- Layar kunci yang netral: tidak menampilkan isi cerita apa pun. Dipakai untuk PIN dan setelah Keluar Cepat. --}}
<div class="min-h-screen flex flex-col" data-quick-exit-shortcut>
    <div class="flex justify-end gap-2 p-5"><x-emergency-button compact /><x-quick-exit /></div>
    <main class="flex-1 flex items-center justify-center px-5 pb-16">
        <div class="w-full max-w-[400px] bg-white border border-line-soft rounded-[32px] p-7 sm:p-9 flex flex-col items-center gap-5 text-center shadow-lift animate-rise" x-data="{ pin: '' }">
            <span class="relative w-[72px] h-[72px] rounded-[24px] bg-terracotta-tint flex items-center justify-center text-terracotta-ink"><x-icon name="lock" size="32" /></span>
            <div class="flex flex-col gap-1.5">
                <h1 class="text-[28px] leading-tight">{{ $mode === 'pin' ? 'Masukkan PIN-mu' : 'Masukkan password-mu' }}</h1>
                <p class="text-[15px] text-ink-soft leading-relaxed">
                    @if($quickLocked)
                        Akunmu dikunci setelah Keluar Cepat. Tidak ada yang bisa membukanya tanpa {{ $mode === 'pin' ? 'PIN' : 'password' }}-mu.
                    @else
                        Cerita, mood, dan jadwalmu dikunci supaya tetap privat.
                    @endif
                </p>
            </div>
            @if($lockedUntil)
                <p class="w-full p-4 rounded-card bg-terracotta-tint text-terracotta-ink text-sm font-bold">PIN salah terlalu sering. Coba lagi {{ $lockedUntil->diffForHumans() }}.</p>
            @endif
            <form method="POST" action="{{ route('student.pin.unlock') }}" class="w-full flex flex-col gap-4" onsubmit="try { sessionStorage.setItem('wl-pin-tab', '1') } catch (e) {}">
                @csrf
                @if($mode === 'pin')
                    <label for="pin" class="sr-only">PIN 6 angka</label>
                    <input id="pin" name="pin" x-model="pin" type="password" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="off" autofocus required class="field !h-16 text-center !text-[28px] tracking-[0.6em] font-extrabold @error('pin') is-invalid @enderror" @disabled($lockedUntil)>
                    <div class="flex justify-center gap-2" aria-hidden="true"><template x-for="i in 6"><span class="w-3 h-3 rounded-full transition" :class="pin.length >= i ? 'bg-terracotta-deep scale-110' : 'bg-line'"></span></template></div>
                    @error('pin')<p class="field-error -mt-1">{{ $message }}</p>@enderror
                    <button class="btn btn-primary btn-lg w-full" :disabled="pin.length !== 6" @disabled($lockedUntil)>Buka</button>
                @else
                    <label for="unlock-pw" class="sr-only">Password akun</label>
                    <input id="unlock-pw" name="password" type="password" autocomplete="current-password" autofocus required placeholder="Password akun" class="field !h-14 @error('password') is-invalid @enderror">
                    @error('password')<p class="field-error -mt-1">{{ $message }}</p>@enderror
                    <button class="btn btn-primary btn-lg w-full">Buka</button>
                    <p class="text-xs text-ink-soft leading-relaxed">Tips: aktifkan Kunci PIN di Profil supaya membuka kunci cukup dengan 6 angka.</p>
                @endif
            </form>
            @if($mode === 'pin')
            <details class="w-full text-left" @if($errors->forgot->any()) open @endif>
                <summary class="text-sm font-extrabold text-terracotta-deep cursor-pointer text-center list-none">Lupa PIN?</summary>
                <form method="POST" action="{{ route('student.pin.forgot') }}" class="mt-4 flex flex-col gap-3" onsubmit="try { sessionStorage.setItem('wl-pin-tab', '1') } catch (e) {}">
                    @csrf
                    <p class="text-sm text-ink-soft leading-relaxed">Masukkan password. PIN dimatikan, buat yang baru di Profil.</p>
                    <label for="forgot-pw" class="sr-only">Password akun</label>
                    <input id="forgot-pw" name="password" type="password" autocomplete="current-password" placeholder="Password akun" class="field" required>
                    @if($errors->forgot->has('password'))<p class="field-error">{{ $errors->forgot->first('password') }}</p>@endif
                    <button class="btn btn-outline btn-sm">Nonaktifkan PIN</button>
                </form>
            </details>
            @endif
            <div class="flex gap-4 text-sm font-bold">
                @unless($quickLocked)<a href="{{ route('student.home') }}" class="text-ink-soft">Ke Beranda</a>@endunless
                <form method="POST" action="{{ route('logout') }}" data-logout>@csrf<button class="text-ink-soft underline">Keluar akun</button></form>
            </div>
        </div>
    </main>
</div>
@endsection
