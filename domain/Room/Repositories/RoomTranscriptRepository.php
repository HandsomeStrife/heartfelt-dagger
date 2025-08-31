<?php

declare(strict_types=1);

namespace Domain\Room\Repositories;

use Domain\Room\Data\RoomTranscriptData;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomTranscript;
use Domain\User\Models\User;
use Illuminate\Support\Collection;

class RoomTranscriptRepository
{
    /**
     * Get all transcripts for a room
     *
     * @return Collection<RoomTranscriptData>
     */
    public function getByRoom(Room $room): Collection
    {
        return RoomTranscript::where('room_id', $room->id)
            ->byStartTime('asc')
            ->get()
            ->map(fn ($transcript) => RoomTranscriptData::from($transcript));
    }

    /**
     * Get transcripts for a room within a time range
     *
     * @return Collection<RoomTranscriptData>
     */
    public function getByRoomInTimeRange(Room $room, int $startMs, int $endMs): Collection
    {
        return RoomTranscript::where('room_id', $room->id)
            ->inTimeRange($startMs, $endMs)
            ->byStartTime('asc')
            ->get()
            ->map(fn ($transcript) => RoomTranscriptData::from($transcript));
    }

    /**
     * Get transcripts by a specific user in a room
     *
     * @return Collection<RoomTranscriptData>
     */
    public function getByUserInRoom(User $user, Room $room): Collection
    {
        return RoomTranscript::where('room_id', $room->id)
            ->byUser($user)
            ->byStartTime('asc')
            ->get()
            ->map(fn ($transcript) => RoomTranscriptData::from($transcript));
    }

    /**
     * Search transcripts in a room by text content
     *
     * @return Collection<RoomTranscriptData>
     */
    public function searchInRoom(Room $room, string $searchTerm): Collection
    {
        return RoomTranscript::where('room_id', $room->id)
            ->searchText($searchTerm)
            ->byStartTime('asc')
            ->get()
            ->map(fn ($transcript) => RoomTranscriptData::from($transcript));
    }

    /**
     * Get high confidence transcripts for a room
     *
     * @return Collection<RoomTranscriptData>
     */
    public function getHighConfidenceByRoom(Room $room, float $minConfidence = 0.8): Collection
    {
        return RoomTranscript::where('room_id', $room->id)
            ->withMinConfidence($minConfidence)
            ->byStartTime('asc')
            ->get()
            ->map(fn ($transcript) => RoomTranscriptData::from($transcript));
    }

    /**
     * Get recent transcripts for a room (last 50)
     *
     * @return Collection<RoomTranscriptData>
     */
    public function getRecentByRoom(Room $room, int $limit = 50): Collection
    {
        return RoomTranscript::where('room_id', $room->id)
            ->byStartTime('desc')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values()
            ->map(fn ($transcript) => RoomTranscriptData::from($transcript));
    }
}
