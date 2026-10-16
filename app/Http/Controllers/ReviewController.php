<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Submit verified review and star rating after a trip is completed (PRD §17.3).
     */
    public function store(Request $request, Booking $booking): RedirectResponse
    {
        $user = $request->user();

        // 1. Authorization: Only the booking's traveler can review
        if ($booking->traveler_id !== $user->id) {
            abort(403, 'Hanya wisatawan pemesan yang dapat memberikan ulasan.');
        }

        // 2. Status verification: Trip must be completed
        $isCompleted = ($booking->status === BookingStatus::Completed || $booking->status->value === 'completed' || $booking->status === 'completed');
        if (! $isCompleted) {
            return back()->with('error', 'Ulasan hanya dapat diberikan setelah perjalanan atau layanan selesai.');
        }

        // 3. Prevent duplicate reviews
        if ($booking->review()->exists()) {
            return back()->with('error', 'Anda sudah memberikan ulasan untuk pesanan ini.');
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1500'],
        ]);

        // 4. Create verified review
        Review::create([
            'booking_id' => $booking->id,
            'traveler_id' => $user->id,
            'guide_profile_id' => $booking->guide_profile_id,
            'vendor_profile_id' => $booking->vendor_profile_id,
            'tour_package_id' => $booking->tour_package_id,
            'vendor_product_id' => $booking->vendor_product_id,
            'rating' => (int) $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'is_published' => true,
        ]);

        // 5. Automatically recalculate guide average rating and review counts
        if ($booking->guide_profile_id && $guide = $booking->guide) {
            $avg = Review::where('guide_profile_id', $guide->id)->where('is_published', true)->avg('rating');
            $count = Review::where('guide_profile_id', $guide->id)->where('is_published', true)->count();
            $guide->update([
                'rating_avg' => round((float) $avg, 2),
                'reviews_count' => $count,
            ]);
        }

        // 6. Automatically recalculate vendor average rating and review counts
        if ($booking->vendor_profile_id && $vendor = $booking->vendor) {
            $avg = Review::where('vendor_profile_id', $vendor->id)->where('is_published', true)->avg('rating');
            $count = Review::where('vendor_profile_id', $vendor->id)->where('is_published', true)->count();
            $vendor->update([
                'rating_avg' => round((float) $avg, 2),
                'reviews_count' => $count,
            ]);
        }

        return back()->with('success', 'Terima kasih! Ulasan dan rating bintang Anda telah berhasil diterbitkan.');
    }
}
