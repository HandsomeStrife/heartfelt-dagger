<?php

declare(strict_types=1);

it('validates communities.json structure and content', function (): void {
    $communitiesPath = base_path('resources/json/communities.json');
    expect(file_exists($communitiesPath))->toBeTrue('Communities data file should exist');
    
    $communities = json_decode((string) file_get_contents($communitiesPath), true);
    expect($communities)->toBeArray('Communities data should be valid JSON');
    expect($communities)->not->toBeEmpty('Communities data should not be empty');
    
    foreach ($communities as $communityKey => $communityData) {
        // Basic structure validation
        expect($communityData)->toHaveKey('key')->and($communityData['key'])->toBe($communityKey);
        expect($communityData)->toHaveKey('name');
        expect($communityData)->toHaveKey('description');
        expect($communityData)->toHaveKey('communityFeature');
        
        // Key consistency
        expect($communityData['key'])->toBe($communityKey, "Community key should match JSON key for {$communityKey}");
        
        // Community feature structure
        $feature = $communityData['communityFeature'];
        expect($feature)->toHaveKey('name');
        expect($feature)->toHaveKey('description');
        
        // Content validation
        expect($communityData['name'])->toBeString()->not->toBeEmpty();
        expect($communityData['description'])->toBeString()->not->toBeEmpty();
        expect($feature['name'])->toBeString()->not->toBeEmpty();
        expect($feature['description'])->toBeString()->not->toBeEmpty();
        
        // Playtest validation (if present)
        if (isset($communityData['playtest'])) {
            expect($communityData['playtest'])->toHaveKey('isPlaytest');
            expect($communityData['playtest'])->toHaveKey('version');
            expect($communityData['playtest'])->toHaveKey('label');
            
            expect($communityData['playtest']['isPlaytest'])->toBeBool();
            expect($communityData['playtest']['version'])->toBeString()->not->toBeEmpty();
            expect($communityData['playtest']['label'])->toBeString()->not->toBeEmpty();
            
            // Validate playtest label format
            expect($communityData['playtest']['label'])->toContain('Void - Playtest');
        }
    }
});

it('identifies playtest vs stable communities correctly', function (): void {
    $communitiesPath = base_path('resources/json/communities.json');
    $communities = json_decode((string) file_get_contents($communitiesPath), true) ?? [];
    
    $stableCommunities = [];
    $playtestCommunities = [];
    
    foreach ($communities as $communityKey => $communityData) {
        if (isset($communityData['playtest']['isPlaytest']) && $communityData['playtest']['isPlaytest'] === true) {
            $playtestCommunities[] = $communityKey;
        } else {
            $stableCommunities[] = $communityKey;
        }
    }
    
    // Verify we have the expected stable communities (based on DaggerHeart SRD)
    $expectedStable = ['highborne', 'loreborne', 'orderborne', 'ridgeborne', 'seaborne', 'slyborne', 'underborne', 'wanderborne', 'wildborne'];
    
    foreach ($expectedStable as $expectedCommunity) {
        expect($stableCommunities)->toContain($expectedCommunity);
    }
    
    // Verify playtest communities are correctly marked
    $expectedPlaytest = ['duneborne', 'freeborne', 'frostborne', 'hearthborne', 'reborne', 'warborne'];
    
    foreach ($expectedPlaytest as $expectedCommunity) {
        expect($playtestCommunities)->toContain($expectedCommunity);
    }
    
    // Verify counts
    expect($stableCommunities)->toHaveCount(9, 'Should have 9 stable communities');
    expect($playtestCommunities)->toHaveCount(6, 'Should have 6 playtest communities');
});

it('validates community features are unique and meaningful', function (): void {
    $communitiesPath = base_path('resources/json/communities.json');
    $communities = json_decode((string) file_get_contents($communitiesPath), true) ?? [];
    
    $featureNames = [];
    $featureDescriptions = [];
    
    foreach ($communities as $communityKey => $communityData) {
        $feature = $communityData['communityFeature'];
        $featureName = $feature['name'];
        $featureDescription = $feature['description'];
        
        // Check for duplicate feature names
        expect($featureNames)->not->toContain($featureName);
        $featureNames[] = $featureName;
        
        // Check for duplicate descriptions (exactly the same)
        expect($featureDescriptions)->not->toContain($featureDescription);
        $featureDescriptions[] = $featureDescription;
        
        // Validate feature name is appropriate length
        expect(strlen($featureName))->toBeGreaterThan(3);
        expect(strlen($featureName))->toBeLessThan(30);
        
        // Validate feature description has substance
        expect(strlen($featureDescription))->toBeGreaterThan(20);
    }
});
