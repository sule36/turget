<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Models\Article;
use App\Models\Country;
use App\Models\Destination;
use App\Models\User;
use Tests\TestCase;

class TurgetDiscoveryAndCmsTest extends TestCase
{
    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::where('email', 'admin@turget.id')->first()
            ?? User::factory()->create(['email' => 'admin@turget.id']);
    }

    public function test_homepage_is_accessible(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_destinations_catalog_is_accessible(): void
    {
        $response = $this->get('/destinations');
        $response->assertStatus(200);
    }

    public function test_destination_detail_page_is_accessible(): void
    {
        $destination = Destination::published()->first();

        if ($destination) {
            $response = $this->get('/places/'.$destination->slug);
            $response->assertStatus(200);
        } else {
            $this->markTestSkipped('No published destination found in database.');
        }
    }

    public function test_search_autocomplete_api_returns_results(): void
    {
        $response = $this->getJson('/api/search/autocomplete?q=uluwatu');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'results',
        ]);
        $this->assertNotEmpty($response->json('results'));
    }

    public function test_articles_catalog_and_detail_are_accessible(): void
    {
        $response = $this->get('/articles');
        $response->assertStatus(200);

        $article = Article::first();
        if ($article) {
            $detailResponse = $this->get('/articles/'.$article->slug);
            $detailResponse->assertStatus(200);
        }
    }

    public function test_locale_switch_changes_session(): void
    {
        $response = $this->post('/locale/en');
        $response->assertSessionHas('locale', 'en');

        $responseId = $this->post('/locale/id');
        $responseId->assertSessionHas('locale', 'id');
    }

    public function test_admin_dashboard_requires_authentication(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_admin_can_access_cms(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/admin');
        $response->assertStatus(200);

        $destResponse = $this->actingAs($this->adminUser)->get('/admin/destinations');
        $destResponse->assertStatus(200);

        $regResponse = $this->actingAs($this->adminUser)->get('/admin/regions');
        $regResponse->assertStatus(200);

        $artResponse = $this->actingAs($this->adminUser)->get('/admin/articles');
        $artResponse->assertStatus(200);
    }

    public function test_workflow_state_transition(): void
    {
        $destination = Destination::first();

        if ($destination) {
            // Set status to Draft via forceFill because status is guarded
            $destination->forceFill(['status' => ContentStatus::Draft])->save();

            // Submit for review
            $response = $this->actingAs($this->adminUser)
                ->post('/admin/workflow/destination/'.$destination->id, [
                    'action' => 'submit',
                    'note' => 'Pengujian alur review otomatis',
                ]);

            $response->assertSessionHas('success');
            $this->assertEquals(ContentStatus::InReview, $destination->fresh()->status);

            // Approve
            $this->actingAs($this->adminUser)
                ->post('/admin/workflow/destination/'.$destination->id, [
                    'action' => 'approve',
                    'note' => 'Disetujui oleh editor penguji',
                ]);
            $this->assertEquals(ContentStatus::Approved, $destination->fresh()->status);

            // Publish back
            $this->actingAs($this->adminUser)
                ->post('/admin/workflow/destination/'.$destination->id, [
                    'action' => 'publish',
                    'note' => 'Diterbitkan kembali',
                ]);
            $this->assertEquals(ContentStatus::Published, $destination->fresh()->status);
        }
    }
}
