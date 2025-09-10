<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

describe('Character Viewer Display Validation', function () {

    it('validates character viewer displays basic character information correctly', function () {
        $character = Character::factory()->create([
            'character_data' => [
                'selected_class' => 'wizard',
                'selected_subclass' => 'school-of-knowledge',
                'selected_ancestry' => 'human',
                'selected_community' => 'loreborne',
                'assigned_traits' => ['agility' => 1, 'strength' => 0, 'finesse' => 0, 'instinct' => 2, 'presence' => 1, 'knowledge' => -1],
                'selected_equipment' => [
                    ['key' => 'staff', 'type' => 'weapon', 'category' => 'weapons'],
                    ['key' => 'robes', 'type' => 'armor', 'category' => 'armor'],
                ],
                'experiences' => [
                    ['name' => 'Arcane Studies', 'description' => 'Years of magical research', 'modifier' => 2],
                    ['name' => 'Ancient Languages', 'description' => 'Knowledge of old tongues', 'modifier' => 2],
                ],
                'selected_domain_cards' => ['ancient-lore', 'radiant-barrier', 'scholarly-insight'],
            ],
            'name' => 'Eldara Moonshadow',
            'pronouns' => 'she/her',
        ]);

        // Navigate directly to character viewer
        $page = visit('/character/'.$character->character_key);

        // Verify basic character information is displayed
        $page->assertPresent('[pest="character-viewer-top-banner"]')
            ->assertPresent('[pest="character-name"]')
            ->assertSee('Eldara Moonshadow')
            ->assertPresent('[pest="character-pronouns"]')
            ->assertSee('she/her');

        // Verify heritage display
        $page->assertPresent('[pest="character-heritage"]')
            ->assertSee('Loreborne Human')
            ->assertSee('Wizard')
            ->assertSee('School of Knowledge');

        // Verify class domains
        $page->assertPresent('[pest="class-domains"]')
            ->assertSee('Codex & Splendor');

        // Verify character stats are displayed
        $page->assertPresent('[pest="character-stats"]')
            ->assertPresent('[pest="evasion-stat"]')
            ->assertPresent('[pest="armor-stat"]');
    });

    it('validates damage and health section displays correctly', function () {
        $character = Character::factory()->create([
            'character_data' => [
                'selected_class' => 'warrior',
                'selected_subclass' => 'stalwart',
                'selected_ancestry' => 'dwarf',
                'selected_community' => 'ridgeborne',
                'assigned_traits' => ['agility' => 0, 'strength' => 2, 'finesse' => 1, 'instinct' => 1, 'presence' => 0, 'knowledge' => -1],
                'selected_equipment' => [
                    ['key' => 'longsword', 'type' => 'weapon', 'category' => 'weapons'],
                    ['key' => 'chainmail-armor', 'type' => 'armor', 'category' => 'armor'],
                ],
                'experiences' => [],
                'selected_domain_cards' => [],
            ],
            'name' => 'Thorin Ironforge',
        ]);

        $page = visit('/character/'.$character->character_key);

        // Verify damage and health section
        $page->assertPresent('[pest="damage-health-section"]');

        // Verify damage thresholds are displayed
        $page->assertPresent('[pest="damage-thresholds"]');

        // Verify hit points section
        $page->assertPresent('[pest="hit-points-section"]')
            ->assertPresent('[pest="hp-counter"]')
            ->assertPresent('[pest="hit-points-track"]');

        // Verify stress section
        $page->assertPresent('[pest="stress-section"]')
            ->assertPresent('[pest="stress-counter"]')
            ->assertPresent('[pest="stress-track"]');

        // Verify armor slots section
        $page->assertPresent('[pest="armor-slots-section"]')
            ->assertPresent('[pest="armor-slots-track"]');
    });

    it('validates equipment sections display correctly', function () {
        $character = Character::factory()->create([
            'character_data' => [
                'selected_class' => 'ranger',
                'selected_subclass' => 'beast-master',
                'selected_ancestry' => 'elf',
                'selected_community' => 'wildborne',
                'assigned_traits' => ['agility' => 2, 'strength' => 1, 'finesse' => 1, 'instinct' => 0, 'presence' => 0, 'knowledge' => -1],
                'selected_equipment' => [
                    ['key' => 'longbow', 'type' => 'weapon', 'category' => 'weapons'],
                    ['key' => 'hatchet', 'type' => 'weapon', 'category' => 'weapons'],
                    ['key' => 'leathers', 'type' => 'armor', 'category' => 'armor'],
                    ['key' => 'rope', 'type' => 'item', 'category' => 'items'],
                    ['key' => 'health-potion', 'type' => 'consumable', 'category' => 'consumables'],
                ],
                'experiences' => [],
                'selected_domain_cards' => [],
            ],
            'name' => 'Lyralei Swiftarrow',
        ]);

        $page = visit('/character/'.$character->character_key);

        // Verify active weapons section
        $page->assertPresent('[pest="active-weapons-section"]');

        // Verify active armor section
        $page->assertPresent('[pest="active-armor-section"]');

        // Verify equipment inventory section
        $page->assertPresent('[pest="equipment-section"]')
            ->assertPresent('[pest="inventory-section"]')
            ->assertPresent('[pest="inventory-list"]');
    });

    it('validates experiences section displays correctly for Clank ancestry', function () {
        $character = Character::factory()->create([
            'character_data' => [
                'selected_class' => 'rogue',
                'selected_subclass' => 'nightwalker',
                'selected_ancestry' => 'clank',
                'selected_community' => 'slyborne',
                'assigned_traits' => ['agility' => 2, 'strength' => 0, 'finesse' => 1, 'instinct' => 1, 'presence' => 0, 'knowledge' => -1],
                'selected_equipment' => [],
                'experiences' => [
                    ['name' => 'Lock Picking', 'description' => 'Mechanical expertise', 'modifier' => 2],
                    ['name' => 'Urban Navigation', 'description' => 'City knowledge', 'modifier' => 2],
                ],
                'clank_bonus_experience' => 'Lock Picking',
                'selected_domain_cards' => [],
            ],
            'name' => 'Cogwright Seven',
        ]);

        $page = visit('/character/'.$character->character_key);

        // Verify experiences section exists
        $page->assertPresent('[pest="experience-section"]')
            ->assertPresent('[pest="experience-list"]');

        // Verify experiences are displayed
        $page->assertSee('Lock Picking')
            ->assertSee('Urban Navigation');
    });

    it('validates domain cards section displays correctly', function () {
        $character = Character::factory()->create([
            'character_data' => [
                'selected_class' => 'druid',
                'selected_subclass' => 'nature-spirit',
                'selected_ancestry' => 'elf',
                'selected_community' => 'wildborne',
                'assigned_traits' => ['agility' => 1, 'strength' => 0, 'finesse' => 1, 'instinct' => 2, 'presence' => 0, 'knowledge' => -1],
                'selected_equipment' => [],
                'experiences' => [],
                'selected_domain_cards' => ['gifted-tracker', 'rune-ward'],
            ],
            'name' => 'Thalion Greenleaf',
        ]);

        $page = visit('/character/'.$character->character_key);

        // Verify domain cards section
        $page->assertPresent('[pest="domain-cards-section"]');
    });

    it('validates subclass features section displays correctly', function () {
        $character = Character::factory()->create([
            'character_data' => [
                'selected_class' => 'wizard',
                'selected_subclass' => 'school-of-knowledge',
                'selected_ancestry' => 'human',
                'selected_community' => 'loreborne',
                'assigned_traits' => ['agility' => 0, 'strength' => 0, 'finesse' => 1, 'instinct' => 1, 'presence' => 0, 'knowledge' => 2],
                'selected_equipment' => [],
                'experiences' => [],
                'selected_domain_cards' => [],
            ],
            'name' => 'Mira Scholarly',
        ]);

        $page = visit('/character/'.$character->character_key);

        // Verify subclass features section exists
        $page->assertPresent('[pest="subclass-features-section"]')
            ->assertSee('School of Knowledge Features');
    });

    it('validates ancestry features section displays correctly', function () {
        $character = Character::factory()->create([
            'character_data' => [
                'selected_class' => 'rogue',
                'selected_subclass' => 'nightwalker',
                'selected_ancestry' => 'clank',
                'selected_community' => 'slyborne',
                'assigned_traits' => ['agility' => 2, 'strength' => 0, 'finesse' => 1, 'instinct' => 1, 'presence' => 0, 'knowledge' => -1],
                'selected_equipment' => [],
                'experiences' => [],
                'selected_domain_cards' => [],
            ],
            'name' => 'Cogwright Seven',
        ]);

        $page = visit('/character/'.$character->character_key);

        // Verify ancestry features section exists
        $page->assertPresent('[pest="ancestry-features-section"]')
            ->assertSee('Clank Features');
    });

    it('validates community features section displays correctly', function () {
        $character = Character::factory()->create([
            'character_data' => [
                'selected_class' => 'guardian',
                'selected_subclass' => 'protector',
                'selected_ancestry' => 'dwarf',
                'selected_community' => 'ridgeborne',
                'assigned_traits' => ['agility' => 0, 'strength' => 2, 'finesse' => 0, 'instinct' => 1, 'presence' => 1, 'knowledge' => -1],
                'selected_equipment' => [],
                'experiences' => [],
                'selected_domain_cards' => [],
            ],
            'name' => 'Thorin Ironforge',
        ]);

        $page = visit('/character/'.$character->character_key);

        // Verify community features section exists
        $page->assertPresent('[pest="community-features-section"]')
            ->assertSee('Ridgeborne Features');
    });

    it('validates playtest content displays proper labels', function () {
        $character = Character::factory()->create([
            'character_data' => [
                'selected_class' => 'druid',
                'selected_subclass' => 'nature-spirit',
                'selected_ancestry' => 'earthkin', // Playtest ancestry
                'selected_community' => 'wildborne',
                'assigned_traits' => ['agility' => 1, 'strength' => 1, 'finesse' => 0, 'instinct' => 2, 'presence' => 0, 'knowledge' => -1],
                'selected_equipment' => [],
                'experiences' => [],
                'selected_domain_cards' => [],
            ],
            'name' => 'Stone Walker',
        ]);

        $page = visit('/character/'.$character->character_key);

        // Verify playtest label is displayed for Earthkin
        $page->assertSee('Void - Playtest');
    });

    it('validates viewer handles missing or empty data gracefully', function () {
        $character = Character::factory()->create([
            'character_data' => [
                'selected_class' => 'sorcerer',
                'selected_subclass' => null,
                'selected_ancestry' => 'human',
                'selected_community' => 'wanderborne',
                'assigned_traits' => [],
                'selected_equipment' => [],
                'experiences' => [],
                'selected_domain_cards' => [],
            ],
            'name' => 'Unnamed Character',
        ]);

        $page = visit('/character/'.$character->character_key);

        // Should still render basic structure even with minimal data
        $page->assertPresent('[pest="character-viewer-top-banner"]')
            ->assertPresent('[pest="damage-health-section"]')
            ->assertPresent('[pest="equipment-section"]');

        // Should show character name or default
        $page->assertSee('Unnamed Character');

        // Should handle empty experiences gracefully
        $page->assertNotPresent('[pest="experience-section"]');
    });

});
