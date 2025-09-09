<?php

declare(strict_types=1);

namespace Domain\Room\Models;

use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionMarker extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\SessionMarkerFactory::new();
    }

    protected $casts = [
        'video_time' => 'integer',
        'stt_time' => 'integer',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function recording(): BelongsTo
    {
        return $this->belongsTo(RoomRecording::class, 'recording_id');
    }

    /**
     * Get the video time in a human-readable format (MM:SS)
     */
    public function getFormattedVideoTime(): ?string
    {
        if ($this->video_time === null) {
            return null;
        }

        $minutes = floor($this->video_time / 60);
        $seconds = $this->video_time % 60;

        return sprintf('%d:%02d', $minutes, $seconds);
    }

    /**
     * Get the STT time in a human-readable format (MM:SS)
     */
    public function getFormattedSttTime(): ?string
    {
        if ($this->stt_time === null) {
            return null;
        }

        $minutes = floor($this->stt_time / 60);
        $seconds = $this->stt_time % 60;

        return sprintf('%d:%02d', $minutes, $seconds);
    }

    /**
     * Get the display name for this marker
     */
    public function getDisplayName(): string
    {
        return $this->identifier ?: 'Marker';
    }

    /**
     * Check if this marker has video timing information
     */
    public function hasVideoTime(): bool
    {
        return $this->video_time !== null;
    }

    /**
     * Check if this marker has STT timing information
     */
    public function hasSttTime(): bool
    {
        return $this->stt_time !== null;
    }

    /**
     * Scope to get markers for a specific room
     */
    public function scopeForRoom($query, int $roomId)
    {
        return $query->where('room_id', $roomId);
    }

    /**
     * Scope to get markers created by a specific user
     */
    public function scopeByCreator($query, int $creatorId)
    {
        return $query->where('creator_id', $creatorId);
    }

    /**
     * Scope to get markers with the same UUID
     */
    public function scopeByUuid($query, string $uuid)
    {
        return $query->where('uuid', $uuid);
    }
}
