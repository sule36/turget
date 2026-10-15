<?php

use App\Http\Controllers\Admin\AdministrativeRegionController as AdminRegionController;
use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DestinationController as AdminDestinationController;
use App\Http\Controllers\Admin\EscrowLedgerController as AdminEscrowLedgerController;
use App\Http\Controllers\Admin\GuideVerificationController as AdminGuideVerificationController;
use App\Http\Controllers\Admin\PackageModerationController as AdminPackageModerationController;
use App\Http\Controllers\Admin\PayoutModerationController as AdminPayoutModerationController;
use App\Http\Controllers\Admin\RefundModerationController as AdminRefundModerationController;
use App\Http\Controllers\Admin\VendorProductModerationController as AdminVendorProductModerationController;
use App\Http\Controllers\Admin\VendorVerificationController as AdminVendorVerificationController;
use App\Http\Controllers\Admin\WorkflowController as AdminWorkflowController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingCheckoutController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\Guide\DashboardController as GuideDashboardController;
use App\Http\Controllers\Guide\OnboardingController as GuideOnboardingController;
use App\Http\Controllers\Guide\PackageController as GuidePackageController;
use App\Http\Controllers\Guide\TripInquiryBoardController as GuideTripInquiryBoardController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PayoutController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TripInquiryController;
use App\Http\Controllers\TripProposalController;
use App\Http\Controllers\Vendor\DashboardController as VendorDashboardController;
use App\Http\Controllers\Vendor\OnboardingController as VendorOnboardingController;
use App\Http\Controllers\Vendor\ProductController as VendorProductController;
use App\Http\Controllers\Vendor\ProfileController as VendorProfileController;
use App\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Turget.id
|--------------------------------------------------------------------------
*/

// Language Switcher
Route::post('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

// Search Autocomplete
Route::get('/api/search/autocomplete', [SearchController::class, 'autocomplete'])->name('search.autocomplete');

// Public Pages
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/destinations', [DestinationController::class, 'index'])->name('destinations.index');
Route::get('/places/{destination:slug}', [DestinationController::class, 'show'])->name('destinations.show');
Route::get('/destinations/{country:slug}', [DestinationController::class, 'country'])->name('destinations.country');
Route::get('/destinations/{country:slug}/{path}', [DestinationController::class, 'region'])
    ->where('path', '.*')
    ->name('destinations.region');

// Public Tour Guides & Packages (PRD §7 & §8)
Route::get('/guides', [GuideController::class, 'index'])->name('guides.index');
Route::get('/guides/{guide:slug}', [GuideController::class, 'show'])->name('guides.show');
Route::get('/packages/{package:slug}', [GuideController::class, 'showPackage'])->name('packages.show');

Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/articles/{article:slug}', [ArticleController::class, 'show'])->name('articles.show');

// Public Vendors & Local Services Marketplace (PRD §8.2, §9, §10)
Route::get('/vendors', [VendorController::class, 'index'])->name('vendors.index');
Route::get('/vendors/products/{slug}', [VendorController::class, 'showProduct'])->name('vendors.products.show');
Route::get('/vendors/{slug}', [VendorController::class, 'show'])->name('vendors.show');
Route::get('/api/destinations/{destination}/recommendations', [VendorController::class, 'recommendationsForDestination'])->name('api.destinations.recommendations');

// Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Authenticated Routes: Trips, Chat, Bookings, Escrow, Payouts
Route::middleware('auth')->group(function () {
    // Custom Trip Planner & Proposals (PRD §11, §12)
    Route::get('/trips/plan', [TripInquiryController::class, 'create'])->name('trips.create');
    Route::post('/trips/plan', [TripInquiryController::class, 'store'])->name('trips.store');
    Route::get('/trips/{trip}', [TripInquiryController::class, 'show'])->name('trips.show');
    Route::get('/my-trips', [TripInquiryController::class, 'myTrips'])->name('trips.my');

    Route::post('/trips/{trip}/proposals', [TripProposalController::class, 'store'])->name('proposals.store');
    Route::post('/proposals/{proposal}/accept', [TripProposalController::class, 'accept'])->name('proposals.accept');
    Route::post('/proposals/{proposal}/reject', [TripProposalController::class, 'reject'])->name('proposals.reject');

    // In-App Chat & Anti-Off-Platform Protection (PRD §13)
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{conversation}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/{conversation}/messages', [ChatController::class, 'sendMessage'])->name('chat.messages.send');
    Route::post('/chat/start', [ChatController::class, 'start'])->name('chat.start');

    // Bookings, Checkout, Payment Gateway & Escrow (PRD §14, §15, §17)
    Route::get('/checkout/package/{package:slug}', [BookingCheckoutController::class, 'checkoutPackage'])->name('checkout.package');
    Route::post('/checkout/package/{package:slug}', [BookingCheckoutController::class, 'storePackageBooking'])->name('checkout.package.store');
    Route::get('/checkout/vendor-product/{product:slug}', [BookingCheckoutController::class, 'checkoutVendorProduct'])->name('checkout.vendor');
    Route::post('/checkout/vendor-product/{product:slug}', [BookingCheckoutController::class, 'storeVendorBooking'])->name('checkout.vendor.store');

    Route::get('/bookings/{booking}/payment', [BookingCheckoutController::class, 'paymentPage'])->name('bookings.payment');
    Route::post('/bookings/{booking}/pay-sandbox', [BookingCheckoutController::class, 'simulatePaymentSuccess'])->name('bookings.pay_sandbox');
    Route::get('/bookings/{booking}', [BookingCheckoutController::class, 'showBooking'])->name('bookings.show');
    Route::get('/my-bookings', [BookingCheckoutController::class, 'myBookings'])->name('bookings.my');
    Route::post('/bookings/{booking}/complete', [BookingCheckoutController::class, 'completeTrip'])->name('bookings.complete');
    Route::post('/bookings/{booking}/refund', [BookingCheckoutController::class, 'requestRefund'])->name('bookings.refund');

    // Payout Wallet & Balance Withdrawal (PRD §16.2)
    Route::get('/payouts', [PayoutController::class, 'index'])->name('payouts.index');
    Route::post('/payouts', [PayoutController::class, 'store'])->name('payouts.store');

    // Tour Guide Onboarding & Self-Management Portal (PRD §7.1, §12, §18)
    Route::get('/become-a-guide', [GuideOnboardingController::class, 'show'])->name('guide.onboard');
    Route::post('/become-a-guide', [GuideOnboardingController::class, 'store'])->name('guide.onboard.store');

    Route::prefix('guide')->name('guide.')->group(function () {
        Route::get('/dashboard', [GuideDashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [GuideDashboardController::class, 'editProfile'])->name('profile.edit');
        Route::put('/profile', [GuideDashboardController::class, 'updateProfile'])->name('profile.update');
        Route::resource('packages', GuidePackageController::class)->except(['show']);

        // Guide Opportunity / Inquiry Board (PRD §12)
        Route::get('/inquiries', [GuideTripInquiryBoardController::class, 'index'])->name('inquiries.index');
        Route::get('/inquiries/{trip}', [GuideTripInquiryBoardController::class, 'show'])->name('inquiries.show');
    });

    // Vendor Onboarding & Self-Management Portal (PRD §8.2, §9.3)
    Route::get('/become-a-vendor', [VendorOnboardingController::class, 'show'])->name('vendor.onboard');
    Route::post('/become-a-vendor', [VendorOnboardingController::class, 'store'])->name('vendor.onboard.store');

    Route::prefix('vendor')->name('vendor.')->group(function () {
        Route::get('/dashboard', [VendorDashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [VendorProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [VendorProfileController::class, 'update'])->name('profile.update');
        Route::resource('products', VendorProductController::class)->except(['show']);
    });
});

// Admin CMS & Verification Suite (Protected)
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth'])
    ->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::resource('destinations', AdminDestinationController::class);
        Route::resource('regions', AdminRegionController::class)->except(['show', 'destroy']);
        Route::resource('articles', AdminArticleController::class);

        // Guide Verification & Package Moderation (PRD §7.1, §7.5, §20.1)
        Route::get('/guides', [AdminGuideVerificationController::class, 'index'])->name('guides.index');
        Route::get('/guides/{guide}', [AdminGuideVerificationController::class, 'show'])->name('guides.show');
        Route::post('/guides/{guide}/action', [AdminGuideVerificationController::class, 'action'])->name('guides.action');

        Route::get('/packages', [AdminPackageModerationController::class, 'index'])->name('packages.index');
        Route::post('/packages/{package}/action', [AdminPackageModerationController::class, 'action'])->name('packages.action');

        // Vendor Verification & Product Moderation (PRD §8.2, §9, §20.1)
        Route::get('/vendors', [AdminVendorVerificationController::class, 'index'])->name('vendors.index');
        Route::get('/vendors/{vendor}', [AdminVendorVerificationController::class, 'show'])->name('vendors.show');
        Route::post('/vendors/{vendor}/review', [AdminVendorVerificationController::class, 'startReview'])->name('vendors.start_review');
        Route::post('/vendors/{vendor}/approve', [AdminVendorVerificationController::class, 'approve'])->name('vendors.approve');
        Route::post('/vendors/{vendor}/revision', [AdminVendorVerificationController::class, 'requestRevision'])->name('vendors.revision');
        Route::post('/vendors/{vendor}/reject', [AdminVendorVerificationController::class, 'reject'])->name('vendors.reject');
        Route::post('/vendors/{vendor}/suspend', [AdminVendorVerificationController::class, 'suspend'])->name('vendors.suspend');
        Route::post('/vendors/{vendor}/reactivate', [AdminVendorVerificationController::class, 'reactivate'])->name('vendors.reactivate');

        Route::get('/vendor-products', [AdminVendorProductModerationController::class, 'index'])->name('vendor_products.index');
        Route::post('/vendor-products/{product}/approve', [AdminVendorProductModerationController::class, 'approve'])->name('vendor_products.approve');
        Route::post('/vendor-products/{product}/archive', [AdminVendorProductModerationController::class, 'archive'])->name('vendor_products.archive');

        // Escrow Ledgers, Commissions & Financial Moderation (PRD §15.2, §16, §17, §20.1)
        Route::get('/escrow', [AdminEscrowLedgerController::class, 'index'])->name('escrow.index');
        Route::post('/escrow/{ledger}/release', [AdminEscrowLedgerController::class, 'forceRelease'])->name('escrow.release');

        Route::get('/payouts', [AdminPayoutModerationController::class, 'index'])->name('payouts.index');
        Route::post('/payouts/{payout}/approve', [AdminPayoutModerationController::class, 'approve'])->name('payouts.approve');
        Route::post('/payouts/{payout}/reject', [AdminPayoutModerationController::class, 'reject'])->name('payouts.reject');

        Route::get('/refunds', [AdminRefundModerationController::class, 'index'])->name('refunds.index');
        Route::post('/refunds/{refund}/process', [AdminRefundModerationController::class, 'process'])->name('refunds.process');

        Route::post('/workflow/{type}/{id}', [AdminWorkflowController::class, 'transition'])
            ->name('workflow.transition');
    });

