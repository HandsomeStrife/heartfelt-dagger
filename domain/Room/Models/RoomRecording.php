<?php

declare(strict_types=1);

namespace Domain\Room\Models;

use Domain\Room\Enums\RecordingStatus;
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
        'uploaded_parts' => 'array',
        'status' => RecordingStatus::class,
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
        return in_array($this->status, [
            RecordingStatus::Completed,
            RecordingStatus::Ready,
            RecordingStatus::Uploaded
        ]);
    }

    /**
     * Check if the recording is still being recorded
     */
    public function isRecording(): bool
    {
        return $this->status === RecordingStatus::Recording;
    }

    /**
     * Check if the recording is being finalized
     */
    public function isFinalizing(): bool
    {
        return $this->status === RecordingStatus::Finalizing;
    }

    /**
     * Check if the recording failed
     */
    public function hasFailed(): bool
    {
        return $this->status === RecordingStatus::Failed;
    }

    /**
     * Mark the recording as recording
     */
    public function markAsRecording(): void
    {
        $this->update(['status' => RecordingStatus::Recording]);
    }

    /**
     * Mark the recording as finalizing
     */
    public function markAsFinalizing(): void
    {
        $this->update(['status' => RecordingStatus::Finalizing]);
    }

    /**
     * Mark the recording as completed
     */
    public function markAsCompleted(): void
    {
        $this->update(['status' => RecordingStatus::Completed]);
    }

    /**
     * Mark the recording as failed
     */
    public function markAsFailed(): void
    {
        $this->update(['status' => RecordingStatus::Failed]);
    }

    /**
     * Add an uploaded part to the tracking
     */
    public function addUploadedPart(int $partNumber, string $etag): void
    {
        $parts = $this->uploaded_parts ?? [];
        $parts[] = [
            'PartNumber' => $partNumber,
            'ETag' => $etag,
        ];
        $this->update(['uploaded_parts' => $parts]);
    }

    /**
     * Get uploaded parts formatted for AWS CompleteMultipartUpload
     */
    public function getUploadedPartsForCompletion(): array
    {
        return $this->uploaded_parts ?? [];
    }

    /**
     * Check if this recording can be finalized
     */
    public function canBeFinalized(): bool
    {
        return $this->status->canBeFinalized() && !empty($this->multipart_upload_id);
    }

    /**
     * Scope query to ready recordings only
     */
    public function scopeReady($query)
    {
        return $query->whereIn('status', [
            RecordingStatus::Completed,
            RecordingStatus::Ready,
            RecordingStatus::Uploaded
        ]);
    }

    /**
     * Scope query to recordings that need finalization
     */
    public function scopeNeedingFinalization($query)
    {
        return $query->where('status', RecordingStatus::Recording)
            ->whereNotNull('multipart_upload_id')
            ->where('updated_at', '<', now()->subMinute());
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
