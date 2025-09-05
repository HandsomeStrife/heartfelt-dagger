<?php

declare(strict_types=1);

use Domain\Character\Actions\LoadCharacterStatusAction;
use Domain\Character\Actions\SaveCharacterStatusAction;
use Domain\Character\Data\CharacterStatusData;
use Domain\Character\Models\Character;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Character Status Actions', function () {
    
    it('can save and load character status', function () {
        // Create a character
        $character = Character::factory()->create([
            'character_key' => 'TESTKEY123',
            'public_key' => 'PUBKEY1234',
        ]);

        // Create status data
        $status_data = CharacterStatusData::createDefault($character->id, [
            'final_hit_points' => 5,
            'stress' => 6,
            'armor_score' => 3,
        ]);

        // Modify some values to test saving
        $status_data->hit_points[0] = true; // Mark first HP
        $status_data->stress[1] = true; // Mark second stress
        $status_data->hope[0] = true; // Mark first hope
        $status_data->gold_chest = true; // Mark gold chest

        // Save the status
        $save_action = new SaveCharacterStatusAction();
        $saved_status = $save_action->execute('TESTKEY123', $status_data);

        // Verify saved data
        expect($saved_status->hit_points[0])->toBe(true);
        expect($saved_status->stress[1])->toBe(true);
        expect($saved_status->hope[0])->toBe(true);
        expect($saved_status->gold_chest)->toBe(true);

        // Load the status
        $load_action = new LoadCharacterStatusAction();
        $loaded_status = $load_action->execute('TESTKEY123', [
            'final_hit_points' => 5,
            'stress' => 6,
            'armor_score' => 3,
        ]);

        // Verify loaded data matches saved data
        expect($loaded_status->hit_points[0])->toBe(true);
        expect($loaded_status->stress[1])->toBe(true);
        expect($loaded_status->hope[0])->toBe(true);
        expect($loaded_status->gold_chest)->toBe(true);
    });

    it('creates default status when none exists', function () {
        // Create a character
        $character = Character::factory()->create([
            'character_key' => 'TESTKEY456',
        ]);

        // Load status (should create default)
        $load_action = new LoadCharacterStatusAction();
        $status = $load_action->execute('TESTKEY456', [
            'final_hit_points' => 7,
            'stress' => 8,
            'armor_score' => 2,
        ]);

        // Verify default values
        expect($status->hit_points)->toHaveCount(7);
        expect($status->stress)->toHaveCount(8);
        expect($status->armor_slots)->toHaveCount(2);
        expect($status->hope)->toHaveCount(6);
        expect($status->gold_handfuls)->toHaveCount(9);
        expect($status->gold_bags)->toHaveCount(9);
        
        // All should be false initially
        expect(array_filter($status->hit_points))->toBeEmpty();
        expect(array_filter($status->stress))->toBeEmpty();
        expect(array_filter($status->hope))->toBeEmpty();
        expect($status->gold_chest)->toBe(false);
    });

    it('adjusts status arrays when stats change', function () {
        // Create a character with initial status
        $character = Character::factory()->create([
            'character_key' => 'TESTKEY789',
        ]);

        // Create initial status with 5 HP
        $initial_status = CharacterStatusData::createDefault($character->id, [
            'final_hit_points' => 5,
            'stress' => 6,
            'armor_score' => 2,
        ]);
        $initial_status->hit_points[2] = true; // Mark 3rd HP

        // Save initial status
        $save_action = new SaveCharacterStatusAction();
        $save_action->execute('TESTKEY789', $initial_status);

        // Load with different stats (7 HP now)
        $load_action = new LoadCharacterStatusAction();
        $adjusted_status = $load_action->execute('TESTKEY789', [
            'final_hit_points' => 7, // Increased from 5 to 7
            'stress' => 6,
            'armor_score' => 2,
        ]);

        // Should have 7 HP slots now, with the 3rd still marked
        expect($adjusted_status->hit_points)->toHaveCount(7);
        expect($adjusted_status->hit_points[2])->toBe(true); // Should preserve existing mark
        expect($adjusted_status->hit_points[5])->toBe(false); // New slots should be false
    });
});


