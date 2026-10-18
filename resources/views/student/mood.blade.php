@extends('layouts.student', ['active' => 'mood'])
@section('title', 'Jurnal Mood')
@php $labels = \App\Models\MoodEntry::LABELS; $max = max(1, $counts->max()); @endphp
@section('content')
<span class="self-start -mt-3 lg:-mt-4 inline-flex items-center gap-2 px-3.5 py-[7px] rounded-full bg-cream-deep text-sm font-extrabold"><x-icon name="lock" size="15" />Hanya kamu yang bisa melihat isi jurnal ini.</span>

<div class="flex flex-wrap gap-5 items-stretch">
    {{-- Kalender ikut setinggi kolom kanan: baris tanggal membesar mengisi ruang, jadi tidak ada sisa kosong. --}}
    <section aria-labelledby="kal-h" class="flex-[3_1_560px] min-w-0 card !rounded-panel p-4 sm:p-[22px] flex flex-col gap-3.5">
        <div class="flex items-center justify-between">
            <h2 id="kal-h" class="text-[22px]">{{ $month->translatedFormat('F Y') }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('student.mood.index', ['bulan' => $prev]) }}" aria-label="Bulan sebelumnya" class="w-11 h-11 rounded-full bg-cream flex items-center justify-center text-ink"><x-icon name="chevron-left" size="18" /></a>
                @if($next)
                    <a href="{{ route('student.mood.index', ['bulan' => $next]) }}" aria-label="Bulan berikutnya" class="w-11 h-11 rounded-full bg-cream flex items-center justify-center text-ink"><x-icon name="chevron-right" size="18" /></a>
                @else
                    <span class="w-11 h-11 rounded-full bg-cream/60 flex items-center justify-center text-ink-soft/40" aria-hidden="true"><x-icon name="chevron-right" size="18" /></span>
                @endif
            </div>
        </div>
        <div class="grid grid-cols-7 gap-1 sm:gap-1.5 text-center text-xs sm:text-[13px] font-extrabold text-ink-soft"><span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span><span>Min</span></div>
        <div class="flex-1 grid grid-cols-7 auto-rows-fr gap-1 sm:gap-1.5">
            @foreach($calendar as $cell)
                @if(! $cell)
                    <div class="min-h-14 sm:min-h-[76px]"></div>
                @else
                    @php $e = $cell['entry']; $isToday = $cell['date']->isSameDay($today); @endphp
                    <div title="{{ $cell['day'].' '.$month->translatedFormat('M').($e ? ': '.$labels[$e->mood] : '') }}" class="min-h-14 sm:min-h-[76px] rounded-xl sm:rounded-2xl flex flex-col items-center justify-center gap-1 sm:gap-1.5 {{ $e ? 'text-ink' : 'text-[#9C9088]' }} {{ $isToday ? 'shadow-[inset_0_0_0_2px_#B5523F]' : '' }}" style="background: {{ $e ? $tints[$e->mood] : '#FDFBF6' }}">
                        <span class="text-xs sm:text-[13px] font-extrabold leading-none">{{ $cell['day'] }}</span>
                        @if($e)<x-mood-face :mood="$e->mood" size="24" class="sm:w-7 sm:h-7" /><span class="sr-only">{{ $labels[$e->mood] }}</span>@endif
                    </div>
                @endif
            @endforeach
        </div>
        <div class="flex flex-wrap gap-x-4 gap-y-1.5 text-xs font-bold text-ink-soft">
            @foreach($labels as $k => $l)<span class="inline-flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded" style="background: {{ $tints[$k] }}"></span>{{ $l }}</span>@endforeach
            <span class="inline-flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded shadow-[inset_0_0_0_2px_#B5523F]"></span>Hari ini</span>
        </div>
    </section>


    <div class="flex-[2_1_320px] min-w-0 flex flex-col gap-5">
        <section aria-labelledby="add-h" class="card !rounded-panel p-[22px] flex flex-col gap-3.5" x-data="{ mood: @js(old('mood', $todayEntry?->mood)) }">
            <h2 id="add-h" class="!font-sans text-[17px] font-extrabold">{{ $todayEntry ? 'Ubah catatan hari ini' : 'Catatan hari ini' }} · {{ $today->translatedFormat('l, j M') }}</h2>
            <form method="POST" action="{{ route('student.mood.upsert') }}" class="flex flex-col gap-3.5">
                @csrf @method('PUT')
                <div role="radiogroup" aria-label="Mood hari ini" class="grid grid-cols-5 gap-1.5">
                    @foreach($labels as $k => $l)
                        <label class="h-[72px] rounded-2xl flex flex-col items-center justify-center gap-1 text-xs cursor-pointer text-ink transition" :class="mood === '{{ $k }}' ? 'border-2 border-sand-deep bg-sand-tint font-extrabold' : 'border-[1.5px] border-line bg-white font-bold'">
                            <input type="radio" name="mood" value="{{ $k }}" x-model="mood" class="sr-only">
                            <x-mood-face :mood="$k" size="28" /><span>{{ $l }}</span>
                        </label>
                    @endforeach
                </div>
                @error('mood')<p class="field-error -mt-2">Pilih salah satu mood dulu, ya.</p>@enderror
                <label for="note-today" class="sr-only">Catatan singkat</label>
                <textarea id="note-today" name="note" rows="3" maxlength="500" placeholder="Apa yang paling terasa hari ini? (boleh satu kalimat)" class="field !bg-[#FDFBF6] !rounded-[14px]">{{ old('note', $todayEntry?->note) }}</textarea>
                @error('note')<p class="field-error -mt-2">{{ $message }}</p>@enderror
                <x-song-input id="song-today" :value="old('song_url', $todayEntry?->song['url'] ?? null)" label="Lagu hari ini (opsional)" />
                <button class="self-start btn btn-primary !h-[46px] !text-[15px]" :disabled="!mood">Simpan catatan</button>
            </form>
        </section>

        <section aria-labelledby="grafik-h" class="flex-1 card !rounded-panel p-[22px] flex flex-col gap-3">
            <div class="flex justify-between items-baseline"><h2 id="grafik-h" class="!font-sans text-[17px] font-extrabold">Mood bulan ini</h2><span class="text-[13px] text-ink-soft">{{ $checkins }} hari check-in</span></div>
            <div class="flex-1 flex flex-col justify-around gap-3">
            @foreach($labels as $k => $l)
                <div class="flex items-center gap-2.5" title="{{ $l }}: {{ $counts[$k] }} hari">
                    <span class="w-16 flex-none text-sm font-bold">{{ $l }}</span>
                    <span class="flex-1 h-3.5 rounded-full bg-cream overflow-hidden flex"><span class="rounded-full bg-sage-deep" style="width: {{ round($counts[$k] / $max * 100) }}%"></span></span>
                    <span class="w-[54px] flex-none text-right text-[13px] font-extrabold">{{ $counts[$k] }} hari</span>
                </div>
            @endforeach
            </div>
        </section>
    </div>
</div>

<section aria-labelledby="tren-h" class="card !rounded-panel p-[22px] flex flex-col gap-3" data-module="mood-chart">
    <div class="flex justify-between items-baseline flex-wrap gap-2"><h2 id="tren-h" class="!font-sans text-[17px] font-extrabold">Tren 30 hari terakhir</h2><span class="text-[13px] text-ink-soft">Garis naik = hari terasa lebih ringan. Hanya untuk melihat pola, bukan penilaian.</span></div>
    <div class="h-[220px]"><canvas id="mood-trend" role="img" aria-label="Grafik tren mood 30 hari"></canvas></div>
    <script type="application/json" id="mood-trend-data">{!! json_encode($trend) !!}</script>
</section>

<section aria-labelledby="catatan-h" class="flex flex-col gap-3" x-data="{ share: null }">
    <h2 id="catatan-h" class="!font-sans text-lg font-extrabold">Catatan harian</h2>
    @if($notes->isEmpty())
        <x-empty-state title="Belum ada catatan" text="Satu kalimat tentang harimu. Hanya untukmu." />
    @else
        <div class="grid grid-cols-[repeat(auto-fit,minmax(min(300px,100%),1fr))] gap-4">
            @foreach($notes as $n)
                <article class="bg-white rounded-card-l p-[18px] flex flex-col gap-3 shadow-soft">
                    <div class="flex items-center gap-3">
                        <span class="w-11 h-11 flex-none rounded-[14px] flex items-center justify-center" style="background: {{ $tints[$n->mood] }}"><x-mood-face :mood="$n->mood" size="28" /></span>
                        <span class="flex flex-col"><span class="font-extrabold text-[15px]">{{ $n->entry_date->translatedFormat('l, j M') }}</span><span class="text-[13px] text-ink-soft">{{ $labels[$n->mood] }}</span></span>
                    </div>
                    @if(filled($n->note))<p class="text-[15px] leading-relaxed">{{ $n->note }}</p>@endif
                    @if($n->song)<x-song-card :song="$n->song" caption="lagu hari itu" />@endif
                    <span class="flex-1"></span>
                    @if($n->shared_at)
                        <span class="self-start inline-flex items-center gap-1.5 px-3 py-2 rounded-full bg-sage-tint text-sage-ink text-[13px] font-extrabold"><x-icon name="check" size="14" />Terkirim ke {{ $n->sharedConversation?->counselor?->displayName() ?? 'Guru BK' }}</span>
                    @else
                        <button type="button" x-on:click="share = {{ $n->id }}" class="self-start inline-flex items-center gap-1.5 h-11 px-4 rounded-full border-[1.5px] border-sage-deep text-sage-ink2 text-sm font-extrabold"><x-icon name="send" size="16" />Kirim ke Guru BK</button>
                    @endif
                </article>
            @endforeach
        </div>
    @endif

    <div x-show="share" x-cloak class="scrim fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:p-6">
        <section role="dialog" aria-modal="true" aria-labelledby="share-h" x-on:click.outside="share = null" x-trap.noscroll="share" class="w-full max-w-lg bg-cream rounded-t-[28px] sm:rounded-panel shadow-modal animate-pop p-6 sm:p-8">
            <h2 id="share-h" class="text-2xl mb-1.5">Kirim catatan ke Guru BK</h2>
            <p class="text-sm text-ink-soft mb-4">Mood dan catatan hari itu dikirim sebagai kartu ke percakapan yang kamu pilih.</p>
            <form method="POST" :action="'{{ url('murid/mood') }}/' + share + '/bagikan'" class="flex flex-col gap-2.5">
                @csrf
                @foreach($conversations as $c)
                    <label class="flex items-center gap-3 p-3.5 rounded-card bg-white border-[1.5px] border-line cursor-pointer has-[:checked]:border-terracotta-deep has-[:checked]:bg-terracotta-tint2">
                        <input type="radio" name="target" value="{{ $c->uuid }}" class="w-5 h-5 accent-terracotta-deep" required>
                        <x-counselor-avatar :counselor="$c->counselor" :size="40" :radius="14" />
                        <span class="flex flex-col"><span class="font-extrabold text-[15px]">{{ $c->counselor->displayName() }}</span><span class="text-xs text-ink-soft">{{ $c->is_anonymous && ! $c->student_id ? 'Kamu anonim sebagai '.$c->anonymous_alias : 'Identitasmu terlihat' }}</span></span>
                    </label>
                @endforeach
                <label class="flex items-center gap-3 p-3.5 rounded-card bg-white border-[1.5px] border-dashed border-sand-deep cursor-pointer has-[:checked]:bg-sand-tint">
                    <input type="radio" name="target" value="new" class="w-5 h-5 accent-sand-deep" required @checked($conversations->isEmpty())>
                    <span class="w-10 h-10 rounded-[14px] bg-sand-tint flex items-center justify-center text-sand-ink"><x-icon name="plus" size="18" /></span>
                    <span class="font-extrabold text-[15px]">Mulai cerita baru</span>
                </label>
                <div class="flex justify-end gap-2 mt-3"><button type="button" class="btn btn-outline btn-sm" x-on:click="share = null">Batal</button><button class="btn btn-primary btn-sm">Kirim</button></div>
            </form>
        </section>
    </div>
</section>
@endsection
