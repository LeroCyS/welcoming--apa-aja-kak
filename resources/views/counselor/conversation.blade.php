@extends('layouts.counselor', ['active' => 'inbox'])
@section('title', $vm['display_name'])
@php
    $tab = in_array(request('tab'), ['kasus', 'catatan', 'ringkasan', 'sesi'], true) ? request('tab') : 'kasus';
    $urgCfg = ['value' => $c->urgency, 'url' => route('counselor.conversations.urgency', $c), 'styles' => \App\Models\Conversation::urgencyStyles()];
@endphp
@section('header')
<header class="flex items-center justify-between gap-3 flex-wrap no-print">
    <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-sm font-bold min-w-0"><x-back-button :href="route('counselor.inbox')" label="Kembali ke Inbox" class="!w-9 !h-9 mr-1" /><a href="{{ route('counselor.inbox') }}" class="text-ink-soft no-underline">Inbox Triase</a><span class="text-[#B9AFA0]">/</span><span class="truncate">{{ $vm['display_name'] }}</span></nav>
</header>
@endsection
@section('main-class', '!pt-4 !gap-3 !pb-4')
@section('content')
{{-- Tiga area: daftar (bisa disembunyikan) · chat · detail kasus (laci; di layar lebar jadi kolom). Pilihan buka/tutup diingat per perangkat. --}}
<div class="flex gap-3 items-stretch flex-1 min-h-0" data-module="counselor-chat" x-data="convLayout()">
    {{-- KIRI: daftar ringkas --}}
    <section x-show="list" x-cloak aria-label="Daftar percakapan" class="hidden lg:flex w-[248px] flex-none box p-2 flex-col gap-1 h-[calc(100vh-172px)] overflow-y-auto"
             x-data="inboxFeed('{{ route('counselor.inbox.feed') }}', {}, 'counselor.{{ auth()->id() }}', { compact: 1, current: '{{ $c->uuid }}' })">
        <div class="flex items-center justify-between px-2 pt-1 pb-1.5"><span class="box-title">Percakapan</span><button type="button" x-on:click="toggleList()" class="icon-btn !w-7 !h-7 !border-transparent hover:!bg-ink/5" aria-label="Sembunyikan daftar" title="Sembunyikan daftar"><x-icon name="chevron-left" size="16" /></button></div>
        <div x-ref="list" class="flex flex-col gap-0.5">@include('counselor.partials.inbox-compact', ['items' => $list, 'current' => $c->uuid])</div>
    </section>

    {{-- TENGAH: chat --}}
    <section aria-label="Percakapan" class="relative flex-1 min-w-0 box flex flex-col h-[calc(100vh-172px)] min-h-[520px] overflow-hidden"
             x-data="chatRoom({{ Js::from([
                'messagesUrl' => route('counselor.conversations.messages.index', $c),
                'sendUrl' => route('counselor.conversations.messages.store', $c),
                'channel' => 'conversation.'.$c->uuid,
                'lastId' => $lastId,
                'version' => $version,
                'draftKey' => 'wl-draft-g-'.$c->uuid,
                'role' => 'counselor',
                'typingUrl' => $vm['archived'] ? null : route('counselor.conversations.typing', $c),
                'imageUrl' => route('counselor.conversations.image.store', $c),
                'imageMaxKb' => config('welcoming.chat_image_max_kb'),
                'messageUrl' => route('counselor.conversations.messages.update', [$c, '__ID__']),
                'quickReplies' => $templates->sortByDesc('mine')->values(),
                'sapaan' => $sapaan,
             ]) }})">
        <div class="px-4 py-2.5 border-b border-line-soft flex items-center gap-3">
            <button type="button" x-show="!list" x-cloak x-on:click="toggleList()" class="hidden lg:inline-flex icon-btn" aria-label="Tampilkan daftar percakapan" title="Tampilkan daftar percakapan"><x-icon name="sidebar" size="17" /></button>
            @if($vm['identified'])<x-avatar-anon :name="$vm['display_name']" :size="36" :radius="12" />@else<x-avatar-anon :avatar="$vm['avatar']" :size="36" :radius="12" />@endif
            <span class="flex flex-col gap-1 flex-1 min-w-0">
                <span class="font-extrabold text-[15px] truncate">{{ $vm['display_name'] }}@if($vm['class_name']) <span class="text-ink-soft font-bold">· {{ $vm['class_name'] }}</span>@endif <span x-show="typing" x-cloak class="text-xs font-bold text-terracotta-ink">sedang mengetik…</span></span>
                <span class="flex gap-1.5 flex-wrap items-center text-xs">
                    {{-- Urgensi: klik untuk menilai/mengubah --}}
                    <span x-data="urgencyPicker(@js($urgCfg))" class="relative" x-on:keydown.escape="menu = false">
                        <button type="button" x-on:click="menu = !menu" :aria-expanded="menu" class="inline-flex items-center gap-1.5 px-2.5 py-[3px] rounded-full font-extrabold" :class="styles[key][1]" title="Urgensi menurut penilaianmu, klik untuk mengubah"><span class="w-[7px] h-[7px] rounded-full" :class="styles[key][2]"></span><span x-text="label">{{ $c->urgencyLabel() }}</span><x-icon name="chevron-down" size="12" /></button>
                        <span x-show="menu" x-cloak x-on:click.outside="menu = false" x-transition.origin.top.left class="absolute left-0 top-full mt-1 z-30 w-48 bg-white rounded-2xl shadow-modal p-1.5 flex flex-col">
                            @foreach(\App\Models\Conversation::URGENCY_LABELS as $k => $l)
                                <button type="button" x-on:click="set('{{ $k }}'); menu = false" class="flex items-center gap-2 px-3 py-2 rounded-xl text-[13px] font-bold text-left hover:bg-cream-light" :class="value === '{{ $k }}' && 'bg-cream'"><span class="w-2 h-2 rounded-full {{ \App\Models\Conversation::urgencyStyles()[$k][2] }}"></span>{{ $l }}</button>
                            @endforeach
                        </span>
                    </span>
                    {{-- HP: cukup urgensi; info lain ada di panel Detail kasus. --}}
                    <x-priority-badge :score="$c->priority_score" class="!py-[3px] !px-2 max-lg:!hidden" />
                    @foreach($vm['topics'] as $t)<span class="max-lg:hidden font-extrabold px-2 py-[3px] rounded-full bg-cream whitespace-nowrap">{{ $t['name'] }}</span>@endforeach
                    @if($vm['identified'])
                        <span class="max-lg:hidden inline-flex items-center gap-1 font-extrabold text-sand-ink" title="{{ $vm['revealed_at'] ? 'Identitas dibuka '.\Carbon\Carbon::parse($vm['revealed_at'])->timezone($tz)->translatedFormat('j M Y') : 'Teridentifikasi sejak awal' }}"><x-icon name="unlock" size="12" />Teridentifikasi</span>
                    @else
                        <span class="max-lg:hidden inline-flex items-center gap-1 font-extrabold text-sage-ink2"><x-icon name="lock" size="12" />Anonim</span>
                    @endif
                    @if($c->is_emergency)<span class="max-lg:hidden font-extrabold text-white bg-danger px-2 py-0.5 rounded-full whitespace-nowrap">Tombol darurat</span>@endif
                    @if($vm['new_member'])<span class="max-lg:hidden font-extrabold px-2 py-[3px] rounded-full bg-sand-tint text-sand-ink whitespace-nowrap" title="Murid baru bergabung ke sekolah ini (kurang dari {{ config('welcoming.new_member_days') }} hari) saat memulai cerita. Tetap tangani seperti biasa.">Anggota baru</span>@endif
                    @if($vm['archived'])<span class="max-lg:hidden font-extrabold px-2 py-[3px] rounded-full bg-cream text-ink-soft whitespace-nowrap" title="Murid sudah tidak terdaftar di sekolah ini. Percakapan hanya bisa dibaca.">Murid sudah pindah sekolah</span>@endif
                    @if($vm['prank'])<span class="max-lg:hidden font-extrabold px-2 py-[3px] rounded-full bg-terracotta-tint text-terracotta-ink whitespace-nowrap">Ditandai iseng</span>@endif
                    @php $reasons = array_values(array_diff($breakdown['reasons'], [\App\Models\Conversation::UNASSESSED_LABEL, $c->urgencyLabel()])); @endphp
                    @if($reasons)<span class="max-lg:hidden text-ink-soft truncate" title="Alasan prioritas">· {{ implode(' · ', $reasons) }}</span>@endif
                </span>
            </span>
            <button type="button" x-on:click="togglePanel()" class="icon-btn" :class="panel && '!bg-sage-tint !border-sage-tint text-sage-ink'" :aria-expanded="panel" aria-controls="panel-kasus" aria-label="Detail kasus" title="Detail kasus"><x-icon name="panel-right" size="17" /></button>
        </div>

        <div x-data="urgencyPicker(@js($urgCfg))" x-show="!value" x-cloak class="px-4 py-2 border-b border-line-soft bg-sand-tint/70 flex items-center gap-2.5 flex-wrap text-[13px] text-sand-ink2">
            <x-icon name="info" size="16" />
            <span class="flex-1 min-w-[180px] font-bold">Kamu belum menilai urgensi cerita ini.</span>
            <span class="flex gap-1 flex-wrap">
                @foreach(\App\Models\Conversation::URGENCY_LABELS as $k => $l)
                    <button type="button" x-on:click="set('{{ $k }}')" :disabled="busy" class="tool-btn !h-7">{{ $l }}</button>
                @endforeach
            </span>
            <p x-show="error" x-text="error" class="field-error w-full !mt-0"></p>
        </div>

        <div x-ref="scroller" class="flex-1 overflow-y-auto px-4 sm:px-6 py-4 flex flex-col gap-2.5 bg-[#FDFBF6]" aria-live="polite" aria-label="Pesan">
            @foreach($messages as $msg){!! $msg['html'] !!}@endforeach
        </div>
        @include('chat.typing', ['role' => 'counselor', 'name' => $vm['identified'] ? $vm['display_name'] : 'Murid'])
        @unless($vm['archived'])@include('chat.media-modals', ['role' => 'counselor'])@endunless
        @include('chat.message-menu')

        <div class="px-3 pt-2 pb-3 border-t border-line-soft flex flex-col gap-2">
            <div class="max-lg:hidden flex gap-1.5 flex-wrap">
                <button type="button" x-on:click="$dispatch('open-modal', 'template')" class="tool-btn"><x-icon name="template" size="14" />Template</button>
                <button type="button" x-on:click="$dispatch('open-modal', 'article')" class="tool-btn"><x-icon name="book" size="14" />Artikel</button>
                @unless($vm['archived'])
                    <button type="button" x-on:click="pickPhoto()" class="tool-btn"><x-icon name="image" size="14" />Foto</button>
                @endunless
                <button type="button" x-on:click="$dispatch('open-modal', 'invite')" class="tool-btn"><x-icon name="calendar" size="14" />Undang sesi</button>
                @if($anonToMe)
                    <button type="button" x-on:click="$dispatch('open-modal', 'identity')" @disabled($pendingIdentity) class="tool-btn !border-sage text-sage-ink disabled:opacity-50" title="{{ $pendingIdentity ? 'Menunggu jawaban murid' : 'Minta murid membuka identitas' }}"><x-icon name="user-check" size="14" />{{ $pendingIdentity ? 'Permintaan identitas terkirim' : 'Minta buka identitas' }}</button>
                @endif
            </div>
            @if($vm['archived'])
                <p class="text-sm font-bold text-ink-soft bg-cream rounded-2xl p-3">Percakapan hanya bisa dibaca. Kamu tetap bisa menutup kasus dan menulis catatan.</p>
            @endif
            <p x-show="error" x-text="error" x-cloak class="field-error !mt-0"></p>
            {{-- HP: alat (foto, template, artikel, sesi, identitas) digabung di tombol "+" dalam kolom tulis yang berbentuk pil. --}}
            <form x-on:submit.prevent="send()" class="relative flex gap-2 items-end max-lg:gap-1.5 max-lg:p-1.5 max-lg:pl-2 max-lg:rounded-[24px] max-lg:bg-white max-lg:border-[1.5px] max-lg:border-line max-lg:shadow-soft max-lg:transition max-lg:focus-within:border-ink-soft/60" @if($vm['archived']) hidden @endif>
                <div class="lg:hidden self-end mb-[3px]">
                    @include('chat.attach-menu', ['items' => array_values(array_filter([
                        ['icon' => 'image', 'label' => 'Foto', 'hint' => 'Kirim gambar dari galeri', 'click' => 'pickPhoto()', 'tone' => 'bg-sage-tint text-sage-ink'],
                        ['icon' => 'template', 'label' => 'Template', 'hint' => 'Pakai balasan tersimpan', 'click' => "\$dispatch('open-modal', 'template')"],
                        ['icon' => 'book', 'label' => 'Artikel', 'hint' => 'Kirim bacaan Self-Help', 'click' => "\$dispatch('open-modal', 'article')"],
                        ['icon' => 'calendar', 'label' => 'Undang sesi', 'hint' => 'Ajak ngobrol tatap muka', 'click' => "\$dispatch('open-modal', 'invite')", 'tone' => 'bg-sand-tint text-sand-ink'],
                        $anonToMe ? ['icon' => 'user-check', 'label' => $pendingIdentity ? 'Permintaan terkirim' : 'Minta buka identitas', 'hint' => $pendingIdentity ? 'Menunggu jawaban murid' : 'Murid bebas memilih', 'click' => "\$dispatch('open-modal', 'identity')", 'tone' => 'bg-sage-tint text-sage-ink', 'disabled' => (bool) $pendingIdentity] : null,
                    ]))])
                </div>
                {{-- Balasan cepat: ketik /shortcut lalu Enter (seperti WhatsApp Business) --}}
                <div x-show="slashMatches.length" x-cloak x-transition.origin.bottom.left class="absolute left-0 right-14 bottom-full mb-2 z-30 bg-white border border-line rounded-[20px] shadow-modal p-1.5 flex flex-col" role="listbox" aria-label="Template balasan cepat">
                    <span class="px-3 pt-1.5 pb-1 text-[11px] font-extrabold tracking-[0.08em] uppercase text-ink-soft">Balasan cepat · ↑↓ pilih, Enter pakai</span>
                    <template x-for="(t, i) in slashMatches" :key="t.id">
                        <button type="button" role="option" :aria-selected="(i === slashIndex).toString()" x-on:mousedown.prevent x-on:click="useQuickReply(t)" x-on:mousemove="slashIndex = i" class="flex items-start gap-3 px-3 py-2.5 rounded-[14px] text-left" :class="i === slashIndex ? 'bg-sage-tint' : ''">
                            <span class="flex-none px-2 py-0.5 rounded-lg bg-white border border-sage/40 text-[13px] font-extrabold text-sage-ink" x-text="'/' + t.shortcut"></span>
                            <span class="min-w-0 flex flex-col"><span class="text-sm font-extrabold truncate" x-text="t.title"></span><span class="text-xs text-ink-soft truncate" x-text="t.body"></span></span>
                        </button>
                    </template>
                </div>
                <label for="guru-input" class="sr-only">Balas pesan</label>
                <textarea id="guru-input" x-ref="input" x-model="body" x-on:input="saveDraft(); autosize(); notifyTyping(); slashClosed = false; slashIndex = 0"
                          x-on:keydown.enter="if (!$event.shiftKey) { $event.preventDefault(); if (!useQuickReply()) send(); }"
                          x-on:keydown.arrow-down="if (moveSlash(1)) $event.preventDefault()" x-on:keydown.arrow-up="if (moveSlash(-1)) $event.preventDefault()"
                          x-on:keydown.tab="if (slashMatches.length) { $event.preventDefault(); useQuickReply(); }" x-on:keydown.escape="slashClosed = true"
                          rows="1" placeholder="Tulis balasan…" class="flex-1 min-w-0 min-h-11 max-h-40 py-2.5 px-4 rounded-[22px] border-[1.5px] border-line bg-white text-[15px] leading-snug resize-none focus:outline-none focus:border-ink-soft max-lg:min-h-[42px] max-lg:px-1 max-lg:rounded-none max-lg:border-0 max-lg:bg-transparent max-lg:placeholder:text-ink-soft/70"></textarea>
                <button type="submit" :disabled="sending || !body.trim()" aria-label="Kirim" class="h-11 px-5 rounded-full bg-sage-deep text-white font-extrabold text-[15px] disabled:opacity-50 max-lg:w-9 max-lg:h-9 max-lg:px-0 max-lg:mb-[3px] max-lg:flex-none max-lg:inline-flex max-lg:items-center max-lg:justify-center"><x-icon name="send" size="16" class="lg:hidden" /><span class="max-lg:hidden">Kirim</span></button>
            </form>
        </div>
    </section>

    {{-- KANAN: detail kasus. ≥1280px kolom samping; lebih kecil: laci yang menutupi chat. --}}
    <div x-show="panel" x-cloak x-transition.opacity x-on:click="togglePanel()" class="xl:hidden fixed inset-0 z-40 bg-ink/30" aria-hidden="true"></div>
    <section id="panel-kasus" x-show="panel" x-cloak aria-label="Detail kasus" x-on:keydown.escape.window="if (panel && window.innerWidth < 1280) togglePanel()"
             class="fixed inset-y-0 right-0 z-50 w-[min(360px,92vw)] bg-cream p-3 shadow-modal xl:shadow-none xl:static xl:z-auto xl:w-[320px] xl:p-0 xl:bg-transparent flex-none flex flex-col gap-2.5 h-screen xl:h-[calc(100vh-172px)] overflow-y-auto" x-data="{ tab: '{{ $tab }}' }">
        <div class="flex items-center gap-2">
            <div role="tablist" aria-label="Detail kasus" class="seg !flex flex-1">
                @foreach(['kasus' => 'Kasus', 'catatan' => 'Catatan', 'ringkasan' => 'Ringkasan', 'sesi' => 'Sesi'] as $k => $l)
                    <button type="button" role="tab" :aria-selected="(tab === '{{ $k }}').toString()" x-on:click="tab = '{{ $k }}'" class="seg-item flex-1 !px-1.5 !text-xs" :class="tab === '{{ $k }}' && 'seg-item-on'">{{ $l }}</button>
                @endforeach
            </div>
            <button type="button" x-on:click="togglePanel()" class="icon-btn !w-8 !h-8" aria-label="Tutup detail kasus" title="Tutup detail kasus"><x-icon name="x" size="16" /></button>
        </div>

        {{-- Tab Kasus --}}
        <div x-show="tab === 'kasus'" class="flex flex-col gap-2.5">
            {{-- HP: label yang disembunyikan dari header chat ditampilkan di sini. --}}
            <div class="lg:hidden box p-3.5 flex flex-col gap-2">
                <span class="flex items-center gap-2"><span class="panel-icon bg-sage-tint text-sage-ink"><x-icon name="info" size="14" /></span><span class="box-title">Info cerita</span></span>
                <span class="flex gap-1.5 flex-wrap items-center text-xs">
                    <x-priority-badge :score="$c->priority_score" class="!py-[3px] !px-2" />
                    @foreach($vm['topics'] as $t)<span class="font-extrabold px-2 py-[3px] rounded-full bg-cream whitespace-nowrap">{{ $t['name'] }}</span>@endforeach
                    @if($vm['identified'])
                        <span class="inline-flex items-center gap-1 font-extrabold text-sand-ink"><x-icon name="unlock" size="12" />Teridentifikasi</span>
                    @else
                        <span class="inline-flex items-center gap-1 font-extrabold text-sage-ink2"><x-icon name="lock" size="12" />Anonim</span>
                    @endif
                    @if($c->is_emergency)<span class="font-extrabold text-white bg-danger px-2 py-0.5 rounded-full whitespace-nowrap">Tombol darurat</span>@endif
                    @if($vm['new_member'])<span class="font-extrabold px-2 py-[3px] rounded-full bg-sand-tint text-sand-ink whitespace-nowrap">Anggota baru</span>@endif
                    @if($vm['archived'])<span class="font-extrabold px-2 py-[3px] rounded-full bg-cream text-ink-soft whitespace-nowrap">Murid sudah pindah sekolah</span>@endif
                    @if($vm['prank'])<span class="font-extrabold px-2 py-[3px] rounded-full bg-terracotta-tint text-terracotta-ink whitespace-nowrap">Ditandai iseng</span>@endif
                </span>
                @if($reasons)<span class="text-xs text-ink-soft leading-snug">Alasan prioritas: {{ implode(' · ', $reasons) }}</span>@endif
            </div>
            <div class="box p-3.5 flex flex-col gap-3">
                <div class="flex flex-col gap-1.5" x-data="urgencyPicker(@js($urgCfg))">
                    <span class="flex items-center gap-2"><span class="panel-icon bg-terracotta-tint text-terracotta-ink"><x-icon name="alert" size="14" /></span><label for="case-urgency" class="box-title">Urgensi (penilaianmu)</label></span>
                    <select id="case-urgency" x-on:change="set($event.target.value)" :disabled="busy" class="field font-extrabold">
                        <option value="" disabled :selected="!value">{{ \App\Models\Conversation::UNASSESSED_LABEL }}</option>
                        @foreach(\App\Models\Conversation::URGENCY_LABELS as $k => $l)<option value="{{ $k }}" :selected="value === '{{ $k }}'">{{ $l }}</option>@endforeach
                    </select>
                    <p x-show="error" x-text="error" x-cloak class="field-error !mt-0"></p>
                    <span class="text-[11px] text-ink-soft">Hanya terlihat oleh Guru BK. Ikut menentukan urutan di Inbox.</span>
                </div>
                <div class="flex flex-col gap-1.5" x-data="{ status: '{{ $c->status }}' }">
                    <span class="flex items-center gap-2"><span class="panel-icon bg-sage-tint text-sage-ink"><x-icon name="kanban" size="14" /></span><label for="case-status" class="box-title">Status kasus</label></span>
                    <form method="POST" action="{{ route('counselor.conversations.status', $c) }}" x-ref="statusForm">
                        @csrf @method('PATCH')
                        <select id="case-status" name="status" x-model="status" x-on:change="status === 'resolved' ? $dispatch('open-modal', 'resolve') : $refs.statusForm.submit()" class="field font-extrabold">
                            @foreach(\App\Models\Conversation::STATUS_LABELS as $k => $l)<option value="{{ $k }}" @selected($c->status === $k)>{{ $l }}</option>@endforeach
                        </select>
                    </form>
                    @if($c->status === 'referred_parent' && $anonToMe)
                        <p class="text-xs font-bold text-sand-ink bg-sand-tint rounded-xl p-2.5">Untuk merujuk ke orang tua, minta murid membuka identitas terlebih dahulu.</p>
                    @endif
                    <span class="text-[11px] text-ink-soft">Dibuka {{ $c->created_at->timezone($tz)->translatedFormat('j M Y') }} · prioritas {{ $c->priority_score }}</span>
                </div>
                <div class="grid grid-cols-2 gap-2 pt-1 border-t border-line-soft"><button type="button" x-on:click="$dispatch('open-modal', 'transfer')" class="mt-2 inline-flex items-center justify-center gap-1.5 h-10 rounded-full bg-sage-tint text-sage-ink text-[13px] font-extrabold transition hover:bg-sage/30"><x-icon name="transfer" size="15" />Serah terima</button>
                <form method="POST" action="{{ route('counselor.conversations.prank', $c) }}" class="mt-2 flex flex-col gap-1" @unless($vm['prank']) data-confirm-title="Tandai cerita ini sebagai iseng?" data-confirm="Admin sekolah akan diberi tahu (tanpa isi cerita dan identitas murid)." data-confirm-ok="Ya, tandai" @endunless>
                    @csrf
                    <button title="Untuk cerita palsu/main-main. Kalau ragu, tangani dulu seperti kasus sungguhan." class="w-full inline-flex items-center justify-center gap-1.5 h-10 rounded-full bg-terracotta-tint2 border border-terracotta-tint text-terracotta-ink text-[13px] font-extrabold transition hover:bg-terracotta-tint"><x-icon name="flag" size="14" />{{ $vm['prank'] ? 'Batalkan iseng' : 'Tandai iseng' }}</button>
                </form>
                </div>
            </div>

            <details class="box group" @if($flags->whereNull('reviewed_at')->isNotEmpty()) open @endif>
                <summary class="flex items-center justify-between gap-2 p-3.5 cursor-pointer list-none"><span class="flex items-center gap-2"><span class="panel-icon bg-danger-tint text-danger"><x-icon name="alert" size="14" /></span><span class="box-title">Kata kunci sensitif</span></span><span class="flex items-center gap-1.5">@if($flags->whereNull('reviewed_at')->count())<span class="min-w-5 h-5 px-1.5 rounded-full bg-danger text-white text-[11px] font-extrabold flex items-center justify-center leading-none pt-px">{{ $flags->whereNull('reviewed_at')->count() }}</span>@endif<x-icon name="chevron-down" size="14" class="transition group-open:rotate-180" /></span></summary>
                <div class="px-3.5 pb-3 flex flex-col">
                    @forelse($flags as $f)
                        <div class="flex items-start gap-2.5 py-2 border-t border-line-soft">
                            <span class="mt-0.5 px-2 py-0.5 rounded-full text-[11px] font-extrabold {{ $f->severity >= 3 ? 'bg-danger text-white' : ($f->severity === 2 ? 'bg-terracotta-tint text-terracotta-ink' : 'bg-sand-tint text-sand-ink') }}">{{ [1 => 'Perhatian', 2 => 'Serius', 3 => 'Kritis'][$f->severity] }}</span>
                            <span class="flex-1 min-w-0 flex flex-col gap-0.5"><span class="text-sm font-extrabold">“{{ $f->keyword?->phrase }}”</span><span class="text-xs text-ink-soft">{{ $f->created_at->timezone($tz)->translatedFormat('j M, H.i') }}{{ $f->reviewed_at ? ' · ditinjau '.$f->reviewer?->displayName() : '' }}</span>
                                @unless($f->reviewed_at)
                                    <form method="POST" action="{{ route('counselor.flags.review', $f) }}">@csrf<button class="mt-0.5 text-xs font-extrabold text-sage-ink underline">Tandai sudah ditinjau</button></form>
                                @endunless
                            </span>
                            @if($f->reviewed_at)<x-icon name="check" size="16" class="text-sage-deep mt-1" />@endif
                        </div>
                    @empty
                        <span class="text-[13px] text-ink-soft">Tidak ada kata kunci sensitif terdeteksi.</span>
                    @endforelse
                </div>
            </details>

            @if($sharedMoods->isNotEmpty())
                <div class="box p-3.5 flex flex-col gap-2">
                    <span class="flex items-center gap-2"><span class="panel-icon bg-sand-tint text-sand-ink"><x-icon name="mood" size="14" /></span><span class="box-title">Mood yang dibagikan murid</span></span>
                    <div class="flex gap-3 flex-wrap">
                        @foreach($sharedMoods as $sm)<span class="flex flex-col items-center gap-0.5 text-[11px] text-ink-soft"><x-mood-face :mood="$sm->meta['mood'] ?? 'neutral'" size="24" />{{ isset($sm->meta['date']) ? \Carbon\Carbon::parse($sm->meta['date'])->translatedFormat('j M') : '' }}</span>@endforeach
                    </div>
                </div>
            @endif

            <details class="box group">
                <summary class="flex items-center justify-between gap-2 p-3.5 cursor-pointer list-none"><span class="flex items-center gap-2"><span class="panel-icon bg-sand-tint text-sand-ink"><x-icon name="clock" size="14" /></span><span class="box-title">Follow-up · riwayat · serah terima</span></span><x-icon name="chevron-down" size="14" class="transition group-open:rotate-180" /></summary>
                <div class="px-3.5 pb-3.5 flex flex-col gap-3">
                    <div class="flex flex-col gap-1.5">
                        <span class="text-xs font-extrabold">Jadwal follow-up</span>
                        @forelse($followUps->take(3) as $fu)
                            <div class="text-xs leading-snug"><span class="font-extrabold">{{ $fu->scheduled_at->timezone($tz)->translatedFormat('D, j M') }}</span> <span class="text-ink-soft">· {{ $fu->cancelled_at ? 'Dibatalkan' : ($fu->response ? 'Jawaban: '.['better' => 'Lebih baik', 'same' => 'Sama aja', 'worse' => 'Makin berat'][$fu->response] : ($fu->sent_at ? 'Terkirim, menunggu jawaban' : 'Terjadwal otomatis')) }}</span></div>
                        @empty
                            <span class="text-xs text-ink-soft">Dijadwalkan otomatis saat kasus ditandai Selesai.</span>
                        @endforelse
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <span class="text-xs font-extrabold">Riwayat status</span>
                        @foreach($logs as $log)
                            <div class="text-xs leading-snug"><span class="font-extrabold">{{ \App\Models\Conversation::STATUS_LABELS[$log->to_status] ?? $log->to_status }}</span> <span class="text-ink-soft">· {{ $log->created_at?->timezone($tz)->translatedFormat('j M H.i') }} · {{ $log->changer?->displayName() ?? 'Sistem' }}{{ $log->note ? ' · '.$log->note : '' }}</span></div>
                        @endforeach
                    </div>
                    @if($transfers->isNotEmpty())
                        <div class="flex flex-col gap-1.5">
                            <span class="text-xs font-extrabold">Catatan serah terima</span>
                            @foreach($transfers as $t)
                                <div class="text-[13px] leading-relaxed"><span class="font-extrabold">{{ $t->fromCounselor->displayName() }} → {{ $t->toCounselor->displayName() }}</span> <span class="text-xs text-ink-soft">{{ $t->created_at->timezone($tz)->translatedFormat('j M Y') }}</span>@if($t->handover_note)<p class="mt-1 p-2.5 rounded-xl bg-cream text-[13px]">{{ $t->handover_note }}</p>@endif</div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </details>
        </div>

        {{-- Tab Catatan Privat --}}
        <div x-show="tab === 'catatan'" x-cloak class="box p-3.5 flex flex-col gap-3">
            <div class="flex items-center justify-between gap-2 flex-wrap"><span class="flex items-center gap-2"><span class="panel-icon bg-sand-tint text-sand-ink"><x-icon name="journal" size="14" /></span><span class="box-title">Catatan privat</span></span><span class="inline-flex items-center gap-1 px-2 py-[3px] rounded-full bg-sand-tint text-sand-ink2 text-[11px] font-extrabold"><x-icon name="lock" size="12" />Hanya kamu</span></div>
            <form method="POST" action="{{ route('counselor.notes.store', $c) }}" class="flex flex-col gap-2">
                @csrf
                <label for="note-new" class="sr-only">Tambah catatan</label>
                <textarea id="note-new" name="body" rows="3" placeholder="Tulis catatan untuk dirimu sendiri…" class="field !bg-sand-tint/30 !border-sand/40 text-sm" required></textarea>
                <button class="self-end btn btn-sage btn-sm">Simpan catatan</button>
            </form>
            @forelse($notes as $n)
                <div class="flex flex-col gap-1 px-3 py-2.5 rounded-xl bg-cream-light border-l-[3px] border-sand" x-data="{ edit: false }">
                    <div class="flex justify-between items-center gap-2"><span class="text-xs font-extrabold text-ink-soft">{{ $n->created_at->timezone($tz)->translatedFormat('j M, H.i') }}{{ $n->updated_at->gt($n->created_at) ? ' · diedit' : '' }}</span>
                        <span class="flex gap-2"><button type="button" x-on:click="edit = !edit" class="text-xs font-extrabold text-sage-ink underline">Edit</button>
                        <form method="POST" action="{{ route('counselor.notes.destroy', $n) }}" data-confirm="Hapus catatan ini?" data-confirm-ok="Ya, hapus" data-confirm-tone="danger">@csrf @method('DELETE')<button class="text-xs font-extrabold text-ink-soft underline">Hapus</button></form></span>
                    </div>
                    <p x-show="!edit" class="text-sm leading-relaxed whitespace-pre-line">{{ $n->body }}</p>
                    <form x-show="edit" x-cloak method="POST" action="{{ route('counselor.notes.update', $n) }}" class="flex flex-col gap-2">@csrf @method('PATCH')<textarea name="body" rows="4" class="field text-sm">{{ $n->body }}</textarea><button class="self-end btn btn-sage btn-sm">Simpan</button></form>
                </div>
            @empty
                <span class="px-3 py-4 rounded-xl border border-dashed border-line text-[13px] text-ink-soft text-center">Belum ada catatan untuk kasus ini.</span>
            @endforelse
        </div>

        {{-- Tab Ringkasan AI --}}
        <div x-show="tab === 'ringkasan'" x-cloak class="box p-3.5" x-ref="summaryBox" x-data="summaryPoller()">
            @include('counselor.partials.summary', ['summary' => $summary])
        </div>

        {{-- Tab Sesi --}}
        <div x-show="tab === 'sesi'" x-cloak class="box p-3.5 flex flex-col gap-3">
            <div class="flex flex-col gap-1.5">
                <span class="flex items-center gap-2"><span class="panel-icon bg-terracotta-tint text-terracotta-ink"><x-icon name="calendar" size="14" /></span><span class="box-title">Sesi tatap muka</span></span>
                @forelse($sessions as $s)
                    <div class="flex items-center gap-2.5 py-1"><span class="w-8 h-8 flex-none rounded-lg bg-terracotta-tint flex items-center justify-center text-terracotta-ink"><x-icon name="calendar" size="16" /></span><span class="flex-1 flex flex-col"><span class="font-extrabold text-[13px]">{{ $s->slot->start_at->timezone($tz)->translatedFormat('D, j M') }} · {{ $s->slot->start_at->timezone($tz)->format('H.i') }}</span><span class="text-xs text-ink-soft">{{ $s->statusLabel() }} · {{ $s->slot->location }}</span></span></div>
                @empty
                    <span class="px-3 py-3 rounded-xl border border-dashed border-line text-[13px] text-ink-soft text-center">Belum ada sesi.</span>
                @endforelse
            </div>
            <div class="flex flex-col gap-1.5 pt-3 border-t border-line-soft">
                <span class="flex items-center gap-2"><span class="panel-icon bg-sage-tint text-sage-ink"><x-icon name="send" size="14" /></span><span class="box-title">Undangan</span></span>
                @forelse($invitations as $inv)
                    <div class="text-[13px]"><span class="font-extrabold">{{ $inv->slots->count() }} pilihan waktu</span> <span class="text-ink-soft">· {{ $inv->status === 'pending' && $inv->expires_at->isPast() ? 'kedaluwarsa' : ['pending' => 'menunggu', 'accepted' => 'diterima', 'declined' => 'belum siap', 'expired' => 'kedaluwarsa'][$inv->status] }} · {{ $inv->created_at->timezone($tz)->translatedFormat('j M') }}</span></div>
                @empty
                    <span class="px-3 py-3 rounded-xl border border-dashed border-line text-[13px] text-ink-soft text-center">Belum ada undangan.</span>
                @endforelse
            </div>
            <div class="flex gap-2 flex-wrap">
                <button type="button" x-on:click="$dispatch('open-modal', 'invite')" class="btn btn-sage btn-sm flex-1">Undang sesi</button>
                <a href="{{ route('counselor.schedule') }}" class="btn btn-soft btn-sm flex-1">Buka jadwal</a>
            </div>
        </div>
    </section>
</div>
@endsection

@push('modals')
<x-modal name="template" title="Template Balasan" max-width="max-w-[760px]">
    <div x-data="templatePicker({{ Js::from(['templates' => $templates, 'topics' => $c->topics->pluck('id'), 'sapaan' => $sapaan]) }})" class="flex gap-4 flex-wrap">
        <div class="flex-[1_1_240px] flex flex-col gap-1.5 max-h-[380px] overflow-y-auto">
            <label class="flex items-center gap-2 text-xs font-bold text-ink-soft mb-1"><input type="checkbox" x-model="all" class="accent-sage-deep">Tampilkan semua topik</label>
            <template x-for="group in groups" :key="group.name">
                <div class="flex flex-col gap-1.5">
                    <span class="mt-2 text-xs font-extrabold tracking-[0.08em] uppercase text-ink-soft" x-text="group.name"></span>
                    <template x-for="t in group.items" :key="t.id">
                        <button type="button" x-on:click="pick(t)" class="text-left px-3.5 py-2.5 rounded-[14px] text-sm text-ink" :class="selected?.id === t.id ? 'border-[1.5px] border-sage-deep bg-[#EEF2EA] font-extrabold' : 'border-[1.5px] border-line bg-white font-bold'"><span x-text="t.title"></span><span x-show="t.mine" class="ml-1 text-[11px] text-sage-ink2">(milikku)</span></button>
                    </template>
                </div>
            </template>
            <p x-show="!groups.length" class="text-sm text-ink-soft">Belum ada template untuk topik ini.</p>
        </div>
        <div class="flex-[2_1_320px] flex flex-col gap-2">
            <label for="tpl-edit" class="text-sm font-extrabold">Edit sebelum dipakai</label>
            <textarea id="tpl-edit" x-model="text" rows="8" class="field !rounded-2xl text-[15px]"></textarea>
            <p class="text-xs text-ink-soft">Template hanya mengisi kolom balasan, tidak langsung terkirim. <code>{sapaan}</code> diganti otomatis.</p>
            <div class="flex justify-end gap-2">
                <button type="button" x-on:click="$dispatch('close-modal', 'template')" class="btn btn-outline btn-sm">Batal</button>
                <button type="button" x-on:click="use()" :disabled="!text.trim()" class="btn btn-sage btn-sm">Pakai di kolom balasan</button>
            </div>
            <a href="{{ route('counselor.profile') }}#template" class="text-xs font-extrabold text-sage-ink self-end">Kelola template saya</a>
        </div>
    </div>
</x-modal>

<x-modal name="article" title="Kirim Artikel Self-Help" max-width="max-w-[640px]">
    <div x-data="articlePicker({{ Js::from(['articles' => $articles, 'url' => route('counselor.conversations.article', $c)]) }})" class="flex flex-col gap-3.5">
        <div class="relative">
            <label for="art-search" class="sr-only">Cari artikel</label>
            <x-icon name="search" size="18" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-ink-soft" />
            <input id="art-search" type="search" x-model="q" placeholder="Cari judul artikel" class="field !pl-10">
        </div>
        <label for="art-note" class="text-sm font-bold">Catatan pendek (opsional)</label>
        <input id="art-note" x-model="note" maxlength="500" placeholder="Misal: Ini bacaan pendek yang mungkin membantu." class="field">
        <span class="text-[13px] text-ink-soft">Disarankan untuk topik {{ $c->topics->pluck('name')->implode(' & ') }} ditampilkan paling atas.</span>
        <div class="flex flex-col gap-2 max-h-[360px] overflow-y-auto">
            <template x-for="a in filtered" :key="a.id">
                <div class="bg-white rounded-[18px] p-3 flex items-center gap-3">
                    <span class="w-[52px] h-[52px] flex-none rounded-[14px] flex items-center justify-center bg-sage-tint text-sage-ink2"><x-icon name="book" size="24" /></span>
                    <span class="flex-1 flex flex-col gap-0.5"><span class="font-extrabold text-[15px]" x-text="a.title"></span><span class="text-xs text-ink-soft" x-text="a.meta"></span><span x-show="a.suggested" class="text-[11px] font-extrabold text-sage-ink2">Disarankan</span></span>
                    <button type="button" x-on:click="send(a)" :disabled="sent.includes(a.id) || busy" class="h-11 px-4 rounded-full text-sm font-extrabold" :class="sent.includes(a.id) ? 'bg-sage-tint text-sage-ink' : 'bg-sage-deep text-white'" x-text="sent.includes(a.id) ? 'Terkirim' : 'Kirim'"></button>
                </div>
            </template>
        </div>
    </div>
</x-modal>

<x-modal name="invite" title="Undang Sesi Tatap Muka" max-width="max-w-[600px]" :show="$errors->has('slot_ids')">
    <form method="POST" action="{{ route('counselor.conversations.invitations.store', $c) }}" class="flex flex-col gap-3.5" x-data="{ picked: [] }">
        @csrf
        <p class="text-sm text-ink-soft">Pilih 1-5 slot (min. 2 jam dari sekarang). Berlaku 7 hari.@if($anonToMe) Murid diminta membuka identitas.@endif</p>
        <div class="flex flex-col gap-2 max-h-[320px] overflow-y-auto">
            @forelse($slots as $s)
                <label class="flex items-center gap-3 px-3.5 py-3 rounded-2xl bg-white border-[1.5px] border-line cursor-pointer has-[:checked]:border-sage-deep has-[:checked]:bg-[#EEF2EA]">
                    <input type="checkbox" name="slot_ids[]" value="{{ $s['id'] }}" x-model="picked" :disabled="picked.length >= 5 && !picked.includes('{{ $s['id'] }}')" class="w-5 h-5 accent-sage-deep"><span class="text-sm font-bold">{{ $s['label'] }}</span>
                </label>
            @empty
                <div class="p-4 rounded-2xl bg-sand-tint text-sand-ink2 text-sm font-bold">Belum ada slot kosong. <a href="{{ route('counselor.schedule') }}" class="underline">Buat slot di Jadwal</a> dulu, ya.</div>
            @endforelse
        </div>
        @error('slot_ids')<p class="field-error">{{ $message }}</p>@enderror
        <label for="inv-msg" class="text-sm font-bold">Pesan (opsional)</label>
        <input id="inv-msg" name="message" maxlength="255" value="Gimana kalau kita ngobrol langsung? Pilih waktu yang paling nyaman buatmu, ya." class="field">
        <div class="flex justify-between items-center gap-2"><span class="text-xs text-ink-soft" x-text="picked.length + '/5 slot dipilih'"></span><div class="flex gap-2"><button type="button" x-on:click="$dispatch('close-modal', 'invite')" class="btn btn-outline btn-sm">Batal</button><button class="btn btn-sage btn-sm" :disabled="!picked.length">Kirim undangan</button></div></div>
    </form>
</x-modal>

@if($anonToMe)
<x-modal name="identity" title="Minta murid membuka identitas?">
    <form method="POST" action="{{ route('counselor.conversations.identity-request', $c) }}" class="flex flex-col gap-3.5">
        @csrf
        <p class="text-sm leading-relaxed text-ink-soft">Murid akan melihat kartu permintaan dan bebas memilih <b>Buka Identitas</b> atau <b>Tetap Anonim</b>, tanpa konsekuensi apa pun. Identitas hanya terbuka lewat aksi murid sendiri.</p>
        <label for="id-msg" class="text-sm font-bold">Alasan singkat (opsional, akan dilihat murid)</label>
        <input id="id-msg" name="message" maxlength="255" value="Supaya aku bisa bantu lebih baik. Kalau belum nyaman, tetap anonim juga nggak apa-apa." class="field">
        <div class="flex justify-end gap-2"><button type="button" x-on:click="$dispatch('close-modal', 'identity')" class="btn btn-outline btn-sm">Batal</button><button class="btn btn-sage btn-sm">Kirim permintaan</button></div>
    </form>
</x-modal>
@endif

<x-modal name="transfer" title="Serah Terima Kasus" :show="$errors->has('to_counselor_id')">
    <form method="POST" action="{{ route('counselor.conversations.transfer', $c) }}" class="flex flex-col gap-3.5">
        @csrf
        <fieldset class="flex flex-col gap-2">
            <legend class="text-sm font-bold mb-1">Serahkan ke</legend>
            @foreach($colleagues as $g)
                <label class="flex items-center gap-3 p-3 rounded-2xl bg-white border-[1.5px] border-line cursor-pointer has-[:checked]:border-sage-deep has-[:checked]:bg-[#EEF2EA]"><input type="radio" name="to_counselor_id" value="{{ $g->id }}" class="w-5 h-5 accent-sage-deep" required><x-counselor-avatar :counselor="$g" :size="36" :radius="12" /><span class="font-extrabold text-sm">{{ $g->displayName() }} <span class="font-bold text-ink-soft">· {{ $g->name }}</span></span></label>
            @endforeach
        </fieldset>
        @error('to_counselor_id')<p class="field-error">{{ $message }}</p>@enderror
        <label for="ho-note" class="text-sm font-bold">Catatan serah terima (opsional, terenkripsi)</label>
        <textarea id="ho-note" name="handover_note" rows="4" class="field text-sm" placeholder="Konteks penting untuk guru berikutnya…"></textarea>
        <ul class="text-xs text-ink-soft list-disc pl-5 leading-relaxed">
            <li>Catatan privatmu <b>tidak</b> ikut berpindah.</li>
            <li>Guru baru hanya melihat nama samaran sampai murid membuka identitas.</li>
        </ul>
        <div class="flex justify-end gap-2"><button type="button" x-on:click="$dispatch('close-modal', 'transfer')" class="btn btn-outline btn-sm">Batal</button><button class="btn btn-sage btn-sm">Serahkan kasus</button></div>
    </form>
</x-modal>

<x-modal name="resolve" title="Tandai kasus selesai?">
    <form method="POST" action="{{ route('counselor.conversations.status', $c) }}" class="flex flex-col gap-3.5">
        @csrf @method('PATCH')
        <input type="hidden" name="status" value="resolved">
        <p class="text-sm leading-relaxed text-ink-soft">Murid diberi tahu dengan pesan netral. Follow-up dikirim {{ config('welcoming.follow_up_days') }} hari lagi. Pesan baru membuka kasus kembali.</p>
        <label for="res-note" class="text-sm font-bold">Catatan (opsional, untuk riwayat status)</label>
        <input id="res-note" name="note" maxlength="255" class="field">
        <div class="flex justify-end gap-2"><button type="button" x-on:click="$dispatch('close-modal', 'resolve'); document.getElementById('case-status').value = '{{ $c->status }}'" class="btn btn-outline btn-sm">Batal</button><button class="btn btn-sage btn-sm">Ya, tandai Selesai</button></div>
    </form>
</x-modal>
@endpush
