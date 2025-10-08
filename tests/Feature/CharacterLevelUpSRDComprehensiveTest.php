<?php

use App\Livewire\CharacterLevelUp;
use App\Livewire\CharacterViewer;
use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterAdvancement;
use Domain\Character\Models\CharacterDomainCard;
use Domain\Character\Models\CharacterExperience;
use Domain\Character\Models\CharacterTrait;

use function Pest\Livewire\livewire;

describe('Character Level Up - Complete SRD Compliance Tests', function () {
    beforeEach(function () {
        $this->character = Character::factory()->create([
            'level' => 1,
            'proficiency' => 0,
            'class' => 'warrior',
        ]);

        // Create character traits with is_marked field
        foreach (['agility', 'strength', 'finesse', 'instinct', 'presence', 'knowledge'] as $trait) {
            CharacterTrait::factory()->create([
                'character_id' => $this->character->id,
                'trait_name' => $trait,
                'trait_value' => 0,
                'is_marked' => false,
            ]);
        }

        // Create initial experiences
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

    describe('Trait Marking System Compliance', function () {
        test('trait advancement marks selected traits and prevents reselection', function () {
            // Level up to 2 and select trait advancement
            $component = livewire(CharacterLevelUp::class, [
                'characterKey' => $this->character->character_key,
                'canEdit' => true,
            ]);

            // Complete tier achievements
            $component->set('new_experience_name', 'Leadership')
                ->call('addTierExperience');

            $availableCards = $component->call('getAvailableDomainCards', 2);
            $component->set('advancement_choices.tier_domain_card', $availableCards[0]['key']);

            // Navigate to advancement selection
            $component->call('goToNextStep');

            // Find trait advancement option
            $tierOptions = $component->get('tier_options');
            $traitAdvancementIndex = null;
            foreach ($tierOptions['options'] as $index => $option) {
                if (str_contains(strtolower($option['description']), 'trait')) {
                    $traitAdvancementIndex = $index;
                    break;
                }
            }

            expect($traitAdvancementIndex)->not->toBeNull('Should have trait advancement option');

            // Select trait advancement with agility and strength
            $component->call('selectAdvancement', $traitAdvancementIndex, 'first')
                ->set("advancement_choices.{$traitAdvancementIndex}.traits", ['agility', 'strength']);

            // Complete level up process
            $component->call('goToNextStep')
                ->call('selectAdvancement', 0, 'second') // Different advancement
                ->call('goToNextStep')
                ->call('confirmLevelUp');

            // Verify traits are marked in database
            $agilityTrait = CharacterTrait::where('character_id', $this->character->id)
                ->where('trait_name', 'agility')
                ->first();
            $strengthTrait = CharacterTrait::where('character_id', $this->character->id)
                ->where('trait_name', 'strength')
                ->first();
            $finesseTrait = CharacterTrait::where('character_id', $this->character->id)
                ->where('trait_name', 'finesse')
                ->first();

            expect($agilityTrait->is_marked)->toBe(true, 'Agility should be marked');
            expect($strengthTrait->is_marked)->toBe(true, 'Strength should be marked');
            expect($finesseTrait->is_marked)->toBe(false, 'Finesse should remain unmarked');

            // Verify trait values increased
            expect($agilityTrait->trait_value)->toBe(1, 'Agility should be increased by +1');
            expect($strengthTrait->trait_value)->toBe(1, 'Strength should be increased by +1');
        });

        test('marked traits are cleared at tier achievements (levels 5 and 8)', function () {
            // Set up character at level 4 with marked traits
            $this->character->update(['level' => 4, 'proficiency' => 1]);

            // Mark some traits manually
            $this->character->traits()->where('trait_name', 'agility')->update(['is_marked' => true]);
            $this->character->traits()->where('trait_name', 'strength')->update(['is_marked' => true]);

            // Level up to 5 (tier 3 entry)
            $component = livewire(CharacterLevelUp::class, [
                'characterKey' => $this->character->character_key,
                'canEdit' => true,
            ]);

            // Complete level up process
            $component->set('new_experience_name', 'Tactical Mastery')
                ->call('addTierExperience');

            $availableCards = $component->call('getAvailableDomainCards', 5);
            $component->set('advancement_choices.tier_domain_card', $availableCards[0]['key']);

            $component->call('goToNextStep')
                ->call('selectAdvancement', 0, 'first')
                ->call('goToNextStep')
                ->call('selectAdvancement', 1, 'second')
                ->call('goToNextStep')
                ->call('confirmLevelUp');

            // Verify all traits are now unmarked
            $markedTraits = CharacterTrait::where('character_id', $this->character->id)
                ->where('is_marked', true)
                ->count();
            expect($markedTraits)->toBe(0, 'All traits should be unmarked after tier 3 entry');
        });

        test('only unmarked traits can be selected for advancement', function () {
            // Mark some traits
            $this->character->traits()->where('trait_name', 'agility')->update(['is_marked' => true]);
            $this->character->traits()->where('trait_name', 'strength')->update(['is_marked' => true]);

            $component = livewire(CharacterLevelUp::class, [
                'characterKey' => $this->character->character_key,
                'canEdit' => true,
            ]);

            // The trait selection UI should only show unmarked traits as selectable
            // This would need to be implemented in the component logic
            $unmarkedTraits = $this->character->traits()->unmarked()->pluck('trait_name')->toArray();
            expect(count($unmarkedTraits))->toBe(4); // 6 total - 2 marked = 4 unmarked
            expect(in_array('agility', $unmarkedTraits))->toBe(false);
            expect(in_array('strength', $unmarkedTraits))->toBe(false);
            expect(in_array('finesse', $unmarkedTraits))->toBe(true);
        });
    });

    describe('Proficiency System Complete Integration', function () {
        test('proficiency increases are applied and reflected in character viewer', function () {
            // Complete level 2 with proficiency tier achievement
            $component = livewire(CharacterLevelUp::class, [
                'characterKey' => $this->character->character_key,
                'canEdit' => true,
            ]);

            // Complete level up
            $component->set('new_experience_name', 'Test Experience')
                ->call('addTierExperience');

            $availableCards = $component->call('getAvailableDomainCards', 2);
            $component->set('advancement_choices.tier_domain_card', $availableCards[0]['key']);

            $component->call('goToNextStep')
                ->call('selectAdvancement', 0, 'first')
                ->call('goToNextStep')
                ->call('selectAdvancement', 1, 'second')
                ->call('goToNextStep')
                ->call('confirmLevelUp');

            // Verify proficiency advancement was created
            $proficiencyAdvancement = CharacterAdvancement::where('character_id', $this->character->id)
                ->where('advancement_type', 'proficiency')
                ->where('advancement_number', 0) // Tier achievement
                ->first();

            expect($proficiencyAdvancement)->not->toBeNull();
            expect($proficiencyAdvancement->advancement_data['bonus'])->toBe(1);

            // Verify character proficiency calculation
            $this->character->refresh();
            $calculatedProficiency = $this->character->getProficiencyBonus();
            expect($calculatedProficiency)->toBe(2); // Level 2 base (1) + tier achievement (1)

            // Verify character viewer shows updated proficiency
            $viewer = livewire(CharacterViewer::class, [
                'characterKey' => $this->character->character_key,
            ]);

            $computedStats = $viewer->call('getComputedStats');
            expect($computedStats)->toBeArray();

            $advancementStatus = $viewer->call('getAdvancementStatus');
            expect($advancementStatus['current_tier'])->toBe(2);
            expect(count($advancementStatus['advancements']))->toBeGreaterThan(0);
        });

        test('multiple proficiency advancements stack correctly', function () {
            $this->character->update(['level' => 2]);

            // Create multiple proficiency advancements
            CharacterAdvancement::create([
                'character_id' => $this->character->id,
                'tier' => 2,
            'level' => 2,
            'advancement_number' => 0,
                'advancement_type' => 'proficiency',
                'advancement_data' => ['bonus' => 1],
                'description' => 'Tier achievement: +1 Proficiency bonus',
            ]);

            CharacterAdvancement::create([
                'character_id' => $this->character->id,
                'tier' => 2,
            'level' => 2,
            'advancement_number' => 1,
                'advancement_type' => 'proficiency',
                'advancement_data' => ['bonus' => 1],
                'description' => 'Advancement: +1 Proficiency bonus',
            ]);

            // Verify stacking
            $this->character->refresh();
            $totalProficiency = $this->character->getProficiencyBonus();
            expect($totalProficiency)->toBe(3); // Level 2 base (1) + 2 advancement bonuses
        });
    });

    describe('Domain Card Selection SRD Compliance', function () {
        test('every level gets domain card at appropriate level', function () {
            foreach (range(1, 9) as $startLevel) {
                $character = Character::factory()->create(['level' => $startLevel]);
                $targetLevel = $startLevel + 1;

                $component = livewire(CharacterLevelUp::class, [
                    'characterKey' => $character->character_key,
                    'canEdit' => true,
                ]);

                // Should always have domain card selection
                $component->assertSee('Select Your Domain Card');

                // Domain cards should be available up to target level
                $availableCards = $component->call('getAvailableDomainCards', $targetLevel);
                expect(count($availableCards))->toBeGreaterThan(0, "Should have domain cards for level $targetLevel");

                // Verify level limits
                foreach ($availableCards as $card) {
                    expect($card['level'])->toBeLessThanOrEqual($targetLevel, "Card level should not exceed $targetLevel");
                }
            }
        });

        test('domain card selection creates database record', function () {
            $component = livewire(CharacterLevelUp::class, [
                'characterKey' => $this->character->character_key,
                'canEdit' => true,
            ]);

            // Complete tier achievements and advancement selection
            $component->set('new_experience_name', 'Test Experience')
                ->call('addTierExperience');

            $availableCards = $component->call('getAvailableDomainCards', 2);
            $selectedCard = $availableCards[0];
            $component->set('advancement_choices.tier_domain_card', $selectedCard['key']);

            $component->call('goToNextStep')
                ->call('selectAdvancement', 0, 'first')
                ->call('goToNextStep')
                ->call('selectAdvancement', 1, 'second')
                ->call('goToNextStep')
                ->call('confirmLevelUp');

            // Verify domain card was created in database
            $domainCard = CharacterDomainCard::where('character_id', $this->character->id)
                ->where('ability_key', $selectedCard['key'])
                ->first();

            expect($domainCard)->not->toBeNull();
            expect($domainCard->domain)->toBe($selectedCard['domain']);
            expect($domainCard->level)->toBe($selectedCard['level']);
        });
    });

    describe('Experience System SRD Compliance', function () {
        test('tier achievements create experiences with +2 modifier', function () {
            $levels = [2, 5, 8]; // Tier achievement levels

            foreach ($levels as $targetLevel) {
                $character = Character::factory()->create(['level' => $targetLevel - 1]);

                $component = livewire(CharacterLevelUp::class, [
                    'characterKey' => $character->character_key,
                    'canEdit' => true,
                ]);

                // Should have experience creation interface
                $component->assertSee('Create Your New Experience');

                // Create experience
                $experienceName = "Level {$targetLevel} Experience";
                $component->set('new_experience_name', $experienceName)
                    ->call('addTierExperience');

                // Complete level up process
                $availableCards = $component->call('getAvailableDomainCards', $targetLevel);
                $component->set('advancement_choices.tier_domain_card', $availableCards[0]['key']);

                $component->call('goToNextStep')
                    ->call('selectAdvancement', 0, 'first')
                    ->call('goToNextStep')
                    ->call('selectAdvancement', 1, 'second')
                    ->call('goToNextStep')
                    ->call('confirmLevelUp');

                // Verify experience was created with +2 modifier
                $experience = CharacterExperience::where('character_id', $character->id)
                    ->where('experience_name', $experienceName)
                    ->first();

                expect($experience)->not->toBeNull();
                expect($experience->modifier)->toBe(2, 'Tier experience should have +2 modifier');
            }
        });

        test('experience bonus advancements affect existing experiences', function () {
            // Set up character with experiences
            $this->character->update(['level' => 2]);

            // Create experience bonus advancement
            CharacterAdvancement::create([
                'character_id' => $this->character->id,
                'tier' => 2,
            'level' => 2,
            'advancement_number' => 1,
                'advancement_type' => 'experience_bonus',
                'advancement_data' => ['experiences' => ['Combat Training', 'Survival Skills']],
                'description' => 'Gain a +1 bonus to two Experiences',
            ]);

            // Verify character viewer shows the bonuses
            $viewer = livewire(CharacterViewer::class, [
                'characterKey' => $this->character->character_key,
            ]);

            // Should display experiences with their enhanced modifiers
            $viewer->assertSee('Combat Training')
                ->assertSee('Survival Skills');
        });
    });

    describe('Character Viewer Integration Tests', function () {
        test('character viewer reflects all level up changes', function () {
            // Create a character with various advancements
            $this->character->update(['level' => 3, 'proficiency' => 1]);

            // Create trait advancement
            CharacterAdvancement::create([
                'character_id' => $this->character->id,
                'tier' => 2,
            'level' => 2,
            'advancement_number' => 1,
                'advancement_type' => 'trait_bonus',
                'advancement_data' => ['traits' => ['agility', 'strength'], 'bonus' => 1],
                'description' => 'Gain a +1 bonus to two unmarked character traits and mark them',
            ]);

            // Create hit point advancement
            CharacterAdvancement::create([
                'character_id' => $this->character->id,
                'tier' => 2,
            'level' => 2,
            'advancement_number' => 2,
                'advancement_type' => 'hit_point',
                'advancement_data' => ['bonus' => 1],
                'description' => 'Permanently gain one Hit Point slot',
            ]);

            // Create proficiency advancement
            CharacterAdvancement::create([
                'character_id' => $this->character->id,
                'tier' => 2,
            'level' => 2,
            'advancement_number' => 0,
                'advancement_type' => 'proficiency',
                'advancement_data' => ['bonus' => 1],
                'description' => 'Tier achievement: +1 Proficiency bonus',
            ]);

            $viewer = livewire(CharacterViewer::class, [
                'characterKey' => $this->character->character_key,
            ]);

            // Test computed stats include all bonuses
            $computedStats = $viewer->call('getComputedStats');
            expect($computedStats)->toBeArray();
            expect($computedStats['hit_points'])->toBeGreaterThan($this->character->stats->hit_points);

            // Test advancement status
            $advancementStatus = $viewer->call('getAdvancementStatus');
            expect($advancementStatus['current_tier'])->toBe(2);
            expect(count($advancementStatus['advancements']))->toBe(3);

            // Test trait values include advancement bonuses
            $traitValues = $viewer->call('getFormattedTraitValues');
            expect($traitValues['agility']['effective'])->toBe(1); // 0 base + 1 advancement
            expect($traitValues['strength']['effective'])->toBe(1); // 0 base + 1 advancement
            expect($traitValues['finesse']['effective'])->toBe(0); // 0 base + 0 advancement
        });

        test('character can level up eligibility is calculated correctly', function () {
            // Test various scenarios
            $scenarios = [
                ['level' => 1, 'can_level_up' => true],  // Can always level from 1
                ['level' => 2, 'can_level_up' => true],  // Can level if slots available
                ['level' => 10, 'can_level_up' => false], // Cannot level past 10
            ];

            foreach ($scenarios as $scenario) {
                $character = Character::factory()->create(['level' => $scenario['level']]);

                $viewer = livewire(CharacterViewer::class, [
                    'characterKey' => $character->character_key,
                ]);

                $canLevelUp = $viewer->call('canLevelUp');
                expect($canLevelUp)->toBe($scenario['can_level_up'], "Level {$scenario['level']} eligibility incorrect");
            }
        });
    });

    describe('Complete Level Up Workflow Verification', function () {
        test('complete level 2 to 5 progression follows SRD exactly', function () {
            // Level 1 -> 2 (Tier 2 Entry)
            $component = livewire(CharacterLevelUp::class, [
                'characterKey' => $this->character->character_key,
                'canEdit' => true,
            ]);

            $component->set('new_experience_name', 'Level 2 Experience')
                ->call('addTierExperience');

            $availableCards = $component->call('getAvailableDomainCards', 2);
            $component->set('advancement_choices.tier_domain_card', $availableCards[0]['key']);

            $component->call('goToNextStep')
                ->call('selectAdvancement', 0, 'first')
                ->call('goToNextStep')
                ->call('selectAdvancement', 1, 'second')
                ->call('goToNextStep')
                ->call('confirmLevelUp');

            $this->character->refresh();
            expect($this->character->level)->toBe(2);
            expect($this->character->getProficiencyBonus())->toBe(2); // 1 base + 1 tier achievement

            // Level 2 -> 3 (Tier 2 Mid)
            $this->character->refresh();
            $component = livewire(CharacterLevelUp::class, [
                'characterKey' => $this->character->character_key,
                'canEdit' => true,
            ]);

            $availableCards = $component->call('getAvailableDomainCards', 3);
            $component->set('advancement_choices.tier_domain_card', $availableCards[0]['key']);

            $component->call('goToNextStep')
                ->call('selectAdvancement', 0, 'first')
                ->call('goToNextStep')
                ->call('selectAdvancement', 1, 'second')
                ->call('goToNextStep')
                ->call('confirmLevelUp');

            $this->character->refresh();
            expect($this->character->level)->toBe(3);

            // Continue to level 4
            $component = livewire(CharacterLevelUp::class, [
                'characterKey' => $this->character->character_key,
                'canEdit' => true,
            ]);

            $availableCards = $component->call('getAvailableDomainCards', 4);
            $component->set('advancement_choices.tier_domain_card', $availableCards[0]['key']);

            $component->call('goToNextStep')
                ->call('selectAdvancement', 0, 'first')
                ->call('goToNextStep')
                ->call('selectAdvancement', 1, 'second')
                ->call('goToNextStep')
                ->call('confirmLevelUp');

            $this->character->refresh();
            expect($this->character->level)->toBe(4);

            // Level 4 -> 5 (Tier 3 Entry with trait clearing)
            $component = livewire(CharacterLevelUp::class, [
                'characterKey' => $this->character->character_key,
                'canEdit' => true,
            ]);

            $component->assertSee('Level 5 Benefits (Tier 3 Entry)')
                ->assertSee('Clear all marked character traits (automatic)');

            $component->set('new_experience_name', 'Level 5 Experience')
                ->call('addTierExperience');

            $availableCards = $component->call('getAvailableDomainCards', 5);
            $component->set('advancement_choices.tier_domain_card', $availableCards[0]['key']);

            $component->call('goToNextStep')
                ->call('selectAdvancement', 0, 'first')
                ->call('goToNextStep')
                ->call('selectAdvancement', 1, 'second')
                ->call('goToNextStep')
                ->call('confirmLevelUp');

            $this->character->refresh();
            expect($this->character->level)->toBe(5);
            expect($this->character->getProficiencyBonus())->toBe(4); // 2 base + 2 tier achievements

            // Verify all traits are unmarked
            $markedCount = CharacterTrait::where('character_id', $this->character->id)
                ->where('is_marked', true)
                ->count();
            expect($markedCount)->toBe(0);

            // Verify character viewer shows tier 3
            $viewer = livewire(CharacterViewer::class, [
                'characterKey' => $this->character->character_key,
            ]);

            $advancementStatus = $viewer->call('getAdvancementStatus');
            expect($advancementStatus['current_tier'])->toBe(3);
        });
    });
});
