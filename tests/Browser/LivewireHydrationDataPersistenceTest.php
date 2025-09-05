<?php

declare(strict_types=1);

use function Pest\Laravel\{actingAs};

it('validates character data persists through Livewire hydration cycles', function () {
    // Create a character using existing working test data patterns
    $character = \Domain\Character\Models\Character::factory()->create([
        'name' => 'Test Hydration Character',
        'class' => 'wizard',
        'subclass' => 'school of knowledge',
        'ancestry' => 'human',
        'community' => 'loreborne',
    ]);

    // Create experiences (this we know works from the screenshot)
    \Domain\Character\Models\CharacterExperience::factory()->create([
        'character_id' => $character->id,
        'experience_name' => 'Academic Research',
        'experience_description' => 'Years of scholarly study',
        'modifier' => 2
    ]);
    \Domain\Character\Models\CharacterExperience::factory()->create([
        'character_id' => $character->id,
        'experience_name' => 'Noble Etiquette',
        'experience_description' => 'Court training',
        'modifier' => 2
    ]);

    $page = visit("/character/{$character->public_key}");
    
    // First, verify the data we know is working from the screenshot
    $page->assertSee('Test Hydration Character')
        ->assertSee('Wizard') // Class
        ->assertSee('School of Knowledge') // Subclass  
        ->assertSee('Human') // Ancestry
        ->assertSee('Loreborne') // Community
        ->assertSee('Academic Research') // Experience 1
        ->assertSee('Noble Etiquette') // Experience 2
        ->assertPresent('[pest="experience-section"]'); // Experience section

    // Now click the refresh button to trigger Livewire hydration
    $page->click('[pest="refresh-button"]');
    
    // Wait for Livewire to process the refresh
    $page->wait(3);

    // Verify all data is STILL present after Livewire refresh
    $page->assertSee('Test Hydration Character', 'Character name disappeared after refresh button click')
        ->assertSee('Wizard', 'Class disappeared after refresh button click')
        ->assertSee('School of Knowledge', 'Subclass disappeared after refresh button click')  
        ->assertSee('Human', 'Ancestry disappeared after refresh button click')
        ->assertSee('Loreborne', 'Community disappeared after refresh button click')
        ->assertSee('Academic Research', 'Experience 1 disappeared after refresh button click')
        ->assertSee('Noble Etiquette', 'Experience 2 disappeared after refresh button click')
        ->assertPresent('[pest="experience-section"]', 'Experience section disappeared after refresh button click');

    // Verify that "Unnamed Experience" is NOT showing (which would indicate missing experience data)
    $page->assertDontSee('Unnamed Experience', 'Found "Unnamed Experience" which indicates experience data structure is broken');

    // Verify computed stats are still correct  
    $page->assertSee('5', 'Hit points disappeared after refresh button click') // Wizard base 5 + School of Knowledge bonus 0
        ->assertSee('6', 'Stress disappeared after refresh button click'); // Base stress
});

it('validates specific DTO data structures persist through hydration', function () {
    $character = \Domain\Character\Models\Character::factory()->create([
        'name' => 'DTO Test Character',
        'class' => 'warrior',
        'ancestry' => 'clank',
        'community' => 'slyborne'
    ]);

    // Create a regular experience
    \Domain\Character\Models\CharacterExperience::factory()->create([
        'character_id' => $character->id,
        'experience_name' => 'Mechanical Aptitude',
        'experience_description' => 'Understanding of clockwork',
        'modifier' => 3
    ]);

    $page = visit("/character/{$character->public_key}");
    
    // Verify experience is present initially
    $page->assertSee('Mechanical Aptitude', 'Experience not found initially')
        ->assertSee('+3', 'Experience modifier not found initially');

    // Click the refresh button to trigger Livewire hydration
    $page->click('[pest="refresh-button"]');
    
    $page->wait(3);

    // Verify experience is STILL present after hydration
    $page->assertSee('Mechanical Aptitude', 'Experience name disappeared after refresh button click')
        ->assertSee('+3', 'Experience modifier disappeared after refresh button click')
        ->assertDontSee('Unnamed Experience', 'Found "Unnamed Experience" which indicates experience data structure is broken');
});
