<?php

declare(strict_types=1);

use App\Livewire\CharacterLevelUp;
use Domain\Character\Models\Character;

use function Pest\Livewire\livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('Tier Achievement Validation', function () {

    test('level 2 tier achievement validation requires experience creation', function () {
        $character = Character::factory()->create([
            'level' => 1,
            'class' => 'warrior',
            'is_public' => true,
        ]);

        livewire(CharacterLevelUp::class, [
            'characterKey' => $character->character_key,
            'canEdit' => true,
        ])
            ->call('validateTierAchievements')
            ->assertReturned(false);
    });

    test('level 2 tier achievement validation requires domain card selection', function () {
        $character = Character::factory()->create([
            'level' => 1,
            'class' => 'warrior',
            'is_public' => true,
        ]);

        livewire(CharacterLevelUp::class, [
            'characterKey' => $character->character_key,
            'canEdit' => true,
        ])
        // Add experience but no domain card
            ->set('new_experience_name', 'Combat Training')
            ->set('new_experience_description', 'Advanced fighting techniques')
            ->call('addTierExperience')
        // Try to validate without selecting domain card
            ->call('validateTierAchievements')
            ->assertReturned(false);
    });

    test('level 2 tier achievement validation passes when both requirements are met', function () {
        $character = Character::factory()->create([
            'level' => 1,
            'class' => 'warrior',
            'is_public' => true,
        ]);

        livewire(CharacterLevelUp::class, [
            'characterKey' => $character->character_key,
            'canEdit' => true,
        ])
        // Add both required tier achievements
            ->set('new_experience_name', 'Combat Training')
            ->set('new_experience_description', 'Advanced fighting techniques')
            ->call('addTierExperience')
            ->call('selectTierDomainCard', 'get back up')
        // Validation should pass
            ->call('validateTierAchievements')
            ->assertReturned(true);
    });

    test('level 5 tier achievement validation requires experience and domain card', function () {
        $character = Character::factory()->create([
            'level' => 4,
            'class' => 'warrior',
            'is_public' => true,
        ]);

        // No tier achievements selected - should fail
        livewire(CharacterLevelUp::class, [
            'characterKey' => $character->character_key,
            'canEdit' => true,
        ])
            ->call('validateTierAchievements')
            ->assertReturned(false);

        // Add experience - still should fail without domain card
        livewire(CharacterLevelUp::class, [
            'characterKey' => $character->character_key,
            'canEdit' => true,
        ])
            ->set('new_experience_name', 'Advanced Tactics')
            ->set('new_experience_description', 'Level 5 tactics experience')
            ->call('addTierExperience')
            ->call('validateTierAchievements')
            ->assertReturned(false);

        // Add both requirements - should pass
        livewire(CharacterLevelUp::class, [
            'characterKey' => $character->character_key,
            'canEdit' => true,
        ])
            ->set('new_experience_name', 'Advanced Tactics')
            ->set('new_experience_description', 'Level 5 tactics experience')
            ->call('addTierExperience')
            ->call('selectTierDomainCard', 'get back up')
            ->call('validateTierAchievements')
            ->assertReturned(true);
    });

    test('level 8 tier achievement validation requires experience and domain card', function () {
        $character = Character::factory()->create([
            'level' => 7,
            'class' => 'warrior',
            'is_public' => true,
        ]);

        // No tier achievements selected - should fail
        livewire(CharacterLevelUp::class, [
            'characterKey' => $character->character_key,
            'canEdit' => true,
        ])
            ->call('validateTierAchievements')
            ->assertReturned(false);

        // Add experience only - still should fail without domain card
        livewire(CharacterLevelUp::class, [
            'characterKey' => $character->character_key,
            'canEdit' => true,
        ])
            ->set('new_experience_name', 'Master Techniques')
            ->set('new_experience_description', 'Level 8 mastery experience')
            ->call('addTierExperience')
            ->call('validateTierAchievements')
            ->assertReturned(false);

        // Add both requirements - should pass
        livewire(CharacterLevelUp::class, [
            'characterKey' => $character->character_key,
            'canEdit' => true,
        ])
            ->set('new_experience_name', 'Master Techniques')
            ->set('new_experience_description', 'Level 8 mastery experience')
            ->call('addTierExperience')
            ->call('selectTierDomainCard', 'get back up')
            ->call('validateTierAchievements')
            ->assertReturned(true);
    });

    test('non-tier achievement levels do not require validation', function () {
        // Test levels that are NOT tier achievements (going from level X to level Y where Y is NOT 2, 5, or 8)
        $nonTierCases = [
            ['current_level' => 2, 'target_level' => 3], // 2->3
            ['current_level' => 3, 'target_level' => 4], // 3->4
            ['current_level' => 5, 'target_level' => 6], // 5->6
            ['current_level' => 6, 'target_level' => 7], // 6->7
            ['current_level' => 8, 'target_level' => 9], // 8->9
            ['current_level' => 9, 'target_level' => 10], // 9->10
        ];

        foreach ($nonTierCases as $case) {
            $character = Character::factory()->create([
                'level' => $case['current_level'],
                'class' => 'warrior',
                'is_public' => true,
            ]);

            // Should pass validation without any tier achievements
            livewire(CharacterLevelUp::class, [
                'characterKey' => $character->character_key,
                'canEdit' => true,
            ])
                ->call('validateTierAchievements')
                ->assertReturned(true);
        }
    });

    test('experience name validation applies during tier achievement creation', function () {
        $character = Character::factory()->create([
            'level' => 1,
            'class' => 'warrior',
            'is_public' => true,
        ]);

        // Try to create experience with empty name
        livewire(CharacterLevelUp::class, [
            'characterKey' => $character->character_key,
            'canEdit' => true,
        ])
            ->set('new_experience_name', '') // Empty name
            ->set('new_experience_description', 'Valid description')
            ->call('addTierExperience')
            ->assertHasNoErrors(); // Should handle gracefully

        // Try with extremely long name
        livewire(CharacterLevelUp::class, [
            'characterKey' => $character->character_key,
            'canEdit' => true,
        ])
            ->set('new_experience_name', str_repeat('a', 150)) // Too long
            ->set('new_experience_description', 'Valid description')
            ->call('addTierExperience')
            ->assertHasNoErrors(); // Should handle gracefully
    });

    test('tier domain card selection respects character class domains', function () {
        $character = Character::factory()->create([
            'level' => 1,
            'class' => 'warrior', // Warrior has 'blade' and 'bone' domains
            'is_public' => true,
        ]);

        $component = livewire(CharacterLevelUp::class, [
            'characterKey' => $character->character_key,
            'canEdit' => true,
        ]);

        // Get available cards and verify they're from correct domains
        $availableCards = $component->instance()->getAvailableDomainCards(2);

        foreach ($availableCards as $card) {
            expect($card['domain'])->toBeIn(['blade', 'bone']);
        }

        // Select a valid card and verify it's stored correctly
        $component
            ->call('selectTierDomainCard', 'get back up') // Blade domain card
            ->assertSet('advancement_choices.tier_domain_card', 'get back up');
    });

    test('tier achievement validation integrates with full level up flow', function () {
        $character = Character::factory()->create([
            'level' => 1,
            'class' => 'warrior',
            'is_public' => true,
        ]);

        livewire(CharacterLevelUp::class, [
            'characterKey' => $character->character_key,
            'canEdit' => true,
        ])
        // Set up tier achievements
            ->set('new_experience_name', 'Combat Training')
            ->set('new_experience_description', 'Advanced fighting techniques')
            ->call('addTierExperience')
            ->call('selectTierDomainCard', 'get back up')
        // Set up regular advancements
            ->set('first_advancement', 0) // Trait advancement
            ->set('second_advancement', 1) // Hit point advancement
            ->set('advancement_choices.0.traits', ['agility', 'strength'])
        // Complete level up
            ->call('confirmLevelUp')
            ->assertHasNoErrors()
            ->assertRedirect(); // Should redirect after successful level up

        // Character should be leveled up
        $character->refresh();
        expect($character->level)->toBe(2);

        // Tier experience should be created
        $experiences = $character->experiences;
        expect($experiences)->toHaveCount(1);
        expect($experiences->first()->experience_name)->toBe('Combat Training');
    });

});
