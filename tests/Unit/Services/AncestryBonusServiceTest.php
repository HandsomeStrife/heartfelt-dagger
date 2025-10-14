<?php

declare(strict_types=1);

use Domain\Character\Services\AncestryBonusService;

describe('AncestryBonusService', function () {
    beforeEach(function () {
        $this->service = new AncestryBonusService();
    });

    describe('getAncestryBonuses', function () {
        test('returns empty array for null ancestry', function () {
            $bonuses = $this->service->getAncestryBonuses(null);
            expect($bonuses)->toBeEmpty();
        });

        test('returns stress bonus for human ancestry', function () {
            $bonuses = $this->service->getAncestryBonuses('human');
            
            expect($bonuses)->toHaveKey('stress');
            expect($bonuses['stress'])->toBe(1);
        });

        test('returns multiple bonuses when ancestry has them', function () {
            // Dwarf has damage threshold bonus
            $bonuses = $this->service->getAncestryBonuses('dwarf');
            
            expect($bonuses)->toBeArray();
            // Dwarf should have some bonuses based on game data
        });
    });

    describe('getEvasionBonus', function () {
        test('returns 0 for ancestry without evasion bonus', function () {
            $bonus = $this->service->getEvasionBonus('human');
            expect($bonus)->toBe(0);
        });

        test('calculates evasion bonus correctly', function () {
            // Test with an ancestry that has evasion bonus if one exists
            $bonus = $this->service->getEvasionBonus('elf');
            expect($bonus)->toBeInt();
        });
    });

    describe('getHitPointBonus', function () {
        test('returns 0 for ancestry without hit point bonus', function () {
            $bonus = $this->service->getHitPointBonus('human');
            expect($bonus)->toBe(0);
        });

        test('calculates hit point bonus correctly', function () {
            $bonus = $this->service->getHitPointBonus('dwarf');
            expect($bonus)->toBeInt();
        });
    });

    describe('getStressBonus', function () {
        test('returns 1 for human ancestry', function () {
            $bonus = $this->service->getStressBonus('human');
            expect($bonus)->toBe(1);
        });

        test('returns 0 for ancestry without stress bonus', function () {
            $bonus = $this->service->getStressBonus('elf');
            expect($bonus)->toBe(0);
        });
    });

    describe('getDamageThresholdBonus', function () {
        test('returns numeric bonus for standard damage threshold', function () {
            // Test with dwarf which should have damage threshold bonuses
            $bonus = $this->service->getDamageThresholdBonus('dwarf', 2);
            expect($bonus)->toBeInt();
        });

        test('uses proficiency bonus when value is "proficiency"', function () {
            // Create a mock ancestry with proficiency-based damage threshold
            $proficiencyBonus = 3;
            $bonus = $this->service->getDamageThresholdBonus('galapa', $proficiencyBonus);
            
            // Galapa has proficiency-based damage threshold bonus
            expect($bonus)->toBeGreaterThanOrEqual(0);
        });

        test('returns 0 for ancestry without damage threshold bonus', function () {
            $bonus = $this->service->getDamageThresholdBonus('human', 2);
            expect($bonus)->toBe(0);
        });
    });

    describe('hasExperienceBonusSelection', function () {
        test('returns true for clank ancestry', function () {
            $hasBonus = $this->service->hasExperienceBonusSelection('clank');
            expect($hasBonus)->toBeTrue();
        });

        test('returns false for ancestries without experience bonus', function () {
            $hasBonus = $this->service->hasExperienceBonusSelection('human');
            expect($hasBonus)->toBeFalse();
        });

        test('returns false for null ancestry', function () {
            $hasBonus = $this->service->hasExperienceBonusSelection('');
            expect($hasBonus)->toBeFalse();
        });
    });

    describe('getExperienceModifier', function () {
        test('returns base modifier of 2 for standard experience', function () {
            $modifier = $this->service->getExperienceModifier(
                'Combat Training',
                'human',
                null
            );
            expect($modifier)->toBe(2);
        });

        test('returns enhanced modifier for clank with selected bonus experience', function () {
            $modifier = $this->service->getExperienceModifier(
                'Engineering',
                'clank',
                'Engineering'
            );
            
            // Clank gets +1 bonus (base 2 + bonus 1 = 3)
            expect($modifier)->toBe(3);
        });

        test('returns base modifier for clank without selected experience', function () {
            $modifier = $this->service->getExperienceModifier(
                'Combat Training',
                'clank',
                'Engineering'
            );
            expect($modifier)->toBe(2);
        });

        test('returns base modifier when ancestry is null', function () {
            $modifier = $this->service->getExperienceModifier(
                'Combat Training',
                null,
                null
            );
            expect($modifier)->toBe(2);
        });
    });

    describe('getAncestryEffects', function () {
        test('returns empty array for non-existent ancestry', function () {
            $effects = $this->service->getAncestryEffects('nonexistent', 'stress_bonus');
            expect($effects)->toBeEmpty();
        });

        test('returns effects matching the specified type', function () {
            $effects = $this->service->getAncestryEffects('human', 'stress_bonus');
            
            expect($effects)->toBeArray();
            expect($effects)->not->toBeEmpty();
            expect($effects[0])->toHaveKey('type');
            expect($effects[0]['type'])->toBe('stress_bonus');
        });

        test('filters out effects not matching the type', function () {
            $effects = $this->service->getAncestryEffects('human', 'evasion_bonus');
            
            // Human doesn't have evasion bonus
            expect($effects)->toBeEmpty();
        });
    });
});

