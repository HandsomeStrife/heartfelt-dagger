<?php

declare(strict_types=1);

namespace Domain\CampaignPage\Enums;

enum PageAccessLevel: string
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
            self::GM_ONLY => 'Only the campaign creator can view this page',
            self::ALL_PLAYERS => 'All campaign members can view this page',
            self::SPECIFIC_PLAYERS => 'Only selected players can view this page',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->toArray();
    }
}
