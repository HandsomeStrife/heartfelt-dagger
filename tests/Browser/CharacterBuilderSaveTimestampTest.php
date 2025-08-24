<?php

declare(strict_types=1);

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Test;
use Tests\DuskTestCase;

class CharacterBuilderSaveTimestampTest extends DuskTestCase
{
    #[Test]
    public function save_button_updates_timestamp_display(): void
    {
        // Create a character
        $character = \Domain\Character\Models\Character::factory()->create([
            'name' => 'Test Character',
            'class' => 'bard',
        ]);

        $this->browse(function (Browser $browser) use ($character) {
            $browser->visit("/character-builder/{$character->character_key}")
                // Wait for the initial timestamp to load
                ->waitForText('Saved', 5)
                
                // Click the save button
                ->click('[dusk="save-character-button"]')
                
                // Wait for save success notification
                ->waitForText('Character saved successfully!', 5)
                
                // Check that the timestamp shows "just now"
                ->waitForText('Saved just now', 5);
        });
    }

    #[Test]
    public function timestamp_updates_over_time(): void
    {
        // Create a character saved 2 minutes ago
        $character = \Domain\Character\Models\Character::factory()->create([
            'name' => 'Test Character',
            'class' => 'bard',
            'updated_at' => now()->subMinutes(2),
        ]);

        $this->browse(function (Browser $browser) use ($character) {
            $browser->visit("/character-builder/{$character->character_key}")
                // Wait for the JavaScript to calculate the initial time
                ->waitFor('[x-text*="timeAgoText"]', 5)
                
                // Should show "2 minutes ago" initially
                ->assertSee('2 minutes ago');
        });
    }
}
