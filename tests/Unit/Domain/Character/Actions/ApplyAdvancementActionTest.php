<?php

declare(strict_types=1);
use Domain\Character\Actions\ApplyAdvancementAction;
use Domain\Character\Data\CharacterAdvancementData;
use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterAdvancement;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->action = new ApplyAdvancementAction;
});
test('execute creates new advancement record', function () {
    $character = Character::factory()->create();
    $advancement_data = CharacterAdvancementData::traitBonus(1, 1, ['agility'], 1);

    $result = $this->action->execute($character, $advancement_data);

    expect($result)->toBeInstanceOf(CharacterAdvancement::class);
    expect($result->character_id)->toEqual($character->id);
    expect($result->tier)->toEqual(1);
    expect($result->advancement_number)->toEqual(1);
    expect($result->advancement_type)->toEqual('trait_bonus');
    expect($result->advancement_data)->toEqual(['traits' => ['agility'], 'bonus' => 1]);
});
test('execute throws exception for duplicate advancement slot', function () {
    $character = Character::factory()->create();

    // Create existing advancement
    CharacterAdvancement::factory()->create([
        'character_id' => $character->id,
        'tier' => 1,
        'advancement_number' => 1,
    ]);

    $advancement_data = CharacterAdvancementData::traitBonus(1, 1, ['agility'], 1);

    expect(fn () => $this->action->execute($character, $advancement_data))
        ->toThrow(\InvalidArgumentException::class, 'Advancement slot already taken');
});
test('execute allows different advancement numbers same tier', function () {
    $character = Character::factory()->create();

    // Create first advancement
    CharacterAdvancement::factory()->create([
        'character_id' => $character->id,
        'tier' => 1,
        'advancement_number' => 1,
    ]);

    $advancement_data = CharacterAdvancementData::hitPoint(1, 2);

    $result = $this->action->execute($character, $advancement_data);

    expect($result)->toBeInstanceOf(CharacterAdvancement::class);
    expect($result->tier)->toEqual(1);
    expect($result->advancement_number)->toEqual(2);
});
test('execute allows same advancement number different tier', function () {
    $character = Character::factory()->create();

    // Create first advancement
    CharacterAdvancement::factory()->create([
        'character_id' => $character->id,
        'tier' => 1,
        'advancement_number' => 1,
    ]);

    $advancement_data = CharacterAdvancementData::stress(2, 1);

    $result = $this->action->execute($character, $advancement_data);

    expect($result)->toBeInstanceOf(CharacterAdvancement::class);
    expect($result->tier)->toEqual(2);
    expect($result->advancement_number)->toEqual(1);
});
test('execute validates tier range', function () {
    $character = Character::factory()->create();
    $advancement_data = CharacterAdvancementData::traitBonus(0, 1, ['agility'], 1);

    // Invalid tier
    expect(fn () => $this->action->execute($character, $advancement_data))
        ->toThrow(\InvalidArgumentException::class, 'Tier must be between 1 and 4');
});
test('execute validates tier upper bound', function () {
    $character = Character::factory()->create();
    $advancement_data = CharacterAdvancementData::traitBonus(5, 1, ['agility'], 1);

    // Invalid tier
    expect(fn () => $this->action->execute($character, $advancement_data))
        ->toThrow(\InvalidArgumentException::class, 'Tier must be between 1 and 4');
});
test('execute validates advancement number range', function () {
    $character = Character::factory()->create();
    $advancement_data = CharacterAdvancementData::traitBonus(1, 0, ['agility'], 1);

    // Invalid advancement number
    expect(fn () => $this->action->execute($character, $advancement_data))
        ->toThrow(\InvalidArgumentException::class, 'Advancement number must be 1 or 2');
});
test('execute validates advancement number upper bound', function () {
    $character = Character::factory()->create();
    $advancement_data = CharacterAdvancementData::traitBonus(1, 3, ['agility'], 1);

    // Invalid advancement number
    expect(fn () => $this->action->execute($character, $advancement_data))
        ->toThrow(\InvalidArgumentException::class, 'Advancement number must be 1 or 2');
});
test('execute validates character level for tier', function () {
    $character = Character::factory()->create(['level' => 1]);
    $advancement_data = CharacterAdvancementData::traitBonus(3, 1, ['agility'], 1);

    // Tier 3 requires level 5+
    expect(fn () => $this->action->execute($character, $advancement_data))
        ->toThrow(\InvalidArgumentException::class, 'Character level insufficient for tier 3');
});
test('execute allows tier progression with sufficient level', function () {
    $character = Character::factory()->create(['level' => 5]);
    $advancement_data = CharacterAdvancementData::traitBonus(3, 1, ['agility'], 1);

    $result = $this->action->execute($character, $advancement_data);

    expect($result)->toBeInstanceOf(CharacterAdvancement::class);
    expect($result->tier)->toEqual(3);
});
test('execute validates multiclass advancement data', function () {
    $character = Character::factory()->create(['level' => 7]);
    // Level for tier 4
    $advancement_data = CharacterAdvancementData::multiclass(4, 1, '');

    // Empty class
    expect(fn () => $this->action->execute($character, $advancement_data))
        ->toThrow(\InvalidArgumentException::class, 'Multiclass advancement requires a class selection');
});
test('execute validates trait bonus advancement data', function () {
    $character = Character::factory()->create();
    $advancement_data = CharacterAdvancementData::traitBonus(1, 1, [], 1);

    // Empty traits array
    expect(fn () => $this->action->execute($character, $advancement_data))
        ->toThrow(\InvalidArgumentException::class, 'Trait bonus advancement requires at least one trait');
});
test('execute creates advancement in transaction', function () {
    $character = Character::factory()->create();
    $advancement_data = CharacterAdvancementData::traitBonus(1, 1, ['agility'], 1);

    // Mock database transaction to test rollback behavior
    DB::shouldReceive('transaction')
        ->once()
        ->with(\Closure::class)
        ->andReturnUsing(function ($callback) {
            return $callback();
        });

    $result = $this->action->execute($character, $advancement_data);

    expect($result)->toBeInstanceOf(CharacterAdvancement::class);
});
test('execute creates different advancement types', function () {
    $character = Character::factory()->create(['level' => 7]);

    $advancement_types = [
        CharacterAdvancementData::traitBonus(1, 1, ['agility'], 1),
        CharacterAdvancementData::hitPoint(1, 2),
        CharacterAdvancementData::stress(2, 1),
        CharacterAdvancementData::experienceBonus(2, 2),
        CharacterAdvancementData::domainCard(3, 1, 2),
        CharacterAdvancementData::evasion(3, 2),
        CharacterAdvancementData::subclass(4, 1, 'upgraded'),
        CharacterAdvancementData::proficiency(4, 2),
    ];

    foreach ($advancement_types as $advancement_data) {
        $result = $this->action->execute($character, $advancement_data);

        expect($result)->toBeInstanceOf(CharacterAdvancement::class);
        expect($result->advancement_type)->toEqual($advancement_data->advancement_type);
        expect($result->advancement_data)->toEqual($advancement_data->advancement_data);
    }

    // Should have 8 total advancements
    expect($character->fresh()->advancements)->toHaveCount(8);
});
test('execute persists advancement to database', function () {
    $character = Character::factory()->create();
    $advancement_data = CharacterAdvancementData::evasion(2, 1);

    $result = $this->action->execute($character, $advancement_data);

    // Verify it's actually in the database
    \Pest\Laravel\assertDatabaseHas('character_advancements', [
        'id' => $result->id,
        'character_id' => $character->id,
        'tier' => 2,
        'advancement_number' => 1,
        'advancement_type' => 'evasion',
    ]);
});
