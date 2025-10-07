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
            
            // CRITICAL: Peer ID must be set BEFORE connecting to channel
            // Don't generate it here - it should already be set by room-webrtc.js
            if (!this.currentPeerId) {
                console.error('❌ CRITICAL: Peer ID not set before connecting to channel!');
                console.error('❌ This indicates an initialization order bug in room-webrtc.js');
                throw new Error('Peer ID must be set before connecting to Reverb channel');
            }
            
            console.log(`🆔 Using peer ID: ${this.currentPeerId}`);
            
            this.publishMessage('request-state', {
                requesterId: this.currentPeerId,
                userId: this.roomWebRTC.currentUserId
            });
        }, 500);
    }

    /**
     * Publishes a WebRTC signaling message to the Reverb channel
     * 
     * CRITICAL: Uses server-side broadcast for reliable delivery
     * Laravel Reverb docs explicitly state whisper is for "ephemeral, unreliable" messages.
     * One dropped signaling message = broken WebRTC connection.
     * 
     * @param {string} type - Message type (e.g., 'user-joined', 'webrtc-offer')
     * @param {object} data - Message payload
     * @param {string|null} targetPeerId - Optional specific peer to target
     */
    async publishMessage(type, data, targetPeerId = null) {
        if (!this.channel) {
            console.warn('❌ Reverb channel not ready');
            return;
        }

        const message = {
            type: type,
            data: data,
            senderId: this.currentPeerId || 'anonymous',
            targetPeerId: targetPeerId
        };

        try {
            // Use server-side endpoint for reliable delivery
            const response = await fetch(`/api/rooms/${this.roomWebRTC.roomData.id}/webrtc-signal`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(message)
            });

            if (!response.ok) {
                throw new Error(`Server signaling failed: ${response.status} ${response.statusText}`);
            }

            console.log(`✅ Signaling message sent via server: ${type}`, targetPeerId ? `(to ${targetPeerId})` : '(broadcast)');
        } catch (error) {
            console.error('❌ Server-side signaling failed:', error);
            
            // FALLBACK: Use whisper as last resort
            // This is not ideal, but better than complete failure
            console.warn('⚠️ Falling back to unreliable whisper method');
            try {
                this.channel.whisper('webrtc-signal', {
                    ...message,
                    userId: this.roomWebRTC.currentUserId,
                    roomId: this.roomWebRTC.roomData.id,
                    timestamp: Date.now(),
                    fallback: true
                });
                console.log(`⚠️ Signaling message sent via whisper fallback: ${type}`);
            } catch (whisperError) {
                console.error('❌ Whisper fallback also failed:', whisperError);
                console.error('❌ CRITICAL: Unable to send signaling message. Connection may fail.');
            }
        }
    }

    /**
     * Alias for backward compatibility with old Ably code
     */
    publishToAbly(type, data, targetPeerId = null) {
        return this.publishMessage(type, data, targetPeerId);
    }

    /**
     * Generates a collision-resistant peer ID
     * Combines user ID, timestamp, and random component for uniqueness
     */
    generatePeerId() {
        const userId = this.roomWebRTC.currentUserId || 'anon';
        const timestamp = Date.now();
        
        // Use crypto.randomUUID if available, otherwise fallback to polyfill
        let randomComponent;
        if (typeof crypto !== 'undefined' && crypto.randomUUID) {
            randomComponent = crypto.randomUUID().split('-')[0]; // Take first segment
        } else {
            // Fallback polyfill for UUID v4
            randomComponent = 'xxxxxxxx'.replace(/[x]/g, () => {
                return (Math.random() * 16 | 0).toString(16);
            });
        }
        
        return `${userId}-${timestamp}-${randomComponent}`;
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

