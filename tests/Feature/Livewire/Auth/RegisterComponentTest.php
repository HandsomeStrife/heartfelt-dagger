<?php

declare(strict_types=1);
use App\Livewire\Auth\Register;
use Domain\User\Models\User;
use function Pest\Livewire\livewire;
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('register component can be rendered', function () {
    livewire(Register::class)
        ->assertStatus(200);
});
test('can register with valid data', function () {
    livewire(Register::class)
        ->set('form.username', 'testuser')
        ->set('form.email', 'test@example.com')
        ->set('form.password', 'password123')
        ->set('form.password_confirmation', 'password123')
        ->call('register')
        ->assertRedirect('/dashboard');

    assertAuthenticated();
    assertDatabaseHas('users', [
        'username' => 'testuser',
        'email' => 'test@example.com',
    ]);
});
test('username is required', function () {
    livewire(Register::class)
        ->set('form.username', '')
        ->set('form.email', 'test@example.com')
        ->set('form.password', 'password123')
        ->set('form.password_confirmation', 'password123')
        ->call('register')
        ->assertHasErrors(['form.username' => 'required']);
});
test('username must be unique', function () {
    User::factory()->create(['username' => 'existinguser']);

    livewire(Register::class)
        ->set('form.username', 'existinguser')
        ->set('form.email', 'test@example.com')
        ->set('form.password', 'password123')
        ->set('form.password_confirmation', 'password123')
        ->call('register')
        ->assertHasErrors(['form.username' => 'unique']);
});
test('email is required', function () {
    livewire(Register::class)
        ->set('form.username', 'testuser')
        ->set('form.email', '')
        ->set('form.password', 'password123')
        ->set('form.password_confirmation', 'password123')
        ->call('register')
        ->assertHasErrors(['form.email' => 'required']);
});
test('email must be valid email', function () {
    livewire(Register::class)
        ->set('form.username', 'testuser')
        ->set('form.email', 'invalid-email')
        ->set('form.password', 'password123')
        ->set('form.password_confirmation', 'password123')
        ->call('register')
        ->assertHasErrors(['form.email' => 'email']);
});
test('email must be unique', function () {
    User::factory()->create(['email' => 'existing@example.com']);

    livewire(Register::class)
        ->set('form.username', 'testuser')
        ->set('form.email', 'existing@example.com')
        ->set('form.password', 'password123')
        ->set('form.password_confirmation', 'password123')
        ->call('register')
        ->assertHasErrors(['form.email' => 'unique']);
});
test('password is required', function () {
    livewire(Register::class)
        ->set('form.username', 'testuser')
        ->set('form.email', 'test@example.com')
        ->set('form.password', '')
        ->set('form.password_confirmation', '')
        ->call('register')
        ->assertHasErrors(['form.password' => 'required']);
});
test('password must be minimum length', function () {
    livewire(Register::class)
        ->set('form.username', 'testuser')
        ->set('form.email', 'test@example.com')
        ->set('form.password', '123')
        ->set('form.password_confirmation', '123')
        ->call('register')
        ->assertHasErrors(['form.password' => 'min']);
});
test('password must be confirmed', function () {
    livewire(Register::class)
        ->set('form.username', 'testuser')
        ->set('form.email', 'test@example.com')
        ->set('form.password', 'password123')
        ->set('form.password_confirmation', 'different-password')
        ->call('register')
        ->assertHasErrors(['form.password' => 'confirmed']);
});
test('password confirmation is required', function () {
    livewire(Register::class)
        ->set('form.username', 'testuser')
        ->set('form.email', 'test@example.com')
        ->set('form.password', 'password123')
        ->set('form.password_confirmation', '')
        ->call('register')
        ->assertHasErrors(['form.password_confirmation' => 'required']);
});
test('can set and view username property', function () {
    livewire(Register::class)
        ->set('form.username', 'testuser')
        ->assertSet('form.username', 'testuser');
});
test('can set and view email property', function () {
    livewire(Register::class)
        ->set('form.email', 'test@example.com')
        ->assertSet('form.email', 'test@example.com');
});
test('can set and view password property', function () {
    livewire(Register::class)
        ->set('form.password', 'password123')
        ->assertSet('form.password', 'password123');
});
test('can set and view password confirmation property', function () {
    livewire(Register::class)
        ->set('form.password_confirmation', 'password123')
        ->assertSet('form.password_confirmation', 'password123');
});
test('register view contains required elements', function () {
    livewire(Register::class)
        ->assertSee('HeartfeltDagger')
        ->assertSee('Join the Adventure')
        ->assertSee('Username')
        ->assertSee('Email')
        ->assertSee('Password')
        ->assertSee('Confirm Password')
        ->assertSee('Begin Adventure');
});
test('user is automatically logged in after registration', function () {
    livewire(Register::class)
        ->set('form.username', 'testuser')
        ->set('form.email', 'test@example.com')
        ->set('form.password', 'password123')
        ->set('form.password_confirmation', 'password123')
        ->call('register')
        ->assertRedirect('/dashboard');

    assertAuthenticated();
});
test('validation errors are displayed', function () {
    livewire(Register::class)
        ->set('form.username', '')
        ->call('register')
        ->assertHasErrors(['form.username' => 'required']);
});
