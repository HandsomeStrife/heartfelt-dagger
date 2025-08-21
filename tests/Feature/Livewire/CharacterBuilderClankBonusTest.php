<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\CharacterBuilder;
use Domain\Character\Models\Character;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CharacterBuilderClankBonusTest extends TestCase
{
    use RefreshDatabase;

    protected function createTestCharacter(): Character
    {
        return Character::factory()->create();
    }

    #[Test]
    public function character_builder_shows_clank_bonus_functionality_for_clank_ancestry(): void
    {
        $character = $this->createTestCharacter();
        
        $component = Livewire::test(CharacterBuilder::class, ['characterKey' => $character->character_key])
            ->call('selectClass', 'warrior')
            ->call('selectAncestry', 'clank')
            ->call('selectCommunity', 'wildborne')
            ->call('addExperience', 'Blacksmith', 'Working with metal and tools');

        $component->assertSee('Click to select for your Clank heritage bonus (+3)');
    }

    #[Test]
    public function character_builder_does_not_show_clank_bonus_for_other_ancestries(): void
    {
        $character = $this->createTestCharacter();
        
        $component = Livewire::test(CharacterBuilder::class, ['characterKey' => $character->character_key])
            ->call('selectClass', 'warrior')
            ->call('selectAncestry', 'human')
            ->call('selectCommunity', 'highborne');

        $component->assertDontSee('As a Clank, you can select one experience for a +3 modifier (Purposeful Design)');
    }

    #[Test]
    public function character_builder_can_select_clank_bonus_experience(): void
    {
        $character = $this->createTestCharacter();
        
        $component = Livewire::test(CharacterBuilder::class, ['characterKey' => $character->character_key])
            ->call('selectClass', 'warrior')
            ->call('selectAncestry', 'clank')
            ->call('selectCommunity', 'wildborne')
            ->call('addExperience', 'Blacksmith', 'Working with metal and tools')
            ->call('selectClankBonusExperience', 'Blacksmith');

        // Check that the bonus experience was set
        $this->assertEquals('Blacksmith', $component->get('character.clank_bonus_experience'));
    }

    #[Test]
    public function character_builder_shows_enhanced_modifier_for_clank_bonus_experience(): void
    {
        $character = $this->createTestCharacter();
        
        $component = Livewire::test(CharacterBuilder::class, ['characterKey' => $character->character_key])
            ->call('selectClass', 'warrior')
            ->call('selectAncestry', 'clank')
            ->call('selectCommunity', 'wildborne')
            ->call('addExperience', 'Blacksmith', 'Working with metal and tools')
            ->call('addExperience', 'Silver Tongue', 'Persuasive speaking')
            ->call('selectClankBonusExperience', 'Blacksmith');

        // The selected experience should show +3, the other should show +2
        $component->assertSee('Clank Bonus');
        $component->assertSee('includes +1 from Clank Purposeful Design');
    }

    #[Test]
    public function character_builder_non_clank_cannot_select_bonus_experience(): void
    {
        $character = $this->createTestCharacter();
        
        $component = Livewire::test(CharacterBuilder::class, ['characterKey' => $character->character_key])
            ->call('selectClass', 'warrior')
            ->call('selectAncestry', 'human')
            ->call('selectCommunity', 'highborne')
            ->call('addExperience', 'Blacksmith', 'Working with metal and tools')
            ->call('selectClankBonusExperience', 'Blacksmith');

        // Non-clank should not have the bonus set
        $this->assertNull($component->get('character.clank_bonus_experience'));
    }

    #[Test]
    public function character_builder_clank_bonus_persists_through_save_and_load(): void
    {
        $character = $this->createTestCharacter();
        
        // Set up character with Clank bonus
        $component = Livewire::test(CharacterBuilder::class, ['characterKey' => $character->character_key])
            ->call('selectClass', 'warrior')
            ->call('selectAncestry', 'clank')
            ->call('selectCommunity', 'wildborne')
            ->call('addExperience', 'Blacksmith', 'Working with metal and tools')
            ->call('selectClankBonusExperience', 'Blacksmith');

        $character_key = $component->get('character.character_key');

        // Load in a new component instance
        $newComponent = Livewire::test(CharacterBuilder::class, ['characterKey' => $character_key]);

        // Verify the bonus experience is preserved
        $this->assertEquals('Blacksmith', $newComponent->get('character.clank_bonus_experience'));
        $this->assertEquals('clank', $newComponent->get('character.selected_ancestry'));
    }

    #[Test]
    public function character_builder_clank_experience_modifier_calculation(): void
    {
        $character = $this->createTestCharacter();
        
        $component = Livewire::test(CharacterBuilder::class, ['characterKey' => $character->character_key])
            ->call('selectClass', 'warrior')
            ->call('selectAncestry', 'clank')
            ->call('selectCommunity', 'wildborne')
            ->call('addExperience', 'Blacksmith', 'Working with metal and tools')
            ->call('addExperience', 'Silver Tongue', 'Persuasive speaking')
            ->call('selectClankBonusExperience', 'Blacksmith');

        $character_data = $component->get('character');
        
        // Test modifier calculation
        $this->assertEquals(3, $character_data->getExperienceModifier('Blacksmith'));
        $this->assertEquals(2, $character_data->getExperienceModifier('Silver Tongue'));
        $this->assertEquals(2, $character_data->getExperienceModifier('NonExistent'));
    }
}
