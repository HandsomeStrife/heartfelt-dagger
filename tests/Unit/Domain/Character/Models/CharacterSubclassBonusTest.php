<?php

declare(strict_types=1);
use Domain\Character\Models\Character;

test('school of war battlemage provides hit point bonus', function () {
    $character = Character::factory()->create([
        'subclass' => 'school of war',
        'level' => 1,
    ]);

    $hitPointBonus = $character->getSubclassHitPointBonus();
    $totalBonuses = $character->getTotalHitPointBonuses();

    expect($hitPointBonus)->toEqual(1);
    expect($totalBonuses)->toHaveKey('subclass');
    expect($totalBonuses['subclass'])->toEqual(1);
});
test('vengeance at ease provides stress bonus', function () {
    $character = Character::factory()->create([
        'subclass' => 'vengeance',
        'level' => 1,
    ]);

    $stressBonus = $character->getSubclassStressBonus();
    $totalBonuses = $character->getTotalStressBonuses();

    expect($stressBonus)->toEqual(1);
    expect($totalBonuses)->toHaveKey('subclass');
    expect($totalBonuses['subclass'])->toEqual(1);
});
test('stalwart provides damage threshold bonuses', function () {
    $character = Character::factory()->create([
        'subclass' => 'stalwart',
        'level' => 1,
    ]);

    $damageThresholdBonus = $character->getSubclassDamageThresholdBonus();

    // Stalwart should get bonuses from foundation (+1), specialization (+2), and mastery (+3) = +6 total
    expect($damageThresholdBonus)->toEqual(6);
});
test('nightwalker provides evasion bonus', function () {
    $character = Character::factory()->create([
        'subclass' => 'nightwalker',
        'level' => 1,
    ]);

    $evasionBonus = $character->getSubclassEvasionBonus();
    $totalBonuses = $character->getTotalEvasionBonuses();

    expect($evasionBonus)->toEqual(1);
    expect($totalBonuses)->toHaveKey('subclass');
    expect($totalBonuses['subclass'])->toEqual(1);
});
test('winged sentinel provides severe threshold bonus', function () {
    $character = Character::factory()->create([
        'subclass' => 'winged sentinel',
        'level' => 1,
    ]);

    $severeThresholdBonus = $character->getSubclassSevereThresholdBonus();

    expect($severeThresholdBonus)->toEqual(4);
});
test('school of knowledge provides domain card bonus', function () {
    $character = Character::factory()->create([
        'subclass' => 'school of knowledge',
        'level' => 1,
    ]);

    $domainCardBonus = $character->getSubclassDomainCardBonus();

    // School of Knowledge should get bonus from specialization feature only = +1 total
    expect($domainCardBonus)->toEqual(1);
});
test('non bonus subclasses return zero', function () {
    $character = Character::factory()->create([
        'subclass' => 'beastbound', // No stat bonuses
        'level' => 1,
    ]);

    expect($character->getSubclassEvasionBonus())->toEqual(0);
    expect($character->getSubclassHitPointBonus())->toEqual(0);
    expect($character->getSubclassStressBonus())->toEqual(0);
    expect($character->getSubclassDamageThresholdBonus())->toEqual(0);
    expect($character->getSubclassSevereThresholdBonus())->toEqual(0);
    expect($character->getSubclassDomainCardBonus())->toEqual(0);
});
test('all subclass bonus methods handle null subclass', function () {
    $character = Character::factory()->create([
        'subclass' => null,
        'level' => 1,
    ]);

    expect($character->getSubclassEvasionBonus())->toEqual(0);
    expect($character->getSubclassHitPointBonus())->toEqual(0);
    expect($character->getSubclassStressBonus())->toEqual(0);
    expect($character->getSubclassDamageThresholdBonus())->toEqual(0);
    expect($character->getSubclassSevereThresholdBonus())->toEqual(0);
    expect($character->getSubclassDomainCardBonus())->toEqual(0);
});
test('subclass and ancestry bonuses stack', function () {
    $character = Character::factory()->create([
        'ancestry' => 'simiah', // +1 evasion
        'subclass' => 'nightwalker', // +1 evasion
        'level' => 1,
    ]);

    $totalEvasionBonuses = $character->getTotalEvasionBonuses();

    expect($totalEvasionBonuses)->toHaveKey('ancestry');
    expect($totalEvasionBonuses)->toHaveKey('subclass');
    expect($totalEvasionBonuses['ancestry'])->toEqual(1);
    expect($totalEvasionBonuses['subclass'])->toEqual(1);
    expect(array_sum($totalEvasionBonuses))->toEqual(2);
});
