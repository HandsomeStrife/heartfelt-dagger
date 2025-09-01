<?php

declare(strict_types=1);

use Domain\Character\Data\CharacterBuilderData;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('serializes and deserializes clank_bonus_experience and manual_step_completions', function () {
    $data = new CharacterBuilderData(
        name: 'Tester',
        selected_ancestry: 'clank',
        experiences: [
            ['name' => 'Tinkering', 'description' => 'gearwork'],
            ['name' => 'Salvage', 'description' => 'parts'],
        ],
        manual_step_completions: [1, 2, 3],
    );

    // Choose an experience to receive the clank bonus
    $data->clank_bonus_experience = 'Tinkering';

    $array = $data->toArray();
    $rehydrated = CharacterBuilderData::fromArray($array);

    expect($rehydrated->clank_bonus_experience)->toBe('Tinkering');
    expect($rehydrated->manual_step_completions)->toBe([1, 2, 3]);
});
