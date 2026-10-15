<?php

namespace Tests\Feature;

use App\Enums\GuideVerificationStatus;
use App\Enums\ListingStatus;
use App\Models\AdministrativeRegion;
use App\Models\Country;
use App\Models\GuideProfile;
use App\Models\TourPackage;
use App\Models\User;
use Tests\TestCase;

class TourGuideOnboardingAndVerificationTest extends TestCase
{
    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::where('email', 'admin@turget.id')->firstOrFail();
    }

    public function test_public_guides_directory_is_accessible(): void
    {
        $response = $this->get('/guides');
        $response->assertStatus(200);
    }

    public function test_public_guide_creator_page_is_accessible(): void
    {
        $guide = GuideProfile::verified()->first();

        if ($guide) {
            $response = $this->get('/guides/'.$guide->slug);
            $response->assertStatus(200);
        } else {
            $this->markTestSkipped('No verified guide found.');
        }
    }

    public function test_public_package_detail_page_is_accessible(): void
    {
        $package = TourPackage::published()->first();

        if ($package) {
            $response = $this->get('/packages/'.$package->slug);
            $response->assertStatus(200);
        } else {
            $this->markTestSkipped('No published package found.');
        }
    }

    public function test_onboarding_requires_authentication(): void
    {
        $response = $this->get('/become-a-guide');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_submit_guide_onboarding(): void
    {
        $newApplicant = User::factory()->create(['locale' => 'id']);
        $country = Country::firstOrFail();
        $region = AdministrativeRegion::firstOrFail();

        $response = $this->actingAs($newApplicant)->post('/become-a-guide', [
            'display_name' => 'Made Satria Wibawa',
            'phone' => '+62 812-9988-7766',
            'country_id' => $country->id,
            'region_id' => $region->id,
            'languages' => ['id', 'en'],
            'specializations' => ['cultural', 'nature_adventure'],
            'experience_years' => 5,
            'price_per_day' => 600000,
            'tagline' => [
                'id' => 'Pemandu alam dan budaya Bali otentik',
                'en' => 'Authentic Bali nature and cultural guide',
            ],
            'bio' => [
                'id' => 'Berpengalaman memandu penjelajahan alam dan pura sakral sejak 2019.',
                'en' => 'Guiding nature expeditions and sacred temples since 2019.',
            ],
            'bank_name' => 'BCA',
            'bank_account_number' => '8820192831',
            'bank_account_holder' => 'Made Satria Wibawa',
            'id_card_number' => '5103019928192019',
            'guide_license_number' => 'HPI-BALI-9921',
        ]);

        $response->assertRedirect('/guide/dashboard');
        $this->assertDatabaseHas('guide_profiles', [
            'user_id' => $newApplicant->id,
            'verification_status' => GuideVerificationStatus::Submitted->value,
            'badge_verified' => false,
        ]);
        $this->assertTrue($newApplicant->fresh()->hasRole('tour_guide'));
    }

    public function test_guide_can_access_dashboard_and_manage_packages(): void
    {
        $guideUser = User::where('email', 'guide.bali@turget.id')->firstOrFail();

        $dashboardResponse = $this->actingAs($guideUser)->get('/guide/dashboard');
        $dashboardResponse->assertStatus(200);

        $packagesResponse = $this->actingAs($guideUser)->get('/guide/packages');
        $packagesResponse->assertStatus(200);

        // Guide creates new package
        $createResponse = $this->actingAs($guideUser)->post('/guide/packages', [
            'title' => [
                'id' => 'Tur Wisata Pura Tirta Empul & Suwat Waterfall',
                'en' => 'Tirta Empul Holy Water Temple & Suwat Waterfall Tour',
            ],
            'tagline' => [
                'id' => 'Penyucian spiritual melukat dan keindahan air terjun alami',
                'en' => 'Spiritual cleansing ritual and pristine jungle waterfall',
            ],
            'description' => [
                'id' => 'Pengalaman melukat di mata air suci Tampaksiring dipandu tata cara adat yang benar.',
                'en' => 'Spiritual purification ritual at sacred spring Tampaksiring.',
            ],
            'tour_type' => 'cultural',
            'price_type' => 'per_person',
            'base_price' => 500000,
            'duration_hours' => 7,
            'duration_days' => 1,
            'min_participants' => 1,
            'max_participants' => 6,
            'submit_for_review' => true,
        ]);

        $createResponse->assertRedirect('/guide/packages');
        $this->assertDatabaseHas('tour_packages', [
            'guide_profile_id' => $guideUser->guideProfile->id,
            'status' => ListingStatus::Submitted->value,
        ]);
    }

    public function test_admin_can_view_verification_queue_and_approve_kyc(): void
    {
        $admin = $this->adminUser;

        // View verification queue
        $queueResponse = $this->actingAs($admin)->get('/admin/guides');
        $queueResponse->assertStatus(200);

        // Find pending applicant
        $pendingGuide = GuideProfile::where('verification_status', GuideVerificationStatus::Submitted)->first();

        if ($pendingGuide) {
            $showResponse = $this->actingAs($admin)->get('/admin/guides/'.$pendingGuide->id);
            $showResponse->assertStatus(200);

            // Approve KYC & award verified badge
            $actionResponse = $this->actingAs($admin)->post('/admin/guides/'.$pendingGuide->id.'/action', [
                'action' => 'approve',
                'note' => 'Dokumen KTP dan lisensi valid.',
            ]);

            $actionResponse->assertSessionHas('success');
            $this->assertEquals(GuideVerificationStatus::Verified, $pendingGuide->fresh()->verification_status);
            $this->assertTrue($pendingGuide->fresh()->badge_verified);
        }
    }

    public function test_admin_can_moderate_tour_package(): void
    {
        $admin = $this->adminUser;

        $package = TourPackage::where('status', ListingStatus::Submitted)->first()
            ?? TourPackage::first();

        if ($package) {
            $package->update(['status' => ListingStatus::Submitted]);

            $response = $this->actingAs($admin)->post('/admin/packages/'.$package->id.'/action', [
                'action' => 'approve',
                'note' => 'Paket memenuhi standar keselamatan dan kejelasan rute.',
            ]);

            $response->assertSessionHas('success');
            $this->assertEquals(ListingStatus::Published, $package->fresh()->status);
        }
    }
}
