<?php

declare(strict_types=1);

use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterAdvancement;

describe('Full Level 5 Character Creation - Complete Workflow with Advancements', function () {
    
    test('warrior level 5 - full creation with all advancements', function () {
        $initialCharacterCount = Character::count();
        
        $page = visit('/character-builder');
        $page->assertPathBeginsWith('/character-builder/');
        
        // === STEP 1: CLASS SELECTION ===
        $page->assertSee('Choose a Class');
        $page->click('[pest="class-card-warrior"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // === STEP 2: SUBCLASS SELECTION ===
        $page->assertSee('Choose Your Subclass');
        $page->click('[pest="subclass-card-call of the slayer"]');
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
        
        // === STEP 5: TRAITS & LEVEL SELECTION ===
        $page->assertSee('Assign Traits');
        
        // Select Level 5
        $page->assertPresent('[pest="level-selector"]');
        $page->click('[pest="level-option-5"]');
        $page->wait(1);
        
        // Advancement panel should appear
        $page->assertSee('Character Advancement');
        $page->assertSee('Level 2');
        $page->assertSee('Level 3');
        $page->assertSee('Level 4');
        $page->assertSee('Level 5');
        
        // Assign base traits
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
        
        // === MAKE ADVANCEMENT SELECTIONS ===
        // Level 2 Advancements
        $page->select('[pest="level-2-advancement-1"]', 'hit_point');
        $page->wait(0.5);
        $page->select('[pest="level-2-advancement-2"]', 'trait_bonus');
        $page->wait(1);
        
        // Level 2 Trait Bonus - Select Agility & Strength
        $page->assertSee('Level 2 - Tier 2');
        $page->click('[pest="trait-bonus-level-2-agility"]');
        $page->wait(0.3);
        $page->click('[pest="trait-bonus-level-2-strength"]');
        $page->wait(1);
        
        // Level 3 Advancements
        $page->select('[pest="level-3-advancement-1"]', 'stress_slot');
        $page->wait(0.5);
        $page->select('[pest="level-3-advancement-2"]', 'evasion');
        $page->wait(1);
        
        // Level 4 Advancements
        $page->select('[pest="level-4-advancement-1"]', 'hit_point');
        $page->wait(0.5);
        $page->select('[pest="level-4-advancement-2"]', 'experience_bonus');
        $page->wait(1);
        
        // Level 5 Advancements
        $page->select('[pest="level-5-advancement-1"]', 'domain_card');
        $page->wait(0.5);
        $page->select('[pest="level-5-advancement-2"]', 'trait_bonus');
        $page->wait(1);
        
        // Level 5 Trait Bonus - Select Finesse & Instinct (unmarked traits in Tier 3)
        $page->assertSee('Level 5 - Tier 3');
        $page->click('[pest="trait-bonus-level-5-finesse"]');
        $page->wait(0.3);
        $page->click('[pest="trait-bonus-level-5-instinct"]');
        $page->wait(1);
        
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // === STEP 6: EQUIPMENT ===
        $page->assertSee('Select Equipment');
        $page->click('[pest="apply-all-warrior-suggestions"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // === STEP 7: BACKGROUND ===
        $page->assertSee('Create Background');
        $page->type('[pest="background-question-0"]', 'My mentor, Greymar the Bold, taught me to wield a blade');
        $page->wait(0.5);
        $page->click('[pest="mark-done-button"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // === STEP 8: EXPERIENCES ===
        $page->assertSee('Add Experiences');
        
        // Add 2 starting experiences
        $page->type('[pest="experience-name-input"]', 'Battle-Hardened Veteran');
        $page->type('[pest="experience-description-input"]', 'Survived countless battles');
        $page->click('[pest="add-experience-button"]');
        $page->wait(1);
        
        $page->type('[pest="experience-name-input"]', 'Wilderness Tracker');
        $page->type('[pest="experience-description-input"]', 'Expert at tracking prey');
        $page->click('[pest="add-experience-button"]');
        $page->wait(1);
        
        // Tier Achievement Experiences
        $page->assertSee('Level 2 - Tier 2 Achievement');
        $page->type('[pest="tier-experience-level-2-name"]', 'Tactician');
        $page->type('[pest="tier-experience-level-2-description"]', 'Strategic combat expert');
        $page->wait(0.5);
        
        $page->assertSee('Level 5 - Tier 3 Achievement');
        $page->type('[pest="tier-experience-level-5-name"]', 'Fearless Leader');
        $page->type('[pest="tier-experience-level-5-description"]', 'Inspires allies in battle');
        $page->wait(0.5);
        
        // Level 4 Experience Bonus - Select 2 experiences
        $page->assertSee('Level 4 - Experience Bonus');
        $page->click('[pest="experience-bonus-level-4-battle-hardened veteran"]');
        $page->wait(0.3);
        $page->click('[pest="experience-bonus-level-4-wilderness tracker"]');
        $page->wait(1);
        
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // === STEP 9: DOMAIN CARDS ===
        $page->assertSee('Select Domain Cards');
        $page->assertSee('6 / 6'); // 2 starting + 4 level cards (2,3,4,5)
        
        // Level 1 cards (2 required)
        $page->click('[pest="domain-card-level-1-blade-deft maneuvers"]');
        $page->wait(1);
        $page->click('[pest="domain-card-level-1-bone-whirlwind"]');
        $page->wait(1);
        
        // Level 2 card
        $page->click('[pest="domain-card-level-2-blade-blade flurry"]');
        $page->wait(1);
        
        // Level 3 card
        $page->click('[pest="domain-card-level-3-bone-bone armor"]');
        $page->wait(1);
        
        // Level 4 card
        $page->click('[pest="domain-card-level-4-blade-blade storm"]');
        $page->wait(1);
        
        // Level 5 card
        $page->click('[pest="domain-card-level-5-bone-bone shield"]');
        $page->wait(1);
        
        // Bonus card from Level 5 "Additional Domain Card" advancement
        $page->assertSee('Tier 3 Advancement Level 5');
        $page->click('[pest="domain-card-advancement-level-5-blade-piercing strike"]');
        $page->wait(1);
        
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // === STEP 10: CONNECTIONS ===
        $page->assertSee('Create Connections');
        $page->type('[pest="connection-question-0"]', 'I saved their life in a desperate battle');
        $page->wait(0.5);
        $page->click('[pest="mark-done-button"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // === FINAL DETAILS & SAVE ===
        $page->assertSee('Final Details');
        $page->type('[pest="character-name-input"]', 'Test Warrior L5');
        $page->type('[pest="pronouns-input"]', 'he/him');
        $page->wait(0.5);
        
        // Save character
        $page->click('[pest="save-character-button"]');
        $page->wait(5); // Wait longer for advancement save
        
        // Should show success toast
        $page->assertSee('Character saved successfully');
        
        // === VERIFY IN DATABASE ===
        expect(Character::count())->toBe($initialCharacterCount + 1);
        
        $character = Character::where('name', 'Test Warrior L5')->latest()->first();
        expect($character)->not->toBeNull();
        expect($character->level)->toBe(5);
        expect($character->character_data['selected_class'])->toBe('warrior');
        
        // Verify advancements saved
        $advancements = CharacterAdvancement::where('character_id', $character->id)->get();
        expect($advancements->count())->toBeGreaterThan(0);
        
        // Verify Level 2 advancements
        $level2Advs = $advancements->where('level', 2);
        expect($level2Advs->count())->toBeGreaterThanOrEqual(2); // At least 2 regular advancements
        
        // Verify Level 5 advancements
        $level5Advs = $advancements->where('level', 5);
        expect($level5Advs->count())->toBeGreaterThanOrEqual(2);
        
        // === VERIFY IN CHARACTER VIEWER ===
        $viewerUrl = "/character/{$character->character_key}";
        $viewerPage = visit($viewerUrl);
        $viewerPage->wait(2);
        
        // Verify character details display
        $viewerPage->assertSee('Test Warrior L5');
        $viewerPage->assertSee('Level 5');
        $viewerPage->assertSee('Warrior');
        
        // Verify trait bonuses are applied
        $viewerPage->assertSee('Agility'); // Should show +3 (base +2, bonus +1)
        $viewerPage->assertSee('Strength'); // Should show +2 (base +1, bonus +1)
        $viewerPage->assertSee('Finesse'); // Should show +1 (base 0, bonus +1)
        $viewerPage->assertSee('Instinct'); // Should show +2 (base +1, bonus +1)
    });
    
    test('ranger level 5 - sage and bone domains', function () {
        $initialCharacterCount = Character::count();
        
        $page = visit('/character-builder');
        $page->assertPathBeginsWith('/character-builder/');
        
        // === Quick workflow through setup ===
        // Step 1: Class
        $page->click('[pest="class-card-ranger"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // Step 2: Subclass
        $page->click('[pest="next-step-button"]'); // Use default
        $page->wait(1);
        
        // Step 3: Ancestry
        $page->click('[pest="ancestry-card-elf"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // Step 4: Community
        $page->click('[pest="community-card-wildborne"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // Step 5: Traits & Advancements
        // Select Level 5
        $page->click('[pest="level-option-5"]');
        $page->wait(1);
        
        // Assign traits
        $page->click('[pest="trait-agility-plus-2"]');
        $page->wait(0.3);
        $page->click('[pest="trait-strength-zero"]');
        $page->wait(0.3);
        $page->click('[pest="trait-finesse-plus-1"]');
        $page->wait(0.3);
        $page->click('[pest="trait-instinct-plus-1"]');
        $page->wait(0.3);
        $page->click('[pest="trait-presence-zero"]');
        $page->wait(0.3);
        $page->click('[pest="trait-knowledge-minus-1"]');
        $page->wait(1);
        
        // Make advancement selections
        $page->select('[pest="level-2-advancement-1"]', 'evasion');
        $page->wait(0.5);
        $page->select('[pest="level-2-advancement-2"]', 'stress_slot');
        $page->wait(0.5);
        
        $page->select('[pest="level-3-advancement-1"]', 'hit_point');
        $page->wait(0.5);
        $page->select('[pest="level-3-advancement-2"]', 'trait_bonus');
        $page->wait(0.5);
        $page->click('[pest="trait-bonus-level-3-agility"]');
        $page->wait(0.3);
        $page->click('[pest="trait-bonus-level-3-finesse"]');
        $page->wait(0.5);
        
        $page->select('[pest="level-4-advancement-1"]', 'hit_point');
        $page->wait(0.5);
        $page->select('[pest="level-4-advancement-2"]', 'evasion');
        $page->wait(0.5);
        
        $page->select('[pest="level-5-advancement-1"]', 'stress_slot');
        $page->wait(0.5);
        $page->select('[pest="level-5-advancement-2"]', 'trait_bonus');
        $page->wait(0.5);
        $page->click('[pest="trait-bonus-level-5-strength"]');
        $page->wait(0.3);
        $page->click('[pest="trait-bonus-level-5-instinct"]');
        $page->wait(1);
        
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // Step 6: Equipment
        $page->click('[pest="apply-all-ranger-suggestions"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // Step 7: Background
        $page->type('[pest="background-question-0"]', 'I roamed the wild lands for years');
        $page->wait(0.5);
        $page->click('[pest="mark-done-button"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // Step 8: Experiences
        $page->type('[pest="experience-name-input"]', 'Wilderness Survival');
        $page->click('[pest="add-experience-button"]');
        $page->wait(1);
        $page->type('[pest="experience-name-input"]', 'Animal Companion');
        $page->click('[pest="add-experience-button"]');
        $page->wait(1);
        
        $page->type('[pest="tier-experience-level-2-name"]', 'Natural Healer');
        $page->wait(0.5);
        $page->type('[pest="tier-experience-level-5-name"]', 'Forest Guardian');
        $page->wait(1);
        
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // Step 9: Domain Cards - Ranger has Sage + Bone
        // Select 2 Level 1 cards
        $page->click('[pest="domain-card-level-1-sage-natures blessing"]');
        $page->wait(1);
        $page->click('[pest="domain-card-level-1-bone-death mark"]');
        $page->wait(1);
        
        // Level 2-5 cards
        $page->click('[pest="domain-card-level-2-sage-healing touch"]');
        $page->wait(1);
        $page->click('[pest="domain-card-level-3-bone-bone spear"]');
        $page->wait(1);
        $page->click('[pest="domain-card-level-4-sage-rejuvenation"]');
        $page->wait(1);
        $page->click('[pest="domain-card-level-5-bone-death grip"]');
        $page->wait(1);
        
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // Step 10: Connections
        $page->type('[pest="connection-question-0"]', 'We traveled together');
        $page->wait(0.5);
        $page->click('[pest="mark-done-button"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // Final Details & Save
        $page->type('[pest="character-name-input"]', 'Test Ranger L5');
        $page->type('[pest="pronouns-input"]', 'she/her');
        $page->wait(0.5);
        $page->click('[pest="save-character-button"]');
        $page->wait(5);
        
        $page->assertSee('Character saved successfully');
        
        // Verify
        expect(Character::count())->toBe($initialCharacterCount + 1);
        
        $character = Character::where('name', 'Test Ranger L5')->latest()->first();
        expect($character)->not->toBeNull();
        expect($character->level)->toBe(5);
        expect($character->character_data['selected_class'])->toBe('ranger');
        
        // Verify advancements
        $advancements = CharacterAdvancement::where('character_id', $character->id)->get();
        expect($advancements->count())->toBeGreaterThan(0);
    });
    
    test('sorcerer level 5 - arcana and midnight domains', function () {
        $initialCharacterCount = Character::count();
        
        $page = visit('/character-builder');
        $page->assertPathBeginsWith('/character-builder/');
        
        // === Quick workflow ===
        $page->click('[pest="class-card-sorcerer"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        $page->click('[pest="next-step-button"]'); // Subclass
        $page->wait(1);
        
        $page->click('[pest="ancestry-card-drakona"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        $page->click('[pest="community-card-slyborne"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // Traits & Level
        $page->click('[pest="level-option-5"]');
        $page->wait(1);
        
        $page->click('[pest="trait-agility-zero"]');
        $page->wait(0.3);
        $page->click('[pest="trait-strength-minus-1"]');
        $page->wait(0.3);
        $page->click('[pest="trait-finesse-plus-1"]');
        $page->wait(0.3);
        $page->click('[pest="trait-instinct-plus-1"]');
        $page->wait(0.3);
        $page->click('[pest="trait-presence-plus-2"]');
        $page->wait(0.3);
        $page->click('[pest="trait-knowledge-zero"]');
        $page->wait(1);
        
        // Advancements - quick selections
        $page->select('[pest="level-2-advancement-1"]', 'hit_point');
        $page->wait(0.3);
        $page->select('[pest="level-2-advancement-2"]', 'hit_point');
        $page->wait(0.5);
        
        $page->select('[pest="level-3-advancement-1"]', 'stress_slot');
        $page->wait(0.3);
        $page->select('[pest="level-3-advancement-2"]', 'stress_slot');
        $page->wait(0.5);
        
        $page->select('[pest="level-4-advancement-1"]', 'evasion');
        $page->wait(0.3);
        $page->select('[pest="level-4-advancement-2"]', 'evasion');
        $page->wait(0.5);
        
        $page->select('[pest="level-5-advancement-1"]', 'hit_point');
        $page->wait(0.3);
        $page->select('[pest="level-5-advancement-2"]', 'stress_slot');
        $page->wait(1);
        
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // Equipment
        $page->click('[pest="apply-all-sorcerer-suggestions"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // Background
        $page->type('[pest="background-question-0"]', 'Magic flows through my veins');
        $page->wait(0.5);
        $page->click('[pest="mark-done-button"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // Experiences
        $page->type('[pest="experience-name-input"]', 'Wild Magic');
        $page->click('[pest="add-experience-button"]');
        $page->wait(1);
        $page->type('[pest="experience-name-input"]', 'Arcane Intuition');
        $page->click('[pest="add-experience-button"]');
        $page->wait(1);
        
        $page->type('[pest="tier-experience-level-2-name"]', 'Power Unleashed');
        $page->wait(0.3);
        $page->type('[pest="tier-experience-level-5-name"]', 'Master of Elements');
        $page->wait(1);
        
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // Domain Cards - Sorcerer has Arcana + Midnight
        $page->click('[pest="domain-card-level-1-arcana-arcane bolt"]');
        $page->wait(1);
        $page->click('[pest="domain-card-level-1-midnight-shadow veil"]');
        $page->wait(1);
        $page->click('[pest="domain-card-level-2-arcana-elemental blast"]');
        $page->wait(1);
        $page->click('[pest="domain-card-level-3-midnight-vanish"]');
        $page->wait(1);
        $page->click('[pest="domain-card-level-4-arcana-mana surge"]');
        $page->wait(1);
        $page->click('[pest="domain-card-level-5-midnight-shadow form"]');
        $page->wait(1);
        
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // Connections
        $page->type('[pest="connection-question-0"]', 'They fear my power');
        $page->wait(0.5);
        $page->click('[pest="mark-done-button"]');
        $page->wait(1);
        $page->click('[pest="next-step-button"]');
        $page->wait(1);
        
        // Save
        $page->type('[pest="character-name-input"]', 'Test Sorcerer L5');
        $page->type('[pest="pronouns-input"]', 'they/them');
        $page->wait(0.5);
        $page->click('[pest="save-character-button"]');
        $page->wait(5);
        
        $page->assertSee('Character saved successfully');
        
        // Verify
        expect(Character::count())->toBe($initialCharacterCount + 1);
        
        $character = Character::where('name', 'Test Sorcerer L5')->latest()->first();
        expect($character)->not->toBeNull();
        expect($character->name)->toBe('Test Sorcerer L5');
        expect($character->character_data['selected_class'])->toBe('sorcerer');
        
        $advancements = CharacterAdvancement::where('character_id', $character->id)->get();
        expect($advancements->count())->toBeGreaterThan(0);
    });
});

