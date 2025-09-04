<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

describe('Equipment Selection', function () {
    
    beforeEach(function () {
        $this->character = Character::factory()->create([
            'character_key' => 'TEST1234',
            'class' => 'warrior',
            'subclass' => 'stalwart',
            'ancestry' => 'human',
            'community' => 'wildborne',
            'character_data' => [
                'selected_class' => 'warrior',
                'selected_subclass' => 'stalwart',
                'selected_ancestry' => 'human',
                'selected_community' => 'wildborne',
                'assigned_traits' => [
                    'agility' => 0,
                    'strength' => 2,
                    'finesse' => -1,
                    'instinct' => 1,
                    'presence' => 0,
                    'knowledge' => 1,
                ],
                'selected_equipment' => [],
                'background' => ['answers' => []],
                'connections' => [],
                'creation_date' => now()->toISOString(),
                'builder_version' => '1.0',
            ],
        ]);
    });

    it('shows unsaved changes banner when equipment is selected', function () {
        $page = visit("/character-builder/{$this->character->character_key}");
        $page->waitForText('Character Builder', 10)
            ->click('[pest="tab-equipment"]')
            ->waitForText('Select Equipment', 5)
            ->assertDontSee('You have unsaved changes')
            
            // Select a primary weapon
            ->click('[pest="suggested-primary-weapon"]')
            ->waitFor('[x-show="hasUnsavedChanges"]', 3)
            ->assertSee('You have unsaved changes');
    });

    it('allows unselecting equipment by clicking selected items', function () {
        $page = visit("/character-builder/{$this->character->character_key}");
        $page->waitForText('Character Builder', 10)
            ->click('[pest="tab-equipment"]')
            ->waitForText('Select Equipment', 5)
            
            // Select a primary weapon
            ->click('[pest="suggested-primary-weapon"]')
            ->waitFor('.bg-emerald-400\\/20', 2)
            
            // Click the same weapon again to unselect it
            ->click('[pest="suggested-primary-weapon"]')
            ->waitUntilMissing('.bg-emerald-400\\/20', 2);
    });

    it('shows correct progress indicators when equipment is selected', function () {
        $page = visit("/character-builder/{$this->character->character_key}");
        $page->waitForText('Character Builder', 10)
            ->click('[pest="tab-equipment"]')
            ->waitForText('Select Equipment', 5)
            
            // Initially no equipment selected - should show incomplete indicators
            ->assertVisible('[x-show="!selectedPrimary"]')
            ->assertVisible('[x-show="!selectedArmor"]')
            ->assertMissing('[x-show="equipmentComplete"]')
            
            // Select primary weapon
            ->click('[pest="suggested-primary-weapon"]')
            ->waitFor('[x-show="selectedPrimary"]', 3)
            ->assertVisible('[x-show="selectedPrimary"]')
            ->assertMissing('[x-show="!selectedPrimary"]')
            
            // Select armor
            ->click('[pest="suggested-armor"]')
            ->waitFor('[x-show="selectedArmor"]', 3)
            ->assertVisible('[x-show="selectedArmor"]')
            ->assertMissing('[x-show="!selectedArmor"]')
            
            // Should now show complete
            ->waitFor('[x-show="equipmentComplete"]', 3)
            ->assertVisible('[x-show="equipmentComplete"]')
            ->assertSee('Complete!');
    });

    it('saves equipment data when save button is clicked', function () {
        $page = visit("/character-builder/{$this->character->character_key}");
        $page->waitForText('Character Builder', 10)
            ->click('[pest="tab-equipment"]')
            ->waitForText('Select Equipment', 5)
            
            // Select equipment
            ->click('[pest="suggested-primary-weapon"]')
            ->click('[pest="suggested-armor"]')
            ->waitFor('[x-show="hasUnsavedChanges"]', 3)
            
            // Click save
            ->click('[pest="floating-save-button"]')
            ->waitFor('[x-show="isSaving"]', 2)
            ->waitUntilMissing('[x-show="isSaving"]', 10)
            ->waitUntilMissing('[x-show="hasUnsavedChanges"]', 3);
        
        // Verify equipment was saved to database
        $this->character->refresh();
        $characterData = $this->character->character_data;
        expect($characterData['selected_equipment'])->toHaveCount(2);
        expect($characterData['selected_equipment'][0]['type'])->toBe('weapon');
        expect($characterData['selected_equipment'][1]['type'])->toBe('armor');
    });

    it('applies all suggested equipment at once', function () {
        $page = visit("/character-builder/{$this->character->character_key}");
        $page->waitForText('Character Builder', 10)
            ->click('[pest="tab-equipment"]')
            ->waitForText('Select Equipment', 5)
            
            // Click "Apply All Suggestions" button
            ->click('[pest="apply-all-suggestions"]')
            ->waitFor('[x-show="selectedPrimary"]', 3)
            ->waitFor('[x-show="selectedArmor"]', 3)
            ->waitFor('[x-show="equipmentComplete"]', 3)
            
            // All suggested equipment should be selected
            ->assertVisible('[x-show="selectedPrimary"]')
            ->assertVisible('[x-show="selectedArmor"]')
            ->assertVisible('[x-show="equipmentComplete"]')
            ->assertSee('Complete!')
            
            // Should trigger unsaved changes
            ->waitFor('[x-show="hasUnsavedChanges"]', 3)
            ->assertSee('You have unsaved changes');
    });
});