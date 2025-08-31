<?php

namespace Database\Factories;

use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterAdvancement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Character\Models\CharacterAdvancement>
 */
class CharacterAdvancementFactory extends Factory
{
    protected $model = CharacterAdvancement::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tier = $this->faker->numberBetween(1, 4);
        $advancement_number = $this->faker->numberBetween(1, 2);
        
        $advancement_types = [
            'trait_bonus',
            'hit_point',
            'stress',
            'experience_bonus',
            'domain_card',
            'evasion',
            'proficiency',
        ];

        if ($tier >= 3) {
            $advancement_types[] = 'subclass';
            $advancement_types[] = 'multiclass';
        }

        $advancement_type = $this->faker->randomElement($advancement_types);

        return [
            'character_id' => Character::factory(),
            'tier' => $tier,
            'advancement_number' => $advancement_number,
            'advancement_type' => $advancement_type,
            'advancement_data' => $this->getAdvancementData($advancement_type),
            'description' => $this->getDescription($advancement_type),
        ];
    }

    private function getAdvancementData(string $type): array
    {
        return match($type) {
            'trait_bonus' => [
                'traits' => $this->faker->randomElements(['agility', 'strength', 'finesse', 'instinct', 'presence', 'knowledge'], 2),
                'bonus' => 1,
            ],
            'hit_point' => [
                'bonus' => 1,
            ],
            'stress' => [
                'bonus' => 1,
            ],
            'experience_bonus' => [
                'experiences' => $this->faker->randomElements(['combat', 'social', 'exploration'], 2),
                'bonus' => 1,
            ],
            'domain_card' => [
                'domain' => $this->faker->randomElement(['arcana', 'blade', 'bone', 'codex', 'grace', 'midnight', 'sage', 'splendor', 'valor']),
                'ability_key' => $this->faker->word,
                'max_level' => $this->faker->numberBetween(1, 4),
            ],
            'evasion' => [
                'bonus' => 1,
            ],
            'proficiency' => [
                'bonus' => 1,
            ],
            'subclass' => [
                'subclass' => $this->faker->word,
            ],
            'multiclass' => [
                'class' => $this->faker->randomElement(['warrior', 'wizard', 'rogue', 'ranger']),
            ],
            default => [],
        };
    }

    private function getDescription(string $type): string
    {
        return match($type) {
            'trait_bonus' => 'Gain a +1 bonus to two unmarked character traits and mark them.',
            'hit_point' => 'Permanently gain one Hit Point slot.',
            'stress' => 'Permanently gain one Stress slot.',
            'experience_bonus' => 'Permanently gain a +1 bonus to two Experiences.',
            'domain_card' => 'Choose an additional domain card of your level or lower from a domain you have access to.',
            'evasion' => 'Permanently gain a +1 bonus to your Evasion.',
            'proficiency' => 'Increase your Proficiency by +1.',
            'subclass' => 'Take an upgraded subclass card. Then cross out the multiclass option for this tier.',
            'multiclass' => 'Multiclass: Choose an additional class for your character.',
            default => 'Unknown advancement',
        };
    }

    public function traitBonus(array $traits = ['agility', 'strength']): static
    {
        return $this->state([
            'advancement_type' => 'trait_bonus',
            'advancement_data' => [
                'traits' => $traits,
                'bonus' => 1,
            ],
            'description' => 'Gain a +1 bonus to ' . implode(' and ', $traits) . ' and mark them.',
        ]);
    }

    public function hitPoint(): static
    {
        return $this->state([
            'advancement_type' => 'hit_point',
            'advancement_data' => ['bonus' => 1],
            'description' => 'Permanently gain one Hit Point slot.',
        ]);
    }

    public function evasion(): static
    {
        return $this->state([
            'advancement_type' => 'evasion',
            'advancement_data' => ['bonus' => 1],
            'description' => 'Permanently gain a +1 bonus to your Evasion.',
        ]);
    }

    public function multiclass(string $class = 'warrior'): static
    {
        return $this->state([
            'tier' => $this->faker->numberBetween(3, 4), // Multiclass only available at tier 3+
            'advancement_type' => 'multiclass',
            'advancement_data' => ['class' => $class],
            'description' => "Multiclass: Choose {$class} as an additional class for your character.",
        ]);
    }
}