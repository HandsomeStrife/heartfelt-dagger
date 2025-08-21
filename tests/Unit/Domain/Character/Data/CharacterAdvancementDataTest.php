<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Character\Data;

use Domain\Character\Data\CharacterAdvancementData;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CharacterAdvancementDataTest extends TestCase
{
    #[Test]
    public function trait_bonus_creates_correct_advancement_data(): void
    {
        $advancement = CharacterAdvancementData::traitBonus(
            tier: 1,
            advancement_number: 1,
            traits: ['agility', 'strength'],
            bonus: 1
        );

        $this->assertEquals(1, $advancement->tier);
        $this->assertEquals(1, $advancement->advancement_number);
        $this->assertEquals('trait_bonus', $advancement->advancement_type);
        $this->assertEquals(['agility', 'strength'], $advancement->advancement_data['traits']);
        $this->assertEquals(1, $advancement->advancement_data['bonus']);
        $this->assertStringContainsString('Gain a +1 bonus to agility and strength', $advancement->description);
    }

    #[Test]
    public function hit_point_creates_correct_advancement_data(): void
    {
        $advancement = CharacterAdvancementData::hitPoint(2, 1);

        $this->assertEquals(2, $advancement->tier);
        $this->assertEquals(1, $advancement->advancement_number);
        $this->assertEquals('hit_point', $advancement->advancement_type);
        $this->assertEquals(1, $advancement->advancement_data['bonus']);
        $this->assertEquals('Gain an additional Hit Point slot', $advancement->description);
    }

    #[Test]
    public function stress_creates_correct_advancement_data(): void
    {
        $advancement = CharacterAdvancementData::stress(2, 2);

        $this->assertEquals(2, $advancement->tier);
        $this->assertEquals(2, $advancement->advancement_number);
        $this->assertEquals('stress', $advancement->advancement_type);
        $this->assertEquals(1, $advancement->advancement_data['bonus']);
        $this->assertEquals('Gain an additional Stress slot', $advancement->description);
    }

    #[Test]
    public function experience_bonus_creates_correct_advancement_data(): void
    {
        $advancement = CharacterAdvancementData::experienceBonus(3, 1);

        $this->assertEquals(3, $advancement->tier);
        $this->assertEquals(1, $advancement->advancement_number);
        $this->assertEquals('experience_bonus', $advancement->advancement_type);
        $this->assertEquals(1, $advancement->advancement_data['bonus']);
        $this->assertEquals('Your experiences now provide a +3 modifier instead of +2', $advancement->description);
    }

    #[Test]
    public function domain_card_creates_correct_advancement_data(): void
    {
        $advancement = CharacterAdvancementData::domainCard(3, 2, 2);

        $this->assertEquals(3, $advancement->tier);
        $this->assertEquals(2, $advancement->advancement_number);
        $this->assertEquals('domain_card', $advancement->advancement_type);
        $this->assertEquals(2, $advancement->advancement_data['level']);
        $this->assertEquals('Take a level 2 domain card from your class domains', $advancement->description);
    }

    #[Test]
    public function evasion_creates_correct_advancement_data(): void
    {
        $advancement = CharacterAdvancementData::evasion(4, 1);

        $this->assertEquals(4, $advancement->tier);
        $this->assertEquals(1, $advancement->advancement_number);
        $this->assertEquals('evasion', $advancement->advancement_type);
        $this->assertEquals(1, $advancement->advancement_data['bonus']);
        $this->assertEquals('Permanently gain a +1 bonus to your Evasion', $advancement->description);
    }

    #[Test]
    public function subclass_creates_correct_advancement_data(): void
    {
        $advancement = CharacterAdvancementData::subclass(4, 2, 'upgraded');

        $this->assertEquals(4, $advancement->tier);
        $this->assertEquals(2, $advancement->advancement_number);
        $this->assertEquals('subclass', $advancement->advancement_type);
        $this->assertEquals('upgraded', $advancement->advancement_data['type']);
        $this->assertEquals('Take an upgraded subclass card', $advancement->description);
    }

    #[Test]
    public function proficiency_creates_correct_advancement_data(): void
    {
        $advancement = CharacterAdvancementData::proficiency(2, 1);

        $this->assertEquals(2, $advancement->tier);
        $this->assertEquals(1, $advancement->advancement_number);
        $this->assertEquals('proficiency', $advancement->advancement_type);
        $this->assertEquals(1, $advancement->advancement_data['bonus']);
        $this->assertEquals('Increase your Proficiency by +1', $advancement->description);
    }

    #[Test]
    public function multiclass_creates_correct_advancement_data(): void
    {
        $advancement = CharacterAdvancementData::multiclass(4, 1, 'wizard');

        $this->assertEquals(4, $advancement->tier);
        $this->assertEquals(1, $advancement->advancement_number);
        $this->assertEquals('multiclass', $advancement->advancement_type);
        $this->assertEquals('wizard', $advancement->advancement_data['class']);
        $this->assertStringContainsString('Multiclass: Choose wizard as an additional class', $advancement->description);
    }

    #[Test]
    public function can_create_advancement_with_custom_data(): void
    {
        $advancement = new CharacterAdvancementData(
            tier: 3,
            advancement_number: 2,
            advancement_type: 'custom',
            advancement_data: ['custom_field' => 'custom_value'],
            description: 'Custom advancement description'
        );

        $this->assertEquals(3, $advancement->tier);
        $this->assertEquals(2, $advancement->advancement_number);
        $this->assertEquals('custom', $advancement->advancement_type);
        $this->assertEquals(['custom_field' => 'custom_value'], $advancement->advancement_data);
        $this->assertEquals('Custom advancement description', $advancement->description);
    }

    #[Test]
    public function trait_bonus_with_multiple_traits_creates_proper_description(): void
    {
        $advancement = CharacterAdvancementData::traitBonus(
            tier: 2,
            advancement_number: 1,
            traits: ['agility', 'finesse', 'instinct'],
            bonus: 1
        );

        $this->assertStringContainsString('agility and finesse and instinct', $advancement->description);
    }

    #[Test]
    public function trait_bonus_with_single_trait_creates_proper_description(): void
    {
        $advancement = CharacterAdvancementData::traitBonus(
            tier: 1,
            advancement_number: 2,
            traits: ['strength'],
            bonus: 1
        );

        $this->assertStringContainsString('Gain a +1 bonus to strength', $advancement->description);
        $this->assertStringNotContainsString(' and ', $advancement->description);
    }
}
