<?php

declare(strict_types=1);

/**
 * Shared test helper functions for Pest tests
 */

/**
 * Create a test character using the factory (only available in Laravel-dependent tests)
 */
function createTestCharacter(): mixed
{
    if (!class_exists('Domain\Character\Models\Character')) {
        throw new \Exception('createTestCharacter() can only be used in Laravel-dependent tests');
    }
    
    return \Domain\Character\Models\Character::factory()->create();
}

/**
 * Create a test character with specific attributes (only available in Laravel-dependent tests)
 */
function createTestCharacterWith(array $attributes): mixed
{
    if (!class_exists('Domain\Character\Models\Character')) {
        throw new \Exception('createTestCharacterWith() can only be used in Laravel-dependent tests');
    }
    
    return \Domain\Character\Models\Character::factory()->create($attributes);
}
