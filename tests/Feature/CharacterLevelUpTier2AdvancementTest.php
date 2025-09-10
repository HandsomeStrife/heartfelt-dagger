<?php

use App\Livewire\CharacterLevelUp;
use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterAdvancement;
use Domain\Character\Models\CharacterDomainCard;
use Domain\Character\Models\CharacterExperience;
use Domain\Character\Models\CharacterTrait;
use function Pest\Livewire\livewire;

describe('Character Level Up - Tier 2 Advancement Options', function () {
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
                'is_marked' => false,
            ]);
        }

        // Create initial experience
        CharacterExperience::factory()->create([
            'character_id' => $this->character->id,
            'experience_name' => 'Combat Training',
            'experience_description' => 'Basic weapon training',
            'modifier' => 2,
        ]);
    });

    test('trait bonus advancement works correctly', function () {
        $component = livewire(CharacterLevelUp::class, [
            'characterKey' => $this->character->character_key,
            'canEdit' => true,
        ]);

        // Set up tier achievements first
        $component->set('new_experience_name', 'Leadership')
            ->set('new_experience_description', 'Leading others')
            ->call('addTierExperience');

        $availableCards = $component->instance()->getAvailableDomainCards(2);
        $firstCard = $availableCards[0];
        $component->set('advancement_choices.tier_domain_card', $firstCard['key']);

        // Select trait bonus advancement (option 0)
        $component->call('selectFirstAdvancement', 0);
        
        // Set trait choices
        $component->set('advancement_choices.0.traits', ['agility', 'strength']);

        // Select another advancement
        $component->call('selectSecondAdvancement', 1); // Hit Point

        // Confirm level up
        $component->call('confirmLevelUp');

        // Verify trait advancement was created
        $advancement = CharacterAdvancement::where('character_id', $this->character->id)
            ->where('advancement_type', 'trait_bonus')
            ->first();
        
        expect($advancement)->not->toBeNull();
        expect($advancement->advancement_data['traits'])->toContain('agility', 'strength');
        expect($advancement->advancement_data['bonus'])->toBe(1);

        // Verify traits are marked
        $agilityTrait = $this->character->traits()->where('trait_name', 'agility')->first();
        $strengthTrait = $this->character->traits()->where('trait_name', 'strength')->first();
        $finesseTrait = $this->character->traits()->where('trait_name', 'finesse')->first();

        expect($agilityTrait->is_marked)->toBe(true);
        expect($strengthTrait->is_marked)->toBe(true);
        expect($finesseTrait->is_marked)->toBe(false); // Not selected

        // Verify effective trait values
        $this->character->refresh();
        expect($this->character->getEffectiveTraitValue(\Domain\Character\Enums\TraitName::AGILITY))->toBe(1);
        expect($this->character->getEffectiveTraitValue(\Domain\Character\Enums\TraitName::STRENGTH))->toBe(1);
        expect($this->character->getEffectiveTraitValue(\Domain\Character\Enums\TraitName::FINESSE))->toBe(0);
    });

    test('hit point advancement works correctly', function () {
        $component = livewire(CharacterLevelUp::class, [
            'characterKey' => $this->character->character_key,
            'canEdit' => true,
        ]);

        // Set up tier achievements
        $component->set('new_experience_name', 'Leadership')
            ->set('new_experience_description', 'Leading others')
            ->call('addTierExperience');

        $availableCards = $component->instance()->getAvailableDomainCards(2);
        $firstCard = $availableCards[0];
        $component->set('advancement_choices.tier_domain_card', $firstCard['key']);

        // Select hit point advancement (option 1)
        $component->call('selectFirstAdvancement', 1);
        $component->call('selectSecondAdvancement', 2); // Stress

        // Confirm level up
        $component->call('confirmLevelUp');

        // Verify hit point advancement was created
        $advancement = CharacterAdvancement::where('character_id', $this->character->id)
            ->where('advancement_type', 'hit_point')
            ->first();
        
        expect($advancement)->not->toBeNull();
        expect($advancement->description)->toContain('Hit Point');

        // Verify character level was updated
        $this->character->refresh();
        expect($this->character->level)->toBe(2);
    });

    test('stress advancement works correctly', function () {
        $component = livewire(CharacterLevelUp::class, [
            'characterKey' => $this->character->character_key,
            'canEdit' => true,
        ]);

        // Set up tier achievements
        $component->set('new_experience_name', 'Leadership')
            ->set('new_experience_description', 'Leading others')
            ->call('addTierExperience');

        $availableCards = $component->instance()->getAvailableDomainCards(2);
        $firstCard = $availableCards[0];
        $component->set('advancement_choices.tier_domain_card', $firstCard['key']);

        // Select stress advancement (option 2)
        $component->call('selectFirstAdvancement', 2);
        $component->call('selectSecondAdvancement', 1); // Hit Point

        // Confirm level up
        $component->call('confirmLevelUp');

        // Verify stress advancement was created
        $advancement = CharacterAdvancement::where('character_id', $this->character->id)
            ->where('advancement_type', 'stress')
            ->first();
        
        expect($advancement)->not->toBeNull();
        expect($advancement->description)->toContain('Stress');
    });

    test('experience bonus advancement works correctly', function () {
        $component = livewire(CharacterLevelUp::class, [
            'characterKey' => $this->character->character_key,
            'canEdit' => true,
        ]);

        // Set up tier achievements
        $component->set('new_experience_name', 'Leadership')
            ->set('new_experience_description', 'Leading others')
            ->call('addTierExperience');

        $availableCards = $component->instance()->getAvailableDomainCards(2);
        $firstCard = $availableCards[0];
        $component->set('advancement_choices.tier_domain_card', $firstCard['key']);

        // Select experience bonus advancement (option 3)
        $component->call('selectFirstAdvancement', 3);
        
        // Set experience choices - select existing and new experience
        $component->call('selectExperienceBonus', 3, 'Combat Training');
        $component->call('selectExperienceBonus', 3, 'Leadership');

        $component->call('selectSecondAdvancement', 1); // Hit Point

        // Confirm level up
        $component->call('confirmLevelUp');

        // Verify experience bonus advancement was created
        $advancement = CharacterAdvancement::where('character_id', $this->character->id)
            ->where('advancement_type', 'experience_bonus')
            ->first();
        
        expect($advancement)->not->toBeNull();
        expect($advancement->advancement_data['experience_bonuses'])->toContain('Combat Training', 'Leadership');
    });

    test('domain card advancement works correctly', function () {
        $component = livewire(CharacterLevelUp::class, [
            'characterKey' => $this->character->character_key,
            'canEdit' => true,
        ]);

        // Set up tier achievements
        $component->set('new_experience_name', 'Leadership')
            ->set('new_experience_description', 'Leading others')
            ->call('addTierExperience');

        $availableCards = $component->instance()->getAvailableDomainCards(2);
        $firstCard = $availableCards[0];
        $component->set('advancement_choices.tier_domain_card', $firstCard['key']);

        // Select domain card advancement (option 4)
        $component->call('selectFirstAdvancement', 4);
        
        // Select a domain card for the advancement
        $secondCard = $availableCards[1] ?? $availableCards[0]; // Get second card or fallback to first
        $component->call('selectDomainCard', 4, $secondCard['key']);

        $component->call('selectSecondAdvancement', 1); // Hit Point

        // Confirm level up
        $component->call('confirmLevelUp');

        // Verify domain card advancement was created
        $advancement = CharacterAdvancement::where('character_id', $this->character->id)
            ->where('advancement_type', 'domain_card')
            ->first();
        
        expect($advancement)->not->toBeNull();

        // Verify two domain cards were created (one for tier achievement, one for advancement)
        $domainCards = CharacterDomainCard::where('character_id', $this->character->id)->get();
        expect($domainCards)->toHaveCount(2);
    });

    test('evasion advancement works correctly', function () {
        $component = livewire(CharacterLevelUp::class, [
            'characterKey' => $this->character->character_key,
            'canEdit' => true,
        ]);

        // Set up tier achievements
        $component->set('new_experience_name', 'Leadership')
            ->set('new_experience_description', 'Leading others')
            ->call('addTierExperience');

        $availableCards = $component->instance()->getAvailableDomainCards(2);
        $firstCard = $availableCards[0];
        $component->set('advancement_choices.tier_domain_card', $firstCard['key']);

        // Select evasion advancement (option 5)
        $component->call('selectFirstAdvancement', 5);
        $component->call('selectSecondAdvancement', 1); // Hit Point

        // Confirm level up
        $component->call('confirmLevelUp');

        // Verify evasion advancement was created
        $advancement = CharacterAdvancement::where('character_id', $this->character->id)
            ->where('advancement_type', 'evasion')
            ->first();
        
        expect($advancement)->not->toBeNull();
        expect($advancement->description)->toContain('Evasion');
    });

    test('multiple selections of same advancement type work correctly', function () {
        $component = livewire(CharacterLevelUp::class, [
            'characterKey' => $this->character->character_key,
            'canEdit' => true,
        ]);

        // Set up tier achievements
        $component->set('new_experience_name', 'Leadership')
            ->set('new_experience_description', 'Leading others')
            ->call('addTierExperience');

        $availableCards = $component->instance()->getAvailableDomainCards(2);
        $firstCard = $availableCards[0];
        $component->set('advancement_choices.tier_domain_card', $firstCard['key']);

        // Select hit point advancement twice (options 1 and 1 again)
        $component->call('selectFirstAdvancement', 1);
        $component->call('selectSecondAdvancement', 1);

        // Confirm level up
        $component->call('confirmLevelUp');

        // Verify two hit point advancements were created
        $advancements = CharacterAdvancement::where('character_id', $this->character->id)
            ->where('advancement_type', 'hit_point')
            ->get();
        
        expect($advancements)->toHaveCount(2);
        expect($advancements[0]->advancement_number)->toBe(1);
        expect($advancements[1]->advancement_number)->toBe(2);
    });

    test('advancement validation prevents invalid selections', function () {
        $component = livewire(CharacterLevelUp::class, [
            'characterKey' => $this->character->character_key,
            'canEdit' => true,
        ]);

        // Set up tier achievements
        $component->set('new_experience_name', 'Leadership')
            ->set('new_experience_description', 'Leading others')
            ->call('addTierExperience');

        $availableCards = $component->instance()->getAvailableDomainCards(2);
        $firstCard = $availableCards[0];
        $component->set('advancement_choices.tier_domain_card', $firstCard['key']);

        // Select trait bonus advancement but don't provide trait choices
        $component->call('selectFirstAdvancement', 0);
        $component->call('selectSecondAdvancement', 1);

        // Try to confirm without required trait selections
        $isValid = $component->instance()->validateSelections();
        expect($isValid)->toBe(false);

        // Now provide valid trait selections
        $component->set('advancement_choices.0.traits', ['agility', 'strength']);
        
        $isValid = $component->instance()->validateSelections();
        expect($isValid)->toBe(true);
    });

    test('tier options are correctly loaded for warrior class', function () {
        $component = livewire(CharacterLevelUp::class, [
            'characterKey' => $this->character->character_key,
            'canEdit' => true,
        ]);

        $tierOptions = $component->get('tier_options');
        expect($tierOptions)->toBeArray();
        expect($tierOptions['selectCount'])->toBe(2);
        expect($tierOptions['options'])->toHaveCount(6);

        // Verify all expected advancement types are present
        $descriptions = collect($tierOptions['options'])->pluck('description')->toArray();
        
        expect($descriptions)->toContain('Gain a +1 bonus to two unmarked character traits and mark them.');
        expect($descriptions)->toContain('Permanently gain one Hit Point slot.');
        expect($descriptions)->toContain('Permanently gain one Stress slot.');
        expect($descriptions)->toContain('Permanently gain a +1 bonus to two Experiences.');
        expect($descriptions)->toContain('Choose an additional domain card of your level or lower from a domain you have access to (up to level 4).');
        expect($descriptions)->toContain('Permanently gain a +1 bonus to your Evasion.');
    });

    test('available advancement options respect max selections', function () {
        $component = livewire(CharacterLevelUp::class, [
            'characterKey' => $this->character->character_key,
            'canEdit' => true,
        ]);

        // Test that we can get available options for each step
        $firstStepOptions = $component->instance()->getAvailableAdvancementsForStep('first');
        $secondStepOptions = $component->instance()->getAvailableAdvancementsForStep('second');

        expect($firstStepOptions)->toBeArray();
        expect($secondStepOptions)->toBeArray();

        // Initially, all options should be available
        expect(count($firstStepOptions))->toBe(6);
        expect(count($secondStepOptions))->toBe(6);

        // Select an advancement with maxSelections = 1 (e.g., evasion)
        $component->call('selectFirstAdvancement', 5);
        
        $secondStepOptions = $component->instance()->getAvailableAdvancementsForStep('second');
        
        // Evasion should NOT be available since it has maxSelections = 1 and we've already selected it once
        expect(array_key_exists(5, $secondStepOptions))->toBe(false);
        
        // But trait bonus (option 0) should still be available since it has maxSelections = 3
        expect(array_key_exists(0, $secondStepOptions))->toBe(true);
    });
});
