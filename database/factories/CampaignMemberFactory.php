<?php

declare(strict_types=1);

namespace Database\Factories;

use Domain\Campaign\Models\Campaign;
use Domain\Campaign\Models\CampaignMember;
use Domain\Character\Models\Character;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Campaign\Models\CampaignMember>
 */
class CampaignMemberFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = CampaignMember::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'campaign_id' => Campaign::factory(),
            'user_id' => User::factory(),
            'character_id' => Character::factory(),
            'joined_at' => now(),
        ];
    }

    /**
     * Indicate that the member joined without a character (empty slot).
     */
    public function withoutCharacter(): static
    {
        return $this->state(fn (array $attributes) => [
            'character_id' => null,
        ]);
    }

    /**
     * Create a member that joined at a specific time.
     */
    public function joinedAt($timestamp): static
    {
        return $this->state(fn (array $attributes) => [
            'joined_at' => $timestamp,
        ]);
    }
}
