<?php

declare(strict_types=1);

describe('Trait Assignment', function () {
    beforeEach(function () {
        $this->character = \Domain\Character\Models\Character::factory()->create([
            'character_data' => [
                'selected_class' => 'warrior',
                'selected_subclass' => 'stalwart',
                'assigned_traits' => [],
                'selected_equipment' => [],
            ]
        ]);
    });

    it('can click trait boxes to assign values', function () {
        visit('/character-builder/' . $this->character->storage_key)
            ->click('[pest="sidebar-tab-5"]')
            ->waitFor('[pest="trait-box"][pest-trait="agility"]')
            ->click('[pest="trait-box"][pest-trait="agility"]')
            ->waitUntil('document.querySelector("[pest-trait=agility]").textContent.includes("2")', 3)
            ->assertSee('+2');
    });

    it('can remove trait assignments by clicking again', function () {
        visit('/character-builder/' . $this->character->storage_key)
            ->click('[pest="sidebar-tab-5"]')
            ->waitFor('[pest="trait-box"][pest-trait="agility"]')
            ->click('[pest="trait-box"][pest-trait="agility"]') // Assign first value
            ->waitUntil('document.querySelector("[pest-trait=agility]").textContent.includes("2")', 3)
            ->click('[pest="trait-box"][pest-trait="agility"]') // Remove value
            ->waitUntil('document.querySelector("[pest-trait=agility]").textContent.includes("Tap to assign")', 3)
            ->assertSee('Tap to assign');
    });

    it('can apply suggested traits for class', function () {
        visit('/character-builder/' . $this->character->storage_key)
            ->click('[pest="sidebar-tab-5"]')
            ->waitFor('[pest="apply-suggested-traits"]')
            ->click('[pest="apply-suggested-traits"]')
            ->waitUntil('document.querySelector("[pest-trait=strength]").textContent.includes("2")', 3)
            ->assertSee('+2');
    });

    it('shows unsaved changes banner when traits are modified', function () {
        visit('/character-builder/' . $this->character->storage_key)
            ->click('[pest="sidebar-tab-5"]')
            ->waitFor('[pest="trait-box"][pest-trait="agility"]')
            ->click('[pest="trait-box"][pest-trait="agility"]')
            ->waitFor('.bg-gradient-to-r.from-amber-500')
            ->assertSee('You have unsaved changes');
    });
});
