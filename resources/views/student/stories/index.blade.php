@extends('layouts.student', ['active' => 'teman'])
@section('title', 'Cerita Teman')
@section('subtitle', 'Cerita tanpa nama dari teman satu sekolahmu.')
@section('content')
@if(session('story_critical'))
    <div class="bg-white border-[1.5px] border-danger-line rounded-card p-5 flex flex-col gap-2.5 max-w-[760px]" role="alert">
        <span class="inline-flex items-center gap-2 font-extrabold text-danger-ink"><x-icon name="phone" size="18" class="text-danger" />Sepertinya kamu sedang melewati hal yang sangat berat</span>
        <p class="text-sm leading-relaxed">Kamu tidak sendiri. Ngobrol dengan Guru BK sekarang, tanpa nama.</p>
        <span class="flex gap-2 flex-wrap">
            <a href="{{ route('student.conversations.create') }}" class="btn btn-primary btn-sm !text-sm">Cerita ke Guru BK</a>
            <a href="{{ route('emergency') }}" class="btn btn-outline btn-sm !text-sm">Kontak darurat</a>
        </span>
    </div>
@endif
{{-- Kolom cerita mengisi lebar (2 kolom bertumpuk tanpa celah), panel samping menempel di kanan. --}}
<div class="grid grid-cols-[minmax(0,1fr)] lg:grid-cols-[minmax(0,1fr)_320px] gap-6 items-start">
    <div class="min-w-0 flex flex-col gap-4">
        <div class="flex items-center justify-between gap-3 flex-wrap">
            <div role="tablist" class="seg">
                <a role="tab" href="{{ route('student.stories.index') }}" aria-selected="{{ $tab === 'semua' ? 'true' : 'false' }}" class="seg-item no-underline {{ $tab === 'semua' ? 'seg-item-on' : '' }}">Semua cerita</a>
                <a role="tab" href="{{ route('student.stories.index', ['tab' => 'ceritaku']) }}" aria-selected="{{ $tab === 'ceritaku' ? 'true' : 'false' }}" class="seg-item no-underline {{ $tab === 'ceritaku' ? 'seg-item-on' : '' }}">Ceritaku @if($myPending)<span class="ml-1 text-xs px-1.5 rounded-full bg-sand-tint text-sand-ink whitespace-nowrap">{{ $myPending }}</span>@endif</a>
            </div>
            <a href="{{ route('student.stories.create') }}" class="btn btn-primary btn-sm !text-sm"><x-icon name="edit" size="16" />Bagikan ceritamu</a>
        </div>

        @if($posts->isNotEmpty())<div class="md:columns-2 gap-4 [&>*]:mb-4 [&>*]:break-inside-avoid">@endif
        @forelse($posts as $post)
            @php $mine = hash_equals($post->author_hash, $hash); $reacted = $myReactions[$post->id] ?? []; @endphp
            <article class="bg-white border border-line-soft rounded-card-l p-5 shadow-soft flex flex-col gap-3.5">
                <header class="flex items-center gap-3 flex-wrap">
                    <span class="w-10 h-10 rounded-2xl bg-terracotta-tint text-terracotta-ink flex items-center justify-center flex-none"><x-icon name="people-heart" size="20" /></span>
                    <span class="flex-1 min-w-0 flex flex-col">
                        <span class="font-extrabold text-[15px]">{{ $mine ? 'Ceritamu' : $post->display_label }}</span>
                        <span class="text-xs text-ink-soft">{{ ($post->moderated_at ?? $post->created_at)->diffForHumans() }}</span>
                    </span>
                    @if($tab === 'ceritaku')
                        <span class="text-xs font-extrabold px-2.5 py-1 rounded-full {{ ['pending' => 'bg-sand-tint text-sand-ink', 'approved' => 'bg-sage-tint text-sage-ink', 'rejected' => 'bg-cream-deep text-ink-soft'][$post->status] }} whitespace-nowrap">{{ $post->statusLabel() }}</span>
                    @endif
                </header>
                <p class="text-[15px] leading-[1.65] whitespace-pre-line wrap-anywhere">{{ $post->body }}</p>
                @if($post->song)<x-song-card :song="$post->song" class="max-w-[420px]" />@endif
                @if($tab === 'ceritaku' && $post->status === 'rejected')
                    <p class="text-sm rounded-2xl bg-cream-light p-3 text-ink-soft"><b>Belum bisa ditampilkan.</b> {{ $post->reject_reason ?: 'Mungkin ada detail yang bisa membuat seseorang dikenali. Kamu boleh menulis ulang tanpa detail itu.' }}</p>
                @endif
                <footer class="flex items-center gap-1.5 flex-wrap">
                    @foreach(\App\Models\StoryPost::REACTIONS as $type => [$label, $icon])
                        @if($mine || $post->status !== 'approved')
                            <span class="inline-flex items-center gap-1 h-8 px-2.5 rounded-full bg-cream-light text-xs font-extrabold text-ink-soft whitespace-nowrap"><x-icon :name="$icon" size="14" />{{ $label }} · {{ $post->{$type.'_count'} }}</span>
                        @else
                            <button type="button" x-data="{ on: @js(in_array($type, $reacted, true)), n: {{ $post->{$type.'_count'} }}, busy: false }"
                                    x-on:click="if (busy) return; busy = true; axios.post(@js(route('student.stories.react', $post)), { type: @js($type) }).then(({ data }) => { on = data.active; n = data.count; }).finally(() => busy = false)"
                                    :aria-pressed="on.toString()" class="inline-flex items-center gap-1 h-8 px-2.5 rounded-full border-[1.5px] text-xs font-extrabold whitespace-nowrap transition"
                                    :class="on ? 'bg-terracotta-tint border-terracotta-deep text-terracotta-ink' : 'bg-white border-line text-ink'">
                                <x-icon :name="$icon" size="14" />{{ $label }} · <span x-text="n">{{ $post->{$type.'_count'} }}</span>
                            </button>
                        @endif
                    @endforeach
                    @if($mine)
                        <form method="POST" action="{{ route('student.stories.destroy', $post) }}" class="ml-auto" x-data data-confirm="Hapus cerita ini?" data-confirm-ok="Ya, hapus" data-confirm-tone="danger">@csrf @method('DELETE')<button class="inline-flex items-center gap-1.5 h-9 px-3 rounded-full text-[13px] font-extrabold text-ink-soft hover:bg-cream-light"><x-icon name="trash" size="15" />Hapus</button></form>
                    @endif
                </footer>
            </article>
        @empty
            <div class="card !rounded-panel">
                @if($tab === 'ceritaku')
                    <x-empty-state title="Kamu belum membagikan cerita" text="Pengalamanmu bisa membuat teman lain merasa tidak sendirian." :cta-href="route('student.stories.create')" cta-label="Bagikan ceritamu" />
                @else
                    <x-empty-state title="Belum ada cerita dari temanmu" text="Jadilah yang pertama berbagi. Ceritamu tampil tanpa nama setelah ditinjau Guru BK." :cta-href="route('student.stories.create')" cta-label="Bagikan ceritamu" />
                @endif
            </div>
        @endforelse
        @if($posts->isNotEmpty())</div>@endif
        {{ $posts->links('partials.pagination') }}
    </div>

    <aside class="flex flex-col gap-4 lg:sticky lg:top-24">
        <div class="bg-sage-tint border border-sage/25 rounded-card-l p-5 flex flex-col gap-2.5 text-sage-ink">
            <span class="inline-flex items-center gap-2 font-extrabold text-base"><x-icon name="shield" size="20" />Ruang yang aman</span>
            <ul class="text-sm leading-[1.6] list-disc pl-5 flex flex-col gap-1">
                <li>Semua cerita tanpa nama dan hanya untuk murid di sekolahmu.</li>
                <li>Guru BK meninjau setiap cerita sebelum tampil.</li>
                <li>Tidak ada komentar, hanya dukungan.</li>
            </ul>
        </div>
        <div class="bg-white border border-line-soft rounded-card-l p-5 flex flex-col gap-3 shadow-soft">
            <span class="font-extrabold text-base">Lagi butuh teman bicara?</span>
            <span class="text-sm leading-[1.55] text-ink-soft">Ceritakan langsung ke Guru BK, tanpa nama juga bisa.</span>
            <a href="{{ route('student.conversations.create') }}" class="btn btn-outline btn-sm !text-sm !font-extrabold">Mulai Cerita</a>
        </div>
    </aside>
</div>
@endsection
