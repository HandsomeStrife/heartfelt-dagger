<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Character\Models\CharacterNotes>
 */
class CharacterNotesFactory extends Factory
{
    protected $model = \Domain\Character\Models\CharacterNotes::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'character_id' => \Domain\Character\Models\Character::factory(),
            'user_id' => \Domain\User\Models\User::factory(),
            'notes' => $this->faker->paragraphs(2, true),
        ];
    }
}
