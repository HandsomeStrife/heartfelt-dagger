<?php

test('livewire basic functionality', function () {
    $page = visit('/character-builder');
    
    $page
            ->wait(2000)
            ->assertSee('Character Builder')
            ->assertSee('Test Livewire (FALSE)')  // Initial state
            ->screenshot('before-click')
            ->click('button[wire\\:click="test"]')
            ->wait(2000)  // Wait for Livewire to update
            ->screenshot('after-click')
            ->assertSee('Test Livewire (TRUE)');  // After clicking
});