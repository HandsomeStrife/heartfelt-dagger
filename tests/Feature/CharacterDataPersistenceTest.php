<?php

declare(strict_types=1);

use Domain\Character\Models\Character;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('character data persists correctly to database', function () {
    $user = User::factory()->create();

    $characterData = [
        'user_id' => $user->id,
        'name' => 'Persistence Test Character',
        'character_key' => 'TEST123456',
        'public_key' => 'PUB123456',
        'selected_class' => 'guardian',
        'selected_subclass' => 'stalwart',
        'selected_ancestry' => 'dwarf',
        'selected_community' => 'ridgeborne',
        'assigned_traits' => [
            'agility' => 0,
            'strength' => 2,
            'finesse' => -1,
            'instinct' => 1,
            'presence' => 0,
            'knowledge' => 1,
        ],
        'selected_equipment' => [
            [
                'key' => 'plate-armor',
                'type' => 'armor',
                'data' => [
                    'name' => 'Plate Armor',
                    'baseScore' => 5,
                ],
            ],
            [
                'key' => 'warhammer',
                'type' => 'weapon',
                'data' => [
                    'name' => 'Warhammer',
                    'trait' => 'Strength',
                    'damage' => ['dice' => 'd10', 'modifier' => 3],
                ],
            ],
        ],
        'experiences' => [
            ['name' => 'Mountain Warfare', 'modifier' => 2],
            ['name' => 'Smithing', 'modifier' => 2],
        ],
        'selected_domain_cards' => [
            ['domain' => 'valor', 'ability_key' => 'inspiring-presence', 'ability_level' => 1],
            ['domain' => 'blade', 'ability_key' => 'weapon-mastery', 'ability_level' => 1],
        ],
        'background_answers' => [
            'What drives you to protect others?',
            'How did you learn to fight?',
            'What is your greatest fear?',
        ],
        'connection_answers' => [
            'We fought together in the war',
            'You saved my life once',
            'We share a dark secret',
        ],
        'personality_traits' => 'Stoic and reliable',
        'personal_history' => 'Grew up in the mountain fortress',
        'motivations' => 'Protect the innocent and uphold justice',
    ];

    $character = Character::create($characterData);

    // Verify character was created
    expect($character)->toBeInstanceOf(Character::class);
    expect($character->name)->toBe('Persistence Test Character');

    // Verify complex data structures persist correctly
    expect($character->assigned_traits)->toBe($characterData['assigned_traits']);
    expect($character->selected_equipment)->toBe($characterData['selected_equipment']);
    expect($character->experiences)->toBe($characterData['experiences']);
    expect($character->selected_domain_cards)->toBe($characterData['selected_domain_cards']);
    expect($character->background_answers)->toBe($characterData['background_answers']);
    expect($character->connection_answers)->toBe($characterData['connection_answers']);

    // Verify character can be retrieved from database
    $retrievedCharacter = Character::where('character_key', 'TEST123456')->first();
    expect($retrievedCharacter)->not->toBeNull();
    expect($retrievedCharacter->name)->toBe('Persistence Test Character');
    expect($retrievedCharacter->selected_class)->toBe('guardian');
});

test('character traits array structure validates correctly', function () {
    $user = User::factory()->create();

    $character = Character::factory()->create([
        'user_id' => $user->id,
        'assigned_traits' => [
            'agility' => -1,
            'strength' => 0,
            'finesse' => 0,
            'instinct' => 1,
            'presence' => 1,
            'knowledge' => 2,
        ],
    ]);

    // Retrieve from database
    $retrievedCharacter = Character::find($character->id);

    // Verify array structure is preserved
    expect($retrievedCharacter->assigned_traits)->toBeArray();
    expect($retrievedCharacter->assigned_traits)->toHaveKey('agility');
    expect($retrievedCharacter->assigned_traits)->toHaveKey('strength');
    expect($retrievedCharacter->assigned_traits)->toHaveKey('finesse');
    expect($retrievedCharacter->assigned_traits)->toHaveKey('instinct');
    expect($retrievedCharacter->assigned_traits)->toHaveKey('presence');
    expect($retrievedCharacter->assigned_traits)->toHaveKey('knowledge');

    // Verify values are correct types and values
    expect($retrievedCharacter->assigned_traits['agility'])->toBe(-1);
    expect($retrievedCharacter->assigned_traits['knowledge'])->toBe(2);
});

test('character equipment data structure persists with integrity', function () {
    $equipment = [
        [
            'key' => 'leather-armor',
            'type' => 'armor',
            'data' => [
                'name' => 'Leather Armor',
                'tier' => 1,
                'baseScore' => 3,
                'baseThresholds' => ['minor' => 1, 'major' => 2, 'severe' => 3],
                'features' => ['Lightweight'],
            ],
        ],
        [
            'key' => 'longsword',
            'type' => 'weapon',
            'data' => [
                'name' => 'Longsword',
                'tier' => 1,
                'type' => 'Primary',
                'trait' => 'Strength',
                'range' => 'Melee',
                'burden' => 'One-Handed',
                'damage' => [
                    'dice' => 'd8',
                    'modifier' => 2,
                    'type' => 'physical',
                ],
                'features' => ['Versatile'],
            ],
        ],
    ];

    $character = Character::factory()->create([
        'selected_equipment' => $equipment,
    ]);

    $retrievedCharacter = Character::find($character->id);

    expect($retrievedCharacter->selected_equipment)->toBe($equipment);
    expect($retrievedCharacter->selected_equipment[0]['data']['name'])->toBe('Leather Armor');
    expect($retrievedCharacter->selected_equipment[1]['data']['damage']['dice'])->toBe('d8');
});

test('character domain cards persist with proper structure', function () {
    $domainCards = [
        [
            'domain' => 'arcana',
            'ability_key' => 'elemental-blast',
            'ability_level' => 1,
        ],
        [
            'domain' => 'midnight',
            'ability_key' => 'shadow-step',
            'ability_level' => 1,
        ],
    ];

    $character = Character::factory()->create([
        'selected_class' => 'sorcerer',
        'selected_domain_cards' => $domainCards,
    ]);

    $retrievedCharacter = Character::find($character->id);

    expect($retrievedCharacter->selected_domain_cards)->toBe($domainCards);
    expect($retrievedCharacter->selected_domain_cards)->toHaveCount(2);

    // Verify each domain card has required fields
    foreach ($retrievedCharacter->selected_domain_cards as $card) {
        expect($card)->toHaveKey('domain');
        expect($card)->toHaveKey('ability_key');
        expect($card)->toHaveKey('ability_level');
        expect($card['ability_level'])->toBe(1); // Starting characters only get level 1
    }
});

test('character experiences maintain proper format', function () {
    $experiences = [
        ['name' => 'Wilderness Survival', 'modifier' => 2, 'description' => 'Living off the land'],
        ['name' => 'Noble Etiquette', 'modifier' => 2, 'description' => 'Court manners and politics'],
    ];

    $character = Character::factory()->create([
        'experiences' => $experiences,
    ]);

    $retrievedCharacter = Character::find($character->id);

    expect($retrievedCharacter->experiences)->toBe($experiences);

    foreach ($retrievedCharacter->experiences as $experience) {
        expect($experience)->toHaveKey('name');
        expect($experience)->toHaveKey('modifier');
        expect($experience['modifier'])->toBe(2); // All experiences have +2 modifier
        expect($experience['name'])->toBeString();
    }
});

test('character relationship with user persists correctly', function () {
    $user = User::factory()->create([
        'username' => 'testuser',
        'email' => 'test@example.com',
    ]);

    $character = Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'User Relationship Test',
    ]);

    // Test direct relationship
    expect($character->user_id)->toBe($user->id);

    // Test Eloquent relationship
    $retrievedCharacter = Character::with('user')->find($character->id);
    expect($retrievedCharacter->user)->not->toBeNull();
    expect($retrievedCharacter->user->username)->toBe('testuser');
    expect($retrievedCharacter->user->email)->toBe('test@example.com');
});

test('character unique keys are properly generated and stored', function () {
    $character = Character::factory()->create([
        'name' => 'Unique Keys Test',
    ]);

    expect($character->character_key)->not->toBeNull();
    expect($character->public_key)->not->toBeNull();
    expect(strlen($character->character_key))->toBe(10); // Based on factory pattern
    expect(strlen($character->public_key))->toBe(10);

    // Keys should be unique
    $character2 = Character::factory()->create([
        'name' => 'Another Character',
    ]);

    expect($character->character_key)->not->toBe($character2->character_key);
    expect($character->public_key)->not->toBe($character2->public_key);
});

test('character optional fields handle null values gracefully', function () {
    $character = Character::factory()->create([
        'name' => 'Minimal Character',
        'selected_class' => 'warrior',
        'selected_ancestry' => 'human',
        'selected_community' => 'wildborne',
        // Leave optional fields null
        'profile_image_path' => null,
        'physical_description' => null,
        'personality_traits' => null,
        'personal_history' => null,
        'motivations' => null,
        'selected_subclass' => null,
    ]);

    $retrievedCharacter = Character::find($character->id);

    expect($retrievedCharacter->profile_image_path)->toBeNull();
    expect($retrievedCharacter->physical_description)->toBeNull();
    expect($retrievedCharacter->personality_traits)->toBeNull();
    expect($retrievedCharacter->personal_history)->toBeNull();
    expect($retrievedCharacter->motivations)->toBeNull();
    expect($retrievedCharacter->selected_subclass)->toBeNull();

    // Required fields should still be present
    expect($retrievedCharacter->name)->toBe('Minimal Character');
    expect($retrievedCharacter->selected_class)->toBe('warrior');
});
