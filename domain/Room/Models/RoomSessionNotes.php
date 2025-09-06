<?php

declare(strict_types=1);

namespace Domain\Room\Models;

use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomSessionNotes extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'notes' => 'string',
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
     * Get or create notes for a specific room and user
     */
    public static function getOrCreateForRoomAndUser(Room $room, User $user): self
    {
        return static::firstOrCreate(
            [
                'room_id' => $room->id,
                'user_id' => $user->id,
            ],
            [
                'notes' => '',
            ]
        );
    }

    /**
     * Update notes for a specific room and user
     */
    public static function updateNotesForRoomAndUser(Room $room, User $user, string $notes): self
    {
        $sessionNotes = static::getOrCreateForRoomAndUser($room, $user);
        $sessionNotes->update(['notes' => $notes]);
        
        return $sessionNotes;
    }

    protected static function newFactory()
    {
        return \Database\Factories\RoomSessionNotesFactory::new();
    }
}
