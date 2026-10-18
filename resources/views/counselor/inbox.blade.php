@extends('layouts.counselor', ['active' => 'inbox'])
@section('title', 'Inbox Triase')
@section('header')
<header class="flex items-center justify-between gap-4 flex-wrap no-print">
    <div class="flex items-center gap-3 min-w-0">
    <x-back-button :href="route('counselor.dashboard')" />
    <div class="flex flex-col gap-0.5">
        <h1 class="text-2xl lg:text-[26px] leading-tight">Inbox Triase</h1>
        <span class="text-sm text-ink-soft">{{ $counts['unread'] }} pesan belum dibaca · paling mendesak di atas</span>
    </div>
    </div>
</header>
@endsection
@section('content')
@php $extra = collect($filters)->only(['topic', 'status', 'identity'])->filter()->count(); @endphp
<div class="flex flex-col gap-3" data-module="inbox" x-data="inboxFeed('{{ route('counselor.inbox.feed') }}', {{ Js::from($filters) }}, 'counselor.{{ auth()->id() }}')">
    <form method="GET" class="flex gap-2 flex-wrap items-center" x-ref="form" x-data="{ more: false }">
        <div class="relative flex-[1_1_260px] max-w-[420px]">
            <label for="inbox-search" class="sr-only">Cari percakapan</label>
            <x-icon name="search" size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-ink-soft" />
            <input id="inbox-search" name="q" type="search" value="{{ $filters['q'] ?? '' }}" placeholder="Cari nama samaran, nama, atau topik" class="field !pl-9 text-sm">
        </div>
        <input type="hidden" name="urgency" value="{{ $filters['urgency'] ?? '' }}">
        <div role="group" aria-label="Filter urgensi" class="seg max-w-full overflow-x-auto">
            @foreach(['' => ['Semua', $counts['all']], 'unassessed' => [\App\Models\Conversation::UNASSESSED_LABEL, $counts['unassessed']], 'urgent' => [\App\Models\Conversation::URGENCY_LABELS['urgent'], $counts['urgent']], 'important' => [\App\Models\Conversation::URGENCY_LABELS['important'], $counts['important']], 'normal' => [\App\Models\Conversation::URGENCY_LABELS['normal'], $counts['normal']]] as $k => [$l, $n])
                @php $on = ($filters['urgency'] ?? '') === $k; @endphp
                <a href="{{ route('counselor.inbox', array_filter(array_merge($filters, ['urgency' => $k]))) }}" aria-pressed="{{ $on ? 'true' : 'false' }}" class="seg-item no-underline {{ $on ? 'seg-item-on' : '' }}">{{ $l }} <span class="text-ink-soft font-bold">{{ $n }}</span></a>
            @endforeach
        </div>
        <div class="relative" x-on:keydown.escape="more = false">
            <button type="button" x-on:click="more = !more" :aria-expanded="more" class="btn btn-soft btn-sm"><x-icon name="filter" size="15" />Filter @if($extra)<span class="min-w-5 h-5 px-1.5 rounded-full bg-ink text-cream text-[11px] flex items-center justify-center leading-none pt-px">{{ $extra }}</span>@endif</button>
            <div x-show="more" x-cloak x-on:click.outside="more = false" x-transition.origin.top.right class="absolute right-0 top-full mt-1.5 z-30 w-[260px] bg-white rounded-2xl shadow-modal p-3.5 flex flex-col gap-2.5">
                <div><label for="f-topik" class="field-label">Topik</label>
                    <select id="f-topik" name="topic" class="field text-sm"><option value="">Semua topik</option>@foreach($topics as $t)<option value="{{ $t->slug }}" @selected(($filters['topic'] ?? '') === $t->slug)>{{ $t->name }}</option>@endforeach</select></div>
                <div><label for="f-status" class="field-label">Status</label>
                    <select id="f-status" name="status" class="field text-sm"><option value="">Semua status</option>@foreach(\App\Models\Conversation::STATUS_LABELS as $k => $l)<option value="{{ $k }}" @selected(($filters['status'] ?? '') === $k)>{{ $l }}</option>@endforeach</select></div>
                <div><label for="f-id" class="field-label">Identitas</label>
                    <select id="f-id" name="identity" class="field text-sm"><option value="">Anonim & teridentifikasi</option><option value="anonim" @selected(($filters['identity'] ?? '') === 'anonim')>Anonim saja</option><option value="teridentifikasi" @selected(($filters['identity'] ?? '') === 'teridentifikasi')>Teridentifikasi saja</option></select></div>
                <div class="flex justify-between gap-2 pt-1">
                    <a href="{{ route('counselor.inbox', array_filter(['urgency' => $filters['urgency'] ?? null])) }}" class="btn btn-ghost btn-sm">Reset</a>
                    <button class="btn btn-sage btn-sm">Terapkan</button>
                </div>
            </div>
        </div>
    </form>
    <div class="box overflow-hidden">
        {{-- Judul kolom (desktop) agar guru langsung paham isi tiap baris --}}
        <div class="hidden lg:grid grid-cols-[44px_minmax(0,1fr)_170px_170px_120px] gap-x-4 px-5 py-3 bg-cream-light/70 border-b border-line-soft text-[11px] font-extrabold tracking-[0.08em] uppercase text-ink-soft">
            <span></span><span>Murid & pesan terakhir</span><span>Urgensi · prioritas</span><span>Status</span><span class="text-right">Terakhir</span>
        </div>
        <ul class="flex flex-col" x-ref="list" aria-live="polite">
            @include('counselor.partials.inbox-list')
        </ul>
    </div>
    <p class="flex flex-wrap items-center gap-x-4 gap-y-1.5 px-1 text-xs font-bold text-ink-soft">
        <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-danger-tint shadow-[inset_3px_0_0_var(--color-danger)]"></span>Garis merah = prioritas kritis, tangani dulu</span>
        <span class="inline-flex items-center gap-1.5"><span class="min-w-[18px] h-[18px] px-1 rounded-full bg-terracotta-deep text-white text-[10px] flex items-center justify-center leading-none pt-px">2</span>Pesan belum dibaca</span>
        <span>Arahkan kursor ke baris untuk melihat alasan prioritasnya.</span>
    </p>
</div>
@endsection
