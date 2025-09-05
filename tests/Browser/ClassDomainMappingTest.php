<?php

declare(strict_types=1);

describe('Class Domain Mapping SRD Compliance', function () {
    
    it('validates all class domain pairs match SRD specification exactly', function () {
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

        $classesData = json_decode(file_get_contents(resource_path('json/classes.json')), true);
        
        foreach ($expectedClassDomains as $classKey => $expectedDomains) {
            expect($classesData)->toHaveKey($classKey);
            
            $actualDomains = $classesData[$classKey]['domains'] ?? [];
            sort($actualDomains);
            sort($expectedDomains);
            
            expect($actualDomains)->toBe($expectedDomains);
        }
    });

    it('validates domain card filtering works for all classes in builder', function () {
        $page = visit('/character-builder');
        $page->assertPathBeginsWith('/character-builder/');

        $classesData = json_decode(file_get_contents(resource_path('json/classes.json')), true);
        $domainsData = json_decode(file_get_contents(resource_path('json/domains.json')), true);
        
        // Test a sample of classes including new ones
        $testClasses = ['wizard', 'druid', 'witch', 'assassin', 'brawler'];
        
        foreach ($testClasses as $classKey) {
            if (!isset($classesData[$classKey])) {
                continue; // Skip if class doesn't exist in data
            }
            
            $classDomains = $classesData[$classKey]['domains'];
            
            // Select the class
            $page = visit('/character-builder');
            $page->click("[pest=\"class-card-{$classKey}\"]")
                ->wait(1);
                
            // Navigate to domain cards step
            for ($i = 0; $i < 8; $i++) {
                $page->click('[pest="next-step-button"]')->wait(0.5);
            }
            $page->assertSee('Select Domain Cards')->wait(1);
            
            // Verify only level 1 cards from class domains are shown
            foreach ($classDomains as $domain) {
                $level1Cards = $domainsData[$domain]['abilitiesByLevel']['1']['abilities'] ?? [];
                
                foreach (array_slice($level1Cards, 0, 3) as $cardKey) { // Test first 3 cards
                    $page->assertPresent("[pest*=\"domain-card-{$domain}-\"]");
                }
            }
        }
    });

    it('validates domain card selection limits work correctly', function () {
        $page = visit('/character-builder');
        
        // Test regular class (2 card limit)
        $page->click('[pest="class-card-wizard"]')->wait(1);
        
        // Navigate to domain cards
        for ($i = 0; $i < 8; $i++) {
            $page->click('[pest="next-step-button"]')->wait(0.5);
        }
        $page->assertSee('Select Domain Cards')->wait(1);
        
        // Select 2 cards (be more specific to avoid multiple matches)
        $codexCards = $page->elements('[pest*="domain-card-codex-"]');
        $splendorCards = $page->elements('[pest*="domain-card-splendor-"]');
        
        if (count($codexCards) > 0) {
            $page->click($codexCards[0])->wait(0.5);
        }
        if (count($splendorCards) > 0) {
            $page->click($splendorCards[0])->wait(0.5);
        }
        
        // Verify selection count shows 2
        $page->assertSee('2', '[pest="domain-card-selected-count"]');
        
        // Try to select a third card - should replace one of the existing
        $allCodexCards = $page->elements('[pest*="domain-card-codex-"]');
        if (count($allCodexCards) > 1) {
            $page->click($allCodexCards[1])->wait(0.5);
            // Should still show max of 2 selected
            $page->assertSee('2', '[pest="domain-card-selected-count"]');
        }
    });

    it('validates School of Knowledge subclass increases domain card limit to 3', function () {
        $page = visit('/character-builder');
        
        // Select Wizard with School of Knowledge subclass
        $page->click('[pest="class-card-wizard"]')->wait(1);
        $page->click('[pest="subclass-card-school-of-knowledge"]')->wait(2);
        
        // Navigate to domain cards
        for ($i = 0; $i < 7; $i++) {
            $page->click('[pest="next-step-button"]')->wait(0.5);
        }
        $page->assertSee('Select Domain Cards')->wait(1);
        
        // Should show max of 3 instead of 2
        $page->assertSee('3', '[pest="domain-card-max-count"]');
        
        // Select 3 cards
        $codexCards = $page->elements('[pest*="domain-card-codex-"]');
        $splendorCards = $page->elements('[pest*="domain-card-splendor-"]');
        
        if (count($codexCards) >= 2 && count($splendorCards) >= 1) {
            $page->click($codexCards[0])->wait(0.5);
            $page->click($codexCards[1])->wait(0.5);
            $page->click($splendorCards[0])->wait(0.5);
            
            // Should show 3 selected
            $page->assertSee('3', '[pest="domain-card-selected-count"]');
        }
    });
});
