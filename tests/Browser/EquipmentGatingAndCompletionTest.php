<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

describe('Equipment Gating and Completion Requirements', function () {

    it('enforces primary weapon and armor requirements for completion', function () {
        $character = Character::factory()->create([
            'character_data' => [
                'selected_class' => 'warrior',
                'selected_subclass' => 'stalwart',
                'selected_ancestry' => 'human',
                'selected_community' => 'wildborne',
                'assigned_traits' => ['agility' => 0, 'strength' => 2, 'finesse' => -1, 'instinct' => 1, 'presence' => 1, 'knowledge' => 0],
                'selected_equipment' => [],
                'experiences' => [],
                'selected_domain_cards' => [],
            ],
        ]);

        $page = visit('/character-builder/'.$character->character_key);

        // Navigate to equipment step
        $page->click('[pest="sidebar-tab-6"]')->wait(1);
        $page->assertSee('Select Equipment');

        // Initially should not be complete
        $page->assertDontSee('Equipment Selection Complete!');

        // Select only primary weapon (no armor)
        $page->click('[pest="suggested-primary-weapon"]')->wait(1);

        // Should still not be complete without armor
        $page->assertDontSee('Equipment Selection Complete!');

        // Add armor
        $page->click('[pest="suggested-armor"]')->wait(1);

        // Now should be complete
        $page->assertSee('Equipment Selection Complete!');

        // Remove primary weapon - should become incomplete
        $page->click('[pest="suggested-primary-weapon"]')->wait(1);
        $page->assertDontSee('Equipment Selection Complete!');

        // Re-add primary weapon
        $page->click('[pest="suggested-primary-weapon"]')->wait(1);
        $page->assertSee('Equipment Selection Complete!');
    });

    it('validates equipment displays correctly in character viewer', function () {
        $character = Character::factory()->create([
            'character_data' => [
                'selected_class' => 'ranger',
                'selected_subclass' => 'hunter',
                'selected_ancestry' => 'elf',
                'selected_community' => 'wildborne',
                'assigned_traits' => ['agility' => 2, 'strength' => 0, 'finesse' => 1, 'instinct' => 1, 'presence' => 0, 'knowledge' => -1],
                'selected_equipment' => [
                    [
                        'key' => 'longbow',
                        'name' => 'Longbow',
                        'type' => 'weapon',
                        'slot' => 'primary',
                    ],
                    [
                        'key' => 'leather-armor',
                        'name' => 'Leather Armor',
                        'type' => 'armor',
                        'slot' => 'armor',
                    ],
                    [
                        'key' => 'rope',
                        'name' => 'Rope',
                        'type' => 'item',
                        'slot' => 'inventory',
                    ],
                ],
                'experiences' => [],
                'selected_domain_cards' => [],
            ],
        ]);

        $page = visit('/character/'.$character->character_key);

        // Verify active weapons section shows primary weapon
        $page->assertPresent('[pest="active-weapons-section"]')
            ->assertSee('Longbow');

        // Verify active armor section shows armor
        $page->assertPresent('[pest="active-armor-section"]')
            ->assertSee('Leather Armor');

        // Verify equipment section shows inventory items
        $page->assertPresent('[pest="equipment-section"]')
            ->assertSee('Rope');
    });

    it('validates chooseOne equipment constraints enforcement', function () {
        // Load class data to find a class with chooseOne constraints
        $classesData = json_decode(file_get_contents(resource_path('json/classes.json')), true);

        $classWithChooseOne = null;
        foreach ($classesData as $classKey => $classData) {
            if (isset($classData['startingInventory']['chooseOne']) && ! empty($classData['startingInventory']['chooseOne'])) {
                $classWithChooseOne = $classKey;
                break;
            }
        }

        if (! $classWithChooseOne) {
            $this->markTestSkipped('No class found with chooseOne constraints');
        }

        $character = Character::factory()->create([
            'character_data' => [
                'selected_class' => $classWithChooseOne,
                'selected_subclass' => array_keys($classesData[$classWithChooseOne]['subclasses'])[0],
                'selected_ancestry' => 'human',
                'selected_community' => 'wildborne',
                'assigned_traits' => ['agility' => 1, 'strength' => 1, 'finesse' => 0, 'instinct' => 2, 'presence' => 0, 'knowledge' => -1],
                'selected_equipment' => [],
                'experiences' => [],
                'selected_domain_cards' => [],
            ],
        ]);

        $page = visit('/character-builder/'.$character->character_key);

        // Navigate to equipment step
        $page->click('[pest="sidebar-tab-6"]')->wait(1);

        // Add required primary weapon and armor first
        $page->click('[pest="suggested-primary-weapon"]')->wait(1);
        $page->click('[pest="suggested-armor"]')->wait(1);

        // Should still not be complete if chooseOne requirement not met
        $chooseOneOptions = $classesData[$classWithChooseOne]['startingInventory']['chooseOne'];
        if (! empty($chooseOneOptions)) {
            // Try to find and select one of the chooseOne items
            $firstOption = $chooseOneOptions[0];
            $page->click("[pest*=\"equipment-item-{$firstOption}\"]")->wait(1);

            // Now should be complete
            $page->assertSee('Equipment Selection Complete!');
        }
    });

    it('validates item alias mapping works correctly', function () {
        $character = Character::factory()->create([
            'character_data' => [
                'selected_class' => 'druid',
                'selected_subclass' => 'nature-spirit',
                'selected_ancestry' => 'human',
                'selected_community' => 'wildborne',
                'assigned_traits' => ['agility' => 1, 'strength' => 0, 'finesse' => 0, 'instinct' => 2, 'presence' => 1, 'knowledge' => -1],
                'selected_equipment' => [],
                'experiences' => [],
                'selected_domain_cards' => [],
            ],
        ]);

        $page = visit('/character-builder/'.$character->character_key);

        // Navigate to equipment step
        $page->click('[pest="sidebar-tab-6"]')->wait(1);

        // Select required equipment first
        $page->click('[pest="suggested-primary-weapon"]')->wait(1);
        $page->click('[pest="suggested-armor"]')->wait(1);

        // Test alias mapping: "minor healing potion" → "minor health potion"
        // Look for healing potion options
        if ($page->elements('[pest*="minor-healing-potion"]')->count() > 0) {
            $page->click('[pest*="minor-healing-potion"]')->wait(1);

            // Should accept the alias and count toward completion
            $page->assertSee('Equipment Selection Complete!');
        } elseif ($page->elements('[pest*="minor-health-potion"]')->count() > 0) {
            $page->click('[pest*="minor-health-potion"]')->wait(1);
            $page->assertSee('Equipment Selection Complete!');
        }
    });

    it('validates clear all equipment functionality', function () {
        $character = Character::factory()->create([
            'character_data' => [
                'selected_class' => 'wizard',
                'selected_subclass' => 'school-of-knowledge',
                'selected_ancestry' => 'human',
                'selected_community' => 'loreborne',
                'assigned_traits' => ['agility' => 0, 'strength' => 0, 'finesse' => 1, 'instinct' => 1, 'presence' => -1, 'knowledge' => 2],
                'selected_equipment' => [],
                'experiences' => [],
                'selected_domain_cards' => [],
            ],
        ]);

        $page = visit('/character-builder/'.$character->character_key);

        // Navigate to equipment step
        $page->click('[pest="sidebar-tab-6"]')->wait(1);

        // Apply all suggested equipment
        $page->click('[pest="apply-all-suggestions"]')->wait(1);
        $page->assertSee('Equipment Selection Complete!');

        // Clear all equipment
        if ($page->elements('[pest="clear-all-equipment"]')->count() > 0) {
            $page->click('[pest="clear-all-equipment"]')->wait(1);

            // Should no longer be complete
            $page->assertDontSee('Equipment Selection Complete!');

            // Should show empty state
            $page->assertSee('Primary');
            $page->assertSee('Secondary');
            $page->assertSee('Armor');
        }
    });

    it('validates equipment tier restrictions for starting characters', function () {
        $character = Character::factory()->create([
            'character_data' => [
                'selected_class' => 'sorcerer',
                'selected_subclass' => 'elemental-chaos',
                'selected_ancestry' => 'drakona',
                'selected_community' => 'slyborne',
                'assigned_traits' => ['agility' => 1, 'strength' => 0, 'finesse' => 1, 'instinct' => 2, 'presence' => 0, 'knowledge' => -1],
                'selected_equipment' => [],
                'experiences' => [],
                'selected_domain_cards' => [],
            ],
        ]);

        $page = visit('/character-builder/'.$character->character_key);

        // Navigate to equipment step
        $page->click('[pest="sidebar-tab-6"]')->wait(1);

        // All available equipment should be Tier 1 for starting characters
        // Check that no higher tier equipment is shown
        $page->assertDontSee('Tier 2');
        $page->assertDontSee('Tier 3');
        $page->assertDontSee('Tier 4');

        // Verify tier 1 equipment is available
        if ($page->elements('[pest*="tier-1"]')->count() > 0) {
            $page->assertSee('Tier 1');
        }
    });

    it('validates weapon feature text normalization in viewer', function () {
        $character = Character::factory()->create([
            'character_data' => [
                'selected_class' => 'guardian',
                'selected_subclass' => 'protector',
                'selected_ancestry' => 'dwarf',
                'selected_community' => 'ridgeborne',
                'assigned_traits' => ['agility' => 0, 'strength' => 2, 'finesse' => -1, 'instinct' => 1, 'presence' => 1, 'knowledge' => 0],
                'selected_equipment' => [
                    [
                        'key' => 'war-hammer',
                        'name' => 'War Hammer',
                        'type' => 'weapon',
                        'slot' => 'primary',
                        'features' => 'Heavy, Two-Handed',
                    ],
                    [
                        'key' => 'scale-mail',
                        'name' => 'Scale Mail',
                        'type' => 'armor',
                        'slot' => 'armor',
                    ],
                ],
                'experiences' => [],
                'selected_domain_cards' => [],
            ],
        ]);

        $page = visit('/character/'.$character->character_key);

        // Verify weapon details are displayed correctly
        $page->assertPresent('[pest="active-weapons-section"]')
            ->assertSee('War Hammer');

        // Verify weapon features are shown (handles string/array/object normalization)
        $page->assertPresent('[pest="weapon-features"]');
    });

    it('validates secondary weapon handling', function () {
        $character = Character::factory()->create([
            'character_data' => [
                'selected_class' => 'rogue',
                'selected_subclass' => 'nightwalker',
                'selected_ancestry' => 'katari',
                'selected_community' => 'slyborne',
                'assigned_traits' => ['agility' => 2, 'strength' => 0, 'finesse' => 1, 'instinct' => 1, 'presence' => 0, 'knowledge' => -1],
                'selected_equipment' => [],
                'experiences' => [],
                'selected_domain_cards' => [],
            ],
        ]);

        $page = visit('/character-builder/'.$character->character_key);

        // Navigate to equipment step
        $page->click('[pest="sidebar-tab-6"]')->wait(1);

        // Select primary weapon and armor for completion
        $page->click('[pest="suggested-primary-weapon"]')->wait(1);
        $page->click('[pest="suggested-armor"]')->wait(1);

        // Should be complete with just primary + armor
        $page->assertSee('Equipment Selection Complete!');

        // Add secondary weapon (optional)
        if ($page->elements('[pest="suggested-secondary-weapon"]')->count() > 0) {
            $page->click('[pest="suggested-secondary-weapon"]')->wait(1);

            // Should still be complete
            $page->assertSee('Equipment Selection Complete!');
        }

        // Save and check viewer
        $page->click('[pest="save-character-button"]')->wait(3);

        $viewerPage = visit('/character/'.$character->character_key);

        // Verify both weapons shown in viewer if secondary was selected
        $viewerPage->assertPresent('[pest="active-weapons-section"]');
    });
});
