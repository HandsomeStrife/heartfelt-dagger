<?php

declare(strict_types=1);
use Domain\Character\Models\Character;


test('clank ancestry shows bonus selection ui', function () {
    $character = Character::factory()->create();

    $page = visit("/characters/{$character->character_key}/edit");
    
    $page
                ->assertPresent('[dusk="character-builder"]')
                ->click('[dusk="select-class-warrior"]')
                ->click('[dusk="select-ancestry-clank"]')
                ->click('[dusk="select-community-wildborne"]')
                ->click('[dusk="step-experience-creation"]')
                ->assertSee('Clank Ancestry: Purposeful Design')
                ->assertSee('Choose one of your experiences that best aligns with your purpose')
                ->assertSee('Add experiences first, then you can select which one receives the Clank bonus');
});

test('clank bonus appears after adding experiences', function () {
    $character = Character::factory()->create();

    $page = visit("/characters/{$character->character_key}/edit");
    
    $page
                ->assertPresent('[dusk="character-builder"]')
                ->click('[dusk="select-class-warrior"]')
                ->click('[dusk="select-ancestry-clank"]')
                ->click('[dusk="select-community-wildborne"]')
                ->click('[dusk="step-experience-creation"]')
                ->assertSee('Clank Ancestry: Purposeful Design')
                ->type('[dusk="new-experience-name"]', 'Blacksmith')
                ->type('[dusk="new-experience-description"]', 'Working with metal and tools')
                ->click('[dusk="add-experience-button"]')
                ->assertSee('Your Experiences')
                ->assertSee('Select experience for +1 bonus:')
                ->assertSee('Blacksmith')
                ->assertSee('+2'); // Should show base modifier initially
});

test('selecting clank bonus updates modifier display', function () {
    $character = Character::factory()->create();

    $page = visit("/characters/{$character->character_key}/edit");
    
    $page
                ->assertPresent('[dusk="character-builder"]')
                ->click('[dusk="select-class-warrior"]')
                ->click('[dusk="select-ancestry-clank"]') 
                ->click('[dusk="select-community-wildborne"]')
                ->click('[dusk="step-experience-creation"]')
                ->assertSee('Clank Ancestry: Purposeful Design')
                ->type('[dusk="new-experience-name"]', 'Blacksmith')
                ->click('[dusk="add-experience-button"]')
                ->assertSee('Select experience for +1 bonus:')
                // Click on the Blacksmith experience to select it for bonus
                ->click('button:contains("Blacksmith")')
                ->assertSee('"Blacksmith" selected for Clank bonus')
                ->assertSee('Clank Bonus')
                ->assertSee('+3'); // Should now show enhanced modifier
});

test('non clank ancestry does not show bonus ui', function () {
    $character = Character::factory()->create();

    $page = visit("/characters/{$character->character_key}/edit");
    
    $page
                ->assertPresent('[dusk="character-builder"]')
                ->click('[dusk="select-class-warrior"]')
                ->click('[dusk="select-ancestry-human"]')
                ->click('[dusk="select-community-highborne"]')
                ->click('[dusk="step-experience-creation"]')
                ->assertDontSee('Clank Ancestry: Purposeful Design')
                ->assertDontSee('Select experience for +1 bonus');
});