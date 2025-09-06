<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Room\Models\RoomSessionNotes>
 */
class RoomSessionNotesFactory extends Factory
{
    protected $model = \Domain\Room\Models\RoomSessionNotes::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'room_id' => \Domain\Room\Models\Room::factory(),
            'user_id' => \Domain\User\Models\User::factory(),
            'notes' => $this->faker->paragraphs(3, true),
        ];
    }
}
