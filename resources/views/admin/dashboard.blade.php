@extends('layouts.admin', ['active' => 'dashboard'])
@section('title', 'Dashboard Admin')
@section('subtitle', $school->name.' · '.now()->timezone($school->timezone)->translatedFormat('l, j F Y'))
@section('content')
<div class="grid grid-cols-[repeat(auto-fit,minmax(min(210px,100%),1fr))] gap-4">
    <div class="card !rounded-card-l p-5 flex flex-col gap-2"><span class="text-sm font-bold text-ink-soft">Jumlah murid</span><span class="num text-[40px] leading-none">{{ number_format($stats['students'], 0, ',', '.') }}</span><span class="text-[13px] text-ink-soft">Kelas {{ $stats['grades']->min() }}-{{ $stats['grades']->max() }}, {{ $stats['classes'] }} rombel</span></div>
    <div class="card !rounded-card-l p-5 flex flex-col gap-2"><span class="text-sm font-bold text-ink-soft">Guru BK</span><span class="num text-[40px] leading-none">{{ $stats['counselors'] }}</span><span class="text-[13px] text-ink-soft">{{ $stats['counselors_active'] === $stats['counselors'] ? 'Semua aktif' : $stats['counselors_active'].' aktif' }}</span></div>
    <div class="card !rounded-card-l p-5 flex flex-col gap-2"><span class="text-sm font-bold text-ink-soft">Akun aktif</span><span class="num text-[40px] leading-none">{{ number_format($stats['active'], 0, ',', '.') }}</span><span class="text-[13px] text-ink-soft">{{ $stats['active_pct'] }}% dari semua akun</span></div>
    <a href="{{ route('admin.accounts') }}" class="card !rounded-card-l p-5 flex flex-col gap-2 no-underline text-ink border-[1.5px] border-[#E6CFA8]"><span class="text-sm font-bold text-ink-soft">Belum ganti password</span><span class="num text-[40px] leading-none text-sand-ink">{{ $stats['pending'] }}</span><span class="text-[13px] font-extrabold text-sand-ink">Lihat daftar akun</span></a>
</div>

<div class="grid grid-cols-[repeat(auto-fit,minmax(min(440px,100%),1fr))] gap-4">
    @foreach($charts as $ch)
        @php $max = max(1, max(array_column($ch['bars'], 'value'))); @endphp
        <section class="card !rounded-panel p-[22px] flex flex-col gap-3.5 min-w-0" aria-labelledby="ch-{{ $loop->index }}">
            <div><h2 id="ch-{{ $loop->index }}" class="text-[21px]">{{ $ch['title'] }}</h2><span class="text-[13px] text-ink-soft">{{ $ch['sub'] }}</span></div>
            <div class="h-[220px] flex items-end gap-3.5 px-1 border-b border-line" role="img" aria-label="{{ $ch['title'] }}: {{ collect($ch['bars'])->map(fn ($b) => $b['full'].' '.$b['value'])->implode(', ') }}">
                @foreach($ch['bars'] as $b)
                    <div class="flex-1 h-full flex flex-col justify-end items-center gap-1.5" title="{{ $b['full'] }}: {{ number_format($b['value'], 0, ',', '.') }} {{ $ch['unit'] }}">
                        <span class="text-xs font-extrabold">{{ number_format($b['value'], 0, ',', '.') }}</span>
                        <span class="w-full max-w-12 rounded-t-md" style="height: {{ round($b['value'] / $max * 170) }}px; background: {{ $ch['color'] }}"></span>
                    </div>
                @endforeach
            </div>
            <div class="flex gap-3.5 px-1">@foreach($ch['bars'] as $b)<span class="flex-1 text-center text-xs text-ink-soft">{{ $b['month'] }}</span>@endforeach</div>
        </section>
    @endforeach
</div>

<div class="flex gap-3 items-center px-[18px] py-4 rounded-card bg-sand-tint text-sm leading-normal text-sand-ink2">
    <x-icon name="lock" size="20" />
    <span>Admin hanya melihat data agregat. Percakapan, jurnal mood, dan identitas murid tidak bisa dibuka.</span>
</div>
@endsection
