<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\TourPackage;
use App\Models\VendorProduct;
use App\Services\PaymentAndEscrowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BookingCheckoutController extends Controller
{
    public function __construct(
        protected PaymentAndEscrowService $paymentService
    ) {}

    /**
     * Checkout page for fixed Tour Package + optional Vendor Add-ons (PRD §8.2, §14).
     */
    public function checkoutPackage(Request $request, TourPackage $package): Response
    {
        $package->load([
            'guide.user',
            'guide.region',
            'destination',
            'vendorAddons' => fn ($q) => $q->published()->with('vendor:id,business_name'),
        ]);

        return Inertia::render('Bookings/Checkout', [
            'itemType' => 'package',
            'item' => $package,
            'defaultPrice' => (float) $package->base_price,
            'platformFeeRate' => PaymentAndEscrowService::PLATFORM_COMMISSION_PERCENT,
        ]);
    }

    /**
     * Checkout page for direct Vendor Product (Stay, Car Rental, Boat, Activity).
     */
    public function checkoutVendorProduct(Request $request, VendorProduct $product): Response
    {
        $product->load(['vendor.region', 'destination']);

        return Inertia::render('Bookings/Checkout', [
            'itemType' => 'vendor_product',
            'item' => $product,
            'defaultPrice' => (float) $product->price,
            'platformFeeRate' => PaymentAndEscrowService::PLATFORM_COMMISSION_PERCENT,
        ]);
    }

    /**
     * Store Package booking.
     */
    public function storePackageBooking(Request $request, TourPackage $package): RedirectResponse
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'participants_count' => ['required', 'integer', 'min:1'],
            'addons' => ['nullable', 'array'],
            'special_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $booking = $this->paymentService->createBookingFromPackage($package, $request->user(), $validated);

        return redirect()->route('bookings.payment', $booking);
    }

    /**
     * Store Vendor Product booking.
     */
    public function storeVendorBooking(Request $request, VendorProduct $product): RedirectResponse
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'quantity' => ['required', 'integer', 'min:1'],
            'participants_count' => ['required', 'integer', 'min:1'],
            'special_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $booking = $this->paymentService->createBookingFromVendorProduct($product, $request->user(), $validated);

        return redirect()->route('bookings.payment', $booking);
    }

    /**
     * Payment Simulator Page (PRD §15.1).
     */
    public function paymentPage(Request $request, Booking $booking): Response|RedirectResponse
    {
        $user = $request->user();
        if ($booking->traveler_id !== $user->id) {
            abort(403);
        }

        if ($booking->status !== \App\Enums\BookingStatus::PendingPayment) {
            return redirect()->route('bookings.show', $booking);
        }

        $booking->load(['tourPackage', 'vendorProduct', 'tripProposal', 'guide', 'vendor', 'addons.vendorProduct']);

        return Inertia::render('Bookings/Payment', [
            'booking' => $booking,
        ]);
    }

    /**
     * Instant Payment Simulation (Midtrans/Sandbox Gateway).
     */
    public function simulatePaymentSuccess(Request $request, Booking $booking): RedirectResponse
    {
        $user = $request->user();
        if ($booking->traveler_id !== $user->id) {
            abort(403);
        }

        $method = $request->input('payment_method', 'qris');

        $this->paymentService->processPaymentSuccess($booking, 'sandbox', $method);

        return redirect()->route('bookings.show', $booking)->with('success', 'Pembayaran berhasil! Dana Anda tersimpan aman dalam Escrow Turget.id dan baru akan diteruskan ke pemandu setelah trip selesai.');
    }

    /**
     * Booking Voucher & Escrow Status (PRD §14.2, §15.2).
     */
    public function showBooking(Request $request, Booking $booking): Response
    {
        $user = $request->user();
        if ($booking->traveler_id !== $user->id && ! $user->hasRole('super_admin')) {
            abort(403);
        }

        $booking->load([
            'traveler',
            'guide.user',
            'vendor',
            'tourPackage',
            'vendorProduct',
            'tripProposal',
            'addons.vendorProduct',
            'transactions',
            'escrowLedgers',
            'review',
        ]);

        return Inertia::render('Bookings/Show', [
            'booking' => $booking,
        ]);
    }

    /**
     * Official Printable E-Voucher & Travel Receipt with Escrow Protection Seal.
     */
    public function voucher(Request $request, Booking $booking): Response
    {
        $user = $request->user();
        if ($booking->traveler_id !== $user->id 
            && $booking->guide?->user_id !== $user->id 
            && $booking->vendor?->user_id !== $user->id 
            && ! $user->hasRole('super_admin')) {
            abort(403);
        }

        $booking->load([
            'traveler',
            'guide.region',
            'vendor.region',
            'tourPackage.destination',
            'vendorProduct',
            'tripProposal',
            'addons.vendorProduct',
            'transactions',
            'escrowLedgers',
        ]);

        return Inertia::render('Bookings/Voucher', [
            'booking' => $booking,
        ]);
    }

    /**
     * Traveler's bookings list.
     */
    public function myBookings(Request $request): Response
    {
        $bookings = Booking::where('traveler_id', $request->user()->id)
            ->with(['tourPackage', 'vendorProduct', 'guide', 'vendor', 'review'])
            ->latest('id')
            ->paginate(10);

        return Inertia::render('Bookings/Index', [
            'bookings' => $bookings,
        ]);
    }

    /**
     * Traveler confirms trip completion -> Escrow funds released (PRD §15.2, §16.1).
     */
    public function completeTrip(Request $request, Booking $booking): RedirectResponse
    {
        $user = $request->user();
        if ($booking->traveler_id !== $user->id) {
            abort(403);
        }

        $this->paymentService->releaseEscrowOnCompletion($booking, $user);

        return back()->with('success', 'Trip dikonfirmasi selesai! Terima kasih atas kepercayaan Anda. Dana honor telah dilepaskan dari escrow ke saldo pemandu wisata.');
    }

    /**
     * Traveler requests cancellation / refund (PRD §17).
     */
    public function requestRefund(Request $request, Booking $booking): RedirectResponse
    {
        $user = $request->user();
        if ($booking->traveler_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $this->paymentService->processRefund($booking, $validated['reason'], $user);

        return back()->with('success', 'Pengajuan pembatalan telah diproses sesuai kebijakan refund Turget.id.');
    }
}
