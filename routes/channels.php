<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Main video room public channel - no authentication required
Broadcast::channel('video-room.main', function () {
    // Public channel - anyone can listen
    return true;
});
