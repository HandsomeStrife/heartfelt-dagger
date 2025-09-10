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

        visit("/character/{$character->public_key}/level-up?character_key={$character->character_key}")
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

        visit("/character/{$character->public_key}/level-up?character_key={$character->character_key}")
            ->assertSee('Tier Achievements')
            // Try to proceed without creating experience or selecting domain card
            ->click('button:contains("Continue")')
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

        visit("/character/{$character->public_key}/level-up?character_key={$character->character_key}")
            ->assertSee('Tier Achievements')
            // Create required experience
            ->type('[wire:model="new_experience_name"]', 'Combat Training')
            ->type('[wire:model="new_experience_description"]', 'Advanced fighting techniques')
            ->click('button:contains("Create Experience")')
            ->waitForText('Combat Training')
            // Select required domain card
            ->click('.tier-domain-card:first-child') // Select first available domain card
            ->waitForText('Domain card selected')
            // Now should be able to proceed
            ->click('button:contains("Continue")')
            ->assertSee('First Advancement')
            ->assertNoJavaScriptErrors();
    });

})->group('browser');
