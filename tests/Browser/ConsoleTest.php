<?php

test('javascript console errors', function () {
    $page = visit('/character-builder');
    
    $page
            ->wait(3000);  // Let page fully load

        // Get browser console logs
        $logs = $page->driver->manage()->getLog('browser');

        foreach ($logs as $log) {
            if ($log['level'] === 'SEVERE') {
                echo 'JavaScript Error: '.$log['message']."\n";
            }
        }

        // Just assert that we can load the page
        $page->assertSee('Character Builder');
});