<?php

uses(\Tests\DuskTestCase::class);
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Test;

test('simple livewire component', function () {
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
});
