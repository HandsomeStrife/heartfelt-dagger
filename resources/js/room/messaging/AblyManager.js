/**
 * AblyManager - Manages Ably realtime messaging connections
 * 
 * Handles connection to room-specific Ably channels, message publishing,
 * and subscription management for WebRTC signaling.
 */

export class AblyManager {
    constructor(roomWebRTC) {
        this.roomWebRTC = roomWebRTC;
        this.ablyChannel = null;
        this.currentPeerId = null;
    }

    /**
     * Connects to the room-specific Ably channel when client is ready
     */
    connectToAblyChannel() {
        const connectWhenReady = () => {
            if (window.AblyClient) {
                this.setupAblyChannel();
            } else {
                // Wait for Ably client to be initialized
                setTimeout(connectWhenReady, 100);
            }
        };
        
        connectWhenReady();
    }

    /**
     * Sets up Ably channel subscriptions and requests current room state
     */
    setupAblyChannel() {
        // Use room-specific channel
        const channelName = `room-${this.roomWebRTC.roomData.id}`;
        this.ablyChannel = window.AblyClient.channels.get(channelName);
        
        // Add error handling for channel operations
        this.ablyChannel.on('failed', (error) => {
            console.error('❌ Ably channel failed:', error);
            if (error.message && error.message.includes('rate limit')) {
                console.warn('⚠️ Ably channel rate limit - this may affect video connectivity');
            }
        });

        this.ablyChannel.on('suspended', (error) => {
            console.warn('⚠️ Ably channel suspended:', error);
        });
        
        // Subscribe to signaling messages only (filter out other app messages)
        this.ablyChannel.subscribe((message) => {
            // Fix #1: Gate Ably to signaling messages only
            if (message.name && message.name !== 'webrtc-signal') return;

            const payload = message.data;
            if (!payload) return;

            // Ignore if message is targeted to a different peer
            if (payload.targetPeerId && payload.targetPeerId !== this.currentPeerId) return;

            // Filter out our own messages
            if (payload.senderId === this.currentPeerId) return;
            
            console.log('📨 Room message type:', payload.type, 'from:', payload.senderId);
            this.roomWebRTC.messageHandler.handleAblyMessage(message);
        });
        
        console.log('✅ Connected to room Ably channel:', channelName);
        
        // Request current state from other users in this room
        setTimeout(() => {
            console.log('📡 Requesting current room state...');
            if (!this.currentPeerId) {
                this.currentPeerId = this.generatePeerId();
                console.log(`🆔 Generated viewer peer ID: ${this.currentPeerId}`);
            }
            this.publishToAbly('request-state', {
                requesterId: this.currentPeerId,
                userId: this.roomWebRTC.currentUserId
            });
        }, 500);
    }

    /**
     * Publishes a message to the Ably channel with proper structure and rate limiting
     */
    publishToAbly(type, data, targetPeerId = null) {
        if (!this.ablyChannel) {
            console.warn('❌ Ably channel not ready');
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

        // Add error handling for rate limit issues
        this.ablyChannel.publish('webrtc-signal', message, (err) => {
            if (err) {
                console.error('❌ Ably publish failed:', err);
                if (err.message && err.message.includes('rate limit')) {
                    console.warn('⚠️ Ably rate limit hit - consider reducing message frequency');
                    // Could implement exponential backoff retry here if needed
                }
            } else {
                console.log(`📤 Published ${type} to room channel`);
            }
        });
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
     * Gets the Ably channel instance
     */
    getChannel() {
        return this.ablyChannel;
    }

    /**
     * Disconnects from the Ably channel
     */
    disconnect() {
        if (this.ablyChannel) {
            this.ablyChannel.unsubscribe();
            this.ablyChannel = null;
        }
    }
}
