<?php

namespace Tests\Feature;

use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_navigation_shows_guest_links_for_unauthenticated_users(): void
    {
        $response = $this->get('/');

        $response->assertSee('Login');
        $response->assertSee('Join Adventure');
        $response->assertDontSee('Character Creator');
        $response->assertDontSee('Rooms');
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
        $response->assertSee('Character Creator');
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

    public function test_character_creator_link_works(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/character-creator');

        $response->assertStatus(200);
        $response->assertSee('Character Creator');
        $response->assertSee('Coming Soon');
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
        $response->assertDontSee('Character Creator');
        $response->assertDontSee('Rooms');
        $response->assertDontSee('Profile');
    }

    public function test_navigation_is_not_shown_on_register_page(): void
    {
        $response = $this->get('/register');

        // Navigation should not be visible on register page
        $response->assertDontSee('Character Creator');
        $response->assertDontSee('Rooms');
        $response->assertDontSee('Profile');
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

    public function test_guest_register_link_works(): void
    {
        $response = $this->get('/');

        $response->assertSee('Join Adventure');

        // Follow the register link
        $registerResponse = $this->get('/register');
        $registerResponse->assertStatus(200);
        $registerResponse->assertSee('Join the Adventure');
    }

    public function test_guest_login_link_works(): void
    {
        $response = $this->get('/');

        $response->assertSee('Login');

        // Follow the login link
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
        $response->assertSee('Character Creator');
        $response->assertSee('Campaigns');
        $response->assertSee('Logout');
    }

    public function test_navigation_styling_includes_heartfeltdagger_branding(): void
    {
        $response = $this->get('/');

        // Check for HeartfeltDagger branding and styling
        $response->assertSee('HeartfeltDagger');
        $response->assertSee('font-federant');
        $response->assertSee('text-white');
    }
}
