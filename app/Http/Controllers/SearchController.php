<?php

namespace App\Http\Controllers;

use App\Models\AdministrativeRegion;
use App\Models\Destination;
use App\Models\DestinationCategory;
use App\Models\GuideProfile;
use App\Models\TourPackage;
use App\Models\VendorProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Autocomplete endpoint for header and hero search inputs.
     * Searches destinations, verified tour guides, published tour packages, vendors, regions, and categories.
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
            ->take(4)
            ->get()
            ->map(fn ($d) => [
                'type' => 'destination',
                'title' => $d->tr('name', $locale),
                'subtitle' => $d->region?->tr('name', $locale) ?? '',
                'url' => $d->publicPath(),
                'image' => $d->cover_image,
                'badge' => $locale === 'id' ? 'Destinasi' : 'Destination',
            ]);

        // 2. Verified Tour Guides
        $guides = GuideProfile::verified()
            ->where(function ($query) use ($term) {
                $query->where('display_name', 'like', $term)
                    ->orWhere('slug', 'like', $term)
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(tagline, '$.id'))) LIKE ?", [$term])
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(tagline, '$.en'))) LIKE ?", [$term]);
            })
            ->with('region')
            ->take(3)
            ->get()
            ->map(fn ($g) => [
                'type' => 'guide',
                'title' => $g->display_name,
                'subtitle' => '⭐ '.($g->rating_avg ?: '5.0').' • '.$g->trips_completed_count.' trip • '.($g->region?->tr('name', $locale) ?? 'Indonesia'),
                'url' => $g->publicPath(),
                'image' => $g->avatar_url,
                'badge' => 'Tour Guide',
            ]);

        // 3. Published Tour Packages
        $packages = TourPackage::published()
            ->where(function ($query) use ($term) {
                $query->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(title, '$.id'))) LIKE ?", [$term])
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(title, '$.en'))) LIKE ?", [$term])
                    ->orWhere('slug', 'like', $term);
            })
            ->with('guide')
            ->take(3)
            ->get()
            ->map(fn ($p) => [
                'type' => 'package',
                'title' => $p->tr('title', $locale),
                'subtitle' => 'Rp '.number_format((float) $p->base_price, 0, ',', '.').' • '.$p->duration_days.' Hari • '.($p->guide?->display_name ?? 'Guide'),
                'url' => $p->publicPath(),
                'image' => $p->cover_image,
                'badge' => $locale === 'id' ? 'Paket Wisata' : 'Tour Package',
            ]);

        // 4. Verified Vendors
        $vendors = VendorProfile::verified()
            ->where(function ($query) use ($term) {
                $query->where('business_name', 'like', $term)
                    ->orWhere('address', 'like', $term)
                    ->orWhere('slug', 'like', $term);
            })
            ->with('region')
            ->take(3)
            ->get()
            ->map(fn ($v) => [
                'type' => 'vendor',
                'title' => $v->business_name,
                'subtitle' => '⭐ '.($v->rating_avg ?: '5.0').' • '.ucfirst(str_replace('_', ' ', $v->category->value ?? (string) $v->category)).' • '.($v->region?->tr('name', $locale) ?? ''),
                'url' => $v->publicPath(),
                'image' => $v->avatar_url,
                'badge' => 'Vendor',
            ]);

        // 5. Regions (Provinces / Cities)
        $regions = AdministrativeRegion::published()
            ->where(function ($query) use ($term) {
                $query->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.id'))) LIKE ?", [$term])
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.en'))) LIKE ?", [$term])
                    ->orWhere('slug', 'like', $term);
            })
            ->with('country')
            ->take(2)
            ->get()
            ->map(fn ($r) => [
                'type' => 'region',
                'title' => $r->tr('name', $locale),
                'subtitle' => $r->typeLabel($locale).' — '.$r->country?->tr('name', $locale),
                'url' => $r->publicPath(),
                'image' => $r->cover_image,
                'badge' => $r->typeLabel($locale),
            ]);

        // 6. Categories
        $categories = DestinationCategory::where(function ($query) use ($term) {
            $query->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.id'))) LIKE ?", [$term])
                ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.en'))) LIKE ?", [$term])
                ->orWhere('slug', 'like', $term);
        })
            ->take(2)
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
            'results' => $destinations
                ->concat($guides)
                ->concat($packages)
                ->concat($vendors)
                ->concat($regions)
                ->concat($categories)
                ->values(),
        ]);
    }
}
