<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Character\Data;

use Domain\Character\Data\CharacterBuilderData;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CharacterBuilderDataDomainCardTest extends TestCase
{
    #[Test]
    public function school_of_knowledge_builder_provides_domain_card_bonuses(): void
    {
        $builder = new CharacterBuilderData(
            selected_class: 'wizard',
            selected_subclass: 'school of knowledge'
        );

        $domainCardBonus = $builder->getSubclassDomainCardBonus();
        $maxDomainCards = $builder->getMaxDomainCards();

        // School of Knowledge should get bonus only from specialization (+1) = +1 total
        $this->assertEquals(1, $domainCardBonus);
        
        // Base 2 cards + 1 bonus card = 3 total
        $this->assertEquals(3, $maxDomainCards);
    }

    #[Test]
    public function non_bonus_subclass_builder_has_default_domain_cards(): void
    {
        $builder = new CharacterBuilderData(
            selected_class: 'ranger',
            selected_subclass: 'beastbound'
        );

        $domainCardBonus = $builder->getSubclassDomainCardBonus();
        $maxDomainCards = $builder->getMaxDomainCards();

        $this->assertEquals(0, $domainCardBonus);
        $this->assertEquals(2, $maxDomainCards); // Base cards only
    }

    #[Test]
    public function null_subclass_builder_has_default_domain_cards(): void
    {
        $builder = new CharacterBuilderData(
            selected_class: 'warrior',
            selected_subclass: null
        );

        $domainCardBonus = $builder->getSubclassDomainCardBonus();
        $maxDomainCards = $builder->getMaxDomainCards();

        $this->assertEquals(0, $domainCardBonus);
        $this->assertEquals(2, $maxDomainCards); // Base cards only
    }

    #[Test]
    public function builder_domain_card_calculations_match_different_subclasses(): void
    {
        $testCases = [
            ['subclass' => 'school of knowledge', 'expected_bonus' => 1, 'expected_max' => 3],
            ['subclass' => 'school of war', 'expected_bonus' => 0, 'expected_max' => 2],
            ['subclass' => 'stalwart', 'expected_bonus' => 0, 'expected_max' => 2],
            ['subclass' => 'nightwalker', 'expected_bonus' => 0, 'expected_max' => 2],
        ];

        foreach ($testCases as $testCase) {
            $builder = new CharacterBuilderData(
                selected_class: 'warrior',
                selected_subclass: $testCase['subclass']
            );

            $this->assertEquals(
                $testCase['expected_bonus'],
                $builder->getSubclassDomainCardBonus(),
                "Builder with subclass {$testCase['subclass']} should have {$testCase['expected_bonus']} domain card bonus"
            );

            $this->assertEquals(
                $testCase['expected_max'],
                $builder->getMaxDomainCards(),
                "Builder with subclass {$testCase['subclass']} should have {$testCase['expected_max']} max domain cards"
            );
        }
    }

    #[Test]
    public function builder_domain_card_methods_handle_missing_subclass_json(): void
    {
        // Create a builder with a non-existent subclass
        $builder = new CharacterBuilderData(
            selected_class: 'warrior',
            selected_subclass: 'non-existent-subclass'
        );

        // Should gracefully return 0 for non-existent subclass
        $this->assertEquals(0, $builder->getSubclassDomainCardBonus());
        $this->assertEquals(2, $builder->getMaxDomainCards());
    }
}
