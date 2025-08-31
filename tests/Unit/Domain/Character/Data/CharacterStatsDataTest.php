<?php

declare(strict_types=1);
use Domain\Character\Data\CharacterBuilderData;
use Domain\Character\Data\CharacterStatsData;
use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterAdvancement;
use Domain\Character\Models\CharacterTrait;
use PHPUnit\Framework\Attributes\Test;
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('from model applies simiah evasion bonus', function () {
    $character = createCharacterWithClass('warrior', 'simiah');

    // Warrior base evasion is 11, Simiah gets +1, Agility trait adds modifier
    createCharacterTrait($character, 'agility', 1);

    $stats = CharacterStatsData::fromModel($character);

    // Base 11 + Simiah bonus 1 + Agility 1 = 13
    expect($stats->evasion)->toEqual(13);
});
test('from model applies giant hit point bonus', function () {
    $character = createCharacterWithClass('warrior', 'giant');

    $stats = CharacterStatsData::fromModel($character);

    // Warrior base 6 + Giant bonus 1 = 7
    expect($stats->hit_points)->toEqual(7);
});
test('from model applies human stress bonus', function () {
    $character = createCharacterWithClass('warrior', 'human');

    $stats = CharacterStatsData::fromModel($character);

    // Base 6 + Human bonus 1 = 7
    expect($stats->stress)->toEqual(7);
});
test('from model applies galapa damage threshold bonus', function () {
    $character = createCharacterWithClass('warrior', 'galapa', level: 3);

    $stats = CharacterStatsData::fromModel($character);

    // Galapa gets bonus equal to proficiency level
    // Level 3 = Proficiency +1, so thresholds get +1
    $expectedMajor = 1 + 1 + 3 + 1;
    // armor + proficiency + level + galapa bonus
    $expectedSevere = 1 + 1 + 8 + 1;

    // armor + proficiency + level + galapa bonus
    expect($stats->major_threshold)->toEqual($expectedMajor);
    expect($stats->severe_threshold)->toEqual($expectedSevere);
});
test('from character builder applies simiah evasion bonus', function () {
    $builder = createBuilderData('warrior', 'simiah');
    $class_data = ['startingEvasion' => 9, 'startingHitPoints' => 7];

    $stats = CharacterStatsData::fromCharacterBuilder($builder, $class_data);

    // Base 9 + Simiah bonus 1 + Agility 1 = 11
    expect($stats->evasion)->toEqual(11);
});
test('from character builder applies giant hit point bonus', function () {
    $builder = createBuilderData('warrior', 'giant');
    $class_data = ['startingEvasion' => 9, 'startingHitPoints' => 7];

    $stats = CharacterStatsData::fromCharacterBuilder($builder, $class_data);

    // Base 7 + Giant bonus 1 = 8
    expect($stats->hit_points)->toEqual(8);
});
test('from character builder applies human stress bonus', function () {
    $builder = createBuilderData('warrior', 'human');
    $class_data = ['startingEvasion' => 9, 'startingHitPoints' => 7];

    $stats = CharacterStatsData::fromCharacterBuilder($builder, $class_data);

    // Base 6 + Human bonus 1 = 7
    expect($stats->stress)->toEqual(7);
});
test('advancement bonuses are applied correctly', function () {
    $character = createCharacterWithClass('warrior', 'elf');

    // Add evasion advancement
    CharacterAdvancement::factory()->create([
        'character_id' => $character->id,
        'tier' => 2,
        'advancement_number' => 1,
        'advancement_type' => 'evasion',
        'advancement_data' => ['bonus' => 1],
    ]);

    // Add hit point advancement
    CharacterAdvancement::factory()->create([
        'character_id' => $character->id,
        'tier' => 2,
        'advancement_number' => 2,
        'advancement_type' => 'hit_point',
        'advancement_data' => ['bonus' => 1],
    ]);

    $stats = CharacterStatsData::fromModel($character);

    // Base evasion 11 + advancement 1 = 12
    expect($stats->evasion)->toEqual(12);

    // Base hit points 6 + advancement 1 = 7
    expect($stats->hit_points)->toEqual(7);
});
test('trait bonus advancements are applied', function () {
    $character = createCharacterWithClass('warrior', 'elf');
    createCharacterTrait($character, 'agility', 0);

    // Base agility 0
    // Add trait bonus advancement for agility
    CharacterAdvancement::factory()->create([
        'character_id' => $character->id,
        'tier' => 1,
        'advancement_number' => 1,
        'advancement_type' => 'trait_bonus',
        'advancement_data' => [
            'traits' => ['agility'],
            'bonus' => 1,
        ],
    ]);

    $stats = CharacterStatsData::fromModel($character);

    // Base evasion 11 + (agility 0 + advancement 1) = 12
    expect($stats->evasion)->toEqual(12);
});
test('multiple advancement bonuses stack', function () {
    $character = createCharacterWithClass('warrior', 'elf');

    // Add multiple evasion advancements
    CharacterAdvancement::factory()->create([
        'character_id' => $character->id,
        'tier' => 2,
        'advancement_number' => 1,
        'advancement_type' => 'evasion',
        'advancement_data' => ['bonus' => 1],
    ]);

    CharacterAdvancement::factory()->create([
        'character_id' => $character->id,
        'tier' => 3,
        'advancement_number' => 1,
        'advancement_type' => 'evasion',
        'advancement_data' => ['bonus' => 1],
    ]);

    $stats = CharacterStatsData::fromModel($character);

    // Base evasion 11 + advancement 1 + advancement 1 = 13
    expect($stats->evasion)->toEqual(13);
});
test('ancestry and advancement bonuses stack', function () {
    $character = createCharacterWithClass('warrior', 'simiah');

    // Add evasion advancement
    CharacterAdvancement::factory()->create([
        'character_id' => $character->id,
        'tier' => 2,
        'advancement_number' => 1,
        'advancement_type' => 'evasion',
        'advancement_data' => ['bonus' => 1],
    ]);

    $stats = CharacterStatsData::fromModel($character);

    // Base evasion 11 + Simiah bonus 1 + advancement 1 = 13
    expect($stats->evasion)->toEqual(13);
});
test('no ancestry bonus returns base stats', function () {
    $character = createCharacterWithClass('warrior', 'elf');

    $stats = CharacterStatsData::fromModel($character);

    // Should use base class values with no ancestry bonuses
    expect($stats->evasion)->toEqual(11);
    // Base warrior evasion
    expect($stats->hit_points)->toEqual(6);
    // Base warrior hit points
    expect($stats->stress)->toEqual(6);
    // Base stress
});
function createCharacterWithClass(string $class, string $ancestry, int $level = 1): Character
{
    return Character::factory()->create([
        'class' => $class,
        'ancestry' => $ancestry,
        'level' => $level,
        'character_data' => [
            'class_data' => [
                'starting_evasion' => 9,
                'starting_hit_points' => 7,
            ],
        ],
    ]);
}

function createCharacterTrait(Character $character, string $trait, int $value): void
{
    CharacterTrait::factory()->create([
        'character_id' => $character->id,
        'trait_name' => $trait,
        'trait_value' => $value,
    ]);
}

function createBuilderData(string $class, string $ancestry): CharacterBuilderData
{
    return CharacterBuilderData::from([
        'selected_class' => $class,
        'selected_ancestry' => $ancestry,
        'assigned_traits' => [
            'agility' => 1,
            'strength' => 0,
            'finesse' => 0,
            'instinct' => -1,
            'presence' => 1,
            'knowledge' => 2,
        ],
        'selected_equipment' => [],
        'experiences' => [],
        'background_answers' => [],
        'connections' => [],
        'selected_domain_cards' => [],
    ]);
}
