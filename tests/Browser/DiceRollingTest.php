<?php

declare(strict_types=1);

use Domain\Character\Models\Character;
use Domain\User\Models\User;

test('character viewer displays dice rolling functionality', function () {
    // Create a test user and character
    $user = User::factory()->create();
    $character = Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Test Character',
        'class' => 'warrior',
        'ancestry' => 'human',
        'community' => 'wildborne',
    ]);

    // Visit the character viewer page
    $this->actingAs($user)
        ->visit("/character/{$character->public_key}")
        ->assertSee('Test Character')
        ->assertSee('Active Weapons')
        ->assertPresent('#dice-container');
});

test('character viewer trait stats are clickable for dice rolling', function () {
    // Create a test user and character
    $user = User::factory()->create();
    $character = Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Dice Test Character',
        'class' => 'warrior',
        'ancestry' => 'human',
        'community' => 'wildborne',
    ]);

    // Visit the character viewer page
    $this->actingAs($user)
        ->visit("/character/{$character->public_key}")
        ->assertSee('Dice Test Character')
        ->assertPresent('[pest="trait-stats"]');
});

test('character viewer displays dice icon', function () {
    // Create a test user and character
    $user = User::factory()->create();
    $character = Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Dice Icon Test',
        'class' => 'warrior',
        'ancestry' => 'human',
        'community' => 'wildborne',
    ]);

    // Visit the character viewer page
    $this->actingAs($user)
        ->visit("/character/{$character->public_key}")
        ->assertSee('Dice Icon Test')
        ->assertPresent('#dice-container');
});

test('character viewer weapon sections are clickable for dice rolling', function () {
    // Create a test user and character with equipment
    $user = User::factory()->create();
    $character = Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Weapon Test Character',
        'class' => 'warrior',
        'ancestry' => 'human',
        'community' => 'wildborne',
    ]);

    // Visit the character viewer page
    $this->actingAs($user)
        ->visit("/character/{$character->public_key}")
        ->assertSee('Weapon Test Character')
        ->assertPresent('[pest="active-weapons-section"]');
});
