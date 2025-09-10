<?php

declare(strict_types=1);

use App\Livewire\CharacterLevelUp;
use Domain\Character\Models\Character;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('tier achievement domain card can be selected', function () {
    $character = Character::factory()->create([
        'level' => 1,
        'class' => 'warrior', // blade + bone domains
        'is_public' => true,
    ]);

    $component = Livewire::test(CharacterLevelUp::class, [
        'characterKey' => $character->character_key,
        'canEdit' => true,
    ]);

    // Select a tier achievement domain card
    $component->call('selectTierDomainCard', 'blade-strike');

    // Should update advancement choices
    $choices = $component->get('advancement_choices');
    expect($choices['tier_domain_card'])->toBe('blade-strike');
});

test('tier achievement domain card can be removed', function () {
    $character = Character::factory()->create([
        'level' => 1,
        'class' => 'warrior',
        'is_public' => true,
    ]);

    $component = Livewire::test(CharacterLevelUp::class, [
        'characterKey' => $character->character_key,
        'canEdit' => true,
    ]);

    // Select and then remove a tier achievement domain card
    $component->call('selectTierDomainCard', 'blade-strike');
    $component->call('removeTierDomainCard');

    // Should remove tier domain card choice
    $choices = $component->get('advancement_choices');
    expect($choices['tier_domain_card'] ?? null)->toBeNull();
});

test('tier achievement domain card selection respects can_edit permission', function () {
    $character = Character::factory()->create([
        'level' => 1,
        'class' => 'warrior',
        'is_public' => true,
    ]);

    $component = Livewire::test(CharacterLevelUp::class, [
        'characterKey' => $character->character_key,
        'canEdit' => false, // Cannot edit
    ]);

    // Try to select a tier achievement domain card
    $component->call('selectTierDomainCard', 'blade-strike');

    // Should not update advancement choices
    $choices = $component->get('advancement_choices');
    expect($choices['tier_domain_card'] ?? null)->toBeNull();
});

test('tier achievement domain card interface shows available cards for character class', function () {
    $character = Character::factory()->create([
        'level' => 1,
        'class' => 'warrior', // blade + bone domains
        'is_public' => true,
    ]);

    $component = Livewire::test(CharacterLevelUp::class, [
        'characterKey' => $character->character_key,
        'canEdit' => true,
    ]);

    // Check that the component loads with appropriate game data
    $component->assertOk();

    // Get available domain cards for level 2 (current + 1)
    $availableCards = $component->instance()->getAvailableDomainCards(2);

    expect($availableCards)->toBeArray();
    expect($availableCards)->not->toBeEmpty();

    // Should only have cards from warrior domains (blade + bone) at level 2 or below
    foreach ($availableCards as $card) {
        expect($card['domain'])->toBeIn(['blade', 'bone']);
        expect($card['level'])->toBeLessThanOrEqual(2);
    }
});
