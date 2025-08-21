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
            'character_key' => 'PRON123456',
        ]);
    }

    #[Test]
    public function it_can_set_and_retrieve_pronouns_via_direct_property(): void
    {
        $component = Livewire::test(CharacterBuilder::class, ['characterKey' => 'PRON123456']);

        // Test different pronoun values using the direct pronouns property
        $pronouns = ['he/him', 'she/her', 'they/them', 'xe/xem', 'custom pronouns'];
        
        foreach ($pronouns as $pronoun) {
            $component->set('pronouns', $pronoun);
            $retrieved = $component->get('pronouns');
            
            $this->assertEquals($pronoun, $retrieved, "Failed to preserve pronoun in property: {$pronoun}");
            
            // Trigger a save operation to persist to database
            $component->call('updatePronouns', $pronoun);
            
            // Verify it's saved to the database
            $character = Character::where('character_key', 'PRON123456')->first();
            $this->assertEquals($pronoun, $character->pronouns, "Failed to save pronoun to database: {$pronoun}");
        }
    }

    #[Test]
    public function it_preserves_pronouns_during_operations(): void
    {
        $component = Livewire::test(CharacterBuilder::class, ['characterKey' => 'PRON123456']);

        // Set pronouns using the direct property
        $component->set('pronouns', 'they/them');
        
        // Verify it's set initially
        $this->assertEquals('they/them', $component->get('pronouns'));
        
        // Test that pronouns are preserved when setting other character properties
        $component->set('character.name', 'Test Character');
        $this->assertEquals('they/them', $component->get('pronouns'), 'Pronouns property lost after setting name');
        
        $component->set('character.selected_ancestry', 'human');
        $this->assertEquals('they/them', $component->get('pronouns'), 'Pronouns property lost after setting ancestry');
        
        // Test that pronouns are preserved when calling component methods
        $component->call('selectAncestry', 'elf');
        $this->assertEquals('they/them', $component->get('pronouns'), 'Pronouns property lost after selectAncestry call');
        
        // Verify it's still saved in the database
        $character = Character::where('character_key', 'PRON123456')->first();
        $this->assertEquals('they/them', $character->pronouns, 'Pronouns not preserved in database after operations');
    }
}
