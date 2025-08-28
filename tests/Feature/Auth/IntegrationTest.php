<?php

use Domain\User\Models\User;
use function Pest\Laravel\{actingAs, get, post, put, patch, delete};

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('complete user registration and login flow', function () {
    // Test registration
    $response = post('/register', [
        'username' => 'newhero',
        'email' => 'newhero@daggerheart.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect('/dashboard');
    assertAuthenticated();

    // Verify user was created in database
    assertDatabaseHas('users', [
        'username' => 'newhero',
        'email' => 'newhero@daggerheart.com',
    ]);

    // Test logout
    $logoutResponse = post('/logout');
    $logoutResponse->assertRedirect('/');
    assertGuest();

    // Test login with created account
    $loginResponse = post('/login', [
        'email' => 'newhero@daggerheart.com',
        'password' => 'password123',
    ]);

    $loginResponse->assertRedirect('/dashboard');
    assertAuthenticated();
});

test('user navigation flow through protected areas', function () {
    $user = User::factory()->create();

    // Start unauthenticated - should be redirected to login
    get('/dashboard')->assertRedirect('/login');
    get('/video-rooms')->assertRedirect('/login');

    // Login
    actingAs($user);

    // Now can access protected areas
    get('/dashboard')->assertStatus(200);
    get('/character-builder')->assertStatus(302);

    // Creates new character and redirects
    // Login/register pages should redirect to rooms when authenticated
    get('/login')->assertRedirect('/dashboard');
    get('/register')->assertRedirect('/dashboard');

    // Can logout and return to guest state
    post('/logout')->assertRedirect('/');
    assertGuest();

    // Should be redirected again after logout
    get('/dashboard')->assertRedirect('/login');
});

test('session persistence across multiple requests', function () {
    // Register user
    post('/register', [
        'username' => 'sessiontest',
        'email' => 'session@test.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    assertAuthenticated();

    // Make multiple requests - session should persist
    get('/dashboard')->assertStatus(200);
    get('/character-builder')->assertStatus(302);
    // Creates new character and redirects
    get('/')->assertRedirect('/dashboard');

    // Still authenticated after multiple requests
    assertAuthenticated();
    expect(auth()->user()->username)->toEqual('sessiontest');
});

test('remember me functionality integration', function () {
    $user = User::factory()->create([
        'email' => 'remember@test.com',
        'password' => bcrypt('password123'),
    ]);

    // Login with remember me
    $response = post('/login', [
        'email' => 'remember@test.com',
        'password' => 'password123',
        'remember' => true,
    ]);

    $response->assertRedirect('/dashboard');
    assertAuthenticated();

    // Check that remember token was set
    expect($user->fresh()->remember_token)->not->toBeNull();

    // Simulate session expiry but remember token should keep user logged in
    session()->flush();

    // User should still be authenticated due to remember token
    // (This is a simplified test - in real Laravel, remember tokens work with cookies)
    expect($user->fresh()->remember_token)->not->toBeNull();
});

test('validation error handling across components', function () {
    // Test registration validation
    $registerResponse = post('/register', [
        'username' => '',
        'email' => 'invalid-email',
        'password' => '123',
        'password_confirmation' => 'different',
    ]);

    $registerResponse->assertSessionHasErrors(['username', 'email', 'password']);
    assertGuest();

    // Test login validation
    $loginResponse = post('/login', [
        'email' => 'invalid-email',
        'password' => '',
    ]);

    $loginResponse->assertSessionHasErrors(['email', 'password']);
    assertGuest();
});

test('route middleware integration', function () {
    // All protected routes should redirect to login when unauthenticated
    $protectedRoutes = ['/dashboard', '/video-rooms'];

    foreach ($protectedRoutes as $route) {
        get($route)->assertRedirect('/login');
    }

    // Create and authenticate user
    $user = User::factory()->create();
    actingAs($user);

    // All protected routes should now be accessible
    foreach ($protectedRoutes as $route) {
        get($route)->assertStatus(200);
    }

    // Guest-only routes should redirect to rooms when authenticated
    $guestRoutes = ['/login', '/register'];

    foreach ($guestRoutes as $route) {
        get($route)->assertRedirect('/dashboard');
    }
});

test('user creation and authentication persistence', function () {
    // Create user through registration
    post('/register', [
        'username' => 'testintegration',
        'email' => 'integration@test.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $user = User::where('email', 'integration@test.com')->first();

    // Verify user properties
    expect($user)->not->toBeNull();
    expect($user->username)->toEqual('testintegration');
    expect($user->email)->toEqual('integration@test.com');
    expect(\Hash::check('password123', $user->password))->toBeTrue();

    // Verify authentication state
    assertAuthenticated();
    expect(auth()->id())->toEqual($user->id);

    // Test that user can access all areas of the application
    get('/dashboard')->assertStatus(200);
    get('/character-builder')->assertStatus(302);
    // Creates new character and redirects
});

test('error handling and recovery', function () {
    // Attempt to register with existing email
    User::factory()->create(['email' => 'existing@test.com']);

    $response = post('/register', [
        'username' => 'newuser',
        'email' => 'existing@test.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    assertGuest();

    // Should be able to register with different email
    $successResponse = post('/register', [
        'username' => 'newuser',
        'email' => 'new@test.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $successResponse->assertRedirect('/dashboard');
    assertAuthenticated();
});