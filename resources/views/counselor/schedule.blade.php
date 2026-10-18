@extends('layouts.counselor', ['active' => 'jadwal'])
@section('title', 'Jadwal Konseling')
@section('header')
<header class="flex items-center justify-between gap-4 flex-wrap no-print">
    <div class="flex items-center gap-3 min-w-0">
    <x-back-button :href="route('counselor.dashboard')" />
    <div class="flex flex-col gap-0.5">
        <h1 class="text-2xl lg:text-[26px] leading-tight">Jadwal Konseling</h1>
        <span class="text-sm text-ink-soft">Minggu ini: {{ $stats['booked'] }} sesi terisi, {{ $stats['available'] }} slot tersedia</span>
    </div>
    </div>
</header>
@endsection
@section('content')
<div class="flex flex-col gap-3">
    <section aria-labelledby="cal-h" class="min-w-0 box p-4 flex flex-col gap-3">
        <div class="flex justify-between items-center gap-3 flex-wrap">
            <div class="flex items-center gap-2">
                <a href="{{ route('counselor.schedule', ['minggu' => $start->subWeek()->toDateString()]) }}" aria-label="Minggu sebelumnya" class="icon-btn"><x-icon name="chevron-left" size="16" /></a>
                <h2 id="cal-h" class="num !font-extrabold text-lg">{{ $start->translatedFormat('j') }}-{{ $start->addDays(6)->translatedFormat('j F Y') }}</h2>
                <a href="{{ route('counselor.schedule', ['minggu' => $start->addWeek()->toDateString()]) }}" aria-label="Minggu berikutnya" class="icon-btn"><x-icon name="chevron-right" size="16" /></a>
            </div>
            <div class="flex gap-3.5 text-xs font-bold flex-wrap items-center">
                <span class="inline-flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded border-[1.5px] border-dashed border-sage-deep bg-[#F4F7F1]"></span>Tersedia</span>
                <span class="inline-flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded bg-terracotta-tint"></span>Terisi</span>
                <span class="inline-flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded bg-line-soft"></span>Diblokir</span>
                <button type="button" x-data x-on:click="$dispatch('open-modal', 'slot')" class="btn btn-sage btn-sm ml-1"><x-icon name="plus" size="15" />Buat slot</button>
            </div>
        </div>
        <details class="rounded-2xl bg-cream-light px-4 py-3 text-sm leading-relaxed">
            <summary class="cursor-pointer font-extrabold inline-flex items-center gap-1.5"><x-icon name="info" size="15" />Apa itu slot dan “Blokir slot”?</summary>
            <ul class="mt-2 list-disc pl-5 flex flex-col gap-1 text-ink-soft">
                <li><b class="text-ink">Slot</b> = jam kosongmu. Murid memilih slot <b class="text-ink">Tersedia</b> saat kamu kirim “Undang sesi”.</li>
                <li><b class="text-ink">Blokir slot</b> = tutup sementara tanpa menghapus, misalnya saat ada rapat.</li>
                <li><b class="text-ink">Buka blokir</b> mengembalikan slot menjadi Tersedia. Slot yang sudah <b class="text-ink">Terisi</b> tidak bisa diblokir; ubah atau batalkan sesinya terlebih dahulu.</li>
            </ul>
        </details>
        <div class="overflow-x-auto">
            <div class="min-w-[640px] grid gap-1.5" style="grid-template-columns: 56px repeat({{ $days->count() }}, minmax(0, 1fr))">
                <span></span>
                @foreach($days as $d)<span class="text-center text-[13px] font-extrabold py-1.5 rounded-xl {{ $d->isToday() ? 'bg-sage-tint text-sage-ink' : 'text-ink-soft' }}">{{ $d->translatedFormat('D, j') }}</span>@endforeach
                @foreach($hours as $h)
                    <span class="text-xs font-bold text-ink-soft pt-2 text-right pr-1">{{ sprintf('%02d.00', $h) }}</span>
                    @foreach($days as $d)
                        @php $cell = $slots->filter(fn ($s) => $s->start_at->timezone($tz)->isSameDay($d) && (int) $s->start_at->timezone($tz)->format('G') === $h); @endphp
                        <div class="min-h-10 rounded-lg border border-line-soft p-0.5 flex flex-col gap-0.5">
                            @foreach($cell as $s)
                                @php $label = $s->start_at->timezone($tz)->format('H.i').'-'.$s->end_at->timezone($tz)->format('H.i'); @endphp
                                <div x-data="{ open: false }" class="relative">
                                    <button type="button" x-on:click="open = !open" class="w-full text-left rounded-lg px-2 py-1.5 text-[11px] font-extrabold leading-tight {{ $s->status === 'booked' ? 'bg-terracotta-tint text-terracotta-ink' : ($s->status === 'blocked' ? 'bg-line-soft text-ink-soft line-through' : 'border-[1.5px] border-dashed border-sage-deep bg-[#F4F7F1] text-sage-ink') }}" title="{{ $label }} · {{ $s->location }}">
                                        {{ $label }}@if($s->status === 'booked' && $s->session)<span class="block font-bold truncate">{{ $s->session->student->name ?? 'Murid' }}</span>@endif
                                    </button>
                                    <div x-show="open" x-cloak x-on:click.outside="open = false" class="absolute z-20 left-0 top-full mt-1 w-52 bg-white rounded-2xl shadow-modal p-3 flex flex-col gap-2 text-xs">
                                        <span class="font-extrabold text-sm">{{ $label }}</span><span class="text-ink-soft">{{ $s->location }} · {{ ['available' => 'Tersedia', 'booked' => 'Terisi', 'blocked' => 'Diblokir'][$s->status] }}</span>
                                        @if($s->status !== 'booked')
                                            <form method="POST" action="{{ route('counselor.slots.toggle', $s) }}">@csrf @method('PATCH')<button class="w-full btn btn-soft !h-9 !text-xs">{{ $s->status === 'blocked' ? 'Buka blokir' : 'Blokir slot' }}</button></form>
                                            <form method="POST" action="{{ route('counselor.slots.destroy', $s) }}" data-confirm="Hapus slot ini?" data-confirm-ok="Ya, hapus" data-confirm-tone="danger">@csrf @method('DELETE')<button class="w-full btn btn-ghost !h-9 !text-xs text-ink-soft">Hapus slot</button></form>
                                        @elseif($s->session)
                                            <a href="{{ route('counselor.conversations.show', $s->session->conversation) }}" class="btn btn-soft !h-9 !text-xs">Buka percakapan</a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                @endforeach
            </div>
        </div>
    </section>


<section aria-labelledby="book-h" class="box p-4 flex flex-col gap-1">
    <h2 id="book-h" class="text-lg mb-1">Sesi mendatang & terbaru</h2>
    @forelse($sessions as $s)
        @php $st = $s->slot->start_at->timezone($tz); @endphp
        <div class="flex items-center gap-3 py-2 px-1 border-t border-line-soft flex-wrap">
            <span class="w-[140px] font-extrabold text-sm">{{ $st->translatedFormat('D, j M') }} · {{ $st->format('H.i') }}</span>
            <span class="flex-1 min-w-[200px] flex flex-col"><span class="font-extrabold text-[15px]">{{ $s->student->name ?? 'Murid' }}@if($s->student->studentProfile?->class_name) · {{ $s->student->studentProfile->class_name }}@endif</span><span class="text-[13px] text-ink-soft">{{ $s->slot->location }}@if($s->reschedule_count) · diubah {{ $s->reschedule_count }}× @endif</span></span>
            <x-badge :tone="['scheduled' => 'sand', 'completed' => 'sage', 'cancelled' => 'neutral', 'no_show' => 'terracotta'][$s->status]" class="!text-xs">{{ $s->statusLabel() }}</x-badge>
            @if($s->status === 'scheduled')
                <form method="POST" action="{{ route('counselor.sessions.update', $s->id) }}" class="flex gap-1.5 flex-wrap">@csrf @method('PATCH')
                    <button name="status" value="completed" class="h-9 px-3 rounded-full bg-sage-tint text-sage-ink text-xs font-extrabold">Selesai</button>
                    <button name="status" value="no_show" class="h-9 px-3 rounded-full bg-cream text-xs font-extrabold">Tidak hadir</button>
                    <button name="status" value="cancelled" data-confirm-title="Batalkan sesi?" data-confirm="Murid akan diberi tahu dan slot dibuka kembali." data-confirm-ok="Ya, batalkan" data-confirm-tone="danger" class="h-9 px-3 rounded-full bg-cream text-xs font-extrabold text-ink-soft">Batalkan</button>
                </form>
            @endif
            <a href="{{ route('counselor.conversations.show', $s->conversation) }}" class="text-xs font-extrabold text-sage-ink">Chat</a>
        </div>
    @empty
        <p class="py-3 text-sm text-ink-soft">Belum ada sesi. Undang murid lewat tombol <b>Undang sesi</b> di chat.</p>
    @endforelse
</section>
</div>
@endsection

@push('modals')
<x-modal name="slot" title="Buat slot konseling" max-width="max-w-[440px]" :show="$errors->hasAny(['date', 'start_time', 'duration', 'location', 'repeat_weeks'])">
    <p class="text-[13px] text-ink-soft -mt-2 mb-3">Murid hanya bisa memilih slot di undanganmu. Slot bentrok dilewati.</p>
    <form method="POST" action="{{ route('counselor.slots.store') }}" class="grid grid-cols-2 gap-2.5">
        @csrf
        <x-input name="date" type="date" label="Tanggal" :value="now($tz)->addDay()->toDateString()" class="col-span-2" required />
        <x-input name="start_time" type="time" label="Jam mulai" value="09:00" required />
        <div><label for="sl-dur" class="field-label">Durasi</label><select id="sl-dur" name="duration" class="field">@foreach([30, 45, 60] as $m)<option value="{{ $m }}" @selected(old('duration', 45) == $m)>{{ $m }} menit</option>@endforeach</select></div>
        <x-input name="location" label="Lokasi" value="Ruang BK" class="col-span-2" required />
        <div class="col-span-2"><label for="sl-rep" class="field-label">Ulangi setiap minggu</label><select id="sl-rep" name="repeat_weeks" class="field">@foreach(range(1, 8) as $w)<option value="{{ $w }}">{{ $w === 1 ? 'Tidak diulang' : 'Selama '.$w.' minggu' }}</option>@endforeach</select></div>
        <div class="col-span-2 flex justify-end gap-2 mt-1"><button type="button" x-on:click="$dispatch('close-modal', 'slot')" class="btn btn-outline btn-sm">Batal</button><button class="btn btn-sage btn-sm">Simpan slot</button></div>
    </form>
</x-modal>
@endpush
