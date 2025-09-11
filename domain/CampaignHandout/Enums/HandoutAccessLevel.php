<?php

declare(strict_types=1);

namespace Domain\CampaignHandout\Enums;

enum HandoutAccessLevel: string
{
    case GM_ONLY = 'gm_only';
    case ALL_PLAYERS = 'all_players';
    case SPECIFIC_PLAYERS = 'specific_players';

    public function label(): string
    {
        return match ($this) {
            self::GM_ONLY => 'GM Only',
            self::ALL_PLAYERS => 'All Players',
            self::SPECIFIC_PLAYERS => 'Specific Players',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::GM_ONLY => 'Only visible to the Game Master',
            self::ALL_PLAYERS => 'Visible to all campaign players',
            self::SPECIFIC_PLAYERS => 'Visible to selected players only',
        };
    }

    public function canBeViewedByAll(): bool
    {
        return $this === self::ALL_PLAYERS;
    }

    public function requiresSpecificAccess(): bool
    {
        return $this === self::SPECIFIC_PLAYERS;
    }
}
