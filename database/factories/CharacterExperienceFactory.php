<?php

declare(strict_types=1);

namespace Database\Factories;

use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterExperience;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Character\Models\CharacterExperience>
 */
class CharacterExperienceFactory extends Factory
{
    protected $model = CharacterExperience::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $experiences = [
            'Combat Training', 'Wilderness Survival', 'Noble Etiquette',
            'Street Fighting', 'Academic Research', 'Merchant Trading',
        ];

        return [
            'character_id' => Character::factory(),
            'experience_name' => $this->faker->randomElement($experiences),
            'experience_description' => $this->faker->sentence(),
            'modifier' => 2,
        ];
    }
}
