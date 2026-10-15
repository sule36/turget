<?php

namespace App\Http\Controllers;

use App\Models\AdministrativeRegion;
use App\Models\Country;
use App\Models\Destination;
use App\Models\DestinationCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DestinationController extends Controller
{
    /**
     * Destinations catalog with filtering by category, region, and search term.
     */
    public function index(Request $request): Response
    {
        $query = Destination::published()
            ->with(['region.parent', 'categories']);

        if ($request->filled('q')) {
            $term = '%'.$request->q.'%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.id')) LIKE ?", [$term])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.en')) LIKE ?", [$term])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(summary, '$.id')) LIKE ?", [$term])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(summary, '$.en')) LIKE ?", [$term])
                    ->orWhere('address', 'like', $term);
            });
        }

        if ($request->filled('category')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        if ($request->filled('region')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('region', function ($r) use ($request) {
                    $r->where('slug', $request->region)
                        ->orWhereHas('parent', fn ($p) => $p->where('slug', $request->region));
                });
            });
        }

        $sort = $request->get('sort', 'popular');
        match ($sort) {
            'newest' => $query->latest('published_at'),
            'name' => $query->orderByRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.id')) ASC"),
            default => $query->orderByDesc('popularity_score'),
        };

        $destinations = $query->paginate(9)->withQueryString();

        $categories = DestinationCategory::orderBy('sort_order')->get();
        $regions = AdministrativeRegion::published()->topLevel()->orderBy('sort_order')->get();

        return Inertia::render('Destinations/Index', [
            'destinations' => $destinations,
            'categories' => $categories,
            'regions' => $regions,
            'filters' => [
                'q' => $request->q ?? '',
                'category' => $request->category ?? '',
                'region' => $request->region ?? '',
                'sort' => $sort,
            ],
        ]);
    }

    /**
     * Country detail page (PRD §5.2).
     */
    public function country(Country $country): Response
    {
        $country->load([
            'topRegions' => fn ($q) => $q->published()->withCount('destinations')->orderBy('sort_order'),
        ]);

        $featuredDestinations = Destination::published()
            ->where('country_id', $country->id)
            ->with(['region.parent', 'categories'])
            ->orderByDesc('popularity_score')
            ->take(6)
            ->get();

        return Inertia::render('Destinations/Country', [
            'country' => $country,
            'featuredDestinations' => $featuredDestinations,
        ]);
    }

    /**
     * Region / City detail page (PRD §5.3, §5.4).
     * Supports nested paths like /destinations/indonesia/bali or /destinations/indonesia/bali/badung.
     */
    public function region(Country $country, string $path): Response
    {
        $segments = explode('/', trim($path, '/'));
        $targetSlug = end($segments);

        $region = AdministrativeRegion::published()
            ->where('country_id', $country->id)
            ->where('slug', $targetSlug)
            ->with(['parent', 'children' => fn ($q) => $q->published()->withCount('destinations')])
            ->firstOrFail();

        // Get destinations for this region (or this region's child cities if it's a province)
        $destinations = Destination::published()
            ->where(function ($q) use ($region) {
                $q->where('region_id', $region->id)
                    ->orWhereIn('region_id', $region->children->pluck('id'));
            })
            ->with(['categories', 'region.parent'])
            ->orderByDesc('popularity_score')
            ->get();

        return Inertia::render('Destinations/Region', [
            'country' => $country,
            'region' => $region,
            'ancestors' => $region->ancestors(),
            'destinations' => $destinations,
        ]);
    }

    /**
     * Destination / Attraction detail page (PRD §5.5, §10.2).
     */
    public function show(Destination $destination, \App\Services\VendorRecommendationService $recommendationService): Response
    {
        $destination->load([
            'country',
            'region.parent',
            'categories',
            'media',
        ]);

        // Increment popularity counter lightly
        $destination->increment('popularity_score');

        // Nearby destinations (PRD §10.2) using real Haversine distance
        $nearbyDestinations = [];
        $nearbyVendorServices = [];

        if ($destination->hasValidCoordinates()) {
            $nearbyDestinations = Destination::published()
                ->where('id', '!=', $destination->id)
                ->near((float) $destination->latitude, (float) $destination->longitude, 50.0)
                ->with(['categories', 'region'])
                ->take(4)
                ->get();

            $nearbyVendorServices = $recommendationService->getRecommendationsForDestination($destination);
        }

        return Inertia::render('Destinations/Show', [
            'destination' => $destination,
            'nearbyDestinations' => $nearbyDestinations,
            'nearbyVendorServices' => $nearbyVendorServices,
        ]);
    }
}
