{{--
    Waktu + centang seperti WhatsApp (hanya untuk pesan milik penonton):
    satu abu-abu = terkirim, penerima sedang offline · dua abu-abu = sudah sampai · dua hijau = sudah dibaca.
--}}
@php $state = $m->read_at ? 'read' : ($m->delivered_at ? 'delivered' : 'sent'); @endphp
<span class="group/rc inline-flex items-center gap-1 text-[11px] text-ink-soft">
    @if($m->isRetracted() && ! ($hideContent ?? true))<span class="font-extrabold text-danger-ink">Dihapus murid · tetap terlihat karena kata kunci kritis ·</span>@endif
    @if($m->edited_at && ! $m->isRetracted())<span class="italic">diedit ·</span>@endif
    {{ $time }}
    @if($mine)
        <span data-receipt="{{ $m->id }}" data-state="{{ $state }}" class="receipt inline-flex" role="img"
              aria-label="{{ ['sent' => 'Terkirim', 'delivered' => 'Sampai', 'read' => 'Dibaca'][$state] }}" title="{{ ['sent' => 'Terkirim (penerima sedang offline)', 'delivered' => 'Sampai', 'read' => 'Dibaca'][$state] }}">
            <svg width="18" height="12" viewBox="0 0 18 12" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path class="tick-1" d="M1 6.5 4.5 10 11 2"/><path class="tick-2" d="M7.5 9.2 8.3 10 14.8 2"/></svg>
        </span>
    @endif
    @if($menu ?? null)
        <button type="button" x-on:click="openMessageMenu(@js($menu))" aria-label="Pilihan pesan" title="Pilihan pesan" class="msg-menu-btn ml-0.5 w-6 h-6 -my-1 rounded-full inline-flex items-center justify-center text-ink-soft hover:bg-ink/5 hover:text-ink"><x-icon name="more" size="14" /></button>
    @endif
</span>
