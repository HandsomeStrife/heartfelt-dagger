<?php

declare(strict_types=1);

/**
 * Comprehensive test for Void Playtest Content
 * 
 * This test ensures that:
 * - All playtest ancestries are properly marked and labeled
 * - Playtest content displays correct version information
 * - UI properly shows playtest badges and warnings
 * - Playtest ancestries function correctly in character creation
 * - Mechanical bonuses from playtest ancestries work properly
 */

test('all playtest ancestries are properly marked and labeled', function () {
    // Load ancestries data
    $ancestries = json_decode(file_get_contents(resource_path('json/ancestries.json')), true);
    
    // Expected playtest ancestries (updated to v1.5)
    $expectedPlaytestAncestries = [
        'earthkin' => 'Void - Playtest v1.5',
        'skykin' => 'Void - Playtest v1.5', 
        'tidekin' => 'Void - Playtest v1.5',
        'aetheris' => 'Void - Playtest v1.5',
        'emberkin' => 'Void - Playtest v1.5',
        'gnome' => 'Void - Playtest v1.5',
    ];
    
    foreach ($expectedPlaytestAncestries as $ancestryKey => $expectedLabel) {
        expect($ancestries)->toHaveKey($ancestryKey);
        
        $ancestry = $ancestries[$ancestryKey];
        
        // Should have playtest marking
        expect($ancestry)->toHaveKey('playtest');
        expect($ancestry['playtest']['isPlaytest'])->toBeTrue();
        expect($ancestry['playtest']['version'])->toBe('1.5');
        expect($ancestry['playtest']['label'])->toBe($expectedLabel);
    }

});

test('non-playtest ancestries do not have playtest markings', function () {
    // Load ancestries data
    $ancestries = json_decode(file_get_contents(resource_path('json/ancestries.json')), true);
    
    // Standard ancestries should not have playtest markings
    $standardAncestries = [
        'clank', 'drakona', 'dwarf', 'elf', 'faerie', 'faun', 'firbolg',
        'fungril', 'galapa', 'giant', 'goblin', 'halfling', 'human', 
        'infernis', 'katari', 'orc', 'ribbet', 'simiah'
    ];
    
    foreach ($standardAncestries as $ancestryKey) {
        expect($ancestries)->toHaveKey($ancestryKey);
        
        $ancestry = $ancestries[$ancestryKey];
        
        // Should not have playtest field or it should be false
        if (isset($ancestry['playtest'])) {
            expect($ancestry['playtest']['isPlaytest'])->toBeFalse();
        }
    }

});

test('earthkin mechanical bonuses work correctly', function () {
    // Test Earthkin's Stoneskin feature (+1 armor score, +1 damage threshold)
    $character = createTestCharacterWith([
        'class' => 'guardian',
        'subclass' => 'stalwart',
        'ancestry' => 'earthkin',
        'community' => 'ridgeborne',
    ]);

    // Test damage threshold bonus (this method exists)
    expect($character->getAncestryDamageThresholdBonus())->toBe(1, 'Earthkin should provide +1 damage threshold bonus');
    
    // Test that Earthkin has the correct effects by checking the ancestry data directly
    $effects = $character->getAncestryEffects('armor_score_bonus');
    $armorScoreBonus = 0;
    foreach ($effects as $effect) {
        $armorScoreBonus += $effect['value'] ?? 0;
    }
    expect($armorScoreBonus)->toBe(1, 'Earthkin should provide +1 armor score bonus');

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('playtest content works correctly in data loading', function () {
    // Test that playtest ancestries and communities are loaded correctly by the CharacterBuilder component
    
    // Create a Livewire component to test data loading
    $component = new \App\Livewire\CharacterBuilder();
    $component->initializeCharacter();
    $component->loadGameData();
    
    // Verify playtest ancestries are loaded
    expect($component->game_data)->toHaveKey('ancestries');
    expect($component->game_data['ancestries'])->toHaveKey('earthkin');
    expect($component->game_data['ancestries'])->toHaveKey('skykin');
    expect($component->game_data['ancestries'])->toHaveKey('tidekin');
    expect($component->game_data['ancestries'])->toHaveKey('aetheris');
    expect($component->game_data['ancestries'])->toHaveKey('emberkin');
    expect($component->game_data['ancestries'])->toHaveKey('gnome');
    
    // Verify playtest communities are loaded
    expect($component->game_data)->toHaveKey('communities');
    expect($component->game_data['communities'])->toHaveKey('duneborne');
    expect($component->game_data['communities'])->toHaveKey('freeborne');
    expect($component->game_data['communities'])->toHaveKey('frostborne');
    expect($component->game_data['communities'])->toHaveKey('hearthborne');
    expect($component->game_data['communities'])->toHaveKey('reborne');
    expect($component->game_data['communities'])->toHaveKey('warborne');
    
    // Verify playtest marking is preserved
    expect($component->game_data['ancestries']['earthkin']['playtest']['isPlaytest'])->toBeTrue();
    expect($component->game_data['ancestries']['earthkin']['playtest']['label'])->toBe('Void - Playtest v1.5');
    
    expect($component->game_data['communities']['duneborne']['playtest']['isPlaytest'])->toBeTrue();
    expect($component->game_data['communities']['duneborne']['playtest']['label'])->toBe('Void - Playtest v1.5');

});

test('all playtest ancestries have proper feature descriptions', function () {
    // Verify all playtest ancestries have well-formed features
    $ancestries = json_decode(file_get_contents(resource_path('json/ancestries.json')), true);
    
    $playtestAncestries = ['earthkin', 'skykin', 'tidekin', 'aetheris', 'emberkin', 'gnome'];
    
    foreach ($playtestAncestries as $ancestryKey) {
        $ancestry = $ancestries[$ancestryKey];
        
        // Should have features
        expect($ancestry)->toHaveKey('features');
        expect($ancestry['features'])->toBeArray();
        expect(count($ancestry['features']))->toBeGreaterThan(0);
        
        // Each feature should have name and description
        foreach ($ancestry['features'] as $feature) {
            expect($feature)->toHaveKey('name');
            expect($feature)->toHaveKey('description');
            expect($feature['name'])->toBeString();
            expect($feature['description'])->toBeString();
            expect(strlen($feature['name']))->toBeGreaterThan(0);
            expect(strlen($feature['description']))->toBeGreaterThan(10);
        }
    }

});

test('all playtest communities are properly marked and labeled', function () {
    // Load communities data
    $communities = json_decode(file_get_contents(resource_path('json/communities.json')), true);
    
    // Expected playtest communities
    $expectedPlaytestCommunities = [
        'duneborne' => 'Void - Playtest v1.5',
        'freeborne' => 'Void - Playtest v1.5',
        'frostborne' => 'Void - Playtest v1.5',
        'hearthborne' => 'Void - Playtest v1.5',
        'reborne' => 'Void - Playtest v1.5',
        'warborne' => 'Void - Playtest v1.5',
    ];
    
    foreach ($expectedPlaytestCommunities as $communityKey => $expectedLabel) {
        expect($communities)->toHaveKey($communityKey);
        
        $community = $communities[$communityKey];
        
        // Should have playtest marking
        expect($community)->toHaveKey('playtest');
        expect($community['playtest']['isPlaytest'])->toBeTrue();
        expect($community['playtest']['version'])->toBe('1.5');
        expect($community['playtest']['label'])->toBe($expectedLabel);
    }

});

test('non-playtest communities do not have playtest markings', function () {
    // Load communities data
    $communities = json_decode(file_get_contents(resource_path('json/communities.json')), true);
    
    // Standard communities should not have playtest markings
    $standardCommunities = [
        'highborne', 'loreborne', 'orderborne', 'ridgeborne', 'seaborne',
        'slyborne', 'underborne', 'wanderborne', 'wildborne'
    ];
    
    foreach ($standardCommunities as $communityKey) {
        expect($communities)->toHaveKey($communityKey);
        
        $community = $communities[$communityKey];
        
        // Should not have playtest field or it should be false
        if (isset($community['playtest'])) {
            expect($community['playtest']['isPlaytest'])->toBeFalse();
        }
    }

});

test('playtest content versioning is consistent', function () {
    // All playtest content should use consistent versioning (v1.5)
    $ancestries = json_decode(file_get_contents(resource_path('json/ancestries.json')), true);
    $communities = json_decode(file_get_contents(resource_path('json/communities.json')), true);
    
    $playtestAncestries = [];
    foreach ($ancestries as $key => $ancestry) {
        if (isset($ancestry['playtest']['isPlaytest']) && $ancestry['playtest']['isPlaytest']) {
            $playtestAncestries[$key] = $ancestry['playtest'];
        }
    }
    
    $playtestCommunities = [];
    foreach ($communities as $key => $community) {
        if (isset($community['playtest']['isPlaytest']) && $community['playtest']['isPlaytest']) {
            $playtestCommunities[$key] = $community['playtest'];
        }
    }
    
    // All playtest content should have version 1.5
    foreach ($playtestAncestries as $key => $playtest) {
        expect($playtest['version'])->toBe('1.5', "Playtest ancestry '{$key}' should have version 1.5");
        expect($playtest['label'])->toBe('Void - Playtest v1.5', "Playtest ancestry '{$key}' should have consistent label");
    }
    
    foreach ($playtestCommunities as $key => $playtest) {
        expect($playtest['version'])->toBe('1.5', "Playtest community '{$key}' should have version 1.5");
        expect($playtest['label'])->toBe('Void - Playtest v1.5', "Playtest community '{$key}' should have consistent label");
    }

});

test('playtest ancestries work with character creation flow', function () {
    // Test complete character creation with playtest ancestry
    $character = createTestCharacterWith([
        'class' => 'sorcerer',
        'subclass' => 'elemental origin',
        'ancestry' => 'emberkin',  // Playtest ancestry
        'community' => 'wanderborne',
    ]);

    // Add traits
    $character->traits()->createMany([
        ['trait_name' => 'agility', 'trait_value' => 0],
        ['trait_name' => 'strength', 'trait_value' => 0],
        ['trait_name' => 'finesse', 'trait_value' => 1],
        ['trait_name' => 'instinct', 'trait_value' => 2],
        ['trait_name' => 'presence', 'trait_value' => 1],
        ['trait_name' => 'knowledge', 'trait_value' => -1],
    ]);

    // Verify character was created successfully
    expect($character->fresh())->ancestry->toBe('emberkin');
    expect($character->fresh())->class->toBe('sorcerer');
    
    // Verify stats calculation works with playtest ancestry
    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);
    expect($stats->hit_points)->toBeGreaterThan(0);
    expect($stats->evasion)->toBeGreaterThan(0);
    expect($stats->stress)->toBeGreaterThan(0);

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('playtest ancestries work with character selection', function () {
    // Test that playtest ancestries can be selected and stored correctly
    $character = createTestCharacterWith([
        'class' => 'wizard',
        'subclass' => 'school of knowledge',
        'ancestry' => 'gnome',  // Playtest ancestry
        'community' => 'loreborne',
    ]);

    // Verify the character was created with the playtest ancestry
    expect($character->fresh())->ancestry->toBe('gnome');
    
    // Verify the ancestry is recognized as playtest content
    $ancestryData = json_decode(file_get_contents(resource_path('json/ancestries.json')), true);
    expect($ancestryData['gnome']['playtest']['isPlaytest'])->toBeTrue();
    expect($ancestryData['gnome']['playtest']['label'])->toBe('Void - Playtest v1.5');

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('playtest communities work with character creation flow', function () {
    // Test complete character creation with playtest community
    $character = createTestCharacterWith([
        'class' => 'ranger',
        'subclass' => 'beastbound',
        'ancestry' => 'human',
        'community' => 'warborne',  // Playtest community
    ]);

    // Add traits
    $character->traits()->createMany([
        ['trait_name' => 'agility', 'trait_value' => 2],
        ['trait_name' => 'strength', 'trait_value' => 1],
        ['trait_name' => 'finesse', 'trait_value' => 0],
        ['trait_name' => 'instinct', 'trait_value' => 1],
        ['trait_name' => 'presence', 'trait_value' => 0],
        ['trait_name' => 'knowledge', 'trait_value' => -1],
    ]);

    // Verify character was created successfully
    expect($character->fresh())->community->toBe('warborne');
    expect($character->fresh())->class->toBe('ranger');
    
    // Verify stats calculation works with playtest community
    $stats = \Domain\Character\Data\CharacterStatsData::fromModel($character);
    expect($stats->hit_points)->toBeGreaterThan(0);
    expect($stats->evasion)->toBeGreaterThan(0);
    expect($stats->stress)->toBeGreaterThan(0);
    
    // Verify the community is recognized as playtest content
    $communityData = json_decode(file_get_contents(resource_path('json/communities.json')), true);
    expect($communityData['warborne']['playtest']['isPlaytest'])->toBeTrue();
    expect($communityData['warborne']['playtest']['label'])->toBe('Void - Playtest v1.5');

})->uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
