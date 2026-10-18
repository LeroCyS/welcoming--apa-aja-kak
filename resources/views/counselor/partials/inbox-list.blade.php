{{-- Baris inbox ringkas. Kasus selesai dilipat di bawah (status buka/tutup disimpan di komponen induk: `showDone`). --}}
@php
    $urg = \App\Models\Conversation::urgencyStyles();
    [$done, $open] = collect($items)->partition(fn ($it) => $it['status'] === 'resolved');
@endphp
@if(empty($items))
    <li class="list-none"><x-empty-state title="Inbox masih tenang" text="Belum ada cerita. Cerita baru muncul di sini, yang paling mendesak di atas." /></li>
@endif
@foreach([$open, $done] as $group)
    @if($loop->last && $done->isNotEmpty())
        <li class="list-none border-t border-line-soft">
            <button type="button" x-on:click="showDone = !showDone" :aria-expanded="showDone" class="w-full flex items-center gap-2 px-5 py-3.5 text-sm font-extrabold text-ink-soft bg-cream-light/50 hover:bg-cream-light">
                <x-icon name="chevron-right" size="14" class="transition" ::class="showDone && 'rotate-90'" />Selesai ({{ $done->count() }})
            </button>
        </li>
    @endif
    @foreach($group as $it)
        @php [$ul, $uc, $ud] = $urg[$it['urgency']]; $critical = $it['priority'] >= 60; @endphp
        <li class="list-none border-t border-line-soft first:border-t-0" @if($it['status'] === 'resolved') x-show="showDone" x-cloak @endif>
            <a href="{{ $it['url'] }}" title="{{ $it['reasons'] ? 'Alasan prioritas: '.implode(', ', $it['reasons']) : '' }}" class="group no-underline text-ink grid grid-cols-[auto_minmax(0,1fr)_auto] lg:grid-cols-[auto_minmax(0,1fr)_170px_170px_120px] gap-x-4 gap-y-2 items-center px-4 lg:px-5 py-4 transition-colors hover:bg-cream-light {{ $critical ? 'bg-danger-tint/40 shadow-[inset_4px_0_0_var(--color-danger)]' : '' }} {{ $it['status'] === 'resolved' ? 'opacity-70' : '' }}">
                @if($it['identified'])
                    <x-avatar-anon :name="$it['display_name']" :size="44" :radius="14" />
                @else
                    <x-avatar-anon :avatar="$it['avatar']" :size="44" :radius="14" />
                @endif
                <span class="min-w-0 flex flex-col gap-1">
                    <span class="flex items-center gap-2 min-w-0">
                        <span class="text-[15px] truncate {{ $it['unread'] ? 'font-extrabold' : 'font-bold' }}">{{ $it['display_name'] }}@if($it['class_name']) <span class="font-bold text-ink-soft">· {{ $it['class_name'] }}</span>@endif</span>
                        @if($it['is_emergency'])<span class="text-[11px] font-extrabold text-white bg-danger px-2 py-0.5 rounded-full flex-none whitespace-nowrap">Darurat</span>@endif
                        @if($it['flagged'])<span title="Ada kata kunci sensitif yang belum ditinjau" class="inline-flex items-center gap-1 text-[11px] font-extrabold text-danger-ink bg-danger-tint px-1.5 sm:px-2 py-0.5 rounded-full flex-none whitespace-nowrap"><x-icon name="alert" size="12" /><span class="hidden sm:inline">Kata sensitif</span><span class="sr-only sm:hidden">Kata sensitif</span></span>@endif
                        @if($it['new_member'] ?? false)<span title="Murid baru bergabung ke sekolah ini saat memulai cerita" class="max-sm:hidden text-[11px] font-extrabold bg-sand-tint text-sand-ink px-2 py-0.5 rounded-full flex-none whitespace-nowrap">Murid baru</span>@endif
                        @if($it['prank'] ?? false)<span class="text-[11px] font-extrabold bg-terracotta-tint text-terracotta-ink px-2 py-0.5 rounded-full flex-none whitespace-nowrap">Iseng</span>@endif
                    </span>
                    <span class="text-sm leading-snug truncate {{ $it['unread'] ? 'text-ink font-semibold' : 'text-ink-soft' }}"><x-message-preview :icon="$it['preview_icon'] ?? null" :prefix="($it['preview_mine'] ?? false) ? 'Kamu: ' : ''" :text="($it['preview_mine'] ?? false) ? \Illuminate\Support\Str::after($it['preview'], 'Kamu: ') : $it['preview']" /></span>
                    @if(collect($it['topics'])->isNotEmpty())
                        <span class="flex gap-1 flex-wrap">@foreach($it['topics'] as $t)<span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-cream border border-line-soft whitespace-nowrap">{{ $t['name'] }}</span>@endforeach</span>
                    @endif
                </span>
                {{-- Urgensi & prioritas --}}
                <span class="hidden lg:flex flex-col items-start gap-1.5">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-extrabold {{ $uc }}"><span class="w-2 h-2 rounded-full {{ $ud }}"></span>{{ $ul }}</span>
                    <x-priority-badge :score="$it['priority']" class="!px-2 !py-0.5 !text-[11px]" />
                </span>
                {{-- Status --}}
                <span class="hidden lg:inline-flex self-center w-fit px-2.5 py-1 rounded-full bg-cream text-xs font-extrabold text-ink-soft whitespace-nowrap">{{ $it['status_label'] }}</span>
                {{-- Waktu & belum dibaca --}}
                <span class="flex flex-col items-end gap-1.5">
                    <span class="text-xs whitespace-nowrap {{ $it['unread'] ? 'font-extrabold text-terracotta-ink' : 'text-ink-soft' }}">{{ $it['last_human'] }}</span>
                    @if($it['unread'])
                        <span aria-label="{{ $it['unread'] }} pesan belum dibaca" class="min-w-[22px] h-[22px] px-1.5 rounded-full bg-terracotta-deep text-white text-[11px] font-extrabold flex items-center justify-center leading-none pt-px">{{ $it['unread'] }}</span>
                    @else
                        <span class="lg:hidden inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-extrabold {{ $uc }}"><span class="w-1.5 h-1.5 rounded-full {{ $ud }}"></span>{{ $ul }}</span>
                    @endif
                </span>
            </a>
        </li>
    @endforeach
@endforeach
