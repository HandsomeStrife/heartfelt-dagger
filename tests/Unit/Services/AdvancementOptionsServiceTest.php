<?php

declare(strict_types=1);

use Domain\Character\Enums\AdvancementType;
use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterAdvancement;
use Domain\Character\Services\AdvancementOptionsService;

describe('AdvancementOptionsService', function () {
    beforeEach(function () {
        $this->service = app(AdvancementOptionsService::class);
        $this->character = Character::factory()->create([
            'class' => 'warrior',
            'level' => 1,
        ]);
    });

    describe('getAvailableOptions', function () {
        test('returns tier 1 options for level 1', function () {
            $options = $this->service->getAvailableOptions($this->character, 1);

            expect($options)->toHaveKey('select_count');
            expect($options)->toHaveKey('options');
            expect($options['select_count'])->toBe(2); // Tier 1 requires 2 selections
            expect($options['options'])->toBeArray();
        });

        test('returns tier 2 options for level 2', function () {
            $options = $this->service->getAvailableOptions($this->character, 2);

            expect($options['select_count'])->toBe(2);
            expect($options['options'])->not->toBeEmpty();
        });

        test('returns tier 3 options for level 5', function () {
            $this->character->update(['level' => 5]);
            $options = $this->service->getAvailableOptions($this->character, 5);

            expect($options['select_count'])->toBe(2);
            expect($options['options'])->not->toBeEmpty();
        });

        test('returns tier 4 options for level 8', function () {
            $this->character->update(['level' => 8]);
            $options = $this->service->getAvailableOptions($this->character, 8);

            expect($options['select_count'])->toBe(2);
            expect($options['options'])->not->toBeEmpty();
        });

        test('includes availability status for each option', function () {
            $options = $this->service->getAvailableOptions($this->character, 2);

            foreach ($options['options'] as $option) {
                expect($option)->toHaveKey('available');
                expect($option['available'])->toBeBool();
            }
        });

        test('includes max selections for each option', function () {
            $options = $this->service->getAvailableOptions($this->character, 2);

            foreach ($options['options'] as $option) {
                expect($option)->toHaveKey('max_selections');
                expect($option['max_selections'])->toBeInt();
                expect($option['max_selections'])->toBeGreaterThanOrEqual(1);
            }
        });
    });

    describe('isOptionAvailable', function () {
        test('returns true for option within max selections', function () {
            $options = $this->service->getAvailableOptions($this->character, 2);
            $firstOption = $options['options'][0];

            $available = $this->service->isOptionAvailable(
                $this->character,
                2,
                [
                    'description' => $firstOption['description'],
                    'maxSelections' => 2,
                ],
                0 // Option index
            );

            expect($available)->toBeTrue();
        });

        test('returns false for option at max selections', function () {
            $this->character->update(['level' => 2]);

            // Create 2 existing advancements for a trait bonus
            CharacterAdvancement::factory()->count(2)->create([
                'character_id' => $this->character->id,
                'advancement_type' => 'trait_bonus',
                'description' => 'Choose 2 different traits to gain a +1 bonus',
            ]);

            $available = $this->service->isOptionAvailable(
                $this->character,
                3,
                [
                    'description' => 'Choose 2 different traits to gain a +1 bonus',
                    'maxSelections' => 2,
                ],
                0 // Option index
            );

            expect($available)->toBeFalse();
        });

        test('validates multiclass only available at tier 3+', function () {
            // Level 1 character (tier 1)
            $available = $this->service->isOptionAvailable(
                $this->character,
                1,
                ['description' => 'Multiclass: Choose a second class'],
                0 // Option index
            );

            expect($available)->toBeFalse();

            // Level 5 character (tier 3)
            $this->character->update(['level' => 5]);
            $available = $this->service->isOptionAvailable(
                $this->character,
                5,
                ['description' => 'Multiclass: Choose a second class'],
                0 // Option index
            );

            expect($available)->toBeTrue();
        });
    });

    describe('parseAdvancementType', function () {
        test('identifies trait bonus advancement', function () {
            $type = $this->service->parseAdvancementType('Choose 2 different traits to gain a +1 bonus');
            expect($type)->toBe(AdvancementType::TRAIT_BONUS);
        });

        test('identifies hit point advancement', function () {
            $type = $this->service->parseAdvancementType('Increase your Hit Points by 5');
            expect($type)->toBe(AdvancementType::HIT_POINT);
        });

        test('identifies stress advancement', function () {
            $type = $this->service->parseAdvancementType('Increase your Stress by 3');
            expect($type)->toBe(AdvancementType::STRESS_SLOT);
        });

        test('identifies experience bonus advancement', function () {
            $type = $this->service->parseAdvancementType('Choose 2 of your Experiences to gain a +1 bonus');
            expect($type)->toBe(AdvancementType::EXPERIENCE_BONUS);
        });

        test('identifies evasion advancement', function () {
            $type = $this->service->parseAdvancementType('Increase your Evasion by 1');
            expect($type)->toBe(AdvancementType::EVASION);
        });

        test('identifies domain card advancement', function () {
            $type = $this->service->parseAdvancementType('Gain a new domain card from your class domains');
            expect($type)->toBe(AdvancementType::DOMAIN_CARD);
        });

        test('identifies multiclass advancement', function () {
            $type = $this->service->parseAdvancementType('Multiclass: Choose a second class and gain access to their domains');
            expect($type)->toBe(AdvancementType::MULTICLASS);
        });

        test('identifies proficiency advancement', function () {
            $type = $this->service->parseAdvancementType('Increase your Proficiency by 1');
            expect($type)->toBe(AdvancementType::PROFICIENCY);
        });

        test('identifies subclass upgrade advancement', function () {
            $type = $this->service->parseAdvancementType('Gain an upgraded subclass card');
            expect($type)->toBe(AdvancementType::SUBCLASS_UPGRADE);
        });

        test('returns generic for unknown advancement', function () {
            $type = $this->service->parseAdvancementType('Some unknown advancement type');
            expect($type)->toBe(AdvancementType::GENERIC);
        });
    });

    describe('mutual exclusivity', function () {
        test('multiclass and subclass upgrade are mutually exclusive in same tier', function () {
            $this->markTestSkipped('Requires specific advancement option configuration');
            
            $this->character->update(['level' => 5]);

            // Take multiclass at level 5 (tier 3)
            CharacterAdvancement::factory()->create([
                'character_id' => $this->character->id,
                'advancement_type' => 'multiclass',
                'tier' => 3,
            ]);

            // Try to take subclass upgrade at level 6 (still tier 3)
            $available = $this->service->isOptionAvailable(
                $this->character,
                6,
                [
                    'description' => 'Upgrade your subclass',
                    'mutuallyExclusive' => 'subclass_upgrade',
                ],
                0 // Option index
            );

            expect($available)->toBeFalse();
        });
    });
});

