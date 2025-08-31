<?php

declare(strict_types=1);
use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterAdvancement;
use PHPUnit\Framework\Attributes\Test;
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('character advancement belongs to character', function () {
    $character = Character::factory()->create();
    $advancement = CharacterAdvancement::factory()->create([
        'character_id' => $character->id,
    ]);

    expect($advancement->character)->toBeInstanceOf(Character::class);
    expect($advancement->character->id)->toEqual($character->id);
});
test('character has many advancements', function () {
    $character = Character::factory()->create();

    $advancement1 = CharacterAdvancement::factory()->create([
        'character_id' => $character->id,
        'tier' => 1,
        'advancement_number' => 1,
    ]);

    $advancement2 = CharacterAdvancement::factory()->create([
        'character_id' => $character->id,
        'tier' => 1,
        'advancement_number' => 2,
    ]);

    expect($character->advancements)->toHaveCount(2);
    expect($character->advancements->contains($advancement1))->toBeTrue();
    expect($character->advancements->contains($advancement2))->toBeTrue();
});
test('advancement data is cast to array', function () {
    $advancement_data = [
        'traits' => ['agility', 'strength'],
        'bonus' => 1,
    ];

    $advancement = CharacterAdvancement::factory()->create([
        'advancement_data' => $advancement_data,
    ]);

    expect($advancement->advancement_data)->toBeArray();
    expect($advancement->advancement_data)->toEqual($advancement_data);
});
test('get data for type returns specific key', function () {
    $advancement = CharacterAdvancement::factory()->create([
        'advancement_data' => [
            'traits' => ['agility', 'strength'],
            'bonus' => 1,
            'description' => 'Test description',
        ],
    ]);

    expect($advancement->getDataForType('traits'))->toEqual(['agility', 'strength']);
    expect($advancement->getDataForType('bonus'))->toEqual(1);
    expect($advancement->getDataForType('description'))->toEqual('Test description');
    expect($advancement->getDataForType('nonexistent'))->toBeNull();
});
test('get trait bonuses returns correct values', function () {
    $advancement = CharacterAdvancement::factory()->create([
        'advancement_type' => 'trait_bonus',
        'advancement_data' => [
            'traits' => ['agility', 'strength'],
            'bonus' => 1,
        ],
    ]);

    $bonuses = $advancement->getTraitBonuses();

    expect($bonuses)->toEqual(['agility' => 1, 'strength' => 1]);
});
test('get trait bonuses returns empty for non trait advancement', function () {
    $advancement = CharacterAdvancement::factory()->create([
        'advancement_type' => 'hit_point',
        'advancement_data' => [
            'bonus' => 1,
        ],
    ]);

    $bonuses = $advancement->getTraitBonuses();

    expect($bonuses)->toEqual([]);
});
test('is advancement type returns correct boolean', function () {
    $advancement = CharacterAdvancement::factory()->create([
        'advancement_type' => 'trait_bonus',
    ]);

    expect($advancement->isAdvancementType('trait_bonus'))->toBeTrue();
    expect($advancement->isAdvancementType('hit_point'))->toBeFalse();
    expect($advancement->isAdvancementType('evasion'))->toBeFalse();
});
test('advancement can have tier and advancement number', function () {
    $advancement = CharacterAdvancement::factory()->create([
        'tier' => 2,
        'advancement_number' => 1,
    ]);

    expect($advancement->tier)->toEqual(2);
    expect($advancement->advancement_number)->toEqual(1);
});
test('unique constraint prevents duplicate tier advancement number', function () {
    $character = Character::factory()->create();

    // First advancement should succeed
    CharacterAdvancement::factory()->create([
        'character_id' => $character->id,
        'tier' => 1,
        'advancement_number' => 1,
    ]);

    expect(fn() => CharacterAdvancement::factory()->create([
        'character_id' => $character->id,
        'tier' => 1,
        'advancement_number' => 1,
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});
test('different tier same advancement number is allowed', function () {
    $character = Character::factory()->create();

    $advancement1 = CharacterAdvancement::factory()->create([
        'character_id' => $character->id,
        'tier' => 1,
        'advancement_number' => 1,
    ]);

    $advancement2 = CharacterAdvancement::factory()->create([
        'character_id' => $character->id,
        'tier' => 2,
        'advancement_number' => 1,
    ]);

    expect($advancement1->id)->not->toEqual($advancement2->id);
    expect($advancement1->tier)->toEqual(1);
    expect($advancement2->tier)->toEqual(2);
});
test('same tier different advancement number is allowed', function () {
    $character = Character::factory()->create();

    $advancement1 = CharacterAdvancement::factory()->create([
        'character_id' => $character->id,
        'tier' => 1,
        'advancement_number' => 1,
    ]);

    $advancement2 = CharacterAdvancement::factory()->create([
        'character_id' => $character->id,
        'tier' => 1,
        'advancement_number' => 2,
    ]);

    expect($advancement1->id)->not->toEqual($advancement2->id);
    expect($advancement1->advancement_number)->toEqual(1);
    expect($advancement2->advancement_number)->toEqual(2);
});
