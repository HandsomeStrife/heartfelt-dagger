<?php

declare(strict_types=1);

use Domain\Character\Models\Character;
use Domain\User\Actions\RegisterUserAction;
use Domain\User\Data\RegisterUserData;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use function Pest\Laravel\{actingAs, get, post, put, patch, delete};
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('complete character save flow from anonymous to user owned', function () {
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
    expect($character1->user_id)->toBeNull();
    expect($character2->user_id)->toBeNull();
    assertDatabaseHas('characters', [
        'id' => $character1->id,
        'user_id' => null,
        'name' => 'Elara the Brave',
    ]);
    assertDatabaseHas('characters', [
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
    assertDatabaseHas('users', [
        'id' => $userData->id,
        'username' => 'heroic_player',
        'email' => 'hero@daggerheart.com',
    ]);

    // Step 3: Verify characters are now associated with the user
    $character1Fresh = $character1->fresh();
    $character2Fresh = $character2->fresh();

    expect($character1Fresh->user_id)->toEqual($userData->id);
    expect($character2Fresh->user_id)->toEqual($userData->id);

    assertDatabaseHas('characters', [
        'id' => $character1->id,
        'user_id' => $userData->id,
        'name' => 'Elara the Brave',
    ]);
    assertDatabaseHas('characters', [
        'id' => $character2->id,
        'user_id' => $userData->id,
        'name' => 'Thorin Ironforge',
    ]);

    // Step 4: Verify user can access their characters through the relationship
    $user = User::find($userData->id);
    $userCharacters = $user->characters;

    expect($userCharacters)->toHaveCount(2);
    expect($userCharacters->contains('id', $character1->id))->toBeTrue();
    expect($userCharacters->contains('id', $character2->id))->toBeTrue();

    // Verify character details are preserved
    $elaraChar = $userCharacters->where('name', 'Elara the Brave')->first();
    $thorinChar = $userCharacters->where('name', 'Thorin Ironforge')->first();

    expect($elaraChar)->not->toBeNull();
    expect($thorinChar)->not->toBeNull();
    expect($elaraChar->class)->toEqual('warrior');
    expect($elaraChar->ancestry)->toEqual('human');
    expect($thorinChar->class)->toEqual('guardian');
    expect($thorinChar->ancestry)->toEqual('dwarf');

    // Step 5: Verify character keys match what was stored in localStorage
    $userCharacterKeys = $userCharacters->pluck('character_key')->toArray();
    expect($userCharacterKeys)->toContain($character1->character_key);
    expect($userCharacterKeys)->toContain($character2->character_key);
    expect(sort($userCharacterKeys))->toEqual(sort($characterKeys));
});
test('complete flow works with login instead of registration', function () {
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
    $response = post(route('login.post'), [
        'email' => 'existing@user.com',
        'password' => 'password123',
        'character_keys' => [$character1->character_key, $character2->character_key],
    ]);

    // Step 3: Verify login was successful
    $response->assertRedirect('/dashboard');
    assertAuthenticatedAs($user);

    // Step 4: Verify characters are now owned by the user
    expect($character1->fresh()->user_id)->toEqual($user->id);
    expect($character2->fresh()->user_id)->toEqual($user->id);

    // Step 5: Verify user can access all their characters
    $userCharacters = $user->fresh()->characters;
    expect($userCharacters)->toHaveCount(2);
    expect($userCharacters->contains('name', 'Gandalf the Grey'))->toBeTrue();
    expect($userCharacters->contains('name', 'Aragorn Strider'))->toBeTrue();
});
test('flow handles mixed scenarios with existing owned characters', function () {
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
    $response = post(route('login.post'), [
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
    assertAuthenticatedAs($user1);

    // Step 4: Verify only anonymous characters were transferred
    expect($anonymousChar1->fresh()->user_id)->toEqual($user1->id);
    expect($anonymousChar2->fresh()->user_id)->toEqual($user1->id);
    expect($ownedByUser2->fresh()->user_id)->toEqual($user2->id);

    // Should remain with user2
    // Step 5: Verify user1 now has all their characters
    $user1Characters = $user1->fresh()->characters;
    expect($user1Characters)->toHaveCount(3);
    // 2 newly associated + 1 existing
    expect($user1Characters->contains('name', 'Anonymous Hero 1'))->toBeTrue();
    expect($user1Characters->contains('name', 'Anonymous Hero 2'))->toBeTrue();
    expect($user1Characters->contains('name', 'Existing User1 Char'))->toBeTrue();
    expect($user1Characters->contains('name', 'User2 Character'))->toBeFalse();
});
test('flow handles empty and invalid character keys gracefully', function () {
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
    $response = post(route('login.post'), [
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
    assertAuthenticatedAs($user);

    // Step 4: Verify only valid character was associated
    expect($validChar->fresh()->user_id)->toEqual($user->id);
    expect($user->fresh()->characters)->toHaveCount(1);
    expect($user->fresh()->characters->first()->name)->toEqual('Valid Character');
});
test('complete flow preserves all character data integrity', function () {
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

    expect($associatedChar)->not->toBeNull();
    expect($associatedChar->name)->toEqual('Complex Character');
    expect($associatedChar->pronouns)->toEqual('they/them');
    expect($associatedChar->class)->toEqual('guardian');
    expect($associatedChar->subclass)->toEqual('stalwart');
    expect($associatedChar->ancestry)->toEqual('dwarf');
    expect($associatedChar->community)->toEqual('ridgeborne');
    expect($associatedChar->level)->toEqual(3);
    expect($associatedChar->is_public)->toBeTrue();

    // Verify complex character_data is preserved
    expect($associatedChar->character_data)->toEqual($complexCharacterData);
    expect($associatedChar->character_data['background']['answers'][0])->toEqual('I grew up in the mountains.');
    expect($associatedChar->character_data['connections'][0])->toEqual('Trusted ally of the mountain clans.');
    expect($associatedChar->character_data['builder_version'])->toEqual('1.0');
});
