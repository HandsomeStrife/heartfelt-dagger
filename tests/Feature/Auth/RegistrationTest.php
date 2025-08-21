<?php

namespace Tests\Feature\Auth;

use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');
    }

    public function test_registration_requires_username(): void
    {
        $response = $this->post('/register', [
            'username' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_registration_requires_email(): void
    {
        $response = $this->post('/register', [
            'username' => 'testuser',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_registration_requires_valid_email(): void
    {
        $response = $this->post('/register', [
            'username' => 'testuser',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_registration_requires_password(): void
    {
        $response = $this->post('/register', [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    public function test_registration_requires_password_confirmation(): void
    {
        $response = $this->post('/register', [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    public function test_registration_requires_minimum_password_length(): void
    {
        $response = $this->post('/register', [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => '123',
            'password_confirmation' => '123',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    public function test_registration_requires_unique_username(): void
    {
        User::factory()->create([
            'username' => 'existinguser',
            'email' => 'existing@example.com',
        ]);

        $response = $this->post('/register', [
            'username' => 'existinguser',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_registration_requires_unique_email(): void
    {
        User::factory()->create([
            'username' => 'existinguser',
            'email' => 'existing@example.com',
        ]);

        $response = $this->post('/register', [
            'username' => 'testuser',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_authenticated_users_cannot_access_registration(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/register');

        $response->assertRedirect('/dashboard');
    }
}
