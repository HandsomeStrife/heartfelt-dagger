<?php

declare(strict_types=1);

namespace Tests\Browser;

use Domain\Character\Models\Character;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Test;
use Tests\DuskTestCase;

class ClankAncestryBonusTest extends DuskTestCase
{
    use DatabaseMigrations;

    #[Test]
    public function clank_ancestry_shows_bonus_selection_ui(): void
    {
        $character = Character::factory()->create();

        $this->browse(function (Browser $browser) use ($character) {
            $browser->visit("/characters/{$character->character_key}/edit")
                    ->waitFor('[dusk="character-builder"]')
                    ->click('[dusk="select-class-warrior"]')
                    ->click('[dusk="select-ancestry-clank"]')
                    ->click('[dusk="select-community-wildborne"]')
                    ->click('[dusk="step-experience-creation"]')
                    ->waitForText('Clank Ancestry: Purposeful Design')
                    ->assertSee('Choose one of your experiences that best aligns with your purpose')
                    ->assertSee('Add experiences first, then you can select which one receives the Clank bonus');
        });
    }

    #[Test]
    public function clank_bonus_appears_after_adding_experiences(): void
    {
        $character = Character::factory()->create();

        $this->browse(function (Browser $browser) use ($character) {
            $browser->visit("/characters/{$character->character_key}/edit")
                    ->waitFor('[dusk="character-builder"]')
                    ->click('[dusk="select-class-warrior"]')
                    ->click('[dusk="select-ancestry-clank"]')
                    ->click('[dusk="select-community-wildborne"]')
                    ->click('[dusk="step-experience-creation"]')
                    ->waitForText('Clank Ancestry: Purposeful Design')
                    ->type('[dusk="new-experience-name"]', 'Blacksmith')
                    ->type('[dusk="new-experience-description"]', 'Working with metal and tools')
                    ->click('[dusk="add-experience-button"]')
                    ->waitForText('Your Experiences')
                    ->waitForText('Select experience for +1 bonus:')
                    ->assertSee('Blacksmith')
                    ->assertSee('+2'); // Should show base modifier initially
        });
    }

    #[Test]
    public function selecting_clank_bonus_updates_modifier_display(): void
    {
        $character = Character::factory()->create();

        $this->browse(function (Browser $browser) use ($character) {
            $browser->visit("/characters/{$character->character_key}/edit")
                    ->waitFor('[dusk="character-builder"]')
                    ->click('[dusk="select-class-warrior"]')
                    ->click('[dusk="select-ancestry-clank"]') 
                    ->click('[dusk="select-community-wildborne"]')
                    ->click('[dusk="step-experience-creation"]')
                    ->waitForText('Clank Ancestry: Purposeful Design')
                    ->type('[dusk="new-experience-name"]', 'Blacksmith')
                    ->click('[dusk="add-experience-button"]')
                    ->waitForText('Select experience for +1 bonus:')
                    // Click on the Blacksmith experience to select it for bonus
                    ->click('button:contains("Blacksmith")')
                    ->waitForText('"Blacksmith" selected for Clank bonus')
                    ->assertSee('Clank Bonus')
                    ->assertSee('+3'); // Should now show enhanced modifier
        });
    }

    #[Test]
    public function non_clank_ancestry_does_not_show_bonus_ui(): void
    {
        $character = Character::factory()->create();

        $this->browse(function (Browser $browser) use ($character) {
            $browser->visit("/characters/{$character->character_key}/edit")
                    ->waitFor('[dusk="character-builder"]')
                    ->click('[dusk="select-class-warrior"]')
                    ->click('[dusk="select-ancestry-human"]')
                    ->click('[dusk="select-community-highborne"]')
                    ->click('[dusk="step-experience-creation"]')
                    ->assertDontSee('Clank Ancestry: Purposeful Design')
                    ->assertDontSee('Select experience for +1 bonus');
        });
    }
}
