@extends('layouts.public')
@section('title', 'Daftar')
@section('content')
{{-- Daftar: tampilan sama dengan halaman masuk (navbar, latar kertas berilustrasi, kartu stiker). Google masih tampilan saja (dummy) kecuali sudah dikonfigurasi. --}}
<div class="relative isolate min-h-screen flex flex-col overflow-hidden" x-data="{ note: '' }">
    @include('auth.partials.backdrop', ['variant' => 'register'])

    @include('auth.partials.nav')

    <div class="flex-1 flex flex-col items-center justify-center px-4 pb-14 pt-4">
        <main class="relative w-full max-w-[560px] bg-white border-[2.5px] border-ink rounded-[32px] shadow-[8px_8px_0_#3E3530] px-6 sm:px-12 pt-10 pb-8 sm:pt-12 sm:pb-10 flex flex-col animate-[rise_.7s_cubic-bezier(.22,1,.36,1)_.1s_both]">
            <div class="flex flex-col items-center text-center">
                <span aria-hidden="true" class="relative w-[88px] aspect-[598/361] animate-[pop-in_.6s_cubic-bezier(.34,1.56,.64,1)_.3s_both]">
                    <img src="{{ asset('images/logo-parts/bubble.png') }}" alt="" class="absolute inset-0 w-full h-full">
                    <img src="{{ asset('images/logo-parts/heart.png') }}" alt="" class="logo-heart absolute inset-0 w-full h-full">
                    <img src="{{ asset('images/logo-parts/arm-left.png') }}" alt="" class="logo-arm-left absolute inset-0 w-full h-full">
                    <img src="{{ asset('images/logo-parts/arm-right.png') }}" alt="" class="logo-arm-right absolute inset-0 w-full h-full">
                </span>
                <h1 class="mt-5 text-[clamp(34px,6vw,48px)] leading-[1.05]">
                    <span class="hero-mask"><span class="inline-block animate-[reveal-up_.8s_cubic-bezier(.22,1,.36,1)_.35s_both]">Buat akun murid,</span></span><br>
                    <span class="hero-mask"><span class="inline-block animate-[reveal-up_.8s_cubic-bezier(.22,1,.36,1)_.47s_both]">mulai <span class="font-hand text-terracotta-deep text-[1.2em] leading-none">ceritamu.</span></span></span>
                </h1>
                <p class="mt-4 text-[16px] leading-[1.6] text-ink-soft animate-[rise_.7s_ease-out_.7s_both]">Cukup email dan password. Kami kirim kode verifikasi ke email-mu, lalu kamu memilih jenjang, sekolah, dan Guru BK.</p>
            </div>

            <div class="form-sticker mt-7 flex flex-col gap-5 animate-[rise_.7s_cubic-bezier(.22,1,.36,1)_.85s_both]">
                @if($googleEnabled)
                    <a href="{{ route('auth.google', array_filter(['intent' => $intent])) }}" class="login-btn bg-ink text-cream hover:bg-[#2E2723]">
                        <svg aria-hidden="true" width="18" height="18" viewBox="0 0 48 48"><path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3C33.7 32.7 29.2 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.2 7.9 3.1l5.7-5.7C34 6.1 29.3 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.3-.1-2.4-.4-3.5z"/><path fill="#FF3D00" d="m6.3 14.7 6.6 4.8C14.7 15.1 19 12 24 12c3.1 0 5.8 1.2 7.9 3.1l5.7-5.7C34 6.1 29.3 4 24 4 16.3 4 9.7 8.3 6.3 14.7z"/><path fill="#4CAF50" d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2C29.2 35.1 26.7 36 24 36c-5.2 0-9.6-3.3-11.3-8l-6.5 5C9.5 39.6 16.2 44 24 44z"/><path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-.8 2.2-2.2 4.2-4.1 5.6l6.2 5.2C37 39.2 44 34 44 24c0-1.3-.1-2.4-.4-3.5z"/></svg>
                        Daftar dengan Google
                    </a>
                @else
                    {{-- Dummy: belum terhubung ke Google --}}
                    <button type="button" x-on:click="note = 'Daftar lewat Google belum tersedia. Pakai email dulu, ya.'" class="login-btn bg-ink text-cream hover:bg-[#2E2723]">
                        <svg aria-hidden="true" width="18" height="18" viewBox="0 0 48 48"><path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3C33.7 32.7 29.2 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.2 7.9 3.1l5.7-5.7C34 6.1 29.3 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.3-.1-2.4-.4-3.5z"/><path fill="#FF3D00" d="m6.3 14.7 6.6 4.8C14.7 15.1 19 12 24 12c3.1 0 5.8 1.2 7.9 3.1l5.7-5.7C34 6.1 29.3 4 24 4 16.3 4 9.7 8.3 6.3 14.7z"/><path fill="#4CAF50" d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2C29.2 35.1 26.7 36 24 36c-5.2 0-9.6-3.3-11.3-8l-6.5 5C9.5 39.6 16.2 44 24 44z"/><path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-.8 2.2-2.2 4.2-4.1 5.6l6.2 5.2C37 39.2 44 34 44 24c0-1.3-.1-2.4-.4-3.5z"/></svg>
                        Lanjut dengan Google
                    </button>
                    <p x-show="note" x-cloak x-text="note" role="status" class="-mt-2 text-center text-[14px] font-bold text-terracotta-ink"></p>
                @endif
                <div class="flex items-center gap-3 text-[14px] font-extrabold text-ink-soft" aria-hidden="true"><span class="flex-1 border-t-2 border-dashed border-line"></span>atau dengan email<span class="flex-1 border-t-2 border-dashed border-line"></span></div>

                <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-4" novalidate>
                    @csrf
                    <input type="hidden" name="intent" value="{{ $intent }}">
                    <x-input name="email" type="email" label="Email" placeholder="contoh: kamu@gmail.com" autocomplete="email" required />
                    <x-input name="password" type="password" label="Password" hint="Minimal 8 karakter." autocomplete="new-password" required />
                    <x-input name="password_confirmation" type="password" label="Ulangi password" autocomplete="new-password" required />
                    <div class="sticker-note bg-sage-tint text-sage-ink">
                        <x-icon name="lock" size="20" stroke="2.2" class="mt-px" />
                        <span>Email hanya untuk masuk. Guru BK dan admin tidak bisa melihatnya.</span>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg btn-ink-shadow w-full !h-[56px]">Daftar</button>
                    <p class="text-center text-[14px] text-ink-soft">Dengan mendaftar, kamu menyetujui <a href="{{ route('terms') }}" class="font-extrabold text-terracotta-deep no-underline">Syarat &amp; Ketentuan</a> dan <a href="{{ route('privacy') }}" class="font-extrabold text-terracotta-deep no-underline">Kebijakan Privasi</a>.</p>
                </form>
            </div>

            <p class="mt-7 pt-5 border-t-2 border-dashed border-line text-center text-[15px] text-ink-soft">Sudah punya akun? <a href="{{ route('login', array_filter(['intent' => $intent])) }}" class="font-extrabold text-terracotta-deep no-underline">Masuk</a></p>
        </main>
    </div>
</div>
@endsection
