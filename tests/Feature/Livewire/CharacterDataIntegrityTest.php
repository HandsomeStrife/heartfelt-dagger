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

class CharacterDataIntegrityTest extends TestCase
{
    use RefreshDatabase;

    private Character $character;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->character = Character::factory()->for($this->user)->create([
            'character_key' => 'INTEG123',
        ]);
    }

    #[Test]
    public function it_preserves_all_character_fields_when_setting_individual_properties(): void
    {
        $component = Livewire::test(CharacterBuilder::class, ['characterKey' => 'INTEG123']);

        // Set multiple properties including using the pronouns workaround
        $component->set('character.name', 'Test Hero');
        $component->set('character_pronouns', 'they/them'); // Use workaround property
        $component->set('character.selected_ancestry', 'human');
        $component->set('character.selected_community', 'wildborne');
        $component->set('character.profile_image_path', '/test/image.jpg');

        // Verify all are set initially (note: character.pronouns may be null due to Livewire issue)
        $this->assertEquals('Test Hero', $component->get('character.name'));
        $this->assertEquals('they/them', $component->get('character_pronouns'));
        $this->assertEquals('human', $component->get('character.selected_ancestry'));
        $this->assertEquals('wildborne', $component->get('character.selected_community'));
        $this->assertEquals('/test/image.jpg', $component->get('character.profile_image_path'));

        // Set one more property and verify others are preserved
        $component->set('character.selected_class', 'warrior');

        // Check if all properties are still preserved
        $this->assertEquals('Test Hero', $component->get('character.name'));
        $this->assertEquals('they/them', $component->get('character_pronouns'), 'Pronouns workaround property lost when setting class');
        $this->assertEquals('human', $component->get('character.selected_ancestry'));
        $this->assertEquals('wildborne', $component->get('character.selected_community'));
        $this->assertEquals('/test/image.jpg', $component->get('character.profile_image_path'));
        $this->assertEquals('warrior', $component->get('character.selected_class'));
    }

    #[Test]
    public function it_preserves_pronouns_during_class_reset_operations(): void
    {
        $component = Livewire::test(CharacterBuilder::class, ['characterKey' => 'INTEG123']);

        // Set up character with pronouns and other data
        $component->set('character.name', 'Test Hero');
        $component->set('character_pronouns', 'they/them');
        $component->set('character.selected_ancestry', 'human');
        $component->set('character.selected_community', 'wildborne');
        $component->set('character.assigned_traits', ['agility' => 2, 'strength' => 1]);

        // Verify initial state (note: character.pronouns may be null due to Livewire issue)
        $this->assertEquals('they/them', $component->get('character_pronouns'));
        $this->assertEquals(['agility' => 2, 'strength' => 1], $component->get('character.assigned_traits'));

        // Trigger class change which should reset traits but preserve pronouns
        $component->call('selectClass', 'warrior');

        // Verify pronouns are preserved and synced properly after the method call
        $this->assertEquals('they/them', $component->get('character_pronouns'), 'Pronouns lost during class reset');
        $this->assertEquals('they/them', $component->get('character.pronouns'), 'Character pronouns not synced after class reset');
        $this->assertEquals('Test Hero', $component->get('character.name'), 'Name lost during class reset');
        $this->assertEquals('human', $component->get('character.selected_ancestry'), 'Ancestry lost during class reset');
        $this->assertEquals('wildborne', $component->get('character.selected_community'), 'Community lost during class reset');
        $this->assertEquals([], $component->get('character.assigned_traits'), 'Traits not reset during class change');
        $this->assertEquals('warrior', $component->get('character.selected_class'), 'Class not set correctly');
    }
}
