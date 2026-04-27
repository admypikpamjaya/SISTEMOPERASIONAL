<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_guest_is_redirected_to_login_from_root(): void
    {
        $response = $this->get('/');

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_logout_requires_post_request(): void
    {
        $user = User::factory()->make([
            'id' => 'test-user-logout-get',
        ]);

        $response = $this->actingAs($user)->get(route('logout'));

        $response->assertStatus(405);
    }

    public function test_logout_route_is_registered_as_post_only(): void
    {
        $methods = app('router')->getRoutes()->getByName('logout')->methods();

        $this->assertSame(['POST'], $methods);
    }
}
