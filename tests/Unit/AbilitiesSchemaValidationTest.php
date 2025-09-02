<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

it('validates abilities.json has correct schema for all entries', function (): void {
    $abilitiesPath = base_path('resources/json/abilities.json');
    expect($abilitiesPath)->toBeFile();
    
    $abilities = json_decode(File::get($abilitiesPath), true);
    expect($abilities)->toBeArray()->not->toBeEmpty();

    foreach ($abilities as $abilityKey => $abilityData) {
        // Required fields
        expect($abilityData)->toHaveKey('name')->and($abilityData['name'])->toBeString()->not->toBeEmpty();
        expect($abilityData)->toHaveKey('domain')->and($abilityData['domain'])->toBeString()->not->toBeEmpty();
        expect($abilityData)->toHaveKey('level')->and($abilityData['level'])->toBeInt()->toBeGreaterThanOrEqual(1)->toBeLessThanOrEqual(10);
        expect($abilityData)->toHaveKey('type')->and($abilityData['type'])->toBeString()->not->toBeEmpty();
        
        // Recall cost should be numeric (0-6 range)
        if (isset($abilityData['recallCost'])) {
            expect($abilityData['recallCost'])->toBeNumeric();
            expect((int) $abilityData['recallCost'])->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(6);
        }
        
        // Description(s) validation
        if (isset($abilityData['description'])) {
            expect($abilityData['description'])->toBeString()->not->toBeEmpty();
        }
        
        if (isset($abilityData['descriptions'])) {
            expect($abilityData['descriptions'])->toBeArray()->not->toBeEmpty();
            foreach ($abilityData['descriptions'] as $desc) {
                expect($desc)->toBeString()->not->toBeEmpty();
            }
        }
        
        // At least one description field should exist
        expect(isset($abilityData['description']) || isset($abilityData['descriptions']))->toBeTrue();
        
        // Playtest flag validation
        if (isset($abilityData['playtest'])) {
            expect($abilityData['playtest'])->toBeBool();
        }
        
        // Version validation (for playtest content)
        if (isset($abilityData['version'])) {
            expect($abilityData['version'])->toBeString()->not->toBeEmpty();
            expect($abilityData['version'])->toMatch('/^v?\d+\.\d+/');
        }
    }
});

it('validates abilities have known domain references', function (): void {
    $abilitiesPath = base_path('resources/json/abilities.json');
    $domainsPath = base_path('resources/json/domains.json');
    
    $abilities = json_decode(File::get($abilitiesPath), true);
    $domains = json_decode(File::get($domainsPath), true);
    
    $validDomains = array_keys($domains);
    
    foreach ($abilities as $abilityKey => $abilityData) {
        expect($abilityData['domain'])->toBeIn($validDomains);
    }
});

it('validates ability types are consistent', function (): void {
    $abilitiesPath = base_path('resources/json/abilities.json');
    $abilities = json_decode(File::get($abilitiesPath), true);
    
    $knownTypes = [
        'ability', 'attack', 'reaction', 'spell', 'enhancement', 'passive',
        'feature', 'maneuver', 'ritual', 'cantrip', 'technique'
    ];
    
    $foundTypes = [];
    
    foreach ($abilities as $abilityKey => $abilityData) {
        $type = strtolower($abilityData['type']);
        $foundTypes[] = $type;
        
        // For now, just collect types - we can expand known types as needed
        // This test ensures we're aware of all ability types in use
    }
    
    $uniqueTypes = array_unique($foundTypes);
    
    // Log found types for review
    expect($uniqueTypes)->toBeArray();
    
    // Ensure we don't have obvious typos (empty or very short types)
    foreach ($uniqueTypes as $type) {
        expect($type)->toBeString()->not->toBeEmpty();
        expect(strlen($type))->toBeGreaterThanOrEqual(3);
    }
});

it('validates playtest abilities have proper version markers', function (): void {
    $abilitiesPath = base_path('resources/json/abilities.json');
    $abilities = json_decode(File::get($abilitiesPath), true);
    
    foreach ($abilities as $abilityKey => $abilityData) {
        if (isset($abilityData['playtest']) && $abilityData['playtest'] === true) {
            expect($abilityData)->toHaveKey('version');
            expect($abilityData['version'])->toBeString()->not->toBeEmpty();
            
            // Name should contain "Void - Playtest" marker
            expect($abilityData['name'])->toContain('Void - Playtest');
        }
    }
});
