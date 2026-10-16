<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\EscrowStatus;
use App\Models\Booking;
use App\Models\Destination;
use App\Models\EscrowLedger;
use App\Models\Favorite;
use App\Models\GuideProfile;
use App\Models\InAppNotification;
use App\Models\TourPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MidtransFavoritesAndNotificationTest extends TestCase
{
    use DatabaseTransactions;

    protected User $traveler;
    protected GuideProfile $guideProfile;
    protected TourPackage $package;
    protected Destination $destination;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.midtrans.server_key' => 'SB-Mid-server-TESTKEY123']);

        $this->traveler = User::where('email', 'traveler@turget.id')->firstOrFail();
        $this->guideProfile = GuideProfile::firstOrFail();
        $this->package = TourPackage::firstOrFail();
        $this->destination = Destination::firstOrFail();
    }

    public function test_midtrans_webhook_processes_settlement_and_updates_escrow(): void
    {
        $serverKey = config('services.midtrans.server_key');

        $booking = Booking::create([
            'traveler_id' => $this->traveler->id,
            'guide_profile_id' => $this->guideProfile->id,
            'tour_package_id' => $this->package->id,
            'booking_code' => 'TRG-PKG-' . strtoupper(bin2hex(random_bytes(3))),
            'booking_type' => 'package',
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDays(6)->toDateString(),
            'participants_count' => 2,
            'subtotal' => 950000,
            'platform_fee' => 50000,
            'total_amount' => 1000000,
            'status' => BookingStatus::PendingPayment,
        ]);

        $orderId = $booking->booking_code;
        $statusCode = '200';
        $grossAmount = number_format((float) $booking->total_amount, 2, '.', '');
        $signature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
            'transaction_id' => 'midtrans-test-' . uniqid(),
            'signature_key' => $signature,
        ];

        $response = $this->postJson('/api/midtrans/webhook', $payload);

        $response->assertStatus(200)
            ->assertJson(['status' => 'ok']);

        $booking->refresh();
        $this->assertEquals(BookingStatus::PaidEscrow, $booking->status);

        $this->assertDatabaseHas('escrow_ledgers', [
            'booking_id' => $booking->id,
            'status' => EscrowStatus::Held->value,
            'amount' => 950000,
        ]);

        $this->assertDatabaseHas('in_app_notifications', [
            'user_id' => $this->traveler->id,
            'type' => 'booking',
        ]);
    }

    public function test_midtrans_webhook_rejects_invalid_signature(): void
    {
        $payload = [
            'order_id' => 'TRG-PKG-INVALID-1234',
            'status_code' => '200',
            'gross_amount' => '500000.00',
            'transaction_status' => 'settlement',
            'signature_key' => 'invalid_sha512_signature',
        ];

        $response = $this->postJson('/api/midtrans/webhook', $payload);

        $response->assertStatus(400)
            ->assertJson(['status' => 'error', 'message' => 'Invalid signature']);
    }

    public function test_traveler_can_toggle_favorite_items(): void
    {
        // Add guide to favorites
        $response = $this->actingAs($this->traveler)
            ->post('/favorites/toggle', [
                'type' => 'guide',
                'id' => $this->guideProfile->id,
            ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('favorites', [
            'user_id' => $this->traveler->id,
            'favoritable_type' => 'guide',
            'favoritable_id' => $this->guideProfile->id,
        ]);

        // Toggle again (remove from favorites)
        $response = $this->actingAs($this->traveler)
            ->post('/favorites/toggle', [
                'type' => 'guide',
                'id' => $this->guideProfile->id,
            ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('favorites', [
            'user_id' => $this->traveler->id,
            'favoritable_type' => 'guide',
            'favoritable_id' => $this->guideProfile->id,
        ]);
    }

    public function test_traveler_can_view_my_favorites_page(): void
    {
        Favorite::create([
            'user_id' => $this->traveler->id,
            'favoritable_type' => 'guide',
            'favoritable_id' => $this->guideProfile->id,
        ]);

        Favorite::create([
            'user_id' => $this->traveler->id,
            'favoritable_type' => 'destination',
            'favoritable_id' => $this->destination->id,
        ]);

        $response = $this->actingAs($this->traveler)->get('/my-favorites');
        $response->assertStatus(200);
    }

    public function test_traveler_can_mark_notification_as_read(): void
    {
        $notification = InAppNotification::create([
            'user_id' => $this->traveler->id,
            'title' => 'Test Notif',
            'message' => 'Halo ini notif uji coba',
            'type' => 'system',
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->traveler)
            ->postJson("/notifications/{$notification->id}/read");

        $response->assertStatus(200)
            ->assertJson(['status' => 'ok']);

        $this->assertTrue($notification->fresh()->is_read);
    }

    public function test_traveler_can_mark_all_notifications_as_read(): void
    {
        InAppNotification::create([
            'user_id' => $this->traveler->id,
            'title' => 'Notif 1',
            'message' => 'Pesan 1',
            'type' => 'system',
            'is_read' => false,
        ]);

        InAppNotification::create([
            'user_id' => $this->traveler->id,
            'title' => 'Notif 2',
            'message' => 'Pesan 2',
            'type' => 'system',
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->traveler)
            ->postJson('/notifications/read-all');

        $response->assertStatus(200)
            ->assertJson(['status' => 'ok']);

        $unreadCount = InAppNotification::where('user_id', $this->traveler->id)
            ->where('is_read', false)
            ->count();

        $this->assertEquals(0, $unreadCount);
    }
}
