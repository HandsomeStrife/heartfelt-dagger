<?php

use Domain\User\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('navigation shows character links for unauthenticated users', function () {
    $response = get('/');

    // Guest users should see branding and guest navigation links
    $response->assertSee('HeartfeltDagger');
    $response->assertSee('Characters');
    $response->assertSee('Tools');
    $response->assertSee('Login');
    $response->assertSee('Register');

    // But should NOT see auth-only navigation features
    // Note: "Campaigns" appears in page content, so we check for dashboard and logout instead
    $response->assertDontSee('Dashboard');
    $response->assertDontSee('Logout');
});

test('navigation shows user menu for authenticated users', function () {
    $user = User::factory()->create([
        'username' => 'testuser',
    ]);

    $response = actingAs($user)->get('/dashboard');

    $response->assertSee('testuser');
    $response->assertSee('Dashboard');
    $response->assertSee('Characters');
    $response->assertSee('Resources');
    $response->assertSee('Campaigns');
    $response->assertSee('Logout');
    $response->assertDontSee('Login');
    $response->assertDontSee('Register');
});

test('navigation displays user avatar initial', function () {
    $user = User::factory()->create([
        'username' => 'TestUser',
    ]);

    $response = actingAs($user)->get('/');

    // Should show first letter of username
    $response->assertSee('T');
});

test('navigation brand links to home', function () {
    $response = get('/');

    $response->assertSee('HeartfeltDagger');
    $response->assertSee('href="/"', false);
});

test('character builder link works', function () {
    $response = get('/character-builder');

    $response->assertStatus(302);
    $response->assertRedirect();

    // Should redirect to the character builder edit page with new character
    expect(str_contains($response->headers->get('location'), '/character-builder/'))->toBeTrue();
});

test('campaigns link works', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->get('/campaigns');

    $response->assertStatus(200);
});

test('navigation is not shown on login page', function () {
    $response = get('/login');

    // Navigation should not be visible on login page
    $response->assertDontSee('Characters');
    $response->assertDontSee('Dashboard');
    $response->assertDontSee('Campaigns');
});

test('navigation is not shown on register page', function () {
    $response = get('/register');

    // Navigation should not be visible on register page
    $response->assertDontSee('Characters');
    $response->assertDontSee('Dashboard');
    $response->assertDontSee('Campaigns');
});

test('navigation is shown on protected pages', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->get('/dashboard');

    $response->assertSee('HeartfeltDagger');
    $response->assertSee($user->username);
});

test('logout form submission', function () {
    $user = User::factory()->create();

    actingAs($user);
    assertAuthenticated();

    $response = post('/logout');

    $response->assertRedirect('/');
    assertGuest();
});

test('register page works directly', function () {
    // Register page should work when accessed directly
    $registerResponse = get('/register');
    $registerResponse->assertStatus(200);
    $registerResponse->assertSee('Join the Adventure');
});

test('login page works directly', function () {
    // Login page should work when accessed directly
    $loginResponse = get('/login');
    $loginResponse->assertStatus(200);
    $loginResponse->assertSee('Enter the Realm');
});

test('dropdown menu structure', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->get('/dashboard');

    // Check for dropdown elements
    $response->assertSee('Dashboard');
    $response->assertSee('Characters');
    $response->assertSee('Resources');
    $response->assertSee('Campaigns');
    $response->assertSee('Logout');
});

test('navigation styling includes heartfeltdagger branding', function () {
    $response = get('/');

    // Check for HeartfeltDagger branding and styling
    $response->assertSee('HeartfeltDagger');
    $response->assertSee('font-outfit');
    $response->assertSee('text-white');
});
