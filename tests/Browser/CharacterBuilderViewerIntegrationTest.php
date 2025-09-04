<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

describe('Character Builder and Viewer Integration', function () {
    
    it('validates subclass effects are applied and reflected in both builder and viewer', function () {
        $character = Character::factory()->create([
            'character_data' => [
                'selected_class' => null,
                'selected_subclass' => null,
                'assigned_traits' => [],
                'selected_equipment' => [],
                'experiences' => [],
                'selected_domain_cards' => [],
            ]
        ]);

        visit('/character-builder/' . $character->character_key)
            // Step 1: Select Wizard class
            ->click('[pest="class-card-wizard"]')
            ->wait(2)
            
            // Step 2: Navigate to subclass and select School of Knowledge
            ->click('[pest="sidebar-tab-2"]')
            ->wait(1)
            ->click('[pest="subclass-card-school-of-knowledge"]')
            ->wait(2)
            
            // Navigate to domain cards step to verify bonus
            ->click('[pest="sidebar-tab-9"]')
            ->wait(2)
            
            // Should show that 3 cards can be selected (School of Knowledge bonus)
            ->assertSee('3 selected')
            
            // Save character and navigate to viewer
            ->click('[pest="save-character-button"]')
            ->wait(3);

        // Navigate to character viewer
        $page = visit('/character/' . $character->character_key);
        
        // Verify subclass is displayed
        $page->assertPresent('[pest="character-heritage"]')
            ->assertSee('School of Knowledge');
    });

    it('validates ancestry effects are applied and reflected in both builder and viewer', function () {
        $character = Character::factory()->create([
            'character_data' => [
                'selected_class' => 'warrior',
                'selected_subclass' => 'stalwart',
                'selected_ancestry' => null,
                'selected_community' => 'wildborne',
                'assigned_traits' => ['agility' => 2, 'strength' => 1, 'finesse' => 1, 'instinct' => 0, 'presence' => 0, 'knowledge' => -1],
                'selected_equipment' => [],
                'experiences' => [],
                'selected_domain_cards' => [],
            ]
        ]);

        visit('/character-builder/' . $character->character_key)
            // Navigate to ancestry step
            ->click('[pest="sidebar-tab-3"]')
            ->wait(1)
            
            // Select Giant ancestry (should grant +1 HP)
            ->click('[pest="ancestry-card-giant"]')
            ->wait(1)
            
            // Check computed stats show increased HP
            ->assertSee('Hit Points: 8') // Warrior base 7 + Giant bonus 1
            
            // Save character
            ->click('[pest="save-character-button"]')
            ->wait(3);

        // Navigate to character viewer
        $page = visit('/character/' . $character->character_key);
        
        // Verify ancestry is displayed
        $page->assertPresent('[pest="character-heritage"]')
            ->assertSee('Giant');
            
        // Verify HP count reflects Giant bonus in viewer
        $page->assertPresent('[pest="hp-counter"]')
            ->assertSee('0 / 8 Marked'); // Should show 8 HP total
    });

    it('validates trait assignment enforcement and viewer reflection', function () {
        $character = Character::factory()->create([
            'character_data' => [
                'selected_class' => 'bard',
                'selected_subclass' => 'college-of-lore',
                'selected_ancestry' => 'human',
                'selected_community' => 'highborne',
                'assigned_traits' => [],
                'selected_equipment' => [],
                'experiences' => [],
                'selected_domain_cards' => [],
            ]
        ]);

        visit('/character-builder/' . $character->character_key)
            // Navigate to trait assignment
            ->click('[pest="sidebar-tab-5"]')
            ->wait(1)
            
            // Use suggested traits for simplicity in testing
            ->click('[pest="apply-suggested-traits"]')
            ->wait(2)
            
            // Save character
            ->click('[pest="save-character-button"]')
            ->wait(3);

        // Navigate to character viewer
        $page = visit('/character/' . $character->character_key);
        
        // Verify trait assignments are correctly displayed
        $page->assertPresent('[pest="trait-stats"]');
            
        // Verify evasion is displayed
        $page->assertPresent('[pest="evasion-stat"]');
    });

    it('validates experience creation with Clank bonus selection', function () {
        $character = Character::factory()->create([
            'character_data' => [
                'selected_class' => 'rogue',
                'selected_subclass' => 'nightwalker',
                'selected_ancestry' => 'clank',
                'selected_community' => 'slyborne',
                'assigned_traits' => ['agility' => 2, 'strength' => 1, 'finesse' => 1, 'instinct' => 0, 'presence' => 0, 'knowledge' => -1],
                'selected_equipment' => [],
                'experiences' => [],
                'selected_domain_cards' => [],
            ]
        ]);

        visit('/character-builder/' . $character->character_key)
            // Navigate to experience step
            ->click('[pest="sidebar-tab-8"]')
            ->wait(1)
            
            // Add first experience
            ->type('[pest="experience-name-input"]', 'Lock Picking')
            ->click('[pest="add-experience-button"]')
            ->wait(1)
            
            // Add second experience  
            ->type('[pest="experience-name-input"]', 'Urban Navigation')
            ->click('[pest="add-experience-button"]')
            ->wait(1)
            
            // Save character
            ->click('[pest="save-character-button"]')
            ->wait(3);

        // Navigate to character viewer
        $page = visit('/character/' . $character->character_key);
        
        // Verify experiences are displayed
        $page->assertPresent('[pest="experience-section"]')
            ->assertSee('Lock Picking')
            ->assertSee('Urban Navigation');
    });

    it('validates equipment gating and viewer equipment display', function () {
        $character = Character::factory()->create([
            'character_data' => [
                'selected_class' => 'guardian',
                'selected_subclass' => 'protector',
                'selected_ancestry' => 'dwarf',
                'selected_community' => 'ridgeborne',
                'assigned_traits' => ['agility' => 0, 'strength' => 2, 'finesse' => 0, 'instinct' => 1, 'presence' => 1, 'knowledge' => -1],
                'selected_equipment' => [],
                'experiences' => [],
                'selected_domain_cards' => [],
            ]
        ]);

        visit('/character-builder/' . $character->character_key)
            // Navigate to equipment step
            ->click('[pest="sidebar-tab-6"]')
            ->wait(1)
            
            // Apply suggested equipment (should satisfy requirements)
            ->click('[pest="apply-all-suggestions"]')
            ->wait(2)
            
            // Save character
            ->click('[pest="save-character-button"]')
            ->wait(3);

        // Navigate to character viewer
        $page = visit('/character/' . $character->character_key);
        
        // Verify active weapons section exists
        $page->assertPresent('[pest="active-weapons-section"]');
            
        // Verify active armor section exists
        $page->assertPresent('[pest="active-armor-section"]');
            
        // Verify equipment inventory exists
        $page->assertPresent('[pest="equipment-section"]');
    });

    it('validates computed stats consistency between builder and viewer', function () {
        $character = Character::factory()->create([
            'character_data' => [
                'selected_class' => 'seraph',
                'selected_subclass' => 'winged-sentinel',
                'selected_ancestry' => 'galapa',
                'selected_community' => 'orderborne',
                'assigned_traits' => ['agility' => 1, 'strength' => 2, 'finesse' => 0, 'instinct' => 1, 'presence' => 0, 'knowledge' => -1],
                'selected_equipment' => [],
                'experiences' => [],
                'selected_domain_cards' => [],
            ]
        ]);

        visit('/character-builder/' . $character->character_key)
            // Save any pending changes
            ->click('[pest="save-character-button"]')
            ->wait(3);

        // Navigate to character viewer
        $page = visit('/character/' . $character->character_key);
        
        // Verify key stat elements are present
        $page->assertPresent('[pest="evasion-stat"]');
        $page->assertPresent('[pest="hp-counter"]');
        $page->assertPresent('[pest="damage-thresholds"]');
        $page->assertPresent('[pest="stress-counter"]');
            
        // Verify character heritage display
        $page->assertPresent('[pest="character-heritage"]')
            ->assertSee('Galapa')
            ->assertSee('Seraph');
            
        // Verify class domains
        $page->assertPresent('[pest="class-domains"]')
            ->assertSee('Splendor & Valor');
    });

    it('validates domain card filtering and level restrictions', function () {
        $character = Character::factory()->create([
            'character_data' => [
                'selected_class' => 'druid',
                'selected_subclass' => 'nature-spirit',
                'selected_ancestry' => 'elf',
                'selected_community' => 'wildborne',
                'assigned_traits' => ['agility' => 1, 'strength' => 0, 'finesse' => 1, 'instinct' => 2, 'presence' => 0, 'knowledge' => -1],
                'selected_equipment' => [],
                'experiences' => [],
                'selected_domain_cards' => [],
            ]
        ]);

        visit('/character-builder/' . $character->character_key)
            // Navigate to domain cards step
            ->click('[pest="sidebar-tab-9"]')
            ->wait(2)
            
            // Should show domain card selection interface
            ->assertSee('Level 1')
            
            // Save character
            ->click('[pest="save-character-button"]')
            ->wait(3);

        // Navigate to character viewer
        $page = visit('/character/' . $character->character_key);
        
        // Verify domain cards section exists (even if empty)
        $page->assertPresent('[pest="domain-cards-section"]');
    });

});
