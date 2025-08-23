<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Character\Models;

use Domain\Character\Models\Character;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CharacterSubclassDomainCardTest extends TestCase
{
    #[Test]
    public function school_of_knowledge_provides_domain_card_bonuses(): void
    {
        $character = Character::factory()->create([
            'subclass' => 'school of knowledge',
            'level' => 1,
        ]);

        $domainCardBonus = $character->getSubclassDomainCardBonus();
        $maxDomainCards = $character->getMaxDomainCards();

        // School of Knowledge should get bonus only from specialization (+1) = +1 total
        $this->assertEquals(1, $domainCardBonus);
        
        // Base 2 cards + 1 bonus card = 3 total
        $this->assertEquals(3, $maxDomainCards);
    }

    #[Test]
    public function non_bonus_subclasses_have_default_domain_cards(): void
    {
        $character = Character::factory()->create([
            'subclass' => 'beastbound', // No domain card bonuses
            'level' => 1,
        ]);

        $domainCardBonus = $character->getSubclassDomainCardBonus();
        $maxDomainCards = $character->getMaxDomainCards();

        $this->assertEquals(0, $domainCardBonus);
        $this->assertEquals(2, $maxDomainCards); // Base cards only
    }

    #[Test]
    public function null_subclass_has_default_domain_cards(): void
    {
        $character = Character::factory()->create([
            'subclass' => null,
            'level' => 1,
        ]);

        $domainCardBonus = $character->getSubclassDomainCardBonus();
        $maxDomainCards = $character->getMaxDomainCards();

        $this->assertEquals(0, $domainCardBonus);
        $this->assertEquals(2, $maxDomainCards); // Base cards only
    }

    #[Test]
    public function various_subclasses_domain_card_calculations(): void
    {
        // Test different subclasses that might have domain card bonuses
        $testCases = [
            ['subclass' => 'school of knowledge', 'expected_bonus' => 1, 'expected_max' => 3],
            ['subclass' => 'school of war', 'expected_bonus' => 0, 'expected_max' => 2],
            ['subclass' => 'stalwart', 'expected_bonus' => 0, 'expected_max' => 2],
            ['subclass' => 'nightwalker', 'expected_bonus' => 0, 'expected_max' => 2],
        ];

        foreach ($testCases as $testCase) {
            $character = Character::factory()->create([
                'subclass' => $testCase['subclass'],
                'level' => 1,
            ]);

            $this->assertEquals(
                $testCase['expected_bonus'],
                $character->getSubclassDomainCardBonus(),
                "Subclass {$testCase['subclass']} should have {$testCase['expected_bonus']} domain card bonus"
            );

            $this->assertEquals(
                $testCase['expected_max'],
                $character->getMaxDomainCards(),
                "Subclass {$testCase['subclass']} should have {$testCase['expected_max']} max domain cards"
            );
        }
    }
}
