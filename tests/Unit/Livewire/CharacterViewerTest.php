<?php

declare(strict_types=1);

use App\Livewire\CharacterViewer;

test('getWeaponFeatureText returns string features directly', function () {
    $viewer = new CharacterViewer;

    $text = $viewer->getWeaponFeatureText([
        'feature' => 'Reliable: +1 to attack rolls',
    ]);

    expect($text)->toBe('Reliable: +1 to attack rolls');
});

test('getWeaponFeatureText extracts description from associative feature array', function () {
    $viewer = new CharacterViewer;

    $text = $viewer->getWeaponFeatureText([
        'feature' => [
            'name' => 'Reliable',
            'description' => '+1 to attack rolls',
        ],
    ]);

    expect($text)->toBe('+1 to attack rolls');
});

test('getWeaponFeatureText concatenates list-style feature arrays', function () {
    $viewer = new CharacterViewer;

    $text = $viewer->getWeaponFeatureText([
        'feature' => [
            'Reliable',
            ['name' => 'Hooked', 'description' => 'Pull the target into Melee range.'],
        ],
    ]);

    expect($text)->toContain('Reliable')
        ->toContain('Pull the target into Melee range.');
});

test('getOrganizedEquipment normalizes singular equipment types', function () {
    $viewer = new CharacterViewer;

    // Create a CharacterData with equipment collection
    $equipment = collect([
        new \Domain\Character\Data\CharacterEquipmentItemData(
            id: 1,
            character_id: 123,
            equipment_type: \Domain\Character\Enums\EquipmentType::WEAPON->value,
            equipment_key: 'shortsword',
            equipment_data: ['name' => 'Shortsword', 'type' => 'Primary'],
            is_equipped: true,
        ),
        new \Domain\Character\Data\CharacterEquipmentItemData(
            id: 2,
            character_id: 123,
            equipment_type: \Domain\Character\Enums\EquipmentType::ARMOR->value,
            equipment_key: 'leather-armor',
            equipment_data: ['name' => 'Leather Armor'],
            is_equipped: true,
        ),
        new \Domain\Character\Data\CharacterEquipmentItemData(
            id: 3,
            character_id: 123,
            equipment_type: \Domain\Character\Enums\EquipmentType::ITEM->value,
            equipment_key: 'rope',
            equipment_data: ['name' => '50 feet of rope'],
            is_equipped: false,
        ),
        new \Domain\Character\Data\CharacterEquipmentItemData(
            id: 4,
            character_id: 123,
            equipment_type: \Domain\Character\Enums\EquipmentType::CONSUMABLE->value,
            equipment_key: 'minor-health-potion',
            equipment_data: ['name' => 'Minor Health Potion'],
            is_equipped: false,
        ),
    ]);

    $viewer->character = new \Domain\Character\Data\CharacterData(
        id: 123,
        character_key: 'TEST123',
        user_id: null,
        name: 'Test Character',
        class: 'warrior',
        pronouns: null,
        subclass: 'gladiator',
        ancestry: 'human',
        community: 'wanderborne',
        level: 1,
        profile_image_path: null,
        stats: new \Domain\Character\Data\CharacterStatsData(10, 5, 2, 6, 7, 12, 0),
        traits: new \Domain\Character\Data\CharacterTraitsData(0, 1, 0, 1, 0, 0),
        equipment: $equipment,
        experiences: collect([]),
        domain_cards: collect([]),
        background: new \Domain\Character\Data\CharacterBackgroundData([], null, null, null, null),
        connections: collect([]),
        is_public: false,
        created_at: now(),
        updated_at: now(),
    );

    $organized = $viewer->getOrganizedEquipment();

    expect($organized['weapons'])->toHaveCount(1)
        ->and($organized['armor'])->toHaveCount(1)
        ->and($organized['items'])->toHaveCount(1)
        ->and($organized['consumables'])->toHaveCount(1);
});
