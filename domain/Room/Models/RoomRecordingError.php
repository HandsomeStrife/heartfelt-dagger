<?php

declare(strict_types=1);

namespace Domain\Room\Models;

use Domain\Room\Enums\RecordingErrorType;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomRecordingError extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'error_type' => RecordingErrorType::class,
        'error_context' => 'array',
        'occurred_at' => 'datetime',
        'resolved_at' => 'datetime',
        'resolved' => 'boolean',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recording(): BelongsTo
    {
        return $this->belongsTo(RoomRecording::class);
    }

    /**
     * Mark this error as resolved
     */
    public function markAsResolved(?string $resolutionNotes = null): void
    {
        $this->update([
            'resolved' => true,
            'resolved_at' => now(),
            'resolution_notes' => $resolutionNotes,
        ]);
    }

    /**
     * Check if this error is unresolved
     */
    public function isUnresolved(): bool
    {
        return !$this->resolved;
    }

    /**
     * Get a human-readable error summary
     */
    public function getSummary(): string
    {
        return $this->error_type->getDescription();
    }
}
