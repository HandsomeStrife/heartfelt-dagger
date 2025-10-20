<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

describe('Simple Character Creation Workflow', function () {
    
    test('warrior level 1 - basic workflow verification', function () {
        $initialCharacterCount = Character::count();
        
        $page = visit('/character-builder');
        $page->assertPathBeginsWith('/character-builder/');
        $page->wait(2); // Wait for initial load
        
        // Step 1: Class Selection
        $page->assertSee('Choose a Class');
        $page->assertPresent('[pest="class-card-warrior"]');
        $page->click('[pest="class-card-warrior"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // Step 2: Subclass - Just click next without selecting (should use default)
        $page->assertSee('Subclass');
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // Step 3: Ancestry
        $page->assertSee('Ancestry');
        $page->assertPresent('[pest="ancestry-card-human"]');
        $page->click('[pest="ancestry-card-human"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // Step 4: Community
        $page->assertSee('Community');
        $page->click('[pest="community-card-wildborne"]'); // Click specific community
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // Step 5: Traits - Use suggestions
        $page->assertSee('Traits');
        $page->wait(2); // Wait for traits to load
        $page->assertPresent('[pest="apply-suggested-traits"]');
        $page->click('[pest="apply-suggested-traits"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // Step 6: Equipment - Just continue (will be incomplete but that's okay for this test)
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // Step 7: Background - Skip
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // Step 8: Experiences - Skip
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // Step 9: Domain Cards - Skip
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // Step 10: Connections - Skip
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // Final step - add name
        $page->assertPresent('[pest="character-name-input"]');
        $page->type('[pest="character-name-input"]', 'Test Warrior Simple');
        $page->wait(0.5);
        
        // Save
        $page->assertPresent('[pest="save-character-button"]');
        $page->click('[pest="save-character-button"]');
        $page->wait(5);
        
        // Verify character was created
        expect(Character::count())->toBe($initialCharacterCount + 1);
        
        $character = Character::where('name', 'Test Warrior Simple')->latest()->first();
        expect($character)->not->toBeNull();
        expect($character->character_data['selected_class'])->toBe('warrior');
    });
});

