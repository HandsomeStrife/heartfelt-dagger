<?php

declare(strict_types=1);

namespace Domain\CampaignFrame\Models;

use Domain\Campaign\Models\Campaign;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignFrameVisibility extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'section_name',
        'is_visible_to_players',
    ];

    protected $casts = [
        'is_visible_to_players' => 'boolean',
    ];

    /**
     * Get the campaign that owns this visibility setting
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Available campaign frame sections that can be controlled
     */
    public static function getAvailableSections(): array
    {
        return [
            'pitch' => 'Campaign Pitch',
            'touchstones' => 'Touchstones',
            'tone' => 'Tone',
            'themes' => 'Themes',
            'player_principles' => 'Player Principles',
            'gm_principles' => 'GM Principles',
            'background_overview' => 'Background Overview',
            'setting_guidance' => 'Setting Guidance',
            'setting_distinctions' => 'Setting Distinctions',
            'inciting_incident' => 'Inciting Incident',
            'special_mechanics' => 'Special Mechanics',
            'campaign_mechanics' => 'Campaign Mechanics',
            'session_zero_questions' => 'Session Zero Questions',
        ];
    }

    /**
     * Get default visibility settings for a campaign
     */
    public static function getDefaultVisibilitySettings(): array
    {
        return [
            'pitch' => true,
            'touchstones' => true,
            'tone' => true,
            'themes' => true,
            'player_principles' => true,
            'gm_principles' => false,
            'background_overview' => true,
            'setting_guidance' => false,
            'setting_distinctions' => false,
            'inciting_incident' => false,
            'special_mechanics' => false,
            'campaign_mechanics' => false,
            'session_zero_questions' => false,
        ];
    }
}
