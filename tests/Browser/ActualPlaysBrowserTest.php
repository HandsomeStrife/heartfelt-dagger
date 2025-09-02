<?php

declare(strict_types=1);

test('actual plays page is accessible from navigation', function () {
    $page = visit('/');
    
    // Click on the Tools dropdown for guests
    $page->click('[data-testid="nav-tools"]')
        ->assertSee('Actual Plays')
        ->click('Actual Plays')
        ->assertPathIs('/actual-plays')
        ->assertSee('Actual Plays')
        ->assertSee('Discover Amazing Daggerheart Adventures');
});

test('actual plays page displays content correctly', function () {
    $page = visit('/actual-plays');
    
    $page->assertSee('Video Actual Plays')
        ->assertSee('Audio Actual Plays')
        ->assertSee('Explorers of Elsewhere')
        ->assertSee('DodoBorne')
        ->assertSee('DMDanT (u/ShiaLovekraft)');
});
