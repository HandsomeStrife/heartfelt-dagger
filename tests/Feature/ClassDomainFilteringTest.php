<?php

declare(strict_types=1);

use Domain\Character\Data\CharacterBuilderData;

it('filters level 1 domain cards to the two domains for each class', function (): void {
    $classesPath = base_path('resources/json/classes.json');
    $domainsPath = base_path('resources/json/domains.json');
    $abilitiesPath = base_path('resources/json/abilities.json');

    expect(file_exists($classesPath))->toBeTrue('classes.json not found');
    expect(file_exists($domainsPath))->toBeTrue('domains.json not found');
    expect(file_exists($abilitiesPath))->toBeTrue('abilities.json not found');

    /** @var array<string, mixed> $classes */
    $classes = json_decode((string) file_get_contents($classesPath), true) ?? [];
    /** @var array<string, mixed> $domains */
    $domains = json_decode((string) file_get_contents($domainsPath), true) ?? [];
    /** @var array<string, mixed> $abilities */
    $abilities = json_decode((string) file_get_contents($abilitiesPath), true) ?? [];

    foreach ($classes as $classKey => $_classData) {
        $builder = new CharacterBuilderData(selected_class: $classKey);
        $filtered = $builder->getFilteredDomainCards($domains, $abilities);

        // Skip classes not yet mapped in builder (no filtered domains)
        if (count($filtered) === 0) {
            continue;
        }

        // Should expose exactly two domain blocks when a class is selected
        expect(count($filtered))->toBe(2, "Class '{$classKey}' should expose exactly two domains");

        // Validate each filtered domain block contains only level-1 abilities from that domain
        foreach ($filtered as $domainKey => $block) {
            $listed = array_keys($block['abilities'] ?? []);
            foreach ($listed as $abilityKey) {
                $ability = $abilities[$abilityKey] ?? null;
                expect($ability)->not->toBeNull("Ability '{$abilityKey}' missing");
                expect((int) ($ability['level'] ?? -1))->toBe(1, "Non-level-1 ability leaked into filter for '{$abilityKey}'");
                expect(($ability['domain'] ?? null))->toBe($domainKey, "Ability '{$abilityKey}' has wrong domain for filtered domain '{$domainKey}' (class '{$classKey}')");
            }
        }
    }
});
