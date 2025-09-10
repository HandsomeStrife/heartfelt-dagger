<?php

declare(strict_types=1);

describe('Abilities Data Integrity and Schema Validation', function () {

    it('validates abilities.json cross-reference with domains.json for all levels 1-10', function () {
        $domainsData = json_decode(file_get_contents(resource_path('json/domains.json')), true);
        $abilitiesData = json_decode(file_get_contents(resource_path('json/abilities.json')), true);

        $validDomains = ['arcana', 'blade', 'bone', 'codex', 'dread', 'grace', 'midnight', 'sage', 'splendor', 'valor'];

        foreach ($domainsData as $domainKey => $domainInfo) {
            expect(in_array($domainKey, $validDomains))->toBeTrue("Domain '{$domainKey}' is not in valid domain list");

            // Check each level 1-10
            for ($level = 1; $level <= 10; $level++) {
                $levelData = $domainInfo['abilitiesByLevel'][$level] ?? null;

                if ($levelData && isset($levelData['abilities'])) {
                    foreach ($levelData['abilities'] as $abilityKey) {
                        // Ability must exist in abilities.json
                        expect($abilitiesData)->toHaveKey($abilityKey)->and($abilitiesData[$abilityKey])->not->toBeNull();

                        $ability = $abilitiesData[$abilityKey];

                        // Domain must match
                        expect($ability['domain'])->toBe($domainKey,
                            "Ability '{$abilityKey}' has domain '{$ability['domain']}' but listed under '{$domainKey}'"
                        );

                        // Level must match exactly (no more allowing <= since we fixed the data)
                        expect($ability['level'])->toBe($level,
                            "Ability '{$abilityKey}' has level '{$ability['level']}' but listed under level {$level}"
                        );
                    }
                }
            }
        }
    });

    it('validates ability schema for every entry in abilities.json', function () {
        $abilitiesData = json_decode(file_get_contents(resource_path('json/abilities.json')), true);
        $validDomains = ['arcana', 'blade', 'bone', 'codex', 'dread', 'grace', 'midnight', 'sage', 'splendor', 'valor'];
        $validTypes = ['Ability', 'Spell', 'Grimoire', 'Attack', 'Reaction'];

        foreach ($abilitiesData as $abilityKey => $ability) {
            // Domain validation
            expect($ability)->toHaveKey('domain')->and($ability['domain'])->not->toBeEmpty();
            expect(in_array($ability['domain'], $validDomains))->toBeTrue(
                "Ability '{$abilityKey}' has invalid domain '{$ability['domain']}'"
            );

            // Level validation
            expect($ability)->toHaveKey('level')->and($ability['level'])->not->toBeNull();
            expect($ability['level'])->toBeInt("Ability '{$abilityKey}' level must be integer");
            expect($ability['level'])->toBeGreaterThanOrEqual(1, "Ability '{$abilityKey}' level must be >= 1");
            expect($ability['level'])->toBeLessThanOrEqual(10, "Ability '{$abilityKey}' level must be <= 10");

            // Type validation
            expect($ability)->toHaveKey('type')->and($ability['type'])->not->toBeEmpty();
            expect(in_array($ability['type'], $validTypes))->toBeTrue(
                "Ability '{$abilityKey}' has invalid type '{$ability['type']}'"
            );

            // Recall cost validation
            expect($ability)->toHaveKey('recallCost')->and($ability['recallCost'])->not->toBeNull();
            expect($ability['recallCost'])->toBeNumeric("Ability '{$abilityKey}' recallCost must be numeric");
            expect($ability['recallCost'])->toBeGreaterThanOrEqual(0, "Ability '{$abilityKey}' recallCost must be >= 0");

            // Descriptions validation
            expect($ability)->toHaveKey('descriptions')->and($ability['descriptions'])->not->toBeEmpty();
            expect($ability['descriptions'])->toBeArray("Ability '{$abilityKey}' descriptions must be array");

            foreach ($ability['descriptions'] as $index => $description) {
                expect($description)->toBeString("Ability '{$abilityKey}' description {$index} must be string");
                expect(trim($description))->not->toBeEmpty("Ability '{$abilityKey}' description {$index} cannot be empty");
            }

            // Playtest validation (if present)
            if (isset($ability['playtest'])) {
                if (isset($ability['playtest']['isPlaytest']) && $ability['playtest']['isPlaytest']) {
                    expect($ability['playtest'])->toHaveKey('label')->and($ability['playtest']['label'])->not->toBeEmpty();
                    expect($ability['playtest']['label'])->toBeString(
                        "Playtest ability '{$abilityKey}' label must be string"
                    );
                    expect(str_contains($ability['playtest']['label'], 'Void - Playtest'))->toBeTrue(
                        "Playtest ability '{$abilityKey}' must have 'Void - Playtest' in label"
                    );
                }
            }

            // Name consistency validation
            expect($ability)->toHaveKey('name')->and($ability['name'])->not->toBeEmpty();
            expect($ability['name'])->toBeString("Ability '{$abilityKey}' name must be string");
            expect(trim($ability['name']))->not->toBeEmpty("Ability '{$abilityKey}' name cannot be empty");
        }
    });

    it('validates presence of domain touched cards at level 7', function () {
        $domainsData = json_decode(file_get_contents(resource_path('json/domains.json')), true);
        $abilitiesData = json_decode(file_get_contents(resource_path('json/abilities.json')), true);

        $expectedTouchedCards = [
            'arcana' => 'arcanatouched',
            'blade' => 'bladetouched',
            'bone' => 'bonetouched',
            'codex' => 'codextouched',
            'grace' => 'gracetouched',
            'midnight' => 'midnighttouched',
            'sage' => 'sagetouched',
            'splendor' => 'splendortouched',
            'valor' => 'valortouched',
            'dread' => 'dreadtouched', // Playtest
        ];

        foreach ($expectedTouchedCards as $domain => $expectedCard) {
            // Check if domain exists in domains.json
            if (! isset($domainsData[$domain])) {
                continue; // Skip if domain doesn't exist (e.g., playtest content)
            }

            $level7Data = $domainsData[$domain]['abilitiesByLevel']['7'] ?? null;
            expect($level7Data)->not->toBeNull("Domain '{$domain}' must have level 7 abilities");
            expect(in_array($expectedCard, $level7Data['abilities']))->toBeTrue(
                "Domain '{$domain}' level 7 must contain touched card '{$expectedCard}'"
            );

            // Verify the touched card exists in abilities.json
            expect($abilitiesData)->toHaveKey($expectedCard)->and($abilitiesData[$expectedCard])->not->toBeNull();

            $touchedCard = $abilitiesData[$expectedCard];
            expect($touchedCard['domain'])->toBe($domain,
                "Touched card '{$expectedCard}' must have domain '{$domain}'"
            );
            expect($touchedCard['level'])->toBe(7,
                "Touched card '{$expectedCard}' must be level 7"
            );
            expect($touchedCard['recallCost'])->toBeNumeric(
                "Touched card '{$expectedCard}' must have valid recall cost"
            );
        }
    });

    it('validates representative high-tier cards exist for levels 8-10', function () {
        $domainsData = json_decode(file_get_contents(resource_path('json/domains.json')), true);
        $abilitiesData = json_decode(file_get_contents(resource_path('json/abilities.json')), true);

        foreach ($domainsData as $domainKey => $domainInfo) {
            for ($level = 8; $level <= 10; $level++) {
                $levelData = $domainInfo['abilitiesByLevel'][$level] ?? null;

                if ($levelData && isset($levelData['abilities']) && ! empty($levelData['abilities'])) {
                    // At least one ability should exist for this level
                    $abilityKey = $levelData['abilities'][0];

                    expect($abilitiesData)->toHaveKey($abilityKey)->and($abilitiesData[$abilityKey])->not->toBeNull();

                    $ability = $abilitiesData[$abilityKey];
                    expect($ability['level'])->toBe($level,
                        "High-tier ability '{$abilityKey}' should be level {$level}"
                    );
                    expect($ability['recallCost'])->toBeNumeric(
                        "High-tier ability '{$abilityKey}' must have valid recall cost"
                    );
                }
            }
        }
    });

    it('validates Codex Grimoire entries have correct type and recall costs', function () {
        $abilitiesData = json_decode(file_get_contents(resource_path('json/abilities.json')), true);

        $grimoireCount = 0;
        foreach ($abilitiesData as $abilityKey => $ability) {
            if ($ability['domain'] === 'codex' && str_contains(strtolower($ability['name']), 'book of')) {
                $grimoireCount++;

                expect($ability['type'])->toBe('Grimoire',
                    "Codex book '{$abilityKey}' must have type 'Grimoire'"
                );
                expect($ability['recallCost'])->toBeNumeric(
                    "Grimoire '{$abilityKey}' must have valid recall cost"
                );
                expect($ability['domain'])->toBe('codex',
                    "Grimoire '{$abilityKey}' must be in codex domain"
                );
            }
        }

        expect($grimoireCount)->toBeGreaterThan(0, 'At least one Codex Grimoire should exist');
    });

    it('validates Dread playtest abilities carry playtest flags', function () {
        $abilitiesData = json_decode(file_get_contents(resource_path('json/abilities.json')), true);

        foreach ($abilitiesData as $abilityKey => $ability) {
            if ($ability['domain'] === 'dread') {
                // All Dread abilities should be playtest
                expect($ability)->toHaveKey('playtest')->and($ability['playtest'])->not->toBeEmpty();
                expect($ability['playtest']['isPlaytest'])->toBe(true,
                    "Dread ability '{$abilityKey}' must be marked as playtest"
                );
                expect(str_contains($ability['playtest']['label'], 'Void - Playtest'))->toBeTrue(
                    "Dread ability '{$abilityKey}' must have 'Void - Playtest' label"
                );
            }
        }
    });

    it('validates no orphaned abilities (all abilities listed in domains.json)', function () {
        $domainsData = json_decode(file_get_contents(resource_path('json/domains.json')), true);
        $abilitiesData = json_decode(file_get_contents(resource_path('json/abilities.json')), true);

        $listedAbilities = [];

        // Collect all abilities listed in domains.json
        foreach ($domainsData as $domainKey => $domainInfo) {
            for ($level = 1; $level <= 10; $level++) {
                $levelData = $domainInfo['abilitiesByLevel'][$level] ?? null;
                if ($levelData && isset($levelData['abilities'])) {
                    $listedAbilities = array_merge($listedAbilities, $levelData['abilities']);
                }
            }
        }

        $listedAbilities = array_unique($listedAbilities);

        // Check that every ability in abilities.json is listed somewhere
        $orphanedAbilities = [];
        foreach ($abilitiesData as $abilityKey => $ability) {
            if (! in_array($abilityKey, $listedAbilities)) {
                $orphanedAbilities[] = $abilityKey;
            }
        }

        if (! empty($orphanedAbilities)) {
            echo 'Found '.count($orphanedAbilities)." orphaned abilities:\n";
            foreach (array_slice($orphanedAbilities, 0, 10) as $orphan) {
                echo "- {$orphan}\n";
            }
            if (count($orphanedAbilities) > 10) {
                echo '... and '.(count($orphanedAbilities) - 10)." more\n";
            }
        }

        expect($orphanedAbilities)->toBeEmpty('Found orphaned abilities that exist in abilities.json but not listed in domains.json');
    });

    it('validates no duplicate listings across multiple levels for same ability', function () {
        $domainsData = json_decode(file_get_contents(resource_path('json/domains.json')), true);

        $abilityLevelMap = [];

        foreach ($domainsData as $domainKey => $domainInfo) {
            for ($level = 1; $level <= 10; $level++) {
                $levelData = $domainInfo['abilitiesByLevel'][$level] ?? null;
                if ($levelData && isset($levelData['abilities'])) {
                    foreach ($levelData['abilities'] as $abilityKey) {
                        if (isset($abilityLevelMap[$abilityKey])) {
                            expect($abilityLevelMap[$abilityKey])->toBe($level,
                                "Ability '{$abilityKey}' listed at multiple levels: {$abilityLevelMap[$abilityKey]} and {$level}"
                            );
                        }
                        $abilityLevelMap[$abilityKey] = $level;
                    }
                }
            }
        }
    });
});
