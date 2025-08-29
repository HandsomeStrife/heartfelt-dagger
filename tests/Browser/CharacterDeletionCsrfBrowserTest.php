<?php

declare(strict_types=1);

test('character deletion works in testing environment without CSRF', function () {
    // Create an anonymous character 
    $character = \Domain\Character\Models\Character::factory()->create([
        'user_id' => null,
        'name' => 'Test Character',
        'class' => 'warrior',
        'ancestry' => 'human',
        'community' => 'wildborne',
    ]);
    
    // In Laravel's testing environment, CSRF middleware is bypassed
    // This test verifies the authorization logic works correctly
    $response = $this->withHeaders([
        'X-Requested-With' => 'XMLHttpRequest',
        'Content-Type' => 'application/json'
        // No CSRF token needed in testing
    ])->delete("/api/character/{$character->character_key}");
    
    // Should succeed (CSRF bypassed in testing, authorization allows anonymous deletion)
    $response->assertStatus(200)
             ->assertJson(['message' => 'Character deleted successfully']);
    
    // Character should be deleted
    $this->assertDatabaseMissing('characters', ['id' => $character->id]);
});

test('character deletion succeeds when CSRF token is included', function () {
    // Create an anonymous character
    $character = \Domain\Character\Models\Character::factory()->create([
        'user_id' => null,
        'name' => 'Test Character Fixed',
        'class' => 'wizard',
        'ancestry' => 'elf',
        'community' => 'loreborne',
    ]);
    
    // Test via direct API call with CSRF token - this should work
    $response = $this->withHeaders([
        'X-Requested-With' => 'XMLHttpRequest',
        'Content-Type' => 'application/json',
        'X-CSRF-TOKEN' => csrf_token() // Include CSRF token
    ])->delete("/api/character/{$character->character_key}");
    
    // Should succeed with 200 status
    $response->assertStatus(200)
             ->assertJson(['message' => 'Character deleted successfully']);
    
    // Character should be deleted
    $this->assertDatabaseMissing('characters', ['id' => $character->id]);
});

test('actual user deletion flow works end-to-end', function () {
    // Create an anonymous character
    $character = \Domain\Character\Models\Character::factory()->create([
        'user_id' => null,
        'name' => 'E2E Test Character',
        'class' => 'rogue',
        'ancestry' => 'goblin',
        'community' => 'slyborne',
    ]);
    
    $page = visit('/characters');
    
    // Simulate the character being in localStorage
    $page->script("
        localStorage.setItem('daggerheart_characters', JSON.stringify(['{$character->character_key}']));
        location.reload();
    ");
    
    $page->wait(2)
         ->assertSee('E2E Test Character');
    
    // Override confirm to auto-accept and trigger actual deletion flow
    $page->script("
        window.confirm = () => true;
        // Find delete button by looking for text content 'Delete'
        const buttons = Array.from(document.querySelectorAll('button'));
        const deleteBtn = buttons.find(btn => btn.textContent.trim() === 'Delete');
        if (deleteBtn) {
            deleteBtn.click();
        }
    ");
    
    $page->wait(3); // Wait for deletion to complete
    
    // Character should be deleted from database
    $this->assertDatabaseMissing('characters', ['id' => $character->id]);
});
