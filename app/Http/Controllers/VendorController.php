<?php

namespace App\Http\Controllers;

use App\Enums\ListingStatus;
use App\Enums\VendorCategory;
use App\Models\AdministrativeRegion;
use App\Models\Destination;
use App\Models\VendorProduct;
use App\Models\VendorProfile;
use App\Services\VendorRecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VendorController extends Controller
{
    /**
     * Public Marketplace Directory of Verified Local Vendors & Services (PRD §8.2, §9).
     */
    public function index(Request $request): Response
    {
        $viewMode = $request->get('view', 'products'); // 'products' or 'vendors'
        $category = $request->get('category');
        $regionId = $request->get('region');
        $search = $request->get('q');
        $sort = $request->get('sort', 'recommended');

        $categoriesList = array_map(fn (VendorCategory $c) => [
            'value' => $c->value,
            'label_id' => $c->label('id'),
            'label_en' => $c->label('en'),
            'icon' => $c->icon(),
        ], VendorCategory::cases());

        $regions = AdministrativeRegion::topLevel()->with('children')->get();

        if ($viewMode === 'vendors') {
            $query = VendorProfile::verified()
                ->with(['region', 'country', 'products' => function ($q) {
                    $q->published()->take(3);
                }]);

            if (filled($category)) {
                $query->where('category', $category);
            }

            if (filled($regionId)) {
                $query->where('region_id', (int) $regionId);
            }

            if (filled($search)) {
                $term = '%'.mb_strtolower($search).'%';
                $query->where(function ($q) use ($term) {
                    $q->whereRaw("LOWER(business_name) LIKE ?", [$term])
                        ->orWhereRaw("LOWER(owner_name) LIKE ?", [$term])
                        ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(description, '$.id'))) LIKE ?", [$term])
                        ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(description, '$.en'))) LIKE ?", [$term]);
                });
            }

            if ($sort === 'rating') {
                $query->orderByDesc('rating_avg')->orderByDesc('reviews_count');
            } else {
                $query->orderByDesc('badge_verified')->orderByDesc('bookings_completed_count')->latest('updated_at');
            }

            $vendors = $query->paginate(12)->withQueryString();

            return Inertia::render('Vendors/Index', [
                'viewMode' => 'vendors',
                'vendors' => $vendors,
                'products' => null,
                'categoriesList' => $categoriesList,
                'regions' => $regions,
                'filters' => [
                    'view' => 'vendors',
                    'category' => $category ?? '',
                    'region' => $regionId ?? '',
                    'q' => $search ?? '',
                    'sort' => $sort,
                ],
            ]);
        }

        // Default view: Products / Services Catalog
        $query = VendorProduct::published()
            ->with(['vendor:id,slug,business_name,category,rating_avg,reviews_count,badge_verified,phone,email', 'destination:id,slug,name']);

        if (filled($category)) {
            $query->where('category', $category);
        }

        if (filled($regionId)) {
            $query->whereHas('vendor', function ($vq) use ($regionId) {
                $vq->where('region_id', (int) $regionId);
            });
        }

        if (filled($search)) {
            $term = '%'.mb_strtolower($search).'%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.id'))) LIKE ?", [$term])
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.en'))) LIKE ?", [$term])
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(description, '$.id'))) LIKE ?", [$term])
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(description, '$.en'))) LIKE ?", [$term])
                    ->orWhereHas('vendor', function ($vq) use ($term) {
                        $vq->whereRaw("LOWER(business_name) LIKE ?", [$term]);
                    });
            });
        }

        if ($sort === 'price_asc') {
            $query->orderBy('price');
        } elseif ($sort === 'price_desc') {
            $query->orderByDesc('price');
        } elseif ($sort === 'rating') {
            $query->orderByDesc('rating_avg');
        } else {
            $query->orderByDesc('is_featured')->latest('updated_at');
        }

        $products = $query->paginate(12)->withQueryString();

        return Inertia::render('Vendors/Index', [
            'viewMode' => 'products',
            'vendors' => null,
            'products' => $products,
            'categoriesList' => $categoriesList,
            'regions' => $regions,
            'filters' => [
                'view' => 'products',
                'category' => $category ?? '',
                'region' => $regionId ?? '',
                'q' => $search ?? '',
                'sort' => $sort,
            ],
        ]);
    }

    /**
     * Public Vendor Profile & Catalog (PRD §9.3).
     */
    public function show(string $slug): Response
    {
        $vendor = VendorProfile::where('slug', $slug)
            ->with([
                'region.parent',
                'country',
                'products' => function ($q) {
                    $q->published()->orderByDesc('is_featured')->latest('id');
                },
            ])
            ->firstOrFail();

        $reviews = collect();
        if (\Illuminate\Support\Facades\Schema::hasTable('reviews')) {
            try {
                $vendor->load([
                    'reviews' => function ($q) {
                        $q->published()->with('traveler')->latest('id')->take(10);
                    },
                ]);
                $reviews = $vendor->reviews ?? collect();
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Reviews query skipped for vendor: ' . $e->getMessage());
            }
        }

        // Related / Nearby destinations for this vendor
        $nearbyDestinations = [];
        if ($vendor->latitude && $vendor->longitude) {
            $nearbyDestinations = Destination::published()
                ->near((float) $vendor->latitude, (float) $vendor->longitude, 25.0)
                ->limit(4)
                ->get();
        }

        return Inertia::render('Vendors/Show', [
            'vendor' => $vendor,
            'nearbyDestinations' => $nearbyDestinations,
            'reviews' => $reviews,
        ]);
    }

    /**
     * Public Product & Add-on Details (PRD §8.2, §9.4).
     */
    public function showProduct(string $slug): Response
    {
        $product = VendorProduct::where('slug', $slug)
            ->with([
                'vendor.region.parent',
                'vendor.country',
                'destination',
                'tourPackages' => function ($q) {
                    $q->published()->with('guide')->limit(3);
                },
            ])
            ->firstOrFail();

        // Other offerings by same vendor
        $otherProducts = VendorProduct::published()
            ->where('vendor_profile_id', $product->vendor_profile_id)
            ->where('id', '!=', $product->id)
            ->limit(3)
            ->get();

        // Similar products in same category
        $similarProducts = VendorProduct::published()
            ->where('category', $product->category->value)
            ->where('id', '!=', $product->id)
            ->limit(3)
            ->get();

        return Inertia::render('Vendors/ProductShow', [
            'product' => $product,
            'otherProducts' => $otherProducts,
            'similarProducts' => $similarProducts,
        ]);
    }

    /**
     * API / JSON endpoint: Recommendations for destination (PRD §10.2).
     */
    public function recommendationsForDestination(
        Destination $destination,
        VendorRecommendationService $recommendationService
    ): JsonResponse {
        $recommendations = $recommendationService->getRecommendationsForDestination($destination);

        return response()->json([
            'destination_id' => $destination->id,
            'destination_name' => $destination->name,
            'recommendations' => $recommendations,
        ]);
    }
}
