@php
    $open = $inv->isOpen();
    $expired = $inv->status === 'expired' || ($inv->status === 'pending' && $inv->expires_at->isPast());
    $fmt = fn ($s) => $s->start_at->timezone($tz)->translatedFormat('l, j M').' · '.$s->start_at->timezone($tz)->format('H.i').'-'.$s->end_at->timezone($tz)->format('H.i');
@endphp
@if($role === 'student')
    <div id="undangan-{{ $inv->id }}" class="self-stretch sm:self-start sm:max-w-[80%] lg:max-w-[62%] bg-white border border-line rounded-card p-4 flex flex-col gap-3 shadow-card scroll-mt-24"
         x-data="{ slot: null, confirm: false }">
        <span class="flex gap-2.5 items-center">
            <span class="w-11 h-11 flex-none rounded-[14px] bg-sand-tint flex items-center justify-center text-sand-deep"><x-icon name="calendar" size="22" /></span>
            <span class="flex flex-col"><span class="font-extrabold text-[15px]">Ngobrol langsung, yuk?</span><span class="text-xs text-ink-soft">Undangan dari {{ $inv->counselor->displayName() }} · berlaku s.d. {{ $inv->expires_at->timezone($tz)->translatedFormat('j M') }}</span></span>
        </span>
        @if($inv->message)<p class="text-sm leading-relaxed">{{ $inv->message }}</p>@endif

        @if($expired)
            <span class="self-start text-sm font-extrabold px-3 py-1.5 rounded-full bg-cream-deep text-ink-soft whitespace-nowrap">Undangan sudah kedaluwarsa</span>
        @elseif($inv->status === 'accepted' && $inv->session)
            <div class="flex flex-col gap-2 p-3 rounded-2xl bg-sage-tint text-sage-ink">
                <span class="text-sm font-extrabold">Jadwal dipilih: {{ $fmt($inv->session->slot) }}</span>
                <span class="flex gap-2 flex-wrap">
                    <a href="{{ route('student.schedule.index') }}" class="text-[13px] font-extrabold underline">Lihat Jadwal Saya</a>
                </span>
            </div>
        @elseif($inv->status === 'declined')
            <span class="self-start text-sm font-bold px-3 py-1.5 rounded-full bg-cream-deep text-ink-soft">Kamu memilih belum siap. Nggak apa-apa, ceritanya tetap bisa lanjut di sini.</span>
        @elseif($open)
            <form method="POST" action="{{ route('student.invitations.accept', $inv) }}" x-ref="acceptForm" class="flex flex-col gap-2" x-on:submit="if ({{ $ctx['anonToCounselor'] ? 'true' : 'false' }} && !confirm) { $event.preventDefault(); confirm = true; }">
                @csrf
                <input type="hidden" name="confirm_reveal" :value="confirm ? 1 : 0">
                <fieldset class="flex flex-col gap-2">
                    <legend class="text-[13px] font-extrabold text-ink-soft mb-1">Pilih satu waktu:</legend>
                    @foreach($inv->slots as $s)
                        @php $free = $s->status === 'available' && $s->start_at->isFuture(); @endphp
                        <label class="flex items-center gap-3 px-3.5 py-3 rounded-2xl border-[1.5px] {{ $free ? 'border-line cursor-pointer has-[:checked]:border-terracotta-deep has-[:checked]:bg-terracotta-tint2' : 'border-line-soft bg-cream-light opacity-60' }}">
                            <input type="radio" name="slot_id" value="{{ $s->id }}" x-model="slot" class="w-5 h-5 accent-terracotta-deep" @disabled(! $free) required>
                            <span class="flex-1 text-sm font-bold">{{ $fmt($s) }}</span>
                            @unless($free)<span class="text-xs font-bold text-ink-soft">sudah terisi</span>@endunless
                        </label>
                    @endforeach
                </fieldset>
                <div class="flex gap-2 mt-1">
                    <button class="flex-[1.4] btn btn-primary btn-sm !h-11 !text-sm" :disabled="!slot">Terima & Pilih Jadwal</button>
                    <button type="submit" form="decline-{{ $inv->id }}" class="flex-1 btn btn-outline btn-sm !h-11 !text-sm">Belum siap</button>
                </div>
            </form>
            <form id="decline-{{ $inv->id }}" method="POST" action="{{ route('student.invitations.decline', $inv) }}" class="hidden">@csrf</form>

            {{-- Dialog: menerima undangan saat anonim akan membuka identitas --}}
            <div x-show="confirm" x-cloak class="scrim fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:p-6">
                <section role="dialog" aria-modal="true" aria-labelledby="inv-rev-{{ $inv->id }}" x-trap.noscroll="confirm" class="w-full max-w-[480px] bg-cream rounded-t-[28px] sm:rounded-panel p-7 flex flex-col gap-3.5 shadow-modal">
                    <div class="w-[60px] h-[60px] rounded-[20px] bg-sage-tint flex items-center justify-center text-sage-ink2 self-center"><x-icon name="user-check" size="30" /></div>
                    <h2 id="inv-rev-{{ $inv->id }}" class="text-[24px] text-center leading-tight">Untuk bertemu langsung, identitasmu akan terlihat oleh {{ $inv->counselor->displayName() }}. Lanjutkan?</h2>
                    <p class="text-sm leading-relaxed text-ink-soft text-center">Nama dan kelasmu akan terlihat dan tidak bisa dibatalkan. Belum siap? Nggak apa-apa.</p>
                    <div class="flex gap-2.5">
                        <button type="button" x-on:click="confirm = false" class="flex-1 btn btn-outline">Belum dulu</button>
                        <button type="button" x-on:click="$nextTick(() => $refs.acceptForm.submit())" class="flex-1 btn btn-sage">Ya, lanjutkan</button>
                    </div>
                </section>
            </div>
        @endif
    </div>
@else
    <div class="self-end max-w-[92%] sm:max-w-[70%] bg-white border border-line rounded-card p-4 flex flex-col gap-2">
        <span class="flex gap-2.5 items-center"><span class="w-10 h-10 flex-none rounded-xl bg-sand-tint flex items-center justify-center text-sand-deep"><x-icon name="calendar" size="20" /></span>
            <span class="flex flex-col"><span class="font-extrabold text-sm">Undangan sesi tatap muka</span><span class="text-xs text-ink-soft">{{ $inv->slots->count() }} pilihan waktu · {{ $expired ? 'kedaluwarsa' : ['pending' => 'menunggu jawaban murid', 'accepted' => 'diterima', 'declined' => 'murid belum siap', 'expired' => 'kedaluwarsa'][$inv->status] }}</span></span>
        </span>
        @if($inv->status === 'accepted' && $inv->session)
            <span class="text-sm font-extrabold text-sage-ink">Jadwal: {{ $fmt($inv->session->slot) }}</span>
        @else
            <ul class="text-xs text-ink-soft list-disc pl-5">@foreach($inv->slots as $s)<li>{{ $fmt($s) }}</li>@endforeach</ul>
        @endif
    </div>
@endif
