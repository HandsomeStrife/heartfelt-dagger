<?php

declare(strict_types=1);

use Domain\Character\Data\CharacterBackgroundData;
use Domain\Character\Data\CharacterData;
use Domain\Character\Data\CharacterEquipmentItemData;
use Domain\Character\Data\CharacterExperienceData;
use Domain\Character\Data\CharacterStatsData;
use Domain\Character\Data\CharacterTraitsData;
use Domain\Character\Enums\EquipmentType;

it('tests CharacterData Wireable serialization with collections', function () {
    // Create collections for the character data
    $experiences = collect([
        new CharacterExperienceData(
            name: 'Another one',
            description: '',
            modifier: 2,
            category: 'General'
        ),
    ]);

    $equipment = collect([
        new CharacterEquipmentItemData(
            id: 1,
            character_id: 12,
            equipment_type: EquipmentType::WEAPON->value,
            equipment_key: 'staff',
            equipment_data: ['name' => 'Staff', 'tier' => 1],
            is_equipped: true,
        ),
    ]);

    $characterData = new CharacterData(
        id: 12,
        character_key: '5XFO9OPAHS',
        user_id: null,
        name: 'Unnamed Character',
        class: 'wizard',
        subclass: 'school of knowledge',
        ancestry: 'drakona',
        community: 'loreborne',
        level: 1,
        profile_image_path: null,
        stats: new CharacterStatsData(10, 5, 2, 6, 7, 12, 0),
        traits: new CharacterTraitsData(-1, 0, 0, 1, 1, 2),
        equipment: $equipment,
        experiences: $experiences,
        domain_cards: collect([]),
        background: new CharacterBackgroundData([], null, null, null, null),
        connections: collect([]),
        is_public: false,
        created_at: now(),
        updated_at: now(),
        pronouns: null,
    );

    // Step 1: Serialize to Livewire format
    $serialized = $characterData->toLivewire();

    // Debug: Let's see what the serialized experiences look like
    dump('Original experiences structure:', $characterData->experiences->toArray());
    dump('Serialized structure:', $serialized);

    // Step 2: Deserialize back from Livewire format
    $deserialized = CharacterData::fromLivewire($serialized);

    // Debug: Let's see what the deserialized experiences look like
    dump('Deserialized experiences structure:', $deserialized->experiences->toArray());

    // The test: experiences should maintain the same structure
    expect($deserialized->experiences)
        ->toBeInstanceOf(\Illuminate\Support\Collection::class)
        ->and($deserialized->experiences->count())
        ->toBe(1)
        ->and($deserialized->experiences->first())
        ->toBeInstanceOf(CharacterExperienceData::class)
        ->and($deserialized->experiences->first()->name)
        ->toBe('Another one');

    // Verify it round-trips correctly
    expect($deserialized->experiences->first()->toArray())
        ->toBe($characterData->experiences->first()->toArray());
});

it('tests individual experience DTO serialization', function () {
    $experienceData = new CharacterExperienceData(
        name: 'Test Experience',
        description: 'Test Description',
        modifier: 2,
        category: 'General'
    );

    // Test that toArray works correctly
    $asArray = $experienceData->toArray();
    dump('Experience toArray:', $asArray);

    expect($asArray)->toHaveKey('name');
    expect($asArray['name'])->toBe('Test Experience');
    expect($asArray['modifier'])->toBe(2);
});
