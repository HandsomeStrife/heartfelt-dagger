<?php

declare(strict_types=1);

namespace Domain\Room\Repositories;

use Domain\Room\Data\RoomRecordingData;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomRecording;
use Domain\User\Models\User;
use Illuminate\Support\Collection;

class RoomRecordingRepository
{
    /**
     * Get all recordings for a user
     *
     * @return Collection<RoomRecordingData>
     */
    public function getByUser(User $user): Collection
    {
        return RoomRecording::whereHas('room', function ($query) use ($user) {
            $query->where('creator_id', $user->id)
                ->orWhereHas('participants', function ($participantQuery) use ($user) {
                    $participantQuery->where('user_id', $user->id);
                });
        })
        ->with(['room', 'user'])
        ->byStartTime()
        ->get()
        ->map(fn (RoomRecording $recording) => RoomRecordingData::from([
            ...$recording->toArray(),
            'room' => $recording->room ? $recording->room->toArray() : null,
            'user' => $recording->user ? $recording->user->toArray() : null,
        ]));
    }

    /**
     * Get recordings for a specific room that a user has access to
     *
     * @return Collection<RoomRecordingData>
     */
    public function getByRoomForUser(Room $room, User $user): Collection
    {
        // Check if user has access to this room
        if (!$this->userCanAccessRoom($room, $user)) {
            return collect();
        }

        return RoomRecording::where('room_id', $room->id)
            ->with(['room', 'user'])
            ->byStartTime()
            ->get()
            ->map(fn (RoomRecording $recording) => RoomRecordingData::from([
            ...$recording->toArray(),
            'room' => $recording->room ? $recording->room->toArray() : null,
            'user' => $recording->user ? $recording->user->toArray() : null,
        ]));
    }

    /**
     * Get recordings by provider for a user
     *
     * @return Collection<RoomRecordingData>
     */
    public function getByProviderForUser(string $provider, User $user): Collection
    {
        return RoomRecording::provider($provider)
            ->whereHas('room', function ($query) use ($user) {
                $query->where('creator_id', $user->id)
                    ->orWhereHas('participants', function ($participantQuery) use ($user) {
                        $participantQuery->where('user_id', $user->id);
                    });
            })
            ->with(['room', 'user'])
            ->byStartTime()
            ->get()
            ->map(fn (RoomRecording $recording) => RoomRecordingData::from([
            ...$recording->toArray(),
            'room' => $recording->room ? $recording->room->toArray() : null,
            'user' => $recording->user ? $recording->user->toArray() : null,
        ]));
    }

    /**
     * Get recordings within a date range for a user
     *
     * @return Collection<RoomRecordingData>
     */
    public function getByDateRangeForUser(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate, User $user): Collection
    {
        $startMs = $startDate->startOfDay()->getTimestampMs();
        $endMs = $endDate->endOfDay()->getTimestampMs();

        return RoomRecording::whereBetween('started_at_ms', [$startMs, $endMs])
            ->whereHas('room', function ($query) use ($user) {
                $query->where('creator_id', $user->id)
                    ->orWhereHas('participants', function ($participantQuery) use ($user) {
                        $participantQuery->where('user_id', $user->id);
                    });
            })
            ->with(['room', 'user'])
            ->byStartTime()
            ->get()
            ->map(fn (RoomRecording $recording) => RoomRecordingData::from([
            ...$recording->toArray(),
            'room' => $recording->room ? $recording->room->toArray() : null,
            'user' => $recording->user ? $recording->user->toArray() : null,
        ]));
    }

    /**
     * Search recordings by room name or description for a user
     *
     * @return Collection<RoomRecordingData>
     */
    public function searchForUser(string $searchTerm, User $user): Collection
    {
        return RoomRecording::whereHas('room', function ($query) use ($user) {
            $query->where('creator_id', $user->id)
                ->orWhereHas('participants', function ($participantQuery) use ($user) {
                    $participantQuery->where('user_id', $user->id);
                });
        })
        ->whereHas('room', function ($query) use ($searchTerm) {
            $query->where(function ($searchQuery) use ($searchTerm) {
                $searchQuery->where('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('description', 'LIKE', "%{$searchTerm}%");
            });
        })
        ->with(['room', 'user'])
        ->byStartTime()
        ->get()
        ->map(fn (RoomRecording $recording) => RoomRecordingData::from([
            ...$recording->toArray(),
            'room' => $recording->room ? $recording->room->toArray() : null,
            'user' => $recording->user ? $recording->user->toArray() : null,
        ]));
    }

    /**
     * Get ready recordings only for a user
     *
     * @return Collection<RoomRecordingData>
     */
    public function getReadyForUser(User $user): Collection
    {
        return RoomRecording::ready()
            ->whereHas('room', function ($query) use ($user) {
                $query->where('creator_id', $user->id)
                    ->orWhereHas('participants', function ($participantQuery) use ($user) {
                        $participantQuery->where('user_id', $user->id);
                    });
            })
            ->with(['room', 'user'])
            ->byStartTime()
            ->get()
            ->map(fn (RoomRecording $recording) => RoomRecordingData::from([
            ...$recording->toArray(),
            'room' => $recording->room ? $recording->room->toArray() : null,
            'user' => $recording->user ? $recording->user->toArray() : null,
        ]));
    }

    /**
     * Get recordings grouped by room for a user
     */
    public function getGroupedByRoomForUser(User $user): Collection
    {
        $recordings = $this->getByUser($user);
        
        return $recordings->groupBy(function (RoomRecordingData $recording) {
            return $recording->room_id;
        })->map(function (Collection $roomRecordings) {
            return [
                'room' => $roomRecordings->first()->room,
                'recordings' => $roomRecordings,
                'total_count' => $roomRecordings->count(),
                'total_size_bytes' => $roomRecordings->sum('size_bytes'),
                'latest_recording' => $roomRecordings->first(), // Already sorted by start time desc
            ];
        });
    }

    /**
     * Get storage analytics for a user
     */
    public function getStorageAnalyticsForUser(User $user): array
    {
        $recordings = RoomRecording::whereHas('room', function ($query) use ($user) {
            $query->where('creator_id', $user->id)
                ->orWhereHas('participants', function ($participantQuery) use ($user) {
                    $participantQuery->where('user_id', $user->id);
                });
        })->get();

        $totalSize = $recordings->sum('size_bytes');
        $totalCount = $recordings->count();
        $totalDuration = $recordings->sum(function (RoomRecording $recording) {
            return $recording->getDurationMs();
        });

        $byProvider = $recordings->groupBy('provider')->map(function ($providerRecordings) {
            return [
                'count' => $providerRecordings->count(),
                'size_bytes' => $providerRecordings->sum('size_bytes'),
                'duration_ms' => $providerRecordings->sum(function (RoomRecording $recording) {
                    return $recording->getDurationMs();
                }),
            ];
        });

        $byStatus = $recordings->groupBy('status')->map(function ($statusRecordings) {
            return [
                'count' => $statusRecordings->count(),
                'size_bytes' => $statusRecordings->sum('size_bytes'),
            ];
        });

        return [
            'total_recordings' => $totalCount,
            'total_size_bytes' => $totalSize,
            'total_duration_ms' => $totalDuration,
            'by_provider' => $byProvider,
            'by_status' => $byStatus,
            'average_size_bytes' => $totalCount > 0 ? round($totalSize / $totalCount) : 0,
            'average_duration_ms' => $totalCount > 0 ? round($totalDuration / $totalCount) : 0,
        ];
    }

    /**
     * Find a recording by ID that a user has access to
     */
    public function findByIdForUser(int $recordingId, User $user): ?RoomRecordingData
    {
        $recording = RoomRecording::with(['room', 'user'])
            ->whereHas('room', function ($query) use ($user) {
                $query->where('creator_id', $user->id)
                    ->orWhereHas('participants', function ($participantQuery) use ($user) {
                        $participantQuery->where('user_id', $user->id);
                    });
            })
            ->find($recordingId);

        return $recording ? RoomRecordingData::from([
            ...$recording->toArray(),
            'room' => $recording->room ? $recording->room->toArray() : null,
            'user' => $recording->user ? $recording->user->toArray() : null,
        ]) : null;
    }

    /**
     * Check if a user can access a room
     */
    private function userCanAccessRoom(Room $room, User $user): bool
    {
        // User is the room creator
        if ($room->creator_id === $user->id) {
            return true;
        }

        // User is a participant in the room
        return $room->participants()->where('user_id', $user->id)->exists();
    }
}
