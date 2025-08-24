<?php

declare(strict_types=1);
use Domain\Character\Data\CharacterBuilderData;
use PHPUnit\Framework\Attributes\Test;
test('school of knowledge builder provides domain card bonuses', function () {
    $builder = new CharacterBuilderData(
        selected_class: 'wizard',
        selected_subclass: 'school of knowledge'
    );

    $domainCardBonus = $builder->getSubclassDomainCardBonus();
    $maxDomainCards = $builder->getMaxDomainCards();

    // School of Knowledge should get bonus only from specialization (+1) = +1 total
    expect($domainCardBonus)->toEqual(1);

    // Base 2 cards + 1 bonus card = 3 total
    expect($maxDomainCards)->toEqual(3);
});
test('non bonus subclass builder has default domain cards', function () {
    $builder = new CharacterBuilderData(
        selected_class: 'ranger',
        selected_subclass: 'beastbound'
    );

    $domainCardBonus = $builder->getSubclassDomainCardBonus();
    $maxDomainCards = $builder->getMaxDomainCards();

    expect($domainCardBonus)->toEqual(0);
    expect($maxDomainCards)->toEqual(2);
    // Base cards only
});
test('null subclass builder has default domain cards', function () {
    $builder = new CharacterBuilderData(
        selected_class: 'warrior',
        selected_subclass: null
    );

    $domainCardBonus = $builder->getSubclassDomainCardBonus();
    $maxDomainCards = $builder->getMaxDomainCards();

    expect($domainCardBonus)->toEqual(0);
    expect($maxDomainCards)->toEqual(2);
    // Base cards only
});
test('builder domain card calculations match different subclasses', function () {
    $testCases = [
        ['subclass' => 'school of knowledge', 'expected_bonus' => 1, 'expected_max' => 3],
        ['subclass' => 'school of war', 'expected_bonus' => 0, 'expected_max' => 2],
        ['subclass' => 'stalwart', 'expected_bonus' => 0, 'expected_max' => 2],
        ['subclass' => 'nightwalker', 'expected_bonus' => 0, 'expected_max' => 2],
    ];

    foreach ($testCases as $testCase) {
        $builder = new CharacterBuilderData(
            selected_class: 'warrior',
            selected_subclass: $testCase['subclass']
        );

        expect($builder->getSubclassDomainCardBonus())->toEqual($testCase['expected_bonus'], "Builder with subclass {$testCase['subclass']} should have {$testCase['expected_bonus']} domain card bonus");

        expect($builder->getMaxDomainCards())->toEqual($testCase['expected_max'], "Builder with subclass {$testCase['subclass']} should have {$testCase['expected_max']} max domain cards");
    }
});
test('builder domain card methods handle missing subclass json', function () {
    // Create a builder with a non-existent subclass
    $builder = new CharacterBuilderData(
        selected_class: 'warrior',
        selected_subclass: 'non-existent-subclass'
    );

    // Should gracefully return 0 for non-existent subclass
    expect($builder->getSubclassDomainCardBonus())->toEqual(0);
    expect($builder->getMaxDomainCards())->toEqual(2);
});
