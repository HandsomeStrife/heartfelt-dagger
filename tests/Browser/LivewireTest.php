<?php

uses(\Tests\DuskTestCase::class);
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Test;

test('livewire basic functionality', function () {
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
});
