<?php

namespace Tests\Feature;

use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_navigation_shows_character_links_for_unauthenticated_users(): void
    {
        $response = $this->get('/');
        
        // Guest users should see branding and character links (they're public)
        $response->assertSee('HeartfeltDagger');
        $response->assertSee('My Characters');
        $response->assertSee('Create Character'); // Note: button text is different from navigation
        
        // But should NOT see auth-only features
        $response->assertDontSee('Login');
        $response->assertDontSee('Join Adventure');
        $response->assertDontSee('Dashboard');
        $response->assertDontSee('Campaigns');
        $response->assertDontSee('Logout');
    }

    public function test_navigation_shows_user_menu_for_authenticated_users(): void
    {
        $user = User::factory()->create([
            'username' => 'testuser',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertSee('testuser');
        $response->assertSee('Dashboard');
        $response->assertSee('My Characters');
        $response->assertSee('Create New Character');
        $response->assertSee('Campaigns');
        $response->assertSee('Logout');
        $response->assertDontSee('Login');
        $response->assertDontSee('Join Adventure');
    }

    public function test_navigation_displays_user_avatar_initial(): void
    {
        $user = User::factory()->create([
            'username' => 'TestUser',
        ]);

        $response = $this->actingAs($user)->get('/');

        // Should show first letter of username
        $response->assertSee('T');
    }

    public function test_navigation_brand_links_to_home(): void
    {
        $response = $this->get('/');

        $response->assertSee('HeartfeltDagger');
        $response->assertSee('href="/"', false);
    }

    public function test_character_builder_link_works(): void
    {
        $response = $this->get('/character-builder');

        $response->assertStatus(302);
        $response->assertRedirect();
        // Should redirect to the character builder edit page with new character
        $this->assertTrue(str_contains($response->headers->get('location'), '/character-builder/'));
    }

    public function test_campaigns_link_works(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/campaigns');

        $response->assertStatus(200);
    }

    public function test_navigation_is_not_shown_on_login_page(): void
    {
        $response = $this->get('/login');

        // Navigation should not be visible on login page
        $response->assertDontSee('Create New Character');
        $response->assertDontSee('My Characters');
        $response->assertDontSee('Dashboard');
    }

    public function test_navigation_is_not_shown_on_register_page(): void
    {
        $response = $this->get('/register');

        // Navigation should not be visible on register page
        $response->assertDontSee('Create New Character');
        $response->assertDontSee('My Characters');
        $response->assertDontSee('Dashboard');
    }

    public function test_navigation_is_shown_on_protected_pages(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertSee('HeartfeltDagger');
        $response->assertSee($user->username);
    }

    public function test_logout_form_submission(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        $this->assertAuthenticated();

        $response = $this->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_register_page_works_directly(): void
    {
        // Register page should work when accessed directly
        $registerResponse = $this->get('/register');
        $registerResponse->assertStatus(200);
        $registerResponse->assertSee('Join the Adventure');
    }

    public function test_login_page_works_directly(): void
    {
        // Login page should work when accessed directly
        $loginResponse = $this->get('/login');
        $loginResponse->assertStatus(200);
        $loginResponse->assertSee('Enter the Realm');
    }

    public function test_dropdown_menu_structure(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        // Check for dropdown elements
        $response->assertSee('Dashboard');
        $response->assertSee('Create New Character');
        $response->assertSee('Campaigns');
        $response->assertSee('Logout');
    }

    public function test_navigation_styling_includes_heartfeltdagger_branding(): void
    {
        $response = $this->get('/');

        // Check for HeartfeltDagger branding and styling
        $response->assertSee('HeartfeltDagger');
        $response->assertSee('font-outfit');
        $response->assertSee('text-white');
    }
}
