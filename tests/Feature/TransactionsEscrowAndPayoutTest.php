<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\EscrowStatus;
use App\Enums\PayoutStatus;
use App\Models\Booking;
use App\Models\EscrowLedger;
use App\Models\GuideProfile;
use App\Models\PayoutRequest;
use App\Models\RefundRequest;
use App\Models\TourPackage;
use App\Models\User;
use App\Models\VendorProduct;
use App\Models\VendorProfile;
use App\Services\PaymentAndEscrowService;
use App\Services\PayoutService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TransactionsEscrowAndPayoutTest extends TestCase
{
    use DatabaseTransactions;

    protected User $traveler;
    protected User $adminUser;
    protected User $guideUser;
    protected GuideProfile $guideProfile;
    protected TourPackage $package;
    protected VendorProduct $vendorProduct;

    protected function setUp(): void
    {
        parent::setUp();

        $this->traveler = User::where('email', 'traveler@turget.id')->firstOrFail();
        $this->adminUser = User::where('email', 'admin@turget.id')->firstOrFail();
        $this->guideProfile = GuideProfile::where('badge_verified', true)->with('user')->firstOrFail();
        $this->guideUser = $this->guideProfile->user;
        $this->package = TourPackage::where('guide_profile_id', $this->guideProfile->id)->first() ?? TourPackage::firstOrFail();
        $this->vendorProduct = VendorProduct::firstOrFail();
    }

    public function test_traveler_can_view_package_checkout_page(): void
    {
        $response = $this->actingAs($this->traveler)->get("/checkout/package/{$this->package->slug}");
        $response->assertStatus(200);
    }

    public function test_traveler_can_view_vendor_product_checkout_page(): void
    {
        $response = $this->actingAs($this->traveler)->get("/checkout/vendor-product/{$this->vendorProduct->slug}");
        $response->assertStatus(200);
    }

    public function test_traveler_can_create_booking_from_package(): void
    {
        $payload = [
            'start_date' => now()->addDays(7)->toDateString(),
            'end_date' => now()->addDays(8)->toDateString(),
            'participants_count' => 2,
            'special_notes' => 'Tolong sediakan air mineral dingin.',
        ];

        $response = $this->actingAs($this->traveler)->post("/checkout/package/{$this->package->slug}", $payload);

        $booking = Booking::where('traveler_id', $this->traveler->id)
            ->where('tour_package_id', $this->package->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($booking);
        $this->assertEquals(BookingStatus::PendingPayment, $booking->status);
        $this->assertGreaterThan(0, $booking->total_amount);
        $this->assertGreaterThan(0, $booking->platform_fee);

        $response->assertRedirect("/bookings/{$booking->id}/payment");
    }

    public function test_sandbox_payment_simulation_locks_funds_in_escrow_ledgers(): void
    {
        $booking = Booking::create([
            'booking_code' => 'TRG-TEST-'.uniqid(),
            'traveler_id' => $this->traveler->id,
            'guide_profile_id' => $this->guideProfile->id,
            'tour_package_id' => $this->package->id,
            'booking_type' => 'package',
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDays(6)->toDateString(),
            'participants_count' => 2,
            'subtotal' => 2000000,
            'platform_fee' => 200000,
            'total_amount' => 2200000,
            'currency' => 'IDR',
            'status' => BookingStatus::PendingPayment,
        ]);

        $response = $this->actingAs($this->traveler)->post("/bookings/{$booking->id}/pay-sandbox", [
            'payment_method' => 'qris',
        ]);

        $booking->refresh();
        $this->assertEquals(BookingStatus::PaidEscrow, $booking->status);
        $this->assertNotNull($booking->paid_at);

        // Check Escrow Ledgers created
        $guideLedger = EscrowLedger::where('booking_id', $booking->id)
            ->where('beneficiary_type', 'guide')
            ->first();
        $this->assertNotNull($guideLedger);
        $this->assertEquals(EscrowStatus::Held, $guideLedger->status);
        $this->assertEquals('2000000.00', $guideLedger->amount);

        $response->assertRedirect("/bookings/{$booking->id}");
    }

    public function test_traveler_can_view_booking_voucher_with_escrow_status(): void
    {
        $booking = Booking::where('traveler_id', $this->traveler->id)->firstOrFail();
        $response = $this->actingAs($this->traveler)->get("/bookings/{$booking->id}");
        $response->assertStatus(200);
    }

    public function test_traveler_can_confirm_trip_completed_releasing_escrow_to_guide_wallet(): void
    {
        $booking = Booking::create([
            'booking_code' => 'TRG-CMPL-'.uniqid(),
            'traveler_id' => $this->traveler->id,
            'guide_profile_id' => $this->guideProfile->id,
            'tour_package_id' => $this->package->id,
            'booking_type' => 'package',
            'start_date' => now()->subDays(2)->toDateString(),
            'end_date' => now()->subDays(1)->toDateString(),
            'participants_count' => 2,
            'subtotal' => 3000000,
            'platform_fee' => 300000,
            'total_amount' => 3300000,
            'currency' => 'IDR',
            'status' => BookingStatus::PaidEscrow,
            'paid_at' => now()->subDays(2),
        ]);

        $ledger = EscrowLedger::create([
            'booking_id' => $booking->id,
            'transaction_id' => 1,
            'beneficiary_type' => 'guide',
            'beneficiary_id' => $this->guideProfile->id,
            'amount' => 3000000,
            'fee_deducted' => 0,
            'net_amount' => 3000000,
            'status' => EscrowStatus::Held,
        ]);

        $response = $this->actingAs($this->traveler)->post("/bookings/{$booking->id}/complete");

        $booking->refresh();
        $ledger->refresh();

        $this->assertEquals(BookingStatus::Completed, $booking->status);
        $this->assertEquals(EscrowStatus::Released, $ledger->status);
        $this->assertNotNull($ledger->released_at);

        $response->assertSessionHas('success');
    }

    public function test_guide_can_view_escrow_wallet_and_available_balance(): void
    {
        $response = $this->actingAs($this->guideUser)->get('/payouts');
        $response->assertStatus(200);
    }

    public function test_guide_can_request_payout_withdrawal(): void
    {
        $payoutService = app(PayoutService::class);
        $balance = $payoutService->getGuideBalance($this->guideProfile);

        // Ensure available balance exists for testing
        if ($balance['available'] < 100000) {
            EscrowLedger::create([
                'booking_id' => 1,
                'transaction_id' => 1,
                'beneficiary_type' => 'guide',
                'beneficiary_id' => $this->guideProfile->id,
                'amount' => 500000,
                'fee_deducted' => 0,
                'net_amount' => 500000,
                'status' => EscrowStatus::Released,
                'released_at' => now(),
            ]);
        }

        $response = $this->actingAs($this->guideUser)->post('/payouts', [
            'amount' => 100000,
            'bank_name' => 'BCA',
            'bank_account_number' => '8201992288',
            'bank_account_holder' => 'Wayan Sudarsana',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('payout_requests', [
            'user_id' => $this->guideUser->id,
            'amount' => '100000.00',
            'bank_name' => 'BCA',
            'status' => 'pending',
        ]);
    }

    public function test_admin_can_view_escrow_ledger_and_platform_commission(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/admin/escrow');
        $response->assertStatus(200);
    }

    public function test_admin_can_approve_payout_request(): void
    {
        $payout = PayoutRequest::where('status', PayoutStatus::Pending)->first();
        if (! $payout) {
            $payout = PayoutRequest::create([
                'user_id' => $this->guideUser->id,
                'guide_profile_id' => $this->guideProfile->id,
                'amount' => 250000,
                'bank_name' => 'Mandiri',
                'bank_account_number' => '12345678',
                'bank_account_holder' => 'Guide Name',
                'status' => PayoutStatus::Pending,
            ]);
        }

        $response = $this->actingAs($this->adminUser)->post("/admin/payouts/{$payout->id}/approve", [
            'proof_file_path' => 'TRF-TEST-PROOF-12345',
            'notes' => 'Transfer berhasil via BI-Fast.',
        ]);

        $payout->refresh();
        $this->assertEquals(PayoutStatus::Completed, $payout->status);
        $this->assertNotNull($payout->processed_at);

        $response->assertSessionHas('success');
    }

    public function test_traveler_can_request_refund_and_admin_can_process_dispute(): void
    {
        $booking = Booking::where('status', BookingStatus::PaidEscrow)->firstOrFail();

        $responseTraveler = $this->actingAs($this->traveler)->post("/bookings/{$booking->id}/refund", [
            'reason' => 'Penerbangan dibatalkan karena cuaca buruk di bandara asal.',
        ]);

        $responseTraveler->assertSessionHas('success');

        $refund = RefundRequest::where('booking_id', $booking->id)->latest('id')->first();
        $this->assertNotNull($refund);
        $this->assertEquals('approved', $refund->status);

        // Admin reviews / updates dispute
        $responseAdmin = $this->actingAs($this->adminUser)->post("/admin/refunds/{$refund->id}/process", [
            'status' => 'approved',
            'approved_amount' => 1200000,
            'notes' => 'Disetujui penyesuaian nominal berdasarkan kesepakatan maskapai.',
        ]);

        $responseAdmin->assertSessionHas('success');
        $refund->refresh();
        $this->assertEquals('1200000.00', $refund->approved_amount);
        $this->assertEquals('Disetujui penyesuaian nominal berdasarkan kesepakatan maskapai.', $refund->admin_notes);
    }
}
