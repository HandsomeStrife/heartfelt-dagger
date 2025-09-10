<?php

declare(strict_types=1);
use App\Livewire\CharacterBuilder;
use Domain\Character\Models\Character;
use Domain\User\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $character = Character::factory()->for($this->user)->create([
        'character_key' => 'test123456',
    ]);
});
it('resets all data except heritage when class changes', function () {

    actingAs($this->user);
    // Setup initial character data with all fields populated
    $component = livewire(CharacterBuilder::class, ['characterKey' => 'test123456']);

    // Set initial data
    $component->set('character.name', 'Test Hero');
    $component->set('character.profile_image_path', '/test/image.jpg');
    $component->set('character.selected_ancestry', 'human');
    $component->set('character.selected_community', 'wildborne');
    $component->set('character.assigned_traits', ['agility' => 2, 'strength' => 1]);
    $component->set('character.selected_equipment', [
        ['key' => 'longsword', 'type' => 'weapon', 'data' => ['name' => 'Longsword']],
    ]);
    $component->set('character.background_answers', [0 => 'Test background answer']);
    $component->set('character.experiences', [
        ['name' => 'Test Experience', 'description' => 'Test Description', 'modifier' => 2],
    ]);
    $component->set('character.selected_domain_cards', [
        ['domain' => 'blade', 'ability_key' => 'blade-strike', 'ability_level' => 1, 'ability_data' => []],
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
});
it('resets all data except heritage and class when subclass changes', function () {
    actingAs($this->user);
    // Setup initial character data with all fields populated
    $component = livewire(CharacterBuilder::class, ['characterKey' => 'test123456']);

    // Set initial data
    $component->set('character.name', 'Test Hero');
    $component->set('character.profile_image_path', '/test/image.jpg');
    $component->set('character.selected_class', 'warrior');
    $component->set('character.selected_ancestry', 'human');
    $component->set('character.selected_community', 'wildborne');
    $component->set('character.assigned_traits', ['agility' => 2, 'strength' => 1]);
    $component->set('character.selected_equipment', [
        ['key' => 'longsword', 'type' => 'weapon', 'data' => ['name' => 'Longsword']],
    ]);
    $component->set('character.background_answers', [0 => 'Test background answer']);
    $component->set('character.experiences', [
        ['name' => 'Test Experience', 'description' => 'Test Description', 'modifier' => 2],
    ]);
    $component->set('character.selected_domain_cards', [
        ['domain' => 'blade', 'ability_key' => 'blade-strike', 'ability_level' => 1, 'ability_data' => []],
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
});
it('preserves empty values correctly', function () {
    $component = livewire(CharacterBuilder::class, ['characterKey' => 'test123456']);

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
});
