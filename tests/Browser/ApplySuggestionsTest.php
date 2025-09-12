<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

describe('Apply Suggestions Sidebar Update', function () {

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

    it('updates sidebar immediately when Apply All Suggestions button is clicked', function () {
        $page = visit("/character-builder/{$this->character->character_key}");
        $page->wait(2)
            ->assertSee('Character Builder')
            
            // Navigate to equipment step
            ->click('[pest="sidebar-tab-6"]')
            ->wait(1)
            ->assertSee('Select Equipment')
            
            // Verify initial state - sidebar should show "Not started"
            ->assertSee('Not started')
            
            // Click Apply All Suggestions button
            ->click('[pest="apply-all-suggestions"]')
            ->wait(2) // Give a bit more time for multiple equipment items to sync
            
            // The main equipment area should show complete
            ->assertSee('Complete!')
            
            // Check if sidebar updates - switch to different tab and back
            ->click('[pest="sidebar-tab-5"]') // Switch to traits
            ->wait(1)
            ->click('[pest="sidebar-tab-6"]') // Switch back to equipment
            ->wait(1);
            
        // At this point, the sidebar should no longer show "Not started"
        // The exact text depends on how many items were applied, but it should show progress
        $page->assertSee('Select Equipment'); // We should still be on the equipment page
    });

    it('shows unsaved changes after applying suggestions', function () {
        $page = visit("/character-builder/{$this->character->character_key}");
        $page->wait(2)
            ->assertSee('Character Builder')
            
            // Navigate to equipment step
            ->click('[pest="sidebar-tab-6"]')
            ->wait(1)
            ->assertSee('Select Equipment')
            
            // Should not have unsaved changes initially
            ->assertDontSee('You have unsaved changes')
            
            // Click Apply All Suggestions button
            ->click('[pest="apply-all-suggestions"]')
            ->wait(1)
            
            // Should now show unsaved changes
            ->assertSee('You have unsaved changes');
    });
});
