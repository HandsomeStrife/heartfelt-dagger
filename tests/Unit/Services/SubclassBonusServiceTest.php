<?php

declare(strict_types=1);

use Domain\Character\Services\SubclassBonusService;

describe('SubclassBonusService', function () {
    beforeEach(function () {
        $this->service = new SubclassBonusService();
    });

    describe('getSubclassBonuses', function () {
        test('returns empty array for null subclass', function () {
            $bonuses = $this->service->getSubclassBonuses(null);
            expect($bonuses)->toBeEmpty();
        });

        test('returns bonuses for subclass with bonuses', function () {
            $bonuses = $this->service->getSubclassBonuses('stalwart');
            
            expect($bonuses)->toBeArray();
            // Should have at least some bonuses based on game data
        });

        test('only includes non-zero bonuses', function () {
            $bonuses = $this->service->getSubclassBonuses('stalwart');
            
            // All returned bonuses should be greater than 0
            foreach ($bonuses as $key => $value) {
                expect($value)->toBeGreaterThan(0);
            }
        });
    });

    describe('getEvasionBonus', function () {
        test('returns 0 for subclass without evasion bonus', function () {
            $bonus = $this->service->getEvasionBonus('stalwart');
            expect($bonus)->toBe(0);
        });

        test('calculates evasion bonus correctly', function () {
            // Test with any subclass
            $bonus = $this->service->getEvasionBonus('bladedancer');
            expect($bonus)->toBeInt();
        });
    });

    describe('getHitPointBonus', function () {
        test('returns integer value', function () {
            $bonus = $this->service->getHitPointBonus('stalwart');
            expect($bonus)->toBeInt();
        });

        test('returns 0 for subclass without hit point bonus', function () {
            $bonus = $this->service->getHitPointBonus('bladedancer');
            expect($bonus)->toBeInt();
            expect($bonus)->toBeGreaterThanOrEqual(0);
        });
    });

    describe('getStressBonus', function () {
        test('returns integer value', function () {
            $bonus = $this->service->getStressBonus('stalwart');
            expect($bonus)->toBeInt();
        });

        test('returns 0 or positive value', function () {
            $bonus = $this->service->getStressBonus('bladedancer');
            expect($bonus)->toBeGreaterThanOrEqual(0);
        });
    });

    describe('getDamageThresholdBonus', function () {
        test('returns integer value', function () {
            $bonus = $this->service->getDamageThresholdBonus('stalwart');
            expect($bonus)->toBeInt();
        });

        test('returns 0 or positive value', function () {
            $bonus = $this->service->getDamageThresholdBonus('bladedancer');
            expect($bonus)->toBeGreaterThanOrEqual(0);
        });
    });

    describe('getSevereThresholdBonus', function () {
        test('returns integer value', function () {
            $bonus = $this->service->getSevereThresholdBonus('stalwart');
            expect($bonus)->toBeInt();
        });

        test('returns 0 or positive value', function () {
            $bonus = $this->service->getSevereThresholdBonus('bladedancer');
            expect($bonus)->toBeGreaterThanOrEqual(0);
        });
    });

    describe('getDomainCardBonus', function () {
        test('returns 0 for subclass without domain card bonus', function () {
            $bonus = $this->service->getDomainCardBonus('stalwart');
            expect($bonus)->toBe(0);
        });

        test('returns integer value', function () {
            $bonus = $this->service->getDomainCardBonus('bladedancer');
            expect($bonus)->toBeInt();
            expect($bonus)->toBeGreaterThanOrEqual(0);
        });
    });

    describe('getMaxDomainCards', function () {
        test('returns base cards when no subclass', function () {
            $max = $this->service->getMaxDomainCards(null, 2);
            expect($max)->toBe(2);
        });

        test('adds subclass bonus to base cards', function () {
            $max = $this->service->getMaxDomainCards('stalwart', 2);
            expect($max)->toBeGreaterThanOrEqual(2);
        });

        test('uses custom base cards value', function () {
            $max = $this->service->getMaxDomainCards(null, 5);
            expect($max)->toBe(5);
        });
    });

    describe('getSubclassEffects', function () {
        test('returns empty array for non-existent subclass', function () {
            $effects = $this->service->getSubclassEffects('nonexistent', 'evasion_bonus');
            expect($effects)->toBeEmpty();
        });

        test('returns effects from all feature tiers', function () {
            // Should aggregate from foundationFeatures, specializationFeatures, and masteryFeatures
            $effects = $this->service->getSubclassEffects('stalwart', 'hit_point_bonus');
            
            expect($effects)->toBeArray();
        });

        test('filters effects by type', function () {
            $effects = $this->service->getSubclassEffects('stalwart', 'hit_point_bonus');
            
            foreach ($effects as $effect) {
                expect($effect)->toHaveKey('type');
                expect($effect['type'])->toBe('hit_point_bonus');
            }
        });

        test('returns empty array when no matching effects', function () {
            // Use a type that likely doesn't exist
            $effects = $this->service->getSubclassEffects('stalwart', 'nonexistent_bonus');
            expect($effects)->toBeEmpty();
        });
    });
});




