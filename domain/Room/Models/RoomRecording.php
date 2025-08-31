<?php

declare(strict_types=1);

namespace Domain\Room\Models;

use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomRecording extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'size_bytes' => 'integer',
        'started_at_ms' => 'integer',
        'ended_at_ms' => 'integer',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if this is a Wasabi recording
     */
    public function isWasabi(): bool
    {
        return $this->provider === 'wasabi';
    }

    /**
     * Check if this is a Google Drive recording
     */
    public function isGoogleDrive(): bool
    {
        return $this->provider === 'google_drive';
    }

    /**
     * Get the recording duration in milliseconds
     */
    public function getDurationMs(): int
    {
        return $this->ended_at_ms - $this->started_at_ms;
    }

    /**
     * Get the recording duration in seconds
     */
    public function getDurationSeconds(): float
    {
        return $this->getDurationMs() / 1000;
    }

    /**
     * Get the recording duration in a human-readable format
     */
    public function getFormattedDuration(): string
    {
        $seconds = $this->getDurationSeconds();
        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;

        if ($minutes > 0) {
            return sprintf('%d:%02d', $minutes, $remainingSeconds);
        }

        return sprintf('0:%02d', $remainingSeconds);
    }

    /**
     * Get the file size in a human-readable format
     */
    public function getFormattedSize(): string
    {
        $bytes = $this->size_bytes;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get the started timestamp as a carbon instance
     */
    public function getStartedAt(): \Carbon\Carbon
    {
        return \Carbon\Carbon::createFromTimestampMs($this->started_at_ms);
    }

    /**
     * Get the ended timestamp as a carbon instance
     */
    public function getEndedAt(): \Carbon\Carbon
    {
        return \Carbon\Carbon::createFromTimestampMs($this->ended_at_ms);
    }

    /**
     * Check if the recording is ready for viewing/download
     */
    public function isReady(): bool
    {
        return $this->status === 'ready' || $this->status === 'uploaded';
    }

    /**
     * Check if the recording is still processing
     */
    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    /**
     * Check if the recording failed
     */
    public function hasFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Mark the recording as ready
     */
    public function markAsReady(): void
    {
        $this->update(['status' => 'ready']);
    }

    /**
     * Mark the recording as processing
     */
    public function markAsProcessing(): void
    {
        $this->update(['status' => 'processing']);
    }

    /**
     * Mark the recording as failed
     */
    public function markAsFailed(): void
    {
        $this->update(['status' => 'failed']);
    }

    /**
     * Scope query to ready recordings only
     */
    public function scopeReady($query)
    {
        return $query->whereIn('status', ['ready', 'uploaded']);
    }

    /**
     * Scope query to a specific provider
     */
    public function scopeProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * Scope query ordered by recording start time
     */
    public function scopeByStartTime($query, string $direction = 'desc')
    {
        return $query->orderBy('started_at_ms', $direction);
    }

    protected static function newFactory()
    {
        return \Database\Factories\RoomRecordingFactory::new();
    }
}
