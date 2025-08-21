<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Character\Data;

use Domain\Character\Data\CharacterStatsData;
use Domain\Character\Models\Character;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CharacterStatsDataSubclassTest extends TestCase
{
    #[Test]
    public function stats_include_subclass_hit_point_bonus(): void
    {
        $character = $this->createCharacterWithSubclass('school of war'); // +1 hit point

        $stats = CharacterStatsData::fromModel($character);

        // Warrior base hit points (6) + subclass bonus (1) = 7
        $this->assertEquals(7, $stats->hit_points);
    }

    #[Test]
    public function stats_include_subclass_stress_bonus(): void
    {
        $character = $this->createCharacterWithSubclass('vengeance'); // +1 stress

        $stats = CharacterStatsData::fromModel($character);

        // Base stress (6) + subclass bonus (1) = 7
        $this->assertEquals(7, $stats->stress);
    }

    #[Test]
    public function stats_include_subclass_evasion_bonus(): void
    {
        $character = $this->createCharacterWithSubclass('nightwalker'); // +1 evasion

        // Set a specific agility value for predictable testing
        $character->traits()->create([
            'character_id' => $character->id,
            'trait_name' => 'agility',
            'trait_value' => 1,
        ]);

        $stats = CharacterStatsData::fromModel($character);

        // Warrior base evasion (11) + agility modifier (1) + subclass bonus (1) = 13
        $this->assertEquals(13, $stats->evasion);
    }

    #[Test]
    public function stats_include_subclass_damage_threshold_bonuses(): void
    {
        $character = $this->createCharacterWithSubclass('stalwart'); // +6 total damage threshold bonus

        $stats = CharacterStatsData::fromModel($character);

        // For level 1 character: armor(1) + proficiency(0) + level(1) + subclass(6) = 8
        // For severe: armor(1) + proficiency(0) + level(1) + 5 + subclass(6) = 13
        $this->assertEquals(8, $stats->major_threshold);
        $this->assertEquals(13, $stats->severe_threshold);
    }

    #[Test]
    public function stats_include_subclass_severe_threshold_bonus(): void
    {
        $character = $this->createCharacterWithSubclass('winged sentinel'); // +4 severe threshold bonus

        $stats = CharacterStatsData::fromModel($character);

        // For level 1 character: armor(1) + proficiency(0) + level(1) + 5 + subclass(4) = 11
        $this->assertEquals(11, $stats->severe_threshold);
    }

    #[Test]
    public function stats_combine_ancestry_and_subclass_bonuses(): void
    {
        $character = $this->createCharacterWithAncestryAndSubclass('simiah', 'nightwalker'); // +1 evasion each

        // Set a specific agility value for predictable testing
        $character->traits()->create([
            'character_id' => $character->id,
            'trait_name' => 'agility',
            'trait_value' => 1,
        ]);

        $stats = CharacterStatsData::fromModel($character);

        // Warrior base evasion (11) + agility modifier (1) + ancestry bonus (1) + subclass bonus (1) = 14
        $this->assertEquals(14, $stats->evasion);
    }

    private function createCharacterWithSubclass(string $subclass): Character
    {
        return Character::factory()->create([
            'class' => 'warrior',
            'subclass' => $subclass,
            'ancestry' => 'elf', // Use elf to avoid random ancestry bonuses
            'level' => 1,
        ]);
    }

    private function createCharacterWithAncestryAndSubclass(string $ancestry, string $subclass): Character
    {
        return Character::factory()->create([
            'class' => 'warrior',
            'ancestry' => $ancestry,
            'subclass' => $subclass,
            'level' => 1,
        ]);
    }
}
