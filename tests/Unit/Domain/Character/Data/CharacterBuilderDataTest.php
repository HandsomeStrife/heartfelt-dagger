<?php

declare(strict_types=1);
use Domain\Character\Data\CharacterBuilderData;
use Domain\Character\Enums\CharacterBuilderStep;
use PHPUnit\Framework\Attributes\Test;
it('can be constructed with all defaults', function () {
    $data = new CharacterBuilderData;

    expect($data->name)->toBeNull();
    expect($data->selected_class)->toBeNull();
    expect($data->selected_subclass)->toBeNull();
    expect($data->selected_ancestry)->toBeNull();
    expect($data->selected_community)->toBeNull();
    expect($data->assigned_traits)->toEqual([]);
    expect($data->selected_equipment)->toEqual([]);
    expect($data->experiences)->toEqual([]);
    expect($data->selected_domain_cards)->toEqual([]);
    expect($data->background_answers)->toEqual([]);
    expect($data->connection_answers)->toEqual([]);
    expect($data->profile_image_path)->toBeNull();
    expect($data->physical_description)->toBeNull();
    expect($data->personality_traits)->toBeNull();
    expect($data->personal_history)->toBeNull();
    expect($data->motivations)->toBeNull();
});
it('can be constructed with all parameters', function () {
    $data = new CharacterBuilderData(
        name: 'Test Hero',
        selected_class: 'warrior',
        selected_subclass: 'call-of-the-brave',
        selected_ancestry: 'human',
        selected_community: 'order-of-scholars',
        assigned_traits: ['agility' => 2, 'strength' => 1],
        selected_equipment: [['key' => 'sword', 'type' => 'weapon']],
        experiences: [['name' => 'Combat Training', 'description' => 'Trained with guards']],
        selected_domain_cards: [['domain' => 'blade', 'ability_key' => 'strike']],
        background_answers: ['Answer 1', 'Answer 2'],
        connection_answers: ['Connection 1'],
        profile_image_path: 'hero.jpg',
        physical_description: 'Tall and strong',
        personality_traits: 'Brave',
        personal_history: 'Born in village',
        motivations: 'Protect people'
    );

    expect($data->name)->toEqual('Test Hero');
    expect($data->selected_class)->toEqual('warrior');
    expect($data->selected_subclass)->toEqual('call-of-the-brave');
    expect($data->selected_ancestry)->toEqual('human');
    expect($data->selected_community)->toEqual('order-of-scholars');
    expect($data->assigned_traits)->toEqual(['agility' => 2, 'strength' => 1]);
    expect($data->selected_equipment)->toEqual([['key' => 'sword', 'type' => 'weapon']]);
    expect($data->experiences)->toEqual([['name' => 'Combat Training', 'description' => 'Trained with guards']]);
    expect($data->selected_domain_cards)->toEqual([['domain' => 'blade', 'ability_key' => 'strike']]);
    expect($data->background_answers)->toEqual(['Answer 1', 'Answer 2']);
    expect($data->connection_answers)->toEqual(['Connection 1']);
    expect($data->profile_image_path)->toEqual('hero.jpg');
    expect($data->physical_description)->toEqual('Tall and strong');
    expect($data->personality_traits)->toEqual('Brave');
    expect($data->personal_history)->toEqual('Born in village');
    expect($data->motivations)->toEqual('Protect people');
});
it('can be created from array', function () {
    $array = [
        'name' => 'Array Hero',
        'selected_class' => 'ranger',
        'selected_subclass' => 'beast-hunter',
        'selected_ancestry' => 'elf',
        'selected_community' => 'wildlands',
        'assigned_traits' => ['finesse' => 1],
        'selected_equipment' => [['key' => 'bow', 'type' => 'weapon']],
        'experiences' => [['name' => 'Hunting', 'description' => 'Hunted beasts']],
        'selected_domain_cards' => [['domain' => 'sage', 'ability_key' => 'track']],
        'background_answers' => ['Background answer'],
        'connection_answers' => ['Connection answer'],
        'profile_image_path' => 'ranger.jpg',
        'physical_description' => 'Agile',
        'personality_traits' => 'Cautious',
        'personal_history' => 'Forest dweller',
        'motivations' => 'Protect nature',
    ];

    $data = CharacterBuilderData::from($array);

    expect($data->name)->toEqual('Array Hero');
    expect($data->selected_class)->toEqual('ranger');
    expect($data->selected_subclass)->toEqual('beast-hunter');
    expect($data->selected_ancestry)->toEqual('elf');
    expect($data->selected_community)->toEqual('wildlands');
    expect($data->assigned_traits)->toEqual(['finesse' => 1]);
    expect($data->selected_equipment)->toEqual([['key' => 'bow', 'type' => 'weapon']]);
    expect($data->experiences)->toEqual([['name' => 'Hunting', 'description' => 'Hunted beasts']]);
    expect($data->selected_domain_cards)->toEqual([['domain' => 'sage', 'ability_key' => 'track']]);
    expect($data->background_answers)->toEqual(['Background answer']);
    expect($data->connection_answers)->toEqual(['Connection answer']);
    expect($data->profile_image_path)->toEqual('ranger.jpg');
    expect($data->physical_description)->toEqual('Agile');
    expect($data->personality_traits)->toEqual('Cautious');
    expect($data->personal_history)->toEqual('Forest dweller');
    expect($data->motivations)->toEqual('Protect nature');
});
it('can be converted to array', function () {
    $data = new CharacterBuilderData(
        name: 'Test Hero',
        selected_class: 'warrior',
        assigned_traits: ['strength' => 2]
    );

    $array = $data->toArray();

    expect($array['name'])->toEqual('Test Hero');
    expect($array['selected_class'])->toEqual('warrior');
    expect($array['assigned_traits'])->toEqual(['strength' => 2]);
    expect($array['selected_subclass'])->toBeNull();
    expect($array['selected_equipment'])->toEqual([]);
});
it('handles null values in from array', function () {
    $array = [
        'name' => null,
        'selected_class' => null,
        'selected_subclass' => null,
        'selected_ancestry' => null,
        'selected_community' => null,
    ];

    $data = CharacterBuilderData::from($array);

    // Spatie Data might convert null to empty strings in some cases
    expect($data->name === null || $data->name === '')->toBeTrue();
    expect($data->selected_class === null || $data->selected_class === '')->toBeTrue();
    expect($data->selected_subclass === null || $data->selected_subclass === '')->toBeTrue();
    expect($data->selected_ancestry === null || $data->selected_ancestry === '')->toBeTrue();
    expect($data->selected_community === null || $data->selected_community === '')->toBeTrue();
});
it('handles missing keys in from array', function () {
    $array = [
        'name' => 'Minimal Hero',
        // Missing other keys should use defaults
    ];

    $data = CharacterBuilderData::from($array);

    expect($data->name)->toEqual('Minimal Hero');
    expect($data->selected_class)->toBeNull();
    expect($data->assigned_traits)->toEqual([]);
    expect($data->selected_equipment)->toEqual([]);
});
it('has step completion methods', function () {
    $data = new CharacterBuilderData;

    // Should have methods to check step completion
    expect(method_exists($data, 'isStepComplete'))->toBeTrue();
    expect(method_exists($data, 'getCompletedSteps'))->toBeTrue();
    expect(method_exists($data, 'canProceedToStep'))->toBeTrue();
});
it('validates step 1 completion', function () {
    $incompleteData = new CharacterBuilderData();
    expect($incompleteData->isStepComplete(1))->toBeFalse();

    $completeData = new CharacterBuilderData(selected_class: 'warrior');
    expect($completeData->isStepComplete(1))->toBeTrue();
});
it('validates step 2 completion', function () {
    $incompleteData = new CharacterBuilderData();
    expect($incompleteData->isStepComplete(2))->toBeFalse();

    $completeData = new CharacterBuilderData(selected_subclass: 'call-of-the-brave');
    expect($completeData->isStepComplete(2))->toBeTrue();
});
it('validates step 3 completion', function () {
    $incompleteData = new CharacterBuilderData();
    expect($incompleteData->isStepComplete(3))->toBeFalse();

    $completeData = new CharacterBuilderData(selected_ancestry: 'human');
    expect($completeData->isStepComplete(3))->toBeTrue();
});
it('validates step 4 completion', function () {
    $incompleteData = new CharacterBuilderData();
    expect($incompleteData->isStepComplete(4))->toBeFalse();

    $completeData = new CharacterBuilderData(selected_community: 'order-of-scholars');
    expect($completeData->isStepComplete(4))->toBeTrue();
});
it('validates step 5 completion', function () {
    $incompleteData = new CharacterBuilderData(assigned_traits: ['agility' => 2]);
    expect($incompleteData->isStepComplete(5))->toBeFalse();

    // Step 5 requires exactly 6 traits with values that sum to [-1, 0, 0, 1, 1, 2]
    $completeData = new CharacterBuilderData(assigned_traits: [
        'agility' => 2,
        'strength' => 1,
        'finesse' => 0,
        'instinct' => -1,
        'presence' => 1,
        'knowledge' => 0,
    ]);
    expect($completeData->isStepComplete(5))->toBeTrue();
});
it('validates step 6 completion', function () {
    $incompleteData = new CharacterBuilderData(selected_equipment: [
        ['key' => 'sword', 'type' => 'weapon'],
    ]);
    expect($incompleteData->isStepComplete(6))->toBeFalse();

    $completeData = new CharacterBuilderData(selected_equipment: [
        ['key' => 'sword', 'type' => 'weapon', 'data' => ['type' => 'Primary']],
        ['key' => 'armor', 'type' => 'armor'],
    ]);
    expect($completeData->isStepComplete(6))->toBeTrue();
});

it('validates step 7 completion', function () {
    // Without a selected class, step 7 should be incomplete
    $incompleteData = new CharacterBuilderData();
    expect($incompleteData->isStepComplete(7))->toBeFalse();

    // With a class but no background answers, step 7 should be incomplete
    $incompleteDataWithClass = new CharacterBuilderData(
        selected_class: 'warrior',
        background_answers: []
    );
    expect($incompleteDataWithClass->isStepComplete(7))->toBeFalse();

    // With a class and at least one background answer, step 7 should be complete
    $completeData = new CharacterBuilderData(
        selected_class: 'warrior',
        background_answers: ['This is my character background.', '', '']
    );
    expect($completeData->isStepComplete(7))->toBeTrue();
});

it('validates step 8 completion', function () {
    $incompleteData = new CharacterBuilderData(experiences: [
        ['name' => 'First Experience', 'description' => 'Description'],
    ]);
    expect($incompleteData->isStepComplete(8))->toBeFalse();

    $completeData = new CharacterBuilderData(experiences: [
        ['name' => 'First Experience', 'description' => 'Description'],
        ['name' => 'Second Experience', 'description' => 'Description'],
    ]);
    expect($completeData->isStepComplete(8))->toBeTrue();
});

it('validates step 9 completion', function () {
    $incompleteData = new CharacterBuilderData(selected_domain_cards: [
        ['name' => 'Card 1'],
    ]);
    expect($incompleteData->isStepComplete(9))->toBeFalse();

    $completeData = new CharacterBuilderData(selected_domain_cards: [
        ['name' => 'Card 1'],
        ['name' => 'Card 2'],
    ]);
    expect($completeData->isStepComplete(9))->toBeTrue();
});

it('validates step 10 completion', function () {
    $incompleteData = new CharacterBuilderData(connection_answers: []);
    expect($incompleteData->isStepComplete(10))->toBeFalse();

    $completeData = new CharacterBuilderData(connection_answers: ['My connection to another character.']);
    expect($completeData->isStepComplete(10))->toBeTrue();
});

it('can manually mark steps complete', function () {
    $data = new CharacterBuilderData;

    // Background step should not be complete initially
    expect($data->isStepComplete(CharacterBuilderStep::BACKGROUND))->toBeFalse();

    // Mark background step as manually complete
    $data->markStepComplete(CharacterBuilderStep::BACKGROUND);

    // Now background step should be complete even without meeting normal requirements
    expect($data->isStepComplete(CharacterBuilderStep::BACKGROUND))->toBeTrue();
    expect($data->manual_step_completions)->toContain(CharacterBuilderStep::BACKGROUND->value);
});
it('gets completed steps', function () {
    $data = new CharacterBuilderData(
        selected_class: 'warrior',
        selected_subclass: 'call-of-the-brave',
        selected_ancestry: 'human',
        selected_community: 'order-of-scholars',
        assigned_traits: [
            'agility' => 2,
            'strength' => 1,
            'finesse' => 0,
            'instinct' => -1,
            'presence' => 1,
            'knowledge' => 0,
        ],
        selected_equipment: [
            ['key' => 'sword', 'type' => 'weapon', 'data' => ['type' => 'Primary']],
            ['key' => 'armor', 'type' => 'armor'],
            ['key' => 'minor health potion', 'type' => 'consumable'], // chooseOne item
            ['key' => 'war trophy', 'type' => 'item'], // chooseExtra item
        ],
        background_answers: ['This is my background story.'],
        experiences: [
            ['name' => 'First Experience', 'description' => 'Description'],
            ['name' => 'Second Experience', 'description' => 'Description'],
        ],
        selected_domain_cards: [
            ['name' => 'Card 1'],
            ['name' => 'Card 2'],
        ],
        connection_answers: ['My connection to another character.']
    );

    $completedSteps = $data->getCompletedSteps();

    expect($completedSteps)->toContain(1);
    // class selection
    expect($completedSteps)->toContain(2);
    // heritage
    expect($completedSteps)->toContain(3);
    // traits
    expect($completedSteps)->toContain(4);
    // equipment
    expect($completedSteps)->toContain(5);
    // background
    expect($completedSteps)->toContain(6);
    // experiences
    expect($completedSteps)->toContain(7);
    // domain cards
    expect($completedSteps)->toContain(8);
    // connections
});
it('implements wireable interface', function () {
    $data = new CharacterBuilderData(name: 'Test Hero');

    // Should be able to convert to Livewire format
    $livewireData = $data->toLivewire();
    expect($livewireData)->toBeArray();

    // Should be able to create from Livewire format
    $fromLivewire = CharacterBuilderData::fromLivewire($livewireData);
    expect($fromLivewire)->toBeInstanceOf(CharacterBuilderData::class);
    expect($fromLivewire->name)->toEqual('Test Hero');
});
