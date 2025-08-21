<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Test;
use Tests\DuskTestCase;

class ExampleBrowserTest extends DuskTestCase
{
    use DatabaseMigrations;

    #[Test]
    public function can_visit_homepage(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertSee('Daggerheart');
        });
    }

    #[Test]
    public function can_visit_character_builder(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/character-builder')
                ->assertSee('Character Builder')
                ->assertPresent('[dusk="progress-bar"]');
        });
    }
}
