<?php

namespace Tests\Feature;

use App\Http\Middleware\AccessToken;
use App\Models\Url;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UrlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->instance(AccessToken::class, new class
        {
            public function handle($request, $next)
            {
                $request->attributes->set('user_details', ['id' => 1]);

                return $next($request);
            }
        });
    }

    public function test_user_can_view_url()
    {
        $response = $this->getJson('/api/url');

        $response->assertStatus(200);
    }

    public function test_user_can_create_url()
    {
        $response = $this->postJson('/api/shorten', [
            'url' => 'https://example.com',
            'title' => 'Example',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('urls', ['url' => 'https://example.com']);
    }

    public function test_user_can_update_url()
    {
        $url = Url::factory()->create(['user_id' => 1]);

        $response = $this->putJson("/api/url/{$url->id}", [
            'title' => 'Updated Title',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('urls', ['title' => 'Updated Title']);
    }

    public function test_user_can_delete_url()
    {
        $url = Url::factory()->create(['user_id' => 1]);

        $response = $this->deleteJson("/api/url/{$url->id}");

        $response->assertStatus(204);
        $this->assertSoftDeleted('urls', ['id' => $url->id]);
    }
}
