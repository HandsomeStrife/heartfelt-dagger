<?php

declare(strict_types=1);
use Domain\Character\Data\CharacterAdvancementData;

test('trait bonus creates correct advancement data', function () {
    $advancement = CharacterAdvancementData::traitBonus(
        tier: 1,
        advancement_number: 1,
        traits: ['agility', 'strength'],
        bonus: 1
    );

    expect($advancement->tier)->toEqual(1);
    expect($advancement->advancement_number)->toEqual(1);
    expect($advancement->advancement_type)->toEqual('trait_bonus');
    expect($advancement->advancement_data['traits'])->toEqual(['agility', 'strength']);
    expect($advancement->advancement_data['bonus'])->toEqual(1);
    expect($advancement->description)->toContain('Gain a +1 bonus to agility and strength');
});
test('hit point creates correct advancement data', function () {
    $advancement = CharacterAdvancementData::hitPoint(2, 1);

    expect($advancement->tier)->toEqual(2);
    expect($advancement->advancement_number)->toEqual(1);
    expect($advancement->advancement_type)->toEqual('hit_point');
    expect($advancement->advancement_data['bonus'])->toEqual(1);
    expect($advancement->description)->toEqual('Gain an additional Hit Point slot');
});
test('stress creates correct advancement data', function () {
    $advancement = CharacterAdvancementData::stress(2, 2);

    expect($advancement->tier)->toEqual(2);
    expect($advancement->advancement_number)->toEqual(2);
    expect($advancement->advancement_type)->toEqual('stress');
    expect($advancement->advancement_data['bonus'])->toEqual(1);
    expect($advancement->description)->toEqual('Gain an additional Stress slot');
});
test('experience bonus creates correct advancement data', function () {
    $advancement = CharacterAdvancementData::experienceBonus(3, 1);

    expect($advancement->tier)->toEqual(3);
    expect($advancement->advancement_number)->toEqual(1);
    expect($advancement->advancement_type)->toEqual('experience_bonus');
    expect($advancement->advancement_data['bonus'])->toEqual(1);
    expect($advancement->description)->toEqual('Your experiences now provide a +3 modifier instead of +2');
});
test('domain card creates correct advancement data', function () {
    $advancement = CharacterAdvancementData::domainCard(3, 2, 2);

    expect($advancement->tier)->toEqual(3);
    expect($advancement->advancement_number)->toEqual(2);
    expect($advancement->advancement_type)->toEqual('domain_card');
    expect($advancement->advancement_data['level'])->toEqual(2);
    expect($advancement->description)->toEqual('Take a level 2 domain card from your class domains');
});
test('evasion creates correct advancement data', function () {
    $advancement = CharacterAdvancementData::evasion(4, 1);

    expect($advancement->tier)->toEqual(4);
    expect($advancement->advancement_number)->toEqual(1);
    expect($advancement->advancement_type)->toEqual('evasion');
    expect($advancement->advancement_data['bonus'])->toEqual(1);
    expect($advancement->description)->toEqual('Permanently gain a +1 bonus to your Evasion');
});
test('subclass creates correct advancement data', function () {
    $advancement = CharacterAdvancementData::subclass(4, 2, 'upgraded');

    expect($advancement->tier)->toEqual(4);
    expect($advancement->advancement_number)->toEqual(2);
    expect($advancement->advancement_type)->toEqual('subclass');
    expect($advancement->advancement_data['type'])->toEqual('upgraded');
    expect($advancement->description)->toEqual('Take an upgraded subclass card');
});
test('proficiency creates correct advancement data', function () {
    $advancement = CharacterAdvancementData::proficiency(2, 1);

    expect($advancement->tier)->toEqual(2);
    expect($advancement->advancement_number)->toEqual(1);
    expect($advancement->advancement_type)->toEqual('proficiency');
    expect($advancement->advancement_data['bonus'])->toEqual(1);
    expect($advancement->description)->toEqual('Increase your Proficiency by +1');
});
test('multiclass creates correct advancement data', function () {
    $advancement = CharacterAdvancementData::multiclass(4, 1, 'wizard');

    expect($advancement->tier)->toEqual(4);
    expect($advancement->advancement_number)->toEqual(1);
    expect($advancement->advancement_type)->toEqual('multiclass');
    expect($advancement->advancement_data['class'])->toEqual('wizard');
    expect($advancement->description)->toContain('Multiclass: Choose wizard as an additional class');
});
test('can create advancement with custom data', function () {
    $advancement = new CharacterAdvancementData(
        tier: 3,
        advancement_number: 2,
        advancement_type: 'custom',
        advancement_data: ['custom_field' => 'custom_value'],
        description: 'Custom advancement description'
    );

    expect($advancement->tier)->toEqual(3);
    expect($advancement->advancement_number)->toEqual(2);
    expect($advancement->advancement_type)->toEqual('custom');
    expect($advancement->advancement_data)->toEqual(['custom_field' => 'custom_value']);
    expect($advancement->description)->toEqual('Custom advancement description');
});
test('trait bonus with multiple traits creates proper description', function () {
    $advancement = CharacterAdvancementData::traitBonus(
        tier: 2,
        advancement_number: 1,
        traits: ['agility', 'finesse', 'instinct'],
        bonus: 1
    );

    expect($advancement->description)->toContain('agility and finesse and instinct');
});
test('trait bonus with single trait creates proper description', function () {
    $advancement = CharacterAdvancementData::traitBonus(
        tier: 1,
        advancement_number: 2,
        traits: ['strength'],
        bonus: 1
    );

    expect($advancement->description)->toContain('Gain a +1 bonus to strength');
    expect($advancement->description)->not->toContain(' and ');
});
