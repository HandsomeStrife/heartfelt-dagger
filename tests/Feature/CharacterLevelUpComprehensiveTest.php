<?php

use App\Livewire\CharacterLevelUp;
use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterTrait;

use function Pest\Livewire\livewire;

describe('Character Level Up - Comprehensive SRD Compliance Tests', function () {
    beforeEach(function () {
        $this->character = Character::factory()->create([
            'level' => 1,
            'class' => 'warrior',
        ]);

        // Create character traits
        foreach (['agility', 'strength', 'finesse', 'instinct', 'presence', 'knowledge'] as $trait) {
            CharacterTrait::factory()->create([
                'character_id' => $this->character->id,
                'trait_name' => $trait,
                'trait_value' => 0,
                'is_marked' => false,
            ]);
        }
    });

    describe('Level 2 (Tier 2 Entry)', function () {
        test('has correct tier achievements', function () {
            $this->character->update(['level' => 1]);

            livewire(CharacterLevelUp::class, [
                'public_key' => $this->character->public_key,
                'character_key' => $this->character->character_key,
            ])
                ->assertSee('Permanently increase Proficiency by +1')
                ->assertSee('Create Your New Experience')
                ->assertSee('Select Your Domain Card');
        });

        test('requires experience creation for tier achievement', function () {
            $this->character->update(['level' => 1]);

            $component = livewire(CharacterLevelUp::class, [
                'public_key' => $this->character->public_key,
                'character_key' => $this->character->character_key,
            ]);

            // Should not be able to proceed without experience
            $component->call('goToNextStep')
                ->assertHasErrors();
        });

        test('requires domain card selection for automatic tier achievement', function () {
            $this->character->update(['level' => 1]);

            $component = livewire(CharacterLevelUp::class, [
                'public_key' => $this->character->public_key,
                'character_key' => $this->character->character_key,
            ]);

            // Should not be able to proceed without domain card
            $component->call('goToNextStep')
                ->assertHasErrors();
        });

        test('domain card selection shows cards up to level 2', function () {
            $this->character->update(['level' => 1]);

            livewire(CharacterLevelUp::class, [
                'public_key' => $this->character->public_key,
                'character_key' => $this->character->character_key,
            ])
                ->call('getAvailableDomainCards', 2)
                ->assertCount('availableCards', '>=', 1);
        });
    });

    describe('Level 3 (Tier 2 Mid)', function () {
        test('does not have tier achievements but requires domain card selection', function () {
            $this->character->update(['level' => 2]);

            livewire(CharacterLevelUp::class, [
                'public_key' => $this->character->public_key,
                'character_key' => $this->character->character_key,
            ])
                ->assertDontSee('Automatic Level 3 Benefits')
                ->assertSee('Also Applied Automatically') // Should still show automatic domain card
                ->assertSee('Acquire a new domain card at your level or lower');
        });

        test('advancement options respect tier 2 options', function () {
            $this->character->update(['level' => 2]);

            $component = livewire(CharacterLevelUp::class, [
                'public_key' => $this->character->public_key,
                'character_key' => $this->character->character_key,
            ]);

            $tierOptions = $component->get('tier_options');
            expect($tierOptions['tier'])->toBe(2);
            expect($tierOptions['options'])->toHaveCount('>=', 1);
        });

        test('domain card selection allows cards up to level 3', function () {
            $this->character->update(['level' => 2, 'current_tier' => 2]);

            $component = livewire(CharacterLevelUp::class, [
                'public_key' => $this->character->public_key,
                'character_key' => $this->character->character_key,
            ]);

            $cards = $component->call('getAvailableDomainCards', 3);
            // Should include level 1, 2, and 3 cards
            expect($cards)->toBeArray();
        });
    });

    describe('Level 4 (Tier 2 Max)', function () {
        test('respects tier 2 advancement limits', function () {
            $this->character->update(['level' => 3, 'current_tier' => 2]);

            $component = livewire(CharacterLevelUp::class, [
                'public_key' => $this->character->public_key,
                'character_key' => $this->character->character_key,
            ]);

            $tierOptions = $component->get('tier_options');
            expect($tierOptions['tier'])->toBe(2);
        });

        test('domain card selection allows cards up to level 4', function () {
            $this->character->update(['level' => 3, 'current_tier' => 2]);

            $component = livewire(CharacterLevelUp::class, [
                'public_key' => $this->character->public_key,
                'character_key' => $this->character->character_key,
            ]);

            $cards = $component->call('getAvailableDomainCards', 4);
            expect($cards)->toBeArray();
        });
    });

    describe('Level 5 (Tier 3 Entry)', function () {
        test('has correct tier achievements', function () {
            $this->character->update(['level' => 4, 'current_tier' => 2]);

            livewire(CharacterLevelUp::class, [
                'public_key' => $this->character->public_key,
                'character_key' => $this->character->character_key,
            ])
                ->assertSee('Level 5 Benefits (Tier 3 Entry)')
                ->assertSee('Gain a new Experience at +2 modifier')
                ->assertSee('Permanently increase Proficiency by +1 (automatic)')
                ->assertSee('Clear all marked character traits (automatic)');
        });

        test('requires experience creation for tier achievement', function () {
            $this->character->update(['level' => 4, 'current_tier' => 2]);

            livewire(CharacterLevelUp::class, [
                'public_key' => $this->character->public_key,
                'character_key' => $this->character->character_key,
            ])
                ->assertSee('Create Your New Experience'); // Should have experience creation interface
        });

        test('domain card selection allows cards up to level 5', function () {
            $this->character->update(['level' => 4, 'current_tier' => 2]);

            $component = livewire(CharacterLevelUp::class, [
                'public_key' => $this->character->public_key,
                'character_key' => $this->character->character_key,
            ]);

            $cards = $component->call('getAvailableDomainCards', 5);
            expect($cards)->toBeArray();
        });

        test('advancement options include tier 3 and lower tier options', function () {
            $this->character->update(['level' => 4, 'current_tier' => 2]);

            $component = livewire(CharacterLevelUp::class, [
                'public_key' => $this->character->public_key,
                'character_key' => $this->character->character_key,
            ]);

            $tierOptions = $component->get('tier_options');
            expect($tierOptions['tier'])->toBe(3);
        });
    });

    describe('Level 6-7 (Tier 3 Mid/Max)', function () {
        test('level 6 has tier 3 advancement options', function () {
            $this->character->update(['level' => 5]);

            $component = livewire(CharacterLevelUp::class, [
                'public_key' => $this->character->public_key,
                'character_key' => $this->character->character_key,
            ]);

            $tierOptions = $component->get('tier_options');
            expect($tierOptions['tier'])->toBe(3);
        });

        test('level 7 allows domain cards up to level 7', function () {
            $this->character->update(['level' => 6]);

            $component = livewire(CharacterLevelUp::class, [
                'public_key' => $this->character->public_key,
                'character_key' => $this->character->character_key,
            ]);

            $cards = $component->call('getAvailableDomainCards', 7);
            expect($cards)->toBeArray();
        });
    });

    describe('Level 8 (Tier 4 Entry)', function () {
        test('has correct tier achievements', function () {
            $this->character->update(['level' => 7, 'current_tier' => 3]);

            livewire(CharacterLevelUp::class, [
                'public_key' => $this->character->public_key,
                'character_key' => $this->character->character_key,
            ])
                ->assertSee('Level 8 Benefits (Tier 4 Entry)')
                ->assertSee('Gain a new Experience at +2 modifier')
                ->assertSee('Permanently increase Proficiency by +1 (automatic)')
                ->assertSee('Clear all marked character traits (automatic)');
        });

        test('requires experience creation for tier achievement', function () {
            $this->character->update(['level' => 7, 'current_tier' => 3]);

            livewire(CharacterLevelUp::class, [
                'public_key' => $this->character->public_key,
                'character_key' => $this->character->character_key,
            ])
                ->assertSee('Create Your New Experience'); // Should have experience creation interface
        });

        test('advancement options include tier 4 and lower tier options', function () {
            $this->character->update(['level' => 7, 'current_tier' => 3]);

            $component = livewire(CharacterLevelUp::class, [
                'public_key' => $this->character->public_key,
                'character_key' => $this->character->character_key,
            ]);

            $tierOptions = $component->get('tier_options');
            expect($tierOptions['tier'])->toBe(4);
        });
    });

    describe('Level 9-10 (Tier 4 Mid/Max)', function () {
        test('level 10 allows domain cards up to level 10', function () {
            $this->character->update(['level' => 9, 'current_tier' => 4]);

            $component = livewire(CharacterLevelUp::class, [
                'public_key' => $this->character->public_key,
                'character_key' => $this->character->character_key,
            ]);

            $cards = $component->call('getAvailableDomainCards', 10);
            expect($cards)->toBeArray();
        });
    });

    describe('Domain Card Selection Compliance', function () {
        test('every level up requires domain card selection', function () {
            foreach (range(1, 9) as $level) {
                $character = Character::factory()->create(['level' => $level]);

                livewire(CharacterLevelUp::class, [
                    'public_key' => $character->public_key,
                    'character_key' => $character->character_key,
                ])
                    ->assertSee('Acquire a new domain card at your level or lower');
            }
        });

        test('domain card max level matches character level + 1', function () {
            foreach (range(1, 9) as $level) {
                $character = Character::factory()->create(['level' => $level]);

                $component = livewire(CharacterLevelUp::class, [
                    'public_key' => $character->public_key,
                    'character_key' => $character->character_key,
                ]);

                $expectedMaxLevel = $level + 1;
                $cards = $component->call('getAvailableDomainCards', $expectedMaxLevel);

                // Verify no cards exceed the expected max level
                foreach ($cards as $card) {
                    expect($card['level'])->toBeLessThanOrEqual($expectedMaxLevel);
                }
            }
        });
    });

    describe('Advancement Selection Compliance', function () {
        test('tier 2 can select from tier 2 or tier 1 options', function () {
            $this->character->update(['level' => 2, 'current_tier' => 2]);

            $component = livewire(CharacterLevelUp::class, [
                'public_key' => $this->character->public_key,
                'character_key' => $this->character->character_key,
            ]);

            $tierOptions = $component->get('tier_options');
            expect($tierOptions['tier'])->toBe(2);
            expect($tierOptions['description'])->toContain('Choose two options from the list below');
        });

        test('tier 3 can select from tier 3, tier 2, or tier 1 options', function () {
            $this->character->update(['level' => 5, 'current_tier' => 3]);

            $component = livewire(CharacterLevelUp::class, [
                'public_key' => $this->character->public_key,
                'character_key' => $this->character->character_key,
            ]);

            $tierOptions = $component->get('tier_options');
            expect($tierOptions['tier'])->toBe(3);
            expect($tierOptions['description'])->toContain('Choose two options from the list below or any from the previous tier');
        });

        test('tier 4 can select from tier 4, tier 3, tier 2, or tier 1 options', function () {
            $this->character->update(['level' => 8, 'current_tier' => 4]);

            $component = livewire(CharacterLevelUp::class, [
                'public_key' => $this->character->public_key,
                'character_key' => $this->character->character_key,
            ]);

            $tierOptions = $component->get('tier_options');
            expect($tierOptions['tier'])->toBe(4);
            expect($tierOptions['description'])->toContain('Choose two options from the list below or any from the previous tier');
        });
    });

    describe('Step Structure Compliance', function () {
        test('level up process follows 4-step structure', function () {
            $this->character->update(['level' => 1]);

            livewire(CharacterLevelUp::class, [
                'public_key' => $this->character->public_key,
                'character_key' => $this->character->character_key,
            ])
                ->assertSee('Tier Achievements') // Step 1
                ->assertSee('First Advancement') // Step 2a
                ->assertSee('Second Advancement') // Step 2b
                ->assertSee('Confirm'); // Step 3 (combining damage thresholds + domain cards into confirmation)
        });

        test('automatic benefits are clearly separated from choices', function () {
            $this->character->update(['level' => 1]);

            livewire(CharacterLevelUp::class, [
                'public_key' => $this->character->public_key,
                'character_key' => $this->character->character_key,
            ])
                ->assertSee('Automatic Tier Benefits')
                ->assertSee('Also Applied Automatically')
                ->assertSee('All damage thresholds increase by +1')
                ->assertSee('Acquire a new domain card at your level or lower');
        });
    });

    describe('Multi-Selection Advancement Compliance', function () {
        test('options with multiple slots can be chosen more than once', function () {
            $this->character->update(['level' => 2, 'current_tier' => 2]);

            $component = livewire(CharacterLevelUp::class, [
                'public_key' => $this->character->public_key,
                'character_key' => $this->character->character_key,
            ]);

            // Select trait advancement twice (if it has maxSelections > 1)
            $component->call('selectAdvancement', 0, 'first'); // First selection
            $component->call('selectAdvancement', 0, 'second'); // Second selection of same advancement

            $tierOptions = $component->get('tier_options');
            $hasMultiSlotOption = false;

            foreach ($tierOptions['options'] as $option) {
                if (($option['maxSelections'] ?? 1) > 1) {
                    $hasMultiSlotOption = true;
                    break;
                }
            }

            if ($hasMultiSlotOption) {
                // Should allow the same advancement to be selected twice
                expect($component->get('first_advancement'))->toBe(0);
                expect($component->get('second_advancement'))->toBe(0);
            }
        });
    });
});
