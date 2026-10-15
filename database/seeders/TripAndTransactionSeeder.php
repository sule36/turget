<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Enums\EscrowStatus;
use App\Enums\PaymentStatus;
use App\Enums\PayoutStatus;
use App\Enums\ProposalStatus;
use App\Enums\TripInquiryStatus;
use App\Models\AdministrativeRegion;
use App\Models\Booking;
use App\Models\BookingAddon;
use App\Models\Conversation;
use App\Models\EscrowLedger;
use App\Models\GuideProfile;
use App\Models\Message;
use App\Models\PayoutRequest;
use App\Models\RefundRequest;
use App\Models\TourPackage;
use App\Models\Transaction;
use App\Models\TripInquiry;
use App\Models\TripProposal;
use App\Models\User;
use App\Models\VendorProduct;
use App\Models\VendorProfile;
use App\Services\AntiOffPlatformService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TripAndTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $traveler = User::where('email', 'traveler@turget.id')->first();
        if (! $traveler) {
            $traveler = User::firstOrCreate(
                ['email' => 'traveler@turget.id'],
                ['name' => 'Sarah Johnson (Traveler)', 'password' => bcrypt('password'), 'locale' => 'en']
            );
            $traveler->syncRoles(['customer']);
        }

        $guide = GuideProfile::where('badge_verified', true)->with('user')->first();
        $vendor = VendorProfile::where('verification_status', 'verified')->first();
        $baliRegion = AdministrativeRegion::where('slug', 'bali')->first() ?? AdministrativeRegion::first();

        if (! $guide || ! $baliRegion) {
            return;
        }

        // 1. Open Trip Inquiry for Guide Bidding
        $openInquiry = TripInquiry::create([
            'user_id' => $traveler->id,
            'region_id' => $baliRegion->id,
            'title' => 'Bespoke Family Cultural & Hidden Waterfall Tour in Bali (4 Days)',
            'start_date' => now()->addDays(14)->toDateString(),
            'end_date' => now()->addDays(17)->toDateString(),
            'duration_days' => 4,
            'adults_count' => 2,
            'children_count' => 1,
            'budget_min' => 4000000,
            'budget_max' => 9000000,
            'currency' => 'IDR',
            'travel_style' => 'cultural',
            'preferences' => [
                'interests' => ['temple', 'trekking', 'culinary'],
                'transport' => 'private_car',
                'accommodation' => 'hotel',
            ],
            'special_requests' => 'Traveling with our 7-year-old daughter. Prefer kid-friendly nature walks, authentic Balinese cooking session, and an English-fluent licensed guide.',
            'status' => TripInquiryStatus::Bidding,
        ]);

        // 2. Guide Proposal Bid
        $proposal = TripProposal::create([
            'trip_inquiry_id' => $openInquiry->id,
            'guide_profile_id' => $guide->id,
            'title' => 'Curated East Bali Heritage, Ancient Water Palace & Secret Falls with Wayan',
            'cover_letter' => "Hello Sarah! As a licensed HPI tour guide born and raised in Bali, I would love to host your family. I have tailored a comfortable 4-day pace suitable for your daughter, including private AC transport, cold mineral water, temple sarongs, and a private cooking masterclass with my family.",
            'itinerary' => [
                ['day' => 1, 'title' => 'Airport Pickup & Cultural Orientation in Ubud', 'description' => 'Pick up at DPS Airport, settle into hotel, afternoon stroll through scenic Campuhan Ridge walk.'],
                ['day' => 2, 'title' => 'Tirta Empul Holy Springs & Traditional Organic Farm', 'description' => 'Morning water purification temple tour followed by authentic family lunch and herb picking.'],
                ['day' => 3, 'title' => 'Tukad Cepung Hidden Waterfall & Besakih Mother Temple', 'description' => 'Sunlight cave waterfall exploration with easy bamboo pathway suitable for children.'],
                ['day' => 4, 'title' => 'Sanur Beach Sunrise & Local Artisan Handicraft Market', 'description' => 'Relaxed cycling along Sanur beach path and souvenir hunt before airport drop-off.'],
            ],
            'price_breakdown' => [
                'guide_fee' => 2800000,
                'private_transport' => 2400000,
                'admission_and_donations' => 950000,
                'family_cooking_lunch' => 850000,
            ],
            'total_amount' => 7000000,
            'currency' => 'IDR',
            'valid_until' => now()->addDays(7),
            'status' => ProposalStatus::Submitted,
        ]);

        // 3. In-App Conversation with Anti-Off-Platform Masking Demonstration
        $antiLeak = new AntiOffPlatformService();
        $conversation = Conversation::create([
            'user_one_id' => $traveler->id,
            'user_two_id' => $guide->user_id,
            'trip_inquiry_id' => $openInquiry->id,
            'last_message_at' => now(),
        ]);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $traveler->id,
            'original_body' => 'Hi Wayan! We loved your proposal. Can we add a short stop at a coffee plantation on Day 2?',
            'clean_body' => 'Hi Wayan! We loved your proposal. Can we add a short stop at a coffee plantation on Day 2?',
            'is_flagged' => false,
            'flagged_reasons' => [],
        ]);

        $rawGuideReply = 'Tentu bisa Sarah! Untuk koordinasi cepat silakan chat WA saya di 081234567890 atau langsung hubungi wa.me/6281234567890 ya!';
        $inspected = $antiLeak->inspect($rawGuideReply);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $guide->user_id,
            'original_body' => $rawGuideReply,
            'clean_body' => $inspected['clean_body'],
            'is_flagged' => $inspected['is_flagged'],
            'flagged_reasons' => $inspected['flagged_reasons'],
        ]);

        // 4. Booking 1: Paid Escrow with Vendor Add-on
        $package = TourPackage::where('guide_profile_id', $guide->id)->first() ?? TourPackage::first();
        $addonProduct = VendorProduct::first();

        $bookingCode = 'TRG-'.now()->format('Ymd').'-'.strtoupper(Str::random(4));
        $subtotal = 3200000;
        $platformFee = $subtotal * 0.10; // 320,000
        $totalAmount = $subtotal + $platformFee;

        $activeBooking = Booking::create([
            'booking_code' => $bookingCode,
            'traveler_id' => $traveler->id,
            'guide_profile_id' => $guide->id,
            'tour_package_id' => $package?->id,
            'booking_type' => 'package',
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDays(6)->toDateString(),
            'participants_count' => 2,
            'subtotal' => $subtotal,
            'platform_fee' => $platformFee,
            'discount' => 0,
            'total_amount' => $totalAmount,
            'currency' => 'IDR',
            'status' => BookingStatus::PaidEscrow,
            'special_notes' => 'Pickup at Seminyak hotel 08:00 AM.',
            'paid_at' => now(),
        ]);

        if ($addonProduct) {
            BookingAddon::create([
                'booking_id' => $activeBooking->id,
                'vendor_product_id' => $addonProduct->id,
                'quantity' => 1,
                'unit_price' => 700000,
                'total_price' => 700000,
            ]);
        }

        $trx = Transaction::create([
            'booking_id' => $activeBooking->id,
            'transaction_code' => 'TX-'.strtoupper(Str::random(8)),
            'payment_gateway' => 'sandbox',
            'payment_method' => 'qris',
            'gateway_reference' => 'SANDBOX-'.now()->timestamp,
            'amount' => $totalAmount,
            'platform_fee' => $platformFee,
            'status' => PaymentStatus::Settlement,
            'payload' => ['simulated' => true],
            'paid_at' => now(),
        ]);

        // Escrow Ledgers (Held)
        EscrowLedger::create([
            'booking_id' => $activeBooking->id,
            'transaction_id' => $trx->id,
            'beneficiary_type' => 'guide',
            'beneficiary_id' => $guide->id,
            'amount' => 2500000,
            'fee_deducted' => 250000,
            'net_amount' => 2250000,
            'status' => EscrowStatus::Held,
            'notes' => 'Honor paket pemandu ditahan di Escrow Turget hingga trip tuntas.',
        ]);

        if ($addonProduct && $addonProduct->vendor_profile_id) {
            EscrowLedger::create([
                'booking_id' => $activeBooking->id,
                'transaction_id' => $trx->id,
                'beneficiary_type' => 'vendor',
                'beneficiary_id' => $addonProduct->vendor_profile_id,
                'amount' => 700000,
                'fee_deducted' => 70000,
                'net_amount' => 630000,
                'status' => EscrowStatus::Held,
                'notes' => 'Add-on vendor ditahan di Escrow Turget.',
            ]);
        }

        EscrowLedger::create([
            'booking_id' => $activeBooking->id,
            'transaction_id' => $trx->id,
            'beneficiary_type' => 'platform',
            'beneficiary_id' => null,
            'amount' => $platformFee,
            'fee_deducted' => 0,
            'net_amount' => $platformFee,
            'status' => EscrowStatus::Held,
            'notes' => 'Komisi platform 10% Turget.id.',
        ]);

        // 5. Booking 2: Completed Booking where Escrow was Released
        $completedBooking = Booking::create([
            'booking_code' => 'TRG-'.now()->subDays(10)->format('Ymd').'-CMPL',
            'traveler_id' => $traveler->id,
            'guide_profile_id' => $guide->id,
            'tour_package_id' => $package?->id,
            'booking_type' => 'package',
            'start_date' => now()->subDays(8)->toDateString(),
            'end_date' => now()->subDays(7)->toDateString(),
            'participants_count' => 2,
            'subtotal' => 2000000,
            'platform_fee' => 200000,
            'discount' => 0,
            'total_amount' => 2200000,
            'currency' => 'IDR',
            'status' => BookingStatus::Completed,
            'paid_at' => now()->subDays(8),
            'completed_at' => now()->subDays(6),
        ]);

        $trxCompleted = Transaction::create([
            'booking_id' => $completedBooking->id,
            'transaction_code' => 'TX-'.strtoupper(Str::random(8)),
            'payment_gateway' => 'sandbox',
            'payment_method' => 'bca_va',
            'gateway_reference' => 'SANDBOX-'.now()->subDays(8)->timestamp,
            'amount' => 2200000,
            'platform_fee' => 200000,
            'status' => PaymentStatus::Settlement,
            'paid_at' => now()->subDays(8),
        ]);

        EscrowLedger::create([
            'booking_id' => $completedBooking->id,
            'transaction_id' => $trxCompleted->id,
            'beneficiary_type' => 'guide',
            'beneficiary_id' => $guide->id,
            'amount' => 2000000,
            'fee_deducted' => 200000,
            'net_amount' => 1800000,
            'status' => EscrowStatus::Released,
            'released_at' => now()->subDays(6),
            'notes' => 'Tuntas dikonfirmasi wisatawan, dana masuk ke saldo pemandu.',
        ]);

        EscrowLedger::create([
            'booking_id' => $completedBooking->id,
            'transaction_id' => $trxCompleted->id,
            'beneficiary_type' => 'platform',
            'beneficiary_id' => null,
            'amount' => 200000,
            'fee_deducted' => 0,
            'net_amount' => 200000,
            'status' => EscrowStatus::Released,
            'released_at' => now()->subDays(6),
            'notes' => 'Komisi 10% platform teralisasi.',
        ]);

        // 6. Payout Request by Guide
        PayoutRequest::create([
            'user_id' => $guide->user_id,
            'guide_profile_id' => $guide->id,
            'amount' => 1000000,
            'currency' => 'IDR',
            'bank_name' => 'BCA',
            'bank_account_number' => '8201928374',
            'bank_account_holder' => $guide->user->name,
            'status' => PayoutStatus::Pending,
        ]);

        // 7. Refund Request Dispute
        $refundBooking = Booking::create([
            'booking_code' => 'TRG-'.now()->format('Ymd').'-RFND',
            'traveler_id' => $traveler->id,
            'guide_profile_id' => $guide->id,
            'tour_package_id' => $package?->id,
            'booking_type' => 'package',
            'start_date' => now()->addDays(10)->toDateString(),
            'end_date' => now()->addDays(11)->toDateString(),
            'participants_count' => 1,
            'subtotal' => 1500000,
            'platform_fee' => 150000,
            'discount' => 0,
            'total_amount' => 1650000,
            'currency' => 'IDR',
            'status' => BookingStatus::PaidEscrow,
            'paid_at' => now(),
        ]);

        RefundRequest::create([
            'booking_id' => $refundBooking->id,
            'traveler_id' => $traveler->id,
            'requested_amount' => 1650000,
            'approved_amount' => 1567500, // 95%
            'deduction_fee' => 82500,
            'reason' => 'Perubahan jadwal penerbangan internasional mendadak dari maskapai.',
            'status' => 'pending',
        ]);
    }
}
