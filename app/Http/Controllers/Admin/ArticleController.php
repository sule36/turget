<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\AuditLog;
use App\Models\ContentRevision;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ArticleController extends Controller
{
    public function index(): Response
    {
        $articles = Article::with(['category', 'author'])
            ->latest('updated_at')
            ->paginate(15);

        return Inertia::render('Admin/Articles/Index', [
            'articles' => $articles,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Articles/Form', [
            'article' => null,
            'categories' => ArticleCategory::all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'article_category_id' => ['required', 'exists:article_categories,id'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:articles,slug'],
            'title.id' => ['required', 'string', 'max:255'],
            'title.en' => ['nullable', 'string', 'max:255'],
            'excerpt.id' => ['nullable', 'string'],
            'excerpt.en' => ['nullable', 'string'],
            'body.id' => ['required', 'string'],
            'body.en' => ['nullable', 'string'],
            'reading_minutes' => ['nullable', 'integer'],
            'cover_image' => ['nullable', 'string'],
        ]);

        $slug = $validated['slug'] ?? Str::slug($validated['title']['id']);

        $article = Article::create([
            'article_category_id' => $validated['article_category_id'],
            'author_id' => auth()->id(),
            'slug' => $slug,
            'title' => $validated['title'],
            'excerpt' => $validated['excerpt'] ?? null,
            'body' => $validated['body'],
            'reading_minutes' => $validated['reading_minutes'] ?? 5,
            'cover_image' => $validated['cover_image'] ?? null,
            'status' => ContentStatus::Published,
            'published_at' => now(),
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        AuditLog::record('article.create', $article);

        return redirect()->route('admin.articles.index')->with('success', 'Artikel berhasil diterbitkan.');
    }

    public function edit(Article $article): Response
    {
        return Inertia::render('Admin/Articles/Form', [
            'article' => $article,
            'categories' => ArticleCategory::all(),
        ]);
    }

    public function update(Request $request, Article $article): RedirectResponse
    {
        $validated = $request->validate([
            'article_category_id' => ['required', 'exists:article_categories,id'],
            'slug' => ['required', 'string', 'max:255', 'unique:articles,slug,'.$article->id],
            'title.id' => ['required', 'string', 'max:255'],
            'title.en' => ['nullable', 'string', 'max:255'],
            'excerpt.id' => ['nullable', 'string'],
            'excerpt.en' => ['nullable', 'string'],
            'body.id' => ['required', 'string'],
            'body.en' => ['nullable', 'string'],
            'reading_minutes' => ['nullable', 'integer'],
            'cover_image' => ['nullable', 'string'],
        ]);

        $article->update([
            'article_category_id' => $validated['article_category_id'],
            'slug' => $validated['slug'],
            'title' => $validated['title'],
            'excerpt' => $validated['excerpt'] ?? null,
            'body' => $validated['body'],
            'reading_minutes' => $validated['reading_minutes'] ?? 5,
            'cover_image' => $validated['cover_image'] ?? null,
            'updated_by' => auth()->id(),
        ]);

        AuditLog::record('article.update', $article);

        return back()->with('success', 'Artikel berhasil diperbarui.');
    }

    public function destroy(Article $article): RedirectResponse
    {
        $article->delete();
        AuditLog::record('article.delete', $article);

        return redirect()->route('admin.articles.index')->with('success', 'Artikel berhasil dihapus.');
    }
}
