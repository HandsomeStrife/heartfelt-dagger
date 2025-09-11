<?php

namespace Database\Factories;

use Domain\Campaign\Models\Campaign;
use Domain\CampaignHandout\Enums\HandoutAccessLevel;
use Domain\CampaignHandout\Enums\HandoutFileType;
use Domain\CampaignHandout\Models\CampaignHandout;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\CampaignHandout\Models\CampaignHandout>
 */
class CampaignHandoutFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = CampaignHandout::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fileType = $this->faker->randomElement(HandoutFileType::cases());
        $fileName = $this->faker->words(2, true) . '.' . $this->getExtensionForType($fileType);
        
        return [
            'campaign_id' => Campaign::factory(),
            'creator_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->optional()->paragraph(),
            'file_name' => $fileName,
            'original_file_name' => $fileName,
            'file_path' => 'campaign-handouts/test/' . $fileName,
            'file_type' => $fileType,
            'mime_type' => $this->getMimeTypeForType($fileType),
            'file_size' => $this->faker->numberBetween(1024, 5242880), // 1KB to 5MB
            'metadata' => $this->getMetadataForType($fileType),
            'access_level' => $this->faker->randomElement(HandoutAccessLevel::cases()),
            'is_visible_in_sidebar' => $this->faker->boolean(30), // 30% chance
            'display_order' => $this->faker->numberBetween(0, 100),
            'is_published' => $this->faker->boolean(90), // 90% chance
        ];
    }

    private function getExtensionForType(HandoutFileType $type): string
    {
        return match ($type) {
            HandoutFileType::IMAGE => $this->faker->randomElement(['jpg', 'png', 'gif', 'webp']),
            HandoutFileType::PDF => 'pdf',
            HandoutFileType::DOCUMENT => $this->faker->randomElement(['doc', 'docx', 'txt']),
            HandoutFileType::AUDIO => $this->faker->randomElement(['mp3', 'wav', 'ogg']),
            HandoutFileType::VIDEO => $this->faker->randomElement(['mp4', 'webm', 'avi']),
            HandoutFileType::OTHER => $this->faker->randomElement(['zip', 'txt', 'json']),
        };
    }

    private function getMimeTypeForType(HandoutFileType $type): string
    {
        return match ($type) {
            HandoutFileType::IMAGE => $this->faker->randomElement(['image/jpeg', 'image/png', 'image/gif', 'image/webp']),
            HandoutFileType::PDF => 'application/pdf',
            HandoutFileType::DOCUMENT => $this->faker->randomElement(['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain']),
            HandoutFileType::AUDIO => $this->faker->randomElement(['audio/mpeg', 'audio/wav', 'audio/ogg']),
            HandoutFileType::VIDEO => $this->faker->randomElement(['video/mp4', 'video/webm', 'video/x-msvideo']),
            HandoutFileType::OTHER => 'application/octet-stream',
        };
    }

    private function getMetadataForType(HandoutFileType $type): array
    {
        return match ($type) {
            HandoutFileType::IMAGE => [
                'width' => $this->faker->numberBetween(100, 2048),
                'height' => $this->faker->numberBetween(100, 2048),
            ],
            default => [],
        };
    }

    public function image(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_type' => HandoutFileType::IMAGE,
            'mime_type' => 'image/jpeg',
            'file_name' => 'test_image.jpg',
            'original_file_name' => 'test_image.jpg',
            'metadata' => [
                'width' => $this->faker->numberBetween(500, 2048),
                'height' => $this->faker->numberBetween(500, 2048),
            ],
        ]);
    }

    public function pdf(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_type' => HandoutFileType::PDF,
            'mime_type' => 'application/pdf',
            'file_name' => 'test_document.pdf',
            'original_file_name' => 'test_document.pdf',
            'metadata' => [],
        ]);
    }

    public function visibleInSidebar(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_visible_in_sidebar' => true,
        ]);
    }

    public function gmOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'access_level' => HandoutAccessLevel::GM_ONLY,
        ]);
    }

    public function allPlayers(): static
    {
        return $this->state(fn (array $attributes) => [
            'access_level' => HandoutAccessLevel::ALL_PLAYERS,
        ]);
    }

    public function specificPlayers(): static
    {
        return $this->state(fn (array $attributes) => [
            'access_level' => HandoutAccessLevel::SPECIFIC_PLAYERS,
        ]);
    }
}
