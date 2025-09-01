<?php

declare(strict_types=1);

use Domain\Character\Actions\LoadCharacterAction;
use Domain\Character\Actions\SaveCharacterAction;
use Domain\Character\Data\CharacterBuilderData;
use Domain\Character\Models\Character;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('persists clank bonus experience through save and load after livewire-style rehydration', function () {
    // Seed a character first
    $builder = new CharacterBuilderData(
        name: 'Clank Hero',
        selected_class: 'warrior',
        selected_ancestry: 'clank',
        experiences: [
            ['name' => 'Tinkering', 'description' => 'gearwork', 'modifier' => 2],
            ['name' => 'Salvage', 'description' => 'parts', 'modifier' => 2],
        ],
    );
    $builder->clank_bonus_experience = 'Tinkering';

    $save = new SaveCharacterAction;
    $character = $save->execute($builder, null);

    // Simulate Livewire rehydration of the $character DTO during a later save
    $rehydrated = CharacterBuilderData::fromArray($builder->toArray());

    // Update the same character using the rehydrated DTO
    $save->updateCharacter($character->fresh(), $rehydrated);

    // Load via action to get a fresh DTO and assert the clank bonus remains
    $load = new LoadCharacterAction;
    $loaded = $load->execute($character->character_key);

    expect($loaded)->not()->toBeNull();
    expect($loaded->clank_bonus_experience)->toBe('Tinkering');
});


