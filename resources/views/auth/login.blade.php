@extends('layouts.public')
@section('title', 'Masuk')
@section('content')
{{-- Login: navbar kecil (kembali, logo, darurat, admin), latar kertas bertitik dengan potongan kertas berilustrasi murid & Guru BK di kiri-kanan, dan kartu stiker di tengah. Google masih tampilan saja (dummy) kecuali Google sudah dikonfigurasi; "Lanjut dengan email" membuka form masuk yang sudah ada. --}}
@php
    $emailFirst = $errors->any() || old('identifier') || old('role', $role) !== 'student';
@endphp
<div class="relative isolate min-h-screen flex flex-col overflow-hidden"
     x-data="{ role: @js(old('role', $role)), email: @js($emailFirst), note: '' }">

    @include('auth.partials.backdrop')

    {{-- Admin masuk lewat URL /login?peran=admin (tidak ada tombolnya). --}}
    @include('auth.partials.nav')

    <div class="flex-1 flex flex-col items-center justify-center px-4 pb-14 pt-4">
        <main class="relative w-full max-w-[560px] bg-white border-[2.5px] border-ink rounded-[32px] shadow-[8px_8px_0_#3E3530] px-6 sm:px-12 pt-10 pb-8 sm:pt-12 sm:pb-10 flex flex-col items-center text-center animate-[rise_.7s_cubic-bezier(.22,1,.36,1)_.1s_both]">
            <span aria-hidden="true" class="relative w-[96px] aspect-[598/361] animate-[pop-in_.6s_cubic-bezier(.34,1.56,.64,1)_.3s_both]">
                <img src="{{ asset('images/logo-parts/bubble.png') }}" alt="" class="absolute inset-0 w-full h-full">
                <img src="{{ asset('images/logo-parts/heart.png') }}" alt="" class="logo-heart absolute inset-0 w-full h-full">
                <img src="{{ asset('images/logo-parts/arm-left.png') }}" alt="" class="logo-arm-left absolute inset-0 w-full h-full">
                <img src="{{ asset('images/logo-parts/arm-right.png') }}" alt="" class="logo-arm-right absolute inset-0 w-full h-full">
            </span>

            <h1 class="mt-5 text-[clamp(36px,6.5vw,52px)] leading-[1.05]">
                <span class="hero-mask"><span class="inline-block animate-[reveal-up_.8s_cubic-bezier(.22,1,.36,1)_.35s_both]">Hai, selamat</span></span><br>
                <span class="hero-mask"><span class="inline-block animate-[reveal-up_.8s_cubic-bezier(.22,1,.36,1)_.47s_both]">datang <span class="font-hand text-terracotta-deep text-[1.2em] leading-none">kembali!</span></span></span>
            </h1>

            <div x-show="!email" x-transition.opacity.duration.250ms class="w-full flex flex-col items-center">
                <p class="mt-4 text-[17px] leading-[1.6] text-ink-soft animate-[rise_.7s_ease-out_.7s_both]">Ruang cerita murid &amp; Guru BK.<br>Tempat bercerita, bukan dihakimi.</p>

                {{-- Wajah mood yang menyapa --}}
                <div aria-hidden="true" class="mt-6 flex items-end gap-3">
                    @foreach([['happy', '#E4EADF', '0s'], ['neutral', '#FBF8EF', '-.6s'], ['sad', '#F3E6D0', '-1.2s'], ['anxious', '#F6DDD7', '-1.8s'], ['happy', '#F2CBC1', '-2.4s']] as $j => [$mood, $bg, $phase])
                        <span class="animate-[pop-in_.45s_cubic-bezier(.34,1.6,.64,1)_both]" style="animation-delay: {{ .8 + $j * .08 }}s">
                            <span class="w-11 h-11 rounded-2xl border-2 border-ink flex items-center justify-center shadow-[2px_2px_0_#3E3530]" style="background: {{ $bg }}; animation: obj-bob 3s ease-in-out {{ $phase }} infinite"><x-mood-face :mood="$mood" size="28" /></span>
                        </span>
                    @endforeach
                </div>

                {{-- Label tulisan tangan + panah ke tombol --}}
                <div aria-hidden="true" class="relative w-full max-w-[380px] h-12 mt-3">
                    <span class="absolute right-0 top-1 font-hand text-[26px] leading-none text-terracotta-deep rotate-[-4deg] animate-[fade-in_.5s_ease-out_1.3s_both]">mulai di sini</span>
                    <svg class="login-arrow absolute right-[34%] top-2 w-16 h-12 overflow-visible" viewBox="0 0 64 48" fill="none" stroke="#D4705F" stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
                        <path pathLength="1" d="M60 8C40 2 20 8 14 30"/>
                        <path pathLength="1" class="login-arrow-head" d="M6 22l8 12 9-10"/>
                    </svg>
                </div>
            </div>

            @if($intent === 'darurat')
                <div class="w-full mt-4 flex gap-3 p-4 rounded-2xl bg-danger-tint text-danger-ink text-sm leading-relaxed font-bold text-left">
                    <x-icon name="phone" size="20" />
                    <span>Dalam bahaya sekarang? Hubungi <a href="{{ route('emergency') }}" class="underline">kontak darurat</a>.</span>
                </div>
            @endif

            {{-- Pilihan masuk --}}
            <div class="w-full max-w-[380px] mt-3 flex flex-col gap-3 animate-[rise_.7s_cubic-bezier(.22,1,.36,1)_1s_both]" x-show="!email" x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="opacity-0 translate-y-2">
                @if($googleEnabled)
                    <a href="{{ route('auth.google', array_filter(['intent' => $intent])) }}" class="login-btn bg-ink text-cream hover:bg-[#2E2723]">
                        <svg aria-hidden="true" width="18" height="18" viewBox="0 0 48 48"><path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3C33.7 32.7 29.2 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.2 7.9 3.1l5.7-5.7C34 6.1 29.3 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.3-.1-2.4-.4-3.5z"/><path fill="#FF3D00" d="m6.3 14.7 6.6 4.8C14.7 15.1 19 12 24 12c3.1 0 5.8 1.2 7.9 3.1l5.7-5.7C34 6.1 29.3 4 24 4 16.3 4 9.7 8.3 6.3 14.7z"/><path fill="#4CAF50" d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2C29.2 35.1 26.7 36 24 36c-5.2 0-9.6-3.3-11.3-8l-6.5 5C9.5 39.6 16.2 44 24 44z"/><path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-.8 2.2-2.2 4.2-4.1 5.6l6.2 5.2C37 39.2 44 34 44 24c0-1.3-.1-2.4-.4-3.5z"/></svg>
                        Masuk dengan Google
                    </a>
                @else
                    {{-- Dummy: belum terhubung ke Google --}}
                    <button type="button" x-on:click="note = 'Masuk lewat Google belum tersedia. Pakai email dulu, ya.'" class="login-btn bg-ink text-cream hover:bg-[#2E2723]">
                        <svg aria-hidden="true" width="18" height="18" viewBox="0 0 48 48"><path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3C33.7 32.7 29.2 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.2 7.9 3.1l5.7-5.7C34 6.1 29.3 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.3-.1-2.4-.4-3.5z"/><path fill="#FF3D00" d="m6.3 14.7 6.6 4.8C14.7 15.1 19 12 24 12c3.1 0 5.8 1.2 7.9 3.1l5.7-5.7C34 6.1 29.3 4 24 4 16.3 4 9.7 8.3 6.3 14.7z"/><path fill="#4CAF50" d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2C29.2 35.1 26.7 36 24 36c-5.2 0-9.6-3.3-11.3-8l-6.5 5C9.5 39.6 16.2 44 24 44z"/><path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-.8 2.2-2.2 4.2-4.1 5.6l6.2 5.2C37 39.2 44 34 44 24c0-1.3-.1-2.4-.4-3.5z"/></svg>
                        Lanjut dengan Google
                    </button>
                @endif
                <button type="button" x-on:click="email = true; note = ''; $nextTick(() => document.getElementById('login-id')?.focus())" class="login-btn bg-terracotta-deep text-white shadow-[3px_3px_0_#3E3530] hover:bg-terracotta-ink">
                    <x-icon name="send" size="17" />
                    Lanjut dengan email
                </button>
                <p x-show="note" x-cloak x-text="note" role="status" class="text-[14px] font-bold text-terracotta-ink"></p>
            </div>

            {{-- Form email (fitur masuk yang sudah ada) --}}
            <div x-show="email" x-cloak x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="opacity-0 translate-y-3" class="form-sticker w-full mt-6 flex flex-col gap-5 text-left">
                {{-- Pilihan peran dengan penanda yang bergeser halus --}}
                <div role="radiogroup" aria-label="Peran" class="seg role-switch relative w-full grid grid-cols-2" x-show="role !== 'admin'">
                    <span aria-hidden="true" class="role-switch-pill absolute top-1 bottom-1 left-1 w-[calc(50%-4px)] rounded-full" :class="role === 'counselor' ? 'bg-sage-deep' : 'bg-terracotta-deep'" :style="`transform: translateX(${role === 'counselor' ? '100%' : '0'})`"></span>
                    <button type="button" role="radio" :aria-checked="(role === 'student').toString()" x-on:click="role = 'student'" class="seg-item relative z-10 text-center inline-flex items-center justify-center gap-2 transition-colors duration-300" :class="role === 'student' ? '!text-white' : 'text-ink-soft'"><x-icon name="chat-heart" size="18" />Saya Murid</button>
                    <button type="button" role="radio" :aria-checked="(role === 'counselor').toString()" x-on:click="role = 'counselor'" class="seg-item relative z-10 text-center inline-flex items-center justify-center gap-2 transition-colors duration-300" :class="role === 'counselor' ? '!text-white' : 'text-ink-soft'"><x-icon name="person" size="18" />Saya Guru BK</button>
                </div>
                <p x-show="role === 'admin'" x-cloak class="sticker-note bg-sand-tint text-sand-ink justify-center font-extrabold"><x-icon name="shield" size="17" />Masuk sebagai Admin Sekolah</p>
                @error('role')<p class="field-error -mt-3" role="alert">{{ $message }}</p>@enderror
            <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-4" novalidate x-ref="loginForm" x-init="$watch('role', () => { const el = $refs.loginForm; el.classList.remove('role-swap'); void el.offsetWidth; el.classList.add('role-swap'); })">
                        @csrf
                        <input type="hidden" name="role" :value="role">
                        <input type="hidden" name="intent" value="{{ $intent }}">
                        <div class="flex flex-col gap-1.5">
                            <label for="login-id" class="field-label" x-text="role === 'student' ? 'Email' : (role === 'counselor' ? 'Email sekolah atau NIP' : 'Email atau kode admin')">Email</label>
                            <input id="login-id" name="identifier" type="text" value="{{ old('identifier') }}" autocomplete="username" required
                                :placeholder="role === 'student' ? 'contoh: kamu@gmail.com' : (role === 'counselor' ? 'contoh: 198804122015032001' : 'contoh: admin')"
                                class="field !h-[52px] @error('identifier') is-invalid @enderror" @error('identifier') aria-invalid="true" aria-describedby="login-id-err" @enderror>
                            @error('identifier')<p id="login-id-err" class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="flex flex-col gap-1.5" x-data="{ forgot: false }">
                            <div class="flex justify-between items-baseline mb-1.5">
                                <label for="login-pw" class="field-label !mb-0">Password</label>
                                <a x-show="role === 'student'" href="{{ route('password.request') }}" class="text-sm font-extrabold text-terracotta-deep no-underline">Lupa password?</a>
                                <button type="button" x-show="role !== 'student'" x-cloak x-on:click="forgot = !forgot" class="text-sm font-extrabold text-terracotta-deep">Lupa password?</button>
                            </div>
                            <input id="login-pw" name="password" type="password" autocomplete="current-password" required class="field !h-[52px]">
                            <p x-show="forgot && role !== 'student'" x-cloak class="text-sm text-ink-soft leading-relaxed mt-1">Minta admin sekolah mereset password. Password kembali ke NIP/kode-mu, lalu ganti saat masuk.</p>
                        </div>
                        <div class="grid transition-[grid-template-rows,opacity,margin] duration-[400ms] ease-[cubic-bezier(.22,1,.36,1)]" :class="role === 'student' ? 'grid-rows-[1fr] opacity-100' : 'grid-rows-[0fr] opacity-0 -mt-4'" :aria-hidden="(role !== 'student').toString()">
                            <div class="overflow-hidden p-[0_6px_6px_0]"><div class="sticker-note bg-sage-tint text-sage-ink">
                            <x-icon name="lock" size="20" stroke="2.2" class="mt-px" />
                            <span>Email dan akun Google-mu tidak terlihat oleh Guru BK maupun admin.</span>
                        </div></div>
                        </div>
                        <button type="submit" class="btn btn-lg btn-ink-shadow w-full !h-[56px]" :class="role === 'student' ? 'btn-primary' : (role === 'counselor' ? 'btn-sage' : 'btn-sand')">Masuk</button>
                    </form>
                <button type="button" x-show="role !== 'admin'" x-on:click="email = false; role = 'student'" class="self-center inline-flex items-center gap-1.5 text-[14px] font-extrabold text-ink-soft hover:text-ink"><x-icon name="arrow-left" size="15" />Pilihan masuk lain</button>
                <button type="button" x-show="role === 'admin'" x-cloak x-on:click="role = 'student'" class="self-center inline-flex items-center gap-1.5 text-[14px] font-extrabold text-ink-soft hover:text-ink"><x-icon name="arrow-left" size="15" />Kembali ke Murid / Guru BK</button>
            </div>

            <p x-show="role === 'student'" class="w-full mt-7 pt-5 border-t-2 border-dashed border-line text-[15px] text-ink-soft">Belum punya akun? <a href="{{ route('register', array_filter(['intent' => $intent])) }}" class="font-extrabold text-terracotta-deep no-underline">Daftar sekarang</a></p>
        </main>
    </div>
</div>
@endsection
