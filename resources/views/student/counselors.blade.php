@extends('layouts.student', ['active' => 'guru'])
@section('title', 'Kenalan Sama Guru BK')
@section('subtitle', 'Pilih yang paling bikin kamu nyaman.')
@section('content')
@php $palette = [['#E4EADF', '#46593B'], ['#F3E6D0', '#6F5229'], ['#F6DDD7', '#8E3E2F']]; @endphp
{{-- Kartu profil horizontal, 2 kolom sama tinggi. Bila jumlah guru ganjil, slot terakhir diisi kartu "Siapa saja yang tersedia" agar tetap simetris. --}}
<div class="grid grid-cols-[minmax(0,1fr)] md:grid-cols-2 gap-5">
    @forelse($counselors as $g)
        @php $p = $g->counselorProfile; [$bg, $fg] = $palette[$g->id % 3]; $st = $availability->statusFor($g); $nick = $p?->nickname ?? $g->name; @endphp
        <article class="bg-white border border-line-soft rounded-panel shadow-soft p-5 sm:p-6 flex gap-5 transition hover:shadow-lift hover:-translate-y-0.5">
            <div class="flex-none flex flex-col items-center gap-2.5">
                @if($p?->photoUrl())
                    <img src="{{ $p->photoUrl() }}" alt="Foto {{ $nick }}" class="w-[88px] h-[88px] sm:w-[104px] sm:h-[104px] rounded-[28px] object-cover">
                @else
                    <span class="w-[88px] h-[88px] sm:w-[104px] sm:h-[104px] rounded-[28px] flex items-center justify-center font-display text-[40px] sm:text-[46px]" style="background: {{ $bg }}; color: {{ $fg }}" aria-hidden="true">{{ $g->initial() }}</span>
                @endif
                <span class="inline-flex items-center gap-1.5 text-xs font-extrabold" :class="$store.presence.is({{ $g->id }}, {{ $st['online'] ? 'true' : 'false' }}) ? 'text-sage-ink' : 'text-ink-soft'">
                    <span class="w-2.5 h-2.5 rounded-full {{ $st['online'] ? 'status-on' : 'status-off' }}" :class="{ 'status-on': $store.presence.is({{ $g->id }}, {{ $st['online'] ? 'true' : 'false' }}), 'status-off': !$store.presence.is({{ $g->id }}, {{ $st['online'] ? 'true' : 'false' }}) }"></span>
                    <span x-text="$store.presence.is({{ $g->id }}, {{ $st['online'] ? 'true' : 'false' }}) ? 'Online' : 'Offline'">{{ $st['online'] ? 'Online' : 'Offline' }}</span>
                </span>
            </div>
            <div class="flex-1 min-w-0 flex flex-col gap-3">
                <div class="flex flex-col">
                    <h2 class="text-[22px] leading-tight truncate">{{ $nick }}</h2>
                    <span class="text-sm font-bold text-ink-soft truncate">{{ $g->name }}</span>
                </div>
                <p class="min-h-[66px] text-[15px] leading-[1.45] line-clamp-3 {{ $p?->intro_message ? 'text-ink' : 'text-ink-soft italic' }}">{{ $p?->intro_message ? '“'.$p->intro_message.'”' : 'Belum menulis perkenalan.' }}</p>
                <div class="flex flex-wrap gap-1.5 text-xs font-bold">
                    <span class="inline-flex items-center gap-1.5 h-7 px-2.5 rounded-full bg-cream-light border border-line-soft max-w-full"><x-icon name="heart" size="12" class="flex-none text-terracotta" /><span class="truncate">{{ $p?->hobbies ?: 'Hobi belum diisi' }}</span></span>
                    <span class="inline-flex items-center gap-1.5 h-7 px-2.5 rounded-full bg-cream-light border border-line-soft max-w-full"><x-icon name="mood" size="12" class="flex-none text-sand-deep" /><span class="truncate">{{ $p?->favorite_food ?: 'Makanan favorit belum diisi' }}</span></span>
                </div>
                <div class="mt-auto pt-3 border-t border-line-soft flex items-center gap-3 flex-wrap">
                    <span class="flex-1 min-w-[150px] inline-flex items-center gap-1.5 text-[13px] font-bold text-ink-soft"><x-icon name="clock" size="14" class="flex-none" /><span class="truncate">{{ $availability->summary($g) }}</span></span>
                    <a href="{{ route('student.conversations.create', ['guru' => $g->id]) }}" class="inline-flex items-center gap-1.5 h-10 px-4 rounded-full bg-terracotta-deep text-white no-underline text-sm font-extrabold transition hover:bg-terracotta-ink whitespace-nowrap">Mulai Cerita<x-icon name="arrow-right" size="15" /></a>
                </div>
            </div>
        </article>
    @empty
        <div class="md:col-span-2 card !rounded-panel"><x-empty-state title="Belum ada Guru BK aktif" text="Coba lagi nanti, atau buka Bantuan Darurat jika butuh bantuan segera." :cta-href="route('emergency')" cta-label="Bantuan Darurat" /></div>
    @endforelse
    @if($counselors->count() % 2 === 1)
        <a href="{{ route('student.conversations.create') }}" class="group no-underline bg-sage-tint border border-sage/25 rounded-panel p-5 sm:p-6 flex gap-5 items-center text-sage-ink transition hover:-translate-y-0.5 hover:shadow-soft">
            <span class="flex-none w-[88px] h-[88px] sm:w-[104px] sm:h-[104px] rounded-[28px] bg-white/70 flex items-center justify-center"><x-icon name="people-heart" size="44" /></span>
            <span class="flex-1 min-w-0 flex flex-col gap-1.5">
                <span class="font-display text-[22px] leading-tight">Belum yakin pilih siapa?</span>
                <span class="text-[15px] leading-snug text-sage-ink2">Pilih “Siapa saja yang tersedia”. Kamu tetap bisa mulai sebagai anonim.</span>
                <span class="mt-1 inline-flex items-center gap-1.5 text-sm font-extrabold">Mulai Cerita<x-icon name="arrow-right" size="15" class="transition-transform group-hover:translate-x-0.5" /></span>
            </span>
        </a>
    @endif
</div>
@if($counselors->count() % 2 === 0 && $counselors->isNotEmpty())
<p class="flex items-center justify-center gap-2 text-sm font-bold text-sage-ink"><x-icon name="lock" size="15" />Siapa pun yang kamu pilih, kamu tetap bisa mulai sebagai anonim.</p>
@endif
@endsection
