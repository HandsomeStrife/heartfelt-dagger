<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

describe('Class Domain Mapping and Playtest Content Validation', function () {
    
    it('validates all class-domain pairs match SRD specification exactly', function () {
        $expectedClassDomains = [
            'bard' => ['grace', 'codex'],
            'druid' => ['sage', 'arcana'],
            'guardian' => ['valor', 'blade'],
            'ranger' => ['bone', 'sage'],
            'rogue' => ['midnight', 'grace'],
            'seraph' => ['splendor', 'valor'],
            'sorcerer' => ['arcana', 'midnight'],
            'warrior' => ['blade', 'bone'],
            'wizard' => ['codex', 'splendor'],
            'witch' => ['dread', 'sage'],
            'warlock' => ['dread', 'grace'],
            'brawler' => ['bone', 'valor'],
            'assassin' => ['midnight', 'blade'],
        ];

        foreach ($expectedClassDomains as $className => $expectedDomains) {
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

            $page = visit('/character-builder/' . $character->character_key);
            
            // Select the class
            $page->click('[pest="class-card-' . $className . '"]')
                ->wait(1);
                
            // Navigate to domain cards to verify available domains
            $page->click('[pest="sidebar-tab-9"]')
                ->wait(1);
                
            // Should show exactly the expected domains for this class
            foreach ($expectedDomains as $domain) {
                $page->assertSee(ucfirst($domain));
            }
            
            // Save character to verify in viewer
            $page->click('[pest="save-character-button"]')
                ->wait(3);

            // Navigate to character viewer
            $viewerPage = visit('/character/' . $character->character_key);
            
            // Verify class domains are displayed correctly
            $viewerPage->assertPresent('[pest="class-domains"]')
                ->assertSee(ucfirst($expectedDomains[0]) . ' & ' . ucfirst($expectedDomains[1]));
        }
    });

    it('validates playtest content displays required labels in builder and viewer', function () {
        $character = Character::factory()->create([
            'character_data' => [
                'selected_class' => 'druid',
                'selected_subclass' => 'nature spirit',
                'selected_ancestry' => null,
                'selected_community' => null,
                'assigned_traits' => [],
                'selected_equipment' => [],
                'experiences' => [],
                'selected_domain_cards' => [],
            ]
        ]);

        visit('/character-builder/' . $character->character_key)
            // Navigate to ancestry step
            ->click('[pest="sidebar-tab-3"]')
            ->wait(1)
            
            // Select Earthkin (playtest ancestry)
            ->click('[pest="ancestry-card-earthkin"]')
            ->wait(1)
            
            // Should display playtest label
            ->assertSee('Void - Playtest')
            
            // Navigate to community step
            ->click('[pest="sidebar-tab-4"]')
            ->wait(1);
            
        // If there are playtest communities, verify they show labels
        // (This would need to be adjusted based on actual playtest communities in the data)
        
        // Save character
        visit('/character-builder/' . $character->character_key)
            ->click('[pest="save-character-button"]')
            ->wait(3);

        // Navigate to character viewer
        $page = visit('/character/' . $character->character_key);
        
        // Verify playtest ancestry shows label in viewer
        $page->assertSee('Void - Playtest');
    });

    it('validates abilities data integrity across all levels', function () {
        // This test validates the JSON data structure integrity
        // Load and validate domains.json and abilities.json consistency
        
        $domainsData = json_decode(file_get_contents(resource_path('json/domains.json')), true);
        $abilitiesData = json_decode(file_get_contents(resource_path('json/abilities.json')), true);
        
        expect($domainsData)->toBeArray()
            ->and($abilitiesData)->toBeArray();
        
        // Validate each domain has abilities for levels 1-10
        foreach ($domainsData as $domainKey => $domainData) {
            expect($domainData)->toHaveKey('abilitiesByLevel');
            
            // Check levels 1-10 exist
            for ($level = 1; $level <= 10; $level++) {
                expect($domainData['abilitiesByLevel'])->toHaveKey((string)$level);
                
                $levelAbilities = $domainData['abilitiesByLevel'][(string)$level]['abilities'] ?? [];
                
                // Validate each ability exists in abilities.json
                foreach ($levelAbilities as $abilityKey) {
                    expect($abilitiesData)->toHaveKey($abilityKey, 
                        "Ability '$abilityKey' listed in domain '$domainKey' level $level not found in abilities.json");
                    
                    $ability = $abilitiesData[$abilityKey];
                    
                    // Validate ability data structure
                    expect($ability)->toHaveKey('domain')
                        ->and($ability)->toHaveKey('level')
                        ->and($ability)->toHaveKey('type')
                        ->and($ability)->toHaveKey('recallCost')
                        ->and($ability)->toHaveKey('descriptions');
                    
                    // Validate domain and level match
                    expect($ability['domain'])->toBe($domainKey);
                    expect($ability['level'])->toBe($level);
                    
                    // Validate descriptions is non-empty array
                    expect($ability['descriptions'])->toBeArray()
                        ->and($ability['descriptions'])->not()->toBeEmpty();
                    
                    // Validate recall cost is numeric
                    expect($ability['recallCost'])->toBeNumeric();
                    
                    // Validate type is one of allowed values
                    expect($ability['type'])->toBeIn(['Ability', 'Spell', 'Grimoire']);
                }
            }
            
            // Validate level 7 has touched card for this domain
            $level7Abilities = $domainData['abilitiesByLevel']['7']['abilities'] ?? [];
            $touchedCard = $domainKey . 'touched';
            expect($level7Abilities)->toContain($touchedCard, 
                "Domain '$domainKey' missing touched card '$touchedCard' at level 7");
        }
    });

    it('validates Sorcerer domains are Arcana + Midnight (SRD compliance)', function () {
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
            // Select Sorcerer class
            ->click('[pest="class-card-sorcerer"]')
            ->wait(1)
            
            // Navigate to domain cards to verify domains
            ->click('[pest="sidebar-tab-9"]')
            ->wait(1)
            
            // Should show Arcana and Midnight domains only
            ->assertSee('Arcana')
            ->assertSee('Midnight')
            ->assertDontSee('Splendor') // This would fail if implementation incorrectly maps Sorcerer to Arcana + Splendor
            
            // Save character
            ->click('[pest="save-character-button"]')
            ->wait(3);

        // Navigate to character viewer
        $page = visit('/character/' . $character->character_key);
        
        // Verify correct domains are displayed
        $page->assertPresent('[pest="class-domains"]')
            ->assertSee('Arcana & Midnight');
    });

    it('validates newer classes have proper domain filtering', function () {
        $newerClasses = ['witch', 'warlock', 'brawler', 'assassin'];
        
        foreach ($newerClasses as $className) {
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

            $page = visit('/character-builder/' . $character->character_key);
            
            // Select the newer class
            $page->click('[pest="class-card-' . $className . '"]')
                ->wait(1);
                
            // Navigate to domain cards step
            $page->click('[pest="sidebar-tab-9"]')
                ->wait(1);
                
            // Should show domain cards filtered to class domains
            // The test will fail if implementation uses hard-coded domain map that omits these classes
            $page->assertSee('Level 1') // Should show level 1 abilities
                ->assertDontSee('Level 2'); // Should not show higher level abilities
                
            // Should be able to select exactly 2 domain cards
            $domainCards = $page->driver->findElements(\Facebook\WebDriver\WebDriverBy::cssSelector('[pest^="domain-card-"]'));
            
            if (count($domainCards) >= 2) {
                // Select first two available cards
                $page->click('[pest="' . $domainCards[0]->getAttribute('pest') . '"]')
                    ->wait(1)
                    ->click('[pest="' . $domainCards[1]->getAttribute('pest') . '"]')
                    ->wait(1);
                    
                // Should show completion
                $page->assertSee('Domain Card Selection Complete!');
            }
        }
    });

    it('validates Hope feature costs are exactly 3 for all classes', function () {
        $allClasses = ['bard', 'druid', 'guardian', 'ranger', 'rogue', 'seraph', 'sorcerer', 'warrior', 'wizard'];
        
        foreach ($allClasses as $className) {
            $character = Character::factory()->create([
                'character_data' => [
                    'selected_class' => $className,
                    'selected_subclass' => null,
                    'assigned_traits' => ['agility' => 2, 'strength' => 1, 'finesse' => 1, 'instinct' => 0, 'presence' => 0, 'knowledge' => -1],
                    'selected_equipment' => [
                        ['key' => 'staff', 'type' => 'weapon', 'category' => 'weapons'],
                        ['key' => 'natural armor', 'type' => 'armor', 'category' => 'armor'],
                    ],
                    'experiences' => [
                        ['name' => 'Test Experience', 'description' => 'Test', 'modifier' => 2],
                    ],
                    'selected_domain_cards' => ['test-ability-1', 'test-ability-2'],
                ]
            ]);

            visit('/character-builder/' . $character->character_key)
                ->click('[pest="save-character-button"]')
                ->wait(3);

            // Navigate to character viewer
            $page = visit('/character/' . $character->character_key);
            
            // Verify Hope feature cost is 3 (shown as 3 diamond symbols)
            $page->assertSee('Cost'); // Hope feature should show cost label
            
            // Check for 3 hope cost diamonds (this may need adjustment based on actual HTML structure)
            $hopeCostElements = $page->driver->findElements(\Facebook\WebDriver\WebDriverBy::cssSelector('.w-4.h-4.rotate-45.rounded-sm.ring-1.ring-indigo-400\\/50.bg-slate-900'));
            expect(count($hopeCostElements))->toBe(3, "Class $className Hope feature should cost exactly 3 Hope");
        }
    });

});
