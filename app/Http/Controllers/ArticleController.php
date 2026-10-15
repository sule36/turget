<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ArticleController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Article::published()->with(['category', 'author']);

        if ($request->filled('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->category));
        }

        $articles = $query->latest('published_at')->paginate(9)->withQueryString();
        $categories = ArticleCategory::orderBy('sort_order')->get();

        return Inertia::render('Articles/Index', [
            'articles' => $articles,
            'categories' => $categories,
            'currentCategory' => $request->category,
        ]);
    }

    public function show(Article $article): Response
    {
        $article->load(['category', 'author', 'destinations', 'regions']);

        $related = Article::published()
            ->where('id', '!=', $article->id)
            ->latest('published_at')
            ->take(3)
            ->get();

        return Inertia::render('Articles/Show', [
            'article' => $article,
            'related' => $related,
        ]);
    }
}
