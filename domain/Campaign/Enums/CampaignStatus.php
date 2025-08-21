<?php

declare(strict_types=1);

namespace Domain\Campaign\Enums;

enum CampaignStatus: string
{
    case ACTIVE = 'active';
    case ARCHIVED = 'archived';
    case COMPLETED = 'completed';
    case PAUSED = 'paused';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::ARCHIVED => 'Archived',
            self::COMPLETED => 'Completed',
            self::PAUSED => 'Paused',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'emerald',
            self::ARCHIVED => 'slate',
            self::COMPLETED => 'blue',
            self::PAUSED => 'amber',
        };
    }
}
