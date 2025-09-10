<?php

declare(strict_types=1);

namespace Domain\Character\Data;

use Domain\Character\Models\Character;
use Livewire\Wireable;
use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;

class CharacterExperiencesData extends Data implements Wireable
{
    use WireableData;

    public function __construct(
        public array $experiences,
    ) {}

    public static function fromModel(Character $character): self
    {
        $experiences = $character->experiences()->get()->map(function ($experience) {
            return [
                'name' => $experience->experience_name,
                'description' => $experience->experience_description,
                'modifier' => $experience->modifier,
                'category' => $experience->getCategory(),
            ];
        })->toArray();

        return new self(experiences: $experiences);
    }

    public static function fromBuilderData(array $experiencesData): self
    {
        return new self(experiences: $experiencesData);
    }

    public function hasMinimumExperiences(): bool
    {
        return count($this->experiences) >= 2;
    }

    public function getExperienceCount(): int
    {
        return count($this->experiences);
    }

    public function getExperienceNames(): array
    {
        return array_column($this->experiences, 'name');
    }

    public function getExperienceByName(string $name): ?array
    {
        foreach ($this->experiences as $experience) {
            if ($experience['name'] === $name) {
                return $experience;
            }
        }

        return null;
    }

    public function getExperiencesByCategory(string $category): array
    {
        return array_filter($this->experiences, function ($experience) use ($category) {
            return ($experience['category'] ?? 'General') === $category;
        });
    }

    public function getCategories(): array
    {
        $categories = array_unique(array_column($this->experiences, 'category'));

        return array_filter($categories);
    }

    public function getTotalModifier(): int
    {
        return array_sum(array_column($this->experiences, 'modifier'));
    }

    public function getPositiveExperiences(): array
    {
        return array_filter($this->experiences, function ($experience) {
            return ($experience['modifier'] ?? 0) > 0;
        });
    }

    public function getNegativeExperiences(): array
    {
        return array_filter($this->experiences, function ($experience) {
            return ($experience['modifier'] ?? 0) < 0;
        });
    }

    public function getFormattedExperiences(): array
    {
        return array_map(function ($experience) {
            $modifier = $experience['modifier'] ?? 2;
            $modifierString = $modifier > 0 ? "+{$modifier}" : (string) $modifier;

            return [
                'name' => $experience['name'],
                'description' => $experience['description'] ?? '',
                'modifier' => $modifier,
                'modifierString' => $modifierString,
                'category' => $experience['category'] ?? 'General',
                'hasDescription' => ! empty($experience['description']),
            ];
        }, $this->experiences);
    }

    public function addExperience(string $name, string $description = '', int $modifier = 2): self
    {
        $experiences = $this->experiences;
        $experiences[] = [
            'name' => $name,
            'description' => $description,
            'modifier' => $modifier,
            'category' => $this->determineCategory($name),
        ];

        return new self(experiences: $experiences);
    }

    public function removeExperience(string $name): self
    {
        $experiences = array_filter($this->experiences, function ($experience) use ($name) {
            return $experience['name'] !== $name;
        });

        return new self(experiences: array_values($experiences));
    }

    private function determineCategory(string $name): string
    {
        $name = strtolower($name);

        if (str_contains($name, 'combat') || str_contains($name, 'fighting') || str_contains($name, 'warrior')) {
            return 'Combat';
        }

        if (str_contains($name, 'magic') || str_contains($name, 'arcane') || str_contains($name, 'spell')) {
            return 'Magic';
        }

        if (str_contains($name, 'social') || str_contains($name, 'noble') || str_contains($name, 'court')) {
            return 'Social';
        }

        if (str_contains($name, 'craft') || str_contains($name, 'smith') || str_contains($name, 'trade')) {
            return 'Crafting';
        }

        if (str_contains($name, 'nature') || str_contains($name, 'wilderness') || str_contains($name, 'survival')) {
            return 'Nature';
        }

        if (str_contains($name, 'lore') || str_contains($name, 'knowledge') || str_contains($name, 'scholar')) {
            return 'Knowledge';
        }

        return 'General';
    }
}
