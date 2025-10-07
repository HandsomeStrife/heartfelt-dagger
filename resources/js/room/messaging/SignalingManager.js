/**
 * SignalingManager - Manages WebRTC signaling via Laravel Reverb
 * 
 * Handles connection to room-specific channels, message publishing,
 * and subscription management for WebRTC signaling using Laravel Echo.
 * 
 * Migrated from Ably to Reverb for better performance and no rate limits.
 */

export class SignalingManager {
    constructor(roomWebRTC) {
        this.roomWebRTC = roomWebRTC;
        this.channel = null;
        this.currentPeerId = null;
    }

    /**
     * Connects to the room-specific Reverb channel when Echo is ready
     */
    connectToChannel() {
        const connectWhenReady = () => {
            if (window.Echo) {
                this.setupChannel();
            } else {
                // Wait for Echo/Reverb client to be initialized
                setTimeout(connectWhenReady, 100);
            }
        };
        
        connectWhenReady();
    }

    /**
     * Sets up Reverb channel subscriptions and requests current room state
     */
    setupChannel() {
        // Use room-specific private channel for WebRTC signaling
        const channelName = `room.${this.roomWebRTC.roomData.id}`;
        
        // For WebRTC signaling, we use presence channels to track who's in the room
        this.channel = window.Echo.join(channelName);
        
        // Listen for WebRTC signaling messages
        this.channel.listen('.webrtc-signal', (message) => {
            const payload = message.data || message;
            
            if (!payload) return;

            // Ignore if message is targeted to a different peer
            if (payload.targetPeerId && payload.targetPeerId !== this.currentPeerId) {
                return;
            }

            // Filter out our own messages
            if (payload.senderId === this.currentPeerId) {
                return;
            }
            
            console.log('📨 Room message type:', payload.type, 'from:', payload.senderId);
            
            // Adapt to old message format for backward compatibility
            this.roomWebRTC.messageHandler.handleAblyMessage({
                data: payload
            });
        });

        // Handle users joining the presence channel
        this.channel.here((users) => {
            console.log('👥 Current room members:', users);
        });

        this.channel.joining((user) => {
            console.log('👋 User joining room:', user);
        });

        this.channel.leaving((user) => {
            console.log('👋 User leaving room:', user);
        });

        // Handle channel subscription errors
        this.channel.error((error) => {
            console.error('❌ Reverb channel error:', error);
        });
        
        console.log('✅ Connected to room Reverb channel:', channelName);
        
        // Request current state from other users in this room
        setTimeout(() => {
            console.log('📡 Requesting current room state...');
            if (!this.currentPeerId) {
                this.currentPeerId = this.generatePeerId();
                console.log(`🆔 Generated peer ID: ${this.currentPeerId}`);
            }
            
            this.publishMessage('request-state', {
                requesterId: this.currentPeerId,
                userId: this.roomWebRTC.currentUserId
            });
        }, 500);
    }

    /**
     * Publishes a WebRTC signaling message to the Reverb channel
     * 
     * Uses client events (whisper) for fast peer-to-peer communication
     * without server-side processing overhead.
     */
    publishMessage(type, data, targetPeerId = null) {
        if (!this.channel) {
            console.warn('❌ Reverb channel not ready');
            return;
        }

        const message = {
            type: type,
            data: data,
            senderId: this.currentPeerId || 'anonymous',
            userId: this.roomWebRTC.currentUserId,
            roomId: this.roomWebRTC.roomData.id,
            targetPeerId: targetPeerId,
            timestamp: Date.now()
        };

        // Use whisper for client-to-client events (faster, no server processing)
        // This is perfect for WebRTC signaling where server doesn't need to process
        this.channel.whisper('webrtc-signal', message);
        
        console.log(`📤 Published ${type} to room channel`, targetPeerId ? `(to ${targetPeerId})` : '(broadcast)');
    }

    /**
     * Alias for backward compatibility with old Ably code
     */
    publishToAbly(type, data, targetPeerId = null) {
        return this.publishMessage(type, data, targetPeerId);
    }

    /**
     * Generates a unique peer ID for this session
     */
    generatePeerId() {
        return Math.random().toString(36).substr(2, 9);
    }

    /**
     * Sets the current peer ID
     */
    setCurrentPeerId(peerId) {
        this.currentPeerId = peerId;
    }

    /**
     * Gets the current peer ID
     */
    getCurrentPeerId() {
        return this.currentPeerId;
    }

    /**
     * Gets the Reverb channel instance
     */
    getChannel() {
        return this.channel;
    }

    /**
     * Disconnects from the Reverb channel
     */
    disconnect() {
        if (this.channel) {
            window.Echo.leave(`room.${this.roomWebRTC.roomData.id}`);
            this.channel = null;
        }
    }
}

// Export with old name for backward compatibility
export { SignalingManager as AblyManager };

