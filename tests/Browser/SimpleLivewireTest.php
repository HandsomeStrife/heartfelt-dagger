<?php

test('simple livewire component', function () {
    $page = visit('/simple-test');
    
    $page
        ->wait(2)
        ->assertSee('Simple Livewire Test')
        ->assertSee('Count: 0')
        ->click('button')
        ->wait(2)
        ->assertSee('Count: 1')
        ->click('button')
        ->wait(2)
        ->assertSee('Count: 2');
});
