<?php

use App\Livewire\CharacterLevelUp;
use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterAdvancement;
use Domain\Character\Models\CharacterDomainCard;
use Domain\Character\Models\CharacterExperience;
use Domain\Character\Models\CharacterTrait;
use function Pest\Livewire\livewire;

describe('Character Level Up - Tier 4 Advancement Options', function () {
    beforeEach(function () {
        $this->character = Character::factory()->create([
            'level' => 7, // Character at level 7, will level up to 8 (tier 4)
            'proficiency' => 2,
            'class' => 'warrior',
        ]);

        // Create character traits (some marked from previous tiers)
        $traits = ['agility', 'strength', 'finesse', 'instinct', 'presence', 'knowledge'];
        $marked_traits = ['agility', 'strength', 'finesse']; // Multiple traits marked from previous advancements
        
        foreach ($traits as $trait) {
            CharacterTrait::factory()->create([
                'character_id' => $this->character->id,
                'trait_name' => $trait,
                'trait_value' => in_array($trait, $marked_traits) ? 1 : 0,
                'is_marked' => in_array($trait, $marked_traits),
            ]);
        }

        // Create multiple experiences from previous levels
        CharacterExperience::factory()->create([
            'character_id' => $this->character->id,
            'experience_name' => 'Combat Training',
            'experience_description' => 'Basic weapon training',
            'modifier' => 2,
        ]);
        
        CharacterExperience::factory()->create([
            'character_id' => $this->character->id,
            'experience_name' => 'Leadership',
            'experience_description' => 'Leading others',
            'modifier' => 2,
        ]);
    });

    test('level 8 tier achievements clear marked traits and add proficiency', function () {
        $component = livewire(CharacterLevelUp::class, [
            'characterKey' => $this->character->character_key,
            'canEdit' => true,
        ]);

        // Verify we're in tier 4
        expect($component->get('current_tier'))->toBe(4);

        // Set up tier achievements
        $component->set('new_experience_name', 'Strategic Planning')
            ->set('new_experience_description', 'Advanced tactical planning')
            ->call('addTierExperience');

        $availableCards = $component->instance()->getAvailableDomainCards(8);
        $firstCard = $availableCards[0];
        $component->set('advancement_choices.tier_domain_card', $firstCard['key']);

        // Select two advancements
        $component->call('selectFirstAdvancement', 0); // Trait bonus
        $component->set('advancement_choices.0.traits', ['instinct', 'presence']);
        $component->call('selectSecondAdvancement', 1); // Hit Point

        // Confirm level up
        $component->call('confirmLevelUp');

        // Verify tier achievement effects
        $this->character->refresh();
        expect($this->character->level)->toBe(8);

        // Verify proficiency bonus advancement was created
        $proficiencyAdvancement = CharacterAdvancement::where('character_id', $this->character->id)
            ->where('advancement_type', 'proficiency')
            ->where('tier', 4)
            ->first();
        expect($proficiencyAdvancement)->not->toBeNull();

        // Verify marked traits were cleared (level 8 tier achievement)
        $markedTraits = $this->character->traits()->where('is_marked', true)->count();
        expect($markedTraits)->toBe(2); // Only the newly selected traits should be marked

        // Verify the newly selected traits are marked
        $newlyMarkedTraits = $this->character->traits()
            ->whereIn('trait_name', ['instinct', 'presence'])
            ->where('is_marked', true)
            ->count();
        expect($newlyMarkedTraits)->toBe(2);
    });

    test('tier 4 has all advancement options available', function () {
        $component = livewire(CharacterLevelUp::class, [
            'characterKey' => $this->character->character_key,
            'canEdit' => true,
        ]);

        $tierOptions = $component->get('tier_options');
        expect($tierOptions)->toBeArray();
        expect($tierOptions['selectCount'])->toBe(2);

        // Tier 4 should have all advancement types
        $descriptions = collect($tierOptions['options'])->pluck('description')->toArray();
        
        // Check for key advancement types
        $hasTraitBonus = collect($descriptions)->contains(fn($desc) => str_contains($desc, 'trait'));
        $hasHitPoint = collect($descriptions)->contains(fn($desc) => str_contains($desc, 'Hit Point'));
        $hasStress = collect($descriptions)->contains(fn($desc) => str_contains($desc, 'Stress'));
        $hasEvasion = collect($descriptions)->contains(fn($desc) => str_contains($desc, 'Evasion'));
        $hasDomainCard = collect($descriptions)->contains(fn($desc) => str_contains($desc, 'domain card'));
        $hasExperience = collect($descriptions)->contains(fn($desc) => str_contains($desc, 'Experience'));
        $hasSubclass = collect($descriptions)->contains(fn($desc) => str_contains($desc, 'subclass'));
        $hasProficiency = collect($descriptions)->contains(fn($desc) => str_contains($desc, 'Proficiency'));
        $hasMulticlass = collect($descriptions)->contains(fn($desc) => str_contains($desc, 'Multiclass'));
        
        expect($hasTraitBonus)->toBe(true);
        expect($hasHitPoint)->toBe(true);
        expect($hasStress)->toBe(true);
        expect($hasEvasion)->toBe(true);
        expect($hasDomainCard)->toBe(true);
        expect($hasExperience)->toBe(true);
        expect($hasSubclass)->toBe(true);
        expect($hasProficiency)->toBe(true);
        expect($hasMulticlass)->toBe(true);
    });

    test('domain card advancement has no level restriction for tier 4', function () {
        $component = livewire(CharacterLevelUp::class, [
            'characterKey' => $this->character->character_key,
            'canEdit' => true,
        ]);

        // Get available domain cards for tier 4 (character level 8)
        $availableCards = $component->instance()->getAvailableDomainCards(8);
        expect($availableCards)->toBeArray();
        expect(count($availableCards))->toBeGreaterThan(0);

        // Verify cards can go up to level 8 (no "up to level 7" restriction like tier 3)
        $maxLevel = collect($availableCards)->max('level');
        expect($maxLevel)->toBeGreaterThanOrEqual(7); // Should have high-level cards available
    });

    test('multiclass advancement works correctly', function () {
        $component = livewire(CharacterLevelUp::class, [
            'characterKey' => $this->character->character_key,
            'canEdit' => true,
        ]);

        // Set up tier achievements
        $component->set('new_experience_name', 'Strategic Planning')
            ->set('new_experience_description', 'Advanced tactical planning')
            ->call('addTierExperience');

        $availableCards = $component->instance()->getAvailableDomainCards(8);
        $firstCard = $availableCards[0];
        $component->set('advancement_choices.tier_domain_card', $firstCard['key']);

        // Find multiclass option
        $tierOptions = $component->get('tier_options');
        $multiclassOptionIndex = null;
        foreach ($tierOptions['options'] as $index => $option) {
            if (str_contains($option['description'], 'Multiclass')) {
                $multiclassOptionIndex = $index;
                break;
            }
        }
        
        expect($multiclassOptionIndex)->not->toBeNull('Should find multiclass option');

        // Select multiclass advancement
        $component->call('selectFirstAdvancement', $multiclassOptionIndex);
        $component->set("advancement_choices.{$multiclassOptionIndex}.class", 'wizard');
        $component->call('selectSecondAdvancement', 1); // Hit Point

        // Confirm level up
        $component->call('confirmLevelUp');

        // Verify multiclass advancement was created
        $advancement = CharacterAdvancement::where('character_id', $this->character->id)
            ->where('advancement_type', 'multiclass')
            ->first();

        expect($advancement)->not->toBeNull();
        expect($advancement->advancement_data['class'])->toBe('wizard');
    });

    test('experience bonus advancement affects existing experiences', function () {
        $component = livewire(CharacterLevelUp::class, [
            'characterKey' => $this->character->character_key,
            'canEdit' => true,
        ]);

        // Set up tier achievements
        $component->set('new_experience_name', 'Strategic Planning')
            ->set('new_experience_description', 'Advanced tactical planning')
            ->call('addTierExperience');

        $availableCards = $component->instance()->getAvailableDomainCards(8);
        $firstCard = $availableCards[0];
        $component->set('advancement_choices.tier_domain_card', $firstCard['key']);

        // Find experience bonus option
        $tierOptions = $component->get('tier_options');
        $experienceOptionIndex = null;
        foreach ($tierOptions['options'] as $index => $option) {
            if (str_contains($option['description'], 'bonus to') && str_contains($option['description'], 'Experience')) {
                $experienceOptionIndex = $index;
                break;
            }
        }
        
        expect($experienceOptionIndex)->not->toBeNull('Should find experience bonus option');

        // Select experience bonus advancement
        $component->call('selectFirstAdvancement', $experienceOptionIndex);
        $component->set("advancement_choices.{$experienceOptionIndex}.experience_bonuses", ['Combat Training', 'Leadership']);
        $component->call('selectSecondAdvancement', 1); // Hit Point

        // Confirm level up
        $component->call('confirmLevelUp');

        // Verify experience bonuses were applied
        $combatTraining = $this->character->experiences()->where('experience_name', 'Combat Training')->first();
        $leadership = $this->character->experiences()->where('experience_name', 'Leadership')->first();
        
        expect($combatTraining->modifier)->toBe(3); // Was 2, now 3
        expect($leadership->modifier)->toBe(3); // Was 2, now 3

        // Verify advancement record was created
        $advancement = CharacterAdvancement::where('character_id', $this->character->id)
            ->where('advancement_type', 'experience_bonus')
            ->first();

        expect($advancement)->not->toBeNull();
        expect($advancement->advancement_data['experience_bonuses'])->toEqual(['Combat Training', 'Leadership']);
    });

    test('all tier 4 advancement validation works correctly', function () {
        $component = livewire(CharacterLevelUp::class, [
            'characterKey' => $this->character->character_key,
            'canEdit' => true,
        ]);

        // Set up tier achievements
        $component->set('new_experience_name', 'Strategic Planning')
            ->set('new_experience_description', 'Advanced tactical planning')
            ->call('addTierExperience');

        $availableCards = $component->instance()->getAvailableDomainCards(8);
        $firstCard = $availableCards[0];
        $component->set('advancement_choices.tier_domain_card', $firstCard['key']);

        // Test that validation passes with tier achievements
        expect($component->instance()->validateTierAchievements())->toBe(true);

        // Test each advancement type validation
        $tierOptions = $component->get('tier_options');
        
        // Test trait bonus validation
        $component->call('selectFirstAdvancement', 0);
        $component->call('selectSecondAdvancement', 1);
        expect($component->instance()->validateSelections())->toBe(false); // Should fail without trait selection
        
        $component->set('advancement_choices.0.traits', ['instinct', 'presence']);
        expect($component->instance()->validateSelections())->toBe(true); // Should pass with trait selection
    });
});
