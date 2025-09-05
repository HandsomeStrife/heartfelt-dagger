<?php

declare(strict_types=1);

namespace Database\Factories;

use Domain\Character\Models\Character;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Character\Models\Character>
 */
class CharacterFactory extends Factory
{
    protected $model = Character::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $classes = ['bard', 'druid', 'guardian', 'ranger', 'rogue', 'seraph', 'sorcerer', 'warrior', 'wizard'];
        $ancestries = ['drakona', 'dwarf', 'elf', 'faerie', 'firbolg', 'galapa', 'giant', 'goblin', 'halfling', 'human', 'katari', 'orc', 'ribbet'];
        $communities = ['highborne', 'loreborne', 'orderborne', 'ridgeborne', 'seaborne', 'slyborne', 'underborne', 'wanderborne', 'wildborne'];

        $selectedClass = $this->faker->randomElement($classes);

        return [
            'character_key' => Character::generateUniqueKey(),
            'public_key' => Character::generateUniquePublicKey(),
            'user_id' => null, // Can be set with ->for(User::factory())
            'name' => $this->faker->firstName(),
            'pronouns' => $this->faker->randomElement(['he/him', 'she/her', 'they/them', 'xe/xem', null]),
            'class' => $selectedClass,
            'subclass' => $this->getRandomSubclass($selectedClass),
            'ancestry' => $this->faker->randomElement($ancestries),
            'community' => $this->faker->randomElement($communities),
            'level' => 1,
            'profile_image_path' => null,
            'character_data' => [
                'background' => [
                    'answers' => [
                        $this->faker->sentence(),
                        $this->faker->sentence(),
                        $this->faker->sentence(),
                    ],
                ],
                'connections' => [
                    $this->faker->sentence(),
                    $this->faker->sentence(),
                    $this->faker->sentence(),
                ],
                'creation_date' => now()->toISOString(),
                'builder_version' => '1.0',
            ],
            'is_public' => $this->faker->boolean(30), // 30% chance of being public
        ];
    }

    /**
     * Create a character for a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn () => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Create a public character.
     */
    public function public(): static
    {
        return $this->state(fn () => [
            'is_public' => true,
        ]);
    }

    /**
     * Create a character with a specific class.
     */
    public function withClass(string $class): static
    {
        return $this->state(fn () => [
            'class' => $class,
            'subclass' => $this->getRandomSubclass($class),
        ]);
    }

    /**
     * Create a complete character with all related data.
     */
    public function complete(): static
    {
        return $this->afterCreating(function (Character $character) {
            // Create traits
            $traits = ['agility', 'strength', 'finesse', 'instinct', 'presence', 'knowledge'];
            $values = [-1, 0, 0, 1, 1, 2];
            shuffle($values);

            foreach ($traits as $index => $trait) {
                $character->traits()->create([
                    'trait_name' => $trait,
                    'trait_value' => $values[$index],
                ]);
            }

            // Create equipment
            $character->equipment()->create([
                'equipment_type' => 'weapon',
                'equipment_key' => 'shortsword',
                'equipment_data' => ['name' => 'Shortsword', 'damage' => '1d6'],
                'is_equipped' => true,
            ]);

            $character->equipment()->create([
                'equipment_type' => 'armor',
                'equipment_key' => 'leather-armor',
                'equipment_data' => ['name' => 'Leather Armor', 'armor_score' => 2],
                'is_equipped' => true,
            ]);

            // Create experiences
            $character->experiences()->create([
                'experience_name' => 'Combat Training',
                'experience_description' => 'Extensive military training',
                'modifier' => 2,
            ]);

            $character->experiences()->create([
                'experience_name' => 'Wilderness Survival',
                'experience_description' => 'Years living in the wild',
                'modifier' => 2,
            ]);

            // Create domain cards
            $character->domainCards()->create([
                'domain' => 'blade',
                'ability_key' => 'strike',
                'ability_level' => 1,
            ]);

            $character->domainCards()->create([
                'domain' => 'valor',
                'ability_key' => 'defend',
                'ability_level' => 1,
            ]);
        });
    }

    private function getRandomSubclass(string $class): ?string
    {
        $subclasses = [
            'bard' => ['troubadour', 'wordsmith'],
            'druid' => ['greenkeeper', 'stormcaller'],
            'guardian' => ['aegis', 'invoker'],
            'ranger' => ['beastbound', 'outrider'],
            'rogue' => ['assassin', 'duelist'],
            'seraph' => ['empyrean', 'vindicator'],
            'sorcerer' => ['stormchaser', 'emberwild'],
            'warrior' => ['brute', 'gladiator'],
            'wizard' => ['arcanum', 'order'],
        ];

        $available = $subclasses[$class] ?? [];

        return empty($available) ? null : $this->faker->randomElement($available);
    }
}
