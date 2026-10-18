<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SelfHelpController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q'));
        $cat = $request->query('kategori');
        $categories = ArticleCategory::orderBy('sort_order')->get();
        $category = $cat ? $categories->firstWhere('slug', $cat) : null;

        $schoolId = $request->user()->school_id;
        $articles = Article::published()->where('school_id', $schoolId)->with(['category', 'author.counselorProfile'])
            ->when($q !== '', fn ($w) => $w->where(fn ($x) => $x->where('title', 'like', "%{$q}%")->orWhere('excerpt', 'like', "%{$q}%")))
            ->when($category, fn ($w) => $w->where('article_category_id', $category->id))
            ->orderByDesc('published_at')->get();

        $featured = ($q === '' && ! $category) ? Article::published()->where('school_id', $schoolId)->where('is_featured', true)->with(['category', 'author.counselorProfile'])->latest('published_at')->first() : null;

        return view('student.selfhelp.index', compact('articles', 'categories', 'category', 'featured', 'q'));
    }

    public function show(Request $request, Article $article): View
    {
        abort_unless($article->status === 'published', 404);
        $this->ensureSameSchool($article);

        $viewed = $request->session()->get('viewed_articles', []);
        if (! in_array($article->id, $viewed, true)) {
            $article->increment('view_count');
            $request->session()->push('viewed_articles', $article->id);
        }

        return view('student.selfhelp.show', [
            'article' => $article->load(['category', 'author.counselorProfile']),
            'related' => Article::published()->where('school_id', $article->school_id)->where('article_category_id', $article->article_category_id)->where('id', '!=', $article->id)->with('category')->latest('published_at')->limit(3)->get(),
        ]);
    }
}
