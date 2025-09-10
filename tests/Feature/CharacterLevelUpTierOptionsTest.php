<?php

declare(strict_types=1);

use App\Livewire\CharacterLevelUp;
use Domain\Character\Models\Character;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('level up component loads tier 2 options for level 1 to 2 advancement', function () {
    $character = Character::factory()->create([
        'level' => 1,
        'class' => 'warrior',
        'is_public' => true,
    ]);

    $component = Livewire::test(CharacterLevelUp::class, [
        'characterKey' => $character->character_key,
        'canEdit' => true,
    ]);

    // Check that current_tier is set to 2 (target tier for level 1->2)
    expect($component->get('current_tier'))->toBe(2);

    // Check that tier_options are loaded
    $tier_options = $component->get('tier_options');
    expect($tier_options)->not->toBeEmpty();
    expect($tier_options)->toHaveKey('options');
    expect($tier_options['options'])->toBeArray();
    expect(count($tier_options['options']))->toBeGreaterThan(0);
});

test('level up component loads tier 3 options for level 4 to 5 advancement', function () {
    $character = Character::factory()->create([
        'level' => 4,
        'class' => 'warrior',
        'is_public' => true,
    ]);

    $component = Livewire::test(CharacterLevelUp::class, [
        'characterKey' => $character->character_key,
        'canEdit' => true,
    ]);

    // Check that current_tier is set to 3 (target tier for level 4->5)
    expect($component->get('current_tier'))->toBe(3);
});

test('tier options include expected advancement types', function () {
    $character = Character::factory()->create([
        'level' => 1,
        'class' => 'warrior',
        'is_public' => true,
    ]);

    $component = Livewire::test(CharacterLevelUp::class, [
        'characterKey' => $character->character_key,
        'canEdit' => true,
    ]);

    $tier_options = $component->get('tier_options');
    expect($tier_options)->toHaveKey('options');

    $options = $tier_options['options'];
    $descriptions = array_column($options, 'description');

    // Should include trait bonus option
    expect($descriptions)->toContain('Gain a +1 bonus to two unmarked character traits and mark them.');

    // Should include hit point option
    expect($descriptions)->toContain('Permanently gain one Hit Point slot.');
});
