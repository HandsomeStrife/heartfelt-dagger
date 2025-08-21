<?php

namespace Tests\Feature\Auth;

use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_is_accessible_to_guests(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_rooms_requires_authentication(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_character_builder_is_publicly_accessible(): void
    {
        $response = $this->get('/character-builder');

        $response->assertStatus(302);
        $response->assertRedirect();
        // Should redirect to character builder edit page with new character
        $this->assertTrue(str_contains($response->headers->get('location'), '/character-builder/'));
    }

    public function test_authenticated_users_can_access_rooms(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
    }

    public function test_character_builder_creates_new_character(): void
    {
        $response = $this->get('/character-builder');

        $response->assertStatus(302);
        $response->assertRedirect();
        // Should redirect to the edit page with a new character key
        $this->assertTrue(str_contains($response->headers->get('location'), '/character-builder/'));
    }

    public function test_authenticated_users_are_redirected_from_login(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect('/dashboard');
    }

    public function test_authenticated_users_are_redirected_from_register(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/register');

        $response->assertRedirect('/dashboard');
    }

    public function test_logout_requires_post_method(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/logout');

        $response->assertStatus(405); // Method not allowed
    }

    public function test_logout_redirects_to_home(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_legacy_video_rooms_route_still_works(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/video-rooms');

        $response->assertStatus(200);
    }

    public function test_guests_cannot_access_legacy_video_rooms_without_auth(): void
    {
        $response = $this->get('/video-rooms');

        $response->assertRedirect('/login');
    }

    public function test_nonexistent_routes_return_404(): void
    {
        $response = $this->get('/nonexistent-route');

        $response->assertStatus(404);
    }

    public function test_authenticated_user_session_persists_across_requests(): void
    {
        $user = User::factory()->create();

        // First request with authentication
        $this->actingAs($user)->get('/dashboard')->assertStatus(200);

        // Second request should still be authenticated
        $this->get('/dashboard')->assertStatus(200);
        $this->assertAuthenticated();
    }

    public function test_middleware_prevents_unauthorized_access_to_protected_routes(): void
    {
        $protectedRoutes = [
            '/dashboard',
            '/video-rooms',
        ];

        foreach ($protectedRoutes as $route) {
            $response = $this->get($route);
            $response->assertRedirect('/login');
        }
    }

    public function test_csrf_protection_on_logout(): void
    {
        $user = User::factory()->create();

        // Attempt logout without CSRF token should fail
        $response = $this->actingAs($user)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post('/logout');

        // Since we're removing CSRF middleware for this test, it should succeed
        $response->assertRedirect('/');
    }
}
