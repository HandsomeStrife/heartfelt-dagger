<?php

declare(strict_types=1);

use Domain\Character\Actions\SaveCharacterAdvancementAction;
use Domain\Character\Actions\DeleteCharacterAdvancementAction;
use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterAdvancement;
use Domain\Character\Repositories\CharacterAdvancementRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Character Advancement Persistence', function () {
    
    test('saves character advancements correctly through Livewire', function () {
        // Create a test character
        $character = Character::factory()->create([
            'level' => 3,
        ]);
        
        // Prepare advancement data in the JavaScript format
        $advancementsData = [
            'creation_advancements' => [
                2 => [
                    ['type' => 'trait_bonus', 'traits' => ['agility', 'strength']],
                    ['type' => 'hit_point'],
                ],
                3 => [
                    ['type' => 'evasion'],
                    ['type' => 'stress'],
                ],
            ],
            'creation_tier_experiences' => [
                2 => ['name' => 'Wilderness Survival', 'description' => 'Expert in outdoor survival'],
            ],
            'creation_domain_cards' => [
                2 => ['domain' => 'blade', 'ability_key' => 'blade-strike', 'name' => 'Blade Strike', 'level' => 2],
                3 => ['domain' => 'bone', 'ability_key' => 'deathly-aura', 'name' => 'Deathly Aura', 'level' => 2],
            ],
            'creation_advancement_cards' => [
                'adv_3_0' => ['domain' => 'blade', 'ability_key' => 'battle-fury', 'name' => 'Battle Fury', 'level' => 2],
            ],
        ];
        
        // Save using the actions (simulating what the Livewire component does)
        $saveAction = new SaveCharacterAdvancementAction();
        $deleteAction = new DeleteCharacterAdvancementAction();
        
        // First delete existing (clean slate)
        $deleteAction->executeAboveLevel($character->id, 1);
        
        // Save regular advancements
        foreach ($advancementsData['creation_advancements'] as $level => $levelAdvancements) {
            foreach ($levelAdvancements as $index => $advancement) {
                $advancementNumber = $index + 1;
                $saveAction->execute(
                    characterId: $character->id,
                    level: (int) $level,
                    advancementNumber: $advancementNumber,
                    advancementType: $advancement['type'],
                    advancementData: $advancement,
                    description: "Test advancement"
                );
            }
        }
        
        // Save tier experiences
        foreach ($advancementsData['creation_tier_experiences'] as $level => $experience) {
            $saveAction->execute(
                characterId: $character->id,
                level: (int) $level,
                advancementNumber: 0,
                advancementType: 'tier_experience',
                advancementData: $experience,
                description: $experience['name']
            );
        }
        
        // Save domain cards
        foreach ($advancementsData['creation_domain_cards'] as $level => $domainCard) {
            $saveAction->execute(
                characterId: $character->id,
                level: (int) $level,
                advancementNumber: -1,
                advancementType: 'domain_card',
                advancementData: $domainCard,
                description: "Domain Card: {$domainCard['name']}"
            );
        }
        
        // Save advancement-granted cards
        foreach ($advancementsData['creation_advancement_cards'] as $advKey => $domainCard) {
            preg_match('/adv_(\d+)_(\d+)/', $advKey, $matches);
            $level = (int) $matches[1];
            $advNumber = (int) $matches[2];
            
            $saveAction->execute(
                characterId: $character->id,
                level: $level,
                advancementNumber: -2 - $advNumber,
                advancementType: 'bonus_domain_card',
                advancementData: $domainCard,
                description: "Bonus Domain Card: {$domainCard['name']}"
            );
        }
        
        // Verify the advancements were saved
        $savedAdvancements = CharacterAdvancement::where('character_id', $character->id)->get();
        
        expect($savedAdvancements)->toHaveCount(8) // 4 regular + 1 tier exp + 2 domain cards + 1 bonus card
            ->and($savedAdvancements->where('advancement_type', 'trait_bonus')->count())->toBe(1)
            ->and($savedAdvancements->where('advancement_type', 'hit_point')->count())->toBe(1)
            ->and($savedAdvancements->where('advancement_type', 'evasion')->count())->toBe(1)
            ->and($savedAdvancements->where('advancement_type', 'stress')->count())->toBe(1)
            ->and($savedAdvancements->where('advancement_type', 'tier_experience')->count())->toBe(1)
            ->and($savedAdvancements->where('advancement_type', 'domain_card')->count())->toBe(2)
            ->and($savedAdvancements->where('advancement_type', 'bonus_domain_card')->count())->toBe(1);
    });
    
    test('loads character advancements correctly from database', function () {
        // Create a character with advancements
        $character = Character::factory()->create(['level' => 3]);
        
        // Create some advancements
        CharacterAdvancement::factory()->create([
            'character_id' => $character->id,
            'level' => 2,
            'advancement_number' => 1,
            'advancement_type' => 'trait_bonus',
            'advancement_data' => ['type' => 'trait_bonus', 'traits' => ['agility', 'strength']],
        ]);
        
        CharacterAdvancement::factory()->create([
            'character_id' => $character->id,
            'level' => 2,
            'advancement_number' => 2,
            'advancement_type' => 'hit_point',
            'advancement_data' => ['type' => 'hit_point'],
        ]);
        
        CharacterAdvancement::factory()->create([
            'character_id' => $character->id,
            'level' => 2,
            'advancement_number' => 0,
            'advancement_type' => 'tier_experience',
            'advancement_data' => ['name' => 'Wilderness Survival', 'description' => 'Expert'],
        ]);
        
        // Load using repository
        $repository = app(CharacterAdvancementRepository::class);
        $advancements = $repository->getForCharacter($character->id);
        
        // Advancements should be ordered by level, then advancement_number
        // tier_experience (adv_num=0) comes first, then trait_bonus (adv_num=1), then hit_point (adv_num=2)
        expect($advancements)->toHaveCount(3)
            ->and($advancements->get(0)->advancement_type)->toBe('tier_experience')
            ->and($advancements->get(1)->advancement_type)->toBe('trait_bonus')
            ->and($advancements->get(1)->advancement_data['traits'])->toBe(['agility', 'strength'])
            ->and($advancements->get(2)->advancement_type)->toBe('hit_point');
    });
    
    test('calculates stat bonuses correctly from advancements', function () {
        // Create a level 3 warrior with advancements
        $character = Character::factory()->create([
            'level' => 3,
            'class' => 'warrior',
            'ancestry' => 'human',
            'character_data' => [
                'assigned_traits' => [
                    'agility' => 2,
                    'strength' => 1,
                    'finesse' => 1,
                    'instinct' => 0,
                    'presence' => 0,
                    'knowledge' => -1,
                ],
            ],
        ]);
        
        // Add advancement bonuses
        CharacterAdvancement::factory()->create([
            'character_id' => $character->id,
            'level' => 2,
            'advancement_number' => 1,
            'advancement_type' => 'hit_point',
            'advancement_data' => ['type' => 'hit_point'],
        ]);
        
        CharacterAdvancement::factory()->create([
            'character_id' => $character->id,
            'level' => 2,
            'advancement_number' => 2,
            'advancement_type' => 'evasion',
            'advancement_data' => ['type' => 'evasion'],
        ]);
        
        CharacterAdvancement::factory()->create([
            'character_id' => $character->id,
            'level' => 3,
            'advancement_number' => 1,
            'advancement_type' => 'stress',
            'advancement_data' => ['type' => 'stress'],
        ]);
        
        // Get bonuses
        $evasionBonuses = $character->getTotalEvasionBonuses();
        $hpBonuses = $character->getTotalHitPointBonuses();
        $stressBonuses = $character->getTotalStressBonuses();
        
        expect($evasionBonuses['advancements'] ?? 0)->toBe(1)
            ->and($hpBonuses['advancements'] ?? 0)->toBe(1)
            ->and($stressBonuses['advancements'] ?? 0)->toBe(1);
    });
    
    test('deletes advancements above a specific level', function () {
        $character = Character::factory()->create(['level' => 5]);
        
        // Create advancements at different levels
        CharacterAdvancement::factory()->create([
            'character_id' => $character->id,
            'level' => 2,
            'advancement_number' => 1,
            'advancement_type' => 'hit_point',
        ]);
        
        CharacterAdvancement::factory()->create([
            'character_id' => $character->id,
            'level' => 3,
            'advancement_number' => 1,
            'advancement_type' => 'evasion',
        ]);
        
        CharacterAdvancement::factory()->create([
            'character_id' => $character->id,
            'level' => 4,
            'advancement_number' => 1,
            'advancement_type' => 'stress',
        ]);
        
        // Delete advancements above level 2
        $deleteAction = new DeleteCharacterAdvancementAction();
        $deleteAction->executeAboveLevel($character->id, 2);
        
        $remaining = CharacterAdvancement::where('character_id', $character->id)->get();
        
        expect($remaining)->toHaveCount(1)
            ->and($remaining->first()->level)->toBe(2);
    });
});
