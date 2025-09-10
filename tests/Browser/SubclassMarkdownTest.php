<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('converts markdown formatting in subclass descriptions correctly', function () {
    $page = visit('/character-builder');

    // Navigate to subclass selection
    $page->click('[pest="class-card-druid"]');
    $page->wait(1);
    $page->click('[pest="next-step-button"]');
    $page->wait(1);

    // Should see subclass selection
    $page->assertSee('Choose Your Subclass');

    // Select the "Warden of the Elements" subclass which has markdown formatting like **Fire:** **Earth:** etc.
    $page->assertVisible('[pest="subclass-card-warden of the elements"]');
    $page->click('[pest="subclass-card-warden of the elements"]');
    $page->wait(1);

    // Check that bold text is converted to HTML (no raw markdown)
    $page->assertDontSee('**Fire:**');
    $page->assertDontSee('**Earth:**');
    $page->assertDontSee('**Water:**');
    $page->assertDontSee('**Air:**');

    // Should contain actual readable text about the elements
    $page->assertSee('Fire:');
    $page->assertSee('Earth:');
    $page->assertSee('Water:');
    $page->assertSee('Air:');
});

it('handles bullet points in subclass descriptions correctly', function () {
    $page = visit('/character-builder');

    // Navigate to subclass selection
    $page->click('[pest="class-card-bard"]');
    $page->wait(1);
    $page->click('[pest="next-step-button"]');
    $page->wait(1);

    // Select troubadour which has bullet points in descriptions
    $page->assertVisible('[pest="subclass-card-troubadour"]');
    $page->click('[pest="subclass-card-troubadour"]');
    $page->wait(1);

    // Should not contain raw markdown bullet syntax
    $page->assertDontSee('- **Relaxing Song:**');
    $page->assertDontSee('- **Epic Song:**');
    $page->assertDontSee('- **Heartbreaking Song:**');

    // Should contain the song names
    $page->assertSee('Relaxing Song:');
    $page->assertSee('Epic Song:');
    $page->assertSee('Heartbreaking Song:');
});

it('processes line breaks correctly in subclass descriptions', function () {
    $page = visit('/character-builder');

    // Navigate to subclass selection
    $page->click('[pest="class-card-ranger"]');
    $page->wait(1);
    $page->click('[pest="next-step-button"]');
    $page->wait(1);

    // Select beastbound which has line breaks in companion description
    $page->assertVisible('[pest="subclass-card-beastbound"]');
    $page->click('[pest="subclass-card-beastbound"]');
    $page->wait(1);

    // Should not contain literal \n\n
    $page->assertDontSee('\n\n');

    // Should contain companion text
    $page->assertSee('Companion');
    $page->assertSee('animal companion');
});
