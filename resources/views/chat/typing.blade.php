{{-- "{nama} sedang mengetik…", muncul langsung saat lawan chat mengetik, supaya terasa ada yang hadir. --}}
<div x-show="typing" x-cloak x-transition.opacity class="{{ $pad ?? 'px-4 sm:px-6 bg-[#FDFBF6]' }} pb-2 -mt-1" aria-live="polite">
    <span class="inline-flex items-center gap-2.5 px-4 py-2.5 rounded-[20px_20px_20px_6px] {{ $role === 'student' ? 'bg-sage-tint text-sage-ink' : 'bg-terracotta-tint text-terracotta-ink' }}">
        <span class="typing-dots" aria-hidden="true"><span></span><span></span><span></span></span>
        <span class="text-[13px] font-bold">{{ $name }} sedang mengetik…</span>
    </span>
</div>
{{-- Pesan baru masuk saat sedang membaca riwayat di atas --}}
<button type="button" x-show="unseen > 0" x-cloak x-transition x-on:click="scrollBottom(true)" class="absolute left-1/2 -translate-x-1/2 bottom-24 z-30 inline-flex items-center gap-2 h-10 px-4 rounded-full shadow-modal text-sm font-extrabold text-white {{ $role === 'student' ? 'bg-terracotta-deep' : 'bg-sage-deep' }}">
    <x-icon name="chevron-down" size="16" /><span x-text="unseen + ' pesan baru'"></span>
</button>
