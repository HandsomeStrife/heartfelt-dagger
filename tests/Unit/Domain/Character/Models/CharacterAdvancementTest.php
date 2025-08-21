<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Character\Models;

use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterAdvancement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CharacterAdvancementTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function character_advancement_belongs_to_character(): void
    {
        $character = Character::factory()->create();
        $advancement = CharacterAdvancement::factory()->create([
            'character_id' => $character->id,
        ]);

        $this->assertInstanceOf(Character::class, $advancement->character);
        $this->assertEquals($character->id, $advancement->character->id);
    }

    #[Test]
    public function character_has_many_advancements(): void
    {
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

        $this->assertCount(2, $character->advancements);
        $this->assertTrue($character->advancements->contains($advancement1));
        $this->assertTrue($character->advancements->contains($advancement2));
    }

    #[Test]
    public function advancement_data_is_cast_to_array(): void
    {
        $advancement_data = [
            'traits' => ['agility', 'strength'],
            'bonus' => 1,
        ];

        $advancement = CharacterAdvancement::factory()->create([
            'advancement_data' => $advancement_data,
        ]);

        $this->assertIsArray($advancement->advancement_data);
        $this->assertEquals($advancement_data, $advancement->advancement_data);
    }

    #[Test]
    public function get_data_for_type_returns_specific_key(): void
    {
        $advancement = CharacterAdvancement::factory()->create([
            'advancement_data' => [
                'traits' => ['agility', 'strength'],
                'bonus' => 1,
                'description' => 'Test description',
            ],
        ]);

        $this->assertEquals(['agility', 'strength'], $advancement->getDataForType('traits'));
        $this->assertEquals(1, $advancement->getDataForType('bonus'));
        $this->assertEquals('Test description', $advancement->getDataForType('description'));
        $this->assertNull($advancement->getDataForType('nonexistent'));
    }

    #[Test]
    public function get_trait_bonuses_returns_correct_values(): void
    {
        $advancement = CharacterAdvancement::factory()->create([
            'advancement_type' => 'trait_bonus',
            'advancement_data' => [
                'traits' => ['agility', 'strength'],
                'bonus' => 1,
            ],
        ]);

        $bonuses = $advancement->getTraitBonuses();
        
        $this->assertEquals(['agility' => 1, 'strength' => 1], $bonuses);
    }

    #[Test]
    public function get_trait_bonuses_returns_empty_for_non_trait_advancement(): void
    {
        $advancement = CharacterAdvancement::factory()->create([
            'advancement_type' => 'hit_point',
            'advancement_data' => [
                'bonus' => 1,
            ],
        ]);

        $bonuses = $advancement->getTraitBonuses();
        
        $this->assertEquals([], $bonuses);
    }

    #[Test]
    public function is_advancement_type_returns_correct_boolean(): void
    {
        $advancement = CharacterAdvancement::factory()->create([
            'advancement_type' => 'trait_bonus',
        ]);

        $this->assertTrue($advancement->isAdvancementType('trait_bonus'));
        $this->assertFalse($advancement->isAdvancementType('hit_point'));
        $this->assertFalse($advancement->isAdvancementType('evasion'));
    }

    #[Test]
    public function advancement_can_have_tier_and_advancement_number(): void
    {
        $advancement = CharacterAdvancement::factory()->create([
            'tier' => 2,
            'advancement_number' => 1,
        ]);

        $this->assertEquals(2, $advancement->tier);
        $this->assertEquals(1, $advancement->advancement_number);
    }

    #[Test]
    public function unique_constraint_prevents_duplicate_tier_advancement_number(): void
    {
        $character = Character::factory()->create();
        
        // First advancement should succeed
        CharacterAdvancement::factory()->create([
            'character_id' => $character->id,
            'tier' => 1,
            'advancement_number' => 1,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        // Second advancement with same tier and advancement_number should fail
        CharacterAdvancement::factory()->create([
            'character_id' => $character->id,
            'tier' => 1,
            'advancement_number' => 1,
        ]);
    }

    #[Test]
    public function different_tier_same_advancement_number_is_allowed(): void
    {
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

        $this->assertNotEquals($advancement1->id, $advancement2->id);
        $this->assertEquals(1, $advancement1->tier);
        $this->assertEquals(2, $advancement2->tier);
    }

    #[Test]
    public function same_tier_different_advancement_number_is_allowed(): void
    {
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

        $this->assertNotEquals($advancement1->id, $advancement2->id);
        $this->assertEquals(1, $advancement1->advancement_number);
        $this->assertEquals(2, $advancement2->advancement_number);
    }
}
