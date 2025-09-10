<?php

declare(strict_types=1);
use App\Livewire\CharacterBuilder;
use Domain\Character\Models\Character;
use Domain\User\Models\User;
use PHPUnit\Framework\Attributes\Test;

use function Pest\Livewire\livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->character = Character::factory()->for($this->user)->create([
        'character_key' => 'PRON123456',
    ]);
});
it('can set and retrieve pronouns via direct property', function () {
    $component = livewire(CharacterBuilder::class, ['characterKey' => 'PRON123456']);

    // Test different pronoun values using the direct pronouns property
    $pronouns = ['he/him', 'she/her', 'they/them', 'xe/xem', 'custom pronouns'];

    foreach ($pronouns as $pronoun) {
        $component->set('pronouns', $pronoun);
        $retrieved = $component->get('pronouns');

        expect($retrieved)->toEqual($pronoun, "Failed to preserve pronoun in property: {$pronoun}");

        // Trigger a save operation to persist to database
        $component->call('updatePronouns', $pronoun);

        // Verify it's saved to the database
        $character = Character::where('character_key', 'PRON123456')->first();
        expect($character->pronouns)->toEqual($pronoun, "Failed to save pronoun to database: {$pronoun}");
    }
});
it('preserves pronouns during operations', function () {
    $component = livewire(CharacterBuilder::class, ['characterKey' => 'PRON123456']);

    // Set pronouns using the direct property
    $component->set('pronouns', 'they/them');

    // Verify it's set initially
    expect($component->get('pronouns'))->toEqual('they/them');

    // Test that pronouns are preserved when setting other character properties
    $component->set('character.name', 'Test Character');
    expect($component->get('pronouns'))->toEqual('they/them', 'Pronouns property lost after setting name');

    $component->set('character.selected_ancestry', 'human');
    expect($component->get('pronouns'))->toEqual('they/them', 'Pronouns property lost after setting ancestry');

    // Test that pronouns are preserved when calling component methods
    $component->call('selectAncestry', 'elf');
    expect($component->get('pronouns'))->toEqual('they/them', 'Pronouns property lost after selectAncestry call');

    // Verify it's still saved in the database
    $character = Character::where('character_key', 'PRON123456')->first();
    expect($character->pronouns)->toEqual('they/them', 'Pronouns not preserved in database after operations');
});
