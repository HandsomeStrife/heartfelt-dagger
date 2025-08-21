<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\CharacterBuilder;
use Domain\Character\Models\Character;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PronounsFieldTest extends TestCase
{
    use RefreshDatabase;

    private Character $character;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->character = Character::factory()->for($this->user)->create([
            'character_key' => 'PRON1234',
        ]);
    }

    #[Test]
    public function it_can_set_and_retrieve_pronouns_via_workaround_property(): void
    {
        $component = Livewire::test(CharacterBuilder::class, ['characterKey' => 'PRON1234']);

        // Test different pronoun values using the workaround property
        $pronouns = ['he/him', 'she/her', 'they/them', 'xe/xem', 'custom pronouns'];
        
        foreach ($pronouns as $pronoun) {
            $component->set('character_pronouns', $pronoun);
            $retrieved = $component->get('character_pronouns');
            $character_retrieved = $component->get('character.pronouns');
            
            $this->assertEquals($pronoun, $retrieved, "Failed to preserve pronoun in property: {$pronoun}");
            $this->assertEquals($pronoun, $character_retrieved, "Failed to sync pronoun to character: {$pronoun}");
        }
    }

    #[Test]
    public function it_preserves_pronouns_during_operations_with_workaround(): void
    {
        $component = Livewire::test(CharacterBuilder::class, ['characterKey' => 'PRON1234']);

        // Set pronouns using the workaround property
        $component->set('character_pronouns', 'they/them');
        
        // Verify it's set initially
        $this->assertEquals('they/them', $component->get('character_pronouns'));
        $this->assertEquals('they/them', $component->get('character.pronouns'));
        
        // Test that pronouns are preserved when setting other character properties
        $component->set('character.name', 'Test Character');
        $this->assertEquals('they/them', $component->get('character_pronouns'), 'Pronouns property lost after setting name');
        
        $component->set('character.selected_ancestry', 'human');
        $this->assertEquals('they/them', $component->get('character_pronouns'), 'Pronouns property lost after setting ancestry');
        
        // Test that pronouns are preserved and synced when calling component methods
        $component->call('selectAncestry', 'elf');
        $this->assertEquals('they/them', $component->get('character_pronouns'), 'Pronouns property lost after selectAncestry call');
        $this->assertEquals('they/them', $component->get('character.pronouns'), 'Character pronouns not synced after selectAncestry call');
    }
}
