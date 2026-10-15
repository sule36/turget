<?php

namespace App\Services;

use App\Enums\VendorCategory;
use App\Models\Destination;
use App\Models\VendorProduct;
use App\Models\VendorProfile;
use Illuminate\Database\Eloquent\Collection;

class VendorRecommendationService
{
    /**
     * Get nearby vendor services for a destination, grouped by category (PRD §8.2, §10).
     *
     * @return array<string, Collection>
     */
    public function getRecommendationsForDestination(Destination $destination, ?float $customRadiusKm = null): array
    {
        if (! $destination->hasValidCoordinates()) {
            return [];
        }

        $lat = (float) $destination->latitude;
        $lng = (float) $destination->longitude;

        $results = [];

        foreach (VendorCategory::cases() as $category) {
            $radius = $customRadiusKm ?? $category->defaultRadiusKm();

            // Products within radius
            $products = VendorProduct::query()
                ->published()
                ->category($category)
                ->near($lat, $lng, $radius)
                ->with(['vendor:id,slug,business_name,category,rating_avg,reviews_count,badge_verified'])
                ->limit(6)
                ->get();

            // If no products have coordinates directly, fall back to vendor's coordinates
            if ($products->isEmpty()) {
                $vendors = VendorProfile::query()
                    ->verified()
                    ->category($category)
                    ->near($lat, $lng, $radius)
                    ->with(['products' => function ($q) {
                        $q->published()->limit(2);
                    }])
                    ->limit(4)
                    ->get();

                $results[$category->value] = [
                    'category' => $category,
                    'radius_km' => $radius,
                    'items' => $vendors,
                    'is_vendor_level' => true,
                ];
            } else {
                $results[$category->value] = [
                    'category' => $category,
                    'radius_km' => $radius,
                    'items' => $products,
                    'is_vendor_level' => false,
                ];
            }
        }

        return $results;
    }

    /**
     * Search nearby products for tour package add-on attachment.
     */
    public function getAddonCandidates(float $lat, float $lng, ?VendorCategory $category = null, float $radiusKm = 25.0): Collection
    {
        $query = VendorProduct::query()
            ->published()
            ->near($lat, $lng, $radiusKm)
            ->with(['vendor:id,slug,business_name,badge_verified']);

        if ($category) {
            $query->category($category);
        }

        return $query->limit(20)->get();
    }
}
