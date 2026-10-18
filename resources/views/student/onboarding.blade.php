@extends('layouts.public')
@section('title', 'Selamat datang')
@php
    $errorStep = match (true) {
        $errors->has('level') => 'jenjang',
        $errors->has('school_id') => 'sekolah',
        $errors->has('counselor_id') => 'guru',
        default => null,
    };
@endphp
@section('content')
{{-- Onboarding murid: tampilan senada halaman masuk/daftar (latar kertas berilustrasi, kartu stiker); tiap langkah masuk dengan geser & pudar halus. --}}
<div class="relative isolate min-h-screen flex flex-col overflow-hidden" data-quick-exit-shortcut data-module="onboarding"
     x-data="onboarding({{ Js::from([
        'firstTime' => $firstTime,
        'step' => $errorStep ?? ($firstTime ? 'tutorial' : 'jenjang'),
        'slides' => 3,
        'level' => old('level', ''),
        'schoolId' => (string) old('school_id', ''),
        'counselorId' => (string) old('counselor_id', ''),
        'schools' => $schools,
        'counselorsUrl' => route('student.onboarding.counselors'),
     ]) }})">
    @include('auth.partials.backdrop', ['variant' => 'onboarding'])

    <div class="container-wl py-4 flex justify-between items-center gap-2 animate-[nav-in_.6s_cubic-bezier(.22,1,.36,1)_both]">
        <span class="flex items-center" aria-label="Welcoming"><img src="{{ asset('images/welcoming-mark.png') }}" alt="" class="w-12 h-auto"><span class="font-display text-[22px] -ml-0.5" aria-hidden="true">elcoming</span></span>
        <span class="flex gap-2"><x-emergency-button compact /><x-quick-exit /></span>
    </div>

    <main class="flex-1 flex items-start sm:items-center justify-center px-4 pt-2 pb-12">
        <form method="POST" action="{{ route('student.onboarding.store') }}" class="onboard form-sticker w-full max-w-[580px] bg-white border-[2.5px] border-ink rounded-[32px] shadow-[8px_8px_0_#3E3530] px-6 py-7 sm:px-10 sm:py-9 flex flex-col gap-6 animate-[rise_.7s_cubic-bezier(.22,1,.36,1)_.1s_both]" x-on:submit="submitting = true">
            @csrf
            {{-- Progres --}}
            <div class="flex flex-col gap-2" aria-hidden="true">
                <span class="font-hand text-[22px] leading-none text-terracotta-deep" x-text="'langkah ' + (steps.indexOf(step) + 1) + ' dari ' + steps.length"></span>
                <div class="flex items-center gap-2">
                    <template x-for="s in steps" :key="s"><span class="relative h-3 flex-1 rounded-full border-2 border-ink bg-cream-light overflow-hidden"><span class="absolute inset-0 bg-terracotta-deep origin-left transition-transform duration-500 ease-[cubic-bezier(.22,1,.36,1)]" :style="`transform: scaleX(${steps.indexOf(s) <= steps.indexOf(step) ? 1 : 0})`"></span></span></template>
                </div>
            </div>

            @if(session('emergency'))
                <div class="sticker-note bg-terracotta-tint text-terracotta-ink">
                    <x-icon name="phone" size="20" stroke="2.2" class="mt-px flex-none" />
                    <span>Kalau kamu dalam bahaya sekarang, jangan tunggu: <a href="{{ route('emergency') }}" class="font-extrabold text-terracotta-ink">buka Bantuan Darurat</a>. Setelah memilih sekolah, Guru BK sekolahmu bisa langsung diberi tahu.</span>
                </div>
            @endif
            @if(session('status'))<div class="sticker-note bg-sage-tint text-sage-ink">{{ session('status') }}</div>@endif

            {{-- 1. Tutorial: cara kerja Welcoming --}}
            <section x-show="step === 'tutorial'" x-transition:enter="onboard-enter" x-transition:enter-start="onboard-enter-start" x-transition:enter-end="onboard-enter-end" x-cloak class="flex flex-col gap-5" aria-live="polite">
                <div class="flex flex-col gap-1.5 text-center">
                    <span class="text-[13px] font-extrabold tracking-[0.1em] uppercase text-terracotta-ink">Akunmu sudah jadi · Cara kerja</span>
                    <h1 class="text-[28px] sm:text-[32px] leading-tight">Tiga langkah, tanpa harus mengetuk pintu ruang BK.</h1>
                </div>
                <div x-show="slide === 0" x-transition:enter="onboard-enter" x-transition:enter-start="onboard-enter-start" x-transition:enter-end="onboard-enter-end" class="flex flex-col gap-4">
                    {{-- Ilustrasi saja (bukan tombol): dibingkai sebagai "contoh tampilan" dengan kursor yang memperagakan klik, supaya tidak dikira harus diklik di sini. --}}
                    <div aria-hidden="true" class="tutorial-preview relative h-[170px] rounded-[22px] border-[2.5px] border-ink shadow-[4px_4px_0_#3E3530] bg-terracotta-tint flex items-center justify-center p-5 select-none pointer-events-none">
                        <div class="relative flex items-center justify-center gap-2 px-4 py-3 rounded-2xl bg-white/80 border border-ink/15">
                            <span class="demo-chip demo-chip-1 px-3 py-1.5 rounded-full text-[13px] font-extrabold border border-line bg-white whitespace-nowrap">Keluarga</span>
                            <span class="demo-chip demo-chip-2 px-3 py-1.5 rounded-full text-[13px] font-extrabold border border-line bg-white whitespace-nowrap">Akademik</span>
                            <span class="demo-chip demo-chip-3 px-3 py-1.5 rounded-full text-[13px] font-extrabold border border-line bg-white whitespace-nowrap">Belum tahu</span>
                            {{-- kursor memperagakan klik ke tiga topik bergantian --}}
                            <svg class="demo-cursor absolute w-7 h-7" viewBox="0 0 24 24" fill="#FFFFFF" stroke="#3E3530" stroke-width="1.8" stroke-linejoin="round"><path d="M5 3l14 8-6 1.5L16 20l-3 1.3-3.2-7.4L5 18z"/></svg>
                        </div>
                    </div>
                    <span class="font-hand text-[26px] leading-none text-terracotta-deep">Langkah 1</span>
                    <h2 class="text-2xl">Pilih topik</h2>
                    <p class="text-base leading-relaxed text-ink-soft">Pilih satu atau beberapa topik. “Belum tahu” juga boleh.</p>
                    <p class="inline-flex items-center gap-1.5 text-[13px] font-bold text-ink-soft"><x-icon name="info" size="15" />Ini baru penjelasan. Kamu memilih topik nanti saat memulai cerita.</p>
                </div>
                <div x-show="slide === 1" x-cloak x-transition:enter="onboard-enter" x-transition:enter-start="onboard-enter-start" x-transition:enter-end="onboard-enter-end" class="flex flex-col gap-4">
                    <div aria-hidden="true" class="relative h-[170px] rounded-[22px] border-[2.5px] border-ink shadow-[4px_4px_0_#3E3530] bg-sage-tint flex flex-col justify-center gap-2 p-5 select-none pointer-events-none">
                        <span class="self-start inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white text-sage-ink2 text-xs font-extrabold"><x-icon name="lock" size="12" stroke="3" />Kamu anonim</span>
                        <span class="self-end max-w-[80%] px-3.5 py-2.5 bg-terracotta-tint text-sm" style="border-radius: 18px 18px 6px 18px">Aku nggak tahu ini masalah atau bukan, tapi…</span>
                    </div>
                    <span class="font-hand text-[26px] leading-none text-terracotta-deep">Langkah 2</span>
                    <h2 class="text-2xl">Cerita secara anonim</h2>
                    <p class="text-base leading-relaxed text-ink-soft">Guru BK hanya melihat nama samaran, misalnya “Kucing Oranye #482”.</p>
                </div>
                <div x-show="slide === 2" x-cloak x-transition:enter="onboard-enter" x-transition:enter-start="onboard-enter-start" x-transition:enter-end="onboard-enter-end" class="flex flex-col gap-4">
                    <div aria-hidden="true" class="relative h-[170px] rounded-[22px] border-[2.5px] border-ink shadow-[4px_4px_0_#3E3530] bg-sand-tint flex items-center justify-center select-none pointer-events-none"><span aria-hidden="true" class="relative w-[160px] aspect-[598/361]"><img src="{{ asset('images/logo-parts/bubble.png') }}" alt="" class="absolute inset-0 w-full h-full"><img src="{{ asset('images/logo-parts/heart.png') }}" alt="" class="logo-heart absolute inset-0 w-full h-full"><img src="{{ asset('images/logo-parts/arm-left.png') }}" alt="" class="logo-arm-left absolute inset-0 w-full h-full"><img src="{{ asset('images/logo-parts/arm-right.png') }}" alt="" class="logo-arm-right absolute inset-0 w-full h-full"></span></div>
                    <span class="font-hand text-[26px] leading-none text-terracotta-deep">Langkah 3</span>
                    <h2 class="text-2xl">Dapat bimbingan</h2>
                    <p class="text-base leading-relaxed text-ink-soft">Guru BK membalas, mengirim bacaan yang pas, dan mengajak ngobrol langsung kalau kamu siap.</p>
                </div>
                <div class="flex justify-center gap-2" aria-hidden="true"><template x-for="i in slides"><span class="h-2.5 rounded-full border-2 border-ink transition-all duration-300" :class="slide === i - 1 ? 'w-8 bg-terracotta-deep' : 'w-2.5 bg-cream-light'"></span></template></div>
                <div class="flex gap-2.5">
                    <button type="button" class="btn btn-outline !border-2 !border-ink" x-show="slide > 0" x-on:click="slide--">Kembali</button>
                    <button type="button" class="flex-1 btn btn-primary btn-ink-shadow" x-on:click="slide < slides - 1 ? slide++ : step = 'jenjang'" x-text="slide < slides - 1 ? 'Lanjut' : 'Pilih sekolahku'">Lanjut</button>
                </div>
                <p class="text-xs text-center text-ink-soft">Nama, Kunci PIN, dan notifikasi bisa kamu atur kapan saja di Profil › Pengaturan.</p>
            </section>

            {{-- 3. Jenjang --}}
            <section x-show="step === 'jenjang'" x-transition:enter="onboard-enter" x-transition:enter-start="onboard-enter-start" x-transition:enter-end="onboard-enter-end" x-cloak class="flex flex-col gap-5">
                <div class="flex flex-col gap-1.5">
                    <span class="text-[13px] font-extrabold tracking-[0.1em] uppercase text-terracotta-ink">Pilih sekolah</span>
                    <h1 class="text-[30px] sm:text-[34px] leading-tight">Jenjang pendidikanmu saat ini?</h1>
                    <p class="text-[15px] leading-relaxed text-ink-soft">Supaya sekolahmu bisa kami tampilkan.</p>
                </div>
                <div class="grid grid-cols-2 gap-3.5 pr-1 pb-1" role="radiogroup" aria-label="Jenjang">
                    @foreach($levels as $key => $label)
                        <label class="onboard-option h-[88px] flex flex-col items-center justify-center gap-0.5 font-display text-2xl">
                            <input type="radio" name="level" value="{{ $key }}" x-model="level" x-on:change="schoolId = ''; counselorId = ''" class="sr-only">{{ $label }}
                            <span class="font-sans text-xs font-bold text-ink-soft">Kelas {{ implode('-', \App\Models\School::LEVELS[$key]['grades']) }}</span>
                        </label>
                    @endforeach
                </div>
                @error('level')<p class="field-error">{{ $message }}</p>@enderror
                <div class="flex gap-2.5">
                    <button type="button" class="btn btn-outline !border-2 !border-ink" x-show="firstTime" x-on:click="step = 'tutorial'; slide = slides - 1">Kembali</button>
                    <button type="button" class="flex-1 btn btn-primary btn-ink-shadow" :disabled="!level" x-on:click="step = 'sekolah'">Lanjut</button>
                </div>
            </section>

            {{-- 4. Sekolah --}}
            <section x-show="step === 'sekolah'" x-transition:enter="onboard-enter" x-transition:enter-start="onboard-enter-start" x-transition:enter-end="onboard-enter-end" x-cloak class="flex flex-col gap-4">
                <div class="flex flex-col gap-1.5">
                    <h1 class="text-[30px] sm:text-[34px] leading-tight">Di sekolah mana kamu mau bercerita?</h1>
                    <p class="text-[15px] leading-relaxed text-ink-soft">Ceritamu hanya sampai ke Guru BK sekolah yang kamu pilih. <b>Pilihan ini tidak bisa kamu ubah sendiri</b>, kalau keliru, minta admin sekolah membantu.</p>
                </div>
                <label for="ob-search" class="sr-only">Cari sekolah</label>
                <input id="ob-search" type="search" x-model="search" placeholder="Cari nama sekolah…" class="field">
                <div class="flex flex-col gap-3 max-h-[340px] overflow-y-auto -mx-1 px-1 pb-2 pr-2" role="radiogroup" aria-label="Sekolah">
                    <template x-for="s in schoolOptions" :key="s.id">
                        <label class="onboard-option flex items-center gap-3 p-3.5">
                            <input type="radio" name="school_id" :value="String(s.id)" x-model="schoolId" x-on:change="counselorId = ''" class="w-5 h-5 accent-terracotta-deep">
                            <span class="flex-1 min-w-0 flex flex-col"><span class="font-extrabold" x-text="s.name"></span><span class="text-[13px] text-ink-soft truncate" x-text="(s.address ? s.address + ' · ' : '') + s.counselors + ' Guru BK'"></span></span>
                        </label>
                    </template>
                    <p x-show="schoolOptions.length === 0" class="text-sm text-ink-soft p-3">Belum ada sekolah <span x-text="search ? 'yang cocok' : 'di jenjang ini'"></span>. Minta Guru BK atau admin sekolahmu menghubungi tim Welcoming.</p>
                </div>
                @error('school_id')<p class="field-error">{{ $message }}</p>@enderror
                <div class="flex gap-2.5">
                    <button type="button" class="btn btn-outline !border-2 !border-ink" x-on:click="step = 'jenjang'">Kembali</button>
                    <button type="button" class="flex-1 btn btn-primary btn-ink-shadow" :disabled="!schoolId" x-on:click="loadCounselors(); step = 'guru'">Lanjut</button>
                </div>
            </section>

            {{-- 5. Guru BK --}}
            <section x-show="step === 'guru'" x-transition:enter="onboard-enter" x-transition:enter-start="onboard-enter-start" x-transition:enter-end="onboard-enter-end" x-cloak class="flex flex-col gap-4">
                <div class="flex flex-col gap-1.5">
                    <span class="text-[13px] font-extrabold tracking-[0.1em] uppercase text-terracotta-ink">Opsional</span>
                    <h1 class="text-[30px] sm:text-[34px] leading-tight">Mau cerita ke siapa?</h1>
                    <p class="text-[15px] leading-relaxed text-ink-soft">Guru BK di <b x-text="selectedSchool?.name"></b>. Boleh dipilih nanti, kamu juga bisa memilih guru lain setiap memulai cerita.</p>
                </div>
                <p x-show="loading" class="text-sm text-ink-soft">Memuat daftar Guru BK…</p>
                <div class="flex flex-col gap-3 pr-1 pb-1" role="radiogroup" aria-label="Guru BK">
                    <template x-for="c in counselors" :key="c.id">
                        <label class="onboard-option flex items-start gap-3 p-3.5">
                            <input type="radio" name="counselor_id" :value="String(c.id)" x-model="counselorId" class="mt-3 w-5 h-5 accent-terracotta-deep">
                            <span class="relative w-12 h-12 flex-none rounded-2xl overflow-hidden border-2 border-ink bg-sage-tint text-sage-ink flex items-center justify-center font-display text-xl">
                                <template x-if="c.photo"><img :src="c.photo" alt="" class="w-full h-full object-cover"></template>
                                <template x-if="!c.photo"><span x-text="c.initial"></span></template>
                                <span class="absolute bottom-0.5 right-0.5 w-3 h-3 rounded-full border-2 border-white" :class="c.online ? 'bg-sage-deep' : 'bg-toggle-off'"></span>
                            </span>
                            <span class="flex-1 min-w-0 flex flex-col gap-0.5">
                                <span class="font-extrabold" x-text="c.name + (c.full && c.full !== c.name ? ' · ' + c.full : '')"></span>
                                <span class="inline-flex items-center gap-1.5 text-[13px] font-bold" :class="c.online ? 'text-sage-ink' : 'text-ink-soft'"><span class="w-2 h-2 rounded-full flex-none" :class="c.online ? 'status-on' : 'status-off'"></span><span x-text="(c.online ? 'Online' : 'Offline') + ' · ' + c.hours"></span></span>
                                <span x-show="c.intro" class="text-[13px] text-ink-soft line-clamp-2" x-text="c.intro"></span>
                            </span>
                        </label>
                    </template>
                    <p x-show="!loading && counselors.length === 0" class="text-sm text-ink-soft p-3">Belum ada Guru BK aktif. Kamu tetap bisa bergabung.</p>
                </div>
                @error('counselor_id')<p class="field-error">{{ $message }}</p>@enderror
                <div class="sticker-note bg-sage-tint text-sage-ink"><x-icon name="lock" size="20" class="mt-px flex-none" /><span>Guru BK dan admin tidak tahu siapa kamu, sampai kamu sendiri membukanya.</span></div>
                <div class="flex gap-2.5 flex-wrap">
                    <button type="button" class="btn btn-outline !border-2 !border-ink" x-on:click="step = 'sekolah'">Kembali</button>
                    <button type="submit" name="skip_counselor" value="1" class="btn btn-ghost text-ink-soft" :disabled="submitting">Nanti saja</button>
                    <button type="submit" class="flex-1 btn btn-primary btn-ink-shadow" :disabled="!counselorId || submitting">Mulai cerita dengan guru ini</button>
                </div>
            </section>
        </form>
    </main>
    <form method="POST" action="{{ route('logout') }}" data-logout class="self-center pb-8">@csrf<button class="text-sm font-extrabold text-ink-soft">Keluar akun</button></form>
</div>
@endsection
