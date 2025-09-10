<?php

declare(strict_types=1);

use Domain\Character\Actions\LoadCharacterStatusAction;
use Domain\Character\Actions\SaveCharacterStatusAction;
use Domain\Character\Data\CharacterStatusData;
use Domain\Character\Models\Character;
use Domain\Character\Repositories\CharacterRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Character Status Integration', function () {

    it('can load character with status via repository', function () {
        // Create a character
        $character = Character::factory()->create([
            'character_key' => 'TESTKEY123',
            'public_key' => 'PUBKEY1234',
        ]);

        // Create some status
        $status_data = CharacterStatusData::createDefault($character->id, [
            'final_hit_points' => 6,
            'stress' => 6,
            'armor_score' => 2,
        ]);
        $status_data->hit_points[0] = true; // Mark first HP

        $save_action = new SaveCharacterStatusAction;
        $save_action->execute('TESTKEY123', $status_data);

        // Load via repository
        $repository = new CharacterRepository;
        $result = $repository->findByKeyWithStatus('TESTKEY123');

        expect($result)->not->toBeNull();
        expect($result['character'])->not->toBeNull();
        expect($result['status'])->not->toBeNull();
        expect($result['status']->hit_points[0])->toBe(true);
    });

    it('can load character by public key with status', function () {
        // Create a character
        $character = Character::factory()->create([
            'character_key' => 'TESTKEY456',
            'public_key' => 'PUBKEY5678',
        ]);

        // Load via repository using public key
        $repository = new CharacterRepository;
        $result = $repository->findByPublicKeyWithStatus('PUBKEY5678');

        expect($result)->not->toBeNull();
        expect($result['character'])->not->toBeNull();
        expect($result['status'])->toBeNull(); // No status created yet
    });

    it('handles status array adjustments when stats change', function () {
        // Create a character
        $character = Character::factory()->create([
            'character_key' => 'TESTKEY789',
        ]);

        // Create initial status with 5 HP
        $initial_status = CharacterStatusData::createDefault($character->id, [
            'final_hit_points' => 5,
            'stress' => 6,
            'armor_score' => 1,
        ]);
        $initial_status->hit_points[2] = true; // Mark 3rd HP

        $save_action = new SaveCharacterStatusAction;
        $save_action->execute('TESTKEY789', $initial_status);

        // Load with different stats (7 HP now)
        $load_action = new LoadCharacterStatusAction;
        $adjusted_status = $load_action->execute('TESTKEY789', [
            'final_hit_points' => 7, // Increased from 5 to 7
            'stress' => 6,
            'armor_score' => 1,
        ]);

        // Should have 7 HP slots now, with the 3rd still marked
        expect($adjusted_status->hit_points)->toHaveCount(7);
        expect($adjusted_status->hit_points[2])->toBe(true); // Should preserve existing mark
        expect($adjusted_status->hit_points[5])->toBe(false); // New slots should be false
    });
});
