<?php

namespace Tests\Feature;

use App\Enums\ListingStatus;
use App\Enums\VendorCategory;
use App\Enums\VendorVerificationStatus;
use App\Models\AdministrativeRegion;
use App\Models\Country;
use App\Models\Destination;
use App\Models\TourPackage;
use App\Models\User;
use App\Models\VendorProduct;
use App\Models\VendorProfile;
use App\Services\VendorRecommendationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VendorMarketplaceAndRecommendationTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::where('email', 'admin@turget.id')->firstOrFail();
    }

    public function test_public_vendors_directory_is_accessible(): void
    {
        $response = $this->get('/vendors');
        $response->assertStatus(200);

        // View vendors mode
        $responseVendors = $this->get('/vendors?view=vendors');
        $responseVendors->assertStatus(200);

        // Filter by category
        $responseCat = $this->get('/vendors?category=accommodation');
        $responseCat->assertStatus(200);
    }

    public function test_public_vendor_storefront_is_accessible(): void
    {
        $vendor = VendorProfile::verified()->first();
        $this->assertNotNull($vendor, 'Verified vendor should exist from seeders.');

        $response = $this->get('/vendors/'.$vendor->slug);
        $response->assertStatus(200);
    }

    public function test_public_product_detail_page_is_accessible(): void
    {
        $product = VendorProduct::published()->first();
        $this->assertNotNull($product, 'Published vendor product should exist from seeders.');

        $response = $this->get('/vendors/products/'.$product->slug);
        $response->assertStatus(200);
    }

    public function test_location_based_recommendation_engine_calculates_haversine_distance(): void
    {
        // Uluwatu Destination (-8.8291, 115.0849)
        $destination = Destination::where('slug', 'like', '%uluwatu%')->first();
        $this->assertNotNull($destination, 'Uluwatu destination should exist.');

        $service = app(VendorRecommendationService::class);
        $recommendations = $service->getRecommendationsForDestination($destination);

        $this->assertIsArray($recommendations);
        $this->assertArrayHasKey('accommodation', $recommendations);

        // Verify API endpoint
        $response = $this->getJson("/api/destinations/{$destination->id}/recommendations");
        $response->assertStatus(200)
            ->assertJsonStructure([
                'destination_id',
                'destination_name',
                'recommendations',
            ]);
    }

    public function test_destination_detail_page_loads_with_nearby_vendor_services(): void
    {
        $destination = Destination::where('slug', 'like', '%uluwatu%')->first();
        $this->assertNotNull($destination);

        $response = $this->get('/places/'.$destination->slug);
        $response->assertStatus(200);
    }

    public function test_tour_package_vendor_addon_relation_works(): void
    {
        $package = TourPackage::has('vendorAddons')->first();
        if ($package) {
            $this->assertTrue($package->vendorAddons()->count() > 0);
            $addon = $package->vendorAddons()->first();
            $this->assertNotNull($addon->pivot->custom_price);
        } else {
            $this->markTestSkipped('No package with addons found.');
        }
    }

    public function test_vendor_onboarding_requires_authentication(): void
    {
        $response = $this->get('/become-a-vendor');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_submit_vendor_onboarding(): void
    {
        $user = User::factory()->create([
            'email' => 'new.vendor@turget.id',
            'status' => 'active',
        ]);

        $country = Country::first();
        $region = AdministrativeRegion::first();

        $response = $this->actingAs($user)->post('/become-a-vendor', [
            'business_name' => 'Kuta Beach Surf School & Rentals',
            'business_type' => 'individual',
            'owner_name' => 'Ketut Suardika',
            'phone' => '081234567899',
            'email' => 'surf@kutabeach.id',
            'address' => 'Jl. Pantai Kuta No. 12',
            'latitude' => -8.7180000,
            'longitude' => 115.1680000,
            'country_id' => $country->id,
            'region_id' => $region->id,
            'category' => 'activity',
            'description' => [
                'id' => 'Penyedia kursus surfing pemula dan sewa papan selancar berkualitas.',
                'en' => 'Beginner surf lessons and quality surfboard rentals in Kuta.',
            ],
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_holder' => 'Ketut Suardika',
            'ktp_owner' => '5171012345678901',
        ]);

        $response->assertRedirect('/vendor/dashboard');

        $vendor = VendorProfile::where('user_id', $user->id)->first();
        $this->assertNotNull($vendor);
        $this->assertEquals(VendorVerificationStatus::Submitted, $vendor->verification_status);
        $this->assertEquals('Kuta Beach Surf School & Rentals', $vendor->business_name);
        $this->assertTrue($user->fresh()->hasRole('vendor'));
    }

    public function test_vendor_can_access_dashboard_and_create_products(): void
    {
        $vendorUser = User::where('email', 'bali.villa@turget.id')->firstOrFail();

        $response = $this->actingAs($vendorUser)->get('/vendor/dashboard');
        $response->assertStatus(200);

        $responseCreate = $this->actingAs($vendorUser)->get('/vendor/products/create');
        $responseCreate->assertStatus(200);

        $responseStore = $this->actingAs($vendorUser)->post('/vendor/products', [
            'name' => [
                'id' => 'Floating Breakfast in Villa Pool',
                'en' => 'Floating Breakfast in Villa Pool',
            ],
            'description' => [
                'id' => 'Pengalaman sarapan terapung di private infinity pool dengan sajian buah dan pastry segar.',
                'en' => 'Floating breakfast experience in your private pool with fresh tropical fruits and pastries.',
            ],
            'category' => 'travel_support',
            'price' => 350000,
            'price_unit' => 'per_session',
            'capacity' => 2,
            'stock_quantity' => 10,
            'booking_model' => 'instant',
            'is_published' => true,
        ]);

        $responseStore->assertRedirect('/vendor/products');

        $product = VendorProduct::where('slug', 'like', 'floating-breakfast%')->first();
        $this->assertNotNull($product);
        $this->assertEquals(ListingStatus::Published, $product->status);
    }

    public function test_admin_can_moderate_vendors_and_verify_kyc(): void
    {
        $vendor = VendorProfile::first();
        $this->assertNotNull($vendor);

        // Admin queue
        $response = $this->actingAs($this->adminUser)->get('/admin/vendors');
        $response->assertStatus(200);

        // Admin show
        $responseShow = $this->actingAs($this->adminUser)->get("/admin/vendors/{$vendor->id}");
        $responseShow->assertStatus(200);

        // Admin request revision
        $responseRev = $this->actingAs($this->adminUser)->post("/admin/vendors/{$vendor->id}/revision", [
            'notes' => 'Harap lampirkan scan NIB terbaru yang telah terverifikasi OSS.',
        ]);
        $responseRev->assertRedirect();
        $this->assertEquals(VendorVerificationStatus::NeedRevision, $vendor->fresh()->verification_status);

        // Admin approve
        $responseApprove = $this->actingAs($this->adminUser)->post("/admin/vendors/{$vendor->id}/approve", [
            'notes' => 'Dokumen lengkap dan terverifikasi.',
        ]);
        $responseApprove->assertRedirect();
        $this->assertEquals(VendorVerificationStatus::Verified, $vendor->fresh()->verification_status);
        $this->assertTrue($vendor->fresh()->badge_verified);
    }

    public function test_admin_can_moderate_vendor_products(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/admin/vendor-products');
        $response->assertStatus(200);

        $product = VendorProduct::first();
        $this->assertNotNull($product);

        // Archive product
        $responseArchive = $this->actingAs($this->adminUser)->post("/admin/vendor-products/{$product->id}/archive");
        $responseArchive->assertRedirect();
        $this->assertEquals(ListingStatus::Archived, $product->fresh()->status);

        // Re-approve / publish product
        $responseApprove = $this->actingAs($this->adminUser)->post("/admin/vendor-products/{$product->id}/approve");
        $responseApprove->assertRedirect();
        $this->assertEquals(ListingStatus::Published, $product->fresh()->status);
    }
}
