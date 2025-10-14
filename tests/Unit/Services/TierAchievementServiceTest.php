<?php

declare(strict_types=1);

use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterExperience;
use Domain\Character\Services\TierAchievementService;

describe('TierAchievementService', function () {
    beforeEach(function () {
        $this->service = app(TierAchievementService::class);
        $this->character = Character::factory()->create([
            'level' => 1,
            'proficiency' => 1,
        ]);
    });

    describe('isTierAchievementLevel', function () {
        test('returns true for level 2', function () {
            expect($this->service->isTierAchievementLevel(2))->toBeTrue();
        });

        test('returns true for level 5', function () {
            expect($this->service->isTierAchievementLevel(5))->toBeTrue();
        });

        test('returns true for level 8', function () {
            expect($this->service->isTierAchievementLevel(8))->toBeTrue();
        });

        test('returns false for level 1', function () {
            expect($this->service->isTierAchievementLevel(1))->toBeFalse();
        });

        test('returns false for level 3', function () {
            expect($this->service->isTierAchievementLevel(3))->toBeFalse();
        });

        test('returns false for level 10', function () {
            expect($this->service->isTierAchievementLevel(10))->toBeFalse();
        });
    });

    describe('getTierAchievements', function () {
        test('returns empty array for non-tier-achievement level', function () {
            $achievements = $this->service->getTierAchievements(1);
            expect($achievements)->toBeEmpty();
        });

        test('returns experience and proficiency for level 2', function () {
            $achievements = $this->service->getTierAchievements(2);

            expect($achievements)->toHaveKey('experience');
            expect($achievements)->toHaveKey('proficiency');
            expect($achievements['experience']['required'])->toBeTrue();
            expect($achievements['proficiency']['increase'])->toBe(1);
            expect($achievements['proficiency']['new_value'])->toBe(2);
        });

        test('includes clear marks for level 5', function () {
            $achievements = $this->service->getTierAchievements(5);

            expect($achievements)->toHaveKey('clear_marks');
            expect($achievements['clear_marks']['description'])->toContain('Clear any marked traits');
        });

        test('includes clear marks for level 8', function () {
            $achievements = $this->service->getTierAchievements(8);

            expect($achievements)->toHaveKey('clear_marks');
        });

        test('does not include clear marks for level 2', function () {
            $achievements = $this->service->getTierAchievements(2);

            expect($achievements)->not->toHaveKey('clear_marks');
        });
    });

    describe('applyTierAchievements', function () {
        test('does nothing for non-tier-achievement level', function () {
            $initialProficiency = $this->character->proficiency;

            $this->service->applyTierAchievements(
                $this->character,
                3,
                ['name' => 'Test Experience', 'description' => 'Test']
            );

            expect($this->character->fresh()->proficiency)->toBe($initialProficiency);
            expect(CharacterExperience::where('character_id', $this->character->id)->count())->toBe(0);
        });

        test('creates experience for tier achievement level', function () {
            $this->service->applyTierAchievements(
                $this->character,
                2,
                ['name' => 'Battle Tactics', 'description' => 'Experience in combat strategy']
            );

            $experience = CharacterExperience::where('character_id', $this->character->id)->first();
            expect($experience)->not->toBeNull();
            expect($experience->experience_name)->toBe('Battle Tactics');
            expect($experience->experience_description)->toBe('Experience in combat strategy');
            expect($experience->modifier)->toBe(2);
        });

        test('updates proficiency at level 2', function () {
            $this->service->applyTierAchievements(
                $this->character,
                2,
                ['name' => 'Test', 'description' => '']
            );

            expect($this->character->fresh()->proficiency)->toBe(2);
        });

        test('updates proficiency at level 5', function () {
            $this->character->update(['proficiency' => 2]);

            $this->service->applyTierAchievements(
                $this->character,
                5,
                ['name' => 'Test', 'description' => '']
            );

            expect($this->character->fresh()->proficiency)->toBe(3);
        });

        test('updates proficiency at level 8', function () {
            $this->character->update(['proficiency' => 3]);

            $this->service->applyTierAchievements(
                $this->character,
                8,
                ['name' => 'Test', 'description' => '']
            );

            expect($this->character->fresh()->proficiency)->toBe(4);
        });

        test('clears marked traits at level 5', function () {
            $this->character->update(['level' => 4]);
            
            // Create marked traits
            $this->character->traits()->create([
                'trait_name' => 'strength',
                'trait_value' => 1,
                'is_marked' => true,
            ]);
            $this->character->traits()->create([
                'trait_name' => 'agility',
                'trait_value' => 1,
                'is_marked' => true,
            ]);

            $this->service->applyTierAchievements(
                $this->character,
                5,
                ['name' => 'Test', 'description' => '']
            );

            $markedCount = $this->character->traits()->where('is_marked', true)->count();
            expect($markedCount)->toBe(0);
        });

        test('clears marked traits at level 8', function () {
            $this->character->update(['level' => 7]);
            
            // Create marked traits
            $this->character->traits()->create([
                'trait_name' => 'strength',
                'trait_value' => 1,
                'is_marked' => true,
            ]);

            $this->service->applyTierAchievements(
                $this->character,
                8,
                ['name' => 'Test', 'description' => '']
            );

            $markedCount = $this->character->traits()->where('is_marked', true)->count();
            expect($markedCount)->toBe(0);
        });

        test('does not clear marked traits at level 2', function () {
            // Create marked traits
            $this->character->traits()->create([
                'trait_name' => 'strength',
                'trait_value' => 1,
                'is_marked' => true,
            ]);

            $this->service->applyTierAchievements(
                $this->character,
                2,
                ['name' => 'Test', 'description' => '']
            );

            $markedCount = $this->character->traits()->where('is_marked', true)->count();
            expect($markedCount)->toBe(1);
        });
    });

    describe('getProficiencyForLevel', function () {
        test('returns 1 for level 1', function () {
            expect($this->service->getProficiencyForLevel(1))->toBe(1);
        });

        test('returns 2 for level 2', function () {
            expect($this->service->getProficiencyForLevel(2))->toBe(2);
        });

        test('returns 2 for level 4', function () {
            expect($this->service->getProficiencyForLevel(4))->toBe(2);
        });

        test('returns 3 for level 5', function () {
            expect($this->service->getProficiencyForLevel(5))->toBe(3);
        });

        test('returns 3 for level 7', function () {
            expect($this->service->getProficiencyForLevel(7))->toBe(3);
        });

        test('returns 4 for level 8', function () {
            expect($this->service->getProficiencyForLevel(8))->toBe(4);
        });

        test('returns 4 for level 10', function () {
            expect($this->service->getProficiencyForLevel(10))->toBe(4);
        });
    });
});

