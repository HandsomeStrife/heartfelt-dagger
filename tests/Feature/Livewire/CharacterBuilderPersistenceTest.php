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

class CharacterBuilderPersistenceTest extends TestCase
{
    use RefreshDatabase;

    private Character $character;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->character = Character::factory()->for($this->user)->create([
            'character_key' => 'test123456',
        ]);
    }

    #[Test]
    public function it_persists_class_selection_to_database(): void
    {
        $component = Livewire::test(CharacterBuilder::class, ['characterKey' => 'test123456']);

        // Select a class
        $component->call('selectClass', 'warrior');

        // Verify the class is set in the component
        $component->assertSet('character.selected_class', 'warrior');

        // Verify the class is persisted to the database
        $this->assertDatabaseHas('characters', [
            'character_key' => 'test123456',
            'class' => 'warrior',
            'subclass' => null, // Should be reset when class changes
        ]);

        // Double-check by refreshing from database
        $this->character->refresh();
        $this->assertEquals('warrior', $this->character->class);
        $this->assertNull($this->character->subclass);
    }

    #[Test]
    public function it_persists_subclass_selection_to_database(): void
    {
        $component = Livewire::test(CharacterBuilder::class, ['characterKey' => 'test123456']);

        // First select a class
        $component->call('selectClass', 'warrior');
        
        // Then select a subclass
        $component->call('selectSubclass', 'stalwart');

        // Verify both class and subclass are set in the component
        $component->assertSet('character.selected_class', 'warrior');
        $component->assertSet('character.selected_subclass', 'stalwart');

        // Verify both are persisted to the database
        $this->assertDatabaseHas('characters', [
            'character_key' => 'test123456',
            'class' => 'warrior',
            'subclass' => 'stalwart',
        ]);

        // Double-check by refreshing from database
        $this->character->refresh();
        $this->assertEquals('warrior', $this->character->class);
        $this->assertEquals('stalwart', $this->character->subclass);
    }

    #[Test]
    public function it_resets_subclass_in_database_when_class_changes(): void
    {
        $component = Livewire::test(CharacterBuilder::class, ['characterKey' => 'test123456']);

        // Set initial class and subclass
        $component->call('selectClass', 'warrior');
        $component->call('selectSubclass', 'stalwart');

        // Verify initial state
        $this->assertDatabaseHas('characters', [
            'character_key' => 'test123456',
            'class' => 'warrior',
            'subclass' => 'stalwart',
        ]);

        // Change to a different class
        $component->call('selectClass', 'wizard');

        // Verify class changed and subclass was reset in database
        $this->assertDatabaseHas('characters', [
            'character_key' => 'test123456',
            'class' => 'wizard',
            'subclass' => null,
        ]);

        // Double-check by refreshing from database
        $this->character->refresh();
        $this->assertEquals('wizard', $this->character->class);
        $this->assertNull($this->character->subclass);
    }

    #[Test]
    public function it_handles_null_class_selection(): void
    {
        $component = Livewire::test(CharacterBuilder::class, ['characterKey' => 'test123456']);

        // First set a class
        $component->call('selectClass', 'warrior');
        $this->assertDatabaseHas('characters', [
            'character_key' => 'test123456',
            'class' => 'warrior',
        ]);

        // Then clear the class selection
        $component->call('selectClass', null);

        // Verify class is cleared in database
        $this->assertDatabaseHas('characters', [
            'character_key' => 'test123456',
            'class' => null,
            'subclass' => null,
        ]);

        // Double-check by refreshing from database
        $this->character->refresh();
        $this->assertNull($this->character->class);
        $this->assertNull($this->character->subclass);
    }
}
