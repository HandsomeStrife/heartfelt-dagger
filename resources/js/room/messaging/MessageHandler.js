/**
 * MessageHandler - Handles incoming Ably messages for WebRTC signaling
 * 
 * Processes different types of room messages including user presence,
 * WebRTC offers/answers, and ICE candidates.
 */

export class MessageHandler {
    constructor(roomWebRTC) {
        this.roomWebRTC = roomWebRTC;
    }

    /**
     * Main message handler that routes messages to appropriate handlers
     */
    handleAblyMessage(message) {
        const { type, data, senderId, targetPeerId } = message.data;

        // Defensive: double-check targeting
        if (targetPeerId && targetPeerId !== this.roomWebRTC.ablyManager.getCurrentPeerId()) return;

        console.log('🎭 Handling room message:', type, 'from:', senderId);

        switch (type) {
            case 'request-state':
                this.handleStateRequest(data, senderId);
                break;
            case 'user-joined':
                this.handleUserJoined(data, senderId);
                break;
            case 'user-left':
                this.handleUserLeft(data, senderId);
                break;
            case 'webrtc-offer':
                this.handleOffer(data, senderId);
                break;
            case 'webrtc-answer':
                this.handleAnswer(data, senderId);
                break;
            case 'webrtc-ice-candidate':
                this.handleIceCandidate(data, senderId);
                break;
            default:
                console.log('🤷 Unknown room message type:', type);
        }
    }

    /**
     * Handles state request from new users joining the room
     */
    handleStateRequest(data, senderId) {
        // If we're in a slot, tell the specific requester about our presence
        if (this.roomWebRTC.isJoined && this.roomWebRTC.currentSlotId) {
            const participantData = this.roomWebRTC.roomData.participants.find(p => p.user_id === this.roomWebRTC.currentUserId);
            // Scope reply to specific requester to reduce chatter
            this.roomWebRTC.ablyManager.publishToAbly('user-joined', {
                slotId: this.roomWebRTC.currentSlotId,
                participantData: participantData
            }, data.requesterId || senderId); // Use requesterId if available
        }
    }

    /**
     * Handles user joined messages
     */
    handleUserJoined(data, senderId) {
        console.log('👋 User joined slot:', data.slotId, 'participant:', data.participantData?.character_name);
        
        // Update slot occupancy
        this.roomWebRTC.slotOccupants.set(data.slotId, {
            peerId: senderId,
            participantData: data.participantData,
            isLocal: false
        });

        // If we're also in a slot, initiate WebRTC connection
        // Fix #2: Prevent offer "glare" - only lower peerId initiates
        if (this.roomWebRTC.isJoined && this.roomWebRTC.currentSlotId && this.roomWebRTC.currentSlotId !== data.slotId) {
            const currentPeerId = this.roomWebRTC.ablyManager.getCurrentPeerId();
            if (currentPeerId && currentPeerId < senderId) {
                this.roomWebRTC.peerConnectionManager.initiateWebRTCConnection(senderId);
            }
        }
    }

    /**
     * Handles user left messages
     */
    handleUserLeft(data, senderId) {
        console.log('👋 User left slot:', data.slotId);
        
        // Remove from slot occupancy
        this.roomWebRTC.slotOccupants.delete(data.slotId);
        
        // Close peer connection if exists
        this.roomWebRTC.peerConnectionManager.cleanupPeerConnection(senderId);
    }

    /**
     * Handles WebRTC offer messages
     */
    handleOffer(data, senderId) {
        this.roomWebRTC.peerConnectionManager.handleOffer(data, senderId);
    }

    /**
     * Handles WebRTC answer messages
     */
    handleAnswer(data, senderId) {
        this.roomWebRTC.peerConnectionManager.handleAnswer(data, senderId);
    }

    /**
     * Handles ICE candidate messages
     */
    handleIceCandidate(data, senderId) {
        this.roomWebRTC.peerConnectionManager.handleIceCandidate(data, senderId);
    }
}
