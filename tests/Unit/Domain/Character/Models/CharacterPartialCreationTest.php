<?php

declare(strict_types=1);

use Domain\Character\Models\Character;
use Domain\User\Models\User;

it('handles null class value in getBanner method', function () {
    $user = User::factory()->create();
    
    // Create a partially created character with null class
    $character = Character::create([
        'character_key' => 'TEST1234',
        'public_key' => 'PUB12345', 
        'user_id' => $user->id,
        'name' => 'Test Character',
        'class' => null, // This could happen during character creation
        'subclass' => null,
        'ancestry' => null,
        'community' => null,
        'level' => 1,
        'character_data' => [
            'background' => ['answers' => []],
            'connections' => [],
            'creation_date' => now()->toISOString(),
            'builder_version' => '1.0',
        ],
    ]);
    
    // Should not throw an error and should return default warrior banner
    $banner = $character->getBanner();
    
    expect($banner)->toContain('banners/warrior.webp');
});

it('handles empty string class value in getBanner method', function () {
    $user = User::factory()->create();
    
    // Create a character with empty string class
    $character = Character::create([
        'character_key' => 'TEST5678',
        'public_key' => 'PUB56789',
        'user_id' => $user->id,
        'name' => 'Test Character',
        'class' => '', // This could also happen
        'subclass' => null,
        'ancestry' => null,
        'community' => null,
        'level' => 1,
        'character_data' => [
            'background' => ['answers' => []],
            'connections' => [],
            'creation_date' => now()->toISOString(),
            'builder_version' => '1.0',
        ],
    ]);
    
    // Should not throw an error and should return default warrior banner
    $banner = $character->getBanner();
    
    expect($banner)->toContain('banners/warrior.webp');
});

it('returns correct banner for valid class', function () {
    $user = User::factory()->create();
    
    $character = Character::create([
        'character_key' => 'TEST9012',
        'public_key' => 'PUB90123',
        'user_id' => $user->id,
        'name' => 'Test Character',
        'class' => 'Wizard',
        'subclass' => null,
        'ancestry' => null,
        'community' => null,
        'level' => 1,
        'character_data' => [
            'background' => ['answers' => []],
            'connections' => [],
            'creation_date' => now()->toISOString(),
            'builder_version' => '1.0',
        ],
    ]);
    
    $banner = $character->getBanner();
    
    expect($banner)->toContain('banners/wizard.webp');
});
