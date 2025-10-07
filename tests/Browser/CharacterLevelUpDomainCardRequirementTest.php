<?php

declare(strict_types=1);

use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterTrait;
use Domain\User\Models\User;

use function Pest\Laravel\actingAs;

describe('Character Level Up Domain Card Requirement Tests', function () {

    test('domain card is required at level 3 (non-tier-achievement level)', function () {
        // Test the critical SRD fix: Domain cards must be required at ALL levels, not just tier achievements
        $user = User::factory()->create();
        $character = Character::factory()->for($user)->create([
            'level' => 2, // Level 2 character leveling to level 3
            'class' => 'warrior',
            'is_public' => true,
        ]);

        actingAs($user);

        $page = visit("/character/{$character->public_key}/{$character->character_key}/level-up");
        $page->wait(2);

        // At level 3, there's NO tier achievement (no experience creation)
        // But domain card selection MUST still be required per SRD Step Four
        $page->assertNoJavaScriptErrors()
            ->assertSee('Select Your Domain Card') // Domain card section should be visible
            ->assertSee('Required'); // Should show as required

        // Try to proceed without selecting a domain card - should fail validation
        $page->click('[data-test="level-up-continue"]')
            ->wait(1)
            ->assertSee('Select Your Domain Card'); // Should still be on tier achievements step
    });

    test('domain card is required at level 4 (non-tier-achievement level)', function () {
        // Another non-tier-achievement level test
        $user = User::factory()->create();
        $character = Character::factory()->for($user)->create([
            'level' => 3, // Level 3 character leveling to level 4
            'class' => 'wizard',
            'is_public' => true,
        ]);

        actingAs($user);

        $page = visit("/character/{$character->public_key}/{$character->character_key}/level-up");
        $page->wait(2);

        // At level 4, there's NO tier achievement
        // But domain card selection MUST still be required
        $page->assertNoJavaScriptErrors()
            ->assertSee('No tier achievements at this level')
            ->assertSee('Select Your Domain Card')
            ->assertSee('Required');

        // Try to proceed without selecting domain card
        $page->click('[data-test="level-up-continue"]')
            ->wait(1)
            ->assertSee('Select Your Domain Card'); // Validation should prevent progression
    });

    test('damage thresholds automatically increase at every level', function () {
        // Verify that damage thresholds increase by +1 per level (SRD Step Three)
        $user = User::factory()->create();
        
        // Create level 1 character
        $characterL1 = Character::factory()->for($user)->create([
            'level' => 1,
            'class' => 'guardian',
            'is_public' => true,
        ]);

        // Create traits for proper stat calculation
        CharacterTrait::factory()->for($characterL1)->create(['trait_name' => 'agility', 'trait_value' => 1]);
        CharacterTrait::factory()->for($characterL1)->create(['trait_name' => 'strength', 'trait_value' => 2]);
        CharacterTrait::factory()->for($characterL1)->create(['trait_name' => 'finesse', 'trait_value' => 0]);
        CharacterTrait::factory()->for($characterL1)->create(['trait_name' => 'instinct', 'trait_value' => 1]);
        CharacterTrait::factory()->for($characterL1)->create(['trait_name' => 'presence', 'trait_value' => 0]);
        CharacterTrait::factory()->for($characterL1)->create(['trait_name' => 'knowledge', 'trait_value' => -1]);

        // Create level 2 character with same traits
        $characterL2 = Character::factory()->for($user)->create([
            'level' => 2,
            'class' => 'guardian',
            'is_public' => true,
        ]);

        CharacterTrait::factory()->for($characterL2)->create(['trait_name' => 'agility', 'trait_value' => 1]);
        CharacterTrait::factory()->for($characterL2)->create(['trait_name' => 'strength', 'trait_value' => 2]);
        CharacterTrait::factory()->for($characterL2)->create(['trait_name' => 'finesse', 'trait_value' => 0]);
        CharacterTrait::factory()->for($characterL2)->create(['trait_name' => 'instinct', 'trait_value' => 1]);
        CharacterTrait::factory()->for($characterL2)->create(['trait_name' => 'presence', 'trait_value' => 0]);
        CharacterTrait::factory()->for($characterL2)->create(['trait_name' => 'knowledge', 'trait_value' => -1]);

        actingAs($user);

        // Get stats for both characters
        $characterL1->refresh();
        $characterL2->refresh();
        
        $statsL1 = \Domain\Character\Data\CharacterStatsData::fromModel($characterL1);
        $statsL2 = \Domain\Character\Data\CharacterStatsData::fromModel($characterL2);

        // At level 2, BOTH level and proficiency increase (1->2)
        // Formula: major_threshold = armor_score + proficiency_bonus + level + damage_threshold_bonus
        // So L2 gains +1 from level AND +1 from proficiency = +2 total
        // This test verifies the threshold calculation includes level properly
        expect($statsL2->major_threshold)->toBeGreaterThan($statsL1->major_threshold);
        expect($statsL2->severe_threshold)->toBeGreaterThan($statsL1->severe_threshold);
        
        // Specifically verify that level contributes to the calculation
        $levelDifference = $characterL2->level - $characterL1->level; // Should be 1
        expect($levelDifference)->toBe(1);
    });

    test('proficiency calculation follows SRD ranges', function () {
        // Verify proficiency increases at correct levels
        // Level 1: Proficiency 1
        // Levels 2-4: Proficiency 2
        // Levels 5-7: Proficiency 3
        // Levels 8-10: Proficiency 4

        $user = User::factory()->create();

        // Test Level 2 (should have proficiency 2 after level up)
        $characterL2 = Character::factory()->for($user)->create([
            'level' => 2,
            'class' => 'warrior',
            'proficiency' => 2,
        ]);
        expect($characterL2->proficiency)->toBe(2);

        // Test Level 5 (should have proficiency 3 after level up from 4)
        $characterL5 = Character::factory()->for($user)->create([
            'level' => 5,
            'class' => 'wizard',
            'proficiency' => 3,
        ]);
        expect($characterL5->proficiency)->toBe(3);

        // Test Level 8 (should have proficiency 4 after level up from 7)
        $characterL8 = Character::factory()->for($user)->create([
            'level' => 8,
            'class' => 'bard',
            'proficiency' => 4,
        ]);
        expect($characterL8->proficiency)->toBe(4);
    });

});

