{{-- Lonceng notifikasi: dropdown terbaru (ikon, jenis, potongan isi), badge, suara & notifikasi perangkat. Data via /notifikasi/feed --}}
@props(['tone' => 'terracotta'])
<div class="relative" x-data="notificationBell('{{ route('notifications.feed') }}', '{{ route('notifications.read-all') }}')" x-on:click.outside="open = false" x-on:keydown.escape.window="open = false">
    <button type="button" x-on:click="toggle()" :aria-label="'Notifikasi, ' + unread + ' baru'" :aria-expanded="open" class="bell-btn head-btn relative" :class="open && '!bg-cream-light !border-ink-soft/50'">
        <x-icon name="bell" size="20" />
        <span x-show="unread > 0" x-text="unread > 99 ? '99+' : unread" x-cloak class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 rounded-full bg-terracotta-deep text-white text-[11px] font-extrabold flex items-center justify-center border-2 border-white shadow-xs leading-none pt-px"></span>
    </button>
    {{-- HP: lonceng tidak di ujung kanan, jadi dropdown digeser agar selebar layar dengan jarak 16px dari tepi.
         Posisi dihitung saat dibuka & saat ukuran layar berubah (bukan saat ditutup) supaya animasi tutup tidak melompat. --}}
    <div x-data="{ place: {} }" x-effect="if (open) place = window.innerWidth < 640 ? { left: (16 - $el.parentElement.getBoundingClientRect().left) + 'px', right: 'auto', width: (window.innerWidth - 32) + 'px', transformOrigin: 'top center' } : {}" x-on:resize.window.debounce.100ms="place = {}; if (open && window.innerWidth < 640) place = { left: (16 - $el.parentElement.getBoundingClientRect().left) + 'px', right: 'auto', width: (window.innerWidth - 32) + 'px', transformOrigin: 'top center' }" :style="place"
         x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1.5 scale-[.97]" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0 scale-100" x-transition:leave-end="opacity-0 -translate-y-1.5 scale-[.97]"
         class="absolute right-0 mt-2.5 w-[min(400px,calc(100vw-32px))] origin-top-right will-change-transform bg-white rounded-card-l shadow-modal border border-line z-50 overflow-hidden">
        <div class="flex items-center justify-between gap-2 px-4 py-3.5 border-b border-line-soft bg-cream-light/60">
            <span class="font-extrabold">Notifikasi <span x-show="unread > 0" class="text-ink-soft font-bold text-sm" x-text="'· ' + unread + ' baru'"></span></span>
            <span class="flex items-center gap-1">
                <button type="button" x-on:click="toggleSound()" class="w-8 h-8 rounded-full flex items-center justify-center text-ink-soft hover:bg-cream-light" :aria-label="muted ? 'Nyalakan suara notifikasi' : 'Matikan suara notifikasi'" :title="muted ? 'Suara mati' : 'Suara menyala'">
                    <svg x-show="!muted" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 5 6 9H2v6h4l5 4V5z"/><path d="M15.5 8.5a5 5 0 0 1 0 7M19 5a10 10 0 0 1 0 14"/></svg>
                    <svg x-show="muted" x-cloak width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 5 6 9H2v6h4l5 4V5z"/><path d="m22 9-6 6M16 9l6 6"/></svg>
                </button>
                <button type="button" class="text-[13px] font-extrabold text-{{ $tone }}-deep px-1" x-on:click="readAll()" x-show="unread > 0">Tandai semua dibaca</button>
            </span>
        </div>
        <button type="button" x-show="pushState === 'default'" x-cloak x-on:click="enablePush()" class="w-full flex items-center gap-2.5 px-4 py-2.5 bg-cream-light text-left text-[13px] font-bold border-b border-line-soft"><x-icon name="bell" size="16" />Aktifkan notifikasi di perangkat ini supaya tetap dapat kabar walau Welcoming ditutup.</button>
        <ul class="max-h-[420px] overflow-y-auto">
            <template x-for="n in items" :key="n.id">
                <li><a :href="n.url" class="flex items-center gap-3 px-4 py-3 no-underline text-ink hover:bg-cream-light border-b border-line-soft last:border-b-0" :class="n.read ? '' : 'bg-cream-light'">
                    <span class="w-9 h-9 flex-none rounded-xl flex items-center justify-center" :class="'bg-' + n.tone + '-tint text-' + n.tone + '-ink'" x-html="n.icon"></span>
                    <span class="flex-1 min-w-0 flex flex-col gap-0.5">
                        <span class="flex items-center gap-1.5 min-w-0 text-[11px] whitespace-nowrap"><span class="font-extrabold uppercase tracking-[0.06em] flex-none" :class="'text-' + n.tone + '-ink'" x-text="n.critical ? 'Penting' : n.label"></span><span class="text-ink-soft truncate" x-text="'· ' + n.time"></span></span>
                        <span class="text-sm font-bold leading-snug" :class="n.critical ? 'text-danger-ink' : ''" x-text="n.title"></span>
                        <span x-show="n.body" class="text-[13px] text-ink-soft leading-snug line-clamp-2" x-text="n.body"></span>
                    </span>
                    <span x-show="!n.read" class="w-2 h-2 rounded-full flex-none" :class="n.critical ? 'bg-danger' : 'bg-{{ $tone }}-deep'"></span>
                </a></li>
            </template>
            <li x-show="items.length === 0" class="px-4 py-8 text-center text-sm text-ink-soft">Belum ada notifikasi.</li>
        </ul>
        <a href="{{ route('notifications.index') }}" class="block text-center px-4 py-3 border-t border-line-soft text-sm font-extrabold no-underline text-{{ $tone }}-deep">Lihat semua</a>
    </div>
</div>
