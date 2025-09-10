<?php

use Domain\User\Models\User;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('login screen can be rendered', function () {
    $response = get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = post('/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    assertAuthenticated();
    $response->assertRedirect('/dashboard');
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = post('/login', [
        'email' => 'test@example.com',
        'password' => 'wrong-password',
    ]);

    assertGuest();
    $response->assertSessionHasErrors('email');
});

test('users can not authenticate with invalid email', function () {
    $response = post('/login', [
        'email' => 'nonexistent@example.com',
        'password' => 'password123',
    ]);

    assertGuest();
    $response->assertSessionHasErrors('email');
});

test('login requires email', function () {
    $response = post('/login', [
        'email' => '',
        'password' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    assertGuest();
});

test('login requires password', function () {
    $response = post('/login', [
        'email' => 'test@example.com',
        'password' => '',
    ]);

    $response->assertSessionHasErrors('password');
    assertGuest();
});

test('login requires valid email format', function () {
    $response = post('/login', [
        'email' => 'invalid-email',
        'password' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    assertGuest();
});

test('remember me functionality', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = post('/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
        'remember' => true,
    ]);

    assertAuthenticated();
    $response->assertRedirect('/dashboard');

    // Check that remember token is set
    expect($user->fresh()->remember_token)->not->toBeNull();
});

test('authenticated users cannot access login', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->get('/login');

    $response->assertRedirect('/dashboard');
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->post('/logout');

    assertGuest();
    $response->assertRedirect('/');
});
