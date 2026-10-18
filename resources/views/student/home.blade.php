@extends('layouts.student', ['active' => 'beranda'])
@section('title', 'Beranda')
@php
    $tz = \App\Models\School::current()->timezone;
    $firstName = explode(' ', $user->displayName())[0];
    $moodDefs = collect(\App\Models\MoodEntry::LABELS);
@endphp
@section('header')
<header class="flex items-end justify-between gap-4 flex-wrap no-print">
    <div class="flex flex-col gap-1">
        <span class="font-hand text-[26px] leading-none text-terracotta-deep">Hai, {{ $firstName }}</span>
        <h1 class="text-[32px] lg:text-[40px] leading-[1.1]">Gimana harimu?</h1>
    </div>
    <span class="hidden sm:inline-flex items-center gap-2 h-10 px-4 rounded-full bg-white border border-line-soft text-sm font-bold text-ink-soft shadow-xs"><x-icon name="calendar" size="16" />{{ now($tz)->translatedFormat('l, j F Y') }}</span>
</header>
@endsection

@section('content')
<div class="grid grid-cols-[minmax(0,1fr)] lg:grid-cols-12 gap-5 items-start" data-module="mood-checkin" x-data="moodCheckin({{ Js::from(['mood' => $today?->mood, 'note' => $today?->note, 'songUrl' => $today?->song['url'] ?? '', 'replies' => $replies, 'labels' => $moodDefs, 'url' => route('student.mood.checkin'), 'prompt' => (bool) $prompt]) }})">
    {{-- Check-in mood --}}
    <section aria-labelledby="mood-q" data-tour="mood" class="lg:col-span-8 lg:self-stretch bg-white border border-line-soft rounded-panel p-5 lg:p-7 flex flex-col gap-5 shadow-card">
        <div x-show="!mood" class="flex flex-col gap-5">
            <div class="flex items-center justify-between gap-2">
                <h2 id="mood-q" class="!font-sans text-lg lg:text-xl font-extrabold">Check-in mood hari ini</h2>
                <span class="inline-flex items-center gap-1.5 h-8 px-3 rounded-full bg-sage-tint text-sage-ink text-xs font-extrabold whitespace-nowrap"><x-icon name="lock" size="12" stroke="3" />Privat</span>
            </div>
            <div class="grid grid-cols-5 gap-1.5 lg:gap-3">
                @foreach($moodDefs as $key => $label)
                    <button type="button" aria-label="{{ $label }}" x-on:click="pick('{{ $key }}')" :disabled="busy" class="group h-[84px] lg:h-[120px] rounded-[20px] lg:rounded-[24px] border-[1.5px] border-line bg-cream-light flex flex-col items-center justify-center gap-1.5 lg:gap-2.5 text-xs lg:text-sm font-bold text-ink transition duration-200 hover:border-terracotta hover:bg-terracotta-tint2 hover:-translate-y-1 hover:shadow-soft active:scale-95">
                        <x-mood-face :mood="$key" size="32" class="lg:w-12 lg:h-12 transition-transform duration-200 group-hover:scale-110" />
                        <span>{{ $label }}</span>
                    </button>
                @endforeach
            </div>
        </div>
        <div x-show="mood" x-cloak class="flex flex-col gap-4">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 lg:w-20 lg:h-20 flex-none rounded-[22px] flex items-center justify-center" :style="'background:' + ({{ Js::from($tints) }})[mood]">
                    @foreach($moodDefs as $key => $label)<template x-if="mood === '{{ $key }}'"><x-mood-face :mood="$key" size="40" /></template>@endforeach
                </div>
                <div class="flex flex-col gap-1 flex-1 min-w-0">
                    <span class="font-display text-[20px] lg:text-[24px] leading-tight">Terima kasih sudah check-in <x-icon name="heart" size="22" stroke="2.2" class="inline-block align-[-3px] text-terracotta-deep" /></span>
                    <span class="text-sm lg:text-[15px] leading-snug text-ink-soft" x-text="replies[mood]"></span>
                </div>
            </div>
            <form x-show="noteOpen" x-cloak x-on:submit.prevent="saveNote()" class="flex flex-col gap-2">
                <label for="note-home" class="text-sm font-bold">Catatan singkat (opsional)</label>
                <textarea id="note-home" x-model="note" maxlength="500" rows="3" placeholder="Apa yang paling terasa hari ini? (boleh satu kalimat)" class="field"></textarea>
                <x-song-input id="song-home" x-model="songUrl" label="Lagu hari ini (opsional)" />
                <p x-show="noteError" x-text="noteError" x-cloak class="field-error !mt-0"></p>
                <div class="flex justify-between items-center"><span class="text-xs text-ink-soft" x-text="(note || '').length + '/500'"></span><button class="btn btn-primary btn-sm" :disabled="busy">Simpan catatan</button></div>
            </form>
            <div class="flex gap-2" x-show="!noteOpen">
                <button type="button" x-on:click="noteOpen = true" class="flex-1 sm:flex-none btn btn-outline btn-sm !text-sm !font-extrabold" x-text="note ? 'Ubah catatan' : 'Tulis catatan'">Tulis catatan</button>
                <button type="button" x-on:click="mood = null" class="btn btn-ghost btn-sm !text-sm text-ink-soft">Ubah</button>
            </div>
        </div>
    </section>

    {{-- Ajakan cerita --}}
    <a href="{{ route('student.conversations.create') }}" data-tour="mulai-cerita" class="group lg:col-span-4 lg:self-stretch min-h-[220px] no-underline text-white bg-terracotta-deep rounded-panel p-6 lg:p-7 flex flex-col justify-between gap-4 relative overflow-hidden shadow-[0_18px_36px_-12px_rgba(181,82,63,0.55)] transition duration-300 hover:-translate-y-0.5 hover:shadow-[0_24px_44px_-12px_rgba(181,82,63,0.6)]">
        <span aria-hidden="true" class="absolute -right-10 -bottom-12 w-48 h-48 rounded-full bg-white/10"></span>
        <span aria-hidden="true" class="absolute right-16 -top-10 w-24 h-24 rounded-full bg-white/10"></span>
        <span class="relative flex flex-col gap-2">
            <span class="text-[15px] font-bold text-[#FBE9E5]">Ada yang lagi mengganjal?</span>
            <span class="font-display text-[28px] lg:text-[30px] leading-[1.15] max-w-[280px]">Ceritain ke Guru BK, anonim kok.</span>
        </span>
        <span class="relative self-start inline-flex items-center gap-2 h-12 px-6 rounded-full bg-white text-terracotta-ink font-extrabold text-base shadow-[0_6px_16px_-4px_rgba(62,53,48,.3)] whitespace-nowrap">Mulai Cerita<x-icon name="arrow-right" size="16" class="transition-transform duration-200 group-hover:translate-x-1" /></span>
    </a>

    {{-- Lanjutkan cerita --}}
    <section aria-labelledby="lanjut-h" data-tour="lanjut" class="lg:col-span-8 lg:self-stretch flex flex-col gap-3">
        <div class="flex items-center justify-between px-1">
            <h2 id="lanjut-h" class="!font-sans text-lg font-extrabold">Lanjutkan cerita</h2>
            @if($conversations->isNotEmpty())<a href="{{ route('student.conversations.index') }}" class="text-sm font-extrabold text-terracotta-deep no-underline">Semua cerita</a>@endif
        </div>
        @if($conversations->isEmpty())
            <div class="flex-1 bg-white/70 border-[1.5px] border-dashed border-line rounded-panel p-5 flex items-center gap-4">
                <span class="tile w-12 h-12 bg-terracotta-tint2 text-terracotta"><x-icon name="chat-heart" size="26" /></span>
                <span class="flex-1 text-[15px] text-ink-soft">Belum ada cerita. Mulai kapan pun kamu siap.</span>
            </div>
        @else
            <div class="flex-1 bg-white border border-line-soft rounded-panel shadow-soft divide-y divide-line-soft overflow-hidden flex flex-col">
                @foreach($conversations as $c)
                    @php $st = $availability->statusFor($c->counselor); @endphp
                    <a href="{{ route('student.conversations.show', $c) }}" class="group no-underline text-ink px-4 lg:px-5 py-4 flex gap-3.5 items-center transition-colors hover:bg-cream-light">
                        <x-counselor-avatar :counselor="$c->counselor" :size="48" :online="$st['online']" />
                        <div class="flex-1 min-w-0 flex flex-col gap-1">
                            <span class="flex items-center gap-2 min-w-0">
                                <span class="font-extrabold text-[15px] lg:text-base truncate">{{ $c->counselor->displayName() }}</span>
                                <span class="hidden md:contents">@foreach($c->topics->take(2) as $t)<x-topic-chip :topic="$t" />@endforeach</span>
                            </span>
                            <span class="text-sm truncate {{ $c->unread_count ? 'font-bold text-ink' : 'text-ink-soft' }}"><x-message-preview :icon="$c->latestMessage?->previewIcon()" :text="$c->latestMessage?->preview()" /></span>
                        </div>
                        <div class="flex-none flex flex-col items-end gap-1.5">
                            <span class="text-xs {{ $c->unread_count ? 'font-extrabold text-terracotta-ink' : 'text-ink-soft' }}">{{ $c->last_message_at?->timezone($tz)->isToday() ? $c->last_message_at->timezone($tz)->format('H.i') : $c->last_message_at?->timezone($tz)->translatedFormat('j M') }}</span>
                            @if($c->unread_count)<span class="min-w-5 h-5 px-1.5 rounded-full bg-terracotta-deep text-white text-[11px] font-extrabold flex items-center justify-center leading-none pt-px" aria-label="{{ $c->unread_count }} pesan baru">{{ $c->unread_count }}</span>@else<x-icon name="chevron-right" size="16" class="text-ink-soft transition-transform group-hover:translate-x-0.5" />@endif
                        </div>
                    </a>
                @endforeach
                <a href="{{ route('student.conversations.create') }}" class="group mt-auto no-underline px-4 lg:px-5 py-3.5 flex items-center gap-3.5 bg-cream-light/60 text-terracotta-deep font-extrabold text-sm transition-colors hover:bg-terracotta-tint2">
                    <span class="w-12 h-12 flex-none rounded-2xl border-[1.5px] border-dashed border-terracotta/50 flex items-center justify-center"><x-icon name="plus" size="18" /></span>
                    Cerita baru dengan Guru BK lain
                    <x-icon name="arrow-right" size="16" class="ml-auto transition-transform group-hover:translate-x-0.5" />
                </a>
            </div>
        @endif
    </section>

    {{-- Kolom samping --}}
    <aside class="lg:col-span-4 flex flex-col gap-5">
        @if($invitation)
            <section aria-labelledby="undangan-d" class="panel !rounded-panel p-5 flex flex-col gap-3.5">
                <h2 id="undangan-d" class="!font-sans text-base font-extrabold">Undangan Sesi</h2>
                @include('student.partials.invitation-teaser', ['invitation' => $invitation, 'bare' => true])
            </section>
        @endif
        <a href="{{ route('student.mood.index') }}" data-tour="mood-minggu" class="group no-underline text-ink bg-white border border-line-soft rounded-panel p-5 flex flex-col gap-4 shadow-soft lift">
            <span class="flex justify-between items-center"><span class="text-base font-extrabold">Mood minggu ini</span><span class="inline-flex items-center gap-1 text-[13px] font-extrabold text-terracotta-deep">Jurnal<x-icon name="chevron-right" size="14" class="transition-transform group-hover:translate-x-0.5" /></span></span>
            <span class="grid grid-cols-7 gap-1">
                @foreach($week as $w)
                    @if($loop->last)
                        {{-- Hari ini: langsung ikut berubah saat check-in, tanpa refresh. --}}
                        <span class="flex flex-col items-center gap-1.5 text-[11px] font-extrabold text-ink">
                            <span class="w-full aspect-square max-w-[40px] rounded-xl flex items-center justify-center ring-2 ring-terracotta transition-colors" :style="'background:' + (mood ? ({{ Js::from($tints) }})[mood] : '#FBF8EF')">
                                @foreach($moodDefs as $key => $label)<span x-show="mood === '{{ $key }}'" x-cloak class="flex"><x-mood-face :mood="$key" size="24" stroke="2.4" /></span>@endforeach
                                <span x-show="!mood" class="w-1.5 h-1.5 rounded-full bg-line"></span>
                            </span>Hari ini
                        </span>
                    @else
                    <span class="flex flex-col items-center gap-1.5 text-[11px] font-bold text-ink-soft">
                        <span class="w-full aspect-square max-w-[40px] rounded-xl flex items-center justify-center" style="background: {{ $w['entry'] ? $tints[$w['entry']->mood] : '#FBF8EF' }}">
                            @if($w['entry'])<x-mood-face :mood="$w['entry']->mood" size="24" stroke="2.4" />@else<span class="w-1.5 h-1.5 rounded-full bg-line"></span>@endif
                        </span>{{ $w['date']->translatedFormat('D') }}
                    </span>
                    @endif
                @endforeach
            </span>
            @php $prevNeg = $week->slice(-3, 2)->filter(fn ($w) => $w['entry']?->isNegative())->count(); @endphp
            <span x-show="{{ $prevNeg }} + (['sad', 'anxious', 'angry'].includes(mood) ? 1 : 0) >= 3" x-cloak class="text-[13px] leading-normal text-ink-soft">Tiga hari terakhir terasa berat. Pelan-pelan, ya.</span>
        </a>
        <a href="{{ route('student.counselors') }}" data-tour="kenalan-guru" class="group no-underline text-ink bg-sage-tint border border-sage/25 rounded-panel p-5 flex items-center gap-4 lift">
            <span class="flex -space-x-3 flex-none">
                @foreach($counselors->take(3) as $g)<x-counselor-avatar :counselor="$g" :size="44" class="ring-2 ring-sage-tint" />@endforeach
            </span>
            <span class="flex-1 min-w-0 flex flex-col">
                <span class="text-base font-extrabold text-sage-ink">Kenalan Guru BK</span>
                <span class="text-[13px] text-sage-ink2">Lihat profil mereka</span>
            </span>
            <x-icon name="chevron-right" size="18" class="text-sage-ink transition-transform group-hover:translate-x-0.5" />
        </a>
    </aside>

    {{-- Bacaan --}}
    <section aria-labelledby="artikel-h" data-tour="bacaan" class="lg:col-span-12 flex flex-col gap-3">
        <div class="flex items-center justify-between px-1">
            <h2 id="artikel-h" class="!font-sans text-lg font-extrabold">Bacaan untukmu</h2>
            <a href="{{ route('student.selfhelp.index') }}" class="text-sm font-extrabold text-terracotta-deep no-underline">Lihat semua</a>
        </div>
        <div class="flex sm:grid sm:grid-cols-[repeat(auto-fit,minmax(220px,1fr))] gap-4 overflow-x-auto sm:overflow-visible -mx-6 px-6 sm:mx-0 sm:px-0 pb-2 snap-x scroll-px-6 [scrollbar-width:none]">
            @foreach($articles as $a)
                <x-article-card :article="$a" compact class="flex-[0_0_230px] sm:flex-auto snap-start" />
            @endforeach
        </div>
    </section>

    {{-- Pop-up ajakan cerita (7.17) --}}
    <div x-show="promptOpen" x-cloak class="scrim fixed inset-0 z-50 flex items-end lg:items-start justify-center lg:pt-[120px] lg:px-6" x-transition.opacity>
        <section role="dialog" aria-modal="true" aria-labelledby="pop-h" x-trap.noscroll="promptOpen" class="relative w-full lg:max-w-[520px] bg-cream rounded-t-[32px] lg:rounded-[32px] px-6 pt-3 pb-8 lg:p-8 flex flex-col items-center gap-4 text-center shadow-modal animate-pop">
            <div class="lg:hidden w-11 h-[5px] rounded-full bg-toggle-off"></div>
            <button type="button" aria-label="Tutup" x-on:click="respond('dismissed')" class="hidden lg:flex absolute right-4 top-4 w-11 h-11 rounded-full bg-white border border-line shadow-xs items-center justify-center transition hover:bg-cream-light"><x-icon name="x" size="20" /></button>
            <img src="{{ asset('images/welcoming-mark.png') }}" alt="" class="w-[140px] lg:w-[170px] h-auto mt-1">
            <h2 id="pop-h" class="text-[26px] lg:text-[28px] leading-[1.2]">Sepertinya akhir-akhir ini berat ya.</h2>
            <p class="text-base leading-relaxed text-ink-soft max-w-[400px]">Mau cerita ke Guru BK? Anonim kok, namamu nggak akan kelihatan.</p>
            <div class="w-full bg-white rounded-card px-4 py-3.5 flex items-center justify-between gap-2">
                <span class="text-[13px] font-bold text-ink-soft text-left">3 hari terakhir</span>
                <div class="flex gap-3">
                    <template x-for="d in promptDays" :key="d.day">
                        <div class="flex flex-col items-center gap-0.5"><span x-html="faces[d.mood]"></span><span class="text-[11px] text-ink-soft" x-text="d.day"></span></div>
                    </template>
                </div>
            </div>
            <div class="w-full flex flex-col lg:flex-row-reverse gap-2.5 mt-1">
                <form method="POST" action="{{ route('student.mood-prompt.respond') }}" class="lg:flex-[1.3]">@csrf<input type="hidden" name="response" value="accepted"><button class="btn btn-primary w-full !h-[52px]">Mau cerita</button></form>
                <button type="button" x-on:click="respond('dismissed')" class="lg:flex-1 btn btn-outline !h-[52px]">Nanti dulu</button>
            </div>
            <a href="{{ route('student.profile') }}#pengaturan" class="text-xs text-ink-soft underline">Matikan ajakan ini di Pengaturan</a>
        </section>
    </div>
    <template x-ref="faces">@foreach($moodDefs as $key => $label)<span data-mood="{{ $key }}"><x-mood-face :mood="$key" size="26" /></span>@endforeach</template>
</div>
@endsection

@push('modals')
@php
    // Langkah tanpa target tampil di tengah layar. Target yang tidak terlihat (mis. menu sidebar di HP) otomatis dilewati.
    $tourSteps = [
        ['target' => null, 'icon' => 'heart', 'title' => 'Hai '.$firstName.', selamat datang!', 'text' => 'Ayo keliling sebentar, kurang dari semenit.'],
        ['target' => 'mulai-cerita', 'title' => 'Mulai cerita di sini', 'text' => 'Pilih Guru BK dan topik, lalu ngobrol. Guru hanya melihat nama samaranmu.'],
        ['target' => 'mood', 'title' => 'Check-in mood harian', 'text' => 'Ketuk wajah yang paling pas hari ini. Hanya kamu yang bisa melihatnya.'],
        ['target' => 'mood-minggu', 'title' => 'Mood minggu ini', 'text' => 'Mood 7 hari terakhir. Ketuk untuk buka jurnal.'],
        ['target' => 'lanjut', 'title' => 'Lanjutkan cerita', 'text' => 'Obrolan yang masih berjalan ada di sini.'],
        ['target' => 'bacaan', 'title' => 'Bacaan untukmu', 'text' => 'Artikel pilihan Guru BK, baca kapan saja.'],
        ['target' => 'kenalan-guru', 'title' => 'Kenalan sama Guru BK', 'text' => 'Lihat profil dan status online Guru BK.'],
        ['target' => 'nav-beranda', 'title' => 'Beranda', 'text' => 'Halaman utamamu.'],
        ['target' => 'nav-cerita', 'title' => 'Semua ceritamu', 'text' => 'Semua obrolanmu dengan Guru BK.'],
        ['target' => 'nav-mood', 'title' => 'Jurnal mood', 'text' => 'Riwayat check-in mood-mu.'],
        ['target' => 'nav-selfhelp', 'title' => 'Self-Help', 'text' => 'Artikel dan tips menenangkan diri.'],
        ['target' => 'menu', 'title' => 'Menu lainnya', 'text' => 'Cerita Teman, Jadwal, Kenalan Guru BK, dan Keluar Akun.'],
        ['target' => 'nav-profil', 'title' => 'Profil & pengaturan', 'text' => 'Atur identitas, PIN, dan notifikasi.'],
        ['target' => 'notif', 'title' => 'Notifikasi', 'text' => 'Balasan guru dan undangan sesi.'],
        ['target' => 'darurat', 'title' => 'Bantuan darurat', 'text' => 'Merasa tidak aman? Langsung ke kontak darurat & Guru BK.'],
        ['target' => 'keluar-cepat', 'title' => 'Keluar Cepat', 'text' => 'Ada yang melihat layarmu? Tekan ini, Ctrl + Q, atau Esc 2x. Layar berganti dan akun terkunci.'],
        ['target' => 'ulang-tur', 'title' => 'Mau lihat tur lagi?', 'text' => 'Tekan tanda tanya ini untuk mengulang tur.'],
        ['target' => null, 'icon' => 'check', 'title' => 'Siap, semua sudah kamu kenal!', 'text' => 'Mulai dengan check-in mood atau langsung cerita.'],
    ];
@endphp
{{-- Walkthrough Beranda: selama berjalan seluruh halaman terkunci (tidak bisa diklik atau diketik), hanya Lanjut / Kembali / Lewati yang aktif. --}}
<div x-data="tour(@js($tourSteps), @js(route('student.tour.complete')), @js($showTour || request()->boolean('tur')))" data-tour-auto="{{ $showTour ? 1 : 0 }}" x-on:tour-start.window="start()" x-show="active" x-cloak x-trap.inert="active" class="tour fixed inset-0 z-[80]" role="dialog" aria-modal="true" aria-labelledby="tour-title">
    <div class="absolute inset-0" x-on:wheel.prevent x-on:touchmove.prevent x-on:click="nudge()"></div>
    <div class="tour-spot" :class="hole ? '' : 'is-empty'" :style="hole ? `top:${hole.top}px; left:${hole.left}px; width:${hole.width}px; height:${hole.height}px; border-radius:${hole.radius}` : ''"></div>
    <section x-ref="card" class="tour-card fixed w-[min(360px,calc(100vw-32px))] bg-white rounded-[24px] border-[2.5px] border-ink shadow-[5px_5px_0_#3E3530] p-5 flex flex-col gap-3" :class="[shake && 'is-shake', hole ? '' : 'is-center']" :style="`left:${box.left}px; top:${box.top}px`">
        <template x-if="box.arrow"><span class="tour-arrow" :class="'is-' + box.arrow" :style="{ left: box.arrowX + 'px' }"></span></template>
        <span x-show="!hole && step.icon" class="w-14 h-14 rounded-2xl bg-terracotta-tint text-terracotta-deep border-2 border-ink flex items-center justify-center -rotate-3">
            <span x-show="step.icon === 'heart'" class="flex"><x-icon name="heart" size="28" /></span>
            <span x-show="step.icon === 'check'" class="flex"><x-icon name="check" size="28" /></span>
        </span>
        <div class="flex items-center gap-2">
            <span class="font-hand text-[22px] leading-none text-terracotta-deep" x-text="`${i + 1} dari ${list.length}`"></span>
            <span class="flex-1 h-1.5 rounded-full bg-cream-light overflow-hidden border border-ink/15"><span class="block h-full bg-terracotta transition-[width] duration-300" :style="`width:${(i + 1) / list.length * 100}%`"></span></span>
        </div>
        <div class="tour-copy flex flex-col gap-1.5">
            <h2 id="tour-title" class="text-xl leading-tight" x-text="step.title"></h2>
            <p class="text-[15px] leading-relaxed text-ink-soft" x-text="step.text"></p>
        </div>
        <div class="flex items-center gap-2 mt-1">
            <button type="button" x-show="!last" x-on:click="finish()" class="text-sm font-extrabold text-ink-soft underline-offset-4 hover:underline mr-auto">Lewati tur</button>
            <span x-show="last" class="mr-auto"></span>
            <button type="button" x-show="i > 0" x-on:click="prev()" class="btn btn-outline btn-sm !text-sm">Kembali</button>
            <button type="button" x-ref="next" x-on:click="next()" class="btn btn-primary btn-sm !text-sm" x-text="last ? 'Selesai' : 'Lanjut'"></button>
        </div>
    </section>
</div>
@endpush

@push('scripts')
<script>
    window.__promptDays = {{ Js::from(($prompt ?? collect())->map(fn ($e) => ['mood' => $e->mood, 'day' => $e->entry_date->translatedFormat('D')])->values()) }};
    window.__promptRespondUrl = @js(route('student.mood-prompt.respond'));
</script>
@endpush
