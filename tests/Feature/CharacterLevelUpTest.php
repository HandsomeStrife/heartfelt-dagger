<?php

declare(strict_types=1);

use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterAdvancement;

use function Pest\Laravel\get;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('character level up page can be accessed for eligible character', function () {
    $character = Character::factory()->create([
        'level' => 2,
        'class' => 'warrior',
        'is_public' => true,
    ]);

    $response = get(route('character.level-up', [
        'public_key' => $character->public_key,
        'character_key' => $character->character_key,
    ]));

    $response->assertOk();
    $response->assertViewIs('character.level-up');
    $response->assertViewHas('character');
    $response->assertViewHas('can_edit');
});

test('character level up page redirects when no advancement slots available', function () {
    $character = Character::factory()->create([
        'level' => 2,
        'class' => 'warrior',
        'is_public' => true,
    ]);

    // Fill both advancement slots for tier 2
    CharacterAdvancement::factory()->create([
        'character_id' => $character->id,
        'tier' => 2,
            'level' => 2,
            'advancement_number' => 1,
        'advancement_type' => 'evasion',
        'advancement_data' => ['bonus' => 1],
        'description' => 'Permanently gain a +1 bonus to your Evasion',
    ]);

    // Fill both advancement slots for the NEXT level (3)
    // canLevelUp() checks if the next level has available slots
    CharacterAdvancement::factory()->create([
        'character_id' => $character->id,
        'tier' => 2,
        'level' => 3,
        'advancement_number' => 1,
        'advancement_type' => 'evasion',
        'advancement_data' => ['bonus' => 1],
        'description' => 'Permanently gain a +1 bonus to your Evasion',
    ]);

    CharacterAdvancement::factory()->create([
        'character_id' => $character->id,
        'tier' => 2,
        'level' => 3,
        'advancement_number' => 2,
        'advancement_type' => 'hit_point',
        'advancement_data' => ['bonus' => 1],
        'description' => 'Permanently gain one Hit Point slot',
    ]);

    $response = get(route('character.level-up', [
        'public_key' => $character->public_key,
        'character_key' => $character->character_key,
    ]));

    $response->assertRedirect(route('character.show', [
        'public_key' => $character->public_key,
        'character_key' => $character->character_key,
    ]));
    $response->assertSessionHas('error');
});

test('character level up page returns 404 for non-existent character', function () {
    $response = get(route('character.level-up', [
        'public_key' => 'invalid',
        'character_key' => 'invalid',
    ]));

    $response->assertNotFound();
});

test('character level up route is properly defined', function () {
    expect(route('character.level-up', [
        'public_key' => 'test-public-key',
        'character_key' => 'test-character-key',
    ]))->toBe('http://localhost/character/test-public-key/test-character-key/level-up');
});
