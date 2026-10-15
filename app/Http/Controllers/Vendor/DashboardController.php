<?php

namespace App\Http\Controllers\Vendor;

use App\Enums\ListingStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Vendor Partner Dashboard (PRD §8.2, §9).
     */
    public function index(Request $request): Response|RedirectResponse
    {
        $user = $request->user();
        $vendor = $user->vendorProfile()
            ->with(['region', 'verifications' => fn ($q) => $q->latest()])
            ->first();

        if (! $vendor) {
            return redirect()->route('vendor.onboard');
        }

        $productsCount = $vendor->products()->count();
        $publishedProductsCount = $vendor->products()->where('status', ListingStatus::Published)->count();

        $recentProducts = $vendor->products()
            ->with('destination:id,name')
            ->latest()
            ->take(5)
            ->get();

        $latestVerification = $vendor->verifications->first();

        return Inertia::render('Vendor/Dashboard', [
            'vendor' => $vendor,
            'stats' => [
                'total_products' => $productsCount,
                'published_products' => $publishedProductsCount,
                'rating_avg' => (float) $vendor->rating_avg,
                'reviews_count' => $vendor->reviews_count,
                'bookings_completed' => $vendor->bookings_completed_count,
                'verification_status' => $vendor->verification_status->value,
                'verification_label' => $vendor->verification_status->label(app()->getLocale()),
                'badge_verified' => $vendor->badge_verified,
            ],
            'latestVerification' => $latestVerification,
            'recentProducts' => $recentProducts,
        ]);
    }
}
