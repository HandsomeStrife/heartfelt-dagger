<?php

declare(strict_types=1);
use App\Livewire\CharacterBuilder;
use function Pest\Laravel\{actingAs, get, post, put, patch, delete};
use Domain\Character\Models\Character;
use function Pest\Laravel\{actingAs, get, post, put, patch, delete};
use Domain\User\Models\User;
use function Pest\Laravel\{actingAs, get, post, put, patch, delete};
use function Pest\Livewire\livewire;
use function Pest\Laravel\{actingAs, get, post, put, patch, delete};
use PHPUnit\Framework\Attributes\Test;
use function Pest\Laravel\{actingAs, get, post, put, patch, delete};
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('character builder allows correct number of domain cards for school of knowledge', function () {
    $user = User::factory()->create();
    $character = Character::factory()->for($user)->create([
        'character_key' => 'TEST123',
        'class' => 'wizard',
        'subclass' => 'school of knowledge',
    ]);

    $component = livewire(CharacterBuilder::class, ['characterKey' => 'TEST123']);

    // Set class and subclass
    $component->set('character.selected_class', 'wizard');
    $component->set('character.selected_subclass', 'school of knowledge');

    // Should allow selecting up to 3 cards for School of Knowledge
    $maxCards = $component->get('character')->getMaxDomainCards();
    expect($maxCards)->toEqual(3);

    // Use real ability keys from abilities.json instead of fake ones
    // Try to select more than 2 cards (which would fail for regular subclasses)
    $component->call('selectDomainCard', 'codex', 'book of ava');
    $component->call('selectDomainCard', 'midnight', 'chokehold');
    $component->call('selectDomainCard', 'codex', 'book of illiat');

    // Should have selected 3 cards (more than the standard 2)
    expect($component->get('character.selected_domain_cards'))->toHaveCount(3);
});
test('character builder allows correct number of domain cards for regular subclass', function () {
    $user = User::factory()->create();
    $character = Character::factory()->for($user)->create([
        'character_key' => 'TEST456',
        'class' => 'warrior',
        'subclass' => 'stalwart',
    ]);

    $component = livewire(CharacterBuilder::class, ['characterKey' => 'TEST456']);

    // Set class and subclass
    $component->set('character.selected_class', 'warrior');
    $component->set('character.selected_subclass', 'stalwart');

    // Should allow selecting only 2 cards for regular subclass
    $maxCards = $component->get('character')->getMaxDomainCards();
    expect($maxCards)->toEqual(2);

    // Simulate selecting cards up to the limit using real ability keys
    $component->call('selectDomainCard', 'blade', 'a soldiers bond');
    $component->call('selectDomainCard', 'bone', 'bare bones');

    // Should have selected the maximum number of cards
    expect($component->get('character.selected_domain_cards'))->toHaveCount(2);

    // Attempting to select another card should not increase the count
    $component->call('selectDomainCard', 'blade', 'battle monster');
    expect($component->get('character.selected_domain_cards'))->toHaveCount(2);
});
test('character builder domain card deselection works correctly', function () {
    $user = User::factory()->create();
    $character = Character::factory()->for($user)->create([
        'character_key' => 'TEST789',
        'class' => 'wizard',
        'subclass' => 'school of knowledge',
    ]);

    $component = livewire(CharacterBuilder::class, ['characterKey' => 'TEST789']);

    // Set class and subclass
    $component->set('character.selected_class', 'wizard');
    $component->set('character.selected_subclass', 'school of knowledge');

    // Select a card using a real ability key
    $component->call('selectDomainCard', 'codex', 'book of ava');

    // Should have 1 card
    expect($component->get('character.selected_domain_cards'))->toHaveCount(1);

    // Deselect the card by clicking it again
    $component->call('selectDomainCard', 'codex', 'book of ava');

    // Should now have 0 cards
    expect($component->get('character.selected_domain_cards'))->toHaveCount(0);
});
test('character builder handles null subclass domain cards', function () {
    $user = User::factory()->create();
    $character = Character::factory()->for($user)->create([
        'character_key' => 'TEST000',
        'class' => 'warrior',
        'subclass' => null,
    ]);

    $component = livewire(CharacterBuilder::class, ['characterKey' => 'TEST000']);

    // Set class and no subclass
    $component->set('character.selected_class', 'warrior');
    $component->set('character.selected_subclass', null);

    // Should allow selecting only 2 cards for null subclass
    $maxCards = $component->get('character')->getMaxDomainCards();
    expect($maxCards)->toEqual(2);

    // Should be able to select up to 2 cards using real ability keys
    $component->call('selectDomainCard', 'blade', 'a soldiers bond');
    $component->call('selectDomainCard', 'bone', 'bare bones');

    expect($component->get('character.selected_domain_cards'))->toHaveCount(2);

    // Attempting to select a third card should not increase the count
    $component->call('selectDomainCard', 'blade', 'battle monster');
    expect($component->get('character.selected_domain_cards'))->toHaveCount(2);
});
