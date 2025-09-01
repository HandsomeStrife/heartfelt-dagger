<?php

declare(strict_types=1);

use Domain\Character\Data\CharacterBuilderData;

it('school of knowledge grants +1 domain card capacity', function (): void {
    $builder = new CharacterBuilderData(
        selected_class: 'wizard',
        selected_subclass: 'school of knowledge'
    );

    expect($builder->getMaxDomainCards())->toBe(3);
});

it('school of war hit_point_bonus effect is +1', function (): void {
    $builder = new CharacterBuilderData(
        selected_class: 'wizard',
        selected_subclass: 'school of war'
    );

    expect($builder->getSubclassHitPointBonus())->toBe(1);
});

it('nightwalker evasion_bonus effect is +1', function (): void {
    $builder = new CharacterBuilderData(
        selected_class: 'rogue',
        selected_subclass: 'nightwalker'
    );
    expect($builder->getSubclassEvasionBonus())->toBe(1);
});

it('clank experience bonus applies +1 to chosen experience (total +3)', function (): void {
    $builder = new CharacterBuilderData(
        selected_ancestry: 'clank',
        experiences: [
            ['name' => 'Wilderness Survival', 'description' => '', 'modifier' => 2],
            ['name' => 'Noble Etiquette', 'description' => '', 'modifier' => 2],
        ],
        clank_bonus_experience: 'Wilderness Survival'
    );

    expect($builder->getExperienceModifier('Wilderness Survival'))->toBe(3);
    expect($builder->getExperienceModifier('Noble Etiquette'))->toBe(2);
});

it('ancestry numeric bonuses apply (galapa +2 thresholds, giant +1 hp, human +1 stress, simiah +1 evasion)', function (): void {
    $classes = json_decode((string) file_get_contents(base_path('resources/json/classes.json')), true);
    $guardian = $classes['guardian'];

    // Galapa
    $galapa = new CharacterBuilderData(selected_class: 'guardian', selected_ancestry: 'galapa');
    $galapaStats = $galapa->getComputedStats($guardian);
    expect($galapaStats['detailed']['damage_thresholds']['ancestry_bonus'])->toBe(2);

    // Giant (+1 HP)
    $giant = new CharacterBuilderData(selected_class: 'guardian', selected_ancestry: 'giant');
    $giantStats = $giant->getComputedStats($guardian);
    expect($giantStats['hit_points'])->toBe(($guardian['startingHitPoints'] ?? 0) + 1);

    // Human (+1 stress)
    $human = new CharacterBuilderData(selected_class: 'guardian', selected_ancestry: 'human');
    $humanStats = $human->getComputedStats($guardian);
    expect($humanStats['stress'])->toBe(7);

    // Simiah (+1 evasion)
    $simiah = new CharacterBuilderData(selected_class: 'guardian', selected_ancestry: 'simiah');
    $simiahStats = $simiah->getComputedStats($guardian);
    expect($simiahStats['evasion'])->toBe(($guardian['startingEvasion'] ?? 0) + 1);
});


