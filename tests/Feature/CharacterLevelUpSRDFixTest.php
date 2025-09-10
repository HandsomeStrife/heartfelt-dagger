<?php

use App\Livewire\CharacterLevelUp;
use Domain\Character\Models\Character;

use function Pest\Livewire\livewire;

test('level 2 has both experience creation and domain card selection', function () {
    $character = Character::factory()->create(['level' => 1]);

    livewire(CharacterLevelUp::class, [
        'characterKey' => $character->character_key,
        'canEdit' => true,
    ])
        ->assertSee('Create Your New Experience') // Tier achievement experience
        ->assertSee('Select Your Domain Card'); // Domain card selection
});

test('level 5 has experience creation and domain card selection', function () {
    $character = Character::factory()->create(['level' => 4]);

    livewire(CharacterLevelUp::class, [
        'characterKey' => $character->character_key,
        'canEdit' => true,
    ])
        ->assertSee('Level 5 Benefits (Tier 3 Entry)')
        ->assertSee('Create Your New Experience') // Should have experience creation interface
        ->assertSee('Select Your Domain Card'); // Should have domain card selection
});

test('level 8 has experience creation and domain card selection', function () {
    $character = Character::factory()->create(['level' => 7]);

    livewire(CharacterLevelUp::class, [
        'characterKey' => $character->character_key,
        'canEdit' => true,
    ])
        ->assertSee('Level 8 Benefits (Tier 4 Entry)')
        ->assertSee('Create Your New Experience') // Should have experience creation interface
        ->assertSee('Select Your Domain Card'); // Should have domain card selection
});

test('non-tier levels still have domain card selection', function () {
    $character = Character::factory()->create(['level' => 3]);

    livewire(CharacterLevelUp::class, [
        'characterKey' => $character->character_key,
        'canEdit' => true,
    ])
        ->assertSee('Select Your Domain Card'); // Should have domain card selection
});

test('character has proficiency field', function () {
    $character = Character::factory()->create(['level' => 1, 'proficiency' => 0]);

    expect($character->proficiency)->toBe(0);

    // Test updating proficiency
    $character->update(['proficiency' => 2]);
    expect($character->fresh()->proficiency)->toBe(2);
});
