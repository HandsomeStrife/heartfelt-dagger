<?php

declare(strict_types=1);
use App\Livewire\CharacterBuilder;
use Domain\Character\Models\Character;
use Domain\User\Models\User;
use function Pest\Livewire\livewire;
use function Pest\Laravel\assertDatabaseHas;
use PHPUnit\Framework\Attributes\Test;
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->character = Character::factory()->for($this->user)->create([
        'character_key' => 'test123456',
    ]);
});
it('persists class selection to database', function () {
    $component = livewire(CharacterBuilder::class, ['characterKey' => 'test123456']);

    // Select a class
    $component->call('selectClass', 'warrior');

    // Verify the class is set in the component
    $component->assertSet('character.selected_class', 'warrior');

    // Verify the class is persisted to the database
    assertDatabaseHas('characters', [
        'character_key' => 'test123456',
        'class' => 'warrior',
        'subclass' => null, // Should be reset when class changes
    ]);

    // Double-check by refreshing from database
    $this->character->refresh();
    expect($this->character->class)->toEqual('warrior');
    expect($this->character->subclass)->toBeNull();
});
it('persists subclass selection to database', function () {
    $component = livewire(CharacterBuilder::class, ['characterKey' => 'test123456']);

    // First select a class
    $component->call('selectClass', 'warrior');

    // Then select a subclass
    $component->call('selectSubclass', 'stalwart');

    // Verify both class and subclass are set in the component
    $component->assertSet('character.selected_class', 'warrior');
    $component->assertSet('character.selected_subclass', 'stalwart');

    // Verify both are persisted to the database
    assertDatabaseHas('characters', [
        'character_key' => 'test123456',
        'class' => 'warrior',
        'subclass' => 'stalwart',
    ]);

    // Double-check by refreshing from database
    $this->character->refresh();
    expect($this->character->class)->toEqual('warrior');
    expect($this->character->subclass)->toEqual('stalwart');
});
it('resets subclass in database when class changes', function () {
    $component = livewire(CharacterBuilder::class, ['characterKey' => 'test123456']);

    // Set initial class and subclass
    $component->call('selectClass', 'warrior');
    $component->call('selectSubclass', 'stalwart');

    // Verify initial state
    assertDatabaseHas('characters', [
        'character_key' => 'test123456',
        'class' => 'warrior',
        'subclass' => 'stalwart',
    ]);

    // Change to a different class
    $component->call('selectClass', 'wizard');

    // Verify class changed and subclass was reset in database
    assertDatabaseHas('characters', [
        'character_key' => 'test123456',
        'class' => 'wizard',
        'subclass' => null,
    ]);

    // Double-check by refreshing from database
    $this->character->refresh();
    expect($this->character->class)->toEqual('wizard');
    expect($this->character->subclass)->toBeNull();
});
it('handles null class selection', function () {
    $component = livewire(CharacterBuilder::class, ['characterKey' => 'test123456']);

    // First set a class
    $component->call('selectClass', 'warrior');
    assertDatabaseHas('characters', [
        'character_key' => 'test123456',
        'class' => 'warrior',
    ]);

    // Then clear the class selection
    $component->call('selectClass', null);

    // Verify class is cleared in database
    assertDatabaseHas('characters', [
        'character_key' => 'test123456',
        'class' => null,
        'subclass' => null,
    ]);

    // Double-check by refreshing from database
    $this->character->refresh();
    expect($this->character->class)->toBeNull();
    expect($this->character->subclass)->toBeNull();
});
