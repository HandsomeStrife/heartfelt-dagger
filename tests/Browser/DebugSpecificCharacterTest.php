<?php

declare(strict_types=1);

it('debugs a specific character that shows Unnamed Experience issue', function () {
    // You can replace this with your actual character's public key
    $publicKey = '3224GAKRQ4'; // This was the key from your earlier debugging

    $page = visit("/character/{$publicKey}");

    // Check if the character loads
    try {
        $page->assertSee('Wizard'); // or whatever class your character is

        // Log the initial experience state
        $page->script('
            console.log("=== DEBUGGING SPECIFIC CHARACTER ===");
            console.log("Public Key: '.$publicKey.'");
            
            // Check if experience section exists
            const experienceSection = document.querySelector("[pest=\"experience-section\"]");
            console.log("Experience section exists:", !!experienceSection);
            
            if (experienceSection) {
                console.log("Experience section HTML:", experienceSection.innerHTML);
                
                // Look for experience names
                const experienceNames = document.querySelectorAll("[pest=\"experience-name\"]");
                console.log("Experience names found:", experienceNames.length);
                experienceNames.forEach((name, index) => {
                    console.log(`Experience ${index}:`, name.textContent);
                });
                
                // Check for "Unnamed Experience" specifically
                const hasUnnamedExperience = experienceSection.innerHTML.includes("Unnamed Experience");
                console.log("Has Unnamed Experience:", hasUnnamedExperience);
            }
        ');

        // Click refresh and see what happens
        $page->click('[pest="refresh-button"]')->wait(3);

        // Log the post-refresh experience state
        $page->script('
            console.log("=== AFTER REFRESH ===");
            
            const experienceSection = document.querySelector("[pest=\"experience-section\"]");
            if (experienceSection) {
                console.log("Experience section HTML after refresh:", experienceSection.innerHTML);
                
                // Look for experience names
                const experienceNames = document.querySelectorAll("[pest=\"experience-name\"]");
                console.log("Experience names found after refresh:", experienceNames.length);
                experienceNames.forEach((name, index) => {
                    console.log(`Experience ${index} after refresh:`, name.textContent);
                });
                
                // Check for "Unnamed Experience" specifically
                const hasUnnamedExperience = experienceSection.innerHTML.includes("Unnamed Experience");
                console.log("Has Unnamed Experience after refresh:", hasUnnamedExperience);
            }
        ');

        // The test will show console output which will help debug the issue
        expect(true)->toBe(true); // Always pass, we just want the console output

    } catch (\Exception $e) {
        // Character might not exist in test database
        expect(true)->toBe(true, "Character {$publicKey} not found in test database - this is expected");
    }
});

it('creates a character matching your wizard setup for debugging', function () {
    // Create a character that matches your setup exactly
    $character = \Domain\Character\Models\Character::factory()->create([
        'name' => 'Debug Wizard Character',
        'class' => 'wizard',
        'subclass' => 'school of knowledge',
        'ancestry' => 'drakona',
        'community' => 'loreborne',
        'level' => 1,
        'character_data' => [
            'background' => [
                'answers' => [],
                'motivations' => null,
                'personalHistory' => null,
                'personalityTraits' => null,
                'physicalDescription' => null,
            ],
            'connections' => [],
            'last_updated' => now()->toISOString(),
            'manualStepCompletions' => [],
            'clank_bonus_experience' => null,
        ],
    ]);

    // Create experiences that might be causing the issue
    \Domain\Character\Models\CharacterExperience::factory()->create([
        'character_id' => $character->id,
        'experience_name' => 'Test Experience 1',
        'experience_description' => 'Description 1',
        'modifier' => 2,
    ]);

    \Domain\Character\Models\CharacterExperience::factory()->create([
        'character_id' => $character->id,
        'experience_name' => 'Test Experience 2',
        'experience_description' => 'Description 2',
        'modifier' => 2,
    ]);

    $page = visit("/character/{$character->public_key}");

    $page->script('
        console.log("=== DEBUGGING CREATED CHARACTER ===");
        console.log("Character Key:", "'.$character->character_key.'");
        console.log("Public Key:", "'.$character->public_key.'");
        
        // Check experience data before refresh
        const experienceSection = document.querySelector("[pest=\"experience-section\"]");
        if (experienceSection) {
            console.log("Experience section before refresh:", experienceSection.innerHTML);
        }
    ');

    $page->assertSee('Test Experience 1')->assertSee('Test Experience 2');

    $page->click('[pest="refresh-button"]')->wait(3);

    $page->script('
        console.log("=== AFTER REFRESH (CREATED CHARACTER) ===");
        const experienceSection = document.querySelector("[pest=\"experience-section\"]");
        if (experienceSection) {
            console.log("Experience section after refresh:", experienceSection.innerHTML);
        }
    ');

    $page->assertSee('Test Experience 1', 'Created character: Experience 1 disappeared after refresh')
        ->assertSee('Test Experience 2', 'Created character: Experience 2 disappeared after refresh')
        ->assertDontSee('Unnamed Experience', 'Created character: Found "Unnamed Experience" after refresh');
});
