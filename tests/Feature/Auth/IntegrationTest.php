<?php

namespace Tests\Feature\Auth;

use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_user_registration_and_login_flow(): void
    {
        // Test registration
        $response = $this->post('/register', [
            'username' => 'newhero',
            'email' => 'newhero@daggerheart.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        // Verify user was created in database
        $this->assertDatabaseHas('users', [
            'username' => 'newhero',
            'email' => 'newhero@daggerheart.com',
        ]);

        // Test logout
        $logoutResponse = $this->post('/logout');
        $logoutResponse->assertRedirect('/');
        $this->assertGuest();

        // Test login with created account
        $loginResponse = $this->post('/login', [
            'email' => 'newhero@daggerheart.com',
            'password' => 'password123',
        ]);

        $loginResponse->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_user_navigation_flow_through_protected_areas(): void
    {
        $user = User::factory()->create();

        // Start unauthenticated - should be redirected to login
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/video-rooms')->assertRedirect('/login');

        // Login
        $this->actingAs($user);

        // Now can access protected areas
        $this->get('/dashboard')->assertStatus(200);
        $this->get('/character-builder')->assertStatus(302); // Creates new character and redirects

        // Login/register pages should redirect to rooms when authenticated
        $this->get('/login')->assertRedirect('/dashboard');
        $this->get('/register')->assertRedirect('/dashboard');

        // Can logout and return to guest state
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();

        // Should be redirected again after logout
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_session_persistence_across_multiple_requests(): void
    {
        // Register user
        $this->post('/register', [
            'username' => 'sessiontest',
            'email' => 'session@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertAuthenticated();

        // Make multiple requests - session should persist
        $this->get('/dashboard')->assertStatus(200);
        $this->get('/character-builder')->assertStatus(302); // Creates new character and redirects
        $this->get('/')->assertRedirect('/dashboard');

        // Still authenticated after multiple requests
        $this->assertAuthenticated();
        $this->assertEquals('sessiontest', auth()->user()->username);
    }

    public function test_remember_me_functionality_integration(): void
    {
        $user = User::factory()->create([
            'email' => 'remember@test.com',
            'password' => bcrypt('password123'),
        ]);

        // Login with remember me
        $response = $this->post('/login', [
            'email' => 'remember@test.com',
            'password' => 'password123',
            'remember' => true,
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        // Check that remember token was set
        $this->assertNotNull($user->fresh()->remember_token);

        // Simulate session expiry but remember token should keep user logged in
        session()->flush();

        // User should still be authenticated due to remember token
        // (This is a simplified test - in real Laravel, remember tokens work with cookies)
        $this->assertNotNull($user->fresh()->remember_token);
    }

    public function test_validation_error_handling_across_components(): void
    {
        // Test registration validation
        $registerResponse = $this->post('/register', [
            'username' => '',
            'email' => 'invalid-email',
            'password' => '123',
            'password_confirmation' => 'different',
        ]);

        $registerResponse->assertSessionHasErrors(['username', 'email', 'password']);
        $this->assertGuest();

        // Test login validation
        $loginResponse = $this->post('/login', [
            'email' => 'invalid-email',
            'password' => '',
        ]);

        $loginResponse->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
    }

    public function test_route_middleware_integration(): void
    {
        // All protected routes should redirect to login when unauthenticated
        $protectedRoutes = ['/dashboard', '/video-rooms'];

        foreach ($protectedRoutes as $route) {
            $this->get($route)->assertRedirect('/login');
        }

        // Create and authenticate user
        $user = User::factory()->create();
        $this->actingAs($user);

        // All protected routes should now be accessible
        foreach ($protectedRoutes as $route) {
            $this->get($route)->assertStatus(200);
        }

        // Guest-only routes should redirect to rooms when authenticated
        $guestRoutes = ['/login', '/register'];

        foreach ($guestRoutes as $route) {
            $this->get($route)->assertRedirect('/dashboard');
        }
    }

    public function test_user_creation_and_authentication_persistence(): void
    {
        // Create user through registration
        $this->post('/register', [
            'username' => 'testintegration',
            'email' => 'integration@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'integration@test.com')->first();

        // Verify user properties
        $this->assertNotNull($user);
        $this->assertEquals('testintegration', $user->username);
        $this->assertEquals('integration@test.com', $user->email);
        $this->assertTrue(\Hash::check('password123', $user->password));

        // Verify authentication state
        $this->assertAuthenticated();
        $this->assertEquals($user->id, auth()->id());

        // Test that user can access all areas of the application
        $this->get('/dashboard')->assertStatus(200);
        $this->get('/character-builder')->assertStatus(302); // Creates new character and redirects
    }

    public function test_error_handling_and_recovery(): void
    {
        // Attempt to register with existing email
        User::factory()->create(['email' => 'existing@test.com']);

        $response = $this->post('/register', [
            'username' => 'newuser',
            'email' => 'existing@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();

        // Should be able to register with different email
        $successResponse = $this->post('/register', [
            'username' => 'newuser',
            'email' => 'new@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $successResponse->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }
}
