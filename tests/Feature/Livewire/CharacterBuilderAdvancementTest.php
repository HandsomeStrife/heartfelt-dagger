<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\CharacterBuilder;
use Domain\Character\Data\CharacterBuilderData;
use Domain\Character\Models\Character;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CharacterBuilderAdvancementTest extends TestCase
{
    use RefreshDatabase;

    protected function createTestCharacter(): Character
    {
        return Character::factory()->create();
    }

    #[Test]
    public function character_builder_displays_ancestry_bonuses_in_stats(): void
    {
        // Create a character first
        $character = Character::factory()->create();
        
        $component = Livewire::test(CharacterBuilder::class, ['characterKey' => $character->character_key])
            ->call('selectClass', 'warrior')
            ->call('selectAncestry', 'simiah')
            ->call('selectCommunity', 'wildborne');

        // Verify computed stats include ancestry bonuses
        $computed_stats = $component->get('computed_stats');
        
        // Simiah should get +1 evasion bonus
        $this->assertArrayHasKey('evasion', $computed_stats);
        $this->assertEquals(12, $computed_stats['evasion']); // Base 11 + 1 from Simiah
    }

    #[Test]
    public function character_builder_shows_giant_hit_point_bonus(): void
    {
        $character = $this->createTestCharacter();
        $component = Livewire::test(CharacterBuilder::class, ['characterKey' => $character->character_key])
            ->call('selectClass', 'warrior')
            ->call('selectAncestry', 'giant')
            ->call('selectCommunity', 'ridgeborne');

        $computed_stats = $component->get('computed_stats');
        
        // Giant should get +1 hit point bonus
        $this->assertArrayHasKey('hit_points', $computed_stats);
        $this->assertEquals(7, $computed_stats['hit_points']); // Base 6 + 1 from Giant
    }

    #[Test]
    public function character_builder_shows_human_stress_bonus(): void
    {
        $character = $this->createTestCharacter();
        $component = Livewire::test(CharacterBuilder::class, ['characterKey' => $character->character_key])
            ->call('selectClass', 'warrior')
            ->call('selectAncestry', 'human')
            ->call('selectCommunity', 'highborne');

        $computed_stats = $component->get('computed_stats');
        
        // Human should get +1 stress bonus
        $this->assertArrayHasKey('stress', $computed_stats);
        $this->assertEquals(7, $computed_stats['stress']); // Base 6 + 1 from Human
    }

    #[Test]
    public function character_builder_shows_galapa_damage_threshold_info(): void
    {
        $character = $this->createTestCharacter();
        $component = Livewire::test(CharacterBuilder::class, ['characterKey' => $character->character_key])
            ->call('selectClass', 'warrior')
            ->call('selectAncestry', 'galapa')
            ->call('selectCommunity', 'seaborne');

        $computed_stats = $component->get('computed_stats');
        
        // Galapa gets damage threshold bonus equal to proficiency
        $this->assertArrayHasKey('major_threshold', $computed_stats);
        $this->assertArrayHasKey('severe_threshold', $computed_stats);
        
        // At level 1, proficiency is 0, so thresholds get +0 bonus  
        $this->assertEquals(6, $computed_stats['major_threshold']); // 1 base armor + 0 prof + 3 + 2 from calculation
        $this->assertEquals(11, $computed_stats['severe_threshold']); // 1 base armor + 0 prof + 8 + 2 from calculation
    }

    #[Test]
    public function character_builder_updates_stats_when_traits_assigned(): void
    {
        $character = $this->createTestCharacter();
        $component = Livewire::test(CharacterBuilder::class, ['characterKey' => $character->character_key])
            ->call('selectClass', 'warrior')
            ->call('selectAncestry', 'simiah')
            ->call('selectCommunity', 'wildborne')
            ->call('assignTrait', 'agility', 2); // Assign +2 to agility

        $computed_stats = $component->get('computed_stats');
        
        // Evasion should be base + ancestry bonus + trait bonus
        $this->assertEquals(14, $computed_stats['evasion']); // Base 11 + Simiah 1 + Agility 2
    }

    #[Test]
    public function character_builder_validates_trait_assignment_with_bonuses(): void
    {
        $character = $this->createTestCharacter();
        $component = Livewire::test(CharacterBuilder::class, ['characterKey' => $character->character_key])
            ->call('selectClass', 'warrior')
            ->call('selectAncestry', 'simiah')
            ->call('selectCommunity', 'wildborne');

        // Assign all traits correctly
        $component->call('assignTrait', 'agility', 2)
            ->call('assignTrait', 'strength', 1)
            ->call('assignTrait', 'finesse', 1)
            ->call('assignTrait', 'instinct', 0)
            ->call('assignTrait', 'presence', 0)
            ->call('assignTrait', 'knowledge', -1);

        $computed_stats = $component->get('computed_stats');
        
        // Verify the stats are computed correctly with trait assignments
        $this->assertEquals(14, $computed_stats['evasion']); // 11 + 1 (ancestry) + 2 (agility)
        $this->assertEquals(6, $computed_stats['hit_points']); // Base warrior hit points
    }

    #[Test]
    public function character_builder_experience_functionality_works(): void
    {
        $character = $this->createTestCharacter();
        $component = Livewire::test(CharacterBuilder::class, ['characterKey' => $character->character_key])
            ->call('selectClass', 'warrior')
            ->call('selectAncestry', 'human')
            ->call('selectCommunity', 'wildborne');

        // Test adding an experience
        $component->set('new_experience_name', 'Blacksmith')
            ->set('new_experience_description', 'Skilled in metalworking')
            ->call('addExperience');

        $character = $component->get('character');
        
        $this->assertCount(1, $character->experiences);
        $this->assertEquals('Blacksmith', $character->experiences[0]['name']);
        $this->assertEquals('Skilled in metalworking', $character->experiences[0]['description']);
        $this->assertEquals(2, $character->experiences[0]['modifier']);
        
        // Verify form was cleared
        $this->assertEquals('', $component->get('new_experience_name'));
        $this->assertEquals('', $component->get('new_experience_description'));
    }

    #[Test]
    public function character_builder_prevents_more_than_two_experiences(): void
    {
        $character = $this->createTestCharacter();
        $component = Livewire::test(CharacterBuilder::class, ['characterKey' => $character->character_key])
            ->call('selectClass', 'warrior')
            ->call('selectAncestry', 'human')
            ->call('selectCommunity', 'wildborne');

        // Add two experiences
        $component->set('new_experience_name', 'Blacksmith')
            ->call('addExperience')
            ->set('new_experience_name', 'Tracker')
            ->call('addExperience');

        $character = $component->get('character');
        $this->assertCount(2, $character->experiences);

        // Try to add a third - should be ignored
        $component->set('new_experience_name', 'Healer')
            ->call('addExperience');

        $character = $component->get('character');
        $this->assertCount(2, $character->experiences); // Still only 2
    }

    #[Test]
    public function character_builder_can_remove_experiences(): void
    {
        $character = $this->createTestCharacter();
        $component = Livewire::test(CharacterBuilder::class, ['characterKey' => $character->character_key])
            ->call('selectClass', 'warrior')
            ->call('selectAncestry', 'human')
            ->call('selectCommunity', 'wildborne');

        // Add an experience
        $component->set('new_experience_name', 'Blacksmith')
            ->call('addExperience');

        $character = $component->get('character');
        $this->assertCount(1, $character->experiences);

        // Remove the experience
        $component->call('removeExperience', 0);

        $character = $component->get('character');
        $this->assertCount(0, $character->experiences);
    }

    #[Test]
    public function character_builder_displays_bonuses_in_ui(): void
    {
        $character = $this->createTestCharacter();
        $component = Livewire::test(CharacterBuilder::class, ['characterKey' => $character->character_key])
            ->call('selectClass', 'warrior')
            ->call('selectAncestry', 'simiah')
            ->call('selectCommunity', 'wildborne');

        $ancestry_bonuses = $component->get('ancestry_bonuses');
        
        // Check that Simiah evasion bonus exists
        $this->assertArrayHasKey('evasion', $ancestry_bonuses);
        $this->assertEquals(1, $ancestry_bonuses['evasion']);
        
        // Other bonuses should not be present (getAncestryBonuses only returns non-zero bonuses)
        $this->assertArrayNotHasKey('hit_points', $ancestry_bonuses);
        $this->assertArrayNotHasKey('stress', $ancestry_bonuses);
        $this->assertArrayNotHasKey('damage_thresholds', $ancestry_bonuses);
    }

    #[Test]
    public function character_builder_updates_progress_with_all_bonuses(): void
    {
        $character = $this->createTestCharacter();
        $component = Livewire::test(CharacterBuilder::class, ['characterKey' => $character->character_key])
            ->call('selectClass', 'warrior')
            ->call('selectAncestry', 'giant')
            ->call('selectCommunity', 'ridgeborne');

        // Complete trait assignment
        $component->call('assignTrait', 'agility', 1)
            ->call('assignTrait', 'strength', 2)
            ->call('assignTrait', 'finesse', 0)
            ->call('assignTrait', 'instinct', 0)
            ->call('assignTrait', 'presence', 1)
            ->call('assignTrait', 'knowledge', -1);

        $computed_stats = $component->get('computed_stats');
        
        // Verify Giant ancestry bonus is applied
        $this->assertEquals(7, $computed_stats['hit_points']); // Base 6 + Giant 1
        $this->assertEquals(12, $computed_stats['evasion']); // Base 11 + Agility 1
    }

    #[Test]
    public function character_builder_computed_stats_property_includes_all_bonuses(): void
    {
        $character = $this->createTestCharacter();
        $component = Livewire::test(CharacterBuilder::class, ['characterKey' => $character->character_key])
            ->call('selectClass', 'bard') // Different class for variety
            ->call('selectAncestry', 'human')
            ->call('selectCommunity', 'highborne');

        $computed_stats = $component->get('computed_stats');
        
        // Should include ancestry bonuses
        $this->assertIsArray($computed_stats);
        $this->assertArrayHasKey('evasion', $computed_stats);
        $this->assertArrayHasKey('hit_points', $computed_stats);
        $this->assertArrayHasKey('stress', $computed_stats);
        $this->assertArrayHasKey('major_threshold', $computed_stats);
        $this->assertArrayHasKey('severe_threshold', $computed_stats);
        
        // Human gets +1 stress
        $this->assertEquals(7, $computed_stats['stress']); // Base 6 + Human 1
    }

    #[Test]
    public function character_builder_saves_and_loads_with_ancestry_bonuses(): void
    {
        // Create and save a character
        $character = $this->createTestCharacter();
        $component = Livewire::test(CharacterBuilder::class, ['characterKey' => $character->character_key])
            ->call('selectClass', 'warrior')
            ->call('selectAncestry', 'simiah')
            ->call('selectCommunity', 'wildborne')
            ->call('saveCharacter');

        $character_key = $component->get('character')->character_key;
        
        // Load the character in a new component
        $newComponent = Livewire::test(CharacterBuilder::class, ['characterKey' => $character_key]);
        
        $computed_stats = $newComponent->get('computed_stats');
        $ancestry_bonuses = $newComponent->get('ancestry_bonuses');
        
        // Verify ancestry bonuses are preserved
        $this->assertEquals(1, $ancestry_bonuses['evasion']);
        $this->assertEquals(12, $computed_stats['evasion']); // Should include Simiah bonus
    }
}
