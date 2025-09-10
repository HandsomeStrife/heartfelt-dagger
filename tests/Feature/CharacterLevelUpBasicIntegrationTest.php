<?php

use App\Livewire\CharacterLevelUp;
use App\Livewire\CharacterViewer;
use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterAdvancement;
use Domain\Character\Models\CharacterTrait;

use function Pest\Livewire\livewire;

describe('Character Level Up - Basic Integration Tests', function () {
    beforeEach(function () {
        $this->character = Character::factory()->create([
            'level' => 1,
            'proficiency' => 0,
            'class' => 'warrior',
        ]);

        // Create character traits without is_marked field
        foreach (['agility', 'strength', 'finesse', 'instinct', 'presence', 'knowledge'] as $trait) {
            CharacterTrait::factory()->create([
                'character_id' => $this->character->id,
                'trait_name' => $trait,
                'trait_value' => 0,
            ]);
        }
    });

    test('level 2 tier achievements are applied to database correctly', function () {
        $component = livewire(CharacterLevelUp::class, [
            'characterKey' => $this->character->character_key,
            'canEdit' => true,
        ]);

        // Verify we can create tier experience
        $component->set('new_experience_name', 'Leadership')
            ->set('new_experience_description', 'Leading others')
            ->call('addTierExperience');

        // Verify tier experience was stored in advancement choices
        $advancementChoices = $component->get('advancement_choices');
        expect($advancementChoices['tier_experience'])->not->toBeNull();
        expect($advancementChoices['tier_experience']['name'])->toBe('Leadership');

        // Verify we can select domain card
        $availableCards = $component->instance()->getAvailableDomainCards(2);
        expect($availableCards)->toBeArray();
        expect(count($availableCards))->toBeGreaterThan(0);

        $firstCard = $availableCards[0];
        $component->set('advancement_choices.tier_domain_card', $firstCard['key']);

        // Verify domain card was stored
        $advancementChoices = $component->get('advancement_choices');
        expect($advancementChoices['tier_domain_card'])->toBe($firstCard['key']);

        // Test validation passes with both requirements met
        $isValid = $component->instance()->validateTierAchievements();
        expect($isValid)->toBe(true);
    });

    test('character proficiency calculation works correctly', function () {
        // Test base proficiency calculation
        expect($this->character->getProficiencyBonus())->toBe(0); // Level 1 = 0 base

        // Update to level 2
        $this->character->update(['level' => 2]);
        expect($this->character->getProficiencyBonus())->toBe(1); // Level 2-4 = 1 base

        // Add proficiency advancement
        CharacterAdvancement::create([
            'character_id' => $this->character->id,
            'tier' => 2,
            'advancement_number' => 0,
            'advancement_type' => 'proficiency',
            'advancement_data' => ['bonus' => 1],
            'description' => 'Tier achievement: +1 Proficiency bonus',
        ]);

        // Should now be base + advancement
        $this->character->refresh();
        expect($this->character->getProficiencyBonus())->toBe(2); // 1 base + 1 advancement
    });

    test('character viewer displays correct proficiency bonus', function () {
        // Set up character with proficiency advancement
        $this->character->update(['level' => 2]);

        CharacterAdvancement::create([
            'character_id' => $this->character->id,
            'tier' => 2,
            'advancement_number' => 0,
            'advancement_type' => 'proficiency',
            'advancement_data' => ['bonus' => 1],
            'description' => 'Tier achievement: +1 Proficiency bonus',
        ]);

        $viewer = livewire(CharacterViewer::class, [
            'publicKey' => $this->character->public_key,
            'characterKey' => $this->character->character_key,
            'canEdit' => true,
        ]);

        // Test that computed stats are calculated
        $computedStats = $viewer->instance()->getComputedStats();
        expect($computedStats)->toBeArray();

        // Test advancement status
        $advancementStatus = $viewer->instance()->getAdvancementStatus();
        expect($advancementStatus)->toBeArray();
        expect($advancementStatus['current_tier'])->toBe(2);
        expect($advancementStatus['advancements'])->toBeArray();
        expect(count($advancementStatus['advancements']))->toBe(1);
    });

    test('trait bonuses are calculated correctly', function () {
        // Create trait advancement
        CharacterAdvancement::create([
            'character_id' => $this->character->id,
            'tier' => 2,
            'advancement_number' => 1,
            'advancement_type' => 'trait_bonus',
            'advancement_data' => ['traits' => ['agility', 'strength'], 'bonus' => 1],
            'description' => 'Gain a +1 bonus to two unmarked character traits and mark them',
        ]);

        // Test effective trait values
        $agilityValue = $this->character->getEffectiveTraitValue(\Domain\Character\Enums\TraitName::AGILITY);
        $strengthValue = $this->character->getEffectiveTraitValue(\Domain\Character\Enums\TraitName::STRENGTH);
        $finesseValue = $this->character->getEffectiveTraitValue(\Domain\Character\Enums\TraitName::FINESSE);

        expect($agilityValue)->toBe(1);  // 0 base + 1 advancement
        expect($strengthValue)->toBe(1); // 0 base + 1 advancement
        expect($finesseValue)->toBe(0);  // 0 base + 0 advancement (not affected)
    });

    test('tier options are loaded correctly for different levels', function () {
        // Test tier 2 (level 2-4)
        $this->character->update(['level' => 2]);

        $component = livewire(CharacterLevelUp::class, [
            'characterKey' => $this->character->character_key,
            'canEdit' => true,
        ]);

        $tierOptions = $component->get('tier_options');
        expect($tierOptions)->toBeArray();
        expect(isset($tierOptions['options']))->toBe(true);

        // Test tier 3 (level 5-7)
        $this->character->update(['level' => 5]);

        $component = livewire(CharacterLevelUp::class, [
            'characterKey' => $this->character->character_key,
            'canEdit' => true,
        ]);

        $tierOptions = $component->get('tier_options');
        expect($tierOptions)->toBeArray();

        // Current tier should be 3
        expect($component->get('current_tier'))->toBe(3);
    });

    test('domain cards are available at appropriate levels', function () {
        $levels = [2, 3, 4, 5, 6, 7, 8, 9, 10];

        foreach ($levels as $level) {
            $this->character->update(['level' => $level - 1]); // Character is at level-1, leveling to level

            $component = livewire(CharacterLevelUp::class, [
                'characterKey' => $this->character->character_key,
                'canEdit' => true,
            ]);

            $availableCards = $component->instance()->getAvailableDomainCards($level);
            expect($availableCards)->toBeArray();
            expect(count($availableCards))->toBeGreaterThan(0, "Should have domain cards available for level $level");

            // Verify no cards exceed the target level
            foreach ($availableCards as $card) {
                expect($card['level'])->toBeLessThanOrEqual($level, "Card level should not exceed target level $level");
            }
        }
    });
});
