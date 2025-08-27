<?php

declare(strict_types=1);

use Domain\Character\Enums\ClassEnum;
use Domain\Character\Enums\DomainEnum;

test('all new playtest classes are added to ClassEnum correctly', function () {
    // Test that all new classes exist as enum cases
    expect(ClassEnum::ASSASSIN->value)->toBe('assassin');
    expect(ClassEnum::BRAWLER->value)->toBe('brawler');
    expect(ClassEnum::WARLOCK->value)->toBe('warlock');
    expect(ClassEnum::WITCH->value)->toBe('witch');
});

test('assassin enum has correct domain mapping', function () {
    $domains = ClassEnum::ASSASSIN->getDomains();
    expect($domains)->toHaveCount(2);
    expect($domains[0])->toBe(DomainEnum::MIDNIGHT);
    expect($domains[1])->toBe(DomainEnum::BLADE);
});

test('brawler enum has correct domain mapping', function () {
    $domains = ClassEnum::BRAWLER->getDomains();
    expect($domains)->toHaveCount(2);
    expect($domains[0])->toBe(DomainEnum::BONE);
    expect($domains[1])->toBe(DomainEnum::VALOR);
});

test('warlock enum has correct domain mapping', function () {
    $domains = ClassEnum::WARLOCK->getDomains();
    expect($domains)->toHaveCount(2);
    expect($domains[0])->toBe(DomainEnum::DREAD);
    expect($domains[1])->toBe(DomainEnum::GRACE);
});

test('witch enum has correct domain mapping', function () {
    $domains = ClassEnum::WITCH->getDomains();
    expect($domains)->toHaveCount(2);
    expect($domains[0])->toBe(DomainEnum::DREAD);
    expect($domains[1])->toBe(DomainEnum::SAGE);
});

test('all classes have exactly two domains each', function () {
    $allClasses = [
        ClassEnum::ASSASSIN,
        ClassEnum::BARD,
        ClassEnum::BRAWLER,
        ClassEnum::DRUID,
        ClassEnum::GUARDIAN,
        ClassEnum::RANGER,
        ClassEnum::ROGUE,
        ClassEnum::SERAPH,
        ClassEnum::SORCERER,
        ClassEnum::WARLOCK,
        ClassEnum::WARRIOR,
        ClassEnum::WITCH,
        ClassEnum::WIZARD,
    ];
    
    foreach ($allClasses as $class) {
        $domains = $class->getDomains();
        expect($domains)->toHaveCount(2, "Class {$class->value} should have exactly 2 domains");
    }
});

test('new classes map to existing JSON data correctly', function () {
    // Load classes JSON to verify enum keys match JSON keys
    $classesPath = resource_path('json/classes.json');
    $classes = json_decode(file_get_contents($classesPath), true);
    
    // Verify all new classes exist in JSON
    expect($classes)->toHaveKey('assassin');
    expect($classes)->toHaveKey('brawler');
    expect($classes)->toHaveKey('warlock');
    expect($classes)->toHaveKey('witch');
    
    // Verify domain consistency between enum and JSON
    expect($classes['assassin']['domains'])->toContain('midnight');
    expect($classes['assassin']['domains'])->toContain('blade');
    
    expect($classes['brawler']['domains'])->toContain('bone');
    expect($classes['brawler']['domains'])->toContain('valor');
    
    expect($classes['warlock']['domains'])->toContain('dread');
    expect($classes['warlock']['domains'])->toContain('grace');
    
    expect($classes['witch']['domains'])->toContain('dread');
    expect($classes['witch']['domains'])->toContain('sage');
});

test('domain enum contains all required domains for new classes', function () {
    // Verify DREAD domain exists for Warlock and Witch
    expect(DomainEnum::DREAD->value)->toBe('dread');
    
    // Verify other domains used by new classes
    expect(DomainEnum::MIDNIGHT->value)->toBe('midnight');
    expect(DomainEnum::BLADE->value)->toBe('blade');
    expect(DomainEnum::BONE->value)->toBe('bone');
    expect(DomainEnum::VALOR->value)->toBe('valor');
    expect(DomainEnum::GRACE->value)->toBe('grace');
    expect(DomainEnum::SAGE->value)->toBe('sage');
});

test('enum cases are in alphabetical order', function () {
    $reflection = new ReflectionEnum(ClassEnum::class);
    $cases = $reflection->getCases();
    $caseNames = array_map(fn($case) => $case->getName(), $cases);
    $sortedCaseNames = $caseNames;
    sort($sortedCaseNames);
    
    expect($caseNames)->toBe($sortedCaseNames, 'Enum cases should be in alphabetical order');
});

test('all playtest classes are marked correctly in JSON', function () {
    $classesPath = resource_path('json/classes.json');
    $classes = json_decode(file_get_contents($classesPath), true);
    
    $playtestClasses = ['assassin', 'brawler', 'warlock', 'witch'];
    
    foreach ($playtestClasses as $className) {
        expect($classes[$className]['playtest']['isPlaytest'])->toBe(true);
        expect($classes[$className]['playtest']['version'])->toBe('1.5');
        expect($classes[$className]['playtest']['label'])->toBe('Void - Playtest v1.5');
    }
});

test('domain combinations are unique across all classes', function () {
    $allClasses = [
        ClassEnum::ASSASSIN,
        ClassEnum::BARD,
        ClassEnum::BRAWLER,
        ClassEnum::DRUID,
        ClassEnum::GUARDIAN,
        ClassEnum::RANGER,
        ClassEnum::ROGUE,
        ClassEnum::SERAPH,
        ClassEnum::SORCERER,
        ClassEnum::WARLOCK,
        ClassEnum::WARRIOR,
        ClassEnum::WITCH,
        ClassEnum::WIZARD,
    ];
    
    $domainCombinations = [];
    
    foreach ($allClasses as $class) {
        $domains = $class->getDomains();
        sort($domains); // Sort to normalize order
        $combinationKey = implode(',', array_map(fn($d) => $d->value, $domains));
        
        if (in_array($combinationKey, $domainCombinations)) {
            $this->fail("Duplicate domain combination found: {$combinationKey} for class {$class->value}");
        }
        
        $domainCombinations[] = $combinationKey;
    }
    
    expect($domainCombinations)->toHaveCount(13, 'All classes should have unique domain combinations');
});