<?php

uses(\Tests\DuskTestCase::class);
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Test;

test('javascript console errors', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/character-builder')
            ->pause(3000);  // Let page fully load

        // Get browser console logs
        $logs = $browser->driver->manage()->getLog('browser');

        foreach ($logs as $log) {
            if ($log['level'] === 'SEVERE') {
                echo 'JavaScript Error: '.$log['message']."\n";
            }
        }

        // Just assert that we can load the page
        $browser->assertSee('Character Builder');
    });
});
