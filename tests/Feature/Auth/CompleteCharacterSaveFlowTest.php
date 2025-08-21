<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Domain\Character\Models\Character;
use Domain\User\Actions\RegisterUserAction;
use Domain\User\Data\RegisterUserData;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CompleteCharacterSaveFlowTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function complete_character_save_flow_from_anonymous_to_user_owned(): void
    {
        // Step 1: Create anonymous characters (simulating localStorage usage)
        $character1 = Character::factory()->create([
            'user_id' => null,
            'name' => 'Elara the Brave',
            'class' => 'warrior',
            'ancestry' => 'human',
            'community' => 'wildborne',
        ]);
        
        $character2 = Character::factory()->create([
            'user_id' => null,
            'name' => 'Thorin Ironforge',
            'class' => 'guardian',
            'ancestry' => 'dwarf',
            'community' => 'ridgeborne',
        ]);

        // Verify characters are initially anonymous
        $this->assertNull($character1->user_id);
        $this->assertNull($character2->user_id);
        $this->assertDatabaseHas('characters', [
            'id' => $character1->id,
            'user_id' => null,
            'name' => 'Elara the Brave',
        ]);
        $this->assertDatabaseHas('characters', [
            'id' => $character2->id,
            'user_id' => null,
            'name' => 'Thorin Ironforge',
        ]);

        // Step 2: User registers with character keys (simulating localStorage integration)
        $characterKeys = [$character1->character_key, $character2->character_key];
        
        $registerData = RegisterUserData::from([
            'username' => 'heroic_player',
            'email' => 'hero@daggerheart.com',
            'password' => 'secretpassword123',
            'password_confirmation' => 'secretpassword123',
        ]);

        $registerAction = app(RegisterUserAction::class);
        $userData = $registerAction->execute($registerData, $characterKeys);

        // Verify user was created successfully
        $this->assertDatabaseHas('users', [
            'id' => $userData->id,
            'username' => 'heroic_player',
            'email' => 'hero@daggerheart.com',
        ]);

        // Step 3: Verify characters are now associated with the user
        $character1Fresh = $character1->fresh();
        $character2Fresh = $character2->fresh();

        $this->assertEquals($userData->id, $character1Fresh->user_id);
        $this->assertEquals($userData->id, $character2Fresh->user_id);
        
        $this->assertDatabaseHas('characters', [
            'id' => $character1->id,
            'user_id' => $userData->id,
            'name' => 'Elara the Brave',
        ]);
        $this->assertDatabaseHas('characters', [
            'id' => $character2->id,
            'user_id' => $userData->id,
            'name' => 'Thorin Ironforge',
        ]);

        // Step 4: Verify user can access their characters through the relationship
        $user = User::find($userData->id);
        $userCharacters = $user->characters;

        $this->assertCount(2, $userCharacters);
        $this->assertTrue($userCharacters->contains('id', $character1->id));
        $this->assertTrue($userCharacters->contains('id', $character2->id));
        
        // Verify character details are preserved
        $elaraChar = $userCharacters->where('name', 'Elara the Brave')->first();
        $thorinChar = $userCharacters->where('name', 'Thorin Ironforge')->first();
        
        $this->assertNotNull($elaraChar);
        $this->assertNotNull($thorinChar);
        $this->assertEquals('warrior', $elaraChar->class);
        $this->assertEquals('human', $elaraChar->ancestry);
        $this->assertEquals('guardian', $thorinChar->class);
        $this->assertEquals('dwarf', $thorinChar->ancestry);

        // Step 5: Verify character keys match what was stored in localStorage
        $userCharacterKeys = $userCharacters->pluck('character_key')->toArray();
        $this->assertContains($character1->character_key, $userCharacterKeys);
        $this->assertContains($character2->character_key, $userCharacterKeys);
        $this->assertEquals(sort($characterKeys), sort($userCharacterKeys));
    }

    #[Test]
    public function complete_flow_works_with_login_instead_of_registration(): void
    {
        // Step 1: Create a user and anonymous characters
        $user = User::factory()->create([
            'email' => 'existing@user.com',
            'password' => Hash::make('password123'),
        ]);

        $character1 = Character::factory()->create([
            'user_id' => null,
            'name' => 'Gandalf the Grey',
        ]);
        
        $character2 = Character::factory()->create([
            'user_id' => null,
            'name' => 'Aragorn Strider',
        ]);

        // Step 2: Login with character keys
        $response = $this->post(route('login.post'), [
            'email' => 'existing@user.com',
            'password' => 'password123',
            'character_keys' => [$character1->character_key, $character2->character_key],
        ]);

        // Step 3: Verify login was successful
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);

        // Step 4: Verify characters are now owned by the user
        $this->assertEquals($user->id, $character1->fresh()->user_id);
        $this->assertEquals($user->id, $character2->fresh()->user_id);

        // Step 5: Verify user can access all their characters
        $userCharacters = $user->fresh()->characters;
        $this->assertCount(2, $userCharacters);
        $this->assertTrue($userCharacters->contains('name', 'Gandalf the Grey'));
        $this->assertTrue($userCharacters->contains('name', 'Aragorn Strider'));
    }

    #[Test]
    public function flow_handles_mixed_scenarios_with_existing_owned_characters(): void
    {
        // Step 1: Create users and characters
        $user1 = User::factory()->create([
            'email' => 'user1@example.com',
            'password' => Hash::make('password'),
        ]);
        
        $user2 = User::factory()->create();

        // Mix of anonymous and owned characters
        $anonymousChar1 = Character::factory()->create(['user_id' => null, 'name' => 'Anonymous Hero 1']);
        $anonymousChar2 = Character::factory()->create(['user_id' => null, 'name' => 'Anonymous Hero 2']);
        $ownedByUser2 = Character::factory()->create(['user_id' => $user2->id, 'name' => 'User2 Character']);
        $existingUser1Char = Character::factory()->create(['user_id' => $user1->id, 'name' => 'Existing User1 Char']);

        // Step 2: User1 logs in with a mix of character keys
        $response = $this->post(route('login.post'), [
            'email' => 'user1@example.com',
            'password' => 'password',
            'character_keys' => [
                $anonymousChar1->character_key,
                $anonymousChar2->character_key,
                $ownedByUser2->character_key, // Should NOT be transferred
            ],
        ]);

        // Step 3: Verify login success
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user1);

        // Step 4: Verify only anonymous characters were transferred
        $this->assertEquals($user1->id, $anonymousChar1->fresh()->user_id);
        $this->assertEquals($user1->id, $anonymousChar2->fresh()->user_id);
        $this->assertEquals($user2->id, $ownedByUser2->fresh()->user_id); // Should remain with user2

        // Step 5: Verify user1 now has all their characters
        $user1Characters = $user1->fresh()->characters;
        $this->assertCount(3, $user1Characters); // 2 newly associated + 1 existing
        $this->assertTrue($user1Characters->contains('name', 'Anonymous Hero 1'));
        $this->assertTrue($user1Characters->contains('name', 'Anonymous Hero 2'));
        $this->assertTrue($user1Characters->contains('name', 'Existing User1 Char'));
        $this->assertFalse($user1Characters->contains('name', 'User2 Character'));
    }

    #[Test]
    public function flow_handles_empty_and_invalid_character_keys_gracefully(): void
    {
        // Step 1: Create user and one valid character
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $validChar = Character::factory()->create([
            'user_id' => null,
            'name' => 'Valid Character',
        ]);

        // Step 2: Login with mix of valid, invalid, and empty keys
        $response = $this->post(route('login.post'), [
            'email' => 'test@example.com',
            'password' => 'password',
            'character_keys' => [
                $validChar->character_key,
                'totally-invalid-key',
                'another-fake-key',
                // Note: not including empty string as that would fail validation
            ],
        ]);

        // Step 3: Verify login still works
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);

        // Step 4: Verify only valid character was associated
        $this->assertEquals($user->id, $validChar->fresh()->user_id);
        $this->assertCount(1, $user->fresh()->characters);
        $this->assertEquals('Valid Character', $user->fresh()->characters->first()->name);
    }

    #[Test]
    public function complete_flow_preserves_all_character_data_integrity(): void
    {
        // Step 1: Create a character with complex data
        $complexCharacterData = [
            'background' => [
                'answers' => [
                    'I grew up in the mountains.',
                    'My family were renowned smiths.',
                    'I seek to restore my clan\'s honor.',
                ],
            ],
            'connections' => [
                'Trusted ally of the mountain clans.',
                'Has a rivalry with the dark mage Valdris.',
                'Seeks to find the lost hammer of kings.',
            ],
            'creation_date' => '2025-01-15T10:30:00.000Z',
            'builder_version' => '1.0',
        ];

        $character = Character::factory()->create([
            'user_id' => null,
            'name' => 'Complex Character',
            'pronouns' => 'they/them',
            'class' => 'guardian',
            'subclass' => 'stalwart',
            'ancestry' => 'dwarf',
            'community' => 'ridgeborne',
            'level' => 3,
            'character_data' => $complexCharacterData,
            'is_public' => true,
        ]);

        // Step 2: Register user with this complex character
        $registerData = RegisterUserData::from([
            'username' => 'data_keeper',
            'email' => 'keeper@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $registerAction = app(RegisterUserAction::class);
        $userData = $registerAction->execute($registerData, [$character->character_key]);

        // Step 3: Verify all data is preserved
        $associatedChar = Character::where('user_id', $userData->id)->first();
        
        $this->assertNotNull($associatedChar);
        $this->assertEquals('Complex Character', $associatedChar->name);
        $this->assertEquals('they/them', $associatedChar->pronouns);
        $this->assertEquals('guardian', $associatedChar->class);
        $this->assertEquals('stalwart', $associatedChar->subclass);
        $this->assertEquals('dwarf', $associatedChar->ancestry);
        $this->assertEquals('ridgeborne', $associatedChar->community);
        $this->assertEquals(3, $associatedChar->level);
        $this->assertTrue($associatedChar->is_public);
        
        // Verify complex character_data is preserved
        $this->assertEquals($complexCharacterData, $associatedChar->character_data);
        $this->assertEquals('I grew up in the mountains.', $associatedChar->character_data['background']['answers'][0]);
        $this->assertEquals('Trusted ally of the mountain clans.', $associatedChar->character_data['connections'][0]);
        $this->assertEquals('1.0', $associatedChar->character_data['builder_version']);
    }
}
