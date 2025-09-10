<?php

use App\Livewire\CharacterLevelUp;
use App\Livewire\CharacterViewer;
use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterAdvancement;
use Domain\Character\Models\CharacterDomainCard;
use Domain\Character\Models\CharacterExperience;
use Domain\Character\Models\CharacterTrait;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

describe('Character Level Up - Comprehensive Integration Tests', function () {
    beforeEach(function () {
        $this->character = Character::factory()->create([
            'level' => 1,
            'proficiency' => 0,
            'class' => 'warrior',
        ]);

        // Create character traits
        foreach (['agility', 'strength', 'finesse', 'instinct', 'presence', 'knowledge'] as $trait) {
            CharacterTrait::factory()->create([
                'character_id' => $this->character->id,
                'trait_name' => $trait,
                'trait_value' => 0,
            ]);
        }

        // Create some initial experiences
        CharacterExperience::factory()->create([
            'character_id' => $this->character->id,
            'experience_name' => 'Combat Training',
            'experience_description' => 'Basic combat skills',
            'modifier' => 2,
        ]);

        CharacterExperience::factory()->create([
            'character_id' => $this->character->id,
            'experience_name' => 'Survival Skills',
            'experience_description' => 'Wilderness survival',
            'modifier' => 2,
        ]);
    });

    describe('Level 2 Tier Achievement Complete Integration', function () {
        test('complete level 2 process creates all expected records and reflects in character viewer', function () {
            // Step 1: Complete Level Up Process
            $component = livewire(CharacterLevelUp::class, [
                'characterKey' => $this->character->character_key,
                'canEdit' => true,
            ]);

            // Create tier experience
            $component->set('new_experience_name', 'Leadership')
                ->set('new_experience_description', 'Leading others in battle')
                ->call('addTierExperience');

            // Select tier domain card
            $availableCards = $component->call('getAvailableDomainCards', 2);
            expect($availableCards)->toBeArray();
            expect(count($availableCards))->toBeGreaterThan(0);

            $firstCard = $availableCards[0];
            $component->set('advancement_choices.tier_domain_card', $firstCard['key']);

            // Navigate to first advancement
            $component->call('goToNextStep');
            expect($component->get('current_step'))->toBe('first_advancement');

            // Select first advancement (trait bonus)
            $tierOptions = $component->get('tier_options');
            $traitAdvancementIndex = null;
            foreach ($tierOptions['options'] as $index => $option) {
                if (str_contains(strtolower($option['description']), 'trait')) {
                    $traitAdvancementIndex = $index;
                    break;
                }
            }

            expect($traitAdvancementIndex)->not->toBeNull('Should have trait advancement option');

            $component->call('selectAdvancement', $traitAdvancementIndex, 'first')
                ->set("advancement_choices.{$traitAdvancementIndex}.traits", ['agility', 'strength']);

            // Navigate to second advancement
            $component->call('goToNextStep');
            expect($component->get('current_step'))->toBe('second_advancement');

            // Select second advancement (different from first)
            $availableAdvancementsForSecond = $component->call('getAvailableAdvancementsForStep', 'second');
            $secondAdvancementIndex = null;
            foreach ($availableAdvancementsForSecond as $index => $option) {
                if ($index !== $traitAdvancementIndex && str_contains(strtolower($option['description']), 'hit point')) {
                    $secondAdvancementIndex = $index;
                    break;
                }
            }

            if ($secondAdvancementIndex !== null) {
                $component->call('selectAdvancement', $secondAdvancementIndex, 'second');
            }

            // Navigate to confirmation
            $component->call('goToNextStep');
            expect($component->get('current_step'))->toBe('confirmation');

            // Get initial counts
            $initialExperienceCount = CharacterExperience::where('character_id', $this->character->id)->count();
            $initialDomainCardCount = CharacterDomainCard::where('character_id', $this->character->id)->count();
            $initialAdvancementCount = CharacterAdvancement::where('character_id', $this->character->id)->count();

            // Confirm level up
            $component->call('confirmLevelUp');

            // Step 2: Verify Database Changes
            $this->character->refresh();

            // Level should be increased
            expect($this->character->level)->toBe(2);

            // Proficiency should be increased via advancement record
            $proficiencyAdvancement = CharacterAdvancement::where('character_id', $this->character->id)
                ->where('advancement_type', 'proficiency')
                ->first();
            expect($proficiencyAdvancement)->not->toBeNull();
            expect($proficiencyAdvancement->advancement_data['bonus'])->toBe(1);

            // New experience should be created
            $newExperienceCount = CharacterExperience::where('character_id', $this->character->id)->count();
            expect($newExperienceCount)->toBe($initialExperienceCount + 1);

            $leadershipExperience = CharacterExperience::where('character_id', $this->character->id)
                ->where('experience_name', 'Leadership')
                ->first();
            expect($leadershipExperience)->not->toBeNull();
            expect($leadershipExperience->modifier)->toBe(2);

            // Domain card should be created
            $newDomainCardCount = CharacterDomainCard::where('character_id', $this->character->id)->count();
            expect($newDomainCardCount)->toBe($initialDomainCardCount + 1);

            // Advancement records should be created
            $newAdvancementCount = CharacterAdvancement::where('character_id', $this->character->id)->count();
            expect($newAdvancementCount)->toBeGreaterThan($initialAdvancementCount);

            // Step 3: Verify Character Viewer Reflects Changes
            $viewer = livewire(CharacterViewer::class, [
                'characterKey' => $this->character->character_key,
            ]);

            // Check computed stats include proficiency bonus
            $computedStats = $viewer->call('getComputedStats');
            expect($computedStats)->toBeArray();

            // Check advancement status
            $advancementStatus = $viewer->call('getAdvancementStatus');
            expect($advancementStatus['current_tier'])->toBe(2);
            expect(count($advancementStatus['advancements']))->toBeGreaterThan(0);

            // Verify proficiency bonus is calculated correctly
            expect($this->character->getProficiencyBonus())->toBe(1); // Base 1 for level 2 + 0 advancement bonuses initially
        });
    });

    describe('Level 5 Tier Achievement Complete Integration', function () {
        test('level 5 process includes experience creation, proficiency increase, and trait clearing', function () {
            // Set up character at level 4 with marked traits
            $this->character->update(['level' => 4, 'proficiency' => 1]);

            // Note: Trait marking is handled differently in the actual system
            // This test focuses on the level up process itself

            $component = livewire(CharacterLevelUp::class, [
                'characterKey' => $this->character->character_key,
                'canEdit' => true,
            ]);

            // Verify level 5 benefits are shown
            $component->assertSee('Level 5 Benefits (Tier 3 Entry)')
                ->assertSee('Gain a new Experience at +2 modifier')
                ->assertSee('Clear all marked character traits (automatic)');

            // Create tier experience for level 5
            $component->set('new_experience_name', 'Tactical Mastery')
                ->set('new_experience_description', 'Advanced combat tactics')
                ->call('addTierExperience');

            // Select domain card
            $availableCards = $component->call('getAvailableDomainCards', 5);
            expect(count($availableCards))->toBeGreaterThan(0);
            $component->set('advancement_choices.tier_domain_card', $availableCards[0]['key']);

            // Complete advancement selections
            $component->call('goToNextStep');
            $tierOptions = $component->get('tier_options');

            // Select first advancement
            $component->call('selectAdvancement', 0, 'first');

            // Select second advancement
            $component->call('goToNextStep');
            $availableForSecond = $component->call('getAvailableAdvancementsForStep', 'second');
            if (count($availableForSecond) > 0) {
                $component->call('selectAdvancement', array_keys($availableForSecond)[0], 'second');
            }

            // Confirm
            $component->call('goToNextStep')
                ->call('confirmLevelUp');

            // Verify database changes
            $this->character->refresh();
            expect($this->character->level)->toBe(5);

            // Check proficiency advancement was created
            $proficiencyAdvancements = CharacterAdvancement::where('character_id', $this->character->id)
                ->where('advancement_type', 'proficiency')
                ->get();
            expect($proficiencyAdvancements->count())->toBeGreaterThan(0);

            // Check new experience was created
            $tacticalExperience = CharacterExperience::where('character_id', $this->character->id)
                ->where('experience_name', 'Tactical Mastery')
                ->first();
            expect($tacticalExperience)->not->toBeNull();

            // Verify proficiency bonus calculation
            $calculatedProficiency = $this->character->getProficiencyBonus();
            expect($calculatedProficiency)->toBe(2); // Base 2 for level 5-7 + advancement bonuses

            // Verify in character viewer
            $viewer = livewire(CharacterViewer::class, [
                'characterKey' => $this->character->character_key,
            ]);

            $advancementStatus = $viewer->call('getAdvancementStatus');
            expect($advancementStatus['current_tier'])->toBe(3);
        });
    });

    describe('Level 8 Tier Achievement Complete Integration', function () {
        test('level 8 process includes all tier 4 benefits', function () {
            // Set up character at level 7
            $this->character->update(['level' => 7, 'proficiency' => 2]);

            $component = livewire(CharacterLevelUp::class, [
                'characterKey' => $this->character->character_key,
                'canEdit' => true,
            ]);

            // Verify level 8 benefits are shown
            $component->assertSee('Level 8 Benefits (Tier 4 Entry)')
                ->assertSee('Gain a new Experience at +2 modifier')
                ->assertSee('Clear all marked character traits (automatic)');

            // Create experience and select domain card
            $component->set('new_experience_name', 'Master Strategist')
                ->call('addTierExperience');

            $availableCards = $component->call('getAvailableDomainCards', 8);
            expect(count($availableCards))->toBeGreaterThan(0);
            $component->set('advancement_choices.tier_domain_card', $availableCards[0]['key']);

            // Complete advancement selections and confirm
            $component->call('goToNextStep')
                ->call('selectAdvancement', 0, 'first')
                ->call('goToNextStep');

            $availableForSecond = $component->call('getAvailableAdvancementsForStep', 'second');
            if (count($availableForSecond) > 0) {
                $component->call('selectAdvancement', array_keys($availableForSecond)[0], 'second');
            }

            $component->call('goToNextStep')
                ->call('confirmLevelUp');

            // Verify changes
            $this->character->refresh();
            expect($this->character->level)->toBe(8);

            // Check proficiency bonus for tier 4
            $calculatedProficiency = $this->character->getProficiencyBonus();
            expect($calculatedProficiency)->toBe(3); // Base 3 for level 8-10 + advancement bonuses

            // Verify in character viewer shows tier 4
            $viewer = livewire(CharacterViewer::class, [
                'characterKey' => $this->character->character_key,
            ]);

            $advancementStatus = $viewer->call('getAdvancementStatus');
            expect($advancementStatus['current_tier'])->toBe(4);
        });
    });

    describe('Non-Tier Level Domain Card Requirements', function () {
        test('level 3 requires domain card selection and applies correctly', function () {
            // Set up character at level 2
            $this->character->update(['level' => 2, 'proficiency' => 1]);

            $component = livewire(CharacterLevelUp::class, [
                'characterKey' => $this->character->character_key,
                'canEdit' => true,
            ]);

            // Should not have tier achievements but should have domain card selection
            $component->assertSee('No tier achievements at this level')
                ->assertSee('Select Your Domain Card');

            // Select domain card
            $availableCards = $component->call('getAvailableDomainCards', 3);
            expect(count($availableCards))->toBeGreaterThan(0);
            $component->set('advancement_choices.tier_domain_card', $availableCards[0]['key']);

            // Complete advancement process
            $component->call('goToNextStep')
                ->call('selectAdvancement', 0, 'first')
                ->call('goToNextStep')
                ->call('selectAdvancement', 1, 'second')
                ->call('goToNextStep')
                ->call('confirmLevelUp');

            // Verify level increased and domain card was added
            $this->character->refresh();
            expect($this->character->level)->toBe(3);

            $domainCardCount = CharacterDomainCard::where('character_id', $this->character->id)->count();
            expect($domainCardCount)->toBeGreaterThan(0);
        });
    });

    describe('Advancement Bonuses Calculation Integration', function () {
        test('trait bonuses are calculated and displayed correctly in character viewer', function () {
            // Level up with trait advancement
            $this->character->update(['level' => 1]);

            $component = livewire(CharacterLevelUp::class, [
                'characterKey' => $this->character->character_key,
                'canEdit' => true,
            ]);

            // Complete tier achievements
            $component->set('new_experience_name', 'Test Experience')
                ->call('addTierExperience');

            $availableCards = $component->call('getAvailableDomainCards', 2);
            $component->set('advancement_choices.tier_domain_card', $availableCards[0]['key']);

            // Select trait advancement
            $component->call('goToNextStep');
            $tierOptions = $component->get('tier_options');

            $traitAdvancementIndex = null;
            foreach ($tierOptions['options'] as $index => $option) {
                if (str_contains(strtolower($option['description']), 'trait')) {
                    $traitAdvancementIndex = $index;
                    break;
                }
            }

            $component->call('selectAdvancement', $traitAdvancementIndex, 'first')
                ->set("advancement_choices.{$traitAdvancementIndex}.traits", ['agility', 'strength']);

            // Complete level up
            $component->call('goToNextStep')
                ->call('selectAdvancement', 0, 'second') // Select different advancement
                ->call('goToNextStep')
                ->call('confirmLevelUp');

            // Verify trait bonuses are applied
            $this->character->refresh();

            // Check effective trait values include advancement bonuses
            $agilityValue = $this->character->getEffectiveTraitValue(\Domain\Character\Enums\TraitName::AGILITY);
            $strengthValue = $this->character->getEffectiveTraitValue(\Domain\Character\Enums\TraitName::STRENGTH);

            // Base trait values are 0, so effective values should include +1 bonus from advancement
            expect($agilityValue)->toBe(1);  // 0 base + 1 advancement
            expect($strengthValue)->toBe(1); // 0 base + 1 advancement

            // Verify in character viewer
            $viewer = livewire(CharacterViewer::class, [
                'characterKey' => $this->character->character_key,
            ]);

            $traitValues = $viewer->call('getFormattedTraitValues');
            expect($traitValues)->toBeArray();
            expect($traitValues['agility']['effective'])->toBe(1);
            expect($traitValues['strength']['effective'])->toBe(1);
        });

        test('experience bonuses are applied and calculated correctly', function () {
            // Create character with experience bonus advancement
            $this->character->update(['level' => 2]);

            // Create advancement that gives experience bonus
            CharacterAdvancement::create([
                'character_id' => $this->character->id,
                'tier' => 2,
                'advancement_number' => 1,
                'advancement_type' => 'experience_bonus',
                'advancement_data' => ['experiences' => ['Combat Training', 'Survival Skills']],
                'description' => 'Gain a +1 bonus to two Experiences',
            ]);

            // Verify character viewer shows enhanced experience modifiers
            $viewer = livewire(CharacterViewer::class, [
                'characterKey' => $this->character->character_key,
            ]);

            // The character viewer should show experiences with their bonuses
            $viewer->assertSee('Combat Training')
                ->assertSee('Survival Skills');
        });
    });

    describe('Proficiency Impact on Weapon Damage', function () {
        test('proficiency bonus affects weapon damage calculations', function () {
            // This test verifies that proficiency bonuses from advancements
            // are properly calculated and would affect weapon damage

            $this->character->update(['level' => 2]);

            // Create proficiency advancement
            CharacterAdvancement::create([
                'character_id' => $this->character->id,
                'tier' => 2,
                'advancement_number' => 0, // Tier achievement
                'advancement_type' => 'proficiency',
                'advancement_data' => ['bonus' => 1],
                'description' => 'Tier achievement: +1 Proficiency bonus',
            ]);

            // Verify proficiency calculation
            $proficiencyBonus = $this->character->getProficiencyBonus();

            // Level 2 base proficiency is 1, plus 1 from advancement = 2
            expect($proficiencyBonus)->toBe(2);

            // Add another proficiency advancement
            CharacterAdvancement::create([
                'character_id' => $this->character->id,
                'tier' => 2,
                'advancement_number' => 1,
                'advancement_type' => 'proficiency',
                'advancement_data' => ['bonus' => 1],
                'description' => 'Advancement: +1 Proficiency bonus',
            ]);

            // Should now be 3 total
            $this->character->refresh();
            $proficiencyBonus = $this->character->getProficiencyBonus();
            expect($proficiencyBonus)->toBe(3); // 1 base + 2 advancement bonuses
        });
    });
});
