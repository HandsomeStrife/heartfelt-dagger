<?php

declare(strict_types=1);

namespace Domain\Campaign\Models;

use Domain\Campaign\Enums\CampaignStatus;
use Domain\CampaignFrame\Models\CampaignFrame;
use Domain\CampaignFrame\Models\CampaignFrameVisibility;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\CampaignFactory::new();
    }

    protected $fillable = [
        'name',
        'description',
        'creator_id',
        'campaign_frame_id',
        'invite_code',
        'campaign_code',
        'status',
        'fear_level',
        'countdown_trackers',
    ];

    protected $casts = [
        'status' => CampaignStatus::class,
        'fear_level' => 'integer',
        'countdown_trackers' => 'array',
    ];

    /**
     * Generate unique invite codes for campaigns
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Campaign $campaign) {
            if (empty($campaign->invite_code)) {
                $campaign->invite_code = static::generateUniqueInviteCode();
            }
            if (empty($campaign->campaign_code)) {
                $campaign->campaign_code = static::generateUniqueCampaignCode();
            }
        });
    }

    /**
     * Generate a unique 8-character invite code for campaign joining
     */
    public static function generateUniqueInviteCode(): string
    {
        do {
            $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $code = '';
            for ($i = 0; $i < 8; $i++) {
                $code .= $characters[random_int(0, strlen($characters) - 1)];
            }
        } while (static::where('invite_code', $code)->exists());

        return $code;
    }

    /**
     * Generate a unique 8-character campaign code for routing
     */
    public static function generateUniqueCampaignCode(): string
    {
        do {
            $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $code = '';
            for ($i = 0; $i < 8; $i++) {
                $code .= $characters[random_int(0, strlen($characters) - 1)];
            }
        } while (static::where('campaign_code', $code)->exists());

        return $code;
    }

    /**
     * Get the user who created this campaign
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Get the campaign frame for this campaign
     */
    public function campaignFrame(): BelongsTo
    {
        return $this->belongsTo(CampaignFrame::class, 'campaign_frame_id');
    }

    /**
     * Get all members of this campaign
     */
    public function members(): HasMany
    {
        return $this->hasMany(CampaignMember::class);
    }

    /**
     * Get campaign frame visibility settings for this campaign
     */
    public function campaignFrameVisibilities(): HasMany
    {
        return $this->hasMany(CampaignFrameVisibility::class);
    }

    /**
     * Get the campaign's invite URL
     */
    public function getInviteUrl(): string
    {
        return route('campaigns.invite', ['invite_code' => $this->invite_code]);
    }

    /**
     * Check if a user is the creator of this campaign
     */
    public function isCreator(User $user): bool
    {
        return $this->creator_id === $user->id;
    }

    /**
     * Check if a user is a member of this campaign
     */
    public function hasMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if a user can access this campaign (member or creator)
     */
    public function canUserAccess(User $user): bool
    {
        return $this->isCreator($user) || $this->hasMember($user);
    }

    /**
     * Get the member count for this campaign
     */
    public function getMemberCount(): int
    {
        return $this->members()->count();
    }

    /**
     * Scope for active campaigns
     */
    public function scopeActive($query)
    {
        return $query->where('status', CampaignStatus::ACTIVE);
    }

    /**
     * Scope for campaigns by creator
     */
    public function scopeByCreator($query, User $user)
    {
        return $query->where('creator_id', $user->id);
    }

    /**
     * Scope for finding campaign by invite code
     */
    public function scopeByInviteCode($query, string $inviteCode)
    {
        return $query->where('invite_code', $inviteCode);
    }

    /**
     * Scope for finding campaign by campaign code
     */
    public function scopeByCampaignCode($query, string $campaignCode)
    {
        return $query->where('campaign_code', $campaignCode);
    }

    /**
     * Check if a campaign frame section is visible to players
     */
    public function isCampaignFrameSectionVisible(string $section): bool
    {
        if (! $this->campaign_frame_id) {
            return false;
        }

        $visibility = $this->campaignFrameVisibilities()
            ->where('section_name', $section)
            ->first();

        if (! $visibility) {
            // Use default visibility if not set
            $defaults = CampaignFrameVisibility::getDefaultVisibilitySettings();

            return $defaults[$section] ?? false;
        }

        return $visibility->is_visible_to_players;
    }

    /**
     * Get all visible campaign frame sections for this campaign
     */
    public function getVisibleCampaignFrameSections(?User $user = null): array
    {
        if (! $this->campaign_frame_id) {
            return [];
        }

        // If user is the creator, they can see all sections
        if ($user && $this->isCreator($user)) {
            return array_keys(CampaignFrameVisibility::getAvailableSections());
        }

        $visibleSections = [];
        $allSections = array_keys(CampaignFrameVisibility::getAvailableSections());

        foreach ($allSections as $section) {
            if ($this->isCampaignFrameSectionVisible($section)) {
                $visibleSections[] = $section;
            }
        }

        return $visibleSections;
    }

    /**
     * Get the route key for the model
     */
    public function getRouteKeyName(): string
    {
        return 'campaign_code';
    }

    /**
     * Get the current fear level
     */
    public function getFearLevel(): int
    {
        return $this->fear_level ?? 0;
    }

    /**
     * Set the fear level (with bounds checking)
     */
    public function setFearLevel(int $level): void
    {
        $this->fear_level = max(0, min(255, $level));
    }

    /**
     * Increase fear level by the specified amount
     */
    public function increaseFear(int $amount = 1): int
    {
        $newLevel = $this->getFearLevel() + $amount;
        $this->setFearLevel($newLevel);

        return $this->fear_level;
    }

    /**
     * Decrease fear level by the specified amount
     */
    public function decreaseFear(int $amount = 1): int
    {
        $newLevel = $this->getFearLevel() - $amount;
        $this->setFearLevel($newLevel);

        return $this->fear_level;
    }

    /**
     * Get all countdown trackers
     */
    public function getCountdownTrackers(): array
    {
        return $this->countdown_trackers ?? [];
    }

    /**
     * Add or update a countdown tracker
     */
    public function setCountdownTracker(string $id, string $name, int $value): void
    {
        $trackers = $this->getCountdownTrackers();
        $trackers[$id] = [
            'name' => $name,
            'value' => max(0, $value),
            'updated_at' => now()->toISOString(),
        ];
        $this->countdown_trackers = $trackers;
    }

    /**
     * Remove a countdown tracker
     */
    public function removeCountdownTracker(string $id): void
    {
        $trackers = $this->getCountdownTrackers();
        unset($trackers[$id]);
        $this->countdown_trackers = $trackers;
    }

    /**
     * Get a specific countdown tracker
     */
    public function getCountdownTracker(string $id): ?array
    {
        $trackers = $this->getCountdownTrackers();

        return $trackers[$id] ?? null;
    }
}
