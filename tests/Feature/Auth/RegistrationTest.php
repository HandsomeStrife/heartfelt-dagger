<?php

use Domain\User\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect('/dashboard');
});

test('registration requires username', function () {
    $response = $this->post('/register', [
        'username' => '',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('username');
    $this->assertGuest();
});

test('registration requires email', function () {
    $response = $this->post('/register', [
        'username' => 'testuser',
        'email' => '',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('registration requires valid email', function () {
    $response = $this->post('/register', [
        'username' => 'testuser',
        'email' => 'invalid-email',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('registration requires password', function () {
    $response = $this->post('/register', [
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => '',
        'password_confirmation' => '',
    ]);

    $response->assertSessionHasErrors('password');
    $this->assertGuest();
});

test('registration requires password confirmation', function () {
    $response = $this->post('/register', [
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'different-password',
    ]);

    $response->assertSessionHasErrors('password');
    $this->assertGuest();
});

test('registration requires minimum password length', function () {
    $response = $this->post('/register', [
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => '123',
        'password_confirmation' => '123',
    ]);

    $response->assertSessionHasErrors('password');
    $this->assertGuest();
});

test('registration requires unique username', function () {
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
});

test('registration requires unique email', function () {
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
});

test('authenticated users cannot access registration', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/register');

    $response->assertRedirect('/dashboard');
});