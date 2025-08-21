<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Test;
use Tests\DuskTestCase;

class LivewireTest extends DuskTestCase
{
    #[Test]
    public function test_livewire_basic_functionality(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/character-builder')
                ->pause(2000)
                ->assertSee('Character Builder')
                ->assertSee('Test Livewire (FALSE)')  // Initial state
                ->screenshot('before-click')
                ->click('button[wire\\:click="test"]')
                ->pause(2000)  // Wait for Livewire to update
                ->screenshot('after-click')
                ->assertSee('Test Livewire (TRUE)');  // After clicking
        });
    }
}
