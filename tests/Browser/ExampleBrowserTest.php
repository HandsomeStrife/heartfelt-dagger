<?php

uses(\Tests\DuskTestCase::class);
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Test;

uses(\Illuminate\Foundation\Testing\DatabaseMigrations::class);

test('can visit homepage', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/')
            ->assertSee('Daggerheart');
    });
});

test('can visit character builder', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/character-builder')
            ->assertSee('Character Builder')
            ->assertPresent('[dusk="progress-bar"]');
    });
});