<?php

declare(strict_types=1);

use Domain\Character\Data\CharacterBuilderData;

describe('CharacterBuilderData Computed Stats', function () {
    
    it('calculates School of War hit point bonus correctly', function () {
        // Create a wizard with School of War subclass (should get +1 HP)
        $character_data = CharacterBuilderData::from([
            'selected_class' => 'wizard',
            'selected_subclass' => 'school of war',
            'selected_ancestry' => 'human',
            'selected_community' => 'loreborne',
            'assigned_traits' => ['agility' => -1, 'strength' => 0, 'finesse' => 0, 'instinct' => 1, 'presence' => 1, 'knowledge' => 2],
            'selected_equipment' => [],
            'background_answers' => ['answer1', 'answer2', 'answer3'],
            'connections' => ['connection1', 'connection2', 'connection3'],
            'experiences' => [],
            'selected_domain_cards' => [],
            'name' => 'Test School of War Wizard'
        ]);

        // Get class data (Wizard starts with 4 HP)
        $class_data = [
            'startingEvasion' => 9,
            'startingHitPoints' => 4
        ];

        $computed_stats = $character_data->getComputedStats($class_data);

        // School of War should add +1 HP, so: 4 (base) + 1 (School of War) = 5
        expect($computed_stats['final_hit_points'])->toBe(5, 'School of War wizard should have 5 HP (4 base + 1 subclass bonus)');
        expect($computed_stats['hit_points'])->toBe(5, 'Legacy hit_points field should also be 5');
    });

    it('calculates multiple HP bonuses correctly', function () {
        // Create a wizard with School of War subclass (+1 HP) AND Giant ancestry (+1 HP)
        $character_data = CharacterBuilderData::from([
            'selected_class' => 'wizard',
            'selected_subclass' => 'school of war',
            'selected_ancestry' => 'giant',
            'selected_community' => 'wildborne',
            'assigned_traits' => ['agility' => 0, 'strength' => 1, 'finesse' => 0, 'instinct' => 1, 'presence' => 2, 'knowledge' => -1],
            'selected_equipment' => [],
            'background_answers' => ['answer1', 'answer2', 'answer3'],
            'connections' => ['connection1', 'connection2', 'connection3'],
            'experiences' => [],
            'selected_domain_cards' => [],
            'name' => 'Test Giant School of War Wizard'
        ]);

        // Get class data (Wizard starts with 4 HP)
        $class_data = [
            'startingEvasion' => 9,
            'startingHitPoints' => 4
        ];

        $computed_stats = $character_data->getComputedStats($class_data);

        // Giant (+1) + School of War (+1) should add +2 HP total, so: 4 (base) + 1 (ancestry) + 1 (subclass) = 6
        expect($computed_stats['final_hit_points'])->toBe(6, 'Giant School of War wizard should have 6 HP (4 base + 1 ancestry + 1 subclass)');
        expect($computed_stats['hit_points'])->toBe(6, 'Legacy hit_points field should also be 6');
    });

    it('calculates subclass without HP bonus correctly', function () {
        // Create a wizard with School of Knowledge subclass (no HP bonus)
        $character_data = CharacterBuilderData::from([
            'selected_class' => 'wizard',
            'selected_subclass' => 'school of knowledge',
            'selected_ancestry' => 'human',
            'selected_community' => 'loreborne',
            'assigned_traits' => ['agility' => -1, 'strength' => 0, 'finesse' => 0, 'instinct' => 1, 'presence' => 1, 'knowledge' => 2],
            'selected_equipment' => [],
            'background_answers' => ['answer1', 'answer2', 'answer3'],
            'connections' => ['connection1', 'connection2', 'connection3'],
            'experiences' => [],
            'selected_domain_cards' => [],
            'name' => 'Test School of Knowledge Wizard'
        ]);

        // Get class data (Wizard starts with 4 HP)
        $class_data = [
            'startingEvasion' => 9,
            'startingHitPoints' => 4
        ];

        $computed_stats = $character_data->getComputedStats($class_data);

        // School of Knowledge has no HP bonus, so: 4 (base) + 0 (subclass) = 4
        expect($computed_stats['final_hit_points'])->toBe(4, 'School of Knowledge wizard should have 4 HP (4 base + 0 subclass bonus)');
        expect($computed_stats['hit_points'])->toBe(4, 'Legacy hit_points field should also be 4');
    });
});
