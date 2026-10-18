{{-- Tab Ringkasan AI --}}
@php
    $newCount = $summary && $summary->status === 'done' && $summary->last_message_id
        ? $c->messages()->reorder()->where('id', '>', $summary->last_message_id)->where('sender_type', '!=', 'system')->count() : 0;
    $levelTone = ['rendah' => 'bg-sage-tint text-sage-ink', 'sedang' => 'bg-sand-tint text-sand-ink', 'tinggi' => 'bg-danger-tint text-danger-ink'];
@endphp
<div class="flex flex-col gap-3" @if($summary && $summary->status === 'pending') data-summary-pending="{{ route('counselor.summaries.show', $summary) }}" @endif>
    <span class="flex items-start gap-2 px-3 py-2.5 rounded-2xl bg-sand-tint text-sand-ink2 text-xs font-extrabold leading-snug"><x-icon name="sparkle" size="15" class="flex-none mt-px" />Ringkasan dibuat AI. Periksa kembali sebelum mengambil keputusan.</span>

    @if(! $summary)
        <p class="px-3 py-3 rounded-2xl border border-dashed border-sage/50 bg-sage-tint/30 text-sm text-sage-ink2 leading-relaxed">Ringkas inti masalah, emosi, dan langkah berikutnya. Data pribadi disamarkan dulu.</p>
    @elseif($summary->status === 'pending')
        <div class="flex flex-col gap-2.5" role="status">
            <span class="text-sm font-bold flex items-center gap-2"><svg width="20" height="20" viewBox="0 0 40 40" fill="none" stroke-width="3" stroke-linecap="round" class="animate-spin"><circle cx="20" cy="20" r="15" stroke="#E6DFCC"/><path d="M20 5a15 15 0 0 1 15 15" stroke="#5C7350"/></svg>Sedang meringkas percakapan…</span>
            <span class="skeleton h-3 w-11/12"></span><span class="skeleton h-3 w-4/5"></span><span class="skeleton h-3 w-3/5"></span><span class="skeleton h-3 w-10/12"></span>
        </div>
    @elseif($summary->status === 'failed')
        <p class="p-3 rounded-2xl bg-terracotta-tint text-terracotta-ink text-sm font-bold">{{ $summary->error ?: 'Ringkasan belum bisa dibuat saat ini. Coba lagi nanti.' }}</p>
    @else
        @php $s = $summary->content; @endphp
        @if($newCount > 0)
            <div class="flex items-center justify-between gap-2 p-3 rounded-2xl bg-sand-tint text-sand-ink2 text-sm font-bold">
                <span>{{ $newCount }} pesan baru sejak ringkasan ini</span>
                <form method="POST" action="{{ route('counselor.summaries.store', $c) }}">@csrf<button class="text-sm font-extrabold underline">Perbarui</button></form>
            </div>
        @endif
        <div class="flex flex-col gap-1"><span class="inline-flex items-center gap-1.5 text-xs font-extrabold uppercase tracking-[0.06em] text-ink"><span class="w-2 h-2 rounded-full bg-sage-deep"></span>Ringkasan</span><p class="text-sm leading-relaxed">{{ $s['ringkasan'] }}</p></div>
        @if($s['inti_masalah'])<div class="flex flex-col gap-1"><span class="inline-flex items-center gap-1.5 text-xs font-extrabold uppercase tracking-[0.06em] text-ink"><span class="w-2 h-2 rounded-full bg-terracotta"></span>Inti Masalah</span><ul class="list-disc pl-5 text-sm leading-relaxed">@foreach($s['inti_masalah'] as $i)<li>{{ $i }}</li>@endforeach</ul></div>@endif
        <div class="flex flex-col gap-1.5"><span class="inline-flex items-center gap-1.5 text-xs font-extrabold uppercase tracking-[0.06em] text-ink"><span class="w-2 h-2 rounded-full bg-sand"></span>Emosi yang Terlihat</span><span class="flex gap-1.5 flex-wrap">@forelse($s['emosi_terdeteksi'] as $e)<span class="px-2.5 py-1 rounded-full bg-terracotta-tint text-terracotta-ink text-xs font-extrabold whitespace-nowrap">{{ $e }}</span>@empty<span class="text-sm text-ink-soft">-</span>@endforelse</span></div>
        @if($s['tindakan_sudah_dilakukan'])<div class="flex flex-col gap-1"><span class="inline-flex items-center gap-1.5 text-xs font-extrabold uppercase tracking-[0.06em] text-ink"><span class="w-2 h-2 rounded-full bg-sage"></span>Yang Sudah Dilakukan</span><ul class="list-disc pl-5 text-sm leading-relaxed">@foreach($s['tindakan_sudah_dilakukan'] as $i)<li>{{ $i }}</li>@endforeach</ul></div>@endif
        <div class="flex flex-col gap-1"><span class="inline-flex items-center gap-1.5 text-xs font-extrabold uppercase tracking-[0.06em] text-ink"><span class="w-2 h-2 rounded-full bg-sage-deep"></span>Saran Langkah Berikutnya</span><ul class="list-disc pl-5 text-sm leading-relaxed">@foreach($s['saran_langkah_berikutnya'] as $i)<li>{{ $i }}</li>@endforeach</ul></div>
        <div class="flex flex-col gap-1.5"><span class="inline-flex items-center gap-1.5 text-xs font-extrabold uppercase tracking-[0.06em] text-ink"><span class="w-2 h-2 rounded-full bg-danger"></span>Tingkat Risiko</span><span class="self-start px-3 py-1 rounded-full text-sm font-extrabold capitalize {{ $levelTone[$s['tingkat_risiko']['level']] ?? '' }} whitespace-nowrap">{{ $s['tingkat_risiko']['level'] }}</span><span class="text-sm text-ink-soft leading-relaxed">{{ $s['tingkat_risiko']['alasan'] }}</span></div>
        <span class="text-xs text-ink-soft">Dibuat {{ $summary->created_at->diffForHumans() }} · {{ $summary->message_count }} pesan · driver {{ $summary->driver }}</span>
        <form method="POST" action="{{ route('counselor.summaries.copy', $summary) }}">@csrf<button class="btn btn-outline btn-sm !h-11 !text-[13px] w-full">Salin ke Catatan Privat</button></form>
    @endif

    @if(! $summary || in_array($summary->status, ['failed', 'done'], true))
        <form method="POST" action="{{ route('counselor.summaries.store', $c) }}">@csrf
            <button class="btn btn-sage btn-sm !h-11 !text-sm w-full" @disabled($nonSystemCount < 4)><x-icon name="sparkle" size="16" />{{ $summary ? 'Buat ringkasan baru' : 'Ringkas Percakapan' }}</button>
        </form>
        @if($nonSystemCount < 4)<span class="text-xs text-ink-soft">Tersedia setelah ada minimal 4 pesan.</span>@endif
    @endif
</div>
