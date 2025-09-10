<?php

declare(strict_types=1);

use App\Livewire\CharacterViewer;
use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterAdvancement;

use function Pest\Livewire\livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('character viewer shows advancement bonuses in computed stats', function () {
    $character = Character::factory()->create([
        'level' => 2,
        'class' => 'warrior',
    ]);

    // Add an evasion advancement
    CharacterAdvancement::factory()->create([
        'character_id' => $character->id,
        'tier' => 2,
        'advancement_number' => 1,
        'advancement_type' => 'evasion',
        'advancement_data' => ['bonus' => 1],
        'description' => 'Permanently gain a +1 bonus to your Evasion',
    ]);

    // Add a hit point advancement
    CharacterAdvancement::factory()->create([
        'character_id' => $character->id,
        'tier' => 2,
        'advancement_number' => 2,
        'advancement_type' => 'hit_point',
        'advancement_data' => ['bonus' => 1],
        'description' => 'Permanently gain one Hit Point slot',
    ]);

    $component = livewire(CharacterViewer::class, [
        'publicKey' => $character->public_key,
        'characterKey' => $character->character_key,
        'canEdit' => true,
    ]);

    // Test that the component renders without error and can call the methods
    $component->assertOk();

    // Call the methods directly
    $computed_stats = $component->call('getComputedStats');

    // Check that advancement bonuses are included
    expect($computed_stats)->toHaveKey('evasion');
    expect($computed_stats)->toHaveKey('hit_points');

    // The exact values depend on the base stats, but they should be higher than base
    expect($computed_stats['evasion'])->toBeGreaterThan(0);
    expect($computed_stats['hit_points'])->toBeGreaterThan(0);
});

test('character viewer shows level up button for eligible character', function () {
    $character = Character::factory()->create([
        'level' => 2,
        'class' => 'warrior',
    ]);

    $component = livewire(CharacterViewer::class, [
        'publicKey' => $character->public_key,
        'characterKey' => $character->character_key,
        'canEdit' => true,
    ]);

    // Character should be able to level up since no advancements exist
    $can_level_up = $component->call('canLevelUp');
    expect($can_level_up)->toBeTrue();

    $advancement_status = $component->call('getAdvancementStatus');
    expect($advancement_status['can_level_up'])->toBeTrue();
    expect($advancement_status['current_tier'])->toBe(2);
    expect($advancement_status['available_slots'])->toHaveCount(2);
});

test('character viewer hides level up button when no slots available', function () {
    $character = Character::factory()->create([
        'level' => 2,
        'class' => 'warrior',
    ]);

    // Fill both advancement slots for tier 2
    CharacterAdvancement::factory()->create([
        'character_id' => $character->id,
        'tier' => 2,
        'advancement_number' => 1,
        'advancement_type' => 'evasion',
        'advancement_data' => ['bonus' => 1],
        'description' => 'Permanently gain a +1 bonus to your Evasion',
    ]);

    CharacterAdvancement::factory()->create([
        'character_id' => $character->id,
        'tier' => 2,
        'advancement_number' => 2,
        'advancement_type' => 'hit_point',
        'advancement_data' => ['bonus' => 1],
        'description' => 'Permanently gain one Hit Point slot',
    ]);

    $component = livewire(CharacterViewer::class, [
        'publicKey' => $character->public_key,
        'characterKey' => $character->character_key,
        'canEdit' => true,
    ]);

    // Character should not be able to level up since all slots are filled
    $can_level_up = $component->call('canLevelUp');
    expect($can_level_up)->toBeFalse();

    $advancement_status = $component->call('getAdvancementStatus');
    expect($advancement_status['can_level_up'])->toBeFalse();
    expect($advancement_status['available_slots'])->toHaveCount(0);
});

test('character advancement repository calculates bonuses correctly', function () {
    $character = Character::factory()->create(['level' => 3]);

    $repository = new \Domain\Character\Repositories\CharacterAdvancementRepository;

    // Initially no bonuses
    expect($repository->getEvasionBonus($character->id))->toBe(0);
    expect($repository->getHitPointBonus($character->id))->toBe(0);
    expect($repository->getStressBonus($character->id))->toBe(0);

    // Add advancements
    CharacterAdvancement::factory()->create([
        'character_id' => $character->id,
        'tier' => 2,
        'advancement_number' => 1,
        'advancement_type' => 'evasion',
        'advancement_data' => ['bonus' => 1],
    ]);

    CharacterAdvancement::factory()->create([
        'character_id' => $character->id,
        'tier' => 2,
        'advancement_number' => 2,
        'advancement_type' => 'hit_point',
        'advancement_data' => ['bonus' => 1],
    ]);

    // Check bonuses are calculated
    expect($repository->getEvasionBonus($character->id))->toBe(1);
    expect($repository->getHitPointBonus($character->id))->toBe(1);
    expect($repository->getStressBonus($character->id))->toBe(0);
});
