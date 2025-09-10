<?php

declare(strict_types=1);
use Domain\Character\Models\Character;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\post;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('characters are saved against user on login with character keys', function () {
    // Create a user
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    // Create some anonymous characters (simulating localStorage characters)
    $character1 = Character::factory()->create(['user_id' => null]);
    $character2 = Character::factory()->create(['user_id' => null]);
    $character3 = Character::factory()->create(['user_id' => null]);

    // Verify characters are initially not associated with any user
    expect($character1->fresh()->user_id)->toBeNull();
    expect($character2->fresh()->user_id)->toBeNull();
    expect($character3->fresh()->user_id)->toBeNull();

    // Simulate login with character keys (this is what would happen when localStorage is read)
    $response = post(route('login.post'), [
        'email' => 'test@example.com',
        'password' => 'password',
        'character_keys' => [$character1->character_key, $character2->character_key],
    ]);

    // User should be redirected after successful login
    $response->assertRedirect('/dashboard');

    // Characters should now be associated with the user
    expect($character1->fresh()->user_id)->toEqual($user->id);
    expect($character2->fresh()->user_id)->toEqual($user->id);

    // Character that wasn't in localStorage should remain unassociated
    expect($character3->fresh()->user_id)->toBeNull();

    // Verify user is authenticated
    assertAuthenticatedAs($user);
});
test('characters are saved against user on registration with character keys', function () {
    // Create some anonymous characters (simulating localStorage characters)
    $character1 = Character::factory()->create(['user_id' => null]);
    $character2 = Character::factory()->create(['user_id' => null]);

    // Verify characters are initially not associated with any user
    expect($character1->fresh()->user_id)->toBeNull();
    expect($character2->fresh()->user_id)->toBeNull();

    // Simulate registration with character keys
    $response = post(route('register.post'), [
        'username' => 'newuser',
        'email' => 'newuser@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'character_keys' => [$character1->character_key, $character2->character_key],
    ]);

    // User should be redirected after successful registration
    $response->assertRedirect('/dashboard');

    // Find the newly created user
    $user = User::where('email', 'newuser@example.com')->first();
    expect($user)->not->toBeNull();

    // Characters should now be associated with the new user
    expect($character1->fresh()->user_id)->toEqual($user->id);
    expect($character2->fresh()->user_id)->toEqual($user->id);

    // Verify user is authenticated
    assertAuthenticatedAs($user);
});
test('only anonymous characters are associated on login', function () {
    // Create users
    $user1 = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);
    $user2 = User::factory()->create();

    // Create characters: some anonymous, some already owned
    $anonymousChar1 = Character::factory()->create(['user_id' => null]);
    $anonymousChar2 = Character::factory()->create(['user_id' => null]);
    $ownedChar = Character::factory()->create(['user_id' => $user2->id]);

    // Try to login with a mix of anonymous and owned character keys
    $response = post(route('login.post'), [
        'email' => 'test@example.com',
        'password' => 'password',
        'character_keys' => [
            $anonymousChar1->character_key,
            $anonymousChar2->character_key,
            $ownedChar->character_key, // This should NOT be transferred
        ],
    ]);

    $response->assertRedirect('/dashboard');

    // Anonymous characters should be associated with user1
    expect($anonymousChar1->fresh()->user_id)->toEqual($user1->id);
    expect($anonymousChar2->fresh()->user_id)->toEqual($user1->id);

    // Already owned character should remain with user2
    expect($ownedChar->fresh()->user_id)->toEqual($user2->id);
});
test('user can access associated characters after login', function () {
    // Create a user
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    // Create anonymous characters
    $character1 = Character::factory()->create(['user_id' => null]);
    $character2 = Character::factory()->create(['user_id' => null]);

    // Login with character keys
    post(route('login.post'), [
        'email' => 'test@example.com',
        'password' => 'password',
        'character_keys' => [$character1->character_key, $character2->character_key],
    ]);

    // Verify user can access their characters through the relationship
    expect($user->fresh()->characters)->toHaveCount(2);

    $userCharacterKeys = $user->fresh()->characters->pluck('character_key')->toArray();
    expect($userCharacterKeys)->toContain($character1->character_key);
    expect($userCharacterKeys)->toContain($character2->character_key);
});
test('system handles invalid character keys gracefully', function () {
    // Create a user
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    // Create one valid character
    $validChar = Character::factory()->create(['user_id' => null]);

    // Login with mix of valid and invalid character keys
    $response = post(route('login.post'), [
        'email' => 'test@example.com',
        'password' => 'password',
        'character_keys' => [
            $validChar->character_key,
            'invalid-key-1',
            'invalid-key-2',
        ],
    ]);

    // Should still login successfully
    $response->assertRedirect('/dashboard');
    assertAuthenticatedAs($user);

    // Valid character should be associated
    expect($validChar->fresh()->user_id)->toEqual($user->id);

    // Should have exactly 1 character associated
    expect($user->fresh()->characters)->toHaveCount(1);
});
