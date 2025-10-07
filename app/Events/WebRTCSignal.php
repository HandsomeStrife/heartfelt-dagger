<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * WebRTC Signaling Event
 * 
 * Provides reliable server-side broadcasting for WebRTC signaling messages
 * (offers, answers, ICE candidates, room state messages).
 * 
 * This replaces the unreliable whisper() method which Laravel Reverb docs
 * explicitly state is for "ephemeral, unreliable" messages. One dropped
 * signaling message can break an entire WebRTC connection.
 * 
 * Benefits:
 * - Guaranteed delivery (ShouldBroadcast)
 * - Server-side validation
 * - Proper authorization via channels.php
 * - Audit logging capability
 * - Rate limiting capability
 */
class WebRTCSignal implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance
     */
    public function __construct(
        public int $roomId,
        public string $type,
        public array $data,
        public string $senderId,
        public int $userId,
        public ?string $targetPeerId = null
    ) {
        // Validate message type
        $validTypes = [
            'request-state',
            'user-joined',
            'user-left',
            'webrtc-offer',
            'webrtc-answer',
            'webrtc-ice-candidate',
            'fear-updated',
            'countdown-updated',
            'countdown-deleted',
            'gm-presence-changed',
            'session-marker-created'
        ];

        if (!in_array($type, $validTypes)) {
            throw new \InvalidArgumentException("Invalid WebRTC signal type: {$type}");
        }
    }

    /**
     * Get the channels the event should broadcast on
     */
    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("room.{$this->roomId}");
    }

    /**
     * The event name to broadcast as
     */
    public function broadcastAs(): string
    {
        return 'webrtc-signal';
    }

    /**
     * Get the data to broadcast
     */
    public function broadcastWith(): array
    {
        return [
            'type' => $this->type,
            'data' => $this->data,
            'senderId' => $this->senderId,
            'userId' => $this->userId,
            'targetPeerId' => $this->targetPeerId,
            'timestamp' => now()->timestamp,
            'server_sent' => true // Flag to distinguish from whisper messages
        ];
    }

    /**
     * Determine if this event should be broadcast to others
     * 
     * @return bool
     */
    public function broadcastWhen(): bool
    {
        // Always broadcast - this is critical signaling data
        return true;
    }
}
