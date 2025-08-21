<?php

declare(strict_types=1);

namespace Database\Factories;

use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterTrait;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Character\Models\CharacterTrait>
 */
class CharacterTraitFactory extends Factory
{
    protected $model = CharacterTrait::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $traits = ['agility', 'strength', 'finesse', 'instinct', 'presence', 'knowledge'];
        $values = [-1, 0, 1, 2];

        return [
            'character_id' => Character::factory(),
            'trait_name' => $this->faker->randomElement($traits),
            'trait_value' => $this->faker->randomElement($values),
        ];
    }
}
