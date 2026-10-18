@extends('layouts.counselor', ['active' => 'kasus'])
@section('title', 'Manajemen Kasus')
@php
    $tz = \App\Models\School::current()->timezone;
    // Kolom status (tanpa "Selesai") → kolom buatan guru → "Selesai" di paling kanan.
    $statusCol = fn ($k) => ['key' => $k, 'title' => \App\Models\Conversation::STATUS_LABELS[$k], 'custom' => null, 'tone' => 'bg-cream-deep/70', 'dot' => null, 'cards' => $items->where('status', $k)->whereNull('board_column_id')->values()];
    $columns = collect(['new', 'monitoring', 'referred_parent'])->map($statusCol)
        ->concat($boardColumns->map(fn ($bc) => ['key' => 'c'.$bc->id, 'title' => $bc->name, 'custom' => $bc, 'tone' => \App\Models\CaseBoardColumn::COLORS[$bc->color][0] ?? 'bg-cream-deep/70', 'dot' => \App\Models\CaseBoardColumn::COLORS[$bc->color][1] ?? 'bg-ink-soft', 'cards' => $items->where('board_column_id', $bc->id)->values()]))
        ->push($statusCol('resolved'));
    $fuWeek = $items->filter(fn ($i) => $i['next_follow_up'] && $i['next_follow_up']->lte(now()->endOfWeek()))->count();
    $urg = \App\Models\Conversation::urgencyStyles();
@endphp
@section('header')
<header class="flex items-center justify-between gap-4 flex-wrap no-print">
    <div class="flex items-center gap-3 min-w-0">
    <x-back-button :href="route('counselor.dashboard')" />
    <div class="flex flex-col gap-0.5">
        <h1 class="text-2xl lg:text-[26px] leading-tight">Manajemen Kasus</h1>
        <span class="text-sm text-ink-soft">{{ $items->count() }} kasus · {{ $fuWeek }} follow-up minggu ini</span>
    </div>
    </div>
</header>
@endsection
@section('content')
@php $extra = collect($filters)->only(['topic', 'urgency', 'identity'])->filter()->count(); @endphp
<div class="flex flex-col gap-3" data-module="kanban" x-data="caseBoard()">
    <form method="GET" class="flex items-center gap-2 flex-wrap" x-data="{ more: false }">
        <input type="hidden" name="tampilan" value="{{ $view }}">
        <div role="tablist" aria-label="Tampilan" class="seg">
            <a role="tab" aria-selected="{{ $view === 'kanban' ? 'true' : 'false' }}" href="{{ route('counselor.cases', array_merge($filters, ['tampilan' => 'kanban'])) }}" class="seg-item no-underline {{ $view === 'kanban' ? 'seg-item-on' : '' }}">Kanban</a>
            <a role="tab" aria-selected="{{ $view === 'tabel' ? 'true' : 'false' }}" href="{{ route('counselor.cases', array_merge($filters, ['tampilan' => 'tabel'])) }}" class="seg-item no-underline {{ $view === 'tabel' ? 'seg-item-on' : '' }}">Tabel</a>
        </div>
        <div class="relative flex-[0_1_300px] ml-auto">
            <label for="k-q" class="sr-only">Cari kasus</label>
            <x-icon name="search" size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-ink-soft" />
            <input id="k-q" name="q" type="search" value="{{ $filters['q'] ?? '' }}" placeholder="Cari nama samaran / nama" class="field !pl-9 text-sm">
        </div>
        <div class="relative" x-on:keydown.escape="more = false">
            <button type="button" x-on:click="more = !more" :aria-expanded="more" class="btn btn-soft btn-sm"><x-icon name="filter" size="15" />Filter @if($extra)<span class="min-w-5 h-5 px-1.5 rounded-full bg-ink text-cream text-[11px] flex items-center justify-center leading-none pt-px">{{ $extra }}</span>@endif</button>
            <div x-show="more" x-cloak x-on:click.outside="more = false" x-transition.origin.top.right class="absolute right-0 top-full mt-1.5 z-30 w-[260px] bg-white rounded-2xl shadow-modal p-3.5 flex flex-col gap-2.5">
                <div><label for="k-topic" class="field-label">Topik</label><select id="k-topic" name="topic" class="field text-sm"><option value="">Semua topik</option>@foreach($topics as $t)<option value="{{ $t->slug }}" @selected(($filters['topic'] ?? '') === $t->slug)>{{ $t->name }}</option>@endforeach</select></div>
                <div><label for="k-urg" class="field-label">Urgensi</label><select id="k-urg" name="urgency" class="field text-sm"><option value="">Semua urgensi</option>@foreach($urg as $k => [$l])<option value="{{ $k }}" @selected(($filters['urgency'] ?? '') === $k)>{{ $l }}</option>@endforeach</select></div>
                <div><label for="k-id" class="field-label">Identitas</label><select id="k-id" name="identity" class="field text-sm"><option value="">Anonim & teridentifikasi</option><option value="anonim" @selected(($filters['identity'] ?? '') === 'anonim')>Anonim saja</option><option value="teridentifikasi" @selected(($filters['identity'] ?? '') === 'teridentifikasi')>Teridentifikasi saja</option></select></div>
                <div class="flex justify-between gap-2 pt-1"><a href="{{ route('counselor.cases', ['tampilan' => $view]) }}" class="btn btn-ghost btn-sm">Reset</a><button class="btn btn-sage btn-sm">Terapkan</button></div>
            </div>
        </div>
    </form>

    <p x-show="message" x-text="message" x-cloak class="p-2.5 rounded-xl bg-sage-tint text-sage-ink text-[13px] font-bold" role="status"></p>

    @if($view === 'kanban')
        <div class="flex gap-3 items-start overflow-x-auto pb-1">
            @foreach($columns as $col)
                {{-- Kolom bisa diciutkan/dilebarkan; kolom ciut tetap jadi tempat jatuhkan kartu. --}}
                <section class="{{ $col['tone'] }} rounded-[18px] p-2 flex flex-col gap-2 transition-all" :class="collapsed['{{ $col['key'] }}'] ? 'w-[168px] flex-none' : 'flex-1 min-w-[220px]'" aria-labelledby="col-{{ $col['key'] }}">
                    <div class="flex items-center gap-1.5 px-1.5 pt-0.5">
                        @if($col['dot'])<span class="w-2.5 h-2.5 rounded-full flex-none {{ $col['dot'] }}" aria-hidden="true"></span>@endif
                        <h2 id="col-{{ $col['key'] }}" class="!font-sans text-[13px] font-extrabold flex-1 truncate" title="{{ $col['custom'] ? 'Kolom buatanmu · status kasus tidak berubah' : 'Kolom status' }}">{{ $col['title'] }}</h2>
                        @if($col['custom'])
                            <span class="relative" x-data="{ menu: false }" x-on:keydown.escape="menu = false">
                                <button type="button" x-on:click="menu = !menu" class="icon-btn !w-7 !h-7 !border-transparent !bg-transparent hover:!bg-white" aria-label="Atur kolom {{ $col['title'] }}" title="Atur kolom"><x-icon name="more" size="15" /></button>
                                <div x-show="menu" x-cloak x-on:click.outside="menu = false" x-transition.origin.top.right class="absolute right-0 top-full mt-1 z-30 w-[240px] bg-white border border-line rounded-2xl shadow-modal p-3 flex flex-col gap-2.5">
                                    <form method="POST" action="{{ route('counselor.board-columns.update', $col['custom']) }}" class="flex flex-col gap-2">
                                        @csrf @method('PATCH')
                                        <label class="field-label !mb-0" for="bc-name-{{ $col['custom']->id }}">Nama kolom</label>
                                        <input id="bc-name-{{ $col['custom']->id }}" name="name" value="{{ $col['custom']->name }}" maxlength="40" required class="field !h-10 text-sm">
                                        @include('counselor.partials.board-colors', ['current' => $col['custom']->color, 'id' => $col['custom']->id])
                                        <button class="btn btn-sage btn-sm">Simpan</button>
                                    </form>
                                    <form method="POST" action="{{ route('counselor.board-columns.destroy', $col['custom']) }}" data-confirm-title="Hapus kolom “{{ $col['title'] }}”?" data-confirm="Kartunya kembali ke kolom status masing-masing." data-confirm-ok="Ya, hapus" data-confirm-tone="danger">@csrf @method('DELETE')<button class="w-full text-[13px] font-extrabold text-danger-ink py-1.5 rounded-xl hover:bg-danger-tint">Hapus kolom</button></form>
                                </div>
                            </span>
                        @endif
                        <span class="min-w-[22px] h-[22px] px-1.5 rounded-full bg-white text-xs font-extrabold flex items-center justify-center leading-none pt-px" data-count>{{ $col['cards']->count() }}</span>
                        <button type="button" x-on:click="toggleColumn('{{ $col['key'] }}')" class="icon-btn !w-7 !h-7 !border-transparent !bg-transparent hover:!bg-white" :aria-label="collapsed['{{ $col['key'] }}'] ? 'Lebarkan kolom' : 'Ciutkan kolom'" :title="collapsed['{{ $col['key'] }}'] ? 'Lebarkan kolom' : 'Ciutkan kolom'"><x-icon name="minimize" size="14" x-show="!collapsed['{{ $col['key'] }}']" /><x-icon name="maximize" size="14" x-show="collapsed['{{ $col['key'] }}']" x-cloak /></button>
                    </div>
                    <p x-show="collapsed['{{ $col['key'] }}']" x-cloak class="px-1.5 text-[11px] text-ink-soft leading-snug">Diciutkan. Seret kartu ke sini untuk memindahkan.</p>
                    <div class="flex flex-col gap-1.5 min-h-[48px]" :class="collapsed['{{ $col['key'] }}'] && '[&>article]:hidden rounded-xl border-[1.5px] border-dashed border-toggle-off'" data-column="{{ $col['key'] }}">
                        @foreach($col['cards'] as $c)
                            @php [$ul, $uc, $ud] = $urg[$c['urgency']]; @endphp
                            <article class="group bg-white rounded-[14px] px-3 py-2.5 flex flex-col gap-1.5 shadow-[0_1px_4px_rgba(62,53,48,0.06)] cursor-grab active:cursor-grabbing" data-uuid="{{ $c['uuid'] }}" data-board-url="{{ route('counselor.conversations.board', $c['uuid']) }}" data-name="{{ $c['display_name'] }}">
                                <div class="flex items-center gap-2 min-w-0">
                                    @if($c['identified'])<x-avatar-anon :name="$c['display_name']" :size="24" :radius="8" />@else<x-avatar-anon :avatar="$c['avatar']" :size="24" :radius="8" />@endif
                                    <a href="{{ $c['url'] }}" class="flex-1 font-extrabold text-[13px] text-ink no-underline truncate">{{ $c['display_name'] }}</a>
                                    <button type="button" x-on:click="openTransfer('{{ route('counselor.conversations.transfer', $c['uuid']) }}', @js($c['display_name']))" class="opacity-0 group-hover:opacity-100 focus:opacity-100 text-ink-soft hover:text-ink" title="Serah terima" aria-label="Serah terima {{ $c['display_name'] }}"><x-icon name="transfer" size="14" /></button>
                                </div>
                                <div class="flex items-center gap-1 flex-wrap text-[11px]">
                                    <span class="inline-flex items-center gap-1 px-1.5 py-px rounded-full font-extrabold {{ $uc }}"><span class="w-1.5 h-1.5 rounded-full {{ $ud }}"></span>{{ $ul }}</span>
                                    <x-priority-badge :score="$c['priority']" class="!px-1.5 !py-px !text-[11px]" />
                                    @if($c['flagged'])<span class="text-danger-ink" title="Kata sensitif belum ditinjau"><x-icon name="alert" size="13" /></span>@endif
                                </div>
                                <div class="flex justify-between gap-2 text-[11px] text-ink-soft">
                                    <span class="truncate">{{ collect($c['topics'])->pluck('name')->implode(', ') }}</span>
                                    <span class="whitespace-nowrap">@if($c['next_follow_up'])FU {{ $c['next_follow_up']->timezone($tz)->translatedFormat('j M') }} · @endif{{ $c['last_human'] }}</span>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endforeach
            {{-- Tambah kolom buatan sendiri --}}
            @if($boardColumns->count() < \App\Models\CaseBoardColumn::MAX_PER_COUNSELOR)
            <section class="w-[220px] flex-none" x-data="{ adding: {{ $errors->column->any() ? 'true' : 'false' }} }">
                <button type="button" x-show="!adding" x-on:click="adding = true; $nextTick(() => $refs.newCol.focus())" class="w-full h-12 rounded-[18px] border-[1.5px] border-dashed border-toggle-off text-sm font-extrabold text-ink-soft inline-flex items-center justify-center gap-1.5 hover:bg-white hover:text-ink transition"><x-icon name="plus" size="16" />Tambah kolom</button>
                <form x-show="adding" x-cloak method="POST" action="{{ route('counselor.board-columns.store') }}" class="bg-white border border-line-soft rounded-[18px] p-3 flex flex-col gap-2.5 shadow-soft">
                    @csrf
                    <label for="bc-new" class="field-label !mb-0">Nama kolom baru</label>
                    <input id="bc-new" x-ref="newCol" name="name" value="{{ old('name') }}" maxlength="40" required placeholder="mis. Tunggu wali kelas" class="field !h-10 text-sm">
                    @include('counselor.partials.board-colors', ['current' => old('color', 'sand'), 'id' => 'new'])
                    @if($errors->column->any())<p class="field-error !mt-0">{{ $errors->column->first() }}</p>@endif
                    <div class="flex gap-2"><button type="button" x-on:click="adding = false" class="btn btn-ghost btn-sm flex-1">Batal</button><button class="btn btn-sage btn-sm flex-1">Tambah</button></div>
                </form>
            </section>
            @endif
        </div>
        <p class="text-xs text-ink-soft">Seret kartu untuk mengubah status. Kolom buatanmu hanya untuk mengelompokkan, status kasusnya tidak berubah.</p>
    @else
        <div class="box p-1 overflow-x-auto">
            <table class="table-wl min-w-[960px]">
                <thead><tr><th scope="col">Murid</th><th scope="col">Topik</th><th scope="col">Urgensi</th><th scope="col">Status</th><th scope="col">Prioritas</th><th scope="col">Terakhir aktif</th><th scope="col">Follow-up berikutnya</th><th scope="col">Aksi</th></tr></thead>
                <tbody>
                    @forelse($items as $c)
                        @php [$ul, $uc, $ud] = $urg[$c['urgency']]; @endphp
                        <tr>
                            <td class="font-extrabold">{{ $c['display_name'] }}@if($c['class_name'])<span class="block text-xs text-ink-soft font-bold">{{ $c['class_name'] }}</span>@endif</td>
                            <td class="text-ink-soft">{{ collect($c['topics'])->pluck('name')->implode(', ') }}</td>
                            <td><span class="inline-flex items-center gap-1.5 px-2.5 py-[3px] rounded-full text-xs font-extrabold {{ $uc }}"><span class="w-[7px] h-[7px] rounded-full {{ $ud }}"></span>{{ $ul }}</span></td>
                            <td class="font-bold">{{ $c['status_label'] }}</td>
                            <td>@if($c['priority'] >= 30)<x-priority-badge :score="$c['priority']" />@else<span class="text-ink-soft">{{ $c['priority'] }}</span>@endif</td>
                            <td class="text-ink-soft">{{ $c['last_human'] }}</td>
                            <td class="text-ink-soft">{{ $c['next_follow_up'] ? $c['next_follow_up']->timezone($tz)->translatedFormat('j M Y') : '-' }}</td>
                            <td><span class="flex gap-1.5"><a href="{{ $c['url'] }}" class="tool-btn no-underline">Buka</a><button type="button" x-on:click="openTransfer('{{ route('counselor.conversations.transfer', $c['uuid']) }}', @js($c['display_name']))" class="tool-btn">Serah terima</button></span></td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><x-empty-state title="Belum ada kasus" text="Kasus muncul di sini setelah murid mulai bercerita." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    {{-- Modal serah terima --}}
    <div x-show="transfer.open" x-cloak class="scrim fixed inset-0 z-50 flex items-end sm:items-start justify-center sm:pt-[110px] sm:px-6">
        <section role="dialog" aria-modal="true" aria-labelledby="ho-h" x-trap.noscroll="transfer.open" x-on:click.outside="transfer.open = false" class="w-full max-w-[520px] bg-cream rounded-t-[24px] sm:rounded-[24px] p-6 flex flex-col gap-4 shadow-modal animate-pop max-h-[90vh] overflow-y-auto">
            <div class="flex flex-col gap-1"><h2 id="ho-h" class="text-2xl">Serah terima kasus <span x-text="transfer.name"></span></h2><span class="text-sm text-ink-soft">Chat dan jadwal ikut pindah. Catatan privatmu tidak.</span></div>
            <form method="POST" :action="transfer.action" class="flex flex-col gap-3.5">
                @csrf
                <div role="radiogroup" aria-label="Pilih guru" class="flex flex-col gap-2">
                    @foreach($colleagues as $g)
                        <label class="flex items-center gap-3 p-3 rounded-2xl bg-white border-[1.5px] border-line cursor-pointer has-[:checked]:border-sage-deep has-[:checked]:bg-[#EEF2EA]"><input type="radio" name="to_counselor_id" value="{{ $g->id }}" class="w-5 h-5 accent-sage-deep" required><x-counselor-avatar :counselor="$g" :size="36" :radius="12" /><span class="font-extrabold text-sm">{{ $g->displayName() }} <span class="font-bold text-ink-soft">· {{ $g->name }}</span></span></label>
                    @endforeach
                </div>
                <label for="ho-note" class="text-sm font-bold">Catatan serah terima (opsional)</label>
                <textarea id="ho-note" name="handover_note" rows="3" class="field text-sm"></textarea>
                <div class="flex justify-end gap-2"><button type="button" x-on:click="transfer.open = false" class="btn btn-outline btn-sm">Batal</button><button class="btn btn-sage btn-sm">Serahkan kasus</button></div>
            </form>
        </section>
    </div>

    {{-- Modal konfirmasi Selesai (drag ke kolom Selesai) --}}
    <div x-show="resolve.open" x-cloak class="scrim fixed inset-0 z-50 flex items-center justify-center p-6">
        <section role="dialog" aria-modal="true" aria-labelledby="rs-h" x-trap.noscroll="resolve.open" class="w-full max-w-[480px] bg-cream rounded-panel p-7 flex flex-col gap-3.5 shadow-modal animate-pop">
            <h2 id="rs-h" class="text-2xl">Tandai “<span x-text="resolve.name"></span>” selesai?</h2>
            <p class="text-sm leading-relaxed text-ink-soft">Murid diberi tahu, lalu follow-up dikirim {{ config('welcoming.follow_up_days') }} hari lagi.</p>
            <div class="flex justify-end gap-2"><button type="button" x-on:click="cancelResolve()" class="btn btn-outline btn-sm">Batal</button><button type="button" x-on:click="confirmResolve()" class="btn btn-sage btn-sm">Ya, tandai Selesai</button></div>
        </section>
    </div>
</div>
@endsection
