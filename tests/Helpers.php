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

/**
 * Browser wait helpers (DOM-predicate based) for deterministic tests
 */
function waitForHydration($page, int $timeoutMs = 6000): void
{
    $attempts = (int) ($timeoutMs / 200);
    for ($i = 0; $i < $attempts; $i++) {
        $ready = $page->script('(() => !!document.body.dataset.hydrated)()');
        if ($ready) {
            return;
        }
        $page->wait(0.2);
    }
    throw new Exception('Hydration never completed');
}

function waitForChecked($page, string $selector, int $timeoutMs = 6000): void
{
    $attempts = (int) ($timeoutMs / 200);
    for ($i = 0; $i < $attempts; $i++) {
        $ok = $page->script("(() => !!document.querySelector('" . addslashes($selector) . ":checked'))()");
        if ($ok) {
            return;
        }
        $page->wait(0.2);
    }
    throw new Exception("Never saw {$selector} checked");
}

function waitForLastSave($page, int $timeoutMs = 6000): void
{
    $start = (int) ($page->script('(() => window.__saveSeq || 0)()') ?? 0);
    $attempts = (int) ($timeoutMs / 200);
    for ($i = 0; $i < $attempts; $i++) {
        $seq = (int) ($page->script('(() => window.__saveSeq || 0)()') ?? 0);
        if ($seq > $start) {
            return;
        }
        $page->wait(0.2);
    }
    throw new Exception('Save never completed');
}

function waitForLocked($page, int $timeoutMs = 6000): void
{
    $attempts = (int) ($timeoutMs / 200);
    for ($i = 0; $i < $attempts; $i++) {
        $locked = $page->script('(() => document.body.dataset.anonLocked === "1")()');
        if ($locked) {
            return;
        }
        $page->wait(0.2);
    }
    throw new Exception('Anonymous lock flag not set');
}
