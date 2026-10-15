<?php

namespace App\Http\Controllers;

use App\Models\AdministrativeRegion;
use App\Models\Article;
use App\Models\Country;
use App\Models\Destination;
use App\Models\DestinationCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(Request $request): Response
    {
        $country = Country::published()->where('slug', 'indonesia')->first();

        $featuredDestinations = Destination::published()
            ->with(['region.parent', 'categories'])
            ->where('is_featured', true)
            ->orderByDesc('popularity_score')
            ->take(6)
            ->get();

        $provinces = AdministrativeRegion::published()
            ->topLevel()
            ->withCount('destinations')
            ->orderBy('sort_order')
            ->take(6)
            ->get();

        $popularCities = AdministrativeRegion::published()
            ->cityLevel()
            ->with('parent')
            ->withCount('destinations')
            ->take(6)
            ->get();

        $categories = DestinationCategory::orderBy('sort_order')->get();

        $articles = Article::published()
            ->with('category')
            ->where('is_featured', true)
            ->latest('published_at')
            ->take(3)
            ->get();

        return Inertia::render('Home', [
            'country' => $country,
            'featuredDestinations' => $featuredDestinations,
            'provinces' => $provinces,
            'popularCities' => $popularCities,
            'categories' => $categories,
            'articles' => $articles,
        ]);
    }
}
