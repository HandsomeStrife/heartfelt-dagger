<?php

use App\Livewire\CharacterLevelUp;
use Domain\Character\Models\Character;

use function Pest\Livewire\livewire;

describe('Character Level Up SRD Compliance', function () {

    test('every level up requires domain card selection as step 4', function () {
        // Test levels 1-9 (can level up to 2-10)
        foreach (range(1, 9) as $level) {
            $character = Character::factory()->create(['level' => $level]);

            livewire(CharacterLevelUp::class, [
                'public_key' => $character->public_key,
                'character_key' => $character->character_key,
            ])
                ->assertSee('Also Applied Automatically') // The automatic benefits section
                ->assertSee('Acquire a new domain card at your level or lower'); // Step 4 requirement
        }
    });

    test('level 2 tier achievements include experience creation AND domain card selection', function () {
        $character = Character::factory()->create(['level' => 1]);

        $component = livewire(CharacterLevelUp::class, [
            'public_key' => $character->public_key,
            'character_key' => $character->character_key,
        ]);

        // Should have BOTH experience creation AND domain card selection
        $component
            ->assertSee('Create Your New Experience') // Tier achievement experience
            ->assertSee('Select Your Domain Card'); // Tier achievement domain card
    });

    test('level 5 tier achievements should include experience creation interface', function () {
        $character = Character::factory()->create(['level' => 4]);

        livewire(CharacterLevelUp::class, [
            'public_key' => $character->public_key,
            'character_key' => $character->character_key,
        ])
            ->assertSee('Level 5 Benefits (Tier 3 Entry)')
            ->assertSee('Gain a new Experience at +2 modifier')
            ->assertSee('Create Your New Experience'); // Should have experience creation interface
    });

    test('level 8 tier achievements should include experience creation interface', function () {
        $character = Character::factory()->create(['level' => 7]);

        livewire(CharacterLevelUp::class, [
            'public_key' => $character->public_key,
            'character_key' => $character->character_key,
        ])
            ->assertSee('Level 8 Benefits (Tier 4 Entry)')
            ->assertSee('Gain a new Experience at +2 modifier')
            ->assertSee('Create Your New Experience'); // Should have experience creation interface
    });

    test('non-tier levels (3,4,6,7,9,10) still require domain card selection', function () {
        $nonTierLevels = [2, 3, 5, 6, 8, 9]; // Going from level X to X+1

        foreach ($nonTierLevels as $level) {
            $character = Character::factory()->create(['level' => $level]);

            livewire(CharacterLevelUp::class, [
                'public_key' => $character->public_key,
                'character_key' => $character->character_key,
            ])
                ->assertSee('Acquire a new domain card at your level or lower'); // Should always be present
        }
    });

    test('domain card max level equals character level + 1', function () {
        foreach (range(1, 9) as $level) {
            $character = Character::factory()->create(['level' => $level]);

            $component = livewire(CharacterLevelUp::class, [
                'public_key' => $character->public_key,
                'character_key' => $character->character_key,
            ]);

            $expectedMaxLevel = $level + 1;
            $cards = $component->call('getAvailableDomainCards', $expectedMaxLevel);

            // Verify domain cards respect level limits
            foreach ($cards as $card) {
                expect($card['level'])->toBeLessThanOrEqual($expectedMaxLevel);
            }
        }
    });

    test('tier progression follows SRD structure', function () {
        // Level 1 = Tier 1, Level 2-4 = Tier 2, Level 5-7 = Tier 3, Level 8-10 = Tier 4
        $tierMap = [
            1 => 1, 2 => 2, 3 => 2, 4 => 2,
            5 => 3, 6 => 3, 7 => 3,
            8 => 4, 9 => 4, 10 => 4,
        ];

        foreach ($tierMap as $level => $expectedTier) {
            if ($level === 10) {
                continue;
            } // Can't level up from 10

            $character = Character::factory()->create(['level' => $level]);

            $component = livewire(CharacterLevelUp::class, [
                'public_key' => $character->public_key,
                'character_key' => $character->character_key,
            ]);

            $currentTier = $component->get('current_tier');
            expect($currentTier)->toBe($expectedTier, "Level $level should be tier $expectedTier");
        }
    });

    test('automatic benefits are always present', function () {
        $character = Character::factory()->create(['level' => 3]); // Any non-tier level

        livewire(CharacterLevelUp::class, [
            'public_key' => $character->public_key,
            'character_key' => $character->character_key,
        ])
            ->assertSee('Also Applied Automatically')
            ->assertSee('All damage thresholds increase by +1') // Step 3 from SRD
            ->assertSee('Acquire a new domain card at your level or lower'); // Step 4 from SRD
    });

    test('advancement selection allows multiple tier options per SRD', function () {
        $character = Character::factory()->create(['level' => 5]); // Tier 3

        $component = livewire(CharacterLevelUp::class, [
            'public_key' => $character->public_key,
            'character_key' => $character->character_key,
        ]);

        $tierOptions = $component->get('tier_options');

        // Should be able to select from tier 3 or lower (as per image description)
        expect($tierOptions['description'] ?? '')->toContain('or any from the previous tier');
    });

    test('multiple slot advancement options can be selected twice', function () {
        $character = Character::factory()->create(['level' => 2]);

        $component = livewire(CharacterLevelUp::class, [
            'public_key' => $character->public_key,
            'character_key' => $character->character_key,
        ]);

        $tierOptions = $component->get('tier_options');

        // Find an option with multiple selections available
        $multiSlotOption = null;
        foreach ($tierOptions['options'] ?? [] as $index => $option) {
            if (($option['maxSelections'] ?? 1) > 1) {
                $multiSlotOption = $index;
                break;
            }
        }

        if ($multiSlotOption !== null) {
            // Should be able to select the same advancement twice
            $component
                ->call('selectAdvancement', $multiSlotOption, 'first')
                ->call('selectAdvancement', $multiSlotOption, 'second');

            expect($component->get('first_advancement'))->toBe($multiSlotOption);
            expect($component->get('second_advancement'))->toBe($multiSlotOption);
        }
    });
});
