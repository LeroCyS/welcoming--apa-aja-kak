@extends('layouts.student', ['active' => 'jadwal'])
@section('title', 'Jadwal Saya')
@section('content')
{{-- Tab di atas, lalu dua kolom yang sama tinggi: daftar sesi & panel info. --}}
<div class="flex flex-col gap-5" x-data="reschedule()">
        <div role="tablist" aria-label="Jadwal" class="seg self-start">
            <a role="tab" aria-selected="{{ $tab === 'mendatang' ? 'true' : 'false' }}" href="{{ route('student.schedule.index') }}" class="seg-item no-underline !h-11 !flex items-center !text-[15px] {{ $tab === 'mendatang' ? 'seg-item-on' : '' }}">Mendatang ({{ $upcoming->count() }})</a>
            <a role="tab" aria-selected="{{ $tab === 'riwayat' ? 'true' : 'false' }}" href="{{ route('student.schedule.index', ['tab' => 'riwayat']) }}" class="seg-item no-underline !h-11 !flex items-center !text-[15px] {{ $tab === 'riwayat' ? 'seg-item-on' : '' }}">Riwayat ({{ $history->count() }})</a>
        </div>
    <div class="grid grid-cols-[minmax(0,1fr)] lg:grid-cols-[minmax(0,1fr)_340px] gap-5 items-stretch">
    <div class="min-w-0 flex flex-col gap-4">
        @if($tab === 'mendatang')
            @forelse($upcoming as $s)
                @php $start = $s->slot->start_at->timezone($tz); $canChange = $s->reschedule_count < $max && $s->slot->start_at->gt(now()->addHours(2)); @endphp
                <article class="bg-white border border-line-soft rounded-panel p-[22px] flex gap-5 items-center flex-wrap shadow-soft">
                    <div class="w-[84px] h-[92px] flex-none rounded-[22px] bg-terracotta-tint flex flex-col items-center justify-center text-terracotta-ink"><span class="text-[13px] font-extrabold uppercase">{{ $start->translatedFormat('l') }}</span><span class="font-display text-4xl leading-none">{{ $start->format('j') }}</span><span class="text-xs font-extrabold uppercase">{{ $start->translatedFormat('M') }}</span></div>
                    <div class="flex-1 min-w-[220px] flex flex-col gap-1.5">
                        <span class="self-start inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-sage-tint text-sage-ink text-xs font-extrabold"><x-icon name="check" size="13" />Terjadwal</span>
                        <span class="font-extrabold text-[19px]">{{ $start->format('H.i') }}-{{ $s->slot->end_at->timezone($tz)->format('H.i') }} · dengan {{ $s->counselor->displayName() }}</span>
                        <span class="text-sm text-ink-soft">{{ $s->slot->location }}@if($s->reschedule_count) · sudah diubah {{ $s->reschedule_count }}× @endif</span>
                    </div>
                    <div class="flex gap-2 flex-wrap">
                        @if($canChange)
                            <button type="button" x-on:click="open({{ $s->id }}, '{{ route('student.schedule.slots', $s) }}', '{{ route('student.schedule.reschedule', $s) }}')" class="btn btn-outline btn-sm !text-sm">Ubah Jadwal</button>
                        @else
                            <span class="text-xs font-bold text-ink-soft max-w-[160px]">{{ $s->reschedule_count >= $max ? 'Batas ubah jadwal (2×) tercapai' : 'Sudah < 2 jam sebelum sesi' }}</span>
                        @endif
                    </div>
                </article>
            @empty
                <div class="card !rounded-panel flex-1 flex items-center justify-center"><x-empty-state title="Belum ada jadwal ngobrol langsung" text="Undangan bertemu dari Guru BK muncul di chat." :cta-href="route('student.conversations.index')" cta-label="Buka chat Guru BK" /></div>
            @endforelse
        @else
            <div class="flex-1 bg-white border border-line-soft rounded-panel px-5 py-2 shadow-soft">
                @forelse($history as $s)
                    @php $start = $s->slot->start_at->timezone($tz); @endphp
                    <div class="flex justify-between items-center gap-3 py-4 border-b border-line-soft last:border-b-0">
                        <span class="flex flex-col gap-0.5"><span class="font-extrabold text-base">{{ $start->translatedFormat('l, j M Y') }} · {{ $start->format('H.i') }}-{{ $s->slot->end_at->timezone($tz)->format('H.i') }}</span><span class="text-sm text-ink-soft">{{ $s->counselor->name }} · {{ $s->slot->location }}</span></span>
                        <span class="text-[13px] font-extrabold {{ $s->status === 'completed' ? 'text-sage-ink' : 'text-ink-soft' }}">{{ $s->status === 'scheduled' ? 'Sudah lewat' : $s->statusLabel() }}</span>
                    </div>
                @empty
                    <x-empty-state title="Belum ada riwayat sesi" text="Sesi yang sudah lewat akan tercatat di sini." />
                @endforelse
            </div>
        @endif
    </div>

    <div class="flex flex-col gap-4">
        <div class="bg-sage-tint border border-sage/25 rounded-panel p-5 flex flex-col gap-2.5 text-sage-ink">
            <span class="inline-flex items-center gap-2 font-extrabold text-base"><x-icon name="lock" size="20" />Tetap rahasia</span>
            <span class="text-sm leading-[1.55]">Jadwal ini hanya terlihat olehmu dan Guru BK yang mengundangmu.</span>
        </div>
        <div class="flex-1 bg-white border border-line-soft rounded-panel p-5 flex flex-col gap-3 shadow-soft">
            <span class="font-extrabold text-base">Mau ngobrol langsung?</span>
            <span class="text-sm leading-[1.55] text-ink-soft">Minta sesi tatap muka lewat chat dengan Guru BK, atau terima undangan yang masuk.</span>
            <a href="{{ route('student.conversations.index') }}" class="mt-auto btn btn-outline btn-sm !text-sm !font-extrabold">Buka chat Guru BK</a>
        </div>
    </div>
    </div>

    {{-- Modal Ubah Jadwal --}}
    <div x-show="isOpen" x-cloak class="scrim fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:p-6">
        <section role="dialog" aria-modal="true" aria-labelledby="rs-h" x-on:click.outside="isOpen = false" x-trap.noscroll="isOpen" class="w-full max-w-lg bg-cream rounded-t-[28px] sm:rounded-panel shadow-modal animate-pop p-6 sm:p-8 max-h-[90vh] overflow-y-auto">
            <h2 id="rs-h" class="text-2xl mb-1.5">Ubah jadwal</h2>
            <p class="text-sm text-ink-soft mb-4">Maksimal 2 kali, paling lambat 2 jam sebelum mulai.</p>
            <template x-if="loading"><x-skeleton :rows="2" /></template>
            <form method="POST" :action="action" x-show="!loading" class="flex flex-col gap-2">
                @csrf
                <template x-for="s in slots" :key="s.id">
                    <label class="flex items-center gap-3 px-3.5 py-3 rounded-2xl bg-white border-[1.5px] border-line cursor-pointer has-[:checked]:border-terracotta-deep has-[:checked]:bg-terracotta-tint2"><input type="radio" name="slot_id" :value="s.id" class="w-5 h-5 accent-terracotta-deep" required><span class="text-sm font-bold" x-text="s.label"></span></label>
                </template>
                <p x-show="!slots.length" class="text-sm text-ink-soft py-4">Belum ada slot lain yang tersedia. Coba tanyakan lewat chat, ya.</p>
                @error('slot_id')<p class="field-error">{{ $message }}</p>@enderror
                <div class="flex justify-end gap-2 mt-3"><button type="button" class="btn btn-outline btn-sm" x-on:click="isOpen = false">Batal</button><button class="btn btn-primary btn-sm" :disabled="!slots.length">Simpan jadwal baru</button></div>
            </form>
        </section>
    </div>
</div>
@endsection
@push('scripts')
<script>
    function reschedule() {
        return {
            isOpen: false, loading: false, slots: [], action: '',
            async open(id, slotsUrl, action) {
                this.isOpen = true; this.loading = true; this.action = action;
                try { this.slots = (await window.axios.get(slotsUrl)).data; } finally { this.loading = false; }
            },
        };
    }
</script>
@endpush
