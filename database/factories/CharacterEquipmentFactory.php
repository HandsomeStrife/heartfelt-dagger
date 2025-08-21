<?php

declare(strict_types=1);

namespace Database\Factories;

use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterEquipment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Character\Models\CharacterEquipment>
 */
class CharacterEquipmentFactory extends Factory
{
    protected $model = CharacterEquipment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['weapon', 'armor', 'item', 'consumable'];
        $keys = ['shortsword', 'leather-armor', 'torch', 'health-potion'];

        return [
            'character_id' => Character::factory(),
            'equipment_type' => $this->faker->randomElement($types),
            'equipment_key' => $this->faker->randomElement($keys),
            'equipment_data' => ['name' => $this->faker->words(2, true)],
            'is_equipped' => true,
        ];
    }
}
