<?php

declare(strict_types=1);
use Domain\Character\Models\Character;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('associates characters with user on login', function () {
    // Create a user
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    // Create some anonymous characters
    $character1 = Character::factory()->create(['user_id' => null]);
    $character2 = Character::factory()->create(['user_id' => null]);

    // Simulate login with character keys
    $response = post(route('login.post'), [
        'email' => 'test@example.com',
        'password' => 'password',
        'character_keys' => [$character1->character_key, $character2->character_key],
    ]);

    // Assert successful login
    $response->assertRedirect('/dashboard');
    \Pest\Laravel\assertAuthenticatedAs($user);

    // Assert characters are now associated with the user
    expect($character1->fresh()->user_id)->toEqual($user->id);
    expect($character2->fresh()->user_id)->toEqual($user->id);
});
it('associates characters with user on registration', function () {
    // Create some anonymous characters
    $character1 = Character::factory()->create(['user_id' => null]);
    $character2 = Character::factory()->create(['user_id' => null]);

    // Simulate registration with character keys
    $response = post(route('register.post'), [
        'username' => 'testuser',
        'email' => 'newuser@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'character_keys' => [$character1->character_key, $character2->character_key],
    ]);

    // Assert successful registration and login
    $response->assertRedirect('/dashboard');
    \Pest\Laravel\assertAuthenticated();

    // Get the newly created user
    $user = User::where('email', 'newuser@example.com')->first();
    expect($user)->not->toBeNull();

    // Assert characters are now associated with the new user
    expect($character1->fresh()->user_id)->toEqual($user->id);
    expect($character2->fresh()->user_id)->toEqual($user->id);
});
it('only associates characters with null user id on login', function () {
    // Create users
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);
    $another_user = User::factory()->create();

    // Create characters
    $anonymous_character = Character::factory()->create(['user_id' => null]);
    $owned_character = Character::factory()->create(['user_id' => $another_user->id]);

    // Simulate login with both character keys
    $response = post(route('login.post'), [
        'email' => 'test@example.com',
        'password' => 'password',
        'character_keys' => [$anonymous_character->character_key, $owned_character->character_key],
    ]);

    // Assert successful login
    $response->assertRedirect('/dashboard');

    // Assert only the anonymous character was associated
    expect($anonymous_character->fresh()->user_id)->toEqual($user->id);
    expect($owned_character->fresh()->user_id)->toEqual($another_user->id);
});
it('handles login without character keys', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    $response = post(route('login.post'), [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $response->assertRedirect('/dashboard');
    \Pest\Laravel\assertAuthenticatedAs($user);
});
it('handles registration without character keys', function () {
    $response = post(route('register.post'), [
        'username' => 'testuser',
        'email' => 'newuser@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect('/dashboard');
    \Pest\Laravel\assertAuthenticated();

    $user = User::where('email', 'newuser@example.com')->first();
    expect($user)->not->toBeNull();
});
it('handles empty character keys array', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    $response = post(route('login.post'), [
        'email' => 'test@example.com',
        'password' => 'password',
        'character_keys' => [],
    ]);

    $response->assertRedirect('/dashboard');
    \Pest\Laravel\assertAuthenticatedAs($user);
});
it('handles invalid character keys', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    $response = post(route('login.post'), [
        'email' => 'test@example.com',
        'password' => 'password',
        'character_keys' => ['invalid-key-1', 'invalid-key-2'],
    ]);

    $response->assertRedirect('/dashboard');
    \Pest\Laravel\assertAuthenticatedAs($user);
});
