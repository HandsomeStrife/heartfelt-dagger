<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Character\Data;

use Domain\Character\Data\CharacterBuilderData;
use Domain\Character\Data\CharacterStatsData;
use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterAdvancement;
use Domain\Character\Models\CharacterTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CharacterStatsDataTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function from_model_applies_simiah_evasion_bonus(): void
    {
        $character = $this->createCharacterWithClass('warrior', 'simiah');
        
        // Warrior base evasion is 11, Simiah gets +1, Agility trait adds modifier
        $this->createCharacterTrait($character, 'agility', 1);
        
        $stats = CharacterStatsData::fromModel($character);
        
        // Base 11 + Simiah bonus 1 + Agility 1 = 13
        $this->assertEquals(13, $stats->evasion);
    }

    #[Test]
    public function from_model_applies_giant_hit_point_bonus(): void
    {
        $character = $this->createCharacterWithClass('warrior', 'giant');
        
        $stats = CharacterStatsData::fromModel($character);
        
        // Warrior base 6 + Giant bonus 1 = 7
        $this->assertEquals(7, $stats->hit_points);
    }

    #[Test]
    public function from_model_applies_human_stress_bonus(): void
    {
        $character = $this->createCharacterWithClass('warrior', 'human');
        
        $stats = CharacterStatsData::fromModel($character);
        
        // Base 6 + Human bonus 1 = 7
        $this->assertEquals(7, $stats->stress);
    }

    #[Test]
    public function from_model_applies_galapa_damage_threshold_bonus(): void
    {
        $character = $this->createCharacterWithClass('warrior', 'galapa', level: 3);
        
        $stats = CharacterStatsData::fromModel($character);
        
        // Galapa gets bonus equal to proficiency level
        // Level 3 = Proficiency +1, so thresholds get +1
        $expectedMajor = 1 + 1 + 3 + 1; // armor + proficiency + level + galapa bonus
        $expectedSevere = 1 + 1 + 8 + 1; // armor + proficiency + level + galapa bonus
        
        $this->assertEquals($expectedMajor, $stats->major_threshold);
        $this->assertEquals($expectedSevere, $stats->severe_threshold);
    }

    #[Test]
    public function from_character_builder_applies_simiah_evasion_bonus(): void
    {
        $builder = $this->createBuilderData('warrior', 'simiah');
        $class_data = ['startingEvasion' => 9, 'startingHitPoints' => 7];
        
        $stats = CharacterStatsData::fromCharacterBuilder($builder, $class_data);
        
        // Base 9 + Simiah bonus 1 + Agility 1 = 11
        $this->assertEquals(11, $stats->evasion);
    }

    #[Test]
    public function from_character_builder_applies_giant_hit_point_bonus(): void
    {
        $builder = $this->createBuilderData('warrior', 'giant');
        $class_data = ['startingEvasion' => 9, 'startingHitPoints' => 7];
        
        $stats = CharacterStatsData::fromCharacterBuilder($builder, $class_data);
        
        // Base 7 + Giant bonus 1 = 8
        $this->assertEquals(8, $stats->hit_points);
    }

    #[Test]
    public function from_character_builder_applies_human_stress_bonus(): void
    {
        $builder = $this->createBuilderData('warrior', 'human');
        $class_data = ['startingEvasion' => 9, 'startingHitPoints' => 7];
        
        $stats = CharacterStatsData::fromCharacterBuilder($builder, $class_data);
        
        // Base 6 + Human bonus 1 = 7
        $this->assertEquals(7, $stats->stress);
    }

    #[Test]
    public function advancement_bonuses_are_applied_correctly(): void
    {
        $character = $this->createCharacterWithClass('warrior', 'elf');
        
        // Add evasion advancement
        CharacterAdvancement::factory()->create([
            'character_id' => $character->id,
            'tier' => 2,
            'advancement_number' => 1,
            'advancement_type' => 'evasion',
            'advancement_data' => ['bonus' => 1],
        ]);
        
        // Add hit point advancement
        CharacterAdvancement::factory()->create([
            'character_id' => $character->id,
            'tier' => 2,
            'advancement_number' => 2,
            'advancement_type' => 'hit_point',
            'advancement_data' => ['bonus' => 1],
        ]);
        
        $stats = CharacterStatsData::fromModel($character);
        
        // Base evasion 11 + advancement 1 = 12
        $this->assertEquals(12, $stats->evasion);
        // Base hit points 6 + advancement 1 = 7
        $this->assertEquals(7, $stats->hit_points);
    }

    #[Test]
    public function trait_bonus_advancements_are_applied(): void
    {
        $character = $this->createCharacterWithClass('warrior', 'elf');
        $this->createCharacterTrait($character, 'agility', 0); // Base agility 0
        
        // Add trait bonus advancement for agility
        CharacterAdvancement::factory()->create([
            'character_id' => $character->id,
            'tier' => 1,
            'advancement_number' => 1,
            'advancement_type' => 'trait_bonus',
            'advancement_data' => [
                'traits' => ['agility'],
                'bonus' => 1,
            ],
        ]);
        
        $stats = CharacterStatsData::fromModel($character);
        
        // Base evasion 11 + (agility 0 + advancement 1) = 12
        $this->assertEquals(12, $stats->evasion);
    }

    #[Test]
    public function multiple_advancement_bonuses_stack(): void
    {
        $character = $this->createCharacterWithClass('warrior', 'elf');
        
        // Add multiple evasion advancements
        CharacterAdvancement::factory()->create([
            'character_id' => $character->id,
            'tier' => 2,
            'advancement_number' => 1,
            'advancement_type' => 'evasion',
            'advancement_data' => ['bonus' => 1],
        ]);
        
        CharacterAdvancement::factory()->create([
            'character_id' => $character->id,
            'tier' => 3,
            'advancement_number' => 1,
            'advancement_type' => 'evasion',
            'advancement_data' => ['bonus' => 1],
        ]);
        
        $stats = CharacterStatsData::fromModel($character);
        
        // Base evasion 11 + advancement 1 + advancement 1 = 13
        $this->assertEquals(13, $stats->evasion);
    }

    #[Test]
    public function ancestry_and_advancement_bonuses_stack(): void
    {
        $character = $this->createCharacterWithClass('warrior', 'simiah');
        
        // Add evasion advancement
        CharacterAdvancement::factory()->create([
            'character_id' => $character->id,
            'tier' => 2,
            'advancement_number' => 1,
            'advancement_type' => 'evasion',
            'advancement_data' => ['bonus' => 1],
        ]);
        
        $stats = CharacterStatsData::fromModel($character);
        
        // Base evasion 11 + Simiah bonus 1 + advancement 1 = 13
        $this->assertEquals(13, $stats->evasion);
    }

    #[Test]
    public function no_ancestry_bonus_returns_base_stats(): void
    {
        $character = $this->createCharacterWithClass('warrior', 'elf');
        
        $stats = CharacterStatsData::fromModel($character);
        
        // Should use base class values with no ancestry bonuses
        $this->assertEquals(11, $stats->evasion); // Base warrior evasion
        $this->assertEquals(6, $stats->hit_points); // Base warrior hit points
        $this->assertEquals(6, $stats->stress); // Base stress
    }

    private function createCharacterWithClass(string $class, string $ancestry, int $level = 1): Character
    {
        return Character::factory()->create([
            'class' => $class,
            'ancestry' => $ancestry,
            'level' => $level,
            'character_data' => [
                'class_data' => [
                    'starting_evasion' => 9,
                    'starting_hit_points' => 7,
                ],
            ],
        ]);
    }

    private function createCharacterTrait(Character $character, string $trait, int $value): void
    {
        CharacterTrait::factory()->create([
            'character_id' => $character->id,
            'trait_name' => $trait,
            'trait_value' => $value,
        ]);
    }

    private function createBuilderData(string $class, string $ancestry): CharacterBuilderData
    {
        return CharacterBuilderData::from([
            'selected_class' => $class,
            'selected_ancestry' => $ancestry,
            'assigned_traits' => [
                'agility' => 1,
                'strength' => 0,
                'finesse' => 0,
                'instinct' => -1,
                'presence' => 1,
                'knowledge' => 2,
            ],
            'selected_equipment' => [],
            'experiences' => [],
            'background_answers' => [],
            'connections' => [],
            'selected_domain_cards' => [],
        ]);
    }
}
