@extends('layouts.public')
@section('title', 'Verifikasi Email')
@section('content')
{{-- Langkah kedua pendaftaran: masukkan kode OTP dari email. Tampilan sama dengan halaman Daftar. --}}
<div class="relative isolate min-h-screen flex flex-col overflow-hidden">
    @include('auth.partials.backdrop', ['variant' => 'register'])

    @include('auth.partials.nav')

    <div class="flex-1 flex flex-col items-center justify-center px-4 pb-14 pt-4">
        <main class="relative w-full max-w-[560px] bg-white border-[2.5px] border-ink rounded-[32px] shadow-[8px_8px_0_#3E3530] px-6 sm:px-12 pt-10 pb-8 sm:pt-12 sm:pb-10 flex flex-col animate-[rise_.7s_cubic-bezier(.22,1,.36,1)_.1s_both]">
            <div class="flex flex-col items-center text-center">
                <span aria-hidden="true" class="w-16 h-16 rounded-2xl border-2 border-ink bg-sage-tint text-sage-ink flex items-center justify-center shadow-[3px_3px_0_#3E3530]"><x-icon name="lock" size="28" stroke="2.2" /></span>
                <h1 class="mt-5 text-[clamp(30px,5vw,42px)] leading-[1.1]">Cek <span class="font-hand text-terracotta-deep text-[1.2em] leading-none">email</span>-mu</h1>
                <p class="mt-4 text-[16px] leading-[1.6] text-ink-soft">Kami mengirim 6 angka kode ke <b class="text-ink break-all">{{ $maskedEmail }}</b>. Kode berlaku {{ $validMinutes }} menit.</p>
            </div>

            <div class="form-sticker mt-7 flex flex-col gap-5">
                <form method="POST" action="{{ route('register.verify.store') }}" class="flex flex-col gap-4" novalidate>
                    @csrf
                    <x-input name="code" label="Kode verifikasi" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" placeholder="000000" class="[&_.field]:text-center [&_.field]:text-[24px] [&_.field]:font-extrabold [&_.field]:tracking-[0.5em]" required autofocus />
                    <button type="submit" class="btn btn-primary btn-lg btn-ink-shadow w-full !h-[56px]">Verifikasi &amp; buat akun</button>
                </form>

                <div class="sticker-note bg-sand-tint text-sand-ink">
                    <x-icon name="info" size="20" stroke="2.2" class="mt-px" />
                    <span>Tidak ada di kotak masuk? Cek folder spam atau promosi.</span>
                </div>

                @if($canResend)
                    <form method="POST" action="{{ route('register.verify.resend') }}" class="text-center" x-data="{ wait: {{ $resendIn }} }" x-init="if (wait > 0) { const t = setInterval(() => { if (--wait <= 0) clearInterval(t) }, 1000) }">
                        @csrf
                        <button type="submit" x-bind:disabled="wait > 0" class="text-[15px] font-extrabold text-terracotta-deep disabled:text-ink-soft disabled:cursor-not-allowed">
                            <span x-show="wait > 0" x-cloak>Kirim ulang kode dalam <span x-text="wait"></span> detik</span>
                            <span x-show="wait <= 0">Kirim ulang kode</span>
                        </button>
                    </form>
                @endif
            </div>

            <p class="mt-7 pt-5 border-t-2 border-dashed border-line text-center text-[15px] text-ink-soft">Salah email? <a href="{{ route('register') }}" class="font-extrabold text-terracotta-deep no-underline">Daftar ulang</a></p>
        </main>
    </div>
</div>
@endsection
