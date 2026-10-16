<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\AdministrativeRegion;
use App\Models\Booking;
use App\Models\GuideProfile;
use App\Models\Review;
use App\Models\TourPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ReviewsVoucherAndProfileTest extends TestCase
{
    use DatabaseTransactions;

    public function test_traveler_can_view_official_electronic_voucher(): void
    {
        $traveler = User::where('email', 'traveler@turget.id')->firstOrFail();
        $guideProfile = GuideProfile::where('badge_verified', true)->firstOrFail();

        $booking = Booking::create([
            'booking_code' => 'TRG-TEST-VOUCHER',
            'traveler_id' => $traveler->id,
            'guide_profile_id' => $guideProfile->id,
            'bookable_type' => GuideProfile::class,
            'bookable_id' => $guideProfile->id,
            'title' => 'Private Guide in Bali',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'duration_days' => 2,
            'participants_count' => 2,
            'status' => BookingStatus::PaidEscrow,
            'subtotal' => 1500000,
            'platform_fee' => 150000,
            'insurance_fee' => 0,
            'total_amount' => 1650000,
            'contact_name' => 'Traveler Test',
            'contact_email' => $traveler->email,
            'contact_phone' => '081234567890',
        ]);

        $response = $this->actingAs($traveler)
            ->get(route('bookings.voucher', $booking));

        $response->assertOk();
    }

    public function test_traveler_can_submit_review_on_completed_trip_and_rating_is_recalculated(): void
    {
        $traveler = User::where('email', 'traveler@turget.id')->firstOrFail();
        $guideProfile = GuideProfile::where('badge_verified', true)->firstOrFail();

        $booking = Booking::create([
            'booking_code' => 'TRG-REV-001',
            'traveler_id' => $traveler->id,
            'guide_profile_id' => $guideProfile->id,
            'bookable_type' => GuideProfile::class,
            'bookable_id' => $guideProfile->id,
            'title' => 'Sunset Uluwatu Tour',
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->subDays(3)->toDateString(),
            'duration_days' => 2,
            'participants_count' => 2,
            'status' => BookingStatus::Completed,
            'subtotal' => 1000000,
            'platform_fee' => 100000,
            'insurance_fee' => 0,
            'total_amount' => 1100000,
            'contact_name' => $traveler->name,
            'contact_email' => $traveler->email,
            'contact_phone' => '081234567890',
        ]);

        $response = $this->actingAs($traveler)
            ->from("/bookings/{$booking->id}")
            ->post(route('bookings.review', $booking), [
                'rating' => 5,
                'comment' => 'Luar biasa sekali! Pemandu sangat ramah dan paham betul sejarah Bali.',
            ]);

        $response->assertRedirect("/bookings/{$booking->id}");
        $this->assertDatabaseHas('reviews', [
            'booking_id' => $booking->id,
            'traveler_id' => $traveler->id,
            'guide_profile_id' => $guideProfile->id,
            'rating' => 5,
        ]);

        $guideProfile->refresh();
        $this->assertGreaterThan(0, (float) $guideProfile->rating_avg);
        $this->assertGreaterThan(0, $guideProfile->reviews_count);
    }

    public function test_traveler_cannot_review_pending_or_uncompleted_trip(): void
    {
        $traveler = User::where('email', 'traveler@turget.id')->firstOrFail();
        $guideProfile = GuideProfile::where('badge_verified', true)->firstOrFail();

        $booking = Booking::create([
            'booking_code' => 'TRG-REV-PENDING',
            'traveler_id' => $traveler->id,
            'guide_profile_id' => $guideProfile->id,
            'bookable_type' => GuideProfile::class,
            'bookable_id' => $guideProfile->id,
            'title' => 'Pending Trip',
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
            'duration_days' => 2,
            'participants_count' => 1,
            'status' => BookingStatus::PaidEscrow,
            'subtotal' => 500000,
            'platform_fee' => 50000,
            'insurance_fee' => 0,
            'total_amount' => 550000,
            'contact_name' => $traveler->name,
            'contact_email' => $traveler->email,
            'contact_phone' => '081234567890',
        ]);

        $response = $this->actingAs($traveler)
            ->from("/bookings/{$booking->id}")
            ->post(route('bookings.review', $booking), [
                'rating' => 5,
                'comment' => 'Belum berangkat tapi mau review',
            ]);

        $response->assertRedirect("/bookings/{$booking->id}");
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('reviews', [
            'booking_id' => $booking->id,
        ]);
    }

    public function test_traveler_can_update_profile_and_password(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'unique_profile_tester@turget.id',
            'phone' => '0811111111',
            'password' => Hash::make('password123'),
        ]);

        // 1. Update Profile
        $response = $this->actingAs($user)
            ->from('/profile')
            ->patch(route('profile.update'), [
                'name' => 'Updated Name',
                'email' => 'unique_profile_tester@turget.id',
                'phone' => '0899999999',
            ]);

        $response->assertRedirect('/profile');
        $user->refresh();
        $this->assertEquals('Updated Name', $user->name);
        $this->assertEquals('0899999999', $user->phone);

        // 2. Update Password
        $passResponse = $this->actingAs($user)
            ->from('/profile')
            ->put(route('profile.password'), [
                'current_password' => 'password123',
                'password' => 'newSecretPass2026!',
                'password_confirmation' => 'newSecretPass2026!',
            ]);

        $passResponse->assertRedirect('/profile');
        $user->refresh();
        $this->assertTrue(Hash::check('newSecretPass2026!', $user->password));
    }
}
