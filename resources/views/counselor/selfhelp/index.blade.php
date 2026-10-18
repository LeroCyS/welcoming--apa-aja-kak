@extends('layouts.counselor', ['active' => 'selfhelp'])
@section('title', 'Kelola Self-Help')
@section('subtitle', 'Bacaan untuk murid & tinjauan Cerita Teman')
@section('back', route('counselor.dashboard'))
@section('content')
<div class="flex justify-between items-center gap-3 flex-wrap">
    <div role="tablist" aria-label="Kelola Self-Help" class="seg">
        <a role="tab" aria-selected="{{ $tab === 'artikel' ? 'true' : 'false' }}" href="{{ route('counselor.selfhelp.index') }}" class="seg-item no-underline {{ $tab === 'artikel' ? 'seg-item-on' : '' }}">Artikel ({{ $total }})</a>
        <a role="tab" aria-selected="{{ $tab === 'cerita-teman' ? 'true' : 'false' }}" href="{{ route('counselor.selfhelp.index', ['tab' => 'cerita-teman']) }}" class="seg-item no-underline {{ $tab === 'cerita-teman' ? 'seg-item-on' : '' }}">Cerita Teman @if($pending->count())<span class="ml-1 px-1.5 rounded-full bg-terracotta-deep text-white text-xs whitespace-nowrap">{{ $pending->count() }}</span>@endif</a>
    </div>
    @if($tab === 'artikel')<a href="{{ route('counselor.selfhelp.create') }}" class="btn btn-sage btn-sm"><x-icon name="plus" size="15" />Tulis artikel</a>@endif
</div>

@if($tab === 'artikel')
    {{-- Filter --}}
    <form method="GET" action="{{ route('counselor.selfhelp.index') }}" class="box p-3.5 flex flex-wrap gap-3 items-end" aria-label="Filter artikel">
        <div class="flex-[2_1_220px] relative">
            <label for="f-q" class="field-label">Cari</label>
            <input id="f-q" name="q" type="search" value="{{ $filters['q'] ?? '' }}" placeholder="Judul atau ringkasan" class="field text-sm">
        </div>
        <div class="flex-[1_1_150px]"><label for="f-kat" class="field-label">Kategori</label><select id="f-kat" name="kategori" class="field text-sm"><option value="">Semua kategori</option>@foreach($categories as $cat)<option value="{{ $cat->id }}" @selected(($filters['kategori'] ?? '') == $cat->id)>{{ $cat->name }}</option>@endforeach</select></div>
        <div class="flex-[1_1_150px]"><label for="f-topik" class="field-label">Topik</label><select id="f-topik" name="topik" class="field text-sm"><option value="">Semua topik</option>@foreach($topics as $t)<option value="{{ $t->id }}" @selected(($filters['topik'] ?? '') == $t->id)>{{ $t->name }}</option>@endforeach</select></div>
        <div class="flex-[1_1_130px]"><label for="f-tipe" class="field-label">Tipe</label><select id="f-tipe" name="tipe" class="field text-sm"><option value="">Semua tipe</option><option value="article" @selected(($filters['tipe'] ?? '') === 'article')>Artikel</option><option value="infographic" @selected(($filters['tipe'] ?? '') === 'infographic')>Infografis</option></select></div>
        <div class="flex-[1_1_130px]"><label for="f-status" class="field-label">Status</label><select id="f-status" name="status" class="field text-sm"><option value="">Semua status</option><option value="published" @selected(($filters['status'] ?? '') === 'published')>Terbit</option><option value="draft" @selected(($filters['status'] ?? '') === 'draft')>Draft</option></select></div>
        <div class="flex-[1_1_150px]"><label for="f-penulis" class="field-label">Penulis</label><select id="f-penulis" name="penulis" class="field text-sm"><option value="">Semua Guru BK</option><option value="saya" @selected(($filters['penulis'] ?? '') === 'saya')>Tulisanku</option><option value="lain" @selected(($filters['penulis'] ?? '') === 'lain')>Guru BK lain</option></select></div>
        <div class="flex gap-2">
            <button class="btn btn-sage btn-sm"><x-icon name="filter" size="15" />Terapkan</button>
            @if($filters)<a href="{{ route('counselor.selfhelp.index') }}" class="btn btn-outline btn-sm no-underline">Reset</a>@endif
        </div>
    </form>
    @if($filters)<p class="text-sm text-ink-soft -mt-1">{{ $articles->count() }} dari {{ $total }} artikel cocok dengan filter.</p>@endif

    @php $catTone = ['stres-ujian' => 'bg-sand-tint text-sand-ink', 'overthinking' => 'bg-sage-tint text-sage-ink', 'pertemanan' => 'bg-terracotta-tint text-terracotta-ink', 'keluarga' => 'bg-terracotta-tint2 text-terracotta-ink', 'manajemen-waktu' => 'bg-sand-tint text-sand-ink2', 'percaya-diri' => 'bg-sage-tint text-sage-ink2']; @endphp
    <div class="box overflow-hidden">
        {{-- Judul kolom (desktop) --}}
        <div class="hidden lg:grid grid-cols-[64px_minmax(0,1fr)_150px_110px_80px_110px_260px] gap-x-4 px-4 py-3 bg-cream-light/70 border-b border-line-soft text-[11px] font-extrabold tracking-[0.08em] uppercase text-ink-soft">
            <span></span><span>Judul</span><span>Kategori</span><span>Status</span><span class="text-right">Dibaca</span><span>Terbit</span><span>Aksi</span>
        </div>
        <ul class="divide-y divide-line-soft">
            @forelse($articles as $a)
                @php $own = (int) $a->author_id === (int) auth()->id(); @endphp
                <li class="grid grid-cols-[56px_minmax(0,1fr)] lg:grid-cols-[64px_minmax(0,1fr)_150px_110px_80px_110px_260px] gap-x-4 gap-y-3 items-center px-4 py-3.5 transition-colors hover:bg-cream-light/60">
                    <a href="{{ route('counselor.selfhelp.show', $a) }}" class="block w-14 lg:w-16 aspect-square rounded-2xl overflow-hidden flex-none" aria-hidden="true" tabindex="-1"><x-article-cover :article="$a" height="h-full" :icon-size="28" /></a>
                    <div class="min-w-0 flex flex-col gap-1">
                        <a href="{{ route('counselor.selfhelp.show', $a) }}" class="font-extrabold text-[15px] leading-snug text-ink no-underline hover:text-sage-ink line-clamp-2">{{ $a->title }}</a>
                        <span class="flex items-center gap-1.5 flex-wrap text-xs font-bold text-ink-soft">
                            <x-icon :name="$a->type === 'infographic' ? 'image' : 'book'" size="13" />{{ $a->type === 'infographic' ? 'Infografis' : 'Artikel' }} · oleh {{ $own ? 'kamu' : $a->author->displayName() }}
                            @if($a->is_featured)<span class="px-2 py-0.5 rounded-full bg-sand-tint text-sand-ink2 font-extrabold whitespace-nowrap inline-flex items-center gap-1"><x-icon name="sparkle" size="12" stroke="2.4" />Unggulan</span>@endif
                        </span>
                    </div>
                    <span class="hidden lg:inline-flex w-fit max-w-full px-2.5 py-1 rounded-full text-xs font-extrabold truncate {{ $catTone[$a->category?->slug] ?? 'bg-cream text-ink-soft' }}">{{ $a->category?->name }}</span>
                    <span class="hidden lg:block">@if($a->status === 'published')<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-sage-tint text-sage-ink text-xs font-extrabold"><span class="w-1.5 h-1.5 rounded-full bg-sage-deep"></span>Terbit</span>@else<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-sand-tint text-sand-ink text-xs font-extrabold"><span class="w-1.5 h-1.5 rounded-full bg-sand"></span>Draft</span>@endif</span>
                    <span class="hidden lg:block text-right text-sm font-extrabold tabular-nums">{{ number_format($a->view_count, 0, ',', '.') }}×</span>
                    <span class="hidden lg:block text-sm text-ink-soft">{{ $a->published_at?->translatedFormat('j M Y') ?? '-' }}</span>
                    {{-- Aksi: selalu 2 tombol selebar sama + 1 slot ikon, agar semua baris sejajar --}}
                    <div class="col-span-2 lg:col-span-1 grid grid-cols-[1fr_1fr_40px] gap-1.5">
                        @if($own)
                            <a href="{{ route('counselor.selfhelp.edit', $a) }}" class="inline-flex items-center justify-center gap-1.5 h-9 rounded-full border-[1.5px] border-line bg-white text-[13px] font-extrabold text-ink no-underline transition hover:bg-cream-light"><x-icon name="edit" size="14" />Edit</a>
                            <form method="POST" action="{{ route('counselor.selfhelp.publish', $a) }}">@csrf<button class="w-full inline-flex items-center justify-center gap-1.5 h-9 rounded-full text-[13px] font-extrabold transition {{ $a->status === 'published' ? 'bg-sand-tint text-sand-ink2 hover:bg-sand/40' : 'bg-sage-tint text-sage-ink hover:bg-sage/30' }}"><x-icon :name="$a->status === 'published' ? 'lock' : 'send'" size="14" />{{ $a->status === 'published' ? 'Jadikan draft' : 'Terbitkan' }}</button></form>
                            <form method="POST" action="{{ route('counselor.selfhelp.destroy', $a) }}" data-confirm="Hapus artikel ini?" data-confirm-ok="Ya, hapus" data-confirm-tone="danger">@csrf @method('DELETE')<button class="w-10 h-9 rounded-full inline-flex items-center justify-center text-ink-soft transition hover:bg-danger-tint hover:text-danger" aria-label="Hapus artikel {{ $a->title }}" title="Hapus"><x-icon name="trash" size="16" /></button></form>
                        @else
                            <a href="{{ route('counselor.selfhelp.show', $a) }}" class="inline-flex items-center justify-center gap-1.5 h-9 rounded-full border-[1.5px] border-line bg-white text-[13px] font-extrabold text-ink no-underline transition hover:bg-cream-light"><x-icon name="eye" size="14" />Lihat</a>
                            <a href="{{ route('counselor.selfhelp.show', $a) }}#komentar" class="inline-flex items-center justify-center gap-1.5 h-9 rounded-full bg-sage-tint text-sage-ink text-[13px] font-extrabold no-underline transition hover:bg-sage/30"><x-icon name="chat" size="14" />Komentar</a>
                            <span class="w-10 h-9 rounded-full inline-flex items-center justify-center text-toggle-off" title="Hanya pembuat yang bisa menghapus" aria-hidden="true"><x-icon name="lock" size="15" /></span>
                        @endif
                    </div>
                </li>
            @empty
                <li class="list-none p-4">
                    @if($filters)
                        <x-empty-state title="Tidak ada artikel yang cocok" text="Coba ubah atau reset filter." :cta-href="route('counselor.selfhelp.index')" cta-label="Reset filter" variant="sage" />
                    @else
                        <x-empty-state title="Belum ada artikel" text="Tulis bacaan pertama untuk murid." :cta-href="route('counselor.selfhelp.create')" cta-label="Tulis Artikel" variant="sage" />
                    @endif
                </li>
            @endforelse
        </ul>
    </div>
    <p class="text-xs text-ink-soft">Hanya pembuat yang bisa mengubah artikelnya. Artikel guru lain bisa kamu komentari.</p>
@else
    <section class="flex flex-col gap-3" aria-labelledby="pending-h">
        <h2 id="pending-h" class="text-[21px]">Menunggu tinjauan</h2>
        <p class="text-sm text-ink-soft">Jangan tampilkan cerita yang memuat nama, kelas, detail pengenal, atau isi yang melukai.</p>
        @forelse($pending as $p)
            @php $phrases = $flagged[$p->id] ?? []; @endphp
            <article class="box p-4 flex flex-col gap-3" x-data="{ reject: false }">
                <div class="flex gap-2 items-center flex-wrap text-sm">
                    <span class="font-extrabold">{{ $p->display_label }}</span><span class="text-ink-soft">· {{ $p->created_at->diffForHumans() }}</span>
                    @if($phrases)<span class="inline-flex items-center gap-1 text-xs font-extrabold text-danger-ink bg-danger-tint px-2 py-0.5 rounded-full"><x-icon name="flag" size="12" />Kata sensitif terdeteksi</span>@endif
                </div>
                <blockquote class="text-base leading-relaxed whitespace-pre-line wrap-anywhere">{!! $phrases ? \App\Support\Highlight::phrases(e($p->body), $phrases) : e($p->body) !!}</blockquote>
                @if($p->song)<x-song-card :song="$p->song" caption="lagu pilihan penulis" class="max-w-[420px]" />@endif
                @if($phrases)<p class="text-xs text-danger-ink">Penulis sudah diarahkan ke Guru BK & kontak darurat. Sebaiknya jangan ditampilkan.</p>@endif
                <form method="POST" action="{{ route('counselor.stories.moderate', $p) }}" class="flex gap-2 flex-wrap items-end">@csrf
                    <button name="decision" value="approved" class="btn btn-sage btn-sm" x-show="!reject">Tampilkan</button>
                    <button type="button" class="btn btn-outline btn-sm" x-show="!reject" x-on:click="reject = true">Jangan tampilkan</button>
                    <div x-show="reject" x-cloak class="flex gap-2 flex-wrap items-end w-full">
                        <div class="flex-1 min-w-[240px]"><label class="field-label" for="r-{{ $p->id }}">Alasan untuk murid (opsional)</label><input id="r-{{ $p->id }}" name="reason" maxlength="255" class="field text-sm" placeholder="Mis. ada nama teman yang bisa dikenali"></div>
                        <button name="decision" value="rejected" class="btn btn-outline btn-sm">Kirim</button>
                        <button type="button" class="btn btn-soft btn-sm" x-on:click="reject = false">Batal</button>
                    </div>
                </form>
            </article>
        @empty
            <div class="box"><x-empty-state title="Tidak ada antrean" text="Semua Cerita Teman sudah ditinjau." /></div>
        @endforelse
    </section>
    <section class="flex flex-col gap-3" aria-labelledby="hist-h">
        <h2 id="hist-h" class="text-[21px]">Riwayat</h2>
        <div class="box px-4 py-1.5">
            @forelse($history as $p)
                <div class="flex gap-3 items-start py-3.5 border-b border-line-soft last:border-b-0 flex-wrap">
                    <span class="flex-1 min-w-[240px] text-sm leading-relaxed wrap-anywhere">“{{ \Illuminate\Support\Str::limit($p->body, 220) }}” <span class="text-ink-soft">· {{ $p->display_label }}</span></span>
                    <x-badge :tone="$p->status === 'approved' ? 'sage' : 'neutral'" class="!text-xs">{{ $p->status === 'approved' ? 'Tampil' : 'Tidak ditampilkan' }}</x-badge>
                    <span class="text-xs text-ink-soft whitespace-nowrap">{{ $p->moderator?->displayName() }} · {{ $p->moderated_at?->translatedFormat('j M') }}</span>
                    @if($p->status === 'approved')
                        <form method="POST" action="{{ route('counselor.stories.moderate', $p) }}" x-data data-confirm="Turunkan cerita ini dari papan?" data-confirm-ok="Ya, turunkan" data-confirm-tone="danger">@csrf<input type="hidden" name="decision" value="rejected"><button class="tool-btn text-ink-soft">Turunkan</button></form>
                    @endif
                </div>
            @empty
                <p class="py-4 text-sm text-ink-soft">Belum ada riwayat tinjauan.</p>
            @endforelse
        </div>
    </section>
@endif
@endsection
