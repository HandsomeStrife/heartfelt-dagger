<?php

declare(strict_types=1);

use Domain\Character\Data\CharacterBuilderData;

describe('CharacterBuilderData Advancement Methods', function () {
    beforeEach(function () {
        $this->data = new CharacterBuilderData(
            selected_class: 'warrior',
            selected_ancestry: 'human',
            assigned_traits: [
                'agility' => 1,
                'strength' => 2,
                'finesse' => 0,
                'instinct' => 0,
                'presence' => 1,
                'knowledge' => -1,
            ],
        );
    });

    describe('requiresAdvancementSelection', function () {
        test('returns false for level 1 character', function () {
            $this->data->starting_level = 1;
            expect($this->data->requiresAdvancementSelection())->toBeFalse();
        });

        test('returns true for level 2+ character', function () {
            $this->data->starting_level = 2;
            expect($this->data->requiresAdvancementSelection())->toBeTrue();

            $this->data->starting_level = 5;
            expect($this->data->requiresAdvancementSelection())->toBeTrue();

            $this->data->starting_level = 10;
            expect($this->data->requiresAdvancementSelection())->toBeTrue();
        });
    });

    describe('validateAdvancementSelections', function () {
        test('returns no errors for level 1 character', function () {
            $this->data->starting_level = 1;
            $errors = $this->data->validateAdvancementSelections();
            expect($errors)->toBeEmpty();
        });

        test('detects missing tier achievement experience at level 2', function () {
            $this->data->starting_level = 2;
            $errors = $this->data->validateAdvancementSelections();
            
            expect($errors)->toHaveKey(2);
            expect($errors[2])->toContain('Missing tier achievement experience');
        });

        test('detects missing domain card', function () {
            $this->data->starting_level = 2;
            $this->data->creation_tier_experiences[2] = ['name' => 'Test Experience'];
            
            $errors = $this->data->validateAdvancementSelections();
            
            expect($errors)->toHaveKey(2);
            expect($errors[2])->toContain('Missing domain card selection');
        });

        test('detects missing advancements', function () {
            $this->data->starting_level = 2;
            $this->data->creation_tier_experiences[2] = ['name' => 'Test Experience'];
            $this->data->creation_domain_cards[2] = 'blade-strike';
            
            $errors = $this->data->validateAdvancementSelections();
            
            expect($errors)->toHaveKey(2);
            expect($errors[2])->toContain('Missing advancement selections');
        });

        test('detects incorrect advancement count', function () {
            $this->data->starting_level = 2;
            $this->data->creation_tier_experiences[2] = ['name' => 'Test Experience'];
            $this->data->creation_domain_cards[2] = 'blade-strike';
            $this->data->creation_advancements[2] = [
                ['type' => 'hit_point'],
            ];
            
            $errors = $this->data->validateAdvancementSelections();
            
            expect($errors)->toHaveKey(2);
            expect($errors[2][0])->toContain('Must select exactly 2 advancements');
        });

        test('passes validation with complete selections', function () {
            $this->data->starting_level = 2;
            $this->data->creation_tier_experiences[2] = ['name' => 'Test Experience'];
            $this->data->creation_domain_cards[2] = 'blade-strike';
            $this->data->creation_advancements[2] = [
                ['type' => 'hit_point'],
                ['type' => 'stress'],
            ];
            
            $errors = $this->data->validateAdvancementSelections();
            
            expect($errors)->toBeEmpty();
        });

        test('validates multiple levels', function () {
            $this->data->starting_level = 5;
            
            // Complete level 2
            $this->data->creation_tier_experiences[2] = ['name' => 'Level 2 Experience'];
            $this->data->creation_domain_cards[2] = 'blade-strike';
            $this->data->creation_advancements[2] = [
                ['type' => 'hit_point'],
                ['type' => 'stress'],
            ];
            
            // Incomplete level 3
            $this->data->creation_domain_cards[3] = 'blade-strike';
            
            // Complete level 4
            $this->data->creation_domain_cards[4] = 'blade-strike';
            $this->data->creation_advancements[4] = [
                ['type' => 'hit_point'],
                ['type' => 'evasion'],
            ];
            
            // Complete level 5
            $this->data->creation_tier_experiences[5] = ['name' => 'Level 5 Experience'];
            $this->data->creation_domain_cards[5] = 'blade-strike';
            $this->data->creation_advancements[5] = [
                ['type' => 'hit_point'],
                ['type' => 'stress'],
            ];
            
            $errors = $this->data->validateAdvancementSelections();
            
            expect($errors)->toHaveKey(3); // Level 3 is incomplete
            expect($errors)->not->toHaveKey(2); // Level 2 is complete
            expect($errors)->not->toHaveKey(4); // Level 4 is complete
            expect($errors)->not->toHaveKey(5); // Level 5 is complete
        });
    });

    describe('getAdvancementProgress', function () {
        test('returns 100% complete for level 1 character', function () {
            $this->data->starting_level = 1;
            $progress = $this->data->getAdvancementProgress();
            
            expect($progress['levels_requiring_advancements'])->toBe(0);
            expect($progress['levels_completed'])->toBe(0);
            expect($progress['percentage'])->toBe(100);
            expect($progress['is_complete'])->toBeTrue();
        });

        test('tracks single level progress', function () {
            $this->data->starting_level = 2;
            $progress = $this->data->getAdvancementProgress();
            
            expect($progress['levels_requiring_advancements'])->toBe(1);
            expect($progress['levels_completed'])->toBe(0);
            expect($progress['percentage'])->toBe(0.0);
            expect($progress['is_complete'])->toBeFalse();
            
            // Complete level 2
            $this->data->creation_tier_experiences[2] = ['name' => 'Test'];
            $this->data->creation_domain_cards[2] = 'blade-strike';
            $this->data->creation_advancements[2] = [
                ['type' => 'hit_point'],
                ['type' => 'stress'],
            ];
            
            $progress = $this->data->getAdvancementProgress();
            expect($progress['levels_completed'])->toBe(1);
            expect($progress['percentage'])->toBe(100.0);
            expect($progress['is_complete'])->toBeTrue();
        });

        test('tracks multi-level progress', function () {
            $this->data->starting_level = 5;
            
            // Complete 2 out of 4 levels (levels 2-5)
            $this->data->creation_tier_experiences[2] = ['name' => 'Level 2'];
            $this->data->creation_domain_cards[2] = 'blade-strike';
            $this->data->creation_advancements[2] = [
                ['type' => 'hit_point'],
                ['type' => 'stress'],
            ];
            
            $this->data->creation_domain_cards[3] = 'blade-strike';
            $this->data->creation_advancements[3] = [
                ['type' => 'hit_point'],
                ['type' => 'evasion'],
            ];
            
            $progress = $this->data->getAdvancementProgress();
            
            expect($progress['levels_requiring_advancements'])->toBe(4);
            expect($progress['levels_completed'])->toBe(2);
            expect($progress['percentage'])->toBe(50.0);
            expect($progress['is_complete'])->toBeFalse();
        });
    });

    describe('calculateAdvancementBonuses', function () {
        test('returns zero bonuses for level 1 character', function () {
            $this->data->starting_level = 1;
            $bonuses = $this->data->calculateAdvancementBonuses();
            
            expect($bonuses['evasion'])->toBe(0);
            expect($bonuses['hit_points'])->toBe(0);
            expect($bonuses['stress'])->toBe(0);
            expect($bonuses['experiences'])->toBe(0);
            expect($bonuses['proficiency'])->toBe(0);
            expect($bonuses['trait_bonuses'])->toBeEmpty();
        });

        test('counts hit point advancements', function () {
            $this->data->starting_level = 3;
            $this->data->creation_advancements = [
                2 => [
                    ['type' => 'hit_point'],
                    ['type' => 'hit_point'],
                ],
                3 => [
                    ['type' => 'hit_point'],
                    ['type' => 'stress'],
                ],
            ];
            
            $bonuses = $this->data->calculateAdvancementBonuses();
            expect($bonuses['hit_points'])->toBe(3);
        });

        test('counts stress advancements', function () {
            $this->data->starting_level = 3;
            $this->data->creation_advancements = [
                2 => [
                    ['type' => 'stress'],
                    ['type' => 'hit_point'],
                ],
                3 => [
                    ['type' => 'stress'],
                    ['type' => 'stress'],
                ],
            ];
            
            $bonuses = $this->data->calculateAdvancementBonuses();
            expect($bonuses['stress'])->toBe(3);
        });

        test('counts evasion advancements', function () {
            $this->data->starting_level = 3;
            $this->data->creation_advancements = [
                2 => [
                    ['type' => 'evasion'],
                    ['type' => 'hit_point'],
                ],
                3 => [
                    ['type' => 'evasion'],
                    ['type' => 'stress'],
                ],
            ];
            
            $bonuses = $this->data->calculateAdvancementBonuses();
            expect($bonuses['evasion'])->toBe(2);
        });

        test('counts trait bonuses', function () {
            $this->data->starting_level = 3;
            $this->data->creation_advancements = [
                2 => [
                    ['type' => 'trait_bonus', 'traits' => ['strength', 'agility']],
                    ['type' => 'hit_point'],
                ],
                3 => [
                    ['type' => 'trait_bonus', 'traits' => ['strength', 'finesse']],
                    ['type' => 'stress'],
                ],
            ];
            
            $bonuses = $this->data->calculateAdvancementBonuses();
            expect($bonuses['trait_bonuses']['strength'])->toBe(2);
            expect($bonuses['trait_bonuses']['agility'])->toBe(1);
            expect($bonuses['trait_bonuses']['finesse'])->toBe(1);
        });

        test('counts experience bonuses', function () {
            $this->data->starting_level = 3;
            $this->data->creation_advancements = [
                2 => [
                    ['type' => 'experience_bonus'],
                    ['type' => 'hit_point'],
                ],
                3 => [
                    ['type' => 'experience_bonus'],
                    ['type' => 'stress'],
                ],
            ];
            
            $bonuses = $this->data->calculateAdvancementBonuses();
            expect($bonuses['experiences'])->toBe(2);
        });

        test('counts proficiency bonuses', function () {
            $this->data->starting_level = 3;
            $this->data->creation_advancements = [
                2 => [
                    ['type' => 'proficiency'],
                    ['type' => 'hit_point'],
                ],
                3 => [
                    ['type' => 'proficiency'],
                    ['type' => 'stress'],
                ],
            ];
            
            $bonuses = $this->data->calculateAdvancementBonuses();
            expect($bonuses['proficiency'])->toBe(2);
        });

        test('counts mixed advancements correctly', function () {
            $this->data->starting_level = 4;
            $this->data->creation_advancements = [
                2 => [
                    ['type' => 'hit_point'],
                    ['type' => 'trait_bonus', 'traits' => ['strength', 'agility']],
                ],
                3 => [
                    ['type' => 'stress'],
                    ['type' => 'evasion'],
                ],
                4 => [
                    ['type' => 'experience_bonus'],
                    ['type' => 'proficiency'],
                ],
            ];
            
            $bonuses = $this->data->calculateAdvancementBonuses();
            expect($bonuses['hit_points'])->toBe(1);
            expect($bonuses['stress'])->toBe(1);
            expect($bonuses['evasion'])->toBe(1);
            expect($bonuses['experiences'])->toBe(1);
            expect($bonuses['proficiency'])->toBe(1);
            expect($bonuses['trait_bonuses']['strength'])->toBe(1);
            expect($bonuses['trait_bonuses']['agility'])->toBe(1);
        });
    });

    describe('calculateFinalLevel', function () {
        test('returns starting level', function () {
            $this->data->starting_level = 1;
            expect($this->data->calculateFinalLevel())->toBe(1);

            $this->data->starting_level = 5;
            expect($this->data->calculateFinalLevel())->toBe(5);

            $this->data->starting_level = 10;
            expect($this->data->calculateFinalLevel())->toBe(10);
        });
    });

    describe('getEffectiveTraitValues', function () {
        test('returns base traits for level 1 character', function () {
            $this->data->starting_level = 1;
            $effective = $this->data->getEffectiveTraitValues();
            
            expect($effective['agility'])->toBe(1);
            expect($effective['strength'])->toBe(2);
            expect($effective['finesse'])->toBe(0);
            expect($effective['instinct'])->toBe(0);
            expect($effective['presence'])->toBe(1);
            expect($effective['knowledge'])->toBe(-1);
        });

        test('applies trait bonuses from advancements', function () {
            $this->data->starting_level = 3;
            $this->data->creation_advancements = [
                2 => [
                    ['type' => 'trait_bonus', 'traits' => ['strength', 'agility']],
                    ['type' => 'hit_point'],
                ],
                3 => [
                    ['type' => 'trait_bonus', 'traits' => ['strength', 'finesse']],
                    ['type' => 'stress'],
                ],
            ];
            
            $effective = $this->data->getEffectiveTraitValues();
            
            expect($effective['agility'])->toBe(2); // 1 + 1
            expect($effective['strength'])->toBe(4); // 2 + 2
            expect($effective['finesse'])->toBe(1); // 0 + 1
            expect($effective['instinct'])->toBe(0); // 0 + 0
            expect($effective['presence'])->toBe(1); // 1 + 0
            expect($effective['knowledge'])->toBe(-1); // -1 + 0
        });
    });

    describe('getComputedStats with advancements', function () {
        test('includes advancement bonuses in stats', function () {
            $this->data->starting_level = 3;
            $this->data->creation_advancements = [
                2 => [
                    ['type' => 'hit_point'],
                    ['type' => 'stress'],
                ],
                3 => [
                    ['type' => 'evasion'],
                    ['type' => 'proficiency'],
                ],
            ];
            
            $classData = [
                'startingEvasion' => 9,
                'startingHitPoints' => 7,
            ];
            
            $stats = $this->data->getComputedStats($classData);
            
            // Base + agility (1) + advancement (1) = 11
            expect($stats['evasion'])->toBe(11);
            
            // Base (7) + advancement (1) = 8
            expect($stats['hit_points'])->toBe(8);
            
            // Base (6) + human ancestry (+1) + advancement (1) = 8
            expect($stats['stress'])->toBe(8);
            
            // Level 3 proficiency (2) + advancement (1) = 3
            expect($stats['proficiency'])->toBe(3);
        });

        test('calculates proficiency by level', function () {
            $classData = [
                'startingEvasion' => 9,
                'startingHitPoints' => 7,
            ];
            
            $this->data->starting_level = 1;
            expect($this->data->getComputedStats($classData)['proficiency'])->toBe(1);
            
            $this->data->starting_level = 2;
            expect($this->data->getComputedStats($classData)['proficiency'])->toBe(2);
            
            $this->data->starting_level = 5;
            expect($this->data->getComputedStats($classData)['proficiency'])->toBe(3);
            
            $this->data->starting_level = 8;
            expect($this->data->getComputedStats($classData)['proficiency'])->toBe(4);
            
            $this->data->starting_level = 10;
            expect($this->data->getComputedStats($classData)['proficiency'])->toBe(4);
        });

        test('applies level damage threshold bonuses', function () {
            $classData = [
                'startingEvasion' => 9,
                'startingHitPoints' => 7,
            ];
            
            $this->data->starting_level = 1;
            $stats = $this->data->getComputedStats($classData);
            // Level 1: armor(0) + base(4) + level_bonus(0) = 4
            expect($stats['major_threshold'])->toBe(4);
            
            $this->data->starting_level = 3;
            $stats = $this->data->getComputedStats($classData);
            // Level 3: armor(0) + base(4) + level_bonus(2) = 6
            expect($stats['major_threshold'])->toBe(6);
            
            $this->data->starting_level = 5;
            $stats = $this->data->getComputedStats($classData);
            // Level 5: armor(0) + base(4) + level_bonus(4) = 8
            expect($stats['major_threshold'])->toBe(8);
        });

        test('includes detailed breakdown with advancement bonuses', function () {
            $this->data->starting_level = 3;
            $this->data->creation_advancements = [
                2 => [
                    ['type' => 'hit_point'],
                    ['type' => 'stress'],
                ],
                3 => [
                    ['type' => 'evasion'],
                    ['type' => 'proficiency'],
                ],
            ];
            
            $classData = [
                'startingEvasion' => 9,
                'startingHitPoints' => 7,
            ];
            
            $stats = $this->data->getComputedStats($classData);
            
            expect($stats['detailed']['evasion']['advancement_bonus'])->toBe(1);
            expect($stats['detailed']['hit_points']['advancement_bonus'])->toBe(1);
            expect($stats['detailed']['stress']['advancement_bonus'])->toBe(1);
            expect($stats['detailed']['proficiency']['level_proficiency'])->toBe(2);
            expect($stats['detailed']['proficiency']['advancement_bonus'])->toBe(1);
            expect($stats['detailed']['proficiency']['total'])->toBe(3);
            expect($stats['detailed']['damage_thresholds']['level_bonus'])->toBe(2);
        });

        test('includes level in stats output', function () {
            $this->data->starting_level = 5;
            
            $classData = [
                'startingEvasion' => 9,
                'startingHitPoints' => 7,
            ];
            
            $stats = $this->data->getComputedStats($classData);
            expect($stats['level'])->toBe(5);
        });
    });
});

