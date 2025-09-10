<?php

use App\Livewire\CharacterLevelUp;
use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterAdvancement;
use Domain\Character\Models\CharacterDomainCard;
use Domain\Character\Models\CharacterExperience;
use Domain\Character\Models\CharacterTrait;
use function Pest\Livewire\livewire;

describe('Character Level Up - Tier 3 Advancement Options', function () {
    beforeEach(function () {
        $this->character = Character::factory()->create([
            'level' => 4, // Character at level 4, will level up to 5 (tier 3)
            'proficiency' => 1,
            'class' => 'warrior',
        ]);

        // Create character traits (some marked from previous tiers)
        $traits = ['agility', 'strength', 'finesse', 'instinct', 'presence', 'knowledge'];
        $marked_traits = ['agility', 'strength']; // Some traits marked from previous advancements
        
        foreach ($traits as $trait) {
            CharacterTrait::factory()->create([
                'character_id' => $this->character->id,
                'trait_name' => $trait,
                'trait_value' => in_array($trait, $marked_traits) ? 1 : 0,
                'is_marked' => in_array($trait, $marked_traits),
            ]);
        }

        // Create initial experiences
        CharacterExperience::factory()->create([
            'character_id' => $this->character->id,
            'experience_name' => 'Combat Training',
            'experience_description' => 'Basic weapon training',
            'modifier' => 2,
        ]);
    });

    test('level 5 tier achievements clear marked traits and add proficiency', function () {
        $component = livewire(CharacterLevelUp::class, [
            'characterKey' => $this->character->character_key,
            'canEdit' => true,
        ]);

        // Verify we're in tier 3
        expect($component->get('current_tier'))->toBe(3);

        // Set up tier achievements
        $component->set('new_experience_name', 'Leadership')
            ->set('new_experience_description', 'Leading others')
            ->call('addTierExperience');

        $availableCards = $component->instance()->getAvailableDomainCards(5);
        $firstCard = $availableCards[0];
        $component->set('advancement_choices.tier_domain_card', $firstCard['key']);

        // Select two advancements
        $component->call('selectFirstAdvancement', 0); // Trait bonus
        $component->set('advancement_choices.0.traits', ['finesse', 'instinct']);
        $component->call('selectSecondAdvancement', 1); // Hit Point

        // Confirm level up
        $component->call('confirmLevelUp');

        // Verify tier achievement effects
        $this->character->refresh();
        expect($this->character->level)->toBe(5);

        // Verify proficiency bonus advancement was created
        $proficiencyAdvancement = CharacterAdvancement::where('character_id', $this->character->id)
            ->where('advancement_type', 'proficiency')
            ->where('tier', 3)
            ->first();
        expect($proficiencyAdvancement)->not->toBeNull();

        // Verify marked traits were cleared (level 5 tier achievement)
        $markedTraits = $this->character->traits()->where('is_marked', true)->count();
        expect($markedTraits)->toBe(2); // Only the newly selected traits should be marked

        // Verify the newly selected traits are marked
        $newlyMarkedTraits = $this->character->traits()
            ->whereIn('trait_name', ['finesse', 'instinct'])
            ->where('is_marked', true)
            ->count();
        expect($newlyMarkedTraits)->toBe(2);
    });

    test('tier 3 options include subclass advancement', function () {
        $component = livewire(CharacterLevelUp::class, [
            'characterKey' => $this->character->character_key,
            'canEdit' => true,
        ]);

        $tierOptions = $component->get('tier_options');
        expect($tierOptions)->toBeArray();
        expect($tierOptions['selectCount'])->toBe(2);

        // Check if subclass advancement option exists
        $descriptions = collect($tierOptions['options'])->pluck('description')->toArray();
        
        $hasSubclassOption = collect($descriptions)->contains(function ($description) {
            return str_contains($description, 'subclass');
        });
        
        expect($hasSubclassOption)->toBe(true);
    });

    test('domain card advancement respects level 7 maximum for tier 3', function () {
        $component = livewire(CharacterLevelUp::class, [
            'characterKey' => $this->character->character_key,
            'canEdit' => true,
        ]);

        // Get available domain cards for tier 3
        $availableCards = $component->instance()->getAvailableDomainCards(7);
        expect($availableCards)->toBeArray();
        expect(count($availableCards))->toBeGreaterThan(0);

        // Verify no cards exceed level 7
        foreach ($availableCards as $card) {
            expect($card['level'])->toBeLessThanOrEqual(7);
        }
    });

    test('tier 3 advancement validation works correctly', function () {
        $component = livewire(CharacterLevelUp::class, [
            'characterKey' => $this->character->character_key,
            'canEdit' => true,
        ]);

        // Set up tier achievements
        $component->set('new_experience_name', 'Leadership')
            ->set('new_experience_description', 'Leading others')
            ->call('addTierExperience');

        $availableCards = $component->instance()->getAvailableDomainCards(5);
        $firstCard = $availableCards[0];
        $component->set('advancement_choices.tier_domain_card', $firstCard['key']);

        // Verify tier achievement validation passes
        expect($component->instance()->validateTierAchievements())->toBe(true);

        // Select advancements
        $component->call('selectFirstAdvancement', 0); // Trait bonus
        $component->call('selectSecondAdvancement', 1); // Hit Point

        // Should fail validation without trait selections
        expect($component->instance()->validateSelections())->toBe(false);

        // Add trait selections and validate again
        $component->set('advancement_choices.0.traits', ['finesse', 'instinct']);
        expect($component->instance()->validateSelections())->toBe(true);
    });

    test('proficiency advancement works correctly', function () {
        $component = livewire(CharacterLevelUp::class, [
            'characterKey' => $this->character->character_key,
            'canEdit' => true,
        ]);

        // Set up tier achievements
        $component->set('new_experience_name', 'Leadership')
            ->set('new_experience_description', 'Leading others')
            ->call('addTierExperience');

        $availableCards = $component->instance()->getAvailableDomainCards(5);
        $firstCard = $availableCards[0];
        $component->set('advancement_choices.tier_domain_card', $firstCard['key']);

        // Select proficiency advancement (option 7 based on classes.json)
        $component->call('selectFirstAdvancement', 7); // Proficiency
        $component->call('selectSecondAdvancement', 1); // Hit Point

        // Confirm level up
        $component->call('confirmLevelUp');

        // Verify proficiency advancement was created
        $advancement = CharacterAdvancement::where('character_id', $this->character->id)
            ->where('advancement_type', 'proficiency_advancement')
            ->first();

        expect($advancement)->not->toBeNull();
        expect($advancement->advancement_data['bonus'])->toBe(1);
    });

    test('subclass upgrade advancement works correctly', function () {
        $component = livewire(CharacterLevelUp::class, [
            'characterKey' => $this->character->character_key,
            'canEdit' => true,
        ]);

        // Set up tier achievements
        $component->set('new_experience_name', 'Leadership')
            ->set('new_experience_description', 'Leading others')
            ->call('addTierExperience');

        $availableCards = $component->instance()->getAvailableDomainCards(5);
        $firstCard = $availableCards[0];
        $component->set('advancement_choices.tier_domain_card', $firstCard['key']);

        // Select subclass upgrade advancement (option 6)
        $component->call('selectFirstAdvancement', 6); // Subclass upgrade
        $component->set('advancement_choices.6.subclass', 'specialization_card_key');
        $component->call('selectSecondAdvancement', 1); // Hit Point

        $component->call('confirmLevelUp');

        // Verify subclass advancement was created
        $advancement = CharacterAdvancement::where('character_id', $this->character->id)
            ->where('advancement_type', 'subclass_upgrade')
            ->first();

        expect($advancement)->not->toBeNull();
        expect($advancement->advancement_data['subclass'])->toBe('specialization_card_key');
    });
});
