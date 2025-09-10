<?php

declare(strict_types=1);

use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterAdvancement;
use Domain\Character\Repositories\CharacterAdvancementRepository;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('Character Advancement Repository Display Logic', function () {

    test('repository calculates hit point bonuses correctly', function () {
        $character = Character::factory()->create([
            'level' => 2,
            'class' => 'warrior',
            'is_public' => true,
        ]);

        // Add a hit point advancement
        CharacterAdvancement::factory()->create([
            'character_id' => $character->id,
            'tier' => 2,
            'advancement_number' => 1,
            'advancement_type' => 'hit_point',
            'advancement_data' => ['bonus' => 1],
            'description' => 'Permanently gain one Hit Point slot.',
        ]);

        $repository = new CharacterAdvancementRepository;
        $hitPointBonus = $repository->getHitPointBonus($character->id);

        expect($hitPointBonus)->toBe(1);
    });

    test('repository calculates evasion bonuses correctly', function () {
        $character = Character::factory()->create([
            'level' => 2,
            'class' => 'warrior',
            'is_public' => true,
        ]);

        // Add an evasion advancement
        CharacterAdvancement::factory()->create([
            'character_id' => $character->id,
            'tier' => 2,
            'advancement_number' => 1,
            'advancement_type' => 'evasion',
            'advancement_data' => ['bonus' => 1],
            'description' => 'Permanently gain a +1 bonus to your Evasion.',
        ]);

        $repository = new CharacterAdvancementRepository;
        $evasionBonus = $repository->getEvasionBonus($character->id);

        expect($evasionBonus)->toBe(1);
    });

    test('repository calculates stress slot bonuses correctly', function () {
        $character = Character::factory()->create([
            'level' => 2,
            'class' => 'warrior',
            'is_public' => true,
        ]);

        // Add a stress advancement
        CharacterAdvancement::factory()->create([
            'character_id' => $character->id,
            'tier' => 2,
            'advancement_number' => 1,
            'advancement_type' => 'stress',
            'advancement_data' => ['bonus' => 1],
            'description' => 'Permanently gain one Stress slot.',
        ]);

        $repository = new CharacterAdvancementRepository;
        $stressBonus = $repository->getStressBonus($character->id);

        expect($stressBonus)->toBe(1);
    });

    test('repository calculates trait bonuses correctly', function () {
        $character = Character::factory()->create([
            'level' => 2,
            'class' => 'warrior',
            'is_public' => true,
        ]);

        // Add trait advancement bonuses
        CharacterAdvancement::factory()->create([
            'character_id' => $character->id,
            'tier' => 2,
            'advancement_number' => 1,
            'advancement_type' => 'trait_bonus',
            'advancement_data' => [
                'traits' => ['agility', 'strength'],
                'bonus' => 1,
            ],
            'description' => 'Gain a +1 bonus to two unmarked character traits and mark them.',
        ]);

        $repository = new CharacterAdvancementRepository;
        $traitBonuses = $repository->getTraitBonuses($character->id);

        expect($traitBonuses['agility'])->toBe(1);
        expect($traitBonuses['strength'])->toBe(1);
    });

    test('repository calculates proficiency bonuses correctly', function () {
        $character = Character::factory()->create([
            'level' => 2,
            'class' => 'warrior',
            'is_public' => true,
        ]);

        // Add proficiency advancement (tier achievement)
        CharacterAdvancement::factory()->create([
            'character_id' => $character->id,
            'tier' => 2,
            'advancement_number' => 0, // Tier achievement
            'advancement_type' => 'proficiency',
            'advancement_data' => ['bonus' => 1],
            'description' => 'Tier achievement: +1 Proficiency bonus',
        ]);

        $repository = new CharacterAdvancementRepository;
        $proficiencyBonus = $repository->getProficiencyBonus($character->id);

        expect($proficiencyBonus)->toBe(1);
    });

    test('repository correctly determines level up availability', function () {
        // Character that can level up
        $characterCanLevel = Character::factory()->create([
            'level' => 1,
            'class' => 'warrior',
            'is_public' => true,
        ]);

        $repository = new CharacterAdvancementRepository;
        expect($repository->canLevelUp($characterCanLevel))->toBeTrue();

        // Character that cannot level up (advancement slots already filled)
        $characterCannotLevel = Character::factory()->create([
            'level' => 2,
            'class' => 'warrior',
            'is_public' => true,
        ]);

        // Fill advancement slots
        CharacterAdvancement::factory()->create([
            'character_id' => $characterCannotLevel->id,
            'tier' => 2,
            'advancement_number' => 1,
        ]);

        CharacterAdvancement::factory()->create([
            'character_id' => $characterCannotLevel->id,
            'tier' => 2,
            'advancement_number' => 2,
        ]);

        expect($repository->canLevelUp($characterCannotLevel))->toBeFalse();
    });

    test('repository calculates advancement status correctly', function () {
        $character = Character::factory()->create([
            'level' => 2,
            'class' => 'warrior',
            'is_public' => true,
        ]);

        // Add one advancement (should show partial progress)
        CharacterAdvancement::factory()->create([
            'character_id' => $character->id,
            'tier' => 2,
            'advancement_number' => 1,
            'advancement_type' => 'hit_point',
            'advancement_data' => ['bonus' => 1],
            'description' => 'Hit point advancement',
        ]);

        $repository = new CharacterAdvancementRepository;
        $availableSlots = $repository->getAvailableSlots($character->id, 2);
        $filledSlots = CharacterAdvancement::where([
            'character_id' => $character->id,
        ])->where('advancement_number', '>', 0)->count();

        expect($filledSlots)->toBe(1);
        expect(count($availableSlots))->toBe(1); // One slot available for tier 2 (since one is used)
    });

    test('repository tracks experience bonus advancements correctly', function () {
        $character = Character::factory()->create([
            'level' => 2,
            'class' => 'warrior',
            'is_public' => true,
        ]);

        // Add experience bonus advancement
        CharacterAdvancement::factory()->create([
            'character_id' => $character->id,
            'tier' => 2,
            'advancement_number' => 1,
            'advancement_type' => 'experience',
            'advancement_data' => [
                'experience_bonuses' => ['Combat Training', 'Stealth'],
                'bonus' => 1,
            ],
            'description' => 'Permanently gain a +1 bonus to two Experiences.',
        ]);

        $repository = new CharacterAdvancementRepository;
        $advancements = $repository->getCharacterAdvancements($character->id);
        $experienceAdvancement = $advancements->where('advancement_type', 'experience')->first();

        expect($experienceAdvancement)->not->toBeNull();
        expect($experienceAdvancement->advancement_data['experience_bonuses'])->toContain('Combat Training');
        expect($experienceAdvancement->advancement_data['experience_bonuses'])->toContain('Stealth');
        expect($experienceAdvancement->advancement_data['bonus'])->toBe(1);
    });

    test('repository handles multiple advancements of same type', function () {
        $character = Character::factory()->create([
            'level' => 3,
            'class' => 'warrior',
            'is_public' => true,
        ]);

        // Add multiple hit point advancements
        CharacterAdvancement::factory()->create([
            'character_id' => $character->id,
            'tier' => 2,
            'advancement_number' => 1,
            'advancement_type' => 'hit_point',
            'advancement_data' => ['bonus' => 1],
            'description' => 'First hit point advancement',
        ]);

        CharacterAdvancement::factory()->create([
            'character_id' => $character->id,
            'tier' => 3,
            'advancement_number' => 1,
            'advancement_type' => 'hit_point',
            'advancement_data' => ['bonus' => 1],
            'description' => 'Second hit point advancement',
        ]);

        $repository = new CharacterAdvancementRepository;
        $hitPointBonus = $repository->getHitPointBonus($character->id);

        expect($hitPointBonus)->toBe(2); // Both advancements should be counted
    });

    test('repository handles domain card advancements', function () {
        $character = Character::factory()->create([
            'level' => 2,
            'class' => 'warrior',
            'is_public' => true,
        ]);

        // Add domain card advancement
        CharacterAdvancement::factory()->create([
            'character_id' => $character->id,
            'tier' => 2,
            'advancement_number' => 1,
            'advancement_type' => 'domain_card',
            'advancement_data' => [
                'domain_card' => 'get back up',
                'domain' => 'blade',
            ],
            'description' => 'Choose an additional domain card.',
        ]);

        // Add tier achievement domain card
        CharacterAdvancement::factory()->create([
            'character_id' => $character->id,
            'tier' => 2,
            'advancement_number' => 0, // Tier achievement
            'advancement_type' => 'tier_domain_card',
            'advancement_data' => [
                'domain_card' => 'whirlwind',
                'domain' => 'blade',
            ],
            'description' => 'Tier achievement: Acquire a new domain card',
        ]);

        // Verify the advancements exist with correct data
        $advancements = CharacterAdvancement::where('character_id', $character->id)->get();
        expect($advancements)->toHaveCount(2);

        $domainCardAdvancement = $advancements->where('advancement_type', 'domain_card')->first();
        $tierDomainCardAdvancement = $advancements->where('advancement_type', 'tier_domain_card')->first();

        expect($domainCardAdvancement->advancement_data['domain_card'])->toBe('get back up');
        expect($tierDomainCardAdvancement->advancement_data['domain_card'])->toBe('whirlwind');
    });

});
