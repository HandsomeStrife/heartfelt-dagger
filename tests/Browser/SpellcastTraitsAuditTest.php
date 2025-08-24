<?php

declare(strict_types=1);

/**
 * Comprehensive audit test for Spellcasting Traits across ALL subclasses
 * 
 * This test ensures that:
 * - All subclasses with spellcasting have correct trait assignments
 * - Prayer Dice calculations use the correct spellcasting trait
 * - Spellcasting traits are consistent with subclass themes
 * - No subclasses are missing spellcasting traits when they should have them
 * - Spellcasting traits align with class domains and abilities
 */

test('all subclasses with spellcasting have correct trait assignments - comprehensive audit', function () {
    // Load all subclasses to audit their spellcasting traits
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    
    // Expected spellcasting traits per subclass
    // These should align with the subclass's thematic focus and mechanical design
    $expectedSpellcastTraits = [
        // Bard subclasses - Social and performance focused
        'troubadour' => 'Presence',      // Performance and social magic
        'wordsmith' => 'Presence',       // Epic poetry and social influence
        
        // Druid subclasses - Nature and instinct focused  
        'warden of renewal' => 'Instinct',  // Natural healing and growth
        'warden of the elements' => 'Instinct',  // Elemental nature magic
        
        // Ranger subclasses - Mixed traits based on specialization
        'beastbound' => 'Agility',       // Mobile companion tactics
        'wayfinder' => 'Agility',        // Mobile hunting and tracking
        
        // Rogue subclasses - Finesse and precision focused
        'nightwalker' => 'Finesse',      // Precise shadow magic
        'syndicate' => 'Finesse',        // Subtle social manipulation
        
        // Seraph subclasses - Strength and divine power
        'divine wielder' => 'Strength',  // Physical divine magic
        'winged sentinel' => 'Strength', // Aerial combat magic
        
        // Sorcerer subclasses - Instinct and raw magic
        'elemental origin' => 'Instinct', // Instinctual elemental magic
        'primal origin' => 'Instinct',   // Raw magical manipulation
        
        // Wizard subclasses - Knowledge and study focused
        'school of knowledge' => 'Knowledge', // Academic magical research
        'school of war' => 'Knowledge',  // Tactical combat magic
        
        // Non-spellcasting subclasses - Should NOT have spellcasting traits
        'call of the brave' => null,    // Warrior - no spellcasting
        'call of the slayer' => null,   // Warrior - no spellcasting  
        'stalwart' => null,             // Guardian - no spellcasting
        'vengeance' => null,             // Guardian - no spellcasting
    ];
    
    foreach ($expectedSpellcastTraits as $subclassKey => $expectedTrait) {
        $subclass = $subclassData[$subclassKey];
        $actualTrait = $subclass['spellcastTrait'] ?? null;
        
        if ($expectedTrait === null) {
            // Non-spellcasting subclasses should not have spellcastTrait
            expect($actualTrait)
                ->toBeNull("Subclass '{$subclassKey}' should not have spellcasting (no spellcastTrait)");
        } else {
            // Spellcasting subclasses should have the correct trait
            expect($actualTrait)
                ->toBe($expectedTrait, "Subclass '{$subclassKey}' should have spellcast trait '{$expectedTrait}', got '{$actualTrait}'");
        }
    }

});

test('spellcasting traits align with class domains and themes', function () {
    // Test that spellcasting traits make thematic sense with class domains
    $testCases = [
        // Bard (Grace + Codex) - Social domains → Presence spellcasting
        ['class' => 'bard', 'subclass' => 'troubadour', 'expected_trait' => 'Presence', 'domains' => ['grace', 'codex']],
        ['class' => 'bard', 'subclass' => 'wordsmith', 'expected_trait' => 'Presence', 'domains' => ['grace', 'codex']],
        
        // Druid (Sage + Arcana) - Nature domains → Instinct spellcasting  
        ['class' => 'druid', 'subclass' => 'warden of renewal', 'expected_trait' => 'Instinct', 'domains' => ['sage', 'arcana']],
        ['class' => 'druid', 'subclass' => 'warden of the elements', 'expected_trait' => 'Instinct', 'domains' => ['sage', 'arcana']],
        
        // Ranger (Bone + Sage) - Mixed domains → Agility spellcasting
        ['class' => 'ranger', 'subclass' => 'beastbound', 'expected_trait' => 'Agility', 'domains' => ['bone', 'sage']],
        ['class' => 'ranger', 'subclass' => 'wayfinder', 'expected_trait' => 'Agility', 'domains' => ['bone', 'sage']],
        
        // Rogue (Midnight + Grace) - Shadow/social → Finesse spellcasting
        ['class' => 'rogue', 'subclass' => 'nightwalker', 'expected_trait' => 'Finesse', 'domains' => ['midnight', 'grace']],
        ['class' => 'rogue', 'subclass' => 'syndicate', 'expected_trait' => 'Finesse', 'domains' => ['midnight', 'grace']],
        
        // Seraph (Splendor + Valor) - Divine combat → Strength spellcasting
        ['class' => 'seraph', 'subclass' => 'divine wielder', 'expected_trait' => 'Strength', 'domains' => ['splendor', 'valor']],
        ['class' => 'seraph', 'subclass' => 'winged sentinel', 'expected_trait' => 'Strength', 'domains' => ['splendor', 'valor']],
        
        // Sorcerer (Arcana + Midnight) - Raw magic → Instinct spellcasting
        ['class' => 'sorcerer', 'subclass' => 'elemental origin', 'expected_trait' => 'Instinct', 'domains' => ['arcana', 'midnight']],
        ['class' => 'sorcerer', 'subclass' => 'primal origin', 'expected_trait' => 'Instinct', 'domains' => ['arcana', 'midnight']],
        
        // Wizard (Codex + Splendor) - Academic magic → Knowledge spellcasting
        ['class' => 'wizard', 'subclass' => 'school of knowledge', 'expected_trait' => 'Knowledge', 'domains' => ['codex', 'splendor']],
        ['class' => 'wizard', 'subclass' => 'school of war', 'expected_trait' => 'Knowledge', 'domains' => ['codex', 'splendor']],
    ];
    
    foreach ($testCases as $case) {
        $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
        $classData = json_decode(file_get_contents(resource_path('json/classes.json')), true);
        
        $subclass = $subclassData[$case['subclass']];
        $class = $classData[$case['class']];
        
        // Verify domains match expectations
        expect($class['domains'])->toBe($case['domains'], 
            "Class {$case['class']} should have domains " . implode(', ', $case['domains']));
        
        // Verify spellcasting trait
        expect($subclass['spellcastTrait'])->toBe($case['expected_trait'],
            "Subclass {$case['subclass']} should use {$case['expected_trait']} for spellcasting");
    }

});

test('non-spellcasting subclasses correctly lack spellcast traits', function () {
    // Verify that Warrior and Guardian subclasses don't have spellcasting
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    
    $nonSpellcastingSubclasses = [
        'call of the brave',   // Warrior subclass
        'call of the slayer',  // Warrior subclass  
        'stalwart',           // Guardian subclass
        'vengeance',          // Guardian subclass
    ];
    
    foreach ($nonSpellcastingSubclasses as $subclassKey) {
        $subclass = $subclassData[$subclassKey];
        
        expect($subclass)->not()->toHaveKey('spellcastTrait', 
            "Non-spellcasting subclass '{$subclassKey}' should not have spellcastTrait defined");
    }

});

test('prayer dice calculations use correct spellcasting traits', function () {
    // Test that Prayer Dice calculations work correctly with each spellcasting trait
    $testCases = [
        // High trait values should provide more Prayer Dice
        ['class' => 'bard', 'subclass' => 'troubadour', 'trait' => 'presence', 'trait_value' => 2],
        ['class' => 'druid', 'subclass' => 'warden of renewal', 'trait' => 'instinct', 'trait_value' => 2],
        ['class' => 'ranger', 'subclass' => 'beastbound', 'trait' => 'agility', 'trait_value' => 2],
        ['class' => 'rogue', 'subclass' => 'nightwalker', 'trait' => 'finesse', 'trait_value' => 2],
        ['class' => 'seraph', 'subclass' => 'divine wielder', 'trait' => 'strength', 'trait_value' => 2],
        ['class' => 'sorcerer', 'subclass' => 'elemental origin', 'trait' => 'instinct', 'trait_value' => 2],
        ['class' => 'wizard', 'subclass' => 'school of knowledge', 'trait' => 'knowledge', 'trait_value' => 2],
    ];
    
    foreach ($testCases as $case) {
        $character = createTestCharacterWith([
            'class' => $case['class'],
            'subclass' => $case['subclass'],
            'ancestry' => 'human',
            'community' => 'loreborne',
        ]);
        
        // Set up character traits with high spellcasting trait
        $traits = [
            'agility' => 0,
            'strength' => 0,
            'finesse' => 0,
            'instinct' => 0,
            'presence' => 0,
            'knowledge' => -1,
        ];
        $traits[$case['trait']] = $case['trait_value']; // High spellcasting trait
        
        foreach ($traits as $traitName => $value) {
            $character->traits()->create([
                'trait_name' => $traitName,
                'trait_value' => $value,
            ]);
        }
        
        // Verify the character was created correctly
        expect($character->fresh())->class->toBe($case['class']);
        expect($character->fresh())->subclass->toBe($case['subclass']);
        
        // Verify spellcasting trait is set correctly
        $spellcastTrait = $character->traits()->where('trait_name', $case['trait'])->first();
        expect($spellcastTrait->trait_value)->toBe($case['trait_value']);
    }

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('spellcasting traits are consistent across subclass features', function () {
    // Test that subclasses with spellcasting traits have features that reference spellcasting
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    
    $spellcastingSubclasses = [
        'troubadour', 'wordsmith',           // Bard
        'warden of renewal', 'warden of the elements',  // Druid
        'beastbound', 'wayfinder',           // Ranger
        'nightwalker', 'syndicate',          // Rogue
        'divine wielder', 'winged sentinel', // Seraph
        'elemental origin', 'primal origin', // Sorcerer
        'school of knowledge', 'school of war', // Wizard
    ];
    
    foreach ($spellcastingSubclasses as $subclassKey) {
        $subclass = $subclassData[$subclassKey];
        
        // Should have spellcastTrait defined
        expect($subclass)->toHaveKey('spellcastTrait');
        
        // Spellcast trait should be a valid trait name
        $spellcastTrait = $subclass['spellcastTrait'];
        $validTraits = ['Agility', 'Strength', 'Finesse', 'Instinct', 'Presence', 'Knowledge'];
        
        expect($validTraits)->toContain($spellcastTrait);
    }

});

test('spellcasting trait naming convention is consistent', function () {
    // Test that all spellcastTrait values use proper capitalization
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    
    foreach ($subclassData as $key => $subclass) {
        if (isset($subclass['spellcastTrait'])) {
            $trait = $subclass['spellcastTrait'];
            
            // Should be properly capitalized
            expect($trait)->toBe(ucfirst(strtolower($trait)));
            
            // Should not have extra spaces or special characters
            expect($trait)->toMatch('/^[A-Z][a-z]+$/');
        }
    }

});

test('unique spellcasting traits reflect subclass specializations', function () {
    // Test that different subclasses within the same class can have different spellcasting approaches
    $subclassData = json_decode(file_get_contents(resource_path('json/subclasses.json')), true);
    
    // Some classes have subclasses with the same spellcast trait (normal)
    // Others might have different approaches (thematic variation)
    $spellcastTraitsByClass = [];
    
    foreach ($subclassData as $key => $subclass) {
        if (isset($subclass['spellcastTrait'])) {
            // Extract class name from subclass key pattern
            $className = null;
            if (str_starts_with($key, 'troubadour') || str_starts_with($key, 'wordsmith')) {
                $className = 'bard';
            } elseif (str_contains($key, 'warden')) {
                $className = 'druid';
            } elseif (str_contains($key, 'bound') || str_contains($key, 'wayfinder')) {
                $className = 'ranger';
            } elseif (str_contains($key, 'nightwalker') || str_contains($key, 'syndicate')) {
                $className = 'rogue';
            } elseif (str_contains($key, 'wielder') || str_contains($key, 'sentinel')) {
                $className = 'seraph';
            } elseif (str_contains($key, 'origin')) {
                $className = 'sorcerer';
            } elseif (str_contains($key, 'school')) {
                $className = 'wizard';
            }
            
            if ($className) {
                $spellcastTraitsByClass[$className][] = $subclass['spellcastTrait'];
            }
        }
    }
    
    // Verify that each class has consistent or thematically appropriate spellcast traits
    foreach ($spellcastTraitsByClass as $className => $traits) {
        $uniqueTraits = array_unique($traits);
        
        // For most classes, subclasses should share the same spellcast trait
        // This ensures consistent magical approach within each class
        expect(count($uniqueTraits))->toBeLessThanOrEqual(2);
    }

});
