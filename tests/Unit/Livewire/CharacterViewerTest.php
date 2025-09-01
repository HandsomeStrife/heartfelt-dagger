<?php

declare(strict_types=1);

use App\Livewire\CharacterViewer;
use Domain\Character\Data\CharacterBuilderData;

test('getWeaponFeatureText returns string features directly', function () {
    $viewer = new CharacterViewer();

    $text = $viewer->getWeaponFeatureText([
        'feature' => 'Reliable: +1 to attack rolls',
    ]);

    expect($text)->toBe('Reliable: +1 to attack rolls');
});

test('getWeaponFeatureText extracts description from associative feature array', function () {
    $viewer = new CharacterViewer();

    $text = $viewer->getWeaponFeatureText([
        'feature' => [
            'name' => 'Reliable',
            'description' => '+1 to attack rolls',
        ],
    ]);

    expect($text)->toBe('+1 to attack rolls');
});

test('getWeaponFeatureText concatenates list-style feature arrays', function () {
    $viewer = new CharacterViewer();

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
    $viewer = new CharacterViewer();

    $viewer->character = new CharacterBuilderData(
        selected_equipment: [
            ['key' => 'shortsword', 'type' => 'weapon', 'data' => ['name' => 'Shortsword', 'type' => 'Primary']],
            ['key' => 'leather-armor', 'type' => 'armor', 'data' => ['name' => 'Leather Armor']],
            ['key' => 'rope', 'type' => 'item', 'data' => ['name' => '50 feet of rope']],
            ['key' => 'minor-health-potion', 'type' => 'consumable', 'data' => ['name' => 'Minor Health Potion']],
        ]
    );

    $organized = $viewer->getOrganizedEquipment();

    expect($organized['weapons'])->toHaveCount(1)
        ->and($organized['armor'])->toHaveCount(1)
        ->and($organized['items'])->toHaveCount(1)
        ->and($organized['consumables'])->toHaveCount(1);
});


