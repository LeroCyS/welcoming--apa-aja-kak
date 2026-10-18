@extends('layouts.counselor', ['active' => 'selfhelp'])
@section('title', $article->exists ? 'Edit Artikel' : 'Tulis Artikel')
@php $cat = $categories->firstWhere('id', old('article_category_id', $article->article_category_id)); @endphp
@section('header')
<header class="flex items-center justify-between gap-4 flex-wrap no-print">
    <div class="flex items-center gap-3 min-w-0">
    <x-back-button :href="$article->exists ? route('counselor.selfhelp.show', $article) : route('counselor.selfhelp.index')" />
    <div class="flex flex-col gap-1.5">
        <nav aria-label="Breadcrumb" class="flex gap-2 text-sm font-bold"><a href="{{ route('counselor.selfhelp.index') }}" class="text-ink-soft no-underline">Kelola Self-Help</a><span class="text-[#B9AFA0]">/</span><span>{{ $article->exists ? 'Edit artikel' : 'Artikel baru' }}</span></nav>
        <h1 class="text-[28px] lg:text-[32px] leading-tight">{{ $article->exists ? 'Edit Artikel' : 'Tulis Artikel' }}</h1>
    </div>
    </div>
    <div class="flex gap-2.5 items-center flex-wrap">
        @if($article->exists)<span class="text-[13px] text-ink-soft">{{ $article->status === 'published' ? 'Terbit' : 'Draft' }} · disimpan {{ $article->updated_at->diffForHumans() }}</span>@endif
        <button type="submit" form="article-form" name="action" value="draft" class="btn btn-outline !h-[46px] !text-[15px]">Simpan Draft</button>
        <button type="submit" form="article-form" name="action" value="publish" class="btn btn-sage !h-[46px] !text-[15px]">{{ $article->status === 'published' ? 'Simpan & Perbarui' : 'Terbitkan' }}</button>
    </div>
</header>
@endsection
@section('content')
<form id="article-form" method="POST" enctype="multipart/form-data" action="{{ $article->exists ? route('counselor.selfhelp.update', $article) : route('counselor.selfhelp.store') }}" data-module="editor"
      x-data="articleEditor({{ Js::from(['title' => old('title', $article->title), 'excerpt' => old('excerpt', $article->excerpt), 'body' => old('body', $article->body ?? ''), 'cover' => $article->coverUrl(), 'categories' => $categories->pluck('name', 'id'), 'imageUrl' => route('counselor.selfhelp.image')]) }})"
      class="flex flex-wrap gap-5 items-start">
    @csrf
    @if($article->exists) @method('PUT') @endif
    <section aria-label="Editor" class="flex-[999_1_520px] min-w-0 bg-white rounded-[26px] p-6 flex flex-col gap-4 shadow-[0_4px_16px_rgba(62,53,48,0.04)]">
        @if($errors->any())<div class="p-3.5 rounded-2xl bg-terracotta-tint text-terracotta-ink text-sm font-bold" role="alert">{{ $errors->first() }}</div>@endif
        <div class="flex flex-col gap-1.5">
            <label for="ed-title" class="text-sm font-extrabold">Judul</label>
            <input id="ed-title" name="title" x-model="title" required maxlength="160" class="field !h-[52px] font-display !text-xl">
        </div>
        <div class="grid grid-cols-[repeat(auto-fit,minmax(min(200px,100%),1fr))] gap-3">
            <div><label for="ed-cat" class="field-label">Kategori</label><select id="ed-cat" name="article_category_id" x-model="category" class="field font-bold">@foreach($categories as $c)<option value="{{ $c->id }}" @selected(old('article_category_id', $article->article_category_id) == $c->id)>{{ $c->name }}</option>@endforeach</select></div>
            <div><label for="ed-type" class="field-label">Jenis</label><select id="ed-type" name="type" class="field font-bold"><option value="article" @selected(old('type', $article->type) === 'article')>Artikel</option><option value="infographic" @selected(old('type', $article->type) === 'infographic')>Infografis</option></select></div>
            <div><label for="ed-slug" class="field-label">Slug (otomatis)</label><input id="ed-slug" name="slug" value="{{ old('slug', $article->slug) }}" :placeholder="slugify(title)" class="field text-sm"></div>
        </div>
        <div>
            <label for="ed-excerpt" class="field-label">Ringkasan singkat</label>
            <input id="ed-excerpt" name="excerpt" x-model="excerpt" maxlength="255" required class="field" placeholder="Satu kalimat yang muncul di kartu artikel">
        </div>
        <fieldset class="flex flex-col gap-2">
            <legend class="text-sm font-extrabold mb-1">Topik terkait <span class="font-bold text-ink-soft">(untuk saran artikel di chat)</span></legend>
            <div class="flex flex-wrap gap-2">
                @php $selTopics = old('topics', $article->exists ? $article->topics->pluck('id')->all() : []); @endphp
                @foreach($topics as $t)
                    <label class="chip cursor-pointer has-[:checked]:bg-sage-deep has-[:checked]:border-sage-deep has-[:checked]:text-white"><input type="checkbox" name="topics[]" value="{{ $t->id }}" class="sr-only" @checked(in_array($t->id, $selTopics))>{{ $t->name }}</label>
                @endforeach
            </div>
        </fieldset>
        <div class="flex flex-col gap-1.5">
            <span class="text-sm font-extrabold">Gambar header</span>
            <label for="ed-img" class="relative h-[140px] rounded-[18px] border-2 border-dashed border-toggle-off bg-[#FDFBF6] flex flex-col items-center justify-center gap-2 cursor-pointer text-ink-soft overflow-hidden">
                <template x-if="cover"><img :src="cover" alt="" class="absolute inset-0 w-full h-full object-cover"></template>
                <x-icon name="image" size="28" />
                <span class="text-sm font-extrabold text-ink relative" x-text="cover ? 'Ganti gambar' : 'Klik untuk unggah gambar'"></span>
                <span class="text-xs relative">JPG/PNG/WEBP, maks. 2 MB · ilustrasi garis lebih disarankan</span>
            </label>
            <input id="ed-img" name="cover" type="file" accept="image/png,image/jpeg,image/webp" data-shrink class="sr-only" x-on:change="cover = URL.createObjectURL($event.target.files[0])">
            @if($article->cover_path)<label class="text-xs font-bold text-ink-soft flex items-center gap-2"><input type="checkbox" name="remove_cover" value="1" class="accent-sage-deep">Hapus gambar saat ini</label>@endif
        </div>
        <div class="flex flex-col gap-1.5">
            <span id="ed-body-label" class="text-sm font-extrabold">Isi artikel</span>
            <div class="border-[1.5px] border-line rounded-[18px] overflow-hidden">
                <div role="toolbar" aria-label="Format teks" class="flex gap-1 p-2 bg-cream-light border-b border-line flex-wrap">
                    <button type="button" x-on:click="cmd('bold')" aria-label="Tebal" class="w-10 h-10 rounded-[10px] bg-white font-extrabold">B</button>
                    <button type="button" x-on:click="cmd('italic')" aria-label="Miring" class="w-10 h-10 rounded-[10px] hover:bg-white italic font-display">I</button>
                    <button type="button" x-on:click="block('h2')" aria-label="Subjudul" class="h-10 px-3 rounded-[10px] hover:bg-white font-extrabold text-sm">H2</button>
                    <button type="button" x-on:click="block('h3')" aria-label="Sub-subjudul" class="h-10 px-3 rounded-[10px] hover:bg-white font-extrabold text-sm">H3</button>
                    <button type="button" x-on:click="block('p')" aria-label="Paragraf" class="h-10 px-3 rounded-[10px] hover:bg-white font-bold text-sm">¶</button>
                    <button type="button" x-on:click="cmd('insertUnorderedList')" aria-label="Daftar berpoin" class="w-10 h-10 rounded-[10px] hover:bg-white flex items-center justify-center"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M9 6h11M9 12h11M9 18h11"/><circle cx="4.5" cy="6" r="1" fill="currentColor"/><circle cx="4.5" cy="12" r="1" fill="currentColor"/><circle cx="4.5" cy="18" r="1" fill="currentColor"/></svg></button>
                    <button type="button" x-on:click="cmd('insertOrderedList')" aria-label="Daftar bernomor" class="w-10 h-10 rounded-[10px] hover:bg-white font-extrabold text-sm">1.</button>
                    <button type="button" x-on:click="block('blockquote')" aria-label="Kutipan" class="w-10 h-10 rounded-[10px] hover:bg-white font-display text-xl">“</button>
                    <button type="button" x-on:click="link()" aria-label="Tautan" class="w-10 h-10 rounded-[10px] hover:bg-white flex items-center justify-center"><x-icon name="link" size="18" /></button>
                    <label class="h-10 px-3 rounded-[10px] hover:bg-white flex items-center gap-1.5 text-sm font-extrabold cursor-pointer" title="Sisipkan foto dari perangkat" :class="uploading && 'opacity-60 pointer-events-none'">
                        <x-icon name="image" size="18" /><span x-text="uploading ? 'Mengunggah…' : 'Foto'">Foto</span>
                        <input type="file" accept="image/png,image/jpeg,image/webp" class="sr-only" x-on:change="uploadImage($event)">
                    </label>
                    <button type="button" x-on:click="image()" aria-label="Gambar dari URL" title="Sisipkan gambar dari URL" class="w-10 h-10 rounded-[10px] hover:bg-white flex items-center justify-center"><x-icon name="link" size="16" /><span class="sr-only">Gambar dari URL</span></button>
                </div>
                <p x-show="uploadError" x-text="uploadError" x-cloak class="field-error px-3 !mt-0"></p>
                <div x-ref="editor" role="textbox" aria-multiline="true" aria-labelledby="ed-body-label" contenteditable="true" x-on:input="sync()" x-on:blur="sync()" class="prose-wl min-h-[360px] px-[22px] py-5 !text-base outline-none bg-white"></div>
            </div>
            <input type="hidden" name="body" x-model="body">
            <p class="text-xs text-ink-soft">Durasi baca dihitung otomatis (200 kata/menit).</p>
        </div>
        <label class="flex items-center gap-2.5 text-sm font-bold"><input type="hidden" name="is_featured" value="0"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $article->is_featured)) class="w-5 h-5 accent-sage-deep">Jadikan artikel unggulan</label>
    </section>

    <section aria-label="Preview" class="flex-[1_1_320px] lg:max-w-[380px] flex flex-col gap-2.5 lg:sticky lg:top-6">
        <span class="text-[13px] font-extrabold tracking-[0.08em] uppercase text-ink-soft">Preview tampilan murid</span>
        <div class="bg-cream border border-line rounded-[32px] p-4 flex flex-col gap-3 shadow-[0_10px_30px_rgba(62,53,48,0.10)]">
            <div class="h-[140px] rounded-[22px] bg-sand-tint overflow-hidden flex items-center justify-center text-[13px] font-bold text-sand-ink">
                <template x-if="cover"><img :src="cover" alt="" class="w-full h-full object-cover"></template>
                <span x-show="!cover">Ilustrasi kategori</span>
            </div>
            <span class="self-start px-2.5 py-1 rounded-full bg-sand-tint text-sand-ink text-[11px] font-extrabold" x-text="(categories[category] || '') + ' · ' + minutes() + ' menit baca'"></span>
            <span class="font-display text-[21px] leading-tight" x-text="title || 'Judul artikel'"></span>
            <span class="text-sm leading-relaxed text-[#5A4E47]" x-text="excerpt || 'Ringkasan singkat artikel…'"></span>
            <span class="text-xs font-bold text-ink-soft">{{ ($article->author ?? auth()->user())->displayName() }}</span>
            <div class="prose-wl !text-sm max-h-[260px] overflow-hidden border-t border-line pt-3" x-html="body"></div>
        </div>
    </section>
</form>
@endsection
