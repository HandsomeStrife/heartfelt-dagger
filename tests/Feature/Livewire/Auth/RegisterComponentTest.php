<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Auth;

use App\Livewire\Auth\Register;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RegisterComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_component_can_be_rendered(): void
    {
        Livewire::test(Register::class)
            ->assertStatus(200);
    }

    public function test_can_register_with_valid_data(): void
    {
        Livewire::test(Register::class)
            ->set('form.username', 'testuser')
            ->set('form.email', 'test@example.com')
            ->set('form.password', 'password123')
            ->set('form.password_confirmation', 'password123')
            ->call('register')
            ->assertRedirect('/dashboard');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'username' => 'testuser',
            'email' => 'test@example.com',
        ]);
    }

    public function test_username_is_required(): void
    {
        Livewire::test(Register::class)
            ->set('form.username', '')
            ->set('form.email', 'test@example.com')
            ->set('form.password', 'password123')
            ->set('form.password_confirmation', 'password123')
            ->call('register')
            ->assertHasErrors(['form.username' => 'required']);
    }

    public function test_username_must_be_unique(): void
    {
        User::factory()->create(['username' => 'existinguser']);

        Livewire::test(Register::class)
            ->set('form.username', 'existinguser')
            ->set('form.email', 'test@example.com')
            ->set('form.password', 'password123')
            ->set('form.password_confirmation', 'password123')
            ->call('register')
            ->assertHasErrors(['form.username' => 'unique']);
    }

    public function test_email_is_required(): void
    {
        Livewire::test(Register::class)
            ->set('form.username', 'testuser')
            ->set('form.email', '')
            ->set('form.password', 'password123')
            ->set('form.password_confirmation', 'password123')
            ->call('register')
            ->assertHasErrors(['form.email' => 'required']);
    }

    public function test_email_must_be_valid_email(): void
    {
        Livewire::test(Register::class)
            ->set('form.username', 'testuser')
            ->set('form.email', 'invalid-email')
            ->set('form.password', 'password123')
            ->set('form.password_confirmation', 'password123')
            ->call('register')
            ->assertHasErrors(['form.email' => 'email']);
    }

    public function test_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        Livewire::test(Register::class)
            ->set('form.username', 'testuser')
            ->set('form.email', 'existing@example.com')
            ->set('form.password', 'password123')
            ->set('form.password_confirmation', 'password123')
            ->call('register')
            ->assertHasErrors(['form.email' => 'unique']);
    }

    public function test_password_is_required(): void
    {
        Livewire::test(Register::class)
            ->set('form.username', 'testuser')
            ->set('form.email', 'test@example.com')
            ->set('form.password', '')
            ->set('form.password_confirmation', '')
            ->call('register')
            ->assertHasErrors(['form.password' => 'required']);
    }

    public function test_password_must_be_minimum_length(): void
    {
        Livewire::test(Register::class)
            ->set('form.username', 'testuser')
            ->set('form.email', 'test@example.com')
            ->set('form.password', '123')
            ->set('form.password_confirmation', '123')
            ->call('register')
            ->assertHasErrors(['form.password' => 'min']);
    }

    public function test_password_must_be_confirmed(): void
    {
        Livewire::test(Register::class)
            ->set('form.username', 'testuser')
            ->set('form.email', 'test@example.com')
            ->set('form.password', 'password123')
            ->set('form.password_confirmation', 'different-password')
            ->call('register')
            ->assertHasErrors(['form.password' => 'confirmed']);
    }

    public function test_password_confirmation_is_required(): void
    {
        Livewire::test(Register::class)
            ->set('form.username', 'testuser')
            ->set('form.email', 'test@example.com')
            ->set('form.password', 'password123')
            ->set('form.password_confirmation', '')
            ->call('register')
            ->assertHasErrors(['form.password_confirmation' => 'required']);
    }

    public function test_can_set_and_view_username_property(): void
    {
        Livewire::test(Register::class)
            ->set('form.username', 'testuser')
            ->assertSet('form.username', 'testuser');
    }

    public function test_can_set_and_view_email_property(): void
    {
        Livewire::test(Register::class)
            ->set('form.email', 'test@example.com')
            ->assertSet('form.email', 'test@example.com');
    }

    public function test_can_set_and_view_password_property(): void
    {
        Livewire::test(Register::class)
            ->set('form.password', 'password123')
            ->assertSet('form.password', 'password123');
    }

    public function test_can_set_and_view_password_confirmation_property(): void
    {
        Livewire::test(Register::class)
            ->set('form.password_confirmation', 'password123')
            ->assertSet('form.password_confirmation', 'password123');
    }

    public function test_register_view_contains_required_elements(): void
    {
        Livewire::test(Register::class)
            ->assertSee('HeartfeltDagger')
            ->assertSee('Join the Adventure')
            ->assertSee('Username')
            ->assertSee('Email')
            ->assertSee('Password')
            ->assertSee('Confirm Password')
            ->assertSee('Begin Adventure');
    }

    public function test_user_is_automatically_logged_in_after_registration(): void
    {
        Livewire::test(Register::class)
            ->set('form.username', 'testuser')
            ->set('form.email', 'test@example.com')
            ->set('form.password', 'password123')
            ->set('form.password_confirmation', 'password123')
            ->call('register')
            ->assertRedirect('/dashboard');

        $this->assertAuthenticated();
    }

    public function test_validation_errors_are_displayed(): void
    {
        Livewire::test(Register::class)
            ->set('form.username', '')
            ->call('register')
            ->assertHasErrors(['form.username' => 'required']);
    }
}
