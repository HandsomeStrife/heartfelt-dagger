<?php

declare(strict_types=1);

use Domain\Character\Models\Character;
use Domain\User\Models\User;
use App\Livewire\CharacterViewer;
use App\Livewire\RoomSidebar\PlayerSidebar;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomParticipant;
use Domain\Room\Data\RoomParticipantData;

describe('Weapon Trait Case Sensitivity Test', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    });

    test('weapon data now comes from JSON files with lowercase traits', function () {
        // Create character with traits (lowercase)
        $character = Character::factory()->create([
            'user_id' => $this->user->id,
            'class' => 'druid',
            'name' => 'Test Druid',
        ]);

        $character->traits()->create(['trait_name' => 'instinct', 'trait_value' => 2]);

        // Create weapon reference - the actual data now comes from JSON
        $character->equipment()->create([
            'equipment_type' => 'weapon',
            'equipment_key' => 'shortstaff', // This key exists in weapons.json
            'equipment_data' => [
                'name' => 'Shortstaff',
                'type' => 'Primary',
                'trait' => 'Instinct', // Old database data (capitalized) - should be overridden
                'range' => 'Close',
                'damage' => ['dice' => 'd8', 'modifier' => 1, 'type' => 'mag'],
            ],
            'is_equipped' => true,
        ]);

        // Test CharacterViewer
        $component = new CharacterViewer();
        $component->mount($character->public_key, $character->character_key, true);

        $trait_values = $component->getFormattedTraitValues();
        expect($trait_values['instinct'])->toBe('+2');

        $organized_equipment = $component->getOrganizedEquipment();
        $primary = collect($organized_equipment['weapons'])->first(fn($w) => ($w['data']['type'] ?? 'Primary') === 'Primary');
        
        // Now the trait should be lowercase because it comes from JSON
        expect($primary['data']['trait'])->toBe('instinct');
        
        // Template should work directly with lowercase trait
        $template_value = $trait_values[$primary['data']['trait'] ?? 'strength'] ?? '+0';
        expect($template_value)->toBe('+2');
    });

    test('PlayerSidebar gets fresh data from JSON files', function () {
        // Create character
        $character = Character::factory()->create([
            'user_id' => $this->user->id,
            'class' => 'druid',
        ]);

        $character->traits()->create(['trait_name' => 'instinct', 'trait_value' => 2]);

        // Create weapon reference - data comes from JSON now
        $character->equipment()->create([
            'equipment_type' => 'weapon',
            'equipment_key' => 'shortstaff', // This exists in weapons.json
            'equipment_data' => [
                'name' => 'Shortstaff',
                'type' => 'Primary',
                'trait' => 'Instinct', // Old capitalized data - should be overridden
                'range' => 'Close',
                'damage' => ['dice' => 'd8', 'modifier' => 1, 'type' => 'mag'],
            ],
            'is_equipped' => true,
        ]);

        // Create room and participant
        $room = Room::factory()->create(['creator_id' => $this->user->id]);
        $participant = RoomParticipant::factory()->create([
            'room_id' => $room->id,
            'user_id' => $this->user->id,
            'character_id' => $character->id,
        ]);

        $participantData = RoomParticipantData::from([
            'id' => $participant->id,
            'room_id' => $participant->room_id,
            'user_id' => $participant->user_id,
            'character_id' => $participant->character_id,
            'character_name' => $participant->character_name,
            'character_class' => $participant->character_class,
            'joined_at' => $participant->joined_at?->toDateTimeString(),
            'left_at' => $participant->left_at?->toDateTimeString(),
            'created_at' => $participant->created_at?->toDateTimeString(),
            'updated_at' => $participant->updated_at?->toDateTimeString(),
            'user' => $participant->user,
            'character' => $participant->character,
        ]);

        // Test PlayerSidebar
        $component = new PlayerSidebar();
        $component->mount($participantData, null, collect());

        $trait_values = $component->getFormattedTraitValues();
        expect($trait_values['instinct'])->toBe('+2');

        $organized_equipment = $component->getOrganizedEquipment();
        $primary = collect($organized_equipment['weapons'])->first(fn($w) => ($w['data']['type'] ?? 'Primary') === 'Primary');
        
        // Now trait should be lowercase from JSON
        expect($primary['data']['trait'])->toBe('instinct');
        
        // Template should work directly with lowercase trait
        $template_value = $trait_values[$primary['data']['trait'] ?? 'strength'] ?? '+0';
        expect($template_value)->toBe('+2');
    });

    test('fallback still works with unknown trait', function () {
        $character = Character::factory()->create(['user_id' => $this->user->id]);
        
        $character->traits()->create(['trait_name' => 'strength', 'trait_value' => 1]);

        // Create weapon with unknown trait
        $character->equipment()->create([
            'equipment_type' => 'weapon',
            'equipment_key' => 'test_weapon',
            'equipment_data' => [
                'name' => 'Test Weapon',
                'type' => 'Primary',
                'trait' => 'UnknownTrait', // This doesn't exist
            ],
            'is_equipped' => true,
        ]);

        $component = new CharacterViewer();
        $component->mount($character->public_key, $character->character_key, true);

        $trait_values = $component->getFormattedTraitValues();
        $organized_equipment = $component->getOrganizedEquipment();
        $primary = collect($organized_equipment['weapons'])->first(fn($w) => ($w['data']['type'] ?? 'Primary') === 'Primary');
        
        // Should fallback to '+0' for unknown trait
        $template_value = $trait_values[$primary['data']['trait'] ?? 'strength'] ?? '+0';
        expect($template_value)->toBe('+0');
    });
});
