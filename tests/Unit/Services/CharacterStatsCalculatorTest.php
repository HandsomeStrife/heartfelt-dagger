<?php

declare(strict_types=1);

use Domain\Character\Services\AncestryBonusService;
use Domain\Character\Services\CharacterStatsCalculator;
use Domain\Character\Services\SubclassBonusService;

describe('CharacterStatsCalculator', function () {
    beforeEach(function () {
        $ancestry_service = new AncestryBonusService();
        $subclass_service = new SubclassBonusService();
        $this->calculator = new CharacterStatsCalculator($ancestry_service, $subclass_service);
    });

    describe('calculateStats', function () {
        test('returns empty array for empty class data', function () {
            $stats = $this->calculator->calculateStats(
                classData: [],
                assignedTraits: [],
                selectedEquipment: [],
                ancestryKey: null,
                subclassKey: null
            );

            expect($stats)->toBeEmpty();
        });

        test('calculates base stats from class data', function () {
            $classData = [
                'startingEvasion' => 9,
                'startingHitPoints' => 7,
            ];

            $traits = [
                'agility' => 1,
                'strength' => 2,
                'finesse' => 0,
                'instinct' => 0,
                'presence' => 1,
                'knowledge' => -1,
            ];

            $stats = $this->calculator->calculateStats(
                classData: $classData,
                assignedTraits: $traits,
                selectedEquipment: [],
                ancestryKey: null,
                subclassKey: null
            );

            // Base evasion (9) + agility (1) = 10
            expect($stats['evasion'])->toBe(10);
            
            // Base HP (7)
            expect($stats['hit_points'])->toBe(7);
            
            // Base stress (6)
            expect($stats['stress'])->toBe(6);
        });

        test('includes armor score in calculations', function () {
            $classData = [
                'startingEvasion' => 9,
                'startingHitPoints' => 7,
            ];

            $equipment = [
                [
                    'type' => 'armor',
                    'data' => ['baseScore' => 2],
                ],
            ];

            $stats = $this->calculator->calculateStats(
                classData: $classData,
                assignedTraits: ['agility' => 0, 'strength' => 0, 'finesse' => 0, 'instinct' => 0, 'presence' => 0, 'knowledge' => 0],
                selectedEquipment: $equipment,
                ancestryKey: null,
                subclassKey: null
            );

            expect($stats['armor_score'])->toBe(2);
            
            // Major threshold = armor (2) + 4 = 6
            expect($stats['major_threshold'])->toBe(6);
            
            // Severe threshold = armor (2) + 9 = 11
            expect($stats['severe_threshold'])->toBe(11);
        });

        test('includes ancestry bonuses', function () {
            $classData = [
                'startingEvasion' => 9,
                'startingHitPoints' => 7,
            ];

            $traits = ['agility' => 0, 'strength' => 0, 'finesse' => 0, 'instinct' => 0, 'presence' => 0, 'knowledge' => 0];

            $stats = $this->calculator->calculateStats(
                classData: $classData,
                assignedTraits: $traits,
                selectedEquipment: [],
                ancestryKey: 'human', // Human gets +1 stress
                subclassKey: null
            );

            // Base stress (6) + human bonus (1) = 7
            expect($stats['stress'])->toBe(7);
        });

        test('includes subclass bonuses', function () {
            $classData = [
                'startingEvasion' => 9,
                'startingHitPoints' => 7,
            ];

            $traits = ['agility' => 0, 'strength' => 0, 'finesse' => 0, 'instinct' => 0, 'presence' => 0, 'knowledge' => 0];

            $stats = $this->calculator->calculateStats(
                classData: $classData,
                assignedTraits: $traits,
                selectedEquipment: [],
                ancestryKey: null,
                subclassKey: 'stalwart'
            );

            // Stalwart may have bonuses that affect the stats
            expect($stats)->toHaveKey('evasion');
            expect($stats)->toHaveKey('hit_points');
            expect($stats)->toHaveKey('stress');
        });

        test('includes advancement bonuses', function () {
            $classData = [
                'startingEvasion' => 9,
                'startingHitPoints' => 7,
            ];

            $traits = ['agility' => 0, 'strength' => 0, 'finesse' => 0, 'instinct' => 0, 'presence' => 0, 'knowledge' => 0];

            $advancementBonuses = [
                'evasion' => 2,
                'hit_points' => 3,
                'stress' => 1,
                'proficiency' => 1,
            ];

            $stats = $this->calculator->calculateStats(
                classData: $classData,
                assignedTraits: $traits,
                selectedEquipment: [],
                ancestryKey: null,
                subclassKey: null,
                startingLevel: 1,
                advancementBonuses: $advancementBonuses
            );

            // Base evasion (9) + advancement (2) = 11
            expect($stats['evasion'])->toBe(11);
            
            // Base HP (7) + advancement (3) = 10
            expect($stats['hit_points'])->toBe(10);
            
            // Base stress (6) + advancement (1) = 7
            expect($stats['stress'])->toBe(7);
            
            // Base proficiency (1) + advancement (1) = 2
            expect($stats['proficiency'])->toBe(2);
        });

        test('calculates proficiency by level', function () {
            $classData = [
                'startingEvasion' => 9,
                'startingHitPoints' => 7,
            ];

            $traits = ['agility' => 0, 'strength' => 0, 'finesse' => 0, 'instinct' => 0, 'presence' => 0, 'knowledge' => 0];

            // Level 1: proficiency 1
            $stats = $this->calculator->calculateStats(
                classData: $classData,
                assignedTraits: $traits,
                selectedEquipment: [],
                ancestryKey: null,
                subclassKey: null,
                startingLevel: 1
            );
            expect($stats['proficiency'])->toBe(1);

            // Level 2: proficiency 2
            $stats = $this->calculator->calculateStats(
                classData: $classData,
                assignedTraits: $traits,
                selectedEquipment: [],
                ancestryKey: null,
                subclassKey: null,
                startingLevel: 2
            );
            expect($stats['proficiency'])->toBe(2);

            // Level 5: proficiency 3
            $stats = $this->calculator->calculateStats(
                classData: $classData,
                assignedTraits: $traits,
                selectedEquipment: [],
                ancestryKey: null,
                subclassKey: null,
                startingLevel: 5
            );
            expect($stats['proficiency'])->toBe(3);

            // Level 8: proficiency 4
            $stats = $this->calculator->calculateStats(
                classData: $classData,
                assignedTraits: $traits,
                selectedEquipment: [],
                ancestryKey: null,
                subclassKey: null,
                startingLevel: 8
            );
            expect($stats['proficiency'])->toBe(4);
        });

        test('includes level bonus for damage thresholds', function () {
            $classData = [
                'startingEvasion' => 9,
                'startingHitPoints' => 7,
            ];

            $traits = ['agility' => 0, 'strength' => 0, 'finesse' => 0, 'instinct' => 0, 'presence' => 0, 'knowledge' => 0];

            $equipment = [
                [
                    'type' => 'armor',
                    'data' => ['baseScore' => 2],
                ],
            ];

            // Level 1: no level bonus
            $statsL1 = $this->calculator->calculateStats(
                classData: $classData,
                assignedTraits: $traits,
                selectedEquipment: $equipment,
                ancestryKey: null,
                subclassKey: null,
                startingLevel: 1
            );

            // Level 5: +4 level bonus
            $statsL5 = $this->calculator->calculateStats(
                classData: $classData,
                assignedTraits: $traits,
                selectedEquipment: $equipment,
                ancestryKey: null,
                subclassKey: null,
                startingLevel: 5
            );

            // Level 5 should have 4 more damage threshold than level 1
            expect($statsL5['major_threshold'])->toBe($statsL1['major_threshold'] + 4);
            expect($statsL5['severe_threshold'])->toBe($statsL1['severe_threshold'] + 4);
        });

        test('provides detailed breakdown', function () {
            $classData = [
                'startingEvasion' => 9,
                'startingHitPoints' => 7,
            ];

            $traits = ['agility' => 1, 'strength' => 2, 'finesse' => 0, 'instinct' => 0, 'presence' => 1, 'knowledge' => -1];

            $stats = $this->calculator->calculateStats(
                classData: $classData,
                assignedTraits: $traits,
                selectedEquipment: [],
                ancestryKey: 'human',
                subclassKey: null
            );

            expect($stats)->toHaveKey('detailed');
            expect($stats['detailed'])->toHaveKey('evasion');
            expect($stats['detailed'])->toHaveKey('hit_points');
            expect($stats['detailed'])->toHaveKey('stress');
            expect($stats['detailed'])->toHaveKey('damage_thresholds');
            expect($stats['detailed'])->toHaveKey('proficiency');

            // Check evasion breakdown
            expect($stats['detailed']['evasion'])->toHaveKey('base');
            expect($stats['detailed']['evasion'])->toHaveKey('agility_modifier');
            expect($stats['detailed']['evasion'])->toHaveKey('ancestry_bonus');
            expect($stats['detailed']['evasion'])->toHaveKey('subclass_bonus');
            expect($stats['detailed']['evasion'])->toHaveKey('advancement_bonus');
            expect($stats['detailed']['evasion'])->toHaveKey('total');
        });

        test('ensures damage thresholds have minimum of 1', function () {
            $classData = [
                'startingEvasion' => 9,
                'startingHitPoints' => 7,
            ];

            $traits = ['agility' => 0, 'strength' => 0, 'finesse' => 0, 'instinct' => 0, 'presence' => 0, 'knowledge' => 0];

            $stats = $this->calculator->calculateStats(
                classData: $classData,
                assignedTraits: $traits,
                selectedEquipment: [], // No armor
                ancestryKey: null,
                subclassKey: null
            );

            // Even with 0 armor, thresholds should be at least 1
            expect($stats['major_threshold'])->toBeGreaterThanOrEqual(1);
            expect($stats['severe_threshold'])->toBeGreaterThanOrEqual(1);
        });
    });
});




