<?php

declare(strict_types=1);

describe('Domain Card Save State', function () {
    beforeEach(function () {
        $this->character = \Domain\Character\Models\Character::factory()->create([
            'character_data' => [
                'selected_class' => 'wizard',
                'selected_subclass' => 'mystic',
                'selected_ancestry' => 'human',
                'selected_community' => 'highborne',
                'assigned_traits' => ['agility' => 2, 'strength' => 1, 'finesse' => 1, 'instinct' => 0, 'presence' => 0, 'knowledge' => -1],
                'selected_equipment' => [],
                'selected_domain_cards' => [],
            ]
        ]);
    });

    it('properly resets save state after domain card changes', function () {
        visit('/character-builder/' . $this->character->storage_key)
            ->click('[pest="sidebar-tab-9"]') // Navigate to domain cards step
            ->wait(1)
            ->assertSee('Select Domain Cards')
            // Select a domain card
            ->click('[x-data*="domain_card"][data-domain="codex"]')
            ->wait(1)
            // Should show unsaved changes banner
            ->assertPresent('.bg-gradient-to-r.from-amber-500')
            ->assertSee('You have unsaved changes')
            // Save the character
            ->click('[pest="save-character-button"]')
            ->wait(2) // Wait for save to complete
            // Save button and banner should disappear
            ->assertMissing('.bg-gradient-to-r.from-amber-500')
            ->assertMissing('[pest="save-character-button"]')
            // Make another domain card change
            ->click('[x-data*="domain_card"][data-domain="midnight"]')
            ->wait(1)
            // Should show unsaved changes banner again
            ->assertPresent('.bg-gradient-to-r.from-amber-500')
            ->assertSee('You have unsaved changes')
            // Save again
            ->click('[pest="save-character-button"]')
            ->wait(2) // Wait for save to complete
            // Should properly reset - no second save needed
            ->assertMissing('.bg-gradient-to-r.from-amber-500')
            ->assertMissing('[pest="save-character-button"]');
    });

    it('handles multiple rapid domain card changes before save', function () {
        visit('/character-builder/' . $this->character->storage_key)
            ->click('[pest="sidebar-tab-9"]') // Navigate to domain cards step
            ->wait(1)
            ->assertSee('Select Domain Cards')
            // Select first card
            ->click('[x-data*="domain_card"][data-domain="codex"]')
            ->wait(0.5)
            // Select second card quickly
            ->click('[x-data*="domain_card"][data-domain="midnight"]')
            ->wait(0.5)
            // Deselect first card
            ->click('[x-data*="domain_card"][data-domain="codex"]')
            ->wait(0.5)
            // Select third card
            ->click('[x-data*="domain_card"][data-domain="midnight"]')
            ->wait(1)
            // Should show unsaved changes banner
            ->assertPresent('.bg-gradient-to-r.from-amber-500')
            ->assertSee('You have unsaved changes')
            // Save the character
            ->click('[pest="save-character-button"]')
            ->wait(2) // Wait for save to complete
            // Should properly reset after one save
            ->assertMissing('.bg-gradient-to-r.from-amber-500')
            ->assertMissing('[pest="save-character-button"]');
    });
});
