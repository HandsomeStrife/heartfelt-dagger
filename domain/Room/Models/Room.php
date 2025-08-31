<?php

declare(strict_types=1);

namespace Domain\Room\Models;

use Domain\Campaign\Models\Campaign;
use Domain\Room\Enums\RoomStatus;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;

class Room extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'status' => RoomStatus::class,
        'guest_count' => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Room $room) {
            if (empty($room->invite_code)) {
                $room->invite_code = static::generateUniqueInviteCode();
            }
            if (empty($room->viewer_code)) {
                $room->viewer_code = static::generateUniqueViewerCode();
            }
        });
    }

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

    public static function generateUniqueViewerCode(): string
    {
        do {
            // Use a longer, more secure viewer code with mixed case and special chars
            $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            $code = '';
            for ($i = 0; $i < 12; $i++) {
                $code .= $characters[random_int(0, strlen($characters) - 1)];
            }
        } while (static::where('viewer_code', $code)->exists());

        return $code;
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(RoomParticipant::class);
    }

    public function activeParticipants(): HasMany
    {
        return $this->hasMany(RoomParticipant::class)->whereNull('left_at');
    }

    public function recordingSettings(): HasOne
    {
        return $this->hasOne(RoomRecordingSettings::class);
    }

    public function recordings(): HasMany
    {
        return $this->hasMany(RoomRecording::class);
    }

    public function transcripts(): HasMany
    {
        return $this->hasMany(RoomTranscript::class);
    }

    /**
     * Get the room's invite URL
     */
    public function getInviteUrl(): string
    {
        return route('rooms.invite', ['invite_code' => $this->invite_code]);
    }

    /**
     * Get the room's viewer URL
     */
    public function getViewerUrl(): string
    {
        return route('rooms.viewer', ['viewer_code' => $this->viewer_code]);
    }

    /**
     * Check if a user is the creator of this room
     */
    public function isCreator(?User $user): bool
    {
        return $user && $this->creator_id === $user->id;
    }

    /**
     * Check if a user is currently participating in this room
     */
    public function hasActiveParticipant(?User $user): bool
    {
        return $user && $this->activeParticipants()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if a user can access this room (campaign member or creator)
     */
    public function canUserAccess(?User $user): bool
    {
        // If room is not linked to a campaign, anyone can access
        if (!$this->campaign_id) {
            return true;
        }

        // Campaign rooms require authentication
        if (!$user) {
            return false;
        }

        // Room creator can always access
        if ($this->isCreator($user)) {
            return true;
        }

        // Check if user can access the campaign
        return $this->campaign->canUserAccess($user);
    }

    /**
     * Get the current number of active participants
     */
    public function getActiveParticipantCount(): int
    {
        return $this->activeParticipants()->count();
    }

    /**
     * Check if the room is at capacity
     * Capacity = creator + guest_count
     */
    public function isAtCapacity(): bool
    {
        return $this->getActiveParticipantCount() >= ($this->guest_count + 1);
    }

    /**
     * Get the total capacity of the room (creator + guests)
     */
    public function getTotalCapacity(): int
    {
        return $this->guest_count + 1;
    }

    /**
     * Scope a query to only include rooms by a specific creator
     */
    public function scopeByCreator(Builder $query, User $user): Builder
    {
        return $query->where('creator_id', $user->id);
    }

    /**
     * Scope a query to only include rooms by invite code
     */
    public function scopeByInviteCode(Builder $query, string $inviteCode): Builder
    {
        return $query->where('invite_code', $inviteCode);
    }

    /**
     * Scope a query to only include rooms by viewer code
     */
    public function scopeByViewerCode(Builder $query, string $viewerCode): Builder
    {
        return $query->where('viewer_code', $viewerCode);
    }

    /**
     * Scope a query to only include active rooms
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', RoomStatus::Active);
    }

    protected static function newFactory()
    {
        return \Database\Factories\RoomFactory::new();
    }
}
