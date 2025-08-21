<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Character\Data;

use Domain\Character\Data\CharacterBuilderData;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CharacterBuilderDataClankTest extends TestCase
{
    #[Test]
    public function clank_ancestry_can_select_bonus_experience(): void
    {
        $data = new CharacterBuilderData(
            selected_ancestry: 'clank',
            clank_bonus_experience: 'Blacksmith'
        );

        $this->assertEquals('Blacksmith', $data->getClankBonusExperience());
    }

    #[Test]
    public function non_clank_ancestry_returns_null_for_bonus_experience(): void
    {
        $data = new CharacterBuilderData(
            selected_ancestry: 'human',
            clank_bonus_experience: 'Blacksmith'
        );

        $this->assertNull($data->getClankBonusExperience());
    }

    #[Test]
    public function clank_bonus_experience_increases_modifier_to_three(): void
    {
        $data = new CharacterBuilderData(
            selected_ancestry: 'clank',
            clank_bonus_experience: 'Blacksmith'
        );

        $this->assertEquals(3, $data->getExperienceModifier('Blacksmith'));
        $this->assertEquals(2, $data->getExperienceModifier('Other Experience'));
    }

    #[Test]
    public function non_clank_ancestry_always_has_base_modifier(): void
    {
        $data = new CharacterBuilderData(selected_ancestry: 'human');

        $this->assertEquals(2, $data->getExperienceModifier('Blacksmith'));
        $this->assertEquals(2, $data->getExperienceModifier('Other Experience'));
    }

    #[Test]
    public function clank_ancestry_without_selected_bonus_has_base_modifier(): void
    {
        $data = new CharacterBuilderData(selected_ancestry: 'clank');

        $this->assertEquals(2, $data->getExperienceModifier('Blacksmith'));
        $this->assertEquals(2, $data->getExperienceModifier('Other Experience'));
    }

    #[Test]
    public function clank_bonus_experience_returns_null_when_not_set(): void
    {
        $data = new CharacterBuilderData(selected_ancestry: 'clank');

        $this->assertNull($data->getClankBonusExperience());
    }

    #[Test]
    public function clank_bonus_experience_can_be_constructed(): void
    {
        $data = new CharacterBuilderData(
            selected_ancestry: 'clank',
            clank_bonus_experience: 'Silver Tongue'
        );

        $this->assertEquals('clank', $data->selected_ancestry);
        $this->assertEquals('Silver Tongue', $data->clank_bonus_experience);
    }
}
