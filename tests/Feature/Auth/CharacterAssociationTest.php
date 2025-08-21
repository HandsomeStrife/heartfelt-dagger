<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Domain\Character\Models\Character;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CharacterAssociationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_associates_characters_with_user_on_login(): void
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        // Create some anonymous characters
        $character1 = Character::factory()->create(['user_id' => null]);
        $character2 = Character::factory()->create(['user_id' => null]);

        // Simulate login with character keys
        $response = $this->post(route('login.post'), [
            'email' => 'test@example.com',
            'password' => 'password',
            'character_keys' => [$character1->character_key, $character2->character_key],
        ]);

        // Assert successful login
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);

        // Assert characters are now associated with the user
        $this->assertEquals($user->id, $character1->fresh()->user_id);
        $this->assertEquals($user->id, $character2->fresh()->user_id);
    }

    #[Test]
    public function it_associates_characters_with_user_on_registration(): void
    {
        // Create some anonymous characters
        $character1 = Character::factory()->create(['user_id' => null]);
        $character2 = Character::factory()->create(['user_id' => null]);

        // Simulate registration with character keys
        $response = $this->post(route('register.post'), [
            'username' => 'testuser',
            'email' => 'newuser@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'character_keys' => [$character1->character_key, $character2->character_key],
        ]);

        // Assert successful registration and login
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        // Get the newly created user
        $user = User::where('email', 'newuser@example.com')->first();
        $this->assertNotNull($user);

        // Assert characters are now associated with the new user
        $this->assertEquals($user->id, $character1->fresh()->user_id);
        $this->assertEquals($user->id, $character2->fresh()->user_id);
    }

    #[Test]
    public function it_only_associates_characters_with_null_user_id_on_login(): void
    {
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
        $response = $this->post(route('login.post'), [
            'email' => 'test@example.com',
            'password' => 'password',
            'character_keys' => [$anonymous_character->character_key, $owned_character->character_key],
        ]);

        // Assert successful login
        $response->assertRedirect('/dashboard');

        // Assert only the anonymous character was associated
        $this->assertEquals($user->id, $anonymous_character->fresh()->user_id);
        $this->assertEquals($another_user->id, $owned_character->fresh()->user_id);
    }

    #[Test]
    public function it_handles_login_without_character_keys(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post(route('login.post'), [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function it_handles_registration_without_character_keys(): void
    {
        $response = $this->post(route('register.post'), [
            'username' => 'testuser',
            'email' => 'newuser@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $user = User::where('email', 'newuser@example.com')->first();
        $this->assertNotNull($user);
    }

    #[Test]
    public function it_handles_empty_character_keys_array(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post(route('login.post'), [
            'email' => 'test@example.com',
            'password' => 'password',
            'character_keys' => [],
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function it_handles_invalid_character_keys(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post(route('login.post'), [
            'email' => 'test@example.com',
            'password' => 'password',
            'character_keys' => ['invalid-key-1', 'invalid-key-2'],
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }
}
