<?php

declare(strict_types=1);

use Domain\Character\Models\Character;
use Domain\User\Models\User;

it('loads characters from database for authenticated users after deletion', function () {
    // Create a user and log them in
    $user = User::factory()->create();
    $this->actingAs($user);
    
    // Create characters owned by the user
    $character1 = Character::factory()->create(['user_id' => $user->id]);
    $character2 = Character::factory()->create(['user_id' => $user->id]);
    
    // Visit the character grid page
    $page = visit('/characters');
    $page->wait(2); // Wait for Livewire to load
    $page->assertSee($character1->name);
    $page->assertSee($character2->name);
    
    // Delete one character via API
    $this->delete("/api/character/{$character1->character_key}")
        ->assertStatus(200);
    
    // Refresh the page to simulate user refreshing after deletion
    $page = visit('/characters');
    $page->wait(2); // Wait for Livewire to load
    $page->assertDontSee($character1->name);
    $page->assertSee($character2->name);
});

it('loads characters from localStorage for guest users after deletion', function () {
    // Create characters without user_id (anonymous characters)
    $character1 = Character::factory()->create(['user_id' => null]);
    $character2 = Character::factory()->create(['user_id' => null]);
    
    // Visit page and set localStorage with both character keys 
    $page = visit('/characters');
    $page->script("
        localStorage.setItem('daggerheart_characters', JSON.stringify(['{$character1->character_key}', '{$character2->character_key}']));
        window.dispatchEvent(new CustomEvent('load-characters-from-storage'));
    ");
    $page->wait(2); // Wait for Livewire to load
    $page->assertSee($character1->name);
    $page->assertSee($character2->name);
    
    // Delete one character via API
    $this->delete("/api/character/{$character1->character_key}")
        ->assertStatus(200);
    
    // Update localStorage to remove the deleted character (simulating frontend behavior)
    $page->script("
        let keys = JSON.parse(localStorage.getItem('daggerheart_characters') || '[]');
        keys = keys.filter(key => key !== '{$character1->character_key}');
        localStorage.setItem('daggerheart_characters', JSON.stringify(keys));
    ");
    
    // Refresh the page and re-set localStorage (since refresh clears localStorage in test)
    $page = visit('/characters');
    $page->script("
        localStorage.setItem('daggerheart_characters', JSON.stringify(['{$character2->character_key}']));
        window.dispatchEvent(new CustomEvent('load-characters-from-storage'));
    ");
    $page->wait(2); // Wait for Livewire to load
    $page->assertDontSee($character1->name);
    $page->assertSee($character2->name);
});

it('does not mix localStorage and database characters for authenticated users', function () {
    // Create a user and log them in
    $user = User::factory()->create();
    $this->actingAs($user);
    
    // Create a character owned by the user
    $ownedCharacter = Character::factory()->create(['user_id' => $user->id]);
    
    // Create an anonymous character
    $anonymousCharacter = Character::factory()->create(['user_id' => null]);
    
    // Set localStorage with the anonymous character key
    $page = visit('/characters');
    $page->script("
        localStorage.setItem('daggerheart_characters', JSON.stringify(['{$anonymousCharacter->character_key}']));
    ");
    $page->wait(2); // Wait for Livewire to load
    $page->assertSee($ownedCharacter->name);
    $page->assertDontSee($anonymousCharacter->name); // Should not see localStorage character
});
