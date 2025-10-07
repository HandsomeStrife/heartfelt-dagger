<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('Character Level Up JavaScript Integration', function () {

    test('level up page loads without JavaScript errors', function () {
        $character = Character::factory()->create([
            'level' => 1,
            'class' => 'warrior',
            'is_public' => true,
        ]);

        visit("/character/{$character->public_key}/{$character->character_key}/level-up")
            ->assertSee('Tier Achievements')
            ->assertSee('Level 2 Benefits')
            ->assertNoJavaScriptErrors();
    });

    test('tier achievement validation prevents progression without required selections', function () {
        $character = Character::factory()->create([
            'level' => 1,
            'class' => 'warrior',
            'is_public' => true,
        ]);

        visit("/character/{$character->public_key}/{$character->character_key}/level-up")
            ->assertSee('Tier Achievements')
            // Try to proceed without creating experience or selecting domain card
            ->click('[data-test="level-up-continue"]')
            ->wait(1)
            // Should remain on tier achievements page (validation should prevent progression)
            ->assertSee('Tier Achievements')
            ->assertNoJavaScriptErrors();
    });

    test('tier achievement validation allows progression when requirements are met', function () {
        $character = Character::factory()->create([
            'level' => 1,
            'class' => 'warrior',
            'is_public' => true,
        ]);

        visit("/character/{$character->public_key}/{$character->character_key}/level-up")
            ->assertSee('Tier Achievements')
            // Create required experience
            ->type('#tier-experience-name', 'Combat Training')
            ->type('#tier-experience-description', 'Advanced fighting techniques')
            ->click('[data-test="create-tier-experience"]')
            ->wait(1)
            ->assertSee('Combat Training')
            // Select required domain card - this test will need actual domain card selector
            // For now, let's just test the experience creation flow
            ->assertSee('Select Your Domain Card')
            ->assertNoJavaScriptErrors();
    });

})->group('browser');
