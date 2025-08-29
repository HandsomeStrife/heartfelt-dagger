<?php

declare(strict_types=1);

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = \Domain\User\Models\User::factory()->create();
});

test('authenticated user can delete their own character successfully', function () {
    // Create a character owned by the user
    $character = \Domain\Character\Models\Character::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Test Character',
        'class' => 'warrior',
        'ancestry' => 'human',
        'community' => 'wildborne',
    ]);
    
    $page = actingAs($this->user)->visit('/characters');
    
    $page->assertSee('Test Character')
         ->assertPresent('button:contains("Delete")')
         ->script("
             // Override confirm to always return true
             window.confirm = () => true;
             // Find and click the delete button
             document.querySelector('button:contains(\"Delete\")').click();
         ")
         ->wait(3) // Wait for deletion to complete
         ->assertDontSee('Test Character');
    
    // Verify character was deleted from database
    $this->assertDatabaseMissing('characters', ['id' => $character->id]);
});

test('authenticated user cannot delete character owned by another user via API', function () {
    $otherUser = \Domain\User\Models\User::factory()->create();
    
    // Create a character owned by another user
    $character = \Domain\Character\Models\Character::factory()->create([
        'user_id' => $otherUser->id,
        'name' => 'Other User Character',
        'class' => 'wizard',
        'ancestry' => 'elf',
        'community' => 'loreborne',
    ]);
    
    // Try to delete via API directly
    $response = actingAs($this->user)->delete("/api/character/{$character->character_key}");
    
    $response->assertStatus(403);
    
    // Verify character still exists in database
    $this->assertDatabaseHas('characters', ['id' => $character->id]);
});

test('authenticated user only sees their own characters on characters page', function () {
    $otherUser = \Domain\User\Models\User::factory()->create();
    
    // Create a character owned by the user
    $ownCharacter = \Domain\Character\Models\Character::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'My Character',
        'class' => 'guardian',
        'ancestry' => 'dwarf',
        'community' => 'ridgeborne',
    ]);
    
    // Create a character owned by another user
    $otherCharacter = \Domain\Character\Models\Character::factory()->create([
        'user_id' => $otherUser->id,
        'name' => 'Other Character',
        'class' => 'bard',
        'ancestry' => 'fairy',
        'community' => 'highborne',
    ]);
    
    actingAs($this->user)
        ->visit('/characters')
        ->assertSee('My Character')
        ->assertPresent('button[title="Delete Character"]')
        ->assertDontSee('Other Character'); // Should not see other user's characters
});

test('character deletion confirmation dialog can be cancelled', function () {
    $character = \Domain\Character\Models\Character::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Delete Test Character',
        'class' => 'sorcerer',
        'ancestry' => 'drakona',
        'community' => 'seaborne',
    ]);
    
    actingAs($this->user)
        ->visit('/characters')
        ->assertSee('Delete Test Character')
        ->click('button[title="Delete Character"]')
        ->wait(1)
        ->assertSee('Are you sure you want to delete this character')
        ->assertSee('This action cannot be undone')
        ->click('[data-test="cancel-delete"]')
        ->wait(1)
        ->assertSee('Delete Test Character'); // Character should still be visible
    
    // Verify character was not deleted
    $this->assertDatabaseHas('characters', ['id' => $character->id]);
});
