<?php

declare(strict_types=1);
use Domain\Character\Models\Character;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('clank ancestry can select bonus experience', function () {
    $character = Character::factory()->create([
        'ancestry' => 'clank',
        'character_data' => ['clank_bonus_experience' => 'Blacksmith'],
    ]);

    expect($character->getClankBonusExperience())->toEqual('Blacksmith');
});
test('non clank ancestry returns null for bonus experience', function () {
    $character = Character::factory()->create([
        'ancestry' => 'human',
        'character_data' => ['clank_bonus_experience' => 'Blacksmith'],
    ]);

    expect($character->getClankBonusExperience())->toBeNull();
});
test('clank bonus experience increases modifier to three', function () {
    $character = Character::factory()->create([
        'ancestry' => 'clank',
        'character_data' => ['clank_bonus_experience' => 'Blacksmith'],
    ]);

    expect($character->getExperienceModifier('Blacksmith'))->toEqual(3);
    expect($character->getExperienceModifier('Other Experience'))->toEqual(2);
});
test('non clank ancestry always has base modifier', function () {
    $character = Character::factory()->create(['ancestry' => 'human']);

    expect($character->getExperienceModifier('Blacksmith'))->toEqual(2);
    expect($character->getExperienceModifier('Other Experience'))->toEqual(2);
});
test('clank ancestry without selected bonus has base modifier', function () {
    $character = Character::factory()->create(['ancestry' => 'clank']);

    expect($character->getExperienceModifier('Blacksmith'))->toEqual(2);
    expect($character->getExperienceModifier('Other Experience'))->toEqual(2);
});
test('clank bonus experience returns null when not set', function () {
    $character = Character::factory()->create(['ancestry' => 'clank']);

    expect($character->getClankBonusExperience())->toBeNull();
});
