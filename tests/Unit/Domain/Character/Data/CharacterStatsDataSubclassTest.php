<?php

declare(strict_types=1);
use Domain\Character\Data\CharacterStatsData;
use Domain\Character\Models\Character;

test('stats include subclass hit point bonus', function () {
    $character = createCharacterWithSubclass('school of war');

    // +1 hit point
    $stats = CharacterStatsData::fromModel($character);

    // Warrior base hit points (6) + subclass bonus (1) = 7
    expect($stats->hit_points)->toEqual(7);
});
test('stats include subclass stress bonus', function () {
    $character = createCharacterWithSubclass('vengeance');

    // +1 stress
    $stats = CharacterStatsData::fromModel($character);

    // Base stress (6) + subclass bonus (1) = 7
    expect($stats->stress)->toEqual(7);
});
test('stats include subclass evasion bonus', function () {
    $character = createCharacterWithSubclass('nightwalker');

    // +1 evasion
    // Set a specific agility value for predictable testing
    $character->traits()->create([
        'character_id' => $character->id,
        'trait_name' => 'agility',
        'trait_value' => 1,
    ]);

    $stats = CharacterStatsData::fromModel($character);

    // Warrior base evasion (11) + agility modifier (1) + subclass bonus (1) = 13
    expect($stats->evasion)->toEqual(13);
});
test('stats include subclass damage threshold bonuses', function () {
    $character = createCharacterWithSubclass('stalwart');

    // +6 total damage threshold bonus
    $stats = CharacterStatsData::fromModel($character);

    // For level 1 character: armor(1) + proficiency(0) + level(1) + subclass(6) = 8
    // For severe: armor(1) + proficiency(0) + level(1) + 5 + subclass(6) = 13
    expect($stats->major_threshold)->toEqual(8);
    expect($stats->severe_threshold)->toEqual(13);
});
test('stats include subclass severe threshold bonus', function () {
    $character = createCharacterWithSubclass('winged sentinel');

    // +4 severe threshold bonus
    $stats = CharacterStatsData::fromModel($character);

    // For level 1 character: armor(1) + proficiency(0) + level(1) + 5 + subclass(4) = 11
    expect($stats->severe_threshold)->toEqual(11);
});
test('stats combine ancestry and subclass bonuses', function () {
    $character = createCharacterWithAncestryAndSubclass('simiah', 'nightwalker');

    // +1 evasion each
    // Set a specific agility value for predictable testing
    $character->traits()->create([
        'character_id' => $character->id,
        'trait_name' => 'agility',
        'trait_value' => 1,
    ]);

    $stats = CharacterStatsData::fromModel($character);

    // Warrior base evasion (11) + agility modifier (1) + ancestry bonus (1) + subclass bonus (1) = 14
    expect($stats->evasion)->toEqual(14);
});
function createCharacterWithSubclass(string $subclass): Character
{
    return Character::factory()->create([
        'class' => 'warrior',
        'subclass' => $subclass,
        'ancestry' => 'elf', // Use elf to avoid random ancestry bonuses
        'level' => 1,
    ]);
}

function createCharacterWithAncestryAndSubclass(string $ancestry, string $subclass): Character
{
    return Character::factory()->create([
        'class' => 'warrior',
        'ancestry' => $ancestry,
        'subclass' => $subclass,
        'level' => 1,
    ]);
}
