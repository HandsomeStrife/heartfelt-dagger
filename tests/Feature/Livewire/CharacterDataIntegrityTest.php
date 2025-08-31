<?php

declare(strict_types=1);
use App\Livewire\CharacterBuilder;
use Domain\Character\Models\Character;
use Domain\User\Models\User;
use function Pest\Livewire\livewire;
use PHPUnit\Framework\Attributes\Test;
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->character = Character::factory()->for($this->user)->create([
        'character_key' => 'INTEG12345',
    ]);
});
it('preserves all character fields when setting individual properties', function () {
    $component = livewire(CharacterBuilder::class, ['characterKey' => 'INTEG12345']);

    // Set multiple properties including pronouns
    $component->set('character.name', 'Test Hero');
    $component->set('pronouns', 'they/them');
    $component->set('character.selected_ancestry', 'human');
    $component->set('character.selected_community', 'wildborne');
    $component->set('character.profile_image_path', '/test/image.jpg');

    // Verify all are set initially
    expect($component->get('character.name'))->toEqual('Test Hero');
    expect($component->get('pronouns'))->toEqual('they/them');
    expect($component->get('character.selected_ancestry'))->toEqual('human');
    expect($component->get('character.selected_community'))->toEqual('wildborne');
    expect($component->get('character.profile_image_path'))->toEqual('/test/image.jpg');

    // Set one more property and verify others are preserved
    $component->set('character.selected_class', 'warrior');

    // Check if all properties are still preserved
    expect($component->get('character.name'))->toEqual('Test Hero');
    expect($component->get('pronouns'))->toEqual('they/them', 'Pronouns property lost when setting class');
    expect($component->get('character.selected_ancestry'))->toEqual('human');
    expect($component->get('character.selected_community'))->toEqual('wildborne');
    expect($component->get('character.profile_image_path'))->toEqual('/test/image.jpg');
    expect($component->get('character.selected_class'))->toEqual('warrior');
});
it('preserves pronouns during class reset operations', function () {
    $component = livewire(CharacterBuilder::class, ['characterKey' => 'INTEG12345']);

    // Set up character with pronouns and other data
    $component->set('character.name', 'Test Hero');
    $component->set('pronouns', 'they/them');
    $component->call('updatePronouns', 'they/them');
    // Trigger save to database
    $component->set('character.selected_ancestry', 'human');
    $component->set('character.selected_community', 'wildborne');
    $component->set('character.assigned_traits', ['agility' => 2, 'strength' => 1]);

    // Verify initial state
    expect($component->get('pronouns'))->toEqual('they/them');
    expect($component->get('character.assigned_traits'))->toEqual(['agility' => 2, 'strength' => 1]);

    // Trigger class change which should reset traits but preserve pronouns
    $component->call('selectClass', 'warrior');

    // Verify pronouns are preserved during class reset
    expect($component->get('pronouns'))->toEqual('they/them', 'Pronouns lost during class reset');
    expect($component->get('character.name'))->toEqual('Test Hero', 'Name lost during class reset');
    expect($component->get('character.selected_ancestry'))->toEqual('human', 'Ancestry lost during class reset');
    expect($component->get('character.selected_community'))->toEqual('wildborne', 'Community lost during class reset');
    expect($component->get('character.assigned_traits'))->toEqual([], 'Traits not reset during class change');
    expect($component->get('character.selected_class'))->toEqual('warrior', 'Class not set correctly');

    // Verify pronouns are saved to database
    $character = Character::where('character_key', 'INTEG12345')->first();
    expect($character->pronouns)->toEqual('they/them', 'Pronouns not preserved in database after class reset');
});
