<?php

namespace App\Http\Controllers;

use App\Models\AdministrativeRegion;
use App\Models\Destination;
use App\Models\DestinationCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Autocomplete endpoint for header and hero search inputs.
     */
    public function autocomplete(Request $request): JsonResponse
    {
        $q = trim((string) $request->input('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $term = '%'.mb_strtolower($q).'%';
        $locale = app()->getLocale();

        // 1. Destinations
        $destinations = Destination::published()
            ->where(function ($query) use ($term) {
                $query->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.id'))) LIKE ?", [$term])
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.en'))) LIKE ?", [$term])
                    ->orWhereRaw("LOWER(address) LIKE ?", [$term])
                    ->orWhere('slug', 'like', $term);
            })
            ->with('region')
            ->take(5)
            ->get()
            ->map(fn ($d) => [
                'type' => 'destination',
                'title' => $d->tr('name', $locale),
                'subtitle' => $d->region?->tr('name', $locale) ?? '',
                'url' => $d->publicPath(),
                'image' => $d->cover_image,
                'badge' => $locale === 'id' ? 'Destinasi' : 'Destination',
            ]);

        // 2. Regions (Provinces / Cities)
        $regions = AdministrativeRegion::published()
            ->where(function ($query) use ($term) {
                $query->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.id'))) LIKE ?", [$term])
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.en'))) LIKE ?", [$term])
                    ->orWhere('slug', 'like', $term);
            })
            ->with('country')
            ->take(4)
            ->get()
            ->map(fn ($r) => [
                'type' => 'region',
                'title' => $r->tr('name', $locale),
                'subtitle' => $r->typeLabel($locale).' — '.$r->country?->tr('name', $locale),
                'url' => $r->publicPath(),
                'image' => $r->cover_image,
                'badge' => $r->typeLabel($locale),
            ]);

        // 3. Categories
        $categories = DestinationCategory::where(function ($query) use ($term) {
            $query->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.id'))) LIKE ?", [$term])
                ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.en'))) LIKE ?", [$term])
                ->orWhere('slug', 'like', $term);
        })
            ->take(3)
            ->get()
            ->map(fn ($c) => [
                'type' => 'category',
                'title' => $c->tr('name', $locale),
                'subtitle' => $locale === 'id' ? 'Kategori Wisata' : 'Travel Category',
                'url' => '/destinations?category='.$c->slug,
                'image' => null,
                'badge' => $locale === 'id' ? 'Kategori' : 'Category',
            ]);

        return response()->json([
            'results' => $destinations->concat($regions)->concat($categories)->values(),
        ]);
    }
}
