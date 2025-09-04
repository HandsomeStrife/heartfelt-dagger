<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

describe('Domain Card Filtering and Selection Validation', function () {
    
    it('validates only class domains are shown in domain card selection', function () {
        $classesData = json_decode(file_get_contents(resource_path('json/classes.json')), true);
        $domainsData = json_decode(file_get_contents(resource_path('json/domains.json')), true);
        
        // Test multiple classes to ensure domain filtering works
        $testClasses = ['wizard', 'druid', 'warrior', 'rogue'];
        
        foreach ($testClasses as $classKey) {
            if (!isset($classesData[$classKey])) {
                continue;
            }
            
            $character = Character::factory()->create([
                'character_data' => [
                    'selected_class' => $classKey,
                    'selected_subclass' => array_keys($classesData[$classKey]['subclasses'])[0],
                    'selected_ancestry' => 'human',
                    'selected_community' => 'wildborne',
                    'assigned_traits' => ['agility' => 1, 'strength' => 1, 'finesse' => 0, 'instinct' => 2, 'presence' => 0, 'knowledge' => -1],
                    'selected_equipment' => [],
                    'experiences' => [],
                    'selected_domain_cards' => [],
                ]
            ]);

            $page = visit('/character-builder/' . $character->character_key);
            
            // Navigate to domain cards step
            $page->click('[pest="sidebar-tab-9"]')->wait(1);
            $page->assertSee('Select Domain Cards');
            
            $classDomains = $classesData[$classKey]['domains'];
            
            // Verify only class domains have cards shown
            foreach ($classDomains as $domain) {
                $page->assertPresent("[pest*=\"domain-card-{$domain}-\"]");
            }
            
            // Verify non-class domains do not have cards shown
            $allDomains = array_keys($domainsData);
            $nonClassDomains = array_diff($allDomains, $classDomains);
            
            foreach (array_slice($nonClassDomains, 0, 2) as $nonClassDomain) {
                // Should not see cards from domains not belonging to this class
                $nonClassCards = $page->elements("[pest*=\"domain-card-{$nonClassDomain}-\"]");
                expect(count($nonClassCards))->toBe(0, "Class {$classKey} should not show {$nonClassDomain} domain cards");
            }
        }
    });

    it('validates only level 1 cards are shown for starting characters', function () {
        $character = Character::factory()->create([
            'character_data' => [
                'selected_class' => 'wizard',
                'selected_subclass' => 'school-of-knowledge',
                'selected_ancestry' => 'human',
                'selected_community' => 'loreborne',
                'assigned_traits' => ['agility' => 0, 'strength' => 0, 'finesse' => 1, 'instinct' => 1, 'presence' => -1, 'knowledge' => 2],
                'selected_equipment' => [],
                'experiences' => [],
                'selected_domain_cards' => [],
            ]
        ]);

        $page = visit('/character-builder/' . $character->character_key);
        
        // Navigate to domain cards step
        $page->click('[pest="sidebar-tab-9"]')->wait(1);
        $page->assertSee('Select Domain Cards');
        
        // All visible cards should be level 1
        $page->assertSee('Lvl 1');
        $page->assertDontSee('Lvl 2');
        $page->assertDontSee('Lvl 3');
        $page->assertDontSee('Lvl 4');
        $page->assertDontSee('Lvl 5');
        
        // Verify level 1 cards from class domains are present
        $domainsData = json_decode(file_get_contents(resource_path('json/domains.json')), true);
        $wizardDomains = ['codex', 'splendor'];
        
        foreach ($wizardDomains as $domain) {
            $level1Cards = $domainsData[$domain]['abilitiesByLevel']['1']['abilities'] ?? [];
            
            foreach (array_slice($level1Cards, 0, 2) as $cardKey) {
                $page->assertPresent("[pest=\"domain-card-{$domain}-{$cardKey}\"]");
            }
        }
    });

    it('validates standard 2 card selection limit', function () {
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

        $page = visit('/character-builder/' . $character->character_key);
        
        // Navigate to domain cards step
        $page->click('[pest="sidebar-tab-9"]')->wait(1);
        $page->assertSee('Select Domain Cards');
        
        // Should show 0 selected initially
        $page->assertSee('0', '[pest="domain-card-selected-count"]');
        
        // Select first card
        $sageCards = $page->elements('[pest*="domain-card-sage-"]');
        if (count($sageCards) > 0) {
            $page->click($sageCards[0])->wait(0.5);
            $page->assertSee('1', '[pest="domain-card-selected-count"]');
        }
        
        // Select second card
        $arcanaCards = $page->elements('[pest*="domain-card-arcana-"]');
        if (count($arcanaCards) > 0) {
            $page->click($arcanaCards[0])->wait(0.5);
            $page->assertSee('2', '[pest="domain-card-selected-count"]');
        }
        
        // Try to select third card - should replace one of the existing
        if (count($sageCards) > 1) {
            $page->click($sageCards[1])->wait(0.5);
            // Should still show maximum of 2 selected
            $page->assertSee('2', '[pest="domain-card-selected-count"]');
        }
        
        // Should show completion
        $page->assertSee('Domain Card Selection Complete!');
    });

    it('validates School of Knowledge 3 card selection limit', function () {
        $character = Character::factory()->create([
            'character_data' => [
                'selected_class' => 'wizard',
                'selected_subclass' => 'school-of-knowledge',
                'selected_ancestry' => 'human',
                'selected_community' => 'loreborne',
                'assigned_traits' => ['agility' => 0, 'strength' => 0, 'finesse' => 1, 'instinct' => 1, 'presence' => -1, 'knowledge' => 2],
                'selected_equipment' => [],
                'experiences' => [],
                'selected_domain_cards' => [],
            ]
        ]);

        $page = visit('/character-builder/' . $character->character_key);
        
        // Navigate to domain cards step
        $page->click('[pest="sidebar-tab-9"]')->wait(1);
        $page->assertSee('Select Domain Cards');
        
        // Should show 3 as max count due to School of Knowledge bonus
        $page->assertSee('3', '[pest="domain-card-max-count"]');
        
        // Select three cards
        $codexCards = $page->elements('[pest*="domain-card-codex-"]');
        $splendorCards = $page->elements('[pest*="domain-card-splendor-"]');
        
        if (count($codexCards) >= 2 && count($splendorCards) >= 1) {
            $page->click($codexCards[0])->wait(0.5);
            $page->assertSee('1', '[pest="domain-card-selected-count"]');
            
            $page->click($codexCards[1])->wait(0.5);
            $page->assertSee('2', '[pest="domain-card-selected-count"]');
            
            $page->click($splendorCards[0])->wait(0.5);
            $page->assertSee('3', '[pest="domain-card-selected-count"]');
            
            // Should show completion with 3 cards
            $page->assertSee('Domain Card Selection Complete!');
        }
    });

    it('validates domain card deselection works correctly', function () {
        $character = Character::factory()->create([
            'character_data' => [
                'selected_class' => 'sorcerer',
                'selected_subclass' => 'elemental-chaos',
                'selected_ancestry' => 'drakona',
                'selected_community' => 'slyborne',
                'assigned_traits' => ['agility' => 1, 'strength' => 0, 'finesse' => 1, 'instinct' => 2, 'presence' => 0, 'knowledge' => -1],
                'selected_equipment' => [],
                'experiences' => [],
                'selected_domain_cards' => [],
            ]
        ]);

        $page = visit('/character-builder/' . $character->character_key);
        
        // Navigate to domain cards step
        $page->click('[pest="sidebar-tab-9"]')->wait(1);
        $page->assertSee('Select Domain Cards');
        
        // Select a card
        $arcanaCards = $page->elements('[pest*="domain-card-arcana-"]');
        if (count($arcanaCards) > 0) {
            $firstCard = $arcanaCards[0];
            $page->click($firstCard)->wait(0.5);
            $page->assertSee('1', '[pest="domain-card-selected-count"]');
            
            // Deselect the same card
            $page->click($firstCard)->wait(0.5);
            $page->assertSee('0', '[pest="domain-card-selected-count"]');
            
            // Should not be complete with no cards
            $page->assertDontSee('Domain Card Selection Complete!');
        }
    });

    it('validates selected domain cards display correctly in character viewer', function () {
        $character = Character::factory()->create([
            'character_data' => [
                'selected_class' => 'guardian',
                'selected_subclass' => 'protector',
                'selected_ancestry' => 'dwarf',
                'selected_community' => 'ridgeborne',
                'assigned_traits' => ['agility' => 0, 'strength' => 2, 'finesse' => -1, 'instinct' => 1, 'presence' => 1, 'knowledge' => 0],
                'selected_equipment' => [],
                'experiences' => [],
                'selected_domain_cards' => ['rally', 'get-back-up'], // Sample guardian domain cards
            ]
        ]);

        $page = visit('/character/' . $character->character_key);
        
        // Verify domain cards section is present
        $page->assertPresent('[pest="domain-cards-section"]');
        
        // Should show the selected cards
        $page->assertPresent('[pest="domain-card-0"]');
        $page->assertPresent('[pest="domain-card-1"]');
        
        // Should show card details
        $page->assertPresent('[pest="domain-card-name"]');
        $page->assertPresent('[pest="domain-card-type"]');
        $page->assertPresent('[pest="domain-card-description"]');
        $page->assertPresent('[pest="domain-card-domain"]');
        
        // Should show recall costs
        $abilitiesData = json_decode(file_get_contents(resource_path('json/abilities.json')), true);
        foreach (['rally', 'get-back-up'] as $cardKey) {
            if (isset($abilitiesData[$cardKey])) {
                $recallCost = $abilitiesData[$cardKey]['recallCost'];
                if ($recallCost > 0) {
                    $page->assertSee((string)$recallCost);
                }
            }
        }
    });

    it('validates ability metadata consistency between builder and viewer', function () {
        $character = Character::factory()->create([
            'character_data' => [
                'selected_class' => 'seraph',
                'selected_subclass' => 'winged-sentinel',
                'selected_ancestry' => 'fairy',
                'selected_community' => 'highborne',
                'assigned_traits' => ['agility' => 1, 'strength' => 0, 'finesse' => 0, 'instinct' => 1, 'presence' => 2, 'knowledge' => -1],
                'selected_equipment' => [],
                'experiences' => [],
                'selected_domain_cards' => ['radiant-weapon', 'rally'], // Sample seraph cards
            ]
        ]);

        $abilitiesData = json_decode(file_get_contents(resource_path('json/abilities.json')), true);
        
        $page = visit('/character/' . $character->character_key);
        
        // Check that ability data in viewer matches JSON
        foreach (['radiant-weapon', 'rally'] as $cardKey) {
            if (isset($abilitiesData[$cardKey])) {
                $ability = $abilitiesData[$cardKey];
                
                // Should show ability name
                $page->assertSee($ability['name']);
                
                // Should show ability type
                $page->assertSee($ability['type']);
                
                // Should show recall cost (if > 0)
                if ($ability['recallCost'] > 0) {
                    $page->assertSee((string)$ability['recallCost']);
                }
                
                // Should show at least part of description
                if (!empty($ability['descriptions'])) {
                    $firstDescription = $ability['descriptions'][0];
                    $firstWords = explode(' ', $firstDescription);
                    if (count($firstWords) > 2) {
                        $searchPhrase = implode(' ', array_slice($firstWords, 0, 3));
                        $page->assertSee($searchPhrase);
                    }
                }
            }
        }
    });

    it('validates playtest domain cards show playtest labels', function () {
        // Look for a class that has access to Dread domain (playtest)
        $classesData = json_decode(file_get_contents(resource_path('json/classes.json')), true);
        
        $dreadClass = null;
        foreach ($classesData as $classKey => $classData) {
            if (in_array('dread', $classData['domains'])) {
                $dreadClass = $classKey;
                break;
            }
        }
        
        if (!$dreadClass) {
            $this->markTestSkipped('No class found with Dread domain access');
        }
        
        $character = Character::factory()->create([
            'character_data' => [
                'selected_class' => $dreadClass,
                'selected_subclass' => array_keys($classesData[$dreadClass]['subclasses'])[0],
                'selected_ancestry' => 'human',
                'selected_community' => 'wildborne',
                'assigned_traits' => ['agility' => 1, 'strength' => 0, 'finesse' => 1, 'instinct' => 2, 'presence' => 0, 'knowledge' => -1],
                'selected_equipment' => [],
                'experiences' => [],
                'selected_domain_cards' => [],
            ]
        ]);

        $page = visit('/character-builder/' . $character->character_key);
        
        // Navigate to domain cards step
        $page->click('[pest="sidebar-tab-9"]')->wait(1);
        $page->assertSee('Select Domain Cards');
        
        // Should see playtest labels on Dread domain cards
        $page->assertSee('Void - Playtest');
        
        // Select a dread card and save
        $dreadCards = $page->elements('[pest*="domain-card-dread-"]');
        if (count($dreadCards) > 0) {
            $page->click($dreadCards[0])->wait(0.5);
            
            // Add one more card to complete selection
            $otherDomainCards = $page->elements('[pest*="domain-card-"]:not([pest*="domain-card-dread-"])');
            if (count($otherDomainCards) > 0) {
                $page->click($otherDomainCards[0])->wait(0.5);
            }
            
            $page->click('[pest="save-character-button"]')->wait(3);
            
            // Verify playtest label appears in viewer
            $viewerPage = visit('/character/' . $character->character_key);
            $viewerPage->assertSee('Void - Playtest');
        }
    });
});
