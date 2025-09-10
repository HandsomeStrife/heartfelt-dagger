<?php

declare(strict_types=1);

use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Debug Character Viewer Stats', function () {

    it('shows what stats are being passed to the character viewer for your specific character', function () {
        // Create the character in the test database
        $character = Character::create([
            'character_key' => '5XFO9OPAHS',
            'public_key' => '3224GAKRQ4',
            'user_id' => null,
            'name' => '',
            'class' => 'wizard',
            'subclass' => 'school of knowledge',
            'ancestry' => 'drakona',
            'community' => 'loreborne',
            'level' => 1,
            'profile_image_path' => null,
            'pronouns' => null,
            'character_data' => [
                'background' => [
                    'answers' => [],
                    'motivations' => null,
                    'personalHistory' => null,
                    'personalityTraits' => null,
                    'physicalDescription' => null,
                ],
                'connections' => [],
                'last_updated' => '2025-09-04T21:15:01.649411Z',
                'manualStepCompletions' => [],
                'clank_bonus_experience' => null,
            ],
            'is_public' => 0,
            'created_at' => '2025-09-04 18:28:37',
            'updated_at' => '2025-09-04 21:15:01',
        ]);

        // Create some basic traits for the character (School of Knowledge wizard should have 5 HP total)
        // Let's give them a basic trait distribution: [-1, 0, 0, +1, +1, +2]
        CharacterTrait::create(['character_id' => $character->id, 'trait_name' => 'agility', 'trait_value' => -1]);
        CharacterTrait::create(['character_id' => $character->id, 'trait_name' => 'strength', 'trait_value' => 0]);
        CharacterTrait::create(['character_id' => $character->id, 'trait_name' => 'finesse', 'trait_value' => 0]);
        CharacterTrait::create(['character_id' => $character->id, 'trait_name' => 'instinct', 'trait_value' => 1]);
        CharacterTrait::create(['character_id' => $character->id, 'trait_name' => 'presence', 'trait_value' => 1]);
        CharacterTrait::create(['character_id' => $character->id, 'trait_name' => 'knowledge', 'trait_value' => 2]);

        $page = visit('/character/3224GAKRQ4');

        // First check that the page loads
        $page->assertSee('HP'); // Basic assertion that the page has HP content

        // Check what the HP counter shows in the DOM
        $page->script('
            const hpCounter = document.querySelector("[pest=\"hp-counter\"]");
            console.log("HP counter text:", hpCounter ? hpCounter.textContent.trim() : "HP counter not found");
            
            // Also check the Alpine.js data
            const viewerElement = document.querySelector("[x-data]");
            if (viewerElement && viewerElement.__x && viewerElement.__x.$data) {
                console.log("Alpine.js final_hit_points:", viewerElement.__x.$data.final_hit_points);
            } else {
                console.log("Could not find Alpine.js data");
            }
        ');

        // Also try to get the computed stats directly from backend
        $computedStats = $character->fresh()->getComputedStats();
        dump('Backend computed stats final_hit_points:', $computedStats['final_hit_points'] ?? 'NOT FOUND');
        dump('Backend computed stats hit_points:', $computedStats['hit_points'] ?? 'NOT FOUND');

        // Check if the character has the correct class data loaded
        $classes_json = file_get_contents(resource_path('json/classes.json'));
        $classes_data = json_decode($classes_json, true);
        $class_data = $classes_data[$character->class] ?? [];
        $computedWithClassData = $character->fresh()->getComputedStats($class_data);
        dump('Backend computed with class data final_hit_points:', $computedWithClassData['final_hit_points'] ?? 'NOT FOUND');

        // This test is just for debugging - always pass
        expect(true)->toBe(true);
    });
});
