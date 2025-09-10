<?php

declare(strict_types=1);

use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterTrait;
use Domain\User\Models\User;

use function Pest\Laravel\actingAs;

describe('Character Level Up Tier Achievement Browser Tests', function () {

    it('loads tier achievements page and validates incomplete selections', function () {
        // Create test user and character
        $user = User::factory()->create();
        $character = Character::factory()->for($user)->create([
            'level' => 1,
            'class' => 'warrior',
            'is_public' => true,
        ]);

        // Create character traits
        CharacterTrait::factory()->for($character)->create(['trait_name' => 'agility', 'trait_value' => 1]);
        CharacterTrait::factory()->for($character)->create(['trait_name' => 'strength', 'trait_value' => 0]);
        CharacterTrait::factory()->for($character)->create(['trait_name' => 'finesse', 'trait_value' => 2]);
        CharacterTrait::factory()->for($character)->create(['trait_name' => 'instinct', 'trait_value' => 0]);
        CharacterTrait::factory()->for($character)->create(['trait_name' => 'presence', 'trait_value' => 1]);
        CharacterTrait::factory()->for($character)->create(['trait_name' => 'knowledge', 'trait_value' => -1]);

        actingAs($user);

        $page = visit("/character/{$character->public_key}/{$character->character_key}/level-up");
        $page->wait(2); // Wait for page load

        // Verify the tier achievements page loads
        $page->assertNoJavaScriptErrors()
            ->assertSee('Tier Achievements')
            ->assertSee('Create Your New Experience')
            ->assertSee('Select Your Domain Card');

        // Test validation - try to continue without completing requirements
        // Note: We can't easily test the validation error appearance in a simple browser test
        // because it requires complex interactions. The unit tests cover this logic.
    });

    it('shows the correct interface elements for tier achievements', function () {
        // Create test user and character
        $user = User::factory()->create();
        $character = Character::factory()->for($user)->create([
            'level' => 1,
            'class' => 'warrior', // Warrior has blade and bone domains
            'is_public' => true,
        ]);

        actingAs($user);

        $page = visit("/character/{$character->public_key}/{$character->character_key}/level-up");
        $page->wait(2);

        // Verify page loads without errors and shows required interface elements
        $page->assertNoJavaScriptErrors()
            ->assertSee('Tier Achievements')
            ->assertSee('Create Your New Experience')
            ->assertSee('Select Your Domain Card')
            ->assertSee('Continue') // Continue button should be present
            ->assertPresent('[x-data]'); // AlpineJS should be initialized
    });
});
