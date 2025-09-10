<?php

declare(strict_types=1);
use App\Livewire\Auth\Login;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\assertGuest;
use function Pest\Livewire\livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('login component can be rendered', function () {
    livewire(Login::class)
        ->assertStatus(200);
});
test('can login with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    livewire(Login::class)
        ->set('form.email', 'test@example.com')
        ->set('form.password', 'password123')
        ->call('login')
        ->assertRedirect('/dashboard');

    assertAuthenticated();
});
test('cannot login with invalid credentials', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    livewire(Login::class)
        ->set('form.email', 'test@example.com')
        ->set('form.password', 'wrong-password')
        ->call('login')
        ->assertHasErrors(['form.email' => 'The provided credentials do not match our records.']);

    assertGuest();
});
test('email is required', function () {
    livewire(Login::class)
        ->set('form.email', '')
        ->set('form.password', 'password123')
        ->call('login')
        ->assertHasErrors(['form.email' => 'required']);
});
test('email must be valid email', function () {
    livewire(Login::class)
        ->set('form.email', 'invalid-email')
        ->set('form.password', 'password123')
        ->call('login')
        ->assertHasErrors(['form.email' => 'email']);
});
test('password is required', function () {
    livewire(Login::class)
        ->set('form.email', 'test@example.com')
        ->set('form.password', '')
        ->call('login')
        ->assertHasErrors(['form.password' => 'required']);
});
test('password must be minimum length', function () {
    livewire(Login::class)
        ->set('form.email', 'test@example.com')
        ->set('form.password', '123')
        ->call('login')
        ->assertHasErrors(['form.password' => 'min']);
});
test('remember me functionality', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    livewire(Login::class)
        ->set('form.email', 'test@example.com')
        ->set('form.password', 'password123')
        ->set('form.remember', true)
        ->call('login')
        ->assertRedirect('/dashboard');

    assertAuthenticated();
});
test('can set and view email property', function () {
    livewire(Login::class)
        ->set('form.email', 'test@example.com')
        ->assertSet('form.email', 'test@example.com');
});
test('can set and view password property', function () {
    livewire(Login::class)
        ->set('form.password', 'password123')
        ->assertSet('form.password', 'password123');
});
test('can toggle remember property', function () {
    livewire(Login::class)
        ->set('form.remember', true)
        ->assertSet('form.remember', true)
        ->set('form.remember', false)
        ->assertSet('form.remember', false);
});
test('login view contains required elements', function () {
    livewire(Login::class)
        ->assertSee('HeartfeltDagger')
        ->assertSee('Enter the Realm')
        ->assertSee('Email')
        ->assertSee('Password')
        ->assertSee('Remember me')
        ->assertSee('Enter the Realm');
});
test('validation errors are displayed', function () {
    livewire(Login::class)
        ->set('form.email', '')
        ->call('login')
        ->assertHasErrors(['form.email' => 'required']);
});
