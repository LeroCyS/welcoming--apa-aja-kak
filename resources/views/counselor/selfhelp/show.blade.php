@extends('layouts.counselor', ['active' => 'selfhelp'])
@section('title', $article->title)
@section('subtitle', 'oleh '.($canManage ? 'kamu' : $article->author->displayName()).' · '.($article->status === 'published' ? 'Terbit' : 'Draft'))
@section('back', route('counselor.selfhelp.index'))
@section('content')
<div class="grid gap-6 items-start xl:grid-cols-[minmax(0,1fr)_380px]">
    <article class="box p-5 sm:p-7 min-w-0 flex flex-col gap-4">
        <div class="rounded-[24px] overflow-hidden"><x-article-cover :article="$article" height="h-[180px] sm:h-[240px]" :icon-size="120" /></div>
        <div class="flex gap-2 flex-wrap items-center text-sm">
            <span class="px-3 py-1 rounded-full bg-sand-tint text-sand-ink text-[13px] font-extrabold whitespace-nowrap">{{ $article->category?->name }}</span>
            @foreach($article->topics as $t)<span class="px-3 py-1 rounded-full bg-cream text-[13px] font-extrabold whitespace-nowrap">{{ $t->name }}</span>@endforeach
            <span class="text-ink-soft">{{ $article->read_minutes }} menit baca · dibaca {{ $article->view_count }}×</span>
        </div>
        <h2 class="text-[28px] sm:text-[34px] leading-tight">{{ $article->title }}</h2>
        <p class="text-ink-soft">{{ $article->excerpt }}</p>
        <div class="prose-wl">{!! $article->body !!}</div>
    </article>

    <aside class="flex flex-col gap-4 xl:sticky xl:top-6">
        @if($canManage)
            <div class="box p-4 flex flex-col gap-2.5">
                <span class="box-title">Artikel milikmu</span>
                <div class="flex gap-2 flex-wrap">
                    <a href="{{ route('counselor.selfhelp.edit', $article) }}" class="btn btn-sage btn-sm no-underline"><x-icon name="edit" size="15" />Edit</a>
                    <form method="POST" action="{{ route('counselor.selfhelp.publish', $article) }}">@csrf<button class="btn btn-outline btn-sm">{{ $article->status === 'published' ? 'Jadikan draft' : 'Terbitkan' }}</button></form>
                </div>
            </div>
        @else
            <div class="box p-4 text-sm leading-relaxed text-ink-soft flex gap-2.5"><x-icon name="info" size="18" class="flex-none mt-0.5" /><span>Hanya {{ $article->author->displayName() }} yang bisa mengubah atau menghapus artikel ini. Punya masukan? Tulis di komentar, {{ $article->author->displayName() }} akan mendapat notifikasi.</span></div>
        @endif

        <section id="komentar" class="box p-4 flex flex-col gap-3 scroll-mt-6" aria-labelledby="komentar-h">
            <h3 id="komentar-h" class="box-title">Komentar Guru BK ({{ $comments->count() }})</h3>
            <p class="text-xs text-ink-soft -mt-1.5">Hanya terlihat oleh Guru BK, tidak oleh murid.</p>
            @forelse($comments as $cm)
                <div id="komentar-{{ $cm->id }}" class="flex gap-2.5 items-start scroll-mt-6">
                    <x-counselor-avatar :counselor="$cm->counselor" :size="32" :radius="10" />
                    <div class="flex-1 min-w-0 bg-cream-light rounded-2xl px-3 py-2">
                        <div class="flex items-center gap-2 text-xs"><span class="font-extrabold">{{ (int) $cm->counselor_id === (int) auth()->id() ? 'Kamu' : $cm->counselor->displayName() }}</span><span class="text-ink-soft">{{ $cm->created_at->diffForHumans() }}</span>
                            @if((int) $cm->counselor_id === (int) auth()->id() || $canManage)
                                <form method="POST" action="{{ route('counselor.selfhelp.comments.destroy', $cm) }}" class="ml-auto" x-data data-confirm="Hapus komentar ini?" data-confirm-ok="Ya, hapus" data-confirm-tone="danger">@csrf @method('DELETE')<button class="text-ink-soft hover:text-ink" aria-label="Hapus komentar"><x-icon name="trash" size="13" /></button></form>
                            @endif
                        </div>
                        <p class="text-sm leading-relaxed whitespace-pre-line break-words mt-0.5">{{ $cm->body }}</p>
                    </div>
                </div>
            @empty
                <p class="text-sm text-ink-soft">Belum ada komentar.</p>
            @endforelse
            <form method="POST" action="{{ route('counselor.selfhelp.comments.store', $article) }}" class="flex flex-col gap-2 pt-1 border-t border-line-soft">@csrf
                <label for="cm-body" class="sr-only">Tulis komentar</label>
                <textarea id="cm-body" name="body" rows="3" maxlength="2000" required class="field text-sm @error('body') is-invalid @enderror" placeholder="{{ $canManage ? 'Balas komentar…' : 'Masukan untuk '.$article->author->displayName().'…' }}">{{ old('body') }}</textarea>
                @error('body')<p class="field-error !mt-0">{{ $message }}</p>@enderror
                <button class="btn btn-sage btn-sm self-end">Kirim komentar</button>
            </form>
        </section>
    </aside>
</div>
@endsection
