<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Test;
use Tests\DuskTestCase;

class SimpleLivewireTest extends DuskTestCase
{
    #[Test]
    public function test_simple_livewire_component(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/simple-test')
                ->pause(2000)
                ->assertSee('Simple Livewire Test')
                ->assertSee('Count: 0')
                ->click('button')
                ->pause(2000)
                ->assertSee('Count: 1')
                ->click('button')
                ->pause(2000)
                ->assertSee('Count: 2');
        });
    }
}
