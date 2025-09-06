/**
 * MessageHandler - Routes and processes different message types
 * 
 * Provides centralized message routing, validation, and error handling
 * for all Ably messages in the room system.
 */
export class MessageHandler {
    constructor(roomWebRTC) {
        this.roomWebRTC = roomWebRTC;
        this.messageValidators = new Map();
        this.messageProcessors = new Map();
        this.messageStats = new Map();
        
        this.setupDefaultValidators();
        this.setupDefaultProcessors();
    }

    /**
     * Sets up default message validators
     */
    setupDefaultValidators() {
        // WebRTC signaling validators
        this.messageValidators.set('offer', (data) => {
            return data && data.offer && data.fromPeerId;
        });
        
        this.messageValidators.set('answer', (data) => {
            return data && data.answer && data.fromPeerId;
        });
        
        this.messageValidators.set('ice-candidate', (data) => {
            return data && data.candidate && data.fromPeerId;
        });
        
        // Room state validators
        this.messageValidators.set('request-state', (data) => {
            return data && data.requesterId;
        });
        
        this.messageValidators.set('user-joined', (data) => {
            return data && data.slotId && data.participantData;
        });
        
        this.messageValidators.set('user-left', (data) => {
            return data && data.slotId;
        });
        
        // Speech validators
        this.messageValidators.set('speech-chunk', (data) => {
            return data && data.text && data.fromPeerId;
        });
        
        this.messageValidators.set('transcript-update', (data) => {
            return data && data.transcript && data.fromPeerId;
        });
    }

    /**
     * Sets up default message processors
     */
    setupDefaultProcessors() {
        // WebRTC signaling processors
        this.messageProcessors.set('offer', (data, senderId) => {
            this.roomWebRTC.peerConnectionManager.handleOffer(data, senderId);
        });
        
        this.messageProcessors.set('answer', (data, senderId) => {
            this.roomWebRTC.peerConnectionManager.handleAnswer(data, senderId);
        });
        
        this.messageProcessors.set('ice-candidate', (data, senderId) => {
            this.roomWebRTC.peerConnectionManager.handleIceCandidate(data, senderId);
        });
        
        // Room state processors
        this.messageProcessors.set('request-state', (data, senderId) => {
            this.handleStateRequest(data, senderId);
        });
        
        this.messageProcessors.set('user-joined', (data, senderId) => {
            this.roomWebRTC.handleUserJoined(data, senderId);
        });
        
        this.messageProcessors.set('user-left', (data, senderId) => {
            this.roomWebRTC.handleUserLeft(data, senderId);
        });
        
        // Speech processors
        this.messageProcessors.set('speech-chunk', (data, senderId) => {
            this.handleSpeechChunk(data, senderId);
        });
        
        this.messageProcessors.set('transcript-update', (data, senderId) => {
            this.handleTranscriptUpdate(data, senderId);
        });
    }

    /**
     * Processes an incoming message
     */
    processMessage(messageType, data, senderId) {
        try {
            // Update message statistics
            this.updateMessageStats(messageType);
            
            // Skip messages from ourselves
            if (senderId === this.roomWebRTC.currentPeerId) {
                return;
            }
            
            console.log(`📨 Processing message: ${messageType} from ${senderId}`);
            
            // Validate message
            if (!this.validateMessage(messageType, data)) {
                console.warn(`📨 Invalid message: ${messageType}`, data);
                return;
            }
            
            // Process message
            const processor = this.messageProcessors.get(messageType);
            if (processor) {
                processor(data, senderId);
            } else {
                console.warn(`📨 No processor for message type: ${messageType}`);
            }
        } catch (error) {
            console.error(`📨 Error processing message ${messageType}:`, error);
        }
    }

    /**
     * Validates a message
     */
    validateMessage(messageType, data) {
        const validator = this.messageValidators.get(messageType);
        if (!validator) {
            // No validator means message is valid by default
            return true;
        }
        
        try {
            return validator(data);
        } catch (error) {
            console.error(`📨 Error validating message ${messageType}:`, error);
            return false;
        }
    }

    /**
     * Updates message statistics
     */
    updateMessageStats(messageType) {
        const current = this.messageStats.get(messageType) || 0;
        this.messageStats.set(messageType, current + 1);
    }

    /**
     * Handles state request messages
     */
    handleStateRequest(data, senderId) {
        console.log(`📡 State request from ${senderId}:`, data);
        
        // If we're in a slot, announce our presence
        if (this.roomWebRTC.isJoined && this.roomWebRTC.currentSlotId) {
            const participantData = this.roomWebRTC.roomData.participants.find(
                p => p.user_id === this.roomWebRTC.currentUserId
            );
            
            if (participantData) {
                // Send our state to the requester
                this.roomWebRTC.ablyManager.announceUserJoined(
                    this.roomWebRTC.currentSlotId,
                    participantData
                );
            }
        }
    }

    /**
     * Handles speech chunk messages
     */
    handleSpeechChunk(data, senderId) {
        console.log(`🎤 Speech chunk from ${senderId}:`, data.text);
        
        // Add to speech buffer or display in UI
        if (this.roomWebRTC.speechBuffer) {
            this.roomWebRTC.speechBuffer.push({
                text: data.text,
                confidence: data.confidence,
                timestamp: data.timestamp,
                peerId: senderId,
                participantName: data.participantName
            });
        }
        
        // Update transcript UI if available
        this.updateTranscriptDisplay(data, senderId);
    }

    /**
     * Handles transcript update messages
     */
    handleTranscriptUpdate(data, senderId) {
        console.log(`📝 Transcript update from ${senderId}:`, data.transcript);
        
        // Update transcript display
        this.updateTranscriptDisplay(data, senderId);
    }

    /**
     * Updates transcript display in UI
     */
    updateTranscriptDisplay(data, senderId) {
        // Find transcript display element
        const transcriptElement = document.querySelector('#transcript-display, [data-transcript-display]');
        if (!transcriptElement) return;
        
        // Create transcript entry
        const entry = document.createElement('div');
        entry.className = 'transcript-entry mb-2 p-2 bg-slate-800 rounded text-sm';
        entry.innerHTML = `
            <div class="flex items-center space-x-2 mb-1">
                <span class="font-medium text-amber-400">${data.participantName || 'Unknown'}</span>
                <span class="text-slate-500 text-xs">${new Date(data.timestamp).toLocaleTimeString()}</span>
            </div>
            <div class="text-slate-300">${data.text || data.transcript}</div>
        `;
        
        // Add to transcript display
        transcriptElement.appendChild(entry);
        
        // Scroll to bottom
        transcriptElement.scrollTop = transcriptElement.scrollHeight;
        
        // Limit number of entries (keep last 50)
        const entries = transcriptElement.querySelectorAll('.transcript-entry');
        if (entries.length > 50) {
            entries[0].remove();
        }
    }

    /**
     * Registers a custom message validator
     */
    registerValidator(messageType, validator) {
        this.messageValidators.set(messageType, validator);
        console.log(`📨 Registered validator for: ${messageType}`);
    }

    /**
     * Registers a custom message processor
     */
    registerProcessor(messageType, processor) {
        this.messageProcessors.set(messageType, processor);
        console.log(`📨 Registered processor for: ${messageType}`);
    }

    /**
     * Unregisters a message validator
     */
    unregisterValidator(messageType) {
        this.messageValidators.delete(messageType);
        console.log(`📨 Unregistered validator for: ${messageType}`);
    }

    /**
     * Unregisters a message processor
     */
    unregisterProcessor(messageType) {
        this.messageProcessors.delete(messageType);
        console.log(`📨 Unregistered processor for: ${messageType}`);
    }

    /**
     * Gets message statistics
     */
    getMessageStats() {
        return new Map(this.messageStats);
    }

    /**
     * Resets message statistics
     */
    resetMessageStats() {
        this.messageStats.clear();
        console.log('📨 Message statistics reset');
    }

    /**
     * Gets supported message types
     */
    getSupportedMessageTypes() {
        return Array.from(this.messageProcessors.keys());
    }

    /**
     * Checks if a message type is supported
     */
    isMessageTypeSupported(messageType) {
        return this.messageProcessors.has(messageType);
    }

    /**
     * Destroys the message handler
     */
    destroy() {
        this.messageValidators.clear();
        this.messageProcessors.clear();
        this.messageStats.clear();
        console.log('📨 MessageHandler destroyed');
    }
}
