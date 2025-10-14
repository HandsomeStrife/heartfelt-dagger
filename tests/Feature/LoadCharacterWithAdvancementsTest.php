<?php

declare(strict_types=1);

use Domain\Character\Actions\LoadCharacterAction;
use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterAdvancement;
use Domain\Character\Models\CharacterDomainCard;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('LoadCharacterAction with Advancements', function () {
    test('loads level 1 character with no advancements', function () {
        // Create a level 1 character
        $character = Character::factory()->create([
            'level' => 1,
            'proficiency' => 1,
        ]);

        $action = new LoadCharacterAction();
        $builder_data = $action->execute($character->character_key);

        expect($builder_data)->not->toBeNull()
            ->and($builder_data->starting_level)->toBe(1)
            ->and($builder_data->creation_advancements)->toBeEmpty()
            ->and($builder_data->creation_tier_experiences)->toBeEmpty()
            ->and($builder_data->creation_domain_cards)->toBeEmpty();
    });

    test('loads level 3 character with advancement records from database', function () {
        // Create a level 3 character with advancements in the database
        $character = Character::factory()->create([
            'name' => 'Test Warrior',
            'class' => 'warrior',
            'subclass' => 'stalwart',
            'ancestry' => 'human',
            'community' => 'highborne',
            'level' => 3,
            'proficiency' => 1,
            'character_data' => [
                'tier_achievements' => [
                    2 => [
                        'experiences' => [
                            'name' => 'Leadership',
                            'description' => 'Leading troops',
                        ],
                    ],
                ],
            ],
        ]);

        // Create advancement records
        CharacterAdvancement::create([
            'character_id' => $character->id,
            'tier' => 1,
            'level' => 2,
            'advancement_number' => 1,
            'advancement_type' => 'hit_point',
            'advancement_data' => [],
            'description' => 'Gained 1 Hit Point',
        ]);

        CharacterAdvancement::create([
            'character_id' => $character->id,
            'tier' => 1,
            'level' => 2,
            'advancement_number' => 2,
            'advancement_type' => 'stress_slot',
            'advancement_data' => [],
            'description' => 'Gained 1 Stress Slot',
        ]);

        CharacterAdvancement::create([
            'character_id' => $character->id,
            'tier' => 1,
            'level' => 3,
            'advancement_number' => 1,
            'advancement_type' => 'trait_bonus',
            'advancement_data' => ['traits' => ['strength', 'instinct']],
            'description' => 'Trait Bonus: Strength, Instinct',
        ]);

        CharacterAdvancement::create([
            'character_id' => $character->id,
            'tier' => 1,
            'level' => 3,
            'advancement_number' => 2,
            'advancement_type' => 'experience_bonus',
            'advancement_data' => ['experience_name' => 'Combat Training'],
            'description' => 'Experience Bonus: Combat Training',
        ]);

        // Create domain card records
        CharacterDomainCard::create([
            'character_id' => $character->id,
            'domain' => 'blade',
            'ability_key' => 'get back up',
            'ability_level' => 1,
        ]);

        CharacterDomainCard::create([
            'character_id' => $character->id,
            'domain' => 'blade',
            'ability_key' => 'bleed them dry',
            'ability_level' => 1,
        ]);

        CharacterDomainCard::create([
            'character_id' => $character->id,
            'domain' => 'bone',
            'ability_key' => 'from the brink',
            'ability_level' => 1,
        ]);

        // Reload the character
        $load_action = new LoadCharacterAction();
        $loaded_data = $load_action->execute($character->character_key);

        // Verify loaded data
        expect($loaded_data)->not->toBeNull()
            ->and($loaded_data->starting_level)->toBe(3)
            ->and($loaded_data->creation_advancements)->toHaveCount(2)
            ->and($loaded_data->creation_advancements[2])->toHaveCount(2)
            ->and($loaded_data->creation_advancements[3])->toHaveCount(2)
            ->and($loaded_data->creation_domain_cards)->toHaveCount(3)
            ->and($loaded_data->creation_domain_cards[1])->toBe('get back up')
            ->and($loaded_data->creation_domain_cards[2])->toBe('bleed them dry')
            ->and($loaded_data->creation_domain_cards[3])->toBe('from the brink');
    });

    test('loads character by public key', function () {
        // Create a level 2 character
        $character = Character::factory()->create([
            'name' => 'Public Character',
            'class' => 'warrior',
            'level' => 2,
            'proficiency' => 1,
        ]);

        CharacterAdvancement::create([
            'character_id' => $character->id,
            'tier' => 1,
            'level' => 2,
            'advancement_number' => 1,
            'advancement_type' => 'hit_point',
            'advancement_data' => [],
            'description' => 'Gained 1 Hit Point',
        ]);

        CharacterDomainCard::create([
            'character_id' => $character->id,
            'domain' => 'blade',
            'ability_key' => 'get back up',
            'ability_level' => 1,
        ]);

        CharacterDomainCard::create([
            'character_id' => $character->id,
            'domain' => 'blade',
            'ability_key' => 'bleed them dry',
            'ability_level' => 1,
        ]);

        // Load by public key
        $load_action = new LoadCharacterAction();
        $loaded_data = $load_action->execute($character->public_key);

        expect($loaded_data)->not->toBeNull()
            ->and($loaded_data->starting_level)->toBe(2)
            ->and($loaded_data->name)->toBe('Public Character');
    });
});
