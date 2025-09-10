<?php

declare(strict_types=1);
use Domain\Character\Data\CharacterBuilderData;

test('clank ancestry can select bonus experience', function () {
    $data = new CharacterBuilderData(
        selected_ancestry: 'clank',
        clank_bonus_experience: 'Blacksmith'
    );

    expect($data->getClankBonusExperience())->toEqual('Blacksmith');
});
test('non clank ancestry returns null for bonus experience', function () {
    $data = new CharacterBuilderData(
        selected_ancestry: 'human',
        clank_bonus_experience: 'Blacksmith'
    );

    expect($data->getClankBonusExperience())->toBeNull();
});
test('clank bonus experience increases modifier to three', function () {
    $data = new CharacterBuilderData(
        selected_ancestry: 'clank',
        clank_bonus_experience: 'Blacksmith'
    );

    expect($data->getExperienceModifier('Blacksmith'))->toEqual(3);
    expect($data->getExperienceModifier('Other Experience'))->toEqual(2);
});
test('non clank ancestry always has base modifier', function () {
    $data = new CharacterBuilderData(selected_ancestry: 'human');

    expect($data->getExperienceModifier('Blacksmith'))->toEqual(2);
    expect($data->getExperienceModifier('Other Experience'))->toEqual(2);
});
test('clank ancestry without selected bonus has base modifier', function () {
    $data = new CharacterBuilderData(selected_ancestry: 'clank');

    expect($data->getExperienceModifier('Blacksmith'))->toEqual(2);
    expect($data->getExperienceModifier('Other Experience'))->toEqual(2);
});
test('clank bonus experience returns null when not set', function () {
    $data = new CharacterBuilderData(selected_ancestry: 'clank');

    expect($data->getClankBonusExperience())->toBeNull();
});
test('clank bonus experience can be constructed', function () {
    $data = new CharacterBuilderData(
        selected_ancestry: 'clank',
        clank_bonus_experience: 'Silver Tongue'
    );

    expect($data->selected_ancestry)->toEqual('clank');
    expect($data->clank_bonus_experience)->toEqual('Silver Tongue');
});
