<?php

declare(strict_types=1);

it('reproduces the specific "Unnamed Experience" issue after refresh', function () {
    // Create a character with specific experience data that might trigger the hydration issue
    $character = \Domain\Character\Models\Character::factory()->create([
        'name' => 'Experience Test Character',
        'class' => 'wizard',
        'subclass' => 'school of knowledge',
        'ancestry' => 'human',
        'community' => 'loreborne',
    ]);

    // Create experiences with different data structures to potentially trigger the issue
    \Domain\Character\Models\CharacterExperience::factory()->create([
        'character_id' => $character->id,
        'experience_name' => 'Academic Research',
        'experience_description' => 'Years of scholarly study',
        'modifier' => 2,
    ]);

    \Domain\Character\Models\CharacterExperience::factory()->create([
        'character_id' => $character->id,
        'experience_name' => 'Noble Etiquette',
        'experience_description' => 'Court training',
        'modifier' => 2,
    ]);

    $page = visit("/character/{$character->public_key}");

    // Verify initial state - should show proper experience names
    $page->assertSee('Academic Research', 'Initial load: Academic Research not found')
        ->assertSee('Noble Etiquette', 'Initial load: Noble Etiquette not found')
        ->assertDontSee('Unnamed Experience', 'Initial load: Found "Unnamed Experience" when it should show proper names');

    // Click refresh button
    $page->click('[pest="refresh-button"]');

    // Wait longer to ensure hydration completes
    $page->wait(5);

    // This is the critical test - after refresh, we should NOT see "Unnamed Experience"
    // If we do see it, it means the hydration is breaking the experience data structure
    $page->assertDontSee('Unnamed Experience', 'CRITICAL: Found "Unnamed Experience" after refresh - hydration is breaking experience data!')
        ->assertSee('Academic Research', 'After refresh: Academic Research disappeared - hydration issue!')
        ->assertSee('Noble Etiquette', 'After refresh: Noble Etiquette disappeared - hydration issue!');

    // Also verify that experience modifiers are still showing
    $page->assertSee('+2', 'Experience modifiers disappeared after refresh');
});

it('tests different experience data scenarios that might trigger hydration issues', function () {
    // Test with minimal data to see if missing fields cause hydration problems
    $character = \Domain\Character\Models\Character::factory()->create([
        'name' => 'Minimal Experience Test',
        'class' => 'rogue',
        'ancestry' => 'elf',
        'community' => 'wildborne',
    ]);

    // Create an experience with minimal data
    \Domain\Character\Models\CharacterExperience::factory()->create([
        'character_id' => $character->id,
        'experience_name' => 'Stealth Training',
        'experience_description' => null, // Null description to test edge case
        'modifier' => 2,
    ]);

    $page = visit("/character/{$character->public_key}");

    $page->assertSee('Stealth Training', 'Initial: Stealth Training not found')
        ->assertDontSee('Unnamed Experience', 'Initial: Found "Unnamed Experience" with minimal data');

    $page->click('[pest="refresh-button"]')->wait(3);

    $page->assertSee('Stealth Training', 'After refresh: Stealth Training disappeared with minimal data')
        ->assertDontSee('Unnamed Experience', 'CRITICAL: Minimal data triggered "Unnamed Experience" after refresh');
});

it('debugs the exact data structure being passed to experience component', function () {
    $character = \Domain\Character\Models\Character::factory()->create([
        'name' => 'Debug Character',
        'class' => 'bard',
        'ancestry' => 'human',
        'community' => 'loreborne',
    ]);

    \Domain\Character\Models\CharacterExperience::factory()->create([
        'character_id' => $character->id,
        'experience_name' => 'Performance Skills',
        'experience_description' => 'Musical training',
        'modifier' => 2,
    ]);

    $page = visit("/character/{$character->public_key}");

    // Before refresh, log the experience data structure
    $page->script('
        console.log("=== BEFORE REFRESH ===");
        const experienceSection = document.querySelector("[pest=\"experience-section\"]");
        if (experienceSection) {
            console.log("Experience section found:", experienceSection.innerHTML);
        }
        const experienceItems = document.querySelectorAll("[pest*=\"experience-item\"]");
        console.log("Experience items count:", experienceItems.length);
        experienceItems.forEach((item, index) => {
            console.log(`Experience ${index}:`, item.innerHTML);
        });
    ');

    $page->assertSee('Performance Skills');

    $page->click('[pest="refresh-button"]')->wait(3);

    // After refresh, log the experience data structure again
    $page->script('
        console.log("=== AFTER REFRESH ===");
        const experienceSection = document.querySelector("[pest=\"experience-section\"]");
        if (experienceSection) {
            console.log("Experience section found:", experienceSection.innerHTML);
        }
        const experienceItems = document.querySelectorAll("[pest*=\"experience-item\"]");
        console.log("Experience items count:", experienceItems.length);
        experienceItems.forEach((item, index) => {
            console.log(`Experience ${index}:`, item.innerHTML);
        });
    ');

    $page->assertSee('Performance Skills', 'Debug test: Performance Skills disappeared after refresh')
        ->assertDontSee('Unnamed Experience', 'Debug test: Found "Unnamed Experience" - check console logs for data structure differences');
});
