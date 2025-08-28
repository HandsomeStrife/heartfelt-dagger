<?php

namespace Database\Factories;

use Domain\Campaign\Models\Campaign;
use Domain\CampaignPage\Enums\PageAccessLevel;
use Domain\CampaignPage\Models\CampaignPage;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\CampaignPage\Models\CampaignPage>
 */
class CampaignPageFactory extends Factory
{
    protected $model = CampaignPage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'campaign_id' => Campaign::factory(),
            'parent_id' => null,
            'creator_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'content' => $this->faker->paragraphs(3, true),
            'category_tags' => $this->faker->randomElements(
                ['Lore', 'NPCs', 'Locations', 'Plot Hooks', 'Rules', 'Background'],
                $this->faker->numberBetween(0, 3)
            ),
            'access_level' => $this->faker->randomElement(PageAccessLevel::cases()),
            'display_order' => $this->faker->numberBetween(1, 10),
            'is_published' => true,
        ];
    }

    /**
     * Create a page that is a child of another page
     */
    public function childOf(CampaignPage $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'campaign_id' => $parent->campaign_id,
            'parent_id' => $parent->id,
        ]);
    }

    /**
     * Create a page with GM-only access
     */
    public function gmOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'access_level' => PageAccessLevel::GM_ONLY,
        ]);
    }

    /**
     * Create a page accessible to all players
     */
    public function allPlayers(): static
    {
        return $this->state(fn (array $attributes) => [
            'access_level' => PageAccessLevel::ALL_PLAYERS,
        ]);
    }

    /**
     * Create a page with specific player access
     */
    public function specificPlayers(): static
    {
        return $this->state(fn (array $attributes) => [
            'access_level' => PageAccessLevel::SPECIFIC_PLAYERS,
        ]);
    }

    /**
     * Create an unpublished draft page
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
        ]);
    }

    /**
     * Create a page with specific category tags
     */
    public function withCategories(array $categories): static
    {
        return $this->state(fn (array $attributes) => [
            'category_tags' => $categories,
        ]);
    }
}
