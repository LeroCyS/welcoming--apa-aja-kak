@extends('layouts.counselor', ['active' => 'profil'])
@section('title', 'Profil Guru')
@section('header')
<header class="flex items-center gap-3 min-w-0 no-print">
    <x-back-button :href="route('counselor.dashboard')" />
    <div class="flex flex-col gap-0.5"><h1 class="text-2xl lg:text-[28px] leading-tight">Profil Guru</h1><span class="text-sm text-ink-soft">Tampil di halaman Kenalan Guru BK</span></div>
</header>
@endsection
@section('content')
@php
    $initialTab = $errors->template->any() ? 'template' : ($errors->has('days') ? 'jam' : ($errors->has('offline_auto_reply') ? 'autoreply' : 'tentang'));
    $activeDays = $hours->filter()->count();
    $sections = [
        'tentang' => ['person', 'Tentang aku', 'Foto, nama panggilan & perkenalan', 'bg-sage-tint text-sage-ink'],
        'jam' => ['clock', 'Jam layanan', $activeDays.' hari aktif dalam seminggu', 'bg-sand-tint text-sand-ink'],
        'autoreply' => ['chat', 'Auto-reply', 'Saat kamu offline / di luar jam', 'bg-terracotta-tint text-terracotta-ink'],
        'template' => ['template', 'Template balasan', $templates->count().' template · pakai /shortcut', 'bg-cream-deep text-ink'],
    ];
@endphp
<div class="grid grid-cols-[minmax(0,1fr)] lg:grid-cols-[300px_minmax(0,1fr)] gap-5 items-start"
     x-data="{ tab: new URLSearchParams(location.search).get('tab') || (location.hash === '#template' ? 'template' : @js($initialTab)), nick: @js(old('nickname', $profile->nickname)), hobi: @js(old('hobbies', $profile->hobbies)), food: @js(old('favorite_food', $profile->favorite_food)), intro: @js(old('intro_message', $profile->intro_message)), photo: @js($profile->photoUrl()) }">

    {{-- Kiri: kartu profil + menu bagian --}}
    <aside class="flex flex-col gap-4 lg:sticky lg:top-24">
        <section class="box overflow-hidden">
            <div class="h-[72px] bg-sage-tint [background-image:radial-gradient(#BFCDB3_1.4px,transparent_1.4px)] [background-size:16px_16px]"></div>
            <div class="px-5 pb-5 -mt-10 flex flex-col items-center gap-1.5 text-center">
                <span class="w-[84px] h-[84px] rounded-[26px] bg-white p-1.5 shadow-soft"><span class="w-full h-full rounded-[20px] bg-sage-tint flex items-center justify-center font-display text-[34px] text-sage-ink2 overflow-hidden"><template x-if="photo"><img :src="photo" alt="" class="w-full h-full object-cover"></template><span x-show="!photo">{{ $user->initial() }}</span></span></span>
                <span class="font-display text-[22px] leading-tight mt-1" x-text="nick">{{ $profile->nickname }}</span>
                <span class="text-sm font-bold text-ink-soft">{{ $user->name }}</span>
                <span class="mt-1 inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold {{ $profile->is_online ? 'bg-sage-tint text-sage-ink' : 'bg-cream text-ink-soft' }}"><span class="w-2 h-2 rounded-full {{ $profile->is_online ? 'status-on' : 'status-off' }}"></span>{{ $profile->is_online ? 'Online' : 'Offline' }}</span>
            </div>
        </section>
        <nav aria-label="Bagian profil" class="box p-2 flex flex-col" role="tablist">
            @foreach($sections as $k => [$icon, $label, $desc, $tone])
                <button type="button" role="tab" :aria-selected="(tab === '{{ $k }}').toString()" x-on:click="tab = '{{ $k }}'" class="flex items-center gap-3 px-3 py-2.5 rounded-2xl text-left transition-colors" :class="tab === '{{ $k }}' ? 'bg-sage-tint/60' : 'hover:bg-cream-light'">
                    <span class="w-9 h-9 flex-none rounded-xl flex items-center justify-center {{ $tone }}"><x-icon :name="$icon" size="17" /></span>
                    <span class="flex-1 min-w-0 flex flex-col"><span class="text-sm font-extrabold">{{ $label }}</span><span class="text-xs text-ink-soft truncate">{{ $desc }}</span></span>
                    <x-icon name="chevron-right" size="15" class="text-ink-soft" />
                </button>
            @endforeach
        </nav>
    </aside>

    {{-- Kanan: isi bagian --}}
    <div class="min-w-0 flex flex-col gap-4">
        {{-- Tentang aku + preview --}}
        <div x-show="tab === 'tentang'" class="grid grid-cols-[minmax(0,1fr)] xl:grid-cols-[minmax(0,1fr)_300px] gap-4 items-start">
            <form method="POST" action="{{ route('counselor.profile.update') }}" enctype="multipart/form-data" class="box p-5 sm:p-6 flex flex-col gap-5">
                @csrf @method('PUT')
                <h2 class="text-xl">Tentang aku</h2>
                <div class="flex items-center gap-4 flex-wrap p-3.5 rounded-2xl bg-cream-light">
                    <span class="w-16 h-16 rounded-[20px] bg-sage-tint flex items-center justify-center font-display text-3xl text-sage-ink2 overflow-hidden flex-none">
                        <template x-if="photo"><img :src="photo" alt="Foto profil" class="w-full h-full object-cover"></template>
                        <span x-show="!photo">{{ $user->initial() }}</span>
                    </span>
                    <div class="flex-1 min-w-[200px] flex flex-col gap-1.5">
                        <label for="pf-photo" class="self-start inline-flex items-center gap-1.5 h-9 px-4 rounded-full bg-sage-tint text-sage-ink text-[13px] font-extrabold cursor-pointer transition hover:bg-sage/30"><x-icon name="image" size="15" />Ganti foto</label>
                        <input id="pf-photo" name="photo" type="file" accept="image/png,image/jpeg,image/webp" data-shrink class="sr-only" x-on:change="photo = URL.createObjectURL($event.target.files[0])">
                        <span class="text-xs text-ink-soft">Foto yang tersenyum lebih disukai murid. Foto besar diperkecil otomatis.</span>
                        @error('photo')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="grid sm:grid-cols-2 gap-3">
                    <div><label for="pf-nick" class="field-label">Nama panggilan</label><input id="pf-nick" name="nickname" x-model="nick" class="field" required>@error('nickname')<p class="field-error">{{ $message }}</p>@enderror</div>
                    <div><label for="pf-gender" class="field-label">Gender (untuk preferensi murid)</label><select id="pf-gender" name="gender" class="field"><option value="">Tidak ditampilkan</option><option value="female" @selected($profile->gender === 'female')>Perempuan</option><option value="male" @selected($profile->gender === 'male')>Laki-laki</option></select></div>
                    <div><label for="pf-hobi" class="field-label">Hobi</label><input id="pf-hobi" name="hobbies" x-model="hobi" class="field"></div>
                    <div><label for="pf-food" class="field-label">Makanan favorit</label><input id="pf-food" name="favorite_food" x-model="food" class="field"></div>
                </div>
                <div><label for="pf-intro" class="field-label">Kalimat pengantar</label><textarea id="pf-intro" name="intro_message" x-model="intro" rows="3" maxlength="500" class="field"></textarea><span class="text-xs text-ink-soft">Cerita kecil tentang dirimu membuat murid merasa lebih dekat.</span></div>
                <button class="self-start btn btn-sage">Simpan profil</button>
            </form>

            <section aria-label="Preview kartu profil" class="flex flex-col gap-2 xl:sticky xl:top-24">
                <span class="px-1 inline-flex items-center gap-1.5 text-[11px] font-extrabold tracking-[0.08em] uppercase text-ink-soft"><x-icon name="eye" size="13" />Yang dilihat murid</span>
                <article class="bg-white rounded-panel border border-line-soft shadow-soft p-4 flex flex-col gap-3">
                    <div class="flex items-center gap-3">
                        <span class="w-14 h-14 rounded-[18px] bg-sage-tint flex items-center justify-center font-display text-2xl text-sage-ink2 overflow-hidden flex-none"><template x-if="photo"><img :src="photo" alt="" class="w-full h-full object-cover"></template><span x-show="!photo">{{ $user->initial() }}</span></span>
                        <span class="min-w-0 flex flex-col"><span class="font-display text-lg leading-tight truncate" x-text="nick"></span><span class="text-xs font-bold text-ink-soft truncate">{{ $user->name }}</span></span>
                    </div>
                    <p class="text-sm leading-[1.5]" :class="!intro && 'text-ink-soft italic'" x-text="intro ? '“' + intro + '”' : 'Belum menulis perkenalan.'"></p>
                    <div class="flex flex-wrap gap-1.5 text-xs font-bold">
                        <span class="inline-flex items-center gap-1.5 h-7 px-2.5 rounded-full bg-cream-light border border-line-soft"><x-icon name="heart" size="12" class="text-terracotta" /><span x-text="hobi || 'Hobi belum diisi'"></span></span>
                        <span class="inline-flex items-center gap-1.5 h-7 px-2.5 rounded-full bg-cream-light border border-line-soft"><x-icon name="mood" size="12" class="text-sand-deep" /><span x-text="food || 'Makanan favorit belum diisi'"></span></span>
                    </div>
                    <span class="inline-flex items-center justify-center gap-1.5 h-10 rounded-full bg-terracotta-deep text-white text-sm font-extrabold">Mulai Cerita<x-icon name="arrow-right" size="15" /></span>
                </article>
            </section>
        </div>

        {{-- Jam layanan --}}
        <form method="POST" action="{{ route('counselor.profile.hours') }}" x-show="tab === 'jam'" x-cloak class="box p-5 sm:p-6 flex flex-col gap-4">
            @csrf @method('PUT')
            <div class="flex flex-col gap-1"><h2 class="text-xl">Jam layanan</h2><p class="text-[13px] text-ink-soft">Di luar jam layanan atau saat Offline, murid menerima auto-reply.</p></div>
            @error('days')<p class="field-error">{{ $message }}</p>@enderror
            <div class="flex flex-col gap-2">
                @foreach($hours as $d => $h)
                    <div class="flex items-center gap-3 flex-wrap px-3.5 py-2.5 rounded-2xl border transition-colors" x-data="{ on: {{ $h ? 'true' : 'false' }} }" :class="on ? 'bg-sage-tint/40 border-sage/30' : 'bg-cream-light border-line-soft'">
                        <label class="w-[120px] flex items-center gap-2.5 font-extrabold text-sm cursor-pointer"><input type="checkbox" name="days[{{ $d }}][active]" value="1" x-model="on" class="w-5 h-5 accent-sage-deep">{{ \App\Models\CounselorServiceHour::DAY_NAMES[$d] }}</label>
                        <input type="time" name="days[{{ $d }}][start]" value="{{ $h ? substr($h->start_time, 0, 5) : '07:00' }}" :disabled="!on" aria-label="Jam mulai {{ \App\Models\CounselorServiceHour::DAY_NAMES[$d] }}" class="field !w-[124px] !h-10 disabled:opacity-40">
                        <span class="text-ink-soft">-</span>
                        <input type="time" name="days[{{ $d }}][end]" value="{{ $h ? substr($h->end_time, 0, 5) : '15:00' }}" :disabled="!on" aria-label="Jam selesai {{ \App\Models\CounselorServiceHour::DAY_NAMES[$d] }}" class="field !w-[124px] !h-10 disabled:opacity-40">
                        <span class="ml-auto text-xs font-extrabold" :class="on ? 'text-sage-ink' : 'text-ink-soft'" x-text="on ? 'Melayani' : 'Libur'"></span>
                    </div>
                @endforeach
            </div>
            <button class="self-start btn btn-sage">Simpan jam layanan</button>
        </form>

        {{-- Auto-reply --}}
        <form method="POST" action="{{ route('counselor.profile.auto-reply') }}" x-show="tab === 'autoreply'" x-cloak class="box p-5 sm:p-6 flex flex-col gap-4">
            @csrf @method('PUT')
            <h2 class="text-xl">Pesan auto-reply</h2>
            <div class="grid sm:grid-cols-2 gap-2.5 text-[13px] leading-relaxed">
                <div class="p-3.5 rounded-2xl bg-terracotta-tint2 text-ink"><span class="flex items-center gap-1.5 font-extrabold text-terracotta-ink mb-1"><x-icon name="clock" size="14" />Kapan terkirim</span>Saat murid mengirim pesan dan kamu sedang <b>Offline</b> atau <b>di luar jam layanan</b>. Sekali per cerita sampai kamu membalas.</div>
                <div class="p-3.5 rounded-2xl bg-sand-tint/70 text-ink"><span class="flex items-center gap-1.5 font-extrabold text-sand-ink2 mb-1"><x-icon name="sparkle" size="14" />Kode otomatis</span><code class="px-1 rounded bg-white">{guru}</code> = nama panggilanmu, <code class="px-1 rounded bg-white">{jam_layanan}</code> = jam layananmu berikutnya.</div>
            </div>
            <textarea name="offline_auto_reply" rows="5" maxlength="1000" class="field" aria-label="Pesan auto-reply" x-ref="autoReply">{{ old('offline_auto_reply', $profile->autoReplyTemplate()) }}</textarea>
            <span class="text-xs text-ink-soft -mt-2">Link Self-Help & kontak darurat ditambahkan otomatis. Kosongkan untuk teks bawaan.</span>
            @error('offline_auto_reply')<p class="field-error">{{ $message }}</p>@enderror
            <div class="flex items-center gap-3 flex-wrap">
                <button class="btn btn-sage">Simpan auto-reply</button>
                <button type="button" x-on:click="$refs.autoReply.value = @js(\App\Models\CounselorProfile::DEFAULT_AUTO_REPLY)" class="text-[13px] font-extrabold text-sage-ink underline">Kembalikan ke teks bawaan</button>
            </div>
        </form>

        {{-- Template balasan (balasan cepat /shortcut) --}}
        <section id="template" x-show="tab === 'template'" x-cloak class="box p-5 sm:p-6 flex flex-col gap-4 scroll-mt-24" x-data="{ editing: null }">
            <div class="flex flex-col gap-1"><h2 class="text-xl">Template balasan</h2>
                <p class="text-[13px] text-ink-soft">Di ruang chat, ketik <code class="px-1.5 py-0.5 rounded-md bg-sage-tint text-sage-ink font-extrabold">/shortcut</code> lalu Enter untuk memasang template. <code class="px-1 rounded bg-cream">{sapaan}</code> = nama/nama samaran murid.</p>
            </div>
            @if($errors->template->any())<p class="field-error">{{ $errors->template->first() }}</p>@endif
            <div class="grid md:grid-cols-2 gap-3">
                @forelse($templates as $t)
                    <article class="rounded-2xl border border-line-soft bg-cream-light/50 p-4 flex flex-col gap-2.5">
                        <div class="flex items-start gap-2">
                            <span class="flex-none px-2 py-0.5 rounded-lg bg-white border border-sage/40 text-[13px] font-extrabold text-sage-ink">/{{ $t->shortcut }}</span>
                            <span class="flex-1 min-w-0 flex flex-col"><span class="font-extrabold text-[15px] truncate">{{ $t->title }}</span><span class="text-xs text-ink-soft">{{ $t->topic?->name ?? 'Semua topik' }}</span></span>
                        </div>
                        <p x-show="editing !== {{ $t->id }}" class="text-sm leading-relaxed text-ink-soft whitespace-pre-line line-clamp-4">{{ $t->body }}</p>
                        <form x-show="editing === {{ $t->id }}" x-cloak method="POST" action="{{ route('counselor.templates.update', $t) }}" class="flex flex-col gap-2">@csrf @method('PUT')
                            <div class="grid grid-cols-[1fr_130px] gap-2">
                                <input name="title" value="{{ $t->title }}" class="field !h-10 text-sm" required aria-label="Judul template">
                                <span class="relative"><span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-extrabold text-sage-ink">/</span><input name="shortcut" value="{{ $t->shortcut }}" maxlength="30" class="field !h-10 !pl-6 text-sm" aria-label="Shortcut"></span>
                            </div>
                            <select name="topic_id" class="field !h-10 text-sm" aria-label="Topik"><option value="">Semua topik</option>@foreach($topics as $tp)<option value="{{ $tp->id }}" @selected($t->topic_id === $tp->id)>{{ $tp->name }}</option>@endforeach</select>
                            <textarea name="body" rows="3" class="field text-sm" required aria-label="Isi template">{{ $t->body }}</textarea>
                            <div class="flex justify-end gap-2"><button type="button" x-on:click="editing = null" class="btn btn-ghost btn-sm">Batal</button><button class="btn btn-sage btn-sm">Simpan</button></div>
                        </form>
                        <div x-show="editing !== {{ $t->id }}" class="mt-auto pt-2 border-t border-line-soft grid grid-cols-2 gap-2">
                            <button type="button" x-on:click="editing = {{ $t->id }}" class="inline-flex items-center justify-center gap-1.5 h-9 rounded-full bg-white border border-line text-[13px] font-extrabold transition hover:bg-cream-light"><x-icon name="edit" size="14" />Edit</button>
                            <form method="POST" action="{{ route('counselor.templates.destroy', $t) }}" data-confirm="Hapus template “{{ $t->title }}”?" data-confirm-ok="Ya, hapus" data-confirm-tone="danger">@csrf @method('DELETE')<button class="w-full inline-flex items-center justify-center gap-1.5 h-9 rounded-full text-[13px] font-extrabold text-ink-soft transition hover:bg-danger-tint hover:text-danger"><x-icon name="trash" size="14" />Hapus</button></form>
                        </div>
                    </article>
                @empty
                    <p class="md:col-span-2 px-4 py-6 rounded-2xl border border-dashed border-line text-sm text-ink-soft text-center">Belum ada template pribadi. Buat satu di bawah.</p>
                @endforelse
            </div>
            <form method="POST" action="{{ route('counselor.templates.store') }}" class="flex flex-col gap-3 p-4 rounded-2xl bg-sage-tint/40 border border-sage/25">
                @csrf
                <span class="inline-flex items-center gap-1.5 font-extrabold text-sm text-sage-ink"><x-icon name="plus" size="15" />Tambah template</span>
                <div class="grid sm:grid-cols-[1fr_160px_1fr] gap-2">
                    <input name="title" placeholder="Judul, mis. Sapaan hangat" class="field" required aria-label="Judul template baru">
                    <span class="relative"><span class="absolute left-3.5 top-1/2 -translate-y-1/2 font-extrabold text-sage-ink">/</span><input name="shortcut" placeholder="salam" maxlength="30" class="field !pl-7" aria-label="Shortcut template baru"></span>
                    <select name="topic_id" class="field" aria-label="Topik template baru"><option value="">Semua topik</option>@foreach($topics as $tp)<option value="{{ $tp->id }}">{{ $tp->name }}</option>@endforeach</select>
                </div>
                <textarea name="body" rows="3" placeholder="Hai {sapaan}, …" class="field" required aria-label="Isi template baru"></textarea>
                <span class="text-xs text-sage-ink2 -mt-1">Shortcut boleh dikosongkan, akan dibuat otomatis dari judul.</span>
                <button class="self-start btn btn-sage btn-sm">Tambah template</button>
            </form>
        </section>
    </div>
</div>
@endsection
