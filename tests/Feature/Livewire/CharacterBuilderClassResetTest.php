<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\CharacterBuilder;
use Domain\Character\Data\CharacterBuilderData;
use Domain\Character\Models\Character;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CharacterBuilderClassResetTest extends TestCase
{
    use RefreshDatabase;

    private Character $character;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->character = Character::factory()->for($this->user)->create([
            'character_key' => 'test1234',
        ]);
    }

    #[Test]
    public function it_resets_all_data_except_heritage_when_class_changes(): void
    {
        // Setup initial character data with all fields populated
        $component = Livewire::test(CharacterBuilder::class, ['characterKey' => 'test1234']);

        // Set initial data
        $component->set('character.name', 'Test Hero');
        $component->set('character.profile_image_path', '/test/image.jpg');
        $component->set('character.selected_ancestry', 'human');
        $component->set('character.selected_community', 'wildborne');
        $component->set('character.assigned_traits', ['agility' => 2, 'strength' => 1]);
        $component->set('character.selected_equipment', [
            ['key' => 'longsword', 'type' => 'weapon', 'data' => ['name' => 'Longsword']]
        ]);
        $component->set('character.background_answers', [0 => 'Test background answer']);
        $component->set('character.experiences', [
            ['name' => 'Test Experience', 'description' => 'Test Description', 'modifier' => 2]
        ]);
        $component->set('character.selected_domain_cards', [
            ['domain' => 'blade', 'ability_key' => 'blade-strike', 'ability_level' => 1, 'ability_data' => []]
        ]);
        $component->set('character.connection_answers', [0 => 'Test connection answer']);

        // Change class to warrior
        $component->call('selectClass', 'warrior');

        // Assert heritage, name, and profile image are preserved
        $component->assertSet('character.name', 'Test Hero');
        $component->assertSet('character.profile_image_path', '/test/image.jpg');
        $component->assertSet('character.selected_ancestry', 'human');
        $component->assertSet('character.selected_community', 'wildborne');

        // Assert class is set correctly
        $component->assertSet('character.selected_class', 'warrior');
        $component->assertSet('character.selected_subclass', null);

        // Assert other data is reset
        $component->assertSet('character.assigned_traits', []);
        $component->assertSet('character.selected_equipment', []);
        $component->assertSet('character.background_answers', []);
        $component->assertSet('character.experiences', []);
        $component->assertSet('character.selected_domain_cards', []);
        $component->assertSet('character.connection_answers', []);
    }

    #[Test]
    public function it_resets_all_data_except_heritage_and_class_when_subclass_changes(): void
    {
        // Setup initial character data with all fields populated
        $component = Livewire::test(CharacterBuilder::class, ['characterKey' => 'test1234']);

        // Set initial data
        $component->set('character.name', 'Test Hero');
        $component->set('character.profile_image_path', '/test/image.jpg');
        $component->set('character.selected_class', 'warrior');
        $component->set('character.selected_ancestry', 'human');
        $component->set('character.selected_community', 'wildborne');
        $component->set('character.assigned_traits', ['agility' => 2, 'strength' => 1]);
        $component->set('character.selected_equipment', [
            ['key' => 'longsword', 'type' => 'weapon', 'data' => ['name' => 'Longsword']]
        ]);
        $component->set('character.background_answers', [0 => 'Test background answer']);
        $component->set('character.experiences', [
            ['name' => 'Test Experience', 'description' => 'Test Description', 'modifier' => 2]
        ]);
        $component->set('character.selected_domain_cards', [
            ['domain' => 'blade', 'ability_key' => 'blade-strike', 'ability_level' => 1, 'ability_data' => []]
        ]);
        $component->set('character.connection_answers', [0 => 'Test connection answer']);

        // Change subclass to stalwart
        $component->call('selectSubclass', 'stalwart');

        // Assert heritage, name, profile image, and class are preserved
        $component->assertSet('character.name', 'Test Hero');
        $component->assertSet('character.profile_image_path', '/test/image.jpg');
        $component->assertSet('character.selected_ancestry', 'human');
        $component->assertSet('character.selected_community', 'wildborne');
        $component->assertSet('character.selected_class', 'warrior');

        // Assert subclass is set correctly
        $component->assertSet('character.selected_subclass', 'stalwart');

        // Assert other data is reset
        $component->assertSet('character.assigned_traits', []);
        $component->assertSet('character.selected_equipment', []);
        $component->assertSet('character.background_answers', []);
        $component->assertSet('character.experiences', []);
        $component->assertSet('character.selected_domain_cards', []);
        $component->assertSet('character.connection_answers', []);
    }

    #[Test]
    public function it_preserves_empty_values_correctly(): void
    {
        $component = Livewire::test(CharacterBuilder::class, ['characterKey' => 'test1234']);

        // Set only some initial data (leaving others empty)
        $component->set('character.name', '');
        $component->set('character.profile_image_path', null);
        $component->set('character.selected_ancestry', null);
        $component->set('character.selected_community', null);

        // Change class
        $component->call('selectClass', 'warrior');

        // Assert empty values are preserved as empty
        $component->assertSet('character.name', '');
        $component->assertSet('character.profile_image_path', null);
        $component->assertSet('character.selected_ancestry', null);
        $component->assertSet('character.selected_community', null);
        $component->assertSet('character.selected_class', 'warrior');
    }
}
