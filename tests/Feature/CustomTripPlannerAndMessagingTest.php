<?php

namespace Tests\Feature;

use App\Enums\ProposalStatus;
use App\Enums\TripInquiryStatus;
use App\Models\AdministrativeRegion;
use App\Models\Conversation;
use App\Models\GuideProfile;
use App\Models\Message;
use App\Models\TripInquiry;
use App\Models\TripProposal;
use App\Models\User;
use App\Services\AntiOffPlatformService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CustomTripPlannerAndMessagingTest extends TestCase
{
    use DatabaseTransactions;

    protected User $traveler;
    protected User $guideUser;
    protected GuideProfile $guideProfile;
    protected AdministrativeRegion $region;

    protected function setUp(): void
    {
        parent::setUp();

        $this->traveler = User::where('email', 'traveler@turget.id')->firstOrFail();
        $this->guideProfile = GuideProfile::where('badge_verified', true)->with('user')->firstOrFail();
        $this->guideUser = $this->guideProfile->user;
        $this->region = AdministrativeRegion::where('slug', 'bali')->first() ?? AdministrativeRegion::first();
    }

    public function test_traveler_can_view_trip_planner_page(): void
    {
        $response = $this->actingAs($this->traveler)->get('/trips/plan');
        $response->assertStatus(200);
    }

    public function test_traveler_can_submit_custom_trip_inquiry(): void
    {
        $payload = [
            'title' => 'Eksplorasi Budaya & Snorkeling 3 Hari di Bali',
            'region_id' => $this->region->id,
            'start_date' => now()->addDays(10)->toDateString(),
            'end_date' => now()->addDays(12)->toDateString(),
            'duration_days' => 3,
            'adults_count' => 2,
            'children_count' => 0,
            'budget_min' => 2000000,
            'budget_max' => 5000000,
            'travel_style' => 'cultural',
            'preferences' => [
                'interests' => ['temple', 'beach'],
                'transport' => 'private_car',
                'accommodation' => 'hotel',
            ],
            'special_requests' => 'Membutuhkan tour guide fasih bahasa Inggris.',
        ];

        $response = $this->actingAs($this->traveler)->post('/trips/plan', $payload);

        $inquiry = TripInquiry::where('title', 'Eksplorasi Budaya & Snorkeling 3 Hari di Bali')->first();
        $this->assertNotNull($inquiry);
        $this->assertEquals(TripInquiryStatus::Open, $inquiry->status);
        $this->assertEquals(2, $inquiry->adults_count);

        $response->assertRedirect('/trips/'.$inquiry->id);
    }

    public function test_verified_guide_can_view_bidding_opportunity_board(): void
    {
        $response = $this->actingAs($this->guideUser)->get('/guide/inquiries');
        $response->assertStatus(200);
    }

    public function test_verified_guide_can_submit_proposal_bid(): void
    {
        $inquiry = TripInquiry::create([
            'user_id' => $this->traveler->id,
            'region_id' => $this->region->id,
            'title' => 'Trip Uji Bidding Guide',
            'start_date' => now()->addDays(7)->toDateString(),
            'end_date' => now()->addDays(8)->toDateString(),
            'duration_days' => 2,
            'adults_count' => 2,
            'children_count' => 0,
            'budget_min' => 2000000,
            'budget_max' => 4000000,
            'currency' => 'IDR',
            'travel_style' => 'adventure',
            'status' => TripInquiryStatus::Open,
        ]);

        $proposalPayload = [
            'title' => 'Paket Petualangan Eksklusif bersama Bli Wayan',
            'cover_letter' => 'Halo! Saya pemandu berpengalaman 8 tahun siap menemani petualangan Anda dengan aman dan seru.',
            'itinerary' => [
                ['day' => 1, 'title' => 'Trekking Air Terjun', 'description' => 'Mendaki rute alami ke air terjun tersembunyi.'],
                ['day' => 2, 'title' => 'Rafting Sungai Ayung', 'description' => 'Arung jeram seru dan makan siang prasmanan.'],
            ],
            'price_breakdown' => [
                'guide_fee' => 1200000,
                'transport' => 1000000,
                'tickets' => 800000,
            ],
            'total_amount' => 3000000,
        ];

        $response = $this->actingAs($this->guideUser)->post("/trips/{$inquiry->id}/proposals", $proposalPayload);
        $response->assertSessionHas('success');

        $proposal = TripProposal::where('trip_inquiry_id', $inquiry->id)->first();
        $this->assertNotNull($proposal);
        $this->assertEquals('3000000.00', $proposal->total_amount);
        $this->assertEquals(ProposalStatus::Submitted, $proposal->status);

        // Trip inquiry status transitions to Bidding
        $inquiry->refresh();
        $this->assertEquals(TripInquiryStatus::Bidding, $inquiry->status);
    }

    public function test_unverified_guide_cannot_submit_proposal_bid(): void
    {
        $unverifiedUser = User::factory()->create();
        GuideProfile::create([
            'user_id' => $unverifiedUser->id,
            'country_id' => $this->region->country_id ?? 1,
            'region_id' => $this->region->id,
            'display_name' => 'Unverified Guide',
            'slug' => 'unverified-guide-'.uniqid(),
            'badge_verified' => false,
            'verification_status' => 'submitted',
        ]);

        $inquiry = TripInquiry::first();

        $response = $this->actingAs($unverifiedUser)->post("/trips/{$inquiry->id}/proposals", [
            'title' => 'Proposal Tanpa Lisensi',
            'cover_letter' => 'Saya belum terverifikasi tapi mau coba.',
            'itinerary' => [['day' => 1, 'title' => 'Jalan']],
            'price_breakdown' => ['guide_fee' => 500000],
            'total_amount' => 500000,
        ]);

        $response->assertStatus(403);
    }

    public function test_traveler_can_view_trip_with_proposals(): void
    {
        $inquiry = TripInquiry::first();
        $response = $this->actingAs($this->traveler)->get("/trips/{$inquiry->id}");
        $response->assertStatus(200);
    }

    public function test_traveler_can_accept_proposal_and_creates_booking(): void
    {
        $inquiry = TripInquiry::create([
            'user_id' => $this->traveler->id,
            'region_id' => $this->region->id,
            'title' => 'Trip untuk Diterima',
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDays(6)->toDateString(),
            'duration_days' => 2,
            'adults_count' => 2,
            'children_count' => 0,
            'travel_style' => 'cultural',
            'status' => TripInquiryStatus::Bidding,
        ]);

        $proposal = TripProposal::create([
            'trip_inquiry_id' => $inquiry->id,
            'guide_profile_id' => $this->guideProfile->id,
            'title' => 'Penawaran Spesial',
            'cover_letter' => 'Penawaran terbaik dari pemandu lokal resmi.',
            'itinerary' => [['day' => 1, 'title' => 'Hari 1']],
            'price_breakdown' => ['guide_fee' => 2000000],
            'total_amount' => 2000000,
            'currency' => 'IDR',
            'status' => ProposalStatus::Submitted,
        ]);

        $response = $this->actingAs($this->traveler)->post("/proposals/{$proposal->id}/accept");

        $proposal->refresh();
        $this->assertEquals(ProposalStatus::Accepted, $proposal->status);

        // Booking created in PendingPayment
        $this->assertDatabaseHas('bookings', [
            'trip_proposal_id' => $proposal->id,
            'traveler_id' => $this->traveler->id,
            'status' => 'pending_payment',
        ]);

        $response->assertRedirect();
    }

    public function test_traveler_can_reject_proposal(): void
    {
        $inquiry = TripInquiry::create([
            'user_id' => $this->traveler->id,
            'region_id' => $this->region->id,
            'title' => 'Trip untuk Ditolak',
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDays(6)->toDateString(),
            'duration_days' => 2,
            'adults_count' => 2,
            'children_count' => 0,
            'travel_style' => 'cultural',
            'status' => TripInquiryStatus::Bidding,
        ]);

        $proposal = TripProposal::create([
            'trip_inquiry_id' => $inquiry->id,
            'guide_profile_id' => $this->guideProfile->id,
            'title' => 'Penawaran Mahal',
            'cover_letter' => 'Terlalu mahal untuk budget.',
            'itinerary' => [['day' => 1, 'title' => 'Hari 1']],
            'price_breakdown' => ['guide_fee' => 10000000],
            'total_amount' => 10000000,
            'currency' => 'IDR',
            'status' => ProposalStatus::Submitted,
        ]);

        $response = $this->actingAs($this->traveler)->post("/proposals/{$proposal->id}/reject");
        $response->assertSessionHas('info');

        $proposal->refresh();
        $this->assertEquals(ProposalStatus::Rejected, $proposal->status);
    }

    public function test_in_app_chat_start_and_message_sending(): void
    {
        $responseStart = $this->actingAs($this->traveler)->post('/chat/start', [
            'target_user_id' => $this->guideUser->id,
        ]);

        $conversation = Conversation::where('user_one_id', $this->traveler->id)
            ->where('user_two_id', $this->guideUser->id)
            ->first();

        $this->assertNotNull($conversation);
        $responseStart->assertRedirect("/chat/{$conversation->id}");

        // Send safe message
        $responseMsg = $this->actingAs($this->traveler)->post("/chat/{$conversation->id}/messages", [
            'message' => 'Halo Bli Wayan, apakah paket ini sudah termasuk tiket masuk pura?',
        ]);

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $this->traveler->id,
            'is_flagged' => false,
        ]);
    }

    public function test_guest_is_redirected_to_login_when_initiating_chat(): void
    {
        $response = $this->post('/chat/start', [
            'target_user_id' => $this->guideUser->id,
        ]);
        $response->assertRedirect('/login');
    }

    public function test_anti_off_platform_service_masks_phone_and_whatsapp(): void
    {
        $antiLeak = new AntiOffPlatformService();

        // Indonesian mobile phone number detection
        $phoneTest = $antiLeak->inspect('Silakan hubungi saya di nomor 08123456789 ya');
        $this->assertTrue($phoneTest['is_flagged']);
        $this->assertContains('phone_number', $phoneTest['flagged_reasons']);
        $this->assertStringContainsString('[Kontak disembunyikan demi keamanan transaksi Turget.id]', $phoneTest['clean_body']);
        $this->assertStringNotContainsString('08123456789', $phoneTest['clean_body']);

        // WhatsApp link detection
        $waTest = $antiLeak->inspect('Langsung WA ke wa.me/6281299990000');
        $this->assertTrue($waTest['is_flagged']);
        $this->assertContains('whatsapp_link', $waTest['flagged_reasons']);
        $this->assertStringNotContainsString('wa.me', $waTest['clean_body']);

        // Email address detection
        $emailTest = $antiLeak->inspect('Kirim email ke pandu.bali@gmail.com');
        $this->assertTrue($emailTest['is_flagged']);
        $this->assertContains('email_address', $emailTest['flagged_reasons']);
        $this->assertStringNotContainsString('pandu.bali@gmail.com', $emailTest['clean_body']);

        // Spaced phone number bypass attempt (0 8 1 2 ...)
        $spacedPhoneTest = $antiLeak->inspect('hubungi saya di 0 8 1 2 3 4 5 6 7 8 9 ya');
        $this->assertTrue($spacedPhoneTest['is_flagged']);
        $this->assertContains('phone_number', $spacedPhoneTest['flagged_reasons']);
        $this->assertStringContainsString('[Kontak disembunyikan demi keamanan transaksi Turget.id]', $spacedPhoneTest['clean_body']);

        // Direct bank transfer bypass attempt
        $bankTest = $antiLeak->inspect('bisa transfer bca 8830123456 langsung ke saya');
        $this->assertTrue($bankTest['is_flagged']);
        $this->assertContains('bank_payment_bypass', $bankTest['flagged_reasons']);
        $this->assertStringContainsString('[Kontak disembunyikan demi keamanan transaksi Turget.id]', $bankTest['clean_body']);

        // Social media handle attempt
        $socialTest = $antiLeak->inspect('kontak saya via ig: @bali_tourguide_official');
        $this->assertTrue($socialTest['is_flagged']);
        $this->assertContains('social_media_contact', $socialTest['flagged_reasons']);
        $this->assertStringContainsString('[Kontak disembunyikan demi keamanan transaksi Turget.id]', $socialTest['clean_body']);

        // Spelled-out phone number attempt
        $spelledTest = $antiLeak->inspect('nomor saya nol delapan satu dua tiga empat lima enam tujuh delapan');
        $this->assertTrue($spelledTest['is_flagged']);
        $this->assertContains('phone_number', $spelledTest['flagged_reasons']);
        $this->assertStringContainsString('[Kontak disembunyikan demi keamanan transaksi Turget.id]', $spelledTest['clean_body']);

        // Clean message passes unflagged
        $cleanTest = $antiLeak->inspect('Kita bertemu di lobi hotel jam 8 pagi dengan pakaian nyaman.');
        $this->assertFalse($cleanTest['is_flagged']);
        $this->assertEquals('Kita bertemu di lobi hotel jam 8 pagi dengan pakaian nyaman.', $cleanTest['clean_body']);
    }
}
