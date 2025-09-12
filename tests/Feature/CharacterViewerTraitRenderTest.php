<?php

declare(strict_types=1);

use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterAdvancement;
use Domain\Character\Enums\TraitName;
use Domain\User\Models\User;
use App\Livewire\CharacterViewer;
use Livewire\Livewire;

describe('Character Viewer Trait Render Test', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    });

    test('CharacterViewer renders correct weapon trait modifier in active weapons section', function () {
        // Create a druid character exactly like in the issue
        $character = Character::factory()->create([
            'user_id' => $this->user->id,
            'class' => 'druid',
            'subclass' => 'warden_of_renewal',
            'ancestry' => 'dwarf',
            'community' => 'loreborne',
            'name' => 'Sample Druid',
        ]);

        // Create traits matching the image
        $character->traits()->create(['trait_name' => 'agility', 'trait_value' => 1]);
        $character->traits()->create(['trait_name' => 'strength', 'trait_value' => 0]);
        $character->traits()->create(['trait_name' => 'finesse', 'trait_value' => 1]);
        $character->traits()->create(['trait_name' => 'instinct', 'trait_value' => 2]); // +2 like in the image
        $character->traits()->create(['trait_name' => 'presence', 'trait_value' => -1]);
        $character->traits()->create(['trait_name' => 'knowledge', 'trait_value' => 0]);

        // Create a shortstaff weapon that uses instinct
        $character->equipment()->create([
            'equipment_type' => 'weapon',
            'equipment_key' => 'shortstaff',
            'equipment_data' => [
                'name' => 'Shortstaff',
                'type' => 'Primary',
                'trait' => 'instinct',
                'range' => 'Close',
                'damage' => ['dice' => 'd6', 'modifier' => 0, 'type' => 'mag'],
                'burden' => 'One-Handed',
                'tier' => 1,
            ],
            'is_equipped' => true,
        ]);

        // Verify base functionality
        expect($character->getTraitValue(TraitName::INSTINCT))->toBe(2);
        expect($character->getEffectiveTraitValue(TraitName::INSTINCT))->toBe(2);

        // Test the CharacterViewer component using Livewire
        $component = Livewire::test(CharacterViewer::class, [
            'publicKey' => $character->public_key,
            'characterKey' => $character->character_key,
            'canEdit' => true,
        ]);

        // Verify the component loads correctly
        $component->assertSuccessful();

        // Check that trait values are correct
        $trait_values = $component->call('getFormattedTraitValues');
        expect($trait_values['instinct'])->toBe('+2');

        // Check render data
        $render_data = $component->render()->getData();
        expect($render_data['trait_values']['instinct'])->toBe('+2');

        // Check that the weapon attack modifier will display correctly
        $organized_equipment = $render_data['organized_equipment'];
        expect($organized_equipment['weapons'])->not->toBeEmpty();
        
        $primary = collect($organized_equipment['weapons'])->first(fn($w) => ($w['data']['type'] ?? 'Primary') === 'Primary');
        expect($primary)->not->toBeNull();
        expect($primary['data']['trait'])->toBe('instinct');
        
        // This is the exact template expression: $traitValues[$primary['data']['trait'] ?? 'strength'] ?? '+0'
        $template_expression = $render_data['trait_values'][$primary['data']['trait'] ?? 'strength'] ?? '+0';
        expect($template_expression)->toBe('+2'); // Should be +2, not +0

        // Test the actual view rendering contains the correct value
        $component->assertSeeTextInOrder(['INSTINCT', '+2'], false);
    });

    test('CharacterViewer with advancement bonuses shows correct weapon trait modifier', function () {
        // Create character with base instinct 0
        $character = Character::factory()->create([
            'user_id' => $this->user->id,
            'class' => 'druid',
            'name' => 'Advanced Druid',
        ]);

        $character->traits()->create(['trait_name' => 'agility', 'trait_value' => 1]);
        $character->traits()->create(['trait_name' => 'strength', 'trait_value' => 0]);
        $character->traits()->create(['trait_name' => 'finesse', 'trait_value' => 1]);
        $character->traits()->create(['trait_name' => 'instinct', 'trait_value' => 0]); // Base 0
        $character->traits()->create(['trait_name' => 'presence', 'trait_value' => -1]);
        $character->traits()->create(['trait_name' => 'knowledge', 'trait_value' => 0]);

        // Add advancement bonus for instinct +2
        CharacterAdvancement::factory()->create([
            'character_id' => $character->id,
            'tier' => 1,
            'advancement_number' => 1,
            'advancement_type' => 'trait_bonus',
            'advancement_data' => [
                'traits' => ['instinct'],
                'bonus' => 2,
            ],
        ]);

        // Create weapon
        $character->equipment()->create([
            'equipment_type' => 'weapon',
            'equipment_key' => 'shortstaff',
            'equipment_data' => [
                'name' => 'Shortstaff',
                'type' => 'Primary',
                'trait' => 'instinct',
                'range' => 'Close',
                'damage' => ['dice' => 'd6', 'modifier' => 0, 'type' => 'mag'],
            ],
            'is_equipped' => true,
        ]);

        // Verify advancement calculation
        expect($character->getTraitValue(TraitName::INSTINCT))->toBe(0); // Base
        expect($character->getEffectiveTraitValue(TraitName::INSTINCT))->toBe(2); // With advancement

        // Test CharacterViewer
        $component = Livewire::test(CharacterViewer::class, [
            'publicKey' => $character->public_key,
            'characterKey' => $character->character_key,
            'canEdit' => true,
        ]);

        $trait_values = $component->call('getFormattedTraitValues');
        expect($trait_values['instinct'])->toBe('+2');

        $render_data = $component->render()->getData();
        expect($render_data['trait_values']['instinct'])->toBe('+2');

        // Verify the weapon will show the correct modifier
        $primary = collect($render_data['organized_equipment']['weapons'])->first(fn($w) => ($w['data']['type'] ?? 'Primary') === 'Primary');
        $template_value = $render_data['trait_values'][$primary['data']['trait'] ?? 'strength'] ?? '+0';
        expect($template_value)->toBe('+2');
    });

    test('debug character viewer loading and trait calculation', function () {
        // Create a character and debug the entire flow
        $character = Character::factory()->create([
            'user_id' => $this->user->id,
            'class' => 'druid',
        ]);

        $character->traits()->create(['trait_name' => 'instinct', 'trait_value' => 2]);

        // Manually test CharacterViewer component
        $component = new CharacterViewer();
        $component->mount($character->public_key, $character->character_key, true);

        // Debug each step
        expect($component->character)->not->toBeNull();
        expect($component->character->name)->toBe($character->name);

        $trait_values = $component->getFormattedTraitValues();
        ray('Debug trait values:', $trait_values);
        
        expect($trait_values)->toHaveKey('instinct');
        expect($trait_values['instinct'])->toBe('+2');

        // This test ensures our fix is working correctly
        expect(true)->toBeTrue();
    });
});
