<?php

declare(strict_types=1);

use App\Livewire\LevelUpModal;
use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterAdvancement;

use function Pest\Livewire\livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('level up modal can be opened with character key', function () {
    $character = Character::factory()->create([
        'level' => 2,
        'class' => 'warrior',
    ]);

    $component = livewire(LevelUpModal::class);

    expect($component->get('show_modal'))->toBeFalse();

    $component->dispatch('open-level-up-modal', $character->character_key);

    expect($component->get('show_modal'))->toBeTrue();
    expect($component->get('character_key'))->toBe($character->character_key);
    expect($component->get('character')->id)->toBe($character->id);
});

test('level up modal shows tier achievements for level 2 character', function () {
    $character = Character::factory()->create([
        'level' => 2,
        'class' => 'warrior',
    ]);

    $component = livewire(LevelUpModal::class);
    $component->dispatch('open-level-up-modal', $character->character_key);

    expect($component->get('current_step'))->toBe('tier_achievements');
    expect($component->get('current_tier'))->toBe(2);
});

test('level up modal loads advancement options for character tier', function () {
    $character = Character::factory()->create([
        'level' => 2,
        'class' => 'warrior',
    ]);

    $component = livewire(LevelUpModal::class);
    $component->dispatch('open-level-up-modal', $character->character_key);

    $tier_options = $component->get('tier_options');

    expect($tier_options)->toHaveKey('selectCount');
    expect($tier_options)->toHaveKey('options');
    expect($tier_options['selectCount'])->toBe(2);
    expect($tier_options['options'])->toBeArray();
});

test('level up modal allows advancement selection', function () {
    $character = Character::factory()->create([
        'level' => 2,
        'class' => 'warrior',
    ]);

    $component = livewire(LevelUpModal::class);
    $component->dispatch('open-level-up-modal', $character->character_key);
    $component->call('nextStep'); // Move to advancement selection

    expect($component->get('current_step'))->toBe('advancement_selection');

    // Select first advancement option
    $component->call('selectAdvancement', 0);

    expect($component->get('selected_advancements'))->toContain(0);

    // Select second advancement option
    $component->call('selectAdvancement', 1);

    expect($component->get('selected_advancements'))->toContain(1);
    expect(count($component->get('selected_advancements')))->toBe(2);
});

test('level up modal prevents proceeding without exactly 2 selections', function () {
    $character = Character::factory()->create([
        'level' => 2,
        'class' => 'warrior',
    ]);

    $component = livewire(LevelUpModal::class);
    $component->dispatch('open-level-up-modal', $character->character_key);
    $component->call('nextStep'); // Move to advancement selection

    // Try to proceed without selections
    $component->call('nextStep');
    expect($component->get('current_step'))->toBe('advancement_selection');

    // Select only one advancement
    $component->call('selectAdvancement', 0);
    $component->call('nextStep');
    expect($component->get('current_step'))->toBe('advancement_selection');

    // Select second advancement
    $component->call('selectAdvancement', 1);
    $component->call('nextStep');
    expect($component->get('current_step'))->toBe('confirmation');
});

test('level up modal can confirm and apply advancements', function () {
    $character = Character::factory()->create([
        'level' => 2,
        'class' => 'warrior',
    ]);

    $component = livewire(LevelUpModal::class);
    $component->dispatch('open-level-up-modal', $character->character_key);

    // Navigate through the flow
    $component->call('nextStep'); // To advancement selection
    $component->call('selectAdvancement', 0); // Hit Point advancement
    $component->call('selectAdvancement', 1); // Stress advancement
    $component->call('nextStep'); // To confirmation

    expect($component->get('current_step'))->toBe('confirmation');

    // Confirm the level up
    $component->call('confirmLevelUp');

    // Check that advancements were created
    $advancements = CharacterAdvancement::where('character_id', $character->id)->get();
    expect($advancements)->toHaveCount(2);

    // Modal should be closed
    expect($component->get('show_modal'))->toBeFalse();
});

test('level up modal closes and resets on cancel', function () {
    $character = Character::factory()->create([
        'level' => 2,
        'class' => 'warrior',
    ]);

    $component = livewire(LevelUpModal::class);
    $component->dispatch('open-level-up-modal', $character->character_key);

    expect($component->get('show_modal'))->toBeTrue();

    $component->call('closeModal');

    expect($component->get('show_modal'))->toBeFalse();
    expect($component->get('character_key'))->toBeNull();
    expect($component->get('character'))->toBeNull();
});

test('character advancement repository can determine level up eligibility', function () {
    $character = Character::factory()->create(['level' => 2]);

    $repository = new \Domain\Character\Repositories\CharacterAdvancementRepository;

    // Character with no advancements should be able to level up
    expect($repository->canLevelUp($character))->toBeTrue();

    // Add two advancements for tier 2
    CharacterAdvancement::factory()->create([
        'character_id' => $character->id,
        'tier' => 2,
        'advancement_number' => 1,
    ]);

    CharacterAdvancement::factory()->create([
        'character_id' => $character->id,
        'tier' => 2,
        'advancement_number' => 2,
    ]);

    // Character with all slots filled should not be able to level up
    expect($repository->canLevelUp($character))->toBeFalse();
});
