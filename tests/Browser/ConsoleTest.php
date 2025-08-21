<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Test;
use Tests\DuskTestCase;

class ConsoleTest extends DuskTestCase
{
    #[Test]
    public function test_javascript_console_errors(): void
    {
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
    }
}
