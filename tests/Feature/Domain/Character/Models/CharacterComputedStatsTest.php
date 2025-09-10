<?php

declare(strict_types=1);

use Domain\Character\Models\Character;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Character Model Computed Stats', function () {

    it('calculates School of War hit point bonus correctly from saved character data', function () {
        // Create a character with School of War subclass saved in database
        $character = Character::factory()->create([
            'class' => 'wizard',
            'subclass' => 'school of war',
            'ancestry' => 'human',
            'community' => 'loreborne',
            'character_data' => [
                'assigned_traits' => ['agility' => -1, 'strength' => 0, 'finesse' => 0, 'instinct' => 1, 'presence' => 1, 'knowledge' => 2],
                'selected_equipment' => [],
                'background_answers' => ['answer1', 'answer2', 'answer3'],
                'connections' => ['connection1', 'connection2', 'connection3'],
                'experiences' => [],
                'selected_domain_cards' => [],
            ],
        ]);

        // Get class data (Wizard starts with 4 HP)
        $class_data = [
            'startingEvasion' => 9,
            'startingHitPoints' => 4,
        ];

        $computed_stats = $character->getComputedStats($class_data);

        // School of War should add +1 HP, so: 4 (base) + 1 (School of War) = 5
        expect($computed_stats['final_hit_points'])->toBe(5, 'School of War wizard should have 5 HP (4 base + 1 subclass bonus)');
        expect($computed_stats['hit_points'])->toBe(5, 'Legacy hit_points field should also be 5');
    });

    it('calculates multiple HP bonuses correctly from saved character data', function () {
        // Create a character with School of War subclass (+1 HP) AND Giant ancestry (+1 HP)
        $character = Character::factory()->create([
            'class' => 'wizard',
            'subclass' => 'school of war',
            'ancestry' => 'giant',
            'community' => 'wildborne',
            'character_data' => [
                'assigned_traits' => ['agility' => 0, 'strength' => 1, 'finesse' => 0, 'instinct' => 1, 'presence' => 2, 'knowledge' => -1],
                'selected_equipment' => [],
                'background_answers' => ['answer1', 'answer2', 'answer3'],
                'connections' => ['connection1', 'connection2', 'connection3'],
                'experiences' => [],
                'selected_domain_cards' => [],
            ],
        ]);

        // Get class data (Wizard starts with 4 HP)
        $class_data = [
            'startingEvasion' => 9,
            'startingHitPoints' => 4,
        ];

        $computed_stats = $character->getComputedStats($class_data);

        // Giant (+1) + School of War (+1) should add +2 HP total, so: 4 (base) + 1 (ancestry) + 1 (subclass) = 6
        expect($computed_stats['final_hit_points'])->toBe(6, 'Giant School of War wizard should have 6 HP (4 base + 1 ancestry + 1 subclass)');
        expect($computed_stats['hit_points'])->toBe(6, 'Legacy hit_points field should also be 6');
    });
});
