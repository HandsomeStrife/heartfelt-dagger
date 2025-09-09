<?php

declare(strict_types=1);

namespace Domain\Room\Repositories;

use Domain\Room\Data\SessionMarkerData;
use Domain\Room\Models\SessionMarker;
use Illuminate\Support\Collection;

class SessionMarkerRepository
{
    /**
     * Get all session markers for a room
     *
     * @return Collection<SessionMarkerData>
     */
    public function getForRoom(int $roomId): Collection
    {
        return SessionMarker::with(['creator', 'user', 'recording'])
            ->forRoom($roomId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (SessionMarker $marker) => SessionMarkerData::fromModel($marker));
    }

    /**
     * Get session markers for a specific user in a room
     *
     * @return Collection<SessionMarkerData>
     */
    public function getForUserInRoom(int $userId, int $roomId): Collection
    {
        return SessionMarker::with(['creator', 'user', 'recording'])
            ->where('user_id', $userId)
            ->forRoom($roomId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (SessionMarker $marker) => SessionMarkerData::fromModel($marker));
    }

    /**
     * Get session markers by UUID (all markers with the same UUID)
     *
     * @return Collection<SessionMarkerData>
     */
    public function getByUuid(string $uuid): Collection
    {
        return SessionMarker::with(['creator', 'user', 'recording'])
            ->byUuid($uuid)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (SessionMarker $marker) => SessionMarkerData::fromModel($marker));
    }

    /**
     * Get session markers created by a specific user
     *
     * @return Collection<SessionMarkerData>
     */
    public function getByCreator(int $creatorId, ?int $roomId = null): Collection
    {
        $query = SessionMarker::with(['creator', 'user', 'recording'])
            ->byCreator($creatorId);
            
        if ($roomId) {
            $query->forRoom($roomId);
        }
        
        return $query->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (SessionMarker $marker) => SessionMarkerData::fromModel($marker));
    }

    /**
     * Get session markers for a specific recording
     *
     * @return Collection<SessionMarkerData>
     */
    public function getForRecording(int $recordingId): Collection
    {
        return SessionMarker::with(['creator', 'user', 'recording'])
            ->where('recording_id', $recordingId)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn (SessionMarker $marker) => SessionMarkerData::fromModel($marker));
    }
}
