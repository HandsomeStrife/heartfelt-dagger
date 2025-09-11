<?php

declare(strict_types=1);

namespace Domain\Room\Repositories;

use Domain\Room\Data\RoomData;
use Domain\Room\Data\RoomParticipantData;
use Domain\Room\Models\Room;
use Domain\User\Models\User;
use Illuminate\Support\Collection;

class RoomRepository
{
    /**
     * Find a room by ID with participant count
     */
    public function findById(int $id): ?RoomData
    {
        $room = Room::with(['creator'])
            ->withCount(['activeParticipants'])
            ->find($id);

        if (! $room) {
            return null;
        }

        return RoomData::from([
            'id' => $room->id,
            'name' => $room->name,
            'description' => $room->description,
            'password' => $room->password,
            'guest_count' => $room->guest_count,
            'creator_id' => $room->creator_id,
            'campaign_id' => $room->campaign_id,
            'invite_code' => $room->invite_code,
            'viewer_code' => $room->viewer_code,
            'status' => $room->status,
            'created_at' => $room->created_at?->toDateTimeString(),
            'updated_at' => $room->updated_at?->toDateTimeString(),
            'creator' => $room->creator,
            'active_participant_count' => $room->active_participants_count,
        ]);
    }

    /**
     * Find a room by invite code
     */
    public function findByInviteCode(string $inviteCode): ?RoomData
    {
        $room = Room::with(['creator'])
            ->withCount(['activeParticipants'])
            ->where('invite_code', $inviteCode)
            ->first();

        if (! $room) {
            return null;
        }

        return RoomData::from([
            'id' => $room->id,
            'name' => $room->name,
            'description' => $room->description,
            'password' => $room->password,
            'guest_count' => $room->guest_count,
            'creator_id' => $room->creator_id,
            'campaign_id' => $room->campaign_id,
            'invite_code' => $room->invite_code,
            'viewer_code' => $room->viewer_code,
            'status' => $room->status,
            'created_at' => $room->created_at?->toDateTimeString(),
            'updated_at' => $room->updated_at?->toDateTimeString(),
            'creator' => $room->creator,
            'active_participant_count' => $room->active_participants_count,
        ]);
    }

    /**
     * Get rooms created by a user
     */
    public function getCreatedByUser(User $user): Collection
    {
        $rooms = Room::with(['creator'])
            ->withCount(['activeParticipants'])
            ->where('creator_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return $rooms->map(fn ($room) => RoomData::from([
            'id' => $room->id,
            'name' => $room->name,
            'description' => $room->description,
            'password' => $room->password,
            'guest_count' => $room->guest_count,
            'creator_id' => $room->creator_id,
            'campaign_id' => $room->campaign_id,
            'invite_code' => $room->invite_code,
            'viewer_code' => $room->viewer_code,
            'status' => $room->status,
            'created_at' => $room->created_at?->toDateTimeString(),
            'updated_at' => $room->updated_at?->toDateTimeString(),
            'creator' => $room->creator,
            'active_participant_count' => $room->active_participants_count,
        ]));
    }

    /**
     * Get rooms joined by a user (currently active participations)
     * Excludes rooms created by the user
     */
    public function getJoinedByUser(User $user): Collection
    {
        $rooms = Room::with(['creator'])
            ->withCount(['activeParticipants'])
            ->whereHas('activeParticipants', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where('creator_id', '!=', $user->id) // Exclude rooms created by this user
            ->orderBy('created_at', 'desc')
            ->get();

        return $rooms->map(fn ($room) => RoomData::from([
            'id' => $room->id,
            'name' => $room->name,
            'description' => $room->description,
            'password' => $room->password,
            'guest_count' => $room->guest_count,
            'creator_id' => $room->creator_id,
            'campaign_id' => $room->campaign_id,
            'invite_code' => $room->invite_code,
            'viewer_code' => $room->viewer_code,
            'status' => $room->status,
            'created_at' => $room->created_at?->toDateTimeString(),
            'updated_at' => $room->updated_at?->toDateTimeString(),
            'creator' => $room->creator,
            'active_participant_count' => $room->active_participants_count,
        ]));
    }

    /**
     * Get rooms that belong to a campaign
     */
    public function getRoomsByCampaign($campaign): Collection
    {
        $rooms = Room::with(['creator'])
            ->withCount(['activeParticipants'])
            ->where('campaign_id', $campaign->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return $rooms->map(fn ($room) => RoomData::from([
            'id' => $room->id,
            'name' => $room->name,
            'description' => $room->description,
            'password' => $room->password,
            'guest_count' => $room->guest_count,
            'creator_id' => $room->creator_id,
            'campaign_id' => $room->campaign_id,
            'invite_code' => $room->invite_code,
            'viewer_code' => $room->viewer_code,
            'status' => $room->status,
            'created_at' => $room->created_at?->toDateTimeString(),
            'updated_at' => $room->updated_at?->toDateTimeString(),
            'creator' => $room->creator,
            'active_participant_count' => $room->active_participants_count,
        ]));
    }

    /**
     * Get active participants for a room
     */
    public function getRoomParticipants(Room $room): Collection
    {
        $participants = $room->activeParticipants()
            ->with(['user', 'character'])
            ->orderBy('joined_at', 'asc')
            ->get();

        return $participants->map(fn ($participant) => RoomParticipantData::from([
            'id' => $participant->id,
            'room_id' => $participant->room_id,
            'user_id' => $participant->user_id,
            'character_id' => $participant->character_id,
            'character_name' => $participant->character_name,
            'character_class' => $participant->character_class,
            'joined_at' => $participant->joined_at?->toDateTimeString(),
            'left_at' => $participant->left_at?->toDateTimeString(),
            'created_at' => $participant->created_at?->toDateTimeString(),
            'updated_at' => $participant->updated_at?->toDateTimeString(),
            'user' => $participant->user,
            'character' => $participant->character,
        ]));
    }

    /**
     * Get recent rooms for dashboard (both created and joined)
     */
    public function getRecentByUser(User $user, int $limit = 3): Collection
    {
        $created = Room::with(['creator'])
            ->withCount(['activeParticipants'])
            ->where('creator_id', $user->id)
            ->get()
            ->map(fn ($room) => RoomData::from([
                'id' => $room->id,
                'name' => $room->name,
                'description' => $room->description,
                'password' => $room->password,
                'guest_count' => $room->guest_count,
                'creator_id' => $room->creator_id,
                'campaign_id' => $room->campaign_id,
                'invite_code' => $room->invite_code,
                'viewer_code' => $room->viewer_code,
                'status' => $room->status,
                'created_at' => $room->created_at?->toDateTimeString(),
                'updated_at' => $room->updated_at?->toDateTimeString(),
                'creator' => $room->creator,
                'active_participant_count' => $room->active_participants_count,
            ]));

        $joined = Room::with(['creator'])
            ->withCount(['activeParticipants'])
            ->whereHas('activeParticipants', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where('creator_id', '!=', $user->id)
            ->get()
            ->map(fn ($room) => RoomData::from([
                'id' => $room->id,
                'name' => $room->name,
                'description' => $room->description,
                'password' => $room->password,
                'guest_count' => $room->guest_count,
                'creator_id' => $room->creator_id,
                'campaign_id' => $room->campaign_id,
                'invite_code' => $room->invite_code,
                'viewer_code' => $room->viewer_code,
                'status' => $room->status,
                'created_at' => $room->created_at?->toDateTimeString(),
                'updated_at' => $room->updated_at?->toDateTimeString(),
                'creator' => $room->creator,
                'active_participant_count' => $room->active_participants_count,
            ]));

        return collect($created->all())
            ->concat($joined->all())
            ->sortByDesc('created_at')
            ->take($limit)
            ->values();
    }
}
