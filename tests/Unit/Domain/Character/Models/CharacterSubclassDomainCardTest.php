<?php

declare(strict_types=1);
use Domain\Character\Models\Character;
use PHPUnit\Framework\Attributes\Test;
test('school of knowledge provides domain card bonuses', function () {
    $character = Character::factory()->create([
        'subclass' => 'school of knowledge',
        'level' => 1,
    ]);

    $domainCardBonus = $character->getSubclassDomainCardBonus();
    $maxDomainCards = $character->getMaxDomainCards();

    // School of Knowledge should get bonus only from specialization (+1) = +1 total
    expect($domainCardBonus)->toEqual(1);

    // Base 2 cards + 1 bonus card = 3 total
    expect($maxDomainCards)->toEqual(3);
});
test('non bonus subclasses have default domain cards', function () {
    $character = Character::factory()->create([
        'subclass' => 'beastbound', // No domain card bonuses
        'level' => 1,
    ]);

    $domainCardBonus = $character->getSubclassDomainCardBonus();
    $maxDomainCards = $character->getMaxDomainCards();

    expect($domainCardBonus)->toEqual(0);
    expect($maxDomainCards)->toEqual(2);
    // Base cards only
});
test('null subclass has default domain cards', function () {
    $character = Character::factory()->create([
        'subclass' => null,
        'level' => 1,
    ]);

    $domainCardBonus = $character->getSubclassDomainCardBonus();
    $maxDomainCards = $character->getMaxDomainCards();

    expect($domainCardBonus)->toEqual(0);
    expect($maxDomainCards)->toEqual(2);
    // Base cards only
});
test('various subclasses domain card calculations', function () {
    // Test different subclasses that might have domain card bonuses
    $testCases = [
        ['subclass' => 'school of knowledge', 'expected_bonus' => 1, 'expected_max' => 3],
        ['subclass' => 'school of war', 'expected_bonus' => 0, 'expected_max' => 2],
        ['subclass' => 'stalwart', 'expected_bonus' => 0, 'expected_max' => 2],
        ['subclass' => 'nightwalker', 'expected_bonus' => 0, 'expected_max' => 2],
    ];

    foreach ($testCases as $testCase) {
        $character = Character::factory()->create([
            'subclass' => $testCase['subclass'],
            'level' => 1,
        ]);

        expect($character->getSubclassDomainCardBonus())->toEqual($testCase['expected_bonus'], "Subclass {$testCase['subclass']} should have {$testCase['expected_bonus']} domain card bonus");

        expect($character->getMaxDomainCards())->toEqual($testCase['expected_max'], "Subclass {$testCase['subclass']} should have {$testCase['expected_max']} max domain cards");
    }
});
