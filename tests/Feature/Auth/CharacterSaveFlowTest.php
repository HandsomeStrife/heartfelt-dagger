<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Domain\Character\Models\Character;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CharacterSaveFlowTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function characters_are_saved_against_user_on_login_with_character_keys(): void
    {
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
        $this->assertNull($character1->fresh()->user_id);
        $this->assertNull($character2->fresh()->user_id);
        $this->assertNull($character3->fresh()->user_id);

        // Simulate login with character keys (this is what would happen when localStorage is read)
        $response = $this->post(route('login.post'), [
            'email' => 'test@example.com',
            'password' => 'password',
            'character_keys' => [$character1->character_key, $character2->character_key],
        ]);

        // User should be redirected after successful login
        $response->assertRedirect('/dashboard');
        
        // Characters should now be associated with the user
        $this->assertEquals($user->id, $character1->fresh()->user_id);
        $this->assertEquals($user->id, $character2->fresh()->user_id);
        
        // Character that wasn't in localStorage should remain unassociated
        $this->assertNull($character3->fresh()->user_id);

        // Verify user is authenticated
        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function characters_are_saved_against_user_on_registration_with_character_keys(): void
    {
        // Create some anonymous characters (simulating localStorage characters)
        $character1 = Character::factory()->create(['user_id' => null]);
        $character2 = Character::factory()->create(['user_id' => null]);

        // Verify characters are initially not associated with any user
        $this->assertNull($character1->fresh()->user_id);
        $this->assertNull($character2->fresh()->user_id);

        // Simulate registration with character keys
        $response = $this->post(route('register.post'), [
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
        $this->assertNotNull($user);

        // Characters should now be associated with the new user
        $this->assertEquals($user->id, $character1->fresh()->user_id);
        $this->assertEquals($user->id, $character2->fresh()->user_id);

        // Verify user is authenticated
        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function only_anonymous_characters_are_associated_on_login(): void
    {
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
        $response = $this->post(route('login.post'), [
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
        $this->assertEquals($user1->id, $anonymousChar1->fresh()->user_id);
        $this->assertEquals($user1->id, $anonymousChar2->fresh()->user_id);

        // Already owned character should remain with user2
        $this->assertEquals($user2->id, $ownedChar->fresh()->user_id);
    }

    #[Test]
    public function user_can_access_associated_characters_after_login(): void
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        // Create anonymous characters
        $character1 = Character::factory()->create(['user_id' => null]);
        $character2 = Character::factory()->create(['user_id' => null]);

        // Login with character keys
        $this->post(route('login.post'), [
            'email' => 'test@example.com',
            'password' => 'password',
            'character_keys' => [$character1->character_key, $character2->character_key],
        ]);

        // Verify user can access their characters through the relationship
        $this->assertCount(2, $user->fresh()->characters);
        
        $userCharacterKeys = $user->fresh()->characters->pluck('character_key')->toArray();
        $this->assertContains($character1->character_key, $userCharacterKeys);
        $this->assertContains($character2->character_key, $userCharacterKeys);
    }

    #[Test]
    public function system_handles_invalid_character_keys_gracefully(): void
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        // Create one valid character
        $validChar = Character::factory()->create(['user_id' => null]);

        // Login with mix of valid and invalid character keys
        $response = $this->post(route('login.post'), [
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
        $this->assertAuthenticatedAs($user);

        // Valid character should be associated
        $this->assertEquals($user->id, $validChar->fresh()->user_id);

        // Should have exactly 1 character associated
        $this->assertCount(1, $user->fresh()->characters);
    }
}
