{{-- Header Guru: notifikasi + toggle Online/Offline + profil --}}
@php $u = auth()->user(); $online = (bool) $u->counselorProfile?->is_online; @endphp
<div class="flex gap-2 items-center justify-end" x-data>
    <x-notification-bell tone="sage" />
    {{-- Yang tertulis di sini sama persis dengan yang dilihat murid. --}}
    <button type="button" x-on:click="$store.myPresence.toggle()" :aria-pressed="$store.myPresence.enabled.toString()" title="Status ini langsung terlihat oleh murid di sekolahmu" class="inline-flex items-center gap-2 h-9 pl-2.5 sm:pl-3 pr-2 rounded-full border-[1.5px] border-line bg-white text-sm font-extrabold" :aria-label="'Status: ' + (!$store.myPresence.connected ? 'Terputus' : ($store.myPresence.online ? 'Online' : ($store.myPresence.enabled ? 'Menghubungkan' : 'Offline')))" :class="$store.myPresence.online ? 'text-sage-ink2' : ($store.myPresence.connected ? 'text-ink-soft' : 'text-danger-ink')">
        <span class="w-2.5 h-2.5 rounded-full" :class="$store.myPresence.online ? 'status-on' : ($store.myPresence.connected && $store.myPresence.enabled ? 'bg-sand-deep' : 'status-off')"></span>
        <span class="hidden sm:inline" x-text="!$store.myPresence.connected ? 'Terputus' : ($store.myPresence.online ? 'Online' : ($store.myPresence.enabled ? 'Menghubungkan…' : 'Offline'))">{{ $online ? 'Online' : 'Offline' }}</span>
        <span class="relative w-8 h-5 rounded-full flex-none transition" :class="$store.myPresence.enabled ? 'bg-sage-deep' : 'bg-toggle-off'"><span class="absolute top-[3px] w-[14px] h-[14px] rounded-full bg-white transition-all" :class="$store.myPresence.enabled ? 'left-[15px]' : 'left-[3px]'"></span></span>
    </button>
    <a href="{{ route('counselor.profile') }}" class="hidden xl:flex items-center gap-2 no-underline text-ink">
        <x-counselor-avatar :counselor="$u" :size="36" :radius="12" />
        <span class="font-extrabold text-sm whitespace-nowrap">{{ $u->displayName() }}</span>
    </a>
</div>
