<?php

use Domain\Room\Models\Room;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Main video room public channel - no authentication required
Broadcast::channel('video-room.main', function () {
    // Public channel - anyone can listen
    return true;
});

// Room-specific presence channels for WebRTC signaling
// Format: room.{roomId}
Broadcast::channel('room.{roomId}', function ($user, $roomId) {
    // Verify user has access to this room
    $room = Room::find($roomId);
    
    if (!$room || !$room->canUserAccess($user)) {
        return false;
    }
    
    // Return user info for presence (visible to all in the channel)
    return [
        'id' => $user->id,
        'name' => $user->name,
    ];
});
