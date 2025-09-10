<?php

declare(strict_types=1);

namespace Database\Factories;

use Domain\CampaignFrame\Enums\ComplexityRating;
use Domain\CampaignFrame\Models\CampaignFrame;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\CampaignFrame\Models\CampaignFrame>
 */
class CampaignFrameFactory extends Factory
{
    protected $model = CampaignFrame::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'complexity_rating' => $this->faker->randomElement(ComplexityRating::cases())->value,
            'is_public' => $this->faker->boolean(30), // 30% chance of being public
            'creator_id' => User::factory(),
            'pitch' => [
                $this->faker->sentence(),
                $this->faker->sentence(),
            ],
            'touchstones' => [
                $this->faker->sentence(),
                $this->faker->sentence(),
            ],
            'tone' => [
                $this->faker->word(),
                $this->faker->word(),
            ],
            'themes' => [
                $this->faker->words(2, true),
                $this->faker->words(2, true),
            ],
            'background_overview' => $this->faker->paragraph(),
            'setting_guidance' => [
                'ancestries' => $this->faker->words(3),
                'communities' => $this->faker->words(3),
                'classes' => $this->faker->words(3),
            ],
            'player_principles' => [
                $this->faker->sentence(),
                $this->faker->sentence(),
            ],
            'gm_principles' => [
                $this->faker->sentence(),
            ],
            'community_guidance' => [
                $this->faker->words(3),
            ],
            'ancestry_guidance' => [
                $this->faker->words(3),
            ],
            'class_guidance' => [
                $this->faker->words(3),
            ],
            'campaign_mechanics' => [
                'name' => $this->faker->words(2, true),
                'description' => $this->faker->paragraph(),
            ],
            'setting_distinctions' => [
                $this->faker->words(2, true),
                $this->faker->words(2, true),
            ],
            'inciting_incident' => $this->faker->paragraph(),
            'special_mechanics' => [
                'name' => $this->faker->words(2, true),
                'description' => $this->faker->paragraph(),
            ],
            'session_zero_questions' => [
                $this->faker->sentence().'?',
                $this->faker->sentence().'?',
                $this->faker->sentence().'?',
            ],
        ];
    }

    /**
     * Make the campaign frame public
     */
    public function public(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
        ]);
    }

    /**
     * Make the campaign frame private
     */
    public function private(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => false,
        ]);
    }

    /**
     * Set a specific complexity rating
     */
    public function withComplexity(ComplexityRating $complexity): Factory
    {
        return $this->state(fn (array $attributes) => [
            'complexity_rating' => $complexity->value,
        ]);
    }
}
