<?php

declare(strict_types=1);

use Domain\Character\Data\CharacterBuilderData;

it('getFilteredDomainCards includes all levels for class domains', function (): void {
    $domainsPath = base_path('resources/json/domains.json');
    $abilitiesPath = base_path('resources/json/abilities.json');

    expect(file_exists($domainsPath))->toBeTrue('domains.json not found');
    expect(file_exists($abilitiesPath))->toBeTrue('abilities.json not found');

    /** @var array<string, mixed> $domains */
    $domains = json_decode((string) file_get_contents($domainsPath), true) ?? [];
    /** @var array<string, mixed> $abilities */
    $abilities = json_decode((string) file_get_contents($abilitiesPath), true) ?? [];

    $builder = new CharacterBuilderData(selected_class: 'bard'); // grace + codex
    $filtered = $builder->getFilteredDomainCards($domains, $abilities);

    // Ensure both domains present
    expect(array_keys($filtered))->toContain('grace', 'codex');

    // For each domain, ensure at least one non-level-1 ability is present if defined in domains.json
    foreach (['grace', 'codex'] as $domainKey) {
        $allKeys = [];
        foreach (($domains[$domainKey]['abilitiesByLevel'] ?? []) as $level => $block) {
            foreach (($block['abilities'] ?? []) as $key) {
                $allKeys[] = $key;
            }
        }
        $allKeys = array_unique($allKeys);
        $presentKeys = array_keys($filtered[$domainKey]['abilities'] ?? []);

        // All declared ability keys for this domain should be present in filtered output
        foreach ($allKeys as $k) {
            expect(in_array($k, $presentKeys, true))->toBeTrue("Missing ability '{$k}' in filtered domain '{$domainKey}'");
        }
    }
});


