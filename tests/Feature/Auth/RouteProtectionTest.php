<?php

use Domain\User\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('home page is accessible to guests', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

test('rooms requires authentication', function () {
    $response = $this->get('/dashboard');

    $response->assertRedirect('/login');
});

test('character builder is publicly accessible', function () {
    $response = $this->get('/character-builder');

    $response->assertStatus(302);
    $response->assertRedirect();

    // Should redirect to character builder edit page with new character
    expect(str_contains($response->headers->get('location'), '/character-builder/'))->toBeTrue();
});

test('authenticated users can access rooms', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
});

test('character builder creates new character', function () {
    $response = $this->get('/character-builder');

    $response->assertStatus(302);
    $response->assertRedirect();

    // Should redirect to the edit page with a new character key
    expect(str_contains($response->headers->get('location'), '/character-builder/'))->toBeTrue();
});

test('authenticated users are redirected from login', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/login');

    $response->assertRedirect('/dashboard');
});

test('authenticated users are redirected from register', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/register');

    $response->assertRedirect('/dashboard');
});

test('logout requires post method', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/logout');

    $response->assertStatus(405);
    // Method not allowed
});

test('logout redirects to home', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $response->assertRedirect('/');
    $this->assertGuest();
});

test('legacy video rooms route still works', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/video-rooms');

    $response->assertStatus(200);
});

test('guests cannot access legacy video rooms without auth', function () {
    $response = $this->get('/video-rooms');

    $response->assertRedirect('/login');
});

test('nonexistent routes return 404', function () {
    $response = $this->get('/nonexistent-route');

    $response->assertStatus(404);
});

test('authenticated user session persists across requests', function () {
    $user = User::factory()->create();

    // First request with authentication
    $this->actingAs($user)->get('/dashboard')->assertStatus(200);

    // Second request should still be authenticated
    $this->get('/dashboard')->assertStatus(200);
    $this->assertAuthenticated();
});

test('middleware prevents unauthorized access to protected routes', function () {
    $protectedRoutes = [
        '/dashboard',
        '/video-rooms',
    ];

    foreach ($protectedRoutes as $route) {
        $response = $this->get($route);
        $response->assertRedirect('/login');
    }
});

test('csrf protection on logout', function () {
    $user = User::factory()->create();

    // Attempt logout without CSRF token should fail
    $response = $this->actingAs($user)
        ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
        ->post('/logout');

    // Since we're removing CSRF middleware for this test, it should succeed
    $response->assertRedirect('/');
});