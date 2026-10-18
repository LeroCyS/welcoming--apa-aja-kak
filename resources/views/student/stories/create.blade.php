@extends('layouts.student', ['active' => 'teman'])
@section('title', 'Bagikan Ceritamu')
@section('subtitle', 'Tampil tanpa nama setelah ditinjau Guru BK.')
@section('back', route('student.stories.index'))
@section('content')
@if($limited)
    <div class="card !rounded-panel"><x-empty-state title="Terima kasih sudah berbagi hari ini" text="Kamu bisa membagikan cerita lagi besok. Setiap cerita sangat berarti." :cta-href="route('student.stories.index')" cta-label="Baca Cerita Teman" /></div>
@else
{{-- Dua kolom: form di kiri, pratinjau langsung + tips di kanan. --}}
<div class="grid grid-cols-[minmax(0,1fr)] lg:grid-cols-[minmax(0,1fr)_420px] gap-6 items-stretch" x-data="{ body: @js(old('body', '')), label: @js(old('label', $grade ? 'grade' : 'anon')) }">
    <form method="POST" action="{{ route('student.stories.store') }}" class="bg-white border border-line-soft rounded-panel shadow-soft p-5 sm:p-7 flex flex-col gap-6">
        @csrf
        <div class="flex flex-col gap-2">
            <label for="s-body" class="text-base font-extrabold">Apa yang ingin kamu ceritakan?</label>
            <textarea id="s-body" name="body" x-model="body" rows="7" placeholder="Contoh: Hari ini aku berhasil ngomong ke orang tua soal jurusan." class="field !rounded-[18px] !bg-cream-light/60 @error('body') is-invalid @enderror"></textarea>
            <p class="flex items-center gap-1.5 text-[13px] font-bold text-ink-soft"><x-icon name="alert" size="14" class="flex-none" />Jangan tulis nama, kelas, atau detail yang bisa dikenali.</p>
            @error('body')<p class="field-error">{{ $message }}</p>@enderror
        </div>

        <x-song-input id="story-song" :value="old('song_url')" label="Tambahkan lagu (opsional)" />

        <fieldset class="flex flex-col gap-2.5">
            <legend class="text-base font-extrabold mb-2.5">Tampilkan sebagai</legend>
            <div class="grid {{ $grade ? 'sm:grid-cols-2' : '' }} gap-2.5">
                @if($grade)
                    <label class="flex items-center gap-3 p-4 rounded-card bg-cream-light border-[1.5px] border-line cursor-pointer transition has-[:checked]:border-terracotta-deep has-[:checked]:bg-terracotta-tint2"><input type="radio" name="label" value="grade" x-model="label" class="w-5 h-5 accent-terracotta-deep" @checked(old('label', 'grade') === 'grade')><span class="flex flex-col"><span class="font-extrabold">Murid kelas {{ $grade }}</span><span class="text-xs text-ink-soft">Lebih terasa dekat</span></span></label>
                @endif
                <label class="flex items-center gap-3 p-4 rounded-card bg-cream-light border-[1.5px] border-line cursor-pointer transition has-[:checked]:border-terracotta-deep has-[:checked]:bg-terracotta-tint2"><input type="radio" name="label" value="anon" x-model="label" class="w-5 h-5 accent-terracotta-deep" @checked(old('label', $grade ? 'grade' : 'anon') === 'anon')><span class="flex flex-col"><span class="font-extrabold">Seorang teman</span><span class="text-xs text-ink-soft">Paling tersembunyi</span></span></label>
            </div>
        </fieldset>

        <div class="flex flex-col gap-4 pt-5 border-t border-line-soft">
            <label class="flex items-start gap-3 text-[15px] leading-snug cursor-pointer"><input type="checkbox" name="consent" value="1" class="mt-0.5 w-5 h-5 flex-none accent-terracotta-deep" @checked(old('consent'))><span>Aku setuju ceritaku ditinjau Guru BK lalu ditampilkan tanpa nama untuk murid di sekolahku.</span></label>
            @error('consent')<p class="field-error -mt-2">{{ $message }}</p>@enderror
            <button class="btn btn-primary btn-lg w-full sm:w-auto sm:self-start"><x-icon name="send" size="18" />Kirim untuk ditinjau</button>
        </div>
    </form>

    <aside class="flex flex-col gap-4">
        <section aria-label="Pratinjau" class="flex flex-col gap-2.5">
            <span class="px-1 text-[13px] font-extrabold tracking-[0.08em] uppercase text-ink-soft">Begini ceritamu tampil</span>
            <article class="bg-white border border-line-soft rounded-card-l p-5 shadow-soft flex flex-col gap-3.5">
                <header class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-2xl bg-terracotta-tint text-terracotta-ink flex items-center justify-center flex-none"><x-icon name="people-heart" size="20" /></span>
                    <span class="flex flex-col"><span class="font-extrabold text-[15px]" x-text="label === 'grade' ? @js('Murid kelas '.$grade) : 'Seorang teman'">Seorang teman</span><span class="text-xs text-ink-soft">baru saja</span></span>
                </header>
                <p class="text-[15px] leading-[1.65] whitespace-pre-line wrap-anywhere line-clamp-6" :class="!body.trim() && 'text-ink-soft italic'" x-text="body.trim() || 'Ceritamu akan muncul di sini…'"></p>
                <footer class="flex items-center gap-1.5 flex-wrap">
                    @foreach(\App\Models\StoryPost::REACTIONS as $type => [$rLabel, $icon])
                        <span class="inline-flex items-center gap-1 h-8 px-2.5 rounded-full border-[1.5px] border-line text-xs font-extrabold text-ink-soft whitespace-nowrap"><x-icon :name="$icon" size="14" />{{ $rLabel }}</span>
                    @endforeach
                </footer>
            </article>
        </section>
        <section class="bg-sage-tint border border-sage/25 rounded-card-l p-5 flex flex-col gap-3 text-sage-ink">
            <span class="inline-flex items-center gap-2 font-extrabold"><x-icon name="lock" size="18" />Tetap tanpa nama</span>
            <ul class="flex flex-col gap-2 text-sm leading-snug text-sage-ink2">
                <li class="flex gap-2"><x-icon name="check" size="16" class="mt-0.5 flex-none" />Tidak ada yang tahu penulisnya.</li>
                <li class="flex gap-2"><x-icon name="check" size="16" class="mt-0.5 flex-none" />Guru BK meninjau dulu sebelum tampil.</li>
                <li class="flex gap-2"><x-icon name="check" size="16" class="mt-0.5 flex-none" />Teman hanya bisa memberi dukungan, bukan komentar.</li>
            </ul>
        </section>
        <section class="flex-1 bg-sand-tint border border-sand/30 rounded-card-l p-5 flex flex-col gap-3 text-sand-ink2">
            <span class="inline-flex items-center gap-2 font-extrabold"><x-icon name="edit" size="18" />Butuh ide?</span>
            <ul class="flex-1 flex flex-col justify-center gap-2 text-sm leading-snug">
                <li class="px-3.5 py-2.5 rounded-2xl bg-white/70">“Hal kecil yang bikin aku lega minggu ini…”</li>
                <li class="px-3.5 py-2.5 rounded-2xl bg-white/70">“Dulu aku takut …, sekarang aku belajar…”</li>
                <li class="px-3.5 py-2.5 rounded-2xl bg-white/70">“Buat yang lagi ngerasa sendirian…”</li>
            </ul>
        </section>
    </aside>
</div>
@endif
@endsection
