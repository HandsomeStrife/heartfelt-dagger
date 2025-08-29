<?php

declare(strict_types=1);

test('anonymous user can create and delete character through full browser flow', function () {
    $page = visit('/character-builder');
    
    $page->assertSee('Choose a Class')
         ->wait(2) // Wait for page to load
         
         // Step 1: Choose class
         ->click('[data-class="warrior"]')
         ->wait(1)
         ->assertSee('Warrior')
         ->assertSee('Blade')
         ->assertSee('Bone')
         ->click('[data-test="next-step"]')
         
         // Step 2: Choose ancestry
         ->wait(1)
         ->assertSee('Choose an Ancestry')
         ->click('[data-ancestry="human"]')
         ->wait(1)
         ->assertSee('Choose a Community')
         ->click('[data-community="wildborne"]')
         ->click('[data-test="next-step"]')
         
         // Step 3: Assign traits - set specific values
         ->wait(1)
         ->assertSee('Assign Character Traits')
         ->select('select[data-trait="agility"]', '0')
         ->select('select[data-trait="strength"]', '2')
         ->select('select[data-trait="finesse"]', '1')
         ->select('select[data-trait="instinct"]', '1')
         ->select('select[data-trait="presence"]', '0')
         ->select('select[data-trait="knowledge"]', '-1')
         ->click('[data-test="next-step"]')
         
         // Step 4: Record character info (automatic)
         ->wait(1)
         ->assertSee('Character Information')
         ->click('[data-test="next-step"]')
         
         // Step 5: Choose equipment
         ->wait(1)
         ->assertSee('Choose Starting Equipment')
         ->click('[data-test="next-step"]')
         
         // Step 6: Background
         ->wait(1)
         ->assertSee('Create Background')
         ->type('textarea[data-question="0"]', 'Test background answer 1')
         ->type('textarea[data-question="1"]', 'Test background answer 2')
         ->type('textarea[data-question="2"]', 'Test background answer 3')
         ->click('[data-test="next-step"]')
         
         // Step 7: Experiences
         ->wait(1)
         ->assertSee('Create Experiences')
         ->type('input[data-experience="0"]', 'Combat Training')
         ->type('input[data-experience="1"]', 'Wilderness Survival')
         ->click('[data-test="next-step"]')
         
         // Step 8: Domain cards
         ->wait(1)
         ->assertSee('Choose Domain Cards')
         ->click('[data-domain-card="0"]') // First available card
         ->click('[data-domain-card="1"]') // Second available card
         ->click('[data-test="next-step"]')
         
         // Step 9: Connections
         ->wait(1)
         ->assertSee('Create Connections')
         ->type('textarea[data-connection="0"]', 'Connection answer 1')
         ->type('textarea[data-connection="1"]', 'Connection answer 2')
         ->type('textarea[data-connection="2"]', 'Connection answer 3')
         ->click('[data-test="finish-character"]')
         
         // Wait for character creation to complete
         ->wait(3);
    
    // Now navigate to characters page and delete
    $page = visit('/characters');
    
    $page->assertSee('My Characters')
         ->wait(2) // Wait for characters to load
         ->assertPresent('.character-card')
         ->click('button[title="Delete Character"]')
         ->wait(1) // Wait for confirmation
         ->click('[data-test="confirm-delete"]') // Confirm deletion
         ->wait(2) // Wait for deletion to complete
         ->assertDontSee('.character-card');
});

test('anonymous user can create character and cancel deletion', function () {
    $page = visit('/character-builder');
    
    $page->assertSee('Choose a Class')
         ->wait(2)
         
         // Quickly create a minimal character
         ->click('[data-class="rogue"]')
         ->wait(1)
         ->assertSee('Rogue')
         ->click('[data-test="next-step"]')
         
         ->wait(1)
         ->assertSee('Choose an Ancestry')
         ->click('[data-ancestry="goblin"]')
         ->wait(1)
         ->assertSee('Choose a Community')
         ->click('[data-community="slyborne"]')
         ->click('[data-test="next-step"]')
         
         // Set required traits
         ->wait(1)
         ->assertSee('Assign Character Traits')
         ->select('select[data-trait="agility"]', '2')
         ->select('select[data-trait="strength"]', '0')
         ->select('select[data-trait="finesse"]', '1')
         ->select('select[data-trait="instinct"]', '1')
         ->select('select[data-trait="presence"]', '0')
         ->select('select[data-trait="knowledge"]', '-1')
         ->click('[data-test="next-step"]')
         
         // Skip through remaining steps quickly
         ->wait(1)
         ->assertSee('Character Information')
         ->click('[data-test="next-step"]')
         
         ->wait(1)
         ->assertSee('Choose Starting Equipment')
         ->click('[data-test="next-step"]')
         
         ->wait(1)
         ->assertSee('Create Background')
         ->type('textarea[data-question="0"]', 'Quick answer 1')
         ->type('textarea[data-question="1"]', 'Quick answer 2')
         ->type('textarea[data-question="2"]', 'Quick answer 3')
         ->click('[data-test="next-step"]')
         
         ->wait(1)
         ->assertSee('Create Experiences')
         ->type('input[data-experience="0"]', 'Stealth')
         ->type('input[data-experience="1"]', 'Lockpicking')
         ->click('[data-test="next-step"]')
         
         ->wait(1)
         ->assertSee('Choose Domain Cards')
         ->click('[data-domain-card="0"]')
         ->click('[data-domain-card="1"]')
         ->click('[data-test="next-step"]')
         
         ->wait(1)
         ->assertSee('Create Connections')
         ->type('textarea[data-connection="0"]', 'Connection 1')
         ->type('textarea[data-connection="1"]', 'Connection 2')
         ->type('textarea[data-connection="2"]', 'Connection 3')
         ->click('[data-test="finish-character"]')
         
         ->wait(3);
    
    // Navigate to characters and try to delete
    $page = visit('/characters');
    
    $page->assertSee('My Characters')
         ->wait(2)
         ->assertPresent('.character-card')
         ->click('button[title="Delete Character"]')
         ->wait(1)
         ->assertSee('Are you sure you want to delete this character')
         ->click('[data-test="cancel-delete"]') // Cancel deletion
         ->wait(1)
         ->assertPresent('.character-card'); // Character should still be visible
});

test('anonymous user character deletion removes character from view', function () {
    // Create an anonymous character directly in the database
    $character = \Domain\Character\Models\Character::factory()->create([
        'user_id' => null, // Anonymous character
        'name' => 'Test Anonymous Character',
        'class' => 'druid',
        'ancestry' => 'fungril',
        'community' => 'underborne',
    ]);
    
    // Test deletion via API (simulating what the frontend JavaScript would do)
    $response = $this->delete("/api/character/{$character->character_key}");
    
    $response->assertStatus(200)
             ->assertJson(['message' => 'Character deleted successfully']);
    
    // Verify character was deleted from database
    $this->assertDatabaseMissing('characters', ['id' => $character->id]);
});

test('anonymous user cannot delete character owned by authenticated user', function () {
    // Create a character owned by a user (simulating someone else's character)
    $user = \Domain\User\Models\User::factory()->create();
    $character = \Domain\Character\Models\Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Owned Character',
        'class' => 'wizard',
        'ancestry' => 'elf',
        'community' => 'loreborne',
    ]);
    
    // Test via direct API call without authentication
    $response = $this->delete("/api/character/{$character->character_key}");
    
    $response->assertStatus(403)
             ->assertJson(['error' => 'Unauthorized']);
    
    // Verify character still exists
    $this->assertDatabaseHas('characters', ['id' => $character->id]);
});
