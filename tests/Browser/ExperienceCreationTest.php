<?php

declare(strict_types=1);

describe('Experience Creation', function () {
    beforeEach(function () {
        $this->character = \Domain\Character\Models\Character::factory()->create([
            'character_data' => [
                'selected_class' => 'warrior',
                'selected_subclass' => 'stalwart',
                'assigned_traits' => ['agility' => 2, 'strength' => 1, 'finesse' => 1, 'instinct' => 0, 'presence' => 0, 'knowledge' => -1],
                'selected_equipment' => [],
                'experiences' => [],
            ]
        ]);
    });

    it('can add a new experience', function () {
        visit('/character-builder/' . $this->character->storage_key)
            ->click('[pest="sidebar-tab-8"]')
            ->wait(1)
            ->assertPresent('[pest="experience-name-input"]')
            ->type('[pest="experience-name-input"]', 'Wilderness Survival')
            ->type('[pest="experience-description-input"]', 'Years of surviving in the harsh wilderness')
            ->click('[pest="add-experience-button"]')
            ->wait(1)
            ->assertPresent('[pest="experience-item"]')
            ->assertSee('Wilderness Survival')
            ->assertSee('Years of surviving in the harsh wilderness');
    });

    it('can remove an experience', function () {
        // First add an experience
        visit('/character-builder/' . $this->character->storage_key)
            ->click('[pest="sidebar-tab-8"]')
            ->wait(1)
            ->assertPresent('[pest="experience-name-input"]')
            ->type('[pest="experience-name-input"]', 'Combat Training')
            ->type('[pest="experience-description-input"]', 'Military combat training')
            ->click('[pest="add-experience-button"]')
            ->wait(1)
            ->assertPresent('[pest="experience-item"]')
            ->assertSee('Combat Training')
            // Then remove it
            ->click('[pest="remove-experience"][pest-index="0"]')
            ->wait(1)
            ->assertMissing('[pest="experience-item"]')
            ->assertDontSee('Combat Training');
    });

    it('can edit an experience description', function () {
        // First add an experience
        visit('/character-builder/' . $this->character->storage_key)
            ->click('[pest="sidebar-tab-8"]')
            ->wait(1)
            ->assertPresent('[pest="experience-name-input"]')
            ->type('[pest="experience-name-input"]', 'Blacksmithing')
            ->type('[pest="experience-description-input"]', 'Basic metalworking')
            ->click('[pest="add-experience-button"]')
            ->wait(1)
            ->assertPresent('[pest="experience-item"]')
            ->assertSee('Basic metalworking')
            // Then edit it
            ->click('[pest="edit-experience"][pest-index="0"]')
            ->wait(1)
            ->assertPresent('[pest="edit-experience-description"][pest-index="0"]')
            ->clear('[pest="edit-experience-description"][pest-index="0"]')
            ->type('[pest="edit-experience-description"][pest-index="0"]', 'Master-level blacksmithing and weapon crafting')
            ->click('[pest="save-experience-edit"][pest-index="0"]')
            ->wait(1)
            ->assertMissing('[pest="edit-experience-description"][pest-index="0"]')
            ->assertSee('Master-level blacksmithing and weapon crafting')
            ->assertDontSee('Basic metalworking');
    });

    it('can cancel editing an experience', function () {
        // First add an experience
        visit('/character-builder/' . $this->character->storage_key)
            ->click('[pest="sidebar-tab-8"]')
            ->wait(1)
            ->assertPresent('[pest="experience-name-input"]')
            ->type('[pest="experience-name-input"]', 'Research')
            ->type('[pest="experience-description-input"]', 'Academic research skills')
            ->click('[pest="add-experience-button"]')
            ->wait(1)
            ->assertPresent('[pest="experience-item"]')
            ->assertSee('Academic research skills')
            // Start editing
            ->click('[pest="edit-experience"][pest-index="0"]')
            ->wait(1)
            ->assertPresent('[pest="edit-experience-description"][pest-index="0"]')
            ->clear('[pest="edit-experience-description"][pest-index="0"]')
            ->type('[pest="edit-experience-description"][pest-index="0"]', 'This should be cancelled')
            // Cancel the edit
            ->click('button:contains("Cancel")')
            ->wait(1)
            ->assertMissing('[pest="edit-experience-description"][pest-index="0"]')
            ->assertSee('Academic research skills')
            ->assertDontSee('This should be cancelled');
    });

    it('shows unsaved changes banner when experiences are modified', function () {
        visit('/character-builder/' . $this->character->storage_key)
            ->click('[pest="sidebar-tab-8"]')
            ->wait(1)
            ->assertPresent('[pest="experience-name-input"]')
            ->type('[pest="experience-name-input"]', 'Test Experience')
            ->click('[pest="add-experience-button"]')
            ->wait(1)
            ->assertPresent('.bg-gradient-to-r.from-amber-500')
            ->assertSee('You have unsaved changes');
    });

    it('limits experiences to 2 maximum', function () {
        visit('/character-builder/' . $this->character->storage_key)
            ->click('[pest="sidebar-tab-8"]')
            ->wait(1)
            ->assertPresent('[pest="experience-name-input"]')
            // Add first experience
            ->type('[pest="experience-name-input"]', 'First Experience')
            ->click('[pest="add-experience-button"]')
            ->wait(1)
            ->assertPresent('[pest="experience-item"]')
            // Add second experience
            ->type('[pest="experience-name-input"]', 'Second Experience')
            ->click('[pest="add-experience-button"]')
            ->wait(1)
            ->assertSee('Second Experience')
            // Try to add third experience - should be disabled
            ->type('[pest="experience-name-input"]', 'Third Experience')
            ->assertAttribute('[pest="add-experience-button"]', 'disabled', 'true');
    });

    it('can assign and remove clank bonus experience', function () {
        // Set character to clank ancestry to enable bonus
        $this->character->update([
            'character_data' => array_merge($this->character->character_data, [
                'selected_ancestry' => 'clank',
                'experiences' => []
            ])
        ]);

        visit('/character-builder/' . $this->character->storage_key)
            ->click('[pest="sidebar-tab-8"]')
            ->wait(1)
            ->assertPresent('[pest="experience-name-input"]')
            // Add an experience
            ->type('[pest="experience-name-input"]', 'Mechanical Expertise')
            ->type('[pest="experience-description-input"]', 'Understanding complex machinery')
            ->click('[pest="add-experience-button"]')
            ->wait(1)
            ->assertPresent('[pest="experience-item"]')
            ->assertSee('Mechanical Expertise')
            // Click the experience card to assign clank bonus
            ->click('[pest="experience-item"][pest-index="0"]')
            ->wait(1)
            ->assertPresent('[pest="remove-clank-bonus"]')
            ->assertSee('Clank Bonus')
            ->assertSee('+3')
            // Remove the clank bonus
            ->click('[pest="remove-clank-bonus"]')
            ->wait(1)
            ->assertMissing('[pest="remove-clank-bonus"]')
            ->assertSee('+2')
            ->assertDontSee('Clank Bonus');
    });

    it('clears clank bonus when experience with bonus is deleted', function () {
        // Set character to clank ancestry and add experience with bonus
        $this->character->update([
            'character_data' => array_merge($this->character->character_data, [
                'selected_ancestry' => 'clank',
                'experiences' => [
                    ['name' => 'Test Experience', 'description' => 'Test description']
                ],
                'clank_bonus_experience' => 'Test Experience'
            ])
        ]);

        visit('/character-builder/' . $this->character->storage_key)
            ->click('[pest="sidebar-tab-8"]')
            ->wait(1)
            ->assertPresent('[pest="experience-item"]')
            ->assertSee('Test Experience')
            ->assertSee('Clank Bonus')
            ->assertSee('+3')
            // Delete the experience
            ->click('[pest="remove-experience"][pest-index="0"]')
            ->wait(1)
            ->assertMissing('[pest="experience-item"]')
            ->assertDontSee('Test Experience')
            // Add a new experience and verify it starts with +2 (bonus cleared)
            ->type('[pest="experience-name-input"]', 'New Experience')
            ->click('[pest="add-experience-button"]')
            ->wait(1)
            ->assertPresent('[pest="experience-item"]')
            ->assertSee('New Experience')
            ->assertSee('+2')
            ->assertDontSee('+3');
    });
});