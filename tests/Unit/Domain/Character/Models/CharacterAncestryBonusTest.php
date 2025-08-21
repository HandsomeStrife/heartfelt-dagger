<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Character\Models;

use Domain\Character\Models\Character;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CharacterAncestryBonusTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function simiah_ancestry_provides_evasion_bonus(): void
    {
        $character = Character::factory()->create([
            'ancestry' => 'simiah',
        ]);

        $this->assertEquals(1, $character->getAncestryEvasionBonus());
    }

    #[Test]
    public function giant_ancestry_provides_hit_point_bonus(): void
    {
        $character = Character::factory()->create([
            'ancestry' => 'giant',
        ]);

        $this->assertEquals(1, $character->getAncestryHitPointBonus());
    }

    #[Test]
    public function human_ancestry_provides_stress_bonus(): void
    {
        $character = Character::factory()->create([
            'ancestry' => 'human',
        ]);

        $this->assertEquals(1, $character->getAncestryStressBonus());
    }

    #[Test]
    public function galapa_ancestry_provides_damage_threshold_bonus(): void
    {
        $character = Character::factory()->create([
            'ancestry' => 'galapa',
            'level' => 2, // Proficiency +1 at level 2
        ]);

        // Galapa gets damage threshold bonus equal to proficiency
        $this->assertEquals(1, $character->getAncestryDamageThresholdBonus());
    }

    #[Test]
    public function galapa_proficiency_bonus_scales_with_level(): void
    {
        $character = Character::factory()->create([
            'ancestry' => 'galapa',
            'level' => 5, // Proficiency +2 at level 5
        ]);

        $this->assertEquals(2, $character->getAncestryDamageThresholdBonus());
    }

    #[Test]
    public function non_bonus_ancestries_return_zero(): void
    {
        $character = Character::factory()->create([
            'ancestry' => 'elf', // Not one of the bonus-providing ancestries
        ]);

        $this->assertEquals(0, $character->getAncestryEvasionBonus());
        $this->assertEquals(0, $character->getAncestryHitPointBonus());
        $this->assertEquals(0, $character->getAncestryStressBonus());
        $this->assertEquals(0, $character->getAncestryDamageThresholdBonus());
    }

    #[Test]
    public function all_ancestry_bonus_methods_handle_null_ancestry(): void
    {
        $character = Character::factory()->create([
            'ancestry' => null,
        ]);

        $this->assertEquals(0, $character->getAncestryEvasionBonus());
        $this->assertEquals(0, $character->getAncestryHitPointBonus());
        $this->assertEquals(0, $character->getAncestryStressBonus());
        $this->assertEquals(0, $character->getAncestryDamageThresholdBonus());
    }

    #[Test]
    public function multiple_ancestries_with_different_bonuses(): void
    {
        $simiah = Character::factory()->create(['ancestry' => 'simiah']);
        $giant = Character::factory()->create(['ancestry' => 'giant']);
        $human = Character::factory()->create(['ancestry' => 'human']);
        $galapa = Character::factory()->create(['ancestry' => 'galapa', 'level' => 3]);

        // Verify each has only their specific bonus
        $this->assertEquals(1, $simiah->getAncestryEvasionBonus());
        $this->assertEquals(0, $simiah->getAncestryHitPointBonus());
        $this->assertEquals(0, $simiah->getAncestryStressBonus());
        $this->assertEquals(0, $simiah->getAncestryDamageThresholdBonus());

        $this->assertEquals(0, $giant->getAncestryEvasionBonus());
        $this->assertEquals(1, $giant->getAncestryHitPointBonus());
        $this->assertEquals(0, $giant->getAncestryStressBonus());
        $this->assertEquals(0, $giant->getAncestryDamageThresholdBonus());

        $this->assertEquals(0, $human->getAncestryEvasionBonus());
        $this->assertEquals(0, $human->getAncestryHitPointBonus());
        $this->assertEquals(1, $human->getAncestryStressBonus());
        $this->assertEquals(0, $human->getAncestryDamageThresholdBonus());

        $this->assertEquals(0, $galapa->getAncestryEvasionBonus());
        $this->assertEquals(0, $galapa->getAncestryHitPointBonus());
        $this->assertEquals(0, $galapa->getAncestryStressBonus());
        $this->assertEquals(1, $galapa->getAncestryDamageThresholdBonus());
    }
}
