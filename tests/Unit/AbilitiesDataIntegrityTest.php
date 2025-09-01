<?php

declare(strict_types=1);

use Illuminate\Support\Collection;

it('abilities referenced by domains.json exist and match domain/level (1–10)', function (): void {
    $domainsPath = base_path('resources/json/domains.json');
    $abilitiesPath = base_path('resources/json/abilities.json');

    expect(file_exists($domainsPath))->toBeTrue('domains.json not found');
    expect(file_exists($abilitiesPath))->toBeTrue('abilities.json not found');

    /** @var array<string, mixed> $domains */
    $domains = json_decode((string) file_get_contents($domainsPath), true) ?? [];
    /** @var array<string, mixed> $abilities */
    $abilities = json_decode((string) file_get_contents($abilitiesPath), true) ?? [];

    foreach ($domains as $domainKey => $domainData) {
        $byLevel = $domainData['abilitiesByLevel'] ?? [];
        foreach ($byLevel as $level => $levelData) {
            $listed = $levelData['abilities'] ?? [];
            foreach ($listed as $abilityKey) {
                $msg = "Ability '{$abilityKey}' for domain '{$domainKey}' level '{$level}'";
                expect(isset($abilities[$abilityKey]))->toBeTrue("{$msg} missing from abilities.json");
                if (! isset($abilities[$abilityKey])) {
                    continue; // avoid cascading errors
                }
                $ability = $abilities[$abilityKey];
                $abilityDomain = $ability['domain'] ?? null;
                $abilityLevel = (int) ($ability['level'] ?? -1);
                expect($abilityDomain)->toBe($domainKey, $msg . " has mismatched domain '" . (is_null($abilityDomain) ? 'null' : (string) $abilityDomain) . "'");

                // Some domain lists repeat lower-level abilities at higher levels; allow ability level <= listed level
                expect($abilityLevel <= (int) $level)->toBeTrue(
                    $msg . " has mismatched level '" . (isset($ability['level']) ? (string) $ability['level'] : 'null') . "'"
                );
            }
        }
    }
});

it('every abilities.json entry has a valid schema and no empty descriptions', function (): void {
    $abilitiesPath = base_path('resources/json/abilities.json');
    expect(file_exists($abilitiesPath))->toBeTrue('abilities.json not found');

    /** @var array<string, mixed> $abilities */
    $abilities = json_decode((string) file_get_contents($abilitiesPath), true) ?? [];

    $allowedDomains = collect(['arcana','blade','bone','codex','dread','grace','midnight','sage','splendor','valor']);
    $allowedTypes = collect(['Ability','Spell','Grimoire']);

    foreach ($abilities as $key => $ability) {
        $ctx = "Ability '{$key}'";
        expect(isset($ability['name']) && is_string($ability['name']) && $ability['name'] !== '')->toBeTrue($ctx . ' must have a non-empty name');

        $domainVal = $ability['domain'] ?? null;
        $domainStr = is_null($domainVal) ? 'null' : (string) $domainVal;
        expect($allowedDomains->contains($domainVal))->toBeTrue($ctx . " has invalid domain '" . $domainStr . "'");

        $levelVal = $ability['level'] ?? null;
        $levelStr = is_null($levelVal) ? 'null' : (string) $levelVal;
        expect(is_int($ability['level']) && $ability['level'] >= 1 && $ability['level'] <= 10)
            ->toBeTrue($ctx . " has invalid level '" . $levelStr . "'");

        $typeVal = $ability['type'] ?? null;
        $typeStr = is_null($typeVal) ? 'null' : (string) $typeVal;
        expect($allowedTypes->contains($typeVal))->toBeTrue($ctx . " has invalid type '" . $typeStr . "'");

        expect(isset($ability['recallCost']) && is_numeric($ability['recallCost']) && $ability['recallCost'] >= 0)
            ->toBeTrue($ctx . ' has invalid recallCost');

        $descriptions = $ability['descriptions'] ?? [];
        expect(is_array($descriptions) && count($descriptions) > 0)->toBeTrue($ctx . ' must include descriptions');
        if (is_array($descriptions)) {
            foreach ($descriptions as $index => $line) {
                expect(is_string($line) && trim($line) !== '')->toBeTrue($ctx . ' description #' . (string) $index . ' is empty');
            }
        }

        if (! empty($ability['playtest']['isPlaytest'] ?? false)) {
            expect(isset($ability['playtest']['label']) && is_string($ability['playtest']['label']) && $ability['playtest']['label'] !== '')
                ->toBeTrue($ctx . ' playtest ability requires a label');
        }
    }
});

it('no orphan abilities: each ability appears in some domain level list', function (): void {
    $domainsPath = base_path('resources/json/domains.json');
    $abilitiesPath = base_path('resources/json/abilities.json');
    expect(file_exists($domainsPath))->toBeTrue('domains.json not found');
    expect(file_exists($abilitiesPath))->toBeTrue('abilities.json not found');

    /** @var array<string, mixed> $domains */
    $domains = json_decode((string) file_get_contents($domainsPath), true) ?? [];
    /** @var array<string, mixed> $abilities */
    $abilities = json_decode((string) file_get_contents($abilitiesPath), true) ?? [];

    $listedKeys = collect($domains)
        ->map(fn ($d) => $d['abilitiesByLevel'] ?? [])
        ->flatMap(fn ($byLevel) => collect($byLevel)->flatMap(fn ($l) => $l['abilities'] ?? []))
        ->values();

    foreach ($abilities as $key => $ability) {
        expect($listedKeys->contains($key))->toBeTrue("Ability '{$key}' is not referenced by any domain level list");
    }
});


