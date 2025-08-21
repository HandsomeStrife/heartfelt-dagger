<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Character\Models;

use Domain\Character\Enums\TraitName;
use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CharacterTraitTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_belongs_to_a_character(): void
    {
        $character = Character::factory()->create();
        $trait = CharacterTrait::factory()->create([
            'character_id' => $character->id,
        ]);

        $this->assertInstanceOf(Character::class, $trait->character);
        $this->assertEquals($character->id, $trait->character->id);
    }

    #[Test]
    public function it_provides_trait_name_as_enum(): void
    {
        $trait = CharacterTrait::factory()->create([
            'trait_name' => 'agility',
        ]);

        $this->assertEquals('agility', $trait->trait_name);
        $this->assertInstanceOf(TraitName::class, $trait->getTraitNameEnum());
        $this->assertEquals(TraitName::AGILITY, $trait->getTraitNameEnum());
    }

    #[Test]
    public function it_uses_guarded_instead_of_fillable(): void
    {
        $trait = new CharacterTrait;

        // Model uses $guarded = [] which means all attributes are mass assignable
        $this->assertEquals([], $trait->getGuarded());
    }

    #[Test]
    public function it_can_be_created_with_all_trait_names(): void
    {
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

            $this->assertEquals($traitName, $trait->trait_name);
            $this->assertEquals($traitName, $trait->getTraitNameEnum()->value);
            $this->assertEquals($value, $trait->trait_value);
        }
    }

    #[Test]
    public function it_validates_trait_value_range(): void
    {
        // Test valid range (-1 to +2) with different characters and traits
        $traitNames = ['agility', 'strength', 'finesse', 'instinct'];

        foreach ([-1, 0, 1, 2] as $index => $value) {
            $character = Character::factory()->create();
            $trait = CharacterTrait::factory()->create([
                'character_id' => $character->id,
                'trait_name' => $traitNames[$index],
                'trait_value' => $value,
            ]);

            $this->assertEquals($value, $trait->trait_value);
        }
    }

    #[Test]
    public function it_can_be_created_via_factory(): void
    {
        $trait = CharacterTrait::factory()->create();

        $this->assertNotNull($trait->character_id);
        $this->assertNotNull($trait->trait_name);
        $this->assertIsInt($trait->trait_value);
        $this->assertGreaterThanOrEqual(-1, $trait->trait_value);
        $this->assertLessThanOrEqual(2, $trait->trait_value);
    }

    #[Test]
    public function it_uses_correct_table_name(): void
    {
        $trait = new CharacterTrait;

        $this->assertEquals('character_traits', $trait->getTable());
    }

    #[Test]
    public function it_has_timestamps(): void
    {
        $trait = CharacterTrait::factory()->create();

        $this->assertNotNull($trait->created_at);
        $this->assertNotNull($trait->updated_at);
    }

    #[Test]
    public function trait_name_enum_has_correct_values(): void
    {
        $this->assertEquals('agility', TraitName::AGILITY->value);
        $this->assertEquals('strength', TraitName::STRENGTH->value);
        $this->assertEquals('finesse', TraitName::FINESSE->value);
        $this->assertEquals('instinct', TraitName::INSTINCT->value);
        $this->assertEquals('presence', TraitName::PRESENCE->value);
        $this->assertEquals('knowledge', TraitName::KNOWLEDGE->value);
    }

    #[Test]
    public function trait_name_enum_has_labels(): void
    {
        $this->assertEquals('Agility', TraitName::AGILITY->label());
        $this->assertEquals('Strength', TraitName::STRENGTH->label());
        $this->assertEquals('Finesse', TraitName::FINESSE->label());
        $this->assertEquals('Instinct', TraitName::INSTINCT->label());
        $this->assertEquals('Presence', TraitName::PRESENCE->label());
        $this->assertEquals('Knowledge', TraitName::KNOWLEDGE->label());
    }

    #[Test]
    public function trait_name_enum_has_values_method(): void
    {
        $expected = ['agility', 'strength', 'finesse', 'instinct', 'presence', 'knowledge'];

        $this->assertEquals($expected, TraitName::values());
    }

    #[Test]
    public function it_can_be_mass_assigned(): void
    {
        $character = Character::factory()->create();

        $attributes = [
            'character_id' => $character->id,
            'trait_name' => 'agility',
            'trait_value' => 2,
        ];

        $trait = CharacterTrait::create($attributes);

        $this->assertEquals($character->id, $trait->character_id);
        $this->assertEquals('agility', $trait->trait_name);
        $this->assertEquals(TraitName::AGILITY, $trait->getTraitNameEnum());
        $this->assertEquals(2, $trait->trait_value);
    }

    #[Test]
    public function multiple_traits_per_character_work(): void
    {
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

        $this->assertEquals(2, $character->traits()->count());
        $this->assertTrue($character->traits->contains($trait1));
        $this->assertTrue($character->traits->contains($trait2));
    }
}
