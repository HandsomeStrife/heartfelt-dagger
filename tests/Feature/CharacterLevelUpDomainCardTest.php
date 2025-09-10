<?php

declare(strict_types=1);

use App\Livewire\CharacterLevelUp;
use Domain\Character\Models\Character;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('level up component loads available domain cards for character class', function () {
    $character = Character::factory()->create([
        'level' => 1,
        'class' => 'warrior', // blade + bone domains
        'is_public' => true,
    ]);

    $component = Livewire::test(CharacterLevelUp::class, [
        'characterKey' => $character->character_key,
        'canEdit' => true,
    ]);

    // Get available domain cards via the component instance
    $availableCards = $component->instance()->getAvailableDomainCards(4);

    expect($availableCards)->toBeArray();
    expect($availableCards)->not->toBeEmpty();

    // Should only have cards from warrior domains (blade + bone)
    foreach ($availableCards as $card) {
        expect($card['domain'])->toBeIn(['blade', 'bone']);
        expect($card['level'])->toBeLessThanOrEqual(4);
    }
});

test('domain card can be selected for advancement', function () {
    $character = Character::factory()->create([
        'level' => 1,
        'class' => 'warrior',
        'is_public' => true,
    ]);

    $component = Livewire::test(CharacterLevelUp::class, [
        'characterKey' => $character->character_key,
        'canEdit' => true,
    ]);

    // Select a domain card for advancement index 0
    $component->call('selectDomainCard', 0, 'blade-strike');

    // Should update advancement choices
    $choices = $component->get('advancement_choices');
    expect($choices[0]['domain_card'])->toBe('blade-strike');
});

test('domain card can be removed from advancement', function () {
    $character = Character::factory()->create([
        'level' => 1,
        'class' => 'warrior',
        'is_public' => true,
    ]);

    $component = Livewire::test(CharacterLevelUp::class, [
        'characterKey' => $character->character_key,
        'canEdit' => true,
    ]);

    // Select and then remove a domain card
    $component->call('selectDomainCard', 0, 'blade-strike');
    $component->call('removeDomainCard', 0);

    // Should remove domain card choice
    $choices = $component->get('advancement_choices');
    expect($choices[0]['domain_card'] ?? null)->toBeNull();
});

test('domain card selection respects can_edit permission', function () {
    $character = Character::factory()->create([
        'level' => 1,
        'class' => 'warrior',
        'is_public' => true,
    ]);

    $component = Livewire::test(CharacterLevelUp::class, [
        'characterKey' => $character->character_key,
        'canEdit' => false, // Cannot edit
    ]);

    // Try to select a domain card
    $component->call('selectDomainCard', 0, 'blade-strike');

    // Should not update advancement choices
    $choices = $component->get('advancement_choices');
    expect($choices[0]['domain_card'] ?? null)->toBeNull();
});
