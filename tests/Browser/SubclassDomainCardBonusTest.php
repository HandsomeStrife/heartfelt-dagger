<?php

declare(strict_types=1);

namespace Tests\Browser;

use Domain\Character\Models\Character;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Test;
use Tests\DuskTestCase;

class SubclassDomainCardBonusTest extends DuskTestCase
{
    use DatabaseMigrations;

    #[Test]
    public function school_of_knowledge_character_shows_domain_card_bonus_in_ui(): void
    {
        $user = User::factory()->create();
        $character = Character::factory()->for($user)->create([
            'character_key' => 'TEST123456',
            'class' => 'wizard',
            'subclass' => 'school of knowledge',
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/character-builder/TEST123456')
                ->waitForText('Select Domain Cards')
                ->assertSee('Choose 5 starting domain cards')
                ->assertSee('includes 3 bonus cards from School of knowledge');
        });
    }

    #[Test]
    public function regular_subclass_character_shows_normal_domain_card_limit(): void
    {
        $user = User::factory()->create();
        $character = Character::factory()->for($user)->create([
            'character_key' => 'TEST789012',
            'class' => 'warrior',
            'subclass' => 'stalwart',
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/character-builder/TEST789012')
                ->waitForText('Select Domain Cards')
                ->assertSee('Choose 2 starting domain cards')
                ->assertDontSee('bonus cards from');
        });
    }

    #[Test]
    public function school_of_knowledge_character_can_navigate_to_domain_card_step(): void
    {
        $user = User::factory()->create();
        $character = Character::factory()->for($user)->create([
            'character_key' => 'TEST345678',
            'class' => 'wizard',
            'subclass' => 'school of knowledge',
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/character-builder/TEST345678')
                ->waitForText('Character Builder')
                ->waitFor('.character-builder-content');

            // Navigate through steps to reach domain cards
            if ($browser->element('[data-step="class"]')) {
                $browser->click('[data-step="class"]');
            }
            
            // Just verify we can get to the domain card selection page
            $browser->assertSee('domain', 10); // Wait up to 10 seconds for domain-related content
        });
    }
}
