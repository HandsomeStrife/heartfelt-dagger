/**
 * AblyManager - Manages Ably realtime messaging for room communication
 * 
 * Handles connection to Ably channels, message publishing/subscribing,
 * and coordination between room participants for WebRTC signaling.
 */
export class AblyManager {
    constructor(roomWebRTC) {
        this.roomWebRTC = roomWebRTC;
        this.ablyChannel = null;
        this.messageHandlers = new Map();
        this.connectionState = 'disconnected';
        
        this.setupMessageHandlers();
    }

    /**
     * Sets up default message handlers
     */
    setupMessageHandlers() {
        // WebRTC signaling handlers
        this.messageHandlers.set('offer', (data, senderId) => {
            this.roomWebRTC.handleOffer(data, senderId);
        });
        
        this.messageHandlers.set('answer', (data, senderId) => {
            this.roomWebRTC.handleAnswer(data, senderId);
        });
        
        this.messageHandlers.set('ice-candidate', (data, senderId) => {
            this.roomWebRTC.handleIceCandidate(data, senderId);
        });
        
        // Room state handlers
        this.messageHandlers.set('request-state', (data, senderId) => {
            this.roomWebRTC.handleStateRequest(data, senderId);
        });
        
        this.messageHandlers.set('user-joined', (data, senderId) => {
            this.roomWebRTC.handleUserJoined(data, senderId);
        });
        
        this.messageHandlers.set('user-left', (data, senderId) => {
            this.roomWebRTC.handleUserLeft(data, senderId);
        });
        
        // Speech and transcript handlers
        this.messageHandlers.set('speech-chunk', (data, senderId) => {
            this.roomWebRTC.handleSpeechChunk && this.roomWebRTC.handleSpeechChunk(data, senderId);
        });
        
        this.messageHandlers.set('transcript-update', (data, senderId) => {
            this.roomWebRTC.handleTranscriptUpdate && this.roomWebRTC.handleTranscriptUpdate(data, senderId);
        });
    }

    /**
     * Connects to the Ably channel for this room
     */
    async connectToChannel() {
        try {
            console.log('📡 Connecting to Ably channel...');
            
            // Initialize Ably if not already done
            if (!window.ably) {
                throw new Error('Ably not initialized. Make sure Ably is loaded before connecting.');
            }
            
            // Connect to room-specific channel
            const channelName = `room-${this.roomWebRTC.roomData.id}`;
            this.ablyChannel = window.ably.channels.get(channelName);
            
            // Set up message listener
            this.ablyChannel.subscribe((message) => {
                this.handleMessage(message);
            });
            
            // Set up connection state listeners
            this.ablyChannel.on('attached', () => {
                this.connectionState = 'connected';
                console.log(`✅ Connected to room Ably channel: ${channelName}`);
            });
            
            this.ablyChannel.on('detached', () => {
                this.connectionState = 'disconnected';
                console.log(`❌ Disconnected from room Ably channel: ${channelName}`);
            });
            
            this.ablyChannel.on('failed', (error) => {
                this.connectionState = 'failed';
                console.error('❌ Ably channel connection failed:', error);
            });
            
            // Attach to channel
            await this.ablyChannel.attach();
            
            return true;
        } catch (error) {
            console.error('❌ Failed to connect to Ably channel:', error);
            this.connectionState = 'failed';
            throw error;
        }
    }

    /**
     * Handles incoming Ably messages
     */
    handleMessage(message) {
        try {
            const { name: messageType, data, clientId: senderId } = message;
            
            // Skip messages from ourselves
            if (senderId === this.roomWebRTC.currentPeerId) {
                return;
            }
            
            console.log(`📨 Received message: ${messageType} from ${senderId}`, data);
            
            // Route message to appropriate handler
            const handler = this.messageHandlers.get(messageType);
            if (handler) {
                handler(data, senderId);
            } else {
                console.warn(`📨 No handler for message type: ${messageType}`);
            }
        } catch (error) {
            console.error('📨 Error handling Ably message:', error, message);
        }
    }

    /**
     * Publishes a message to the room channel
     */
    async publishMessage(messageType, data, targetClientId = null) {
        if (!this.ablyChannel) {
            console.error('📤 Cannot publish: Ably channel not connected');
            return false;
        }
        
        try {
            const messageData = {
                name: messageType,
                data: data
            };
            
            // Add target client ID if specified (for direct messages)
            if (targetClientId) {
                messageData.clientId = targetClientId;
            }
            
            await this.ablyChannel.publish(messageType, data);
            
            console.log(`📤 Published message: ${messageType}`, data);
            return true;
        } catch (error) {
            console.error(`📤 Failed to publish message: ${messageType}`, error);
            return false;
        }
    }

    /**
     * Publishes a message to a specific participant (direct message)
     */
    async publishDirectMessage(messageType, data, targetPeerId) {
        // For Ably, we can use presence or a naming convention
        // This is a simplified implementation - in production you might use Ably's presence API
        const directMessageType = `${messageType}-direct-${targetPeerId}`;
        return await this.publishMessage(directMessageType, {
            ...data,
            targetPeerId: targetPeerId,
            fromPeerId: this.roomWebRTC.currentPeerId
        });
    }

    /**
     * Requests the current room state from other participants
     */
    async requestRoomState() {
        console.log('📡 Requesting current room state...');
        
        const requestData = {
            requesterId: this.roomWebRTC.currentPeerId,
            timestamp: Date.now()
        };
        
        const success = await this.publishMessage('request-state', requestData);
        
        if (success) {
            console.log('📤 Published request-state to room channel');
        }
        
        return success;
    }

    /**
     * Announces that the user has joined a slot
     */
    async announceUserJoined(slotId, participantData) {
        const joinData = {
            slotId: slotId,
            participantData: participantData,
            timestamp: Date.now()
        };
        
        return await this.publishMessage('user-joined', joinData);
    }

    /**
     * Announces that the user has left a slot
     */
    async announceUserLeft(slotId) {
        const leaveData = {
            slotId: slotId,
            timestamp: Date.now()
        };
        
        return await this.publishMessage('user-left', leaveData);
    }

    /**
     * Sends WebRTC offer to a specific peer
     */
    async sendOffer(targetPeerId, offer) {
        const offerData = {
            offer: offer,
            fromPeerId: this.roomWebRTC.currentPeerId,
            timestamp: Date.now()
        };
        
        return await this.publishDirectMessage('offer', offerData, targetPeerId);
    }

    /**
     * Sends WebRTC answer to a specific peer
     */
    async sendAnswer(targetPeerId, answer) {
        const answerData = {
            answer: answer,
            fromPeerId: this.roomWebRTC.currentPeerId,
            timestamp: Date.now()
        };
        
        return await this.publishDirectMessage('answer', answerData, targetPeerId);
    }

    /**
     * Sends ICE candidate to a specific peer
     */
    async sendIceCandidate(targetPeerId, candidate) {
        const candidateData = {
            candidate: candidate,
            fromPeerId: this.roomWebRTC.currentPeerId,
            timestamp: Date.now()
        };
        
        return await this.publishDirectMessage('ice-candidate', candidateData, targetPeerId);
    }

    /**
     * Sends speech chunk for transcription
     */
    async sendSpeechChunk(speechData) {
        const chunkData = {
            text: speechData.text,
            confidence: speechData.confidence,
            timestamp: speechData.timestamp,
            fromPeerId: this.roomWebRTC.currentPeerId,
            participantName: speechData.participantName
        };
        
        return await this.publishMessage('speech-chunk', chunkData);
    }

    /**
     * Sends transcript update
     */
    async sendTranscriptUpdate(transcriptData) {
        const updateData = {
            transcript: transcriptData.transcript,
            timestamp: transcriptData.timestamp,
            fromPeerId: this.roomWebRTC.currentPeerId,
            participantName: transcriptData.participantName
        };
        
        return await this.publishMessage('transcript-update', updateData);
    }

    /**
     * Registers a custom message handler
     */
    registerMessageHandler(messageType, handler) {
        this.messageHandlers.set(messageType, handler);
        console.log(`📨 Registered handler for message type: ${messageType}`);
    }

    /**
     * Unregisters a message handler
     */
    unregisterMessageHandler(messageType) {
        this.messageHandlers.delete(messageType);
        console.log(`📨 Unregistered handler for message type: ${messageType}`);
    }

    /**
     * Gets the current connection state
     */
    getConnectionState() {
        return this.connectionState;
    }

    /**
     * Checks if connected to Ably
     */
    isConnected() {
        return this.connectionState === 'connected';
    }

    /**
     * Gets channel presence (other participants)
     */
    async getPresence() {
        if (!this.ablyChannel) return [];
        
        try {
            const presence = await this.ablyChannel.presence.get();
            return presence.map(member => ({
                clientId: member.clientId,
                data: member.data,
                action: member.action
            }));
        } catch (error) {
            console.error('📡 Failed to get channel presence:', error);
            return [];
        }
    }

    /**
     * Enters presence (announces our participation)
     */
    async enterPresence(presenceData = {}) {
        if (!this.ablyChannel) return false;
        
        try {
            await this.ablyChannel.presence.enter({
                peerId: this.roomWebRTC.currentPeerId,
                slotId: this.roomWebRTC.currentSlotId,
                ...presenceData
            });
            
            console.log('📡 Entered channel presence');
            return true;
        } catch (error) {
            console.error('📡 Failed to enter presence:', error);
            return false;
        }
    }

    /**
     * Leaves presence (announces our departure)
     */
    async leavePresence() {
        if (!this.ablyChannel) return false;
        
        try {
            await this.ablyChannel.presence.leave();
            console.log('📡 Left channel presence');
            return true;
        } catch (error) {
            console.error('📡 Failed to leave presence:', error);
            return false;
        }
    }

    /**
     * Disconnects from Ably channel
     */
    async disconnect() {
        if (this.ablyChannel) {
            try {
                await this.leavePresence();
                await this.ablyChannel.detach();
                this.ablyChannel = null;
                this.connectionState = 'disconnected';
                console.log('📡 Disconnected from Ably channel');
            } catch (error) {
                console.error('📡 Error disconnecting from Ably:', error);
            }
        }
    }

    /**
     * Cleans up Ably manager resources
     */
    async destroy() {
        await this.disconnect();
        this.messageHandlers.clear();
        console.log('📡 AblyManager destroyed');
    }
}
