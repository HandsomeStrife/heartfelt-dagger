<?php

declare(strict_types=1);

namespace Domain\Room\Models;

use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomTranscript extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'started_at_ms' => 'integer',
        'ended_at_ms' => 'integer',
        'confidence' => 'float',
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
     * Get the transcript duration in milliseconds
     */
    public function getDurationMs(): int
    {
        return $this->ended_at_ms - $this->started_at_ms;
    }

    /**
     * Get the transcript duration in seconds
     */
    public function getDurationSeconds(): float
    {
        return $this->getDurationMs() / 1000;
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
     * Get a formatted timestamp for display
     */
    public function getFormattedTimestamp(): string
    {
        return $this->getStartedAt()->format('H:i:s');
    }

    /**
     * Get the confidence score as a percentage
     */
    public function getConfidencePercentage(): ?int
    {
        return $this->confidence ? (int) round($this->confidence * 100) : null;
    }

    /**
     * Check if this transcript has high confidence
     */
    public function hasHighConfidence(): bool
    {
        return $this->confidence && $this->confidence >= 0.8;
    }

    /**
     * Get the trimmed text content
     */
    public function getTrimmedText(int $maxLength = 100): string
    {
        if (strlen($this->text) <= $maxLength) {
            return $this->text;
        }

        return substr($this->text, 0, $maxLength).'...';
    }

    /**
     * Scope query ordered by transcript start time
     */
    public function scopeByStartTime($query, string $direction = 'asc')
    {
        return $query->orderBy('started_at_ms', $direction);
    }

    /**
     * Scope query to transcripts within a time range
     */
    public function scopeInTimeRange($query, int $startMs, int $endMs)
    {
        return $query->where('started_at_ms', '>=', $startMs)
            ->where('ended_at_ms', '<=', $endMs);
    }

    /**
     * Scope query to transcripts by user
     */
    public function scopeByUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    /**
     * Scope query to transcripts with minimum confidence
     */
    public function scopeWithMinConfidence($query, float $minConfidence)
    {
        return $query->where('confidence', '>=', $minConfidence);
    }

    /**
     * Scope query to search transcript text
     */
    public function scopeSearchText($query, string $searchTerm)
    {
        return $query->where('text', 'LIKE', '%'.$searchTerm.'%');
    }

    protected static function newFactory()
    {
        // We'll create this factory later
        return null; // \Database\Factories\RoomTranscriptFactory::new();
    }
}
