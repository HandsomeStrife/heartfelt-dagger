<?php

declare(strict_types=1);
use Domain\Character\Enums\TraitName;
use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterTrait;
use PHPUnit\Framework\Attributes\Test;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('belongs to a character', function () {
    $character = Character::factory()->create();
    $trait = CharacterTrait::factory()->create([
        'character_id' => $character->id,
    ]);

    expect($trait->character)->toBeInstanceOf(Character::class);
    expect($trait->character->id)->toEqual($character->id);
});
it('provides trait name as enum', function () {
    $trait = CharacterTrait::factory()->create([
        'trait_name' => 'agility',
    ]);

    expect($trait->trait_name)->toEqual('agility');
    expect($trait->getTraitNameEnum())->toBeInstanceOf(TraitName::class);
    expect($trait->getTraitNameEnum())->toEqual(TraitName::AGILITY);
});
it('uses guarded instead of fillable', function () {
    $trait = new CharacterTrait;

    // Model uses $guarded = [] which means all attributes are mass assignable
    expect($trait->getGuarded())->toEqual([]);
});
it('can be created with all trait names', function () {
    $character = Character::factory()->create();

    $traits = [
        'agility' => 2,
        'strength' => 1,
        'finesse' => 0,
        'instinct' => -1,
        'presence' => 1,
        'knowledge' => -1,
    ];

    foreach ($traits as $traitName => $value) {
        $trait = CharacterTrait::factory()->create([
            'character_id' => $character->id,
            'trait_name' => $traitName,
            'trait_value' => $value,
        ]);

        expect($trait->trait_name)->toEqual($traitName);
        expect($trait->getTraitNameEnum()->value)->toEqual($traitName);
        expect($trait->trait_value)->toEqual($value);
    }
});
it('validates trait value range', function () {
    // Test valid range (-1 to +2) with different characters and traits
    $traitNames = ['agility', 'strength', 'finesse', 'instinct'];

    foreach ([-1, 0, 1, 2] as $index => $value) {
        $character = Character::factory()->create();
        $trait = CharacterTrait::factory()->create([
            'character_id' => $character->id,
            'trait_name' => $traitNames[$index],
            'trait_value' => $value,
        ]);

        expect($trait->trait_value)->toEqual($value);
    }
});
it('can be created via factory', function () {
    $trait = CharacterTrait::factory()->create();

    expect($trait->character_id)->not->toBeNull();
    expect($trait->trait_name)->not->toBeNull();
    expect($trait->trait_value)->toBeInt();
    expect($trait->trait_value)->toBeGreaterThanOrEqual(-1);
    expect($trait->trait_value)->toBeLessThanOrEqual(2);
});
it('uses correct table name', function () {
    $trait = new CharacterTrait;

    expect($trait->getTable())->toEqual('character_traits');
});
it('has timestamps', function () {
    $trait = CharacterTrait::factory()->create();

    expect($trait->created_at)->not->toBeNull();
    expect($trait->updated_at)->not->toBeNull();
});
test('trait name enum has correct values', function () {
    expect(TraitName::AGILITY->value)->toEqual('agility');
    expect(TraitName::STRENGTH->value)->toEqual('strength');
    expect(TraitName::FINESSE->value)->toEqual('finesse');
    expect(TraitName::INSTINCT->value)->toEqual('instinct');
    expect(TraitName::PRESENCE->value)->toEqual('presence');
    expect(TraitName::KNOWLEDGE->value)->toEqual('knowledge');
});
test('trait name enum has labels', function () {
    expect(TraitName::AGILITY->label())->toEqual('Agility');
    expect(TraitName::STRENGTH->label())->toEqual('Strength');
    expect(TraitName::FINESSE->label())->toEqual('Finesse');
    expect(TraitName::INSTINCT->label())->toEqual('Instinct');
    expect(TraitName::PRESENCE->label())->toEqual('Presence');
    expect(TraitName::KNOWLEDGE->label())->toEqual('Knowledge');
});
test('trait name enum has values method', function () {
    $expected = ['agility', 'strength', 'finesse', 'instinct', 'presence', 'knowledge'];

    expect(TraitName::values())->toEqual($expected);
});
it('can be mass assigned', function () {
    $character = Character::factory()->create();

    $attributes = [
        'character_id' => $character->id,
        'trait_name' => 'agility',
        'trait_value' => 2,
    ];

    $trait = CharacterTrait::create($attributes);

    expect($trait->character_id)->toEqual($character->id);
    expect($trait->trait_name)->toEqual('agility');
    expect($trait->getTraitNameEnum())->toEqual(TraitName::AGILITY);
    expect($trait->trait_value)->toEqual(2);
});
test('multiple traits per character work', function () {
    $character = Character::factory()->create();

    $trait1 = CharacterTrait::factory()->create([
        'character_id' => $character->id,
        'trait_name' => 'agility',
        'trait_value' => 2,
    ]);

    $trait2 = CharacterTrait::factory()->create([
        'character_id' => $character->id,
        'trait_name' => 'strength',
        'trait_value' => 1,
    ]);

    expect($character->traits()->count())->toEqual(2);
    expect($character->traits->contains($trait1))->toBeTrue();
    expect($character->traits->contains($trait2))->toBeTrue();
});
