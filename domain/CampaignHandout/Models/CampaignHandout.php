<?php

declare(strict_types=1);

namespace Domain\CampaignHandout\Models;

use Domain\Campaign\Models\Campaign;
use Domain\CampaignHandout\Enums\HandoutAccessLevel;
use Domain\CampaignHandout\Enums\HandoutFileType;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class CampaignHandout extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\CampaignHandoutFactory::new();
    }

    protected $guarded = [];

    protected $casts = [
        'access_level' => HandoutAccessLevel::class,
        'file_type' => HandoutFileType::class,
        'metadata' => 'array',
        'is_visible_in_sidebar' => 'boolean',
        'is_published' => 'boolean',
        'file_size' => 'integer',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function authorizedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'campaign_handout_access', 'campaign_handout_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * Check if a user can view this handout
     */
    public function canBeViewedBy(?User $user): bool
    {
        // Not published handouts can only be viewed by creator
        if (! $this->is_published && $user?->id !== $this->creator_id) {
            return false;
        }

        // Creator can always view
        if ($user && $user->id === $this->creator_id) {
            return true;
        }

        // Campaign creator (GM) can always view
        if ($user && $user->id === $this->campaign->creator_id) {
            return true;
        }

        return match ($this->access_level) {
            HandoutAccessLevel::GM_ONLY => false,
            HandoutAccessLevel::ALL_PLAYERS => $this->userIsCampaignMember($user),
            HandoutAccessLevel::SPECIFIC_PLAYERS => $this->userHasSpecificAccess($user),
        };
    }

    /**
     * Check if user is a campaign member
     */
    private function userIsCampaignMember(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->campaign->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if user has specific access granted
     */
    private function userHasSpecificAccess(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->authorizedUsers()->where('user_id', $user->id)->exists();
    }

    /**
     * Get the full URL for this handout file
     */
    public function getFileUrl(): string
    {
        // Check if S3 is configured
        if (config('filesystems.disks.s3.key') && config('filesystems.disks.s3.secret')) {
            return Storage::disk('s3')->url($this->file_path);
        }

        return Storage::disk('public')->url($this->file_path);
    }

    /**
     * Get a formatted file size string
     */
    public function getFormattedFileSize(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check if this handout is an image that can be previewed
     */
    public function isPreviewableImage(): bool
    {
        return $this->file_type === HandoutFileType::IMAGE;
    }

    /**
     * Check if this handout is a PDF
     */
    public function isPdf(): bool
    {
        return $this->file_type === HandoutFileType::PDF;
    }

    /**
     * Check if this handout can be previewed in the browser
     */
    public function isPreviewable(): bool
    {
        return $this->file_type->isPreviewable();
    }

    /**
     * Get image dimensions if available
     */
    public function getImageDimensions(): ?array
    {
        if ($this->file_type === HandoutFileType::IMAGE && isset($this->metadata['width'], $this->metadata['height'])) {
            return [
                'width' => $this->metadata['width'],
                'height' => $this->metadata['height'],
            ];
        }

        return null;
    }
}
