<?php

declare(strict_types=1);

namespace Domain\Room\Data;

use Domain\Room\Models\SessionMarker;
use Livewire\Wireable;
use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

class SessionMarkerData extends Data implements Wireable
{
    use WireableData;

    public function __construct(
        public ?int $id,
        public string $uuid,
        public ?string $identifier,
        public int $creator_id,
        public int $user_id,
        public int $room_id,
        public ?int $recording_id,
        public ?int $video_time,
        public ?int $stt_time,
        public ?string $created_at,
        public ?string $updated_at,
        public Lazy|string|null $creator_name = null,
        public Lazy|string|null $user_name = null,
        public Lazy|string|null $formatted_video_time = null,
        public Lazy|string|null $formatted_stt_time = null,
    ) {}

    public static function fromModel(SessionMarker $marker): self
    {
        return self::from([
            'id' => $marker->id,
            'uuid' => $marker->uuid,
            'identifier' => $marker->identifier,
            'creator_id' => $marker->creator_id,
            'user_id' => $marker->user_id,
            'room_id' => $marker->room_id,
            'recording_id' => $marker->recording_id,
            'video_time' => $marker->video_time,
            'stt_time' => $marker->stt_time,
            'created_at' => $marker->created_at?->toISOString(),
            'updated_at' => $marker->updated_at?->toISOString(),
            'creator_name' => Lazy::whenLoaded('creator', $marker, fn () => $marker->creator?->username),
            'user_name' => Lazy::whenLoaded('user', $marker, fn () => $marker->user?->username),
            'formatted_video_time' => Lazy::create(fn () => $marker->getFormattedVideoTime()),
            'formatted_stt_time' => Lazy::create(fn () => $marker->getFormattedSttTime()),
        ]);
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
}
