<?php

declare(strict_types=1);

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Test;
use Tests\DuskTestCase;

class CharacterBuilderSubHeaderTest extends DuskTestCase
{
    #[Test]
    public function character_builder_shows_black_sub_header_with_title_preview_and_save_buttons(): void
    {
        // Create a character with a class selected
        $character = \Domain\Character\Models\Character::factory()->create([
            'name' => 'Test Character',
            'class' => 'bard',
        ]);

        $this->browse(function (Browser $browser) use ($character) {
            $browser->visit("/character-builder/{$character->character_key}")
                // Check that the sub-navigation component exists
                ->assertPresent('x-sub-navigation')
                
                // Check that the title is in the sub-header
                ->within('x-sub-navigation', function (Browser $browser) {
                    $browser->assertSeeIn('h1', 'Character Builder');
                })
                
                // Check that both Save and Preview buttons are in the sub-header
                ->within('x-sub-navigation', function (Browser $browser) {
                    $browser->assertPresent('[dusk="save-character-button"]')
                        ->assertSee('Save')
                        ->assertPresent('[dusk="preview-character-button"]')
                        ->assertSee('Preview');
                });
        });
    }

    #[Test]
    public function character_builder_shows_last_saved_time_when_available(): void
    {
        // Create a character and update it so it has an updated_at timestamp
        $character = \Domain\Character\Models\Character::factory()->create([
            'name' => 'Test Character',
            'class' => 'bard',
        ]);
        
        // Touch the model to ensure it has an updated_at timestamp
        $character->touch();

        $this->browse(function (Browser $browser) use ($character) {
            $browser->visit("/character-builder/{$character->character_key}")
                // Check that the last saved time is displayed
                ->within('x-sub-navigation', function (Browser $browser) {
                    $browser->assertSee('Saved')
                        ->waitForText('just now', 5); // Wait for JavaScript to calculate time
                });
        });
    }

    #[Test]
    public function character_builder_shows_save_button_but_hides_preview_when_no_class_selected(): void
    {
        // Create a character without a class
        $character = \Domain\Character\Models\Character::factory()->create([
            'name' => 'Test Character',
            'class' => null,
        ]);

        $this->browse(function (Browser $browser) use ($character) {
            $browser->visit("/character-builder/{$character->character_key}")
                // Check that the Save button is present but Preview button is not
                ->within('x-sub-navigation', function (Browser $browser) {
                    $browser->assertPresent('[dusk="save-character-button"]')
                        ->assertSee('Save')
                        ->assertMissing('[dusk="preview-character-button"]');
                });
        });
    }
}
