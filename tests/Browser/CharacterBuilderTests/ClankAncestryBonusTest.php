<?php

declare(strict_types=1);
// We avoid direct factories to reduce DB coupling in browser tests


test('clank ancestry shows bonus selection ui', function () {
    $page = visit('/character-builder');
    $page->wait(2)->assertSee('Choose a Class');
    
    // Reset builder step to 1 to ensure selectors are visible
    $page->script('sessionStorage.removeItem("characterBuilderCurrentStep")');

    $page
        ->assertSee('Character Builder')
        ->click('[dusk="sidebar-tab-1"]')
        ->wait(0.5)
        ->click('[dusk="class-card-warrior"]')
        ->wait(0.8)
        ->click('[dusk="sidebar-tab-3"]')
        ->assertSee('Choose Your Ancestry')
        ->click('[dusk="ancestry-card-clank"]')
        ->wait(0.8)
        ->click('[dusk="sidebar-tab-4"]')
        ->assertSee('Choose Your Community')
        ->click('[dusk="community-card-wildborne"]')
        ->wait(0.8)
        ->click('[dusk="sidebar-tab-8"]')
        ->assertSee('Add Experiences')
        // Add one experience so the selection UI is rendered in the list
        ->type('[dusk="new-experience-name"]', 'Blacksmith')
        ->type('[dusk="new-experience-description"]', 'Working with metal and tools')
        ->click('[dusk="add-experience-button"]')
        ->wait(1)
        ->assertSee('Your Experiences')
        ->assertSee('Click to select for your Clank heritage bonus (+3)');
});

test('clank bonus appears after adding experiences', function () {
    $page = visit('/character-builder');
    $page->wait(2)->assertSee('Choose a Class');
    
    $page
        ->assertSee('Character Builder')
        ->click('[dusk="sidebar-tab-1"]')
        ->click('[dusk="class-card-warrior"]')
        ->click('[dusk="sidebar-tab-3"]')
        ->click('[dusk="ancestry-card-clank"]')
        ->click('[dusk="sidebar-tab-4"]')
        ->click('[dusk="community-card-wildborne"]')
        ->click('[dusk="sidebar-tab-8"]')
        ->type('[dusk="new-experience-name"]', 'Blacksmith')
        ->type('[dusk="new-experience-description"]', 'Working with metal and tools')
        ->click('[dusk="add-experience-button"]')
        ->wait(1)
        ->assertSee('Your Experiences')
        ->assertSee('Blacksmith')
        ->assertSee('+2'); // Should show base modifier initially
});

test('selecting clank bonus updates modifier display', function () {
    $page = visit('/character-builder');
    $page->wait(2)->assertSee('Choose a Class');
    
    $page
        ->assertSee('Character Builder')
        ->click('[dusk="sidebar-tab-1"]')
        ->click('[dusk="class-card-warrior"]')
        ->click('[dusk="sidebar-tab-3"]')
        ->click('[dusk="ancestry-card-clank"]') 
        ->click('[dusk="sidebar-tab-4"]')
        ->click('[dusk="community-card-wildborne"]')
        ->click('[dusk="sidebar-tab-8"]')
        ->type('[dusk="new-experience-name"]', 'Blacksmith')
        ->click('[dusk="add-experience-button"]')
        ->wait(1)
        ->click('[dusk="experience-card-0"]')
        ->assertSee('Clank Bonus')
        ->assertSee('+3'); // Should now show enhanced modifier
});

test('clank bonus appears in viewer experience list', function () {
    // Go through builder to set up Clank and select bonus experience
    $page = visit('/character-builder');
    $page->wait(2)->assertSee('Choose a Class');
    $page
        ->assertSee('Character Builder')
        ->click('[dusk="sidebar-tab-1"]')
        ->click('[dusk="class-card-warrior"]')
        ->click('[dusk="sidebar-tab-3"]')
        ->click('[dusk="ancestry-card-clank"]')
        ->click('[dusk="sidebar-tab-4"]')
        ->click('[dusk="community-card-wildborne"]')
        ->click('[dusk="sidebar-tab-8"]')
        ->type('[dusk="new-experience-name"]', 'Blacksmith')
        ->type('[dusk="new-experience-description"]', 'Working with metal and tools')
        ->click('[dusk="add-experience-button"]')
        ->wait(1)
        ->click('[dusk="experience-card-0"]');

    // Extract character key from URL and fetch public key synchronously
    $path = $page->script('(function(){ return window.location.pathname; })()');
    $character_key = is_array($path) ? ($path[0] ?? '') : $path;
    $character_key = is_string($character_key) ? trim($character_key) : '';
    $character_key = preg_replace('#^/character-builder/#', '', $character_key);
    $json = $page->script('(function(){ var xhr = new XMLHttpRequest(); xhr.open("GET", "/api/character/' . "' + '" . '" . ' . '",' . 'false); })()');
    // Fallback: simple fetch via synchronous XHR
    $responseText = $page->script('(function(){ try { var xhr = new XMLHttpRequest(); xhr.open("GET", "/api/character/' . "' + '" . '" . ' . '",' . 'false); } catch(e) { return null; } })()');
    // If above failed, do one more attempt with concatenation inside browser
    if (!$responseText) {
        $responseText = $page->script('(function(){ try { var key = window.location.pathname.split("/").pop(); var xhr = new XMLHttpRequest(); xhr.open("GET", "/api/character/"+key, false); xhr.send(null); return xhr.responseText; } catch(e) { return null; } })()');
    }
    $public_key = null;
    if ($responseText) {
        $payload = is_array($responseText) ? ($responseText[0] ?? null) : $responseText;
        $data = @json_decode((string)$payload, true);
        if (is_array($data)) {
            $public_key = $data['public_key'] ?? null;
        }
    }
    // Open the viewer page for the same character
    $viewer = visit('/character/' . $public_key);
    waitForHydration($viewer);

    // Assert the viewer reflects the Clank bonus (+3) and shows the bonus label
    $viewer
        ->assertSee('Experience')
        ->assertSee('Blacksmith')
        ->assertSee('Clank Bonus')
        ->assertSee('+3');
});

test('non clank ancestry does not show bonus ui', function () {
    $page = visit('/character-builder');
    $page->wait(2)->assertSee('Choose a Class');
    
    $page
        ->assertSee('Character Builder')
        ->click('[dusk="sidebar-tab-1"]')
        ->click('[dusk="class-card-warrior"]')
        ->click('[dusk="sidebar-tab-3"]')
        ->click('[dusk="ancestry-card-human"]')
        ->click('[dusk="sidebar-tab-4"]')
        ->click('[dusk="community-card-highborne"]')
        ->click('[dusk="sidebar-tab-8"]')
        ->type('[dusk="new-experience-name"]', 'Blacksmith')
        ->click('[dusk="add-experience-button"]')
        ->wait(1)
        ->assertSee('Your Experiences')
        ->assertDontSee('Click to select for your Clank heritage bonus (+3)');
});