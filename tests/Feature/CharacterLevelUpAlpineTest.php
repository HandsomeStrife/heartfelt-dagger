<?php

declare(strict_types=1);

use App\Livewire\CharacterLevelUp;
use Domain\Character\Models\Character;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('alpine js data is properly initialized', function () {
    $character = Character::factory()->create([
        'level' => 1,
        'class' => 'warrior',
        'is_public' => true,
    ]);

    $component = Livewire::test(CharacterLevelUp::class, [
        'characterKey' => $character->character_key,
        'canEdit' => true,
    ]);

    // Check that component renders without errors
    $component->assertOk();

    // Check that basic state is set correctly for four-step workflow
    expect($component->get('current_step'))->toBe('tier_achievements');
    expect($component->get('first_advancement'))->toBeNull();
    expect($component->get('second_advancement'))->toBeNull();
    expect($component->get('advancement_choices'))->toBeArray();
    expect($component->get('current_tier'))->toBe(2); // Level 1->2 should be tier 2
});

test('alpine js entangle works with livewire state', function () {
    $character = Character::factory()->create([
        'level' => 1,
        'class' => 'warrior',
        'is_public' => true,
    ]);

    $component = Livewire::test(CharacterLevelUp::class, [
        'characterKey' => $character->character_key,
        'canEdit' => true,
    ]);

    // Modify Livewire state - AlpineJS should sync via wire.entangle
    $component->set('current_step', 'first_advancement');
    expect($component->get('current_step'))->toBe('first_advancement');

    // Test the new four-step advancement selection
    $component->set('first_advancement', 0);
    expect($component->get('first_advancement'))->toBe(0);

    $component->set('second_advancement', 1);
    expect($component->get('second_advancement'))->toBe(1);
});

test('component renders alpine data attributes correctly', function () {
    $character = Character::factory()->create([
        'level' => 1,
        'class' => 'warrior',
        'is_public' => true,
    ]);

    $component = Livewire::test(CharacterLevelUp::class, [
        'characterKey' => $character->character_key,
        'canEdit' => true,
    ]);

    // Check that Alpine x-data is present in the HTML output
    $component->assertSee('x-data=', false);
    $component->assertSee('$wire.entangle', false);
    $component->assertSee('currentStep', false);
    $component->assertSee('firstAdvancement', false);
    $component->assertSee('secondAdvancement', false);
    $component->assertSee('availableSlots', false);
    $component->assertSee('advancementChoices', false);
});
