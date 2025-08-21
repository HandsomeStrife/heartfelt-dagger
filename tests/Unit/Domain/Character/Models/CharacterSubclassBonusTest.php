<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Character\Models;

use Domain\Character\Models\Character;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CharacterSubclassBonusTest extends TestCase
{
    #[Test]
    public function school_of_war_battlemage_provides_hit_point_bonus(): void
    {
        $character = Character::factory()->create([
            'subclass' => 'school of war',
            'level' => 1,
        ]);

        $hitPointBonus = $character->getSubclassHitPointBonus();
        $totalBonuses = $character->getTotalHitPointBonuses();

        $this->assertEquals(1, $hitPointBonus);
        $this->assertArrayHasKey('subclass', $totalBonuses);
        $this->assertEquals(1, $totalBonuses['subclass']);
    }

    #[Test]
    public function vengeance_at_ease_provides_stress_bonus(): void
    {
        $character = Character::factory()->create([
            'subclass' => 'vengeance',
            'level' => 1,
        ]);

        $stressBonus = $character->getSubclassStressBonus();
        $totalBonuses = $character->getTotalStressBonuses();

        $this->assertEquals(1, $stressBonus);
        $this->assertArrayHasKey('subclass', $totalBonuses);
        $this->assertEquals(1, $totalBonuses['subclass']);
    }

    #[Test]
    public function stalwart_provides_damage_threshold_bonuses(): void
    {
        $character = Character::factory()->create([
            'subclass' => 'stalwart',
            'level' => 1,
        ]);

        $damageThresholdBonus = $character->getSubclassDamageThresholdBonus();

        // Stalwart should get bonuses from foundation (+1), specialization (+2), and mastery (+3) = +6 total
        $this->assertEquals(6, $damageThresholdBonus);
    }

    #[Test]
    public function nightwalker_provides_evasion_bonus(): void
    {
        $character = Character::factory()->create([
            'subclass' => 'nightwalker',
            'level' => 1,
        ]);

        $evasionBonus = $character->getSubclassEvasionBonus();
        $totalBonuses = $character->getTotalEvasionBonuses();

        $this->assertEquals(1, $evasionBonus);
        $this->assertArrayHasKey('subclass', $totalBonuses);
        $this->assertEquals(1, $totalBonuses['subclass']);
    }

    #[Test]
    public function winged_sentinel_provides_severe_threshold_bonus(): void
    {
        $character = Character::factory()->create([
            'subclass' => 'winged sentinel',
            'level' => 1,
        ]);

        $severeThresholdBonus = $character->getSubclassSevereThresholdBonus();

        $this->assertEquals(4, $severeThresholdBonus);
    }

    #[Test]
    public function school_of_knowledge_provides_domain_card_bonus(): void
    {
        $character = Character::factory()->create([
            'subclass' => 'school of knowledge',
            'level' => 1,
        ]);

        $domainCardBonus = $character->getSubclassDomainCardBonus();

        // School of Knowledge should get bonuses from foundation (+1), specialization (+1), and mastery (+1) = +3 total
        $this->assertEquals(3, $domainCardBonus);
    }

    #[Test]
    public function non_bonus_subclasses_return_zero(): void
    {
        $character = Character::factory()->create([
            'subclass' => 'beastbound', // No stat bonuses
            'level' => 1,
        ]);

        $this->assertEquals(0, $character->getSubclassEvasionBonus());
        $this->assertEquals(0, $character->getSubclassHitPointBonus());
        $this->assertEquals(0, $character->getSubclassStressBonus());
        $this->assertEquals(0, $character->getSubclassDamageThresholdBonus());
        $this->assertEquals(0, $character->getSubclassSevereThresholdBonus());
        $this->assertEquals(0, $character->getSubclassDomainCardBonus());
    }

    #[Test]
    public function all_subclass_bonus_methods_handle_null_subclass(): void
    {
        $character = Character::factory()->create([
            'subclass' => null,
            'level' => 1,
        ]);

        $this->assertEquals(0, $character->getSubclassEvasionBonus());
        $this->assertEquals(0, $character->getSubclassHitPointBonus());
        $this->assertEquals(0, $character->getSubclassStressBonus());
        $this->assertEquals(0, $character->getSubclassDamageThresholdBonus());
        $this->assertEquals(0, $character->getSubclassSevereThresholdBonus());
        $this->assertEquals(0, $character->getSubclassDomainCardBonus());
    }

    #[Test]
    public function subclass_and_ancestry_bonuses_stack(): void
    {
        $character = Character::factory()->create([
            'ancestry' => 'simiah', // +1 evasion
            'subclass' => 'nightwalker', // +1 evasion
            'level' => 1,
        ]);

        $totalEvasionBonuses = $character->getTotalEvasionBonuses();

        $this->assertArrayHasKey('ancestry', $totalEvasionBonuses);
        $this->assertArrayHasKey('subclass', $totalEvasionBonuses);
        $this->assertEquals(1, $totalEvasionBonuses['ancestry']);
        $this->assertEquals(1, $totalEvasionBonuses['subclass']);
        $this->assertEquals(2, array_sum($totalEvasionBonuses));
    }
}
