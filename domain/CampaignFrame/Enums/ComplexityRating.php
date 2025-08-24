<?php

declare(strict_types=1);

namespace Domain\CampaignFrame\Enums;

enum ComplexityRating: int
{
    case SIMPLE = 1;
    case MODERATE = 2;
    case COMPLEX = 3;
    case VERY_COMPLEX = 4;

    public function label(): string
    {
        return match ($this) {
            self::SIMPLE => 'Simple',
            self::MODERATE => 'Moderate',
            self::COMPLEX => 'Complex',
            self::VERY_COMPLEX => 'Very Complex',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::SIMPLE => 'Minor deviations from core rules, easy to integrate',
            self::MODERATE => 'Some new mechanics, requires basic understanding',
            self::COMPLEX => 'Significant rule modifications, requires experience',
            self::VERY_COMPLEX => 'Major system changes, for advanced users only',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->map(fn (self $case) => [
                'value' => $case->value,
                'label' => $case->label(),
                'description' => $case->description(),
            ])
            ->toArray();
    }
}
