<?php

use Domain\User\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('registration screen can be rendered', function () {
    $response = get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = post('/register', [
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    assertAuthenticated();
    $response->assertRedirect('/dashboard');
});

test('registration requires username', function () {
    $response = post('/register', [
        'username' => '',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('username');
    assertGuest();
});

test('registration requires email', function () {
    $response = post('/register', [
        'username' => 'testuser',
        'email' => '',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    assertGuest();
});

test('registration requires valid email', function () {
    $response = post('/register', [
        'username' => 'testuser',
        'email' => 'invalid-email',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    assertGuest();
});

test('registration requires password', function () {
    $response = post('/register', [
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => '',
        'password_confirmation' => '',
    ]);

    $response->assertSessionHasErrors('password');
    assertGuest();
});

test('registration requires password confirmation', function () {
    $response = post('/register', [
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'different-password',
    ]);

    $response->assertSessionHasErrors('password');
    assertGuest();
});

test('registration requires minimum password length', function () {
    $response = post('/register', [
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => '123',
        'password_confirmation' => '123',
    ]);

    $response->assertSessionHasErrors('password');
    assertGuest();
});

test('registration requires unique username', function () {
    User::factory()->create([
        'username' => 'existinguser',
        'email' => 'existing@example.com',
    ]);

    $response = post('/register', [
        'username' => 'existinguser',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('username');
    assertGuest();
});

test('registration requires unique email', function () {
    User::factory()->create([
        'username' => 'existinguser',
        'email' => 'existing@example.com',
    ]);

    $response = post('/register', [
        'username' => 'testuser',
        'email' => 'existing@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    assertGuest();
});

test('authenticated users cannot access registration', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->get('/register');

    $response->assertRedirect('/dashboard');
});
