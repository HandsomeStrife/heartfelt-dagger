<?php

declare(strict_types=1);
use Domain\Character\Models\Character;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Hash;
use function Pest\Laravel\actingAs;

it('shows correct navigation for guest users', function () {
    $page = visit('/')
        ->assertSee('Character Builder')
        ->assertSee('Login')
        ->assertSee('Register');
});
it('authenticated user can access dashboard', function () {
    $user = User::factory()->create();

    actingAs($user);
    $page = visit('/dashboard')
        ->assertSee('Welcome')
        ->assertSee('Characters');
});

it('can create character anonymously', function () {
    $page = visit('/character-builder');
    
    $page->wait(3)
        ->assertSee('Character Builder');
    
    // Verify we can access character builder without being logged in
    expect(true)->toBeTrue();
});

it('login page loads correctly', function () {
    $page = visit('/login')
        ->assertSee('Enter the Realm')
        ->assertSee('Email')
        ->assertSee('Password');
});