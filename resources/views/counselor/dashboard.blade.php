@extends('layouts.counselor', ['active' => 'dashboard'])
@section('title', 'Dashboard Analitik')
@section('subtitle', $greeting.', '.auth()->user()->displayName().' · '.$today)
@php
    $hatch = 'background: repeating-linear-gradient(45deg, #EFE9DA 0 3px, #F8F5EC 3px 6px);';
    $ramp = ['#FBEDE9', '#F2CBC1', '#E3A08F', '#CB7563', '#A9483A'];
    $c = $data['cards'];
    $diff = $c['new'] - $c['new_prev'];
@endphp
@section('content')
<div class="flex items-center justify-between gap-3 flex-wrap">
    <div role="tablist" aria-label="Periode" class="seg">
        @foreach(\App\Services\AnalyticsService::PERIODS as $k => $l)
            <a role="tab" aria-selected="{{ $data['period'] === $k ? 'true' : 'false' }}" href="{{ route('counselor.dashboard', ['periode' => $k]) }}" class="seg-item no-underline {{ $data['period'] === $k ? 'seg-item-on' : '' }}">{{ $l }}</a>
        @endforeach
    </div>
    <span class="inline-flex items-center gap-1.5 text-xs font-bold text-ink-soft" title="Data di bawah {{ $data['min'] }} disembunyikan untuk menjaga anonimitas."><x-icon name="lock" size="13" />Data &lt; {{ $data['min'] }} disembunyikan demi anonimitas</span>
</div>

{{-- Angka utama dalam satu strip --}}
<div class="box grid grid-cols-2 md:grid-cols-4 [&>*]:border-line-soft max-md:[&>*:nth-child(odd)]:border-r max-md:[&>*:nth-child(-n+2)]:border-b md:[&>*:not(:last-child)]:border-r">
    <div class="px-5 py-4 flex flex-col gap-1"><span class="text-xs font-bold text-ink-soft">Kasus aktif</span><span class="num text-[30px] leading-none">{{ $c['active'] }}</span><span class="text-xs text-ink-soft">dari {{ $c['counselors'] }} Guru BK</span></div>
    <div class="px-5 py-4 flex flex-col gap-1"><span class="text-xs font-bold text-ink-soft">Kasus baru · {{ mb_strtolower(\App\Services\AnalyticsService::PERIODS[$data['period']]) }}</span><span class="num text-[30px] leading-none">{{ $c['new'] }}</span><span class="text-xs text-ink-soft">{{ $diff === 0 ? 'Sama dengan '.$data['prev_label'] : abs($diff).' '.($diff > 0 ? 'lebih banyak' : 'lebih sedikit').' dari '.$data['prev_label'] }}</span></div>
    <div class="px-5 py-4 flex flex-col gap-1"><span class="text-xs font-bold text-ink-soft">Rata-rata respons pertama</span><span class="num text-[30px] leading-none">{{ $c['avg_response'] }}</span><span class="text-xs text-ink-soft">{{ $data['range'] }}</span></div>
    <div class="px-5 py-4 flex flex-col gap-1"><span class="text-xs font-bold text-ink-soft">Sesi tatap muka</span><span class="num text-[30px] leading-none">{{ $c['sessions'] }}</span><span class="text-xs text-ink-soft">pada periode ini</span></div>
</div>

<div class="grid grid-cols-[minmax(0,1fr)] lg:grid-cols-[minmax(0,1fr)_340px] gap-5 items-stretch">
{{-- Satu kartu analitik: tab di header kartu, isi grafik di bawahnya. --}}
<div class="box min-w-0 flex flex-col overflow-hidden" x-data="{ tab: 'tren' }">
    {{-- Analitik dalam tab, supaya tidak menumpuk --}}
    <div role="tablist" aria-label="Analitik" class="flex gap-1 overflow-x-auto px-3 pt-3 pb-2 border-b border-line-soft [scrollbar-width:none]">
        @foreach(['tren' => 'Tren cerita', 'topik' => 'Topik terbanyak', 'angkatan' => 'Per angkatan', 'mood' => 'Mood sekolah'] as $k => $l)
            <button type="button" role="tab" x-on:click="tab = '{{ $k }}'" :aria-selected="(tab === '{{ $k }}').toString()" class="h-10 px-4 rounded-full text-sm font-bold whitespace-nowrap transition-colors" :class="tab === '{{ $k }}' ? 'bg-ink text-cream' : 'text-ink-soft hover:bg-ink/5'">{{ $l }}</button>
        @endforeach
    </div>
    <section aria-labelledby="h-tren" x-show="tab === 'tren'" role="tabpanel" class="flex-1 p-5 flex flex-col gap-4 min-w-0" data-module="dashboard-charts">
        <div><h2 id="h-tren" class="text-lg">Tren jumlah cerita per bulan</h2><span class="text-xs text-ink-soft">Percakapan baru, {{ $data['trend']->first()['full'] }} - {{ $data['trend']->last()['full'] }}</span></div>
        <div class="h-[280px]"><canvas id="trend-chart" role="img" aria-label="Grafik garis jumlah cerita: {{ $data['trend']->map(fn ($t) => $t['full'].' '.$t['value'])->implode(', ') }}"></canvas></div>
        <script type="application/json" id="trend-data">{!! json_encode($data['trend']) !!}</script>
    </section>

    <section aria-labelledby="h-topik" x-show="tab === 'topik'" role="tabpanel" class="flex-1 p-5 flex flex-col gap-4 min-w-0">
        <div><h2 id="h-topik" class="text-lg">Topik terbanyak</h2><span class="text-xs text-ink-soft">{{ $data['range'] }} · satu cerita bisa punya beberapa topik</span></div>
        <div class="flex flex-col gap-2.5">
            @foreach($data['topicBars'] as $b)
                <div class="flex items-center gap-3" title="{{ $b['name'] }}: {{ $b['value'] ?? 'kurang dari '.$data['min'].' (disembunyikan)' }}">
                    <span class="w-[130px] flex-none text-sm font-bold">{{ $b['name'] }}</span>
                    <span class="flex-1 h-5 flex">@if($b['value'])<span class="rounded-r bg-terracotta-deep" style="width: {{ max(4, $b['pct']) }}%"></span>@else<span class="w-[18%] rounded" style="{{ $hatch }}"></span>@endif</span>
                    <span class="w-10 flex-none text-right text-sm font-extrabold">{{ $b['value'] ?? '<'.$data['min'] }}</span>
                </div>
            @endforeach
        </div>
    </section>

    <section aria-labelledby="h-angkatan" x-show="tab === 'angkatan'" role="tabpanel" class="flex-1 p-5 flex flex-col gap-4 min-w-0">
        <div><h2 id="h-angkatan" class="text-lg">Topik per angkatan</h2><span class="text-xs text-ink-soft">{{ $data['range'] }} · jumlah percakapan</span></div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[380px] text-[13px]" style="border-collapse: separate; border-spacing: 3px">
                <thead><tr><th scope="col" class="text-left font-bold text-ink-soft pb-1.5">Topik</th>@foreach($data['grades'] as $g)<th scope="col" class="font-bold text-ink-soft">Kelas {{ $g }}</th>@endforeach</tr></thead>
                <tbody>
                    @foreach($data['heat'] as $r)
                        <tr>
                            <th scope="row" class="text-left font-bold pr-2 whitespace-nowrap">{{ $r['topic'] }}</th>
                            @foreach($r['cells'] as $cell)
                                @if($cell['value'] === null)
                                    <td title="{{ $r['topic'] }}, Kelas {{ $cell['grade'] }}: disembunyikan" class="h-[34px] min-w-16 rounded-md text-center font-bold text-xs text-ink-soft" style="{{ $hatch }}">&lt;{{ $data['min'] }}</td>
                                @else
                                    @php $i = min(4, (int) floor(($cell['value'] / max(1, $data['heatMax'])) * 4.999)); @endphp
                                    <td title="{{ $r['topic'] }}, Kelas {{ $cell['grade'] }}: {{ $cell['value'] }} percakapan" class="h-[34px] min-w-16 rounded-md text-center font-bold" style="background: {{ $ramp[$i] }}; color: {{ $i >= 3 ? '#FFFFFF' : '#3E3530' }}">{{ $cell['value'] }}</td>
                                @endif
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="flex items-center gap-2 text-xs text-ink-soft flex-wrap">
            <span>Sedikit</span><span class="flex gap-0.5">@foreach($ramp as $r)<span class="w-5 h-3 rounded-[3px]" style="background: {{ $r }}"></span>@endforeach</span><span>Banyak</span>
            <span class="ml-2 inline-flex items-center gap-1.5"><span class="w-5 h-3 rounded-[3px]" style="{{ $hatch }}"></span>&lt; {{ $data['min'] }} disembunyikan</span>
        </div>
    </section>

    <section aria-labelledby="h-mood" x-show="tab === 'mood'" role="tabpanel" class="flex-1 p-5 flex flex-col gap-4 min-w-0">
        <div><h2 id="h-mood" class="text-lg">Distribusi mood sekolah</h2><span class="text-xs text-ink-soft">Check-in harian anonim · {{ $data['range'] }} · hanya agregat</span></div>
        <div class="flex flex-wrap gap-x-3.5 gap-y-1.5 text-xs font-bold">
            @foreach(\App\Models\MoodEntry::LABELS as $k => $l)<span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-[3px]" style="background: {{ \App\Models\MoodEntry::COLORS[$k] }}; {{ $k === 'neutral' ? 'box-shadow: inset 0 0 0 1px #CFC6B3' : '' }}"></span>{{ $l }}</span>@endforeach
        </div>
        @foreach(array_merge([['label' => 'Semua angkatan'] + $data['moodAll']], $data['mood']->all()) as $row)
            <div class="flex flex-col gap-1.5">
                <div class="flex justify-between text-[13px]"><span class="font-bold">{{ $row['label'] }}</span><span class="text-ink-soft">{{ $row['visible'] ? $row['total'].' check-in' : 'kurang dari '.$data['min'].' check-in' }}</span></div>
                @if($row['visible'])
                    <div class="flex gap-0.5 h-7">
                        @foreach($row['segments'] as $s)
                            @continue($s['value'] === null && ($s['raw_zero'] ?? false))
                            @if($s['value'] === null)
                                <div title="{{ $s['label'] }}: < {{ $data['min'] }}" class="w-8 flex-none rounded text-[10px] font-bold text-ink-soft flex items-center justify-center" style="{{ $hatch }}">&lt;{{ $data['min'] }}</div>
                            @else
                                <div title="{{ $s['label'] }}: {{ $s['value'] }} ({{ $s['pct'] }}%)" class="rounded text-[11px] font-extrabold flex items-center justify-center overflow-hidden" style="flex: {{ $s['pct'] }} 1 0; background: {{ $s['color'] }}; color: {{ in_array($s['mood'], ['happy', 'sad', 'angry']) ? '#fff' : '#3E3530' }}">{{ $s['pct'] >= 8 ? $s['pct'].'%' : '' }}</div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="h-7 rounded flex items-center justify-center text-xs font-bold text-ink-soft" style="{{ $hatch }}">Disembunyikan untuk menjaga anonimitas</div>
                @endif
            </div>
        @endforeach
        <p class="text-xs text-ink-soft">Hanya data agregat. Jurnal mood individu tidak terlihat.</p>
    </section>
</div>

<aside class="flex flex-col gap-5 min-w-0">
    <section aria-labelledby="h-tindakan" class="flex-1 box p-2 flex flex-col">
        <h2 id="h-tindakan" class="px-3 pt-2.5 pb-2 !font-sans text-[15px] font-extrabold">Perlu tindakan</h2>
        <a href="{{ route('counselor.inbox', ['urgency' => 'urgent']) }}" class="flex items-center gap-3 px-3 py-3 rounded-2xl no-underline text-ink hover:bg-danger-tint/50 transition-colors">
            <span class="w-10 h-10 flex-none rounded-xl bg-danger-tint text-danger flex items-center justify-center"><x-icon name="alert" size="18" /></span>
            <span class="flex-1 flex flex-col"><span class="text-sm font-extrabold">Urgensi tinggi / kritis</span><span class="text-xs text-ink-soft">Buka di Inbox</span></span>
            <span class="num text-2xl text-terracotta-ink">{{ $c['urgent'] }}</span>
        </a>
        <a href="{{ route('counselor.inbox', ['urgency' => \App\Models\Conversation::UNASSESSED]) }}" class="flex items-center gap-3 px-3 py-3 rounded-2xl no-underline text-ink hover:bg-ink/5 transition-colors">
            <span class="w-10 h-10 flex-none rounded-xl bg-cream-light border border-dashed border-toggle-off text-ink-soft flex items-center justify-center"><x-icon name="info" size="18" /></span>
            <span class="flex-1 flex flex-col"><span class="text-sm font-extrabold">Kasusmu belum dinilai urgensinya</span><span class="text-xs text-ink-soft">Nilai di Inbox</span></span>
            <span class="num text-2xl">{{ $myUnassessed }}</span>
        </a>
        {{-- Pintasan ke halaman yang sering dibuka, mengisi sisa tinggi kartu --}}
        <div class="mt-auto pt-2 mx-1 border-t border-line-soft flex flex-col">
            <span class="px-2 pt-2.5 pb-1 text-[11px] font-extrabold tracking-[0.08em] uppercase text-ink-soft">Pintasan</span>
            @foreach([['counselor.cases', 'kanban', 'Manajemen kasus'], ['counselor.schedule', 'calendar', 'Jadwal konseling'], ['counselor.selfhelp.create', 'edit', 'Tulis artikel']] as [$route, $icon, $label])
                <a href="{{ route($route) }}" class="group flex items-center gap-3 px-2 py-2.5 rounded-xl no-underline text-ink text-sm font-bold hover:bg-cream-light transition-colors"><x-icon :name="$icon" size="17" class="text-ink-soft" /><span class="flex-1">{{ $label }}</span><x-icon name="chevron-right" size="15" class="text-ink-soft transition-transform group-hover:translate-x-0.5" /></a>
            @endforeach
        </div>
    </section>
    @if($data['insights'])
        <details class="box bg-sand-tint/60 border-sand-tint group">
            <summary class="flex items-center gap-2.5 px-4 py-3 cursor-pointer list-none">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="text-sand-ink"><path d="M9 18h6M10 21h4M12 3a6 6 0 0 0-3.5 10.9c.6.5 1 1.2 1 2.1h5c0-.9.4-1.6 1-2.1A6 6 0 0 0 12 3z"/></svg>
                <span class="flex-1 text-[13px] font-extrabold text-sand-ink2">{{ count($data['insights']) }} insight {{ mb_strtolower(\App\Services\AnalyticsService::PERIODS[$data['period']]) }}</span>
                <x-icon name="chevron-down" size="14" class="transition group-open:rotate-180 text-sand-ink" />
            </summary>
            <ul class="px-4 pb-3 flex flex-col">
                @foreach($data['insights'] as $ins)
                    <li class="flex flex-col items-start gap-2 py-3 border-t border-sand-tint">
                        <span class="text-sm leading-[1.5]">{{ $ins['text'] }} @if($ins['program'])Pertimbangkan: <b>{{ $ins['program'] }}</b>.@endif</span>
                        <a href="{{ route('counselor.selfhelp.create') }}" class="tool-btn no-underline">Tulis artikel terkait</a>
                    </li>
                @endforeach
            </ul>
        </details>
    @endif
</aside>
</div>
@endsection
