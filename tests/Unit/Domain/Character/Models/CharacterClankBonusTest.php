<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Character\Models;

use Domain\Character\Models\Character;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CharacterClankBonusTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function clank_ancestry_can_select_bonus_experience(): void
    {
        $character = Character::factory()->create([
            'ancestry' => 'clank',
            'character_data' => ['clank_bonus_experience' => 'Blacksmith'],
        ]);

        $this->assertEquals('Blacksmith', $character->getClankBonusExperience());
    }

    #[Test]
    public function non_clank_ancestry_returns_null_for_bonus_experience(): void
    {
        $character = Character::factory()->create([
            'ancestry' => 'human',
            'character_data' => ['clank_bonus_experience' => 'Blacksmith'],
        ]);

        $this->assertNull($character->getClankBonusExperience());
    }

    #[Test]
    public function clank_bonus_experience_increases_modifier_to_three(): void
    {
        $character = Character::factory()->create([
            'ancestry' => 'clank',
            'character_data' => ['clank_bonus_experience' => 'Blacksmith'],
        ]);

        $this->assertEquals(3, $character->getExperienceModifier('Blacksmith'));
        $this->assertEquals(2, $character->getExperienceModifier('Other Experience'));
    }

    #[Test]
    public function non_clank_ancestry_always_has_base_modifier(): void
    {
        $character = Character::factory()->create(['ancestry' => 'human']);

        $this->assertEquals(2, $character->getExperienceModifier('Blacksmith'));
        $this->assertEquals(2, $character->getExperienceModifier('Other Experience'));
    }

    #[Test]
    public function clank_ancestry_without_selected_bonus_has_base_modifier(): void
    {
        $character = Character::factory()->create(['ancestry' => 'clank']);

        $this->assertEquals(2, $character->getExperienceModifier('Blacksmith'));
        $this->assertEquals(2, $character->getExperienceModifier('Other Experience'));
    }

    #[Test]
    public function clank_bonus_experience_returns_null_when_not_set(): void
    {
        $character = Character::factory()->create(['ancestry' => 'clank']);

        $this->assertNull($character->getClankBonusExperience());
    }
}
