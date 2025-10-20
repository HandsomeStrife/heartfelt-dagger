<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

describe('Full Level 1 Character Creation - Complete Workflow', function () {
    
    test('warrior level 1 - full creation to save', function () {
        // Get count of characters before test
        $initialCharacterCount = Character::count();
        
        $page = visit('/character-builder');
        $page->assertPathBeginsWith('/character-builder/');
        
        // === STEP 1: CLASS SELECTION ===
        $page->assertSee('Choose a Class');
        $page->click('[pest="class-card-warrior"]');
        $page->wait(1);
        $page->assertSee('Warrior'); // Class name displayed
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // === STEP 2: SUBCLASS SELECTION ===
        $page->assertSee('Choose Your Subclass');
        $page->click('[pest="subclass-card-stalwart"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // === STEP 3: ANCESTRY SELECTION ===
        $page->assertSee('Choose Your Ancestry');
        $page->click('[pest="ancestry-card-human"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // === STEP 4: COMMUNITY SELECTION ===
        $page->assertSee('Choose Your Community');
        $page->click('[pest="community-card-wildborne"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // === STEP 5: TRAIT ASSIGNMENT ===
        $page->assertSee('Assign Traits');
        $page->click('[pest="trait-agility-plus-2"]');
        $page->wait(0.3);
        $page->click('[pest="trait-strength-plus-1"]');
        $page->wait(0.3);
        $page->click('[pest="trait-finesse-zero"]');
        $page->wait(0.3);
        $page->click('[pest="trait-instinct-plus-1"]');
        $page->wait(0.3);
        $page->click('[pest="trait-presence-minus-1"]');
        $page->wait(0.3);
        $page->click('[pest="trait-knowledge-zero"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // === STEP 6: EQUIPMENT SELECTION ===
        $page->assertSee('Select Equipment');
        // Use Apply All Warrior Suggestions
        $page->click('[pest="apply-all-warrior-suggestions"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // === STEP 7: BACKGROUND ===
        $page->assertSee('Create Background');
        $page->type('[pest="background-question-0"]', 'I trained under the legendary warrior Greymar');
        $page->wait(0.5);
        $page->click('[pest="mark-done-button"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // === STEP 8: EXPERIENCES ===
        $page->assertSee('Add Experiences');
        // Add first experience
        $page->type('[pest="experience-name-input"]', 'Battle-Hardened Veteran');
        $page->type('[pest="experience-description-input"]', 'Survived countless battles');
        $page->click('[pest="add-experience-button"]');
        $page->wait(1);
        
        // Add second experience
        $page->type('[pest="experience-name-input"]', 'Wilderness Tracker');
        $page->type('[pest="experience-description-input"]', 'Expert at tracking prey');
        $page->click('[pest="add-experience-button"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // === STEP 9: DOMAIN CARDS ===
        $page->assertSee('Select Domain Cards');
        $page->assertSee('2 / 2'); // Level 1 requires 2 cards
        
        // Select first domain card (Blade domain)
        $page->click('[pest="domain-card-blade-deft maneuvers"]');
        $page->wait(1);
        
        // Select second domain card (Bone domain)
        $page->click('[pest="domain-card-bone-whirlwind"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // === STEP 10: CONNECTIONS ===
        $page->assertSee('Create Connections');
        $page->type('[pest="connection-question-0"]', 'I saved their life in battle');
        $page->wait(0.5);
        $page->click('[pest="mark-done-button"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // === FINAL DETAILS & SAVE ===
        $page->assertSee('Final Details');
        $page->type('[pest="character-name-input"]', 'Test Warrior L1');
        $page->type('[pest="pronouns-input"]', 'he/him');
        $page->wait(0.5);
        
        // Save character
        $page->click('[pest="save-character-button"]');
        $page->wait(3); // Wait for save to complete
        
        // Should show success toast
        $page->assertSee('Character saved successfully');
        
        // === VERIFY IN DATABASE ===
        expect(Character::count())->toBe($initialCharacterCount + 1);
        
        $character = Character::where('name', 'Test Warrior L1')->latest()->first();
        expect($character)->not->toBeNull();
        expect($character->level)->toBe(1);
        expect($character->character_data['selected_class'])->toBe('warrior');
        expect($character->character_data['selected_subclass'])->toBe('stalwart');
        expect($character->character_data['selected_ancestry'])->toBe('human');
        expect($character->character_data['selected_community'])->toBe('wildborne');
    });
    
    test('wizard level 1 - different domains', function () {
        $initialCharacterCount = Character::count();
        
        $page = visit('/character-builder');
        $page->assertPathBeginsWith('/character-builder/');
        
        // Quick workflow through steps
        // Step 1: Class
        $page->click('[pest="class-card-wizard"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // Step 2: Subclass
        $page->click('[pest="subclass-card-scholar of secrets"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // Step 3: Ancestry
        $page->click('[pest="ancestry-card-elf"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // Step 4: Community
        $page->click('[pest="community-card-loreborne"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // Step 5: Traits
        $page->click('[pest="trait-agility-zero"]');
        $page->wait(0.3);
        $page->click('[pest="trait-strength-minus-1"]');
        $page->wait(0.3);
        $page->click('[pest="trait-finesse-plus-1"]');
        $page->wait(0.3);
        $page->click('[pest="trait-instinct-zero"]');
        $page->wait(0.3);
        $page->click('[pest="trait-presence-plus-1"]');
        $page->wait(0.3);
        $page->click('[pest="trait-knowledge-plus-2"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // Step 6: Equipment - Apply Wizard Suggestions
        $page->click('[pest="apply-all-wizard-suggestions"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // Step 7: Background
        $page->type('[pest="background-question-0"]', 'I studied at the Arcane Academy');
        $page->wait(0.5);
        $page->click('[pest="mark-done-button"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // Step 8: Experiences
        $page->type('[pest="experience-name-input"]', 'Arcane Scholar');
        $page->click('[pest="add-experience-button"]');
        $page->wait(1);
        $page->type('[pest="experience-name-input"]', 'Ancient Lore');
        $page->click('[pest="add-experience-button"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // Step 9: Domain Cards - Wizard has Codex + Midnight domains
        $page->assertSee('Select Domain Cards');
        // Select Codex card
        $page->click('[pest="domain-card-codex-book of ava"]');
        $page->wait(1);
        // Select Midnight card
        $page->click('[pest="domain-card-midnight-shadow step"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // Step 10: Connections
        $page->type('[pest="connection-question-0"]', 'We studied together');
        $page->wait(0.5);
        $page->click('[pest="mark-done-button"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // Final Details & Save
        $page->type('[pest="character-name-input"]', 'Test Wizard L1');
        $page->type('[pest="pronouns-input"]', 'they/them');
        $page->wait(0.5);
        $page->click('[pest="save-character-button"]');
        $page->wait(3);
        
        $page->assertSee('Character saved successfully');
        
        // Verify in database
        expect(Character::count())->toBe($initialCharacterCount + 1);
        
        $character = Character::where('name', 'Test Wizard L1')->latest()->first();
        expect($character)->not->toBeNull();
        expect($character->character_data['selected_class'])->toBe('wizard');
    });
    
    test('seraph level 1 - splendor and valor domains', function () {
        $initialCharacterCount = Character::count();
        
        $page = visit('/character-builder');
        $page->assertPathBeginsWith('/character-builder/');
        
        // === Quick workflow ===
        // Step 1: Class
        $page->click('[pest="class-card-seraph"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // Step 2: Subclass
        $page->click('[pest="next-step-button"]'); // Use default subclass
        $page->wait(1);
        
        // Step 3: Ancestry
        $page->click('[pest="ancestry-card-drakona"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // Step 4: Community
        $page->click('[pest="community-card-orderborne"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // Step 5: Traits
        $page->click('[pest="trait-agility-zero"]');
        $page->wait(0.3);
        $page->click('[pest="trait-strength-plus-1"]');
        $page->wait(0.3);
        $page->click('[pest="trait-finesse-plus-1"]');
        $page->wait(0.3);
        $page->click('[pest="trait-instinct-zero"]');
        $page->wait(0.3);
        $page->click('[pest="trait-presence-plus-2"]');
        $page->wait(0.3);
        $page->click('[pest="trait-knowledge-minus-1"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // Step 6: Equipment
        $page->click('[pest="apply-all-seraph-suggestions"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // Step 7: Background
        $page->type('[pest="background-question-0"]', 'Called by divine purpose');
        $page->wait(0.5);
        $page->click('[pest="mark-done-button"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // Step 8: Experiences
        $page->type('[pest="experience-name-input"]', 'Divine Protector');
        $page->click('[pest="add-experience-button"]');
        $page->wait(1);
        $page->type('[pest="experience-name-input"]', 'Inspiring Leader');
        $page->click('[pest="add-experience-button"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // Step 9: Domain Cards - Seraph has Splendor + Valor
        // Select Splendor card
        $page->click('[pest="domain-card-splendor-radiant ward"]');
        $page->wait(1);
        // Select Valor card
        $page->click('[pest="domain-card-valor-rally cry"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // Step 10: Connections
        $page->type('[pest="connection-question-0"]', 'I protect them');
        $page->wait(0.5);
        $page->click('[pest="mark-done-button"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // Final Details & Save
        $page->type('[pest="character-name-input"]', 'Test Seraph L1');
        $page->type('[pest="pronouns-input"]', 'she/her');
        $page->wait(0.5);
        $page->click('[pest="save-character-button"]');
        $page->wait(3);
        
        $page->assertSee('Character saved successfully');
        
        // Verify
        expect(Character::count())->toBe($initialCharacterCount + 1);
        
        $character = Character::where('name', 'Test Seraph L1')->latest()->first();
        expect($character)->not->toBeNull();
        expect($character->character_data['selected_class'])->toBe('seraph');
    });
});

