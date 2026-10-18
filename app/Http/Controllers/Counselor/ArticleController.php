<?php

namespace App\Http\Controllers\Counselor;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\ArticleComment;
use App\Models\StoryPost;
use App\Models\Topic;
use App\Services\ChatMedia;
use App\Services\KeywordScanner;
use App\Support\Notify;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Mews\Purifier\Facades\Purifier;

class ArticleController extends Controller
{
    public const FILTERS = ['q', 'kategori', 'tipe', 'status', 'penulis', 'topik'];

    public function index(Request $request, KeywordScanner $scanner): View
    {
        $tab = $request->query('tab') === 'cerita-teman' ? 'cerita-teman' : 'artikel';
        $user = $request->user();
        $schoolId = $user->school_id;
        $f = array_filter($request->only(self::FILTERS), fn ($v) => filled($v));

        $articles = Article::where('school_id', $schoolId)->with(['category', 'author.counselorProfile'])->withCount('comments')
            ->when($f['q'] ?? null, fn ($q, $term) => $q->where(fn ($w) => $w->where('title', 'like', "%{$term}%")->orWhere('excerpt', 'like', "%{$term}%")))
            ->when($f['kategori'] ?? null, fn ($q, $id) => $q->where('article_category_id', $id))
            ->when($f['tipe'] ?? null, fn ($q, $type) => $q->where('type', $type))
            ->when($f['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when(($f['penulis'] ?? null) === 'saya', fn ($q) => $q->where('author_id', $user->id))
            ->when(($f['penulis'] ?? null) === 'lain', fn ($q) => $q->where('author_id', '!=', $user->id))
            ->when($f['topik'] ?? null, fn ($q, $id) => $q->whereHas('topics', fn ($t) => $t->whereKey($id)))
            ->orderByRaw("status = 'draft' desc")->orderByDesc('updated_at')->get();

        $pending = StoryPost::where('school_id', $schoolId)->where('status', 'pending')->oldest()->get();

        return view('counselor.selfhelp.index', [
            'tab' => $tab,
            'articles' => $articles,
            'filters' => $f,
            'categories' => ArticleCategory::orderBy('sort_order')->get(),
            'topics' => Topic::active()->get(),
            'total' => Article::where('school_id', $schoolId)->count(),
            'pending' => $pending,
            // Kata sensitif disorot agar guru bisa menolak cerita berbahaya dan menyiapkan dukungan.
            'flagged' => $pending->mapWithKeys(fn (StoryPost $p) => [$p->id => $scanner->scan($p->body, $schoolId)->pluck('phrase')->all()]),
            'history' => StoryPost::where('school_id', $schoolId)->where('status', '!=', 'pending')->with('moderator.counselorProfile')->latest('moderated_at')->limit(30)->get(),
        ]);
    }

    /** Pratinjau artikel (termasuk draf) + komentar sesama Guru BK. */
    public function show(Request $request, Article $article): View
    {
        $this->ensureSameSchool($article);

        return view('counselor.selfhelp.show', [
            'article' => $article->load(['category', 'topics', 'author.counselorProfile']),
            'comments' => $article->comments()->with('counselor.counselorProfile')->oldest()->get(),
            'canManage' => $request->user()->can('manage', $article),
        ]);
    }

    public function comment(Request $request, Article $article): RedirectResponse
    {
        $this->ensureSameSchool($article);
        $data = $request->validate(['body' => ['required', 'string', 'max:2000']], ['body.required' => 'Tulis komentarnya dulu.']);
        $comment = $article->comments()->create(['counselor_id' => $request->user()->id, 'body' => trim($data['body'])]);

        // Komentar guru lain masuk ke pembuat; balasan pembuat masuk ke guru yang pernah berkomentar.
        $url = route('counselor.selfhelp.show', $article).'#komentar-'.$comment->id;
        $recipients = (int) $article->author_id !== (int) $request->user()->id
            ? collect([$article->author])
            : $article->comments()->where('counselor_id', '!=', $request->user()->id)->with('counselor')->get()->pluck('counselor')->unique('id');
        foreach ($recipients as $recipient) {
            Notify::user($recipient, 'article_comment', $request->user()->displayName().' mengomentari “'.Str::limit($article->title, 60).'”', $url, false, Str::limit($comment->body, 120));
        }

        return redirect()->to(route('counselor.selfhelp.show', $article).'#komentar-'.$comment->id)->with('status', 'Komentar terkirim ke pembuat artikel.');
    }

    /** Komentar bisa dihapus penulisnya sendiri atau pembuat artikel. */
    public function destroyComment(Request $request, ArticleComment $comment): RedirectResponse
    {
        $article = $comment->article;
        $this->ensureSameSchool($article);
        abort_unless((int) $comment->counselor_id === (int) $request->user()->id || $request->user()->can('manage', $article), 403);
        $comment->delete();

        return redirect()->to(route('counselor.selfhelp.show', $article).'#komentar')->with('status', 'Komentar dihapus.');
    }

    public function create(): View
    {
        return $this->form(new Article(['type' => 'article', 'status' => 'draft']));
    }

    public function edit(Article $article): View
    {
        $this->ensureSameSchool($article);
        $this->authorize('manage', $article);

        return $this->form($article->load('topics'));
    }

    protected function form(Article $article): View
    {
        return view('counselor.selfhelp.editor', [
            'article' => $article,
            'categories' => ArticleCategory::orderBy('sort_order')->get(),
            'topics' => Topic::active()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $article = new Article(['school_id' => $request->user()->school_id, 'author_id' => $request->user()->id, 'view_count' => 0]);

        return $this->save($request, $article);
    }

    public function update(Request $request, Article $article): RedirectResponse
    {
        $this->ensureSameSchool($article);
        $this->authorize('manage', $article);

        return $this->save($request, $article);
    }

    protected function save(Request $request, Article $article): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:180', 'alpha_dash', Rule::unique('articles', 'slug')->ignore($article->id)],
            'article_category_id' => ['required', 'exists:article_categories,id'],
            'type' => ['required', Rule::in(['article', 'infographic'])],
            'excerpt' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:60000'],
            'topics' => ['array'],
            'topics.*' => ['integer', 'exists:topics,id'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_cover' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'action' => ['required', Rule::in(['draft', 'publish'])],
        ], [
            'cover.max' => 'Gambar maksimal 2 MB.',
            'cover.uploaded' => 'Foto gagal diunggah, kemungkinan ukurannya terlalu besar untuk server. Coba foto lain atau perkecil dulu.',
            'cover.mimes' => 'Gambar harus JPG, PNG, atau WEBP.',
            'slug.unique' => 'Slug sudah dipakai artikel lain.',
        ], ['title' => 'judul', 'excerpt' => 'ringkasan', 'body' => 'isi artikel', 'article_category_id' => 'kategori']);

        $body = Purifier::clean($data['body'], 'article');
        if (trim(strip_tags($body)) === '') {
            return back()->withInput()->withErrors(['body' => 'Isi artikel masih kosong.']);
        }

        $slug = $data['slug'] ?: Str::slug($data['title']);
        $base = $slug;
        $i = 2;
        while (Article::where('slug', $slug)->where('id', '!=', $article->id ?? 0)->exists()) {
            $slug = $base.'-'.$i++;
        }

        if ($request->hasFile('cover')) {
            if ($article->cover_path && ! str_starts_with($article->cover_path, 'images/')) {
                Storage::disk('public')->delete($article->cover_path);
            }
            $article->cover_path = $request->file('cover')->storeAs('covers', Str::random(32).'.'.$request->file('cover')->extension(), 'public');
        } elseif ($request->boolean('remove_cover') && $article->cover_path) {
            Storage::disk('public')->delete($article->cover_path);
            $article->cover_path = null;
        }

        $article->fill([
            'title' => $data['title'],
            'slug' => $slug,
            'article_category_id' => $data['article_category_id'],
            'type' => $data['type'],
            'excerpt' => $data['excerpt'],
            'body' => $body,
            'read_minutes' => Article::readMinutesFor($body),
            'is_featured' => $request->boolean('is_featured'),
        ]);
        if ($data['action'] === 'publish') {
            $article->status = 'published';
            $article->published_at ??= now();
        } elseif (! $article->exists) {
            $article->status = 'draft';
        }
        $article->save();
        $article->topics()->sync($data['topics'] ?? []);

        return redirect()->route('counselor.selfhelp.edit', $article)->with('status', $data['action'] === 'publish' ? 'Artikel terbit dan bisa dibaca murid.' : 'Draft tersimpan.');
    }

    /** Foto untuk disisipkan ke isi artikel, diunggah dari perangkat guru (di-encode ulang tanpa EXIF). */
    public function uploadImage(Request $request, ChatMedia $media): JsonResponse
    {
        $request->validate(['image' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192']], [
            'image.required' => 'Pilih foto dulu, ya.',
            'image.image' => 'File harus berupa foto.',
            'image.mimes' => 'Format foto harus JPG, PNG, atau WEBP.',
            'image.max' => 'Ukuran foto maksimal 8 MB.',
            'image.uploaded' => 'Foto gagal diunggah, kemungkinan ukurannya terlalu besar untuk server. Coba foto lain atau perkecil dulu.',
        ]);
        $stored = $media->storeImage($request->file('image'), 'article-images/'.$request->user()->school_id, 'public');

        return response()->json(['url' => Storage::disk('public')->url($stored['path'])]);
    }

    public function publish(Article $article): RedirectResponse
    {
        $this->ensureSameSchool($article);
        $this->authorize('manage', $article);
        $article->update(['status' => $article->status === 'published' ? 'draft' : 'published', 'published_at' => $article->published_at ?? now()]);

        return back()->with('status', $article->status === 'published' ? 'Artikel diterbitkan.' : 'Artikel dikembalikan ke draft.');
    }

    public function destroy(Article $article): RedirectResponse
    {
        $this->ensureSameSchool($article);
        $this->authorize('manage', $article);
        if ($article->cover_path && ! str_starts_with($article->cover_path, 'images/')) {
            Storage::disk('public')->delete($article->cover_path);
        }
        $article->delete();

        return redirect()->route('counselor.selfhelp.index')->with('status', 'Artikel dihapus.');
    }
}
