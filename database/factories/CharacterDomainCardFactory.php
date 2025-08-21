<?php

declare(strict_types=1);

namespace Database\Factories;

use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterDomainCard;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Character\Models\CharacterDomainCard>
 */
class CharacterDomainCardFactory extends Factory
{
    protected $model = CharacterDomainCard::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $domains = ['blade', 'bone', 'codex', 'grace', 'midnight', 'arcana', 'splendor', 'sage', 'valor'];
        $abilities = ['strike', 'defend', 'heal', 'blast', 'shield', 'enchant'];

        return [
            'character_id' => Character::factory(),
            'domain' => $this->faker->randomElement($domains),
            'ability_key' => $this->faker->randomElement($abilities),
            'ability_level' => $this->faker->numberBetween(1, 3),
        ];
    }
}
