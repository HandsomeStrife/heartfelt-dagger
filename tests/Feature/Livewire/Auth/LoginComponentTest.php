<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Auth;

use App\Livewire\Auth\Login;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class LoginComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_component_can_be_rendered(): void
    {
        Livewire::test(Login::class)
            ->assertStatus(200);
    }

    public function test_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        Livewire::test(Login::class)
            ->set('form.email', 'test@example.com')
            ->set('form.password', 'password123')
            ->call('login')
            ->assertRedirect('/dashboard');

        $this->assertAuthenticated();
    }

    public function test_cannot_login_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        Livewire::test(Login::class)
            ->set('form.email', 'test@example.com')
            ->set('form.password', 'wrong-password')
            ->call('login')
            ->assertHasErrors(['form.email' => 'The provided credentials do not match our records.']);

        $this->assertGuest();
    }

    public function test_email_is_required(): void
    {
        Livewire::test(Login::class)
            ->set('form.email', '')
            ->set('form.password', 'password123')
            ->call('login')
            ->assertHasErrors(['form.email' => 'required']);
    }

    public function test_email_must_be_valid_email(): void
    {
        Livewire::test(Login::class)
            ->set('form.email', 'invalid-email')
            ->set('form.password', 'password123')
            ->call('login')
            ->assertHasErrors(['form.email' => 'email']);
    }

    public function test_password_is_required(): void
    {
        Livewire::test(Login::class)
            ->set('form.email', 'test@example.com')
            ->set('form.password', '')
            ->call('login')
            ->assertHasErrors(['form.password' => 'required']);
    }

    public function test_password_must_be_minimum_length(): void
    {
        Livewire::test(Login::class)
            ->set('form.email', 'test@example.com')
            ->set('form.password', '123')
            ->call('login')
            ->assertHasErrors(['form.password' => 'min']);
    }

    public function test_remember_me_functionality(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        Livewire::test(Login::class)
            ->set('form.email', 'test@example.com')
            ->set('form.password', 'password123')
            ->set('form.remember', true)
            ->call('login')
            ->assertRedirect('/dashboard');

        $this->assertAuthenticated();
    }

    public function test_can_set_and_view_email_property(): void
    {
        Livewire::test(Login::class)
            ->set('form.email', 'test@example.com')
            ->assertSet('form.email', 'test@example.com');
    }

    public function test_can_set_and_view_password_property(): void
    {
        Livewire::test(Login::class)
            ->set('form.password', 'password123')
            ->assertSet('form.password', 'password123');
    }

    public function test_can_toggle_remember_property(): void
    {
        Livewire::test(Login::class)
            ->set('form.remember', true)
            ->assertSet('form.remember', true)
            ->set('form.remember', false)
            ->assertSet('form.remember', false);
    }

    public function test_login_view_contains_required_elements(): void
    {
        Livewire::test(Login::class)
            ->assertSee('HeartfeltDagger')
            ->assertSee('Enter the Realm')
            ->assertSee('Email')
            ->assertSee('Password')
            ->assertSee('Remember me')
            ->assertSee('Enter the Realm');
    }

    public function test_validation_errors_are_displayed(): void
    {
        Livewire::test(Login::class)
            ->set('form.email', '')
            ->call('login')
            ->assertHasErrors(['form.email' => 'required']);
    }
}
