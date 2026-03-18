<?php

namespace Tests\Feature;

use App\Http\Middleware\AccessToken;
use App\Models\Click;
use App\Models\Url;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClickTest extends TestCase
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

    public function test_user_can_view_clicks()
    {
        $response = $this->getJson('/api/clicks');

        $response->assertStatus(200);
    }

    public function test_user_can_delete_click()
    {
        $click = Click::factory()->create(['id' => 1, 'url_id' => 1]);
        Url::factory()->create(['id' => 1, 'user_id' => 1]);

        $response = $this->deleteJson("/api/clicks/{$click->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('clicks', ['id' => $click->id]);
    }
}
