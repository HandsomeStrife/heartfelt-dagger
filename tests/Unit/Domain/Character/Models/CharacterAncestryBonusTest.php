<?php

declare(strict_types=1);
use Domain\Character\Models\Character;
use PHPUnit\Framework\Attributes\Test;
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('simiah ancestry provides evasion bonus', function () {
    $character = Character::factory()->create([
        'ancestry' => 'simiah',
    ]);

    expect($character->getAncestryEvasionBonus())->toEqual(1);
});
test('giant ancestry provides hit point bonus', function () {
    $character = Character::factory()->create([
        'ancestry' => 'giant',
    ]);

    expect($character->getAncestryHitPointBonus())->toEqual(1);
});
test('human ancestry provides stress bonus', function () {
    $character = Character::factory()->create([
        'ancestry' => 'human',
    ]);

    expect($character->getAncestryStressBonus())->toEqual(1);
});
test('galapa ancestry provides damage threshold bonus', function () {
    $character = Character::factory()->create([
        'ancestry' => 'galapa',
        'level' => 2, // Proficiency +1 at level 2
    ]);

    // Galapa gets damage threshold bonus equal to proficiency
    expect($character->getAncestryDamageThresholdBonus())->toEqual(1);
});
test('galapa proficiency bonus scales with level', function () {
    $character = Character::factory()->create([
        'ancestry' => 'galapa',
        'level' => 5, // Proficiency +2 at level 5
    ]);

    expect($character->getAncestryDamageThresholdBonus())->toEqual(2);
});
test('non bonus ancestries return zero', function () {
    $character = Character::factory()->create([
        'ancestry' => 'elf', // Not one of the bonus-providing ancestries
    ]);

    expect($character->getAncestryEvasionBonus())->toEqual(0);
    expect($character->getAncestryHitPointBonus())->toEqual(0);
    expect($character->getAncestryStressBonus())->toEqual(0);
    expect($character->getAncestryDamageThresholdBonus())->toEqual(0);
});
test('all ancestry bonus methods handle null ancestry', function () {
    $character = Character::factory()->create([
        'ancestry' => null,
    ]);

    expect($character->getAncestryEvasionBonus())->toEqual(0);
    expect($character->getAncestryHitPointBonus())->toEqual(0);
    expect($character->getAncestryStressBonus())->toEqual(0);
    expect($character->getAncestryDamageThresholdBonus())->toEqual(0);
});
test('multiple ancestries with different bonuses', function () {
    $simiah = Character::factory()->create(['ancestry' => 'simiah']);
    $giant = Character::factory()->create(['ancestry' => 'giant']);
    $human = Character::factory()->create(['ancestry' => 'human']);
    $galapa = Character::factory()->create(['ancestry' => 'galapa', 'level' => 3]);

    // Verify each has only their specific bonus
    expect($simiah->getAncestryEvasionBonus())->toEqual(1);
    expect($simiah->getAncestryHitPointBonus())->toEqual(0);
    expect($simiah->getAncestryStressBonus())->toEqual(0);
    expect($simiah->getAncestryDamageThresholdBonus())->toEqual(0);

    expect($giant->getAncestryEvasionBonus())->toEqual(0);
    expect($giant->getAncestryHitPointBonus())->toEqual(1);
    expect($giant->getAncestryStressBonus())->toEqual(0);
    expect($giant->getAncestryDamageThresholdBonus())->toEqual(0);

    expect($human->getAncestryEvasionBonus())->toEqual(0);
    expect($human->getAncestryHitPointBonus())->toEqual(0);
    expect($human->getAncestryStressBonus())->toEqual(1);
    expect($human->getAncestryDamageThresholdBonus())->toEqual(0);

    expect($galapa->getAncestryEvasionBonus())->toEqual(0);
    expect($galapa->getAncestryHitPointBonus())->toEqual(0);
    expect($galapa->getAncestryStressBonus())->toEqual(0);
    expect($galapa->getAncestryDamageThresholdBonus())->toEqual(1);
});
