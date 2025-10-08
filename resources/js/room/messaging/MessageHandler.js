/**
 * MessageHandler - Handles incoming Ably messages for WebRTC signaling
 * 
 * Processes different types of room messages including user presence,
 * WebRTC offers/answers, and ICE candidates.
 * 
 * MEDIUM FIX: Applies debouncing to rapid message updates
 */

import { debounce } from '../utils/debounce';

export class MessageHandler {
    constructor(roomWebRTC) {
        this.roomWebRTC = roomWebRTC;
        
        // MEDIUM FIX: Debounced handlers for rapid updates (250ms delay)
        this.debouncedHandleCountdownUpdate = debounce(this._handleCountdownUpdate.bind(this), 250);
        this.debouncedHandleMarkerCreated = debounce(this._handleSessionMarkerCreated.bind(this), 250);
        
        // CRITICAL FIX: Track connection fallback timeouts to prevent orphaned timers
        this.connectionFallbackTimeouts = new Map(); // Map<peerId, timeoutId>
    }

    /**
     * Main message handler that routes messages to appropriate handlers
     */
    handleAblyMessage(message) {
        const { type, data, senderId, targetPeerId } = message.data;

        // Defensive: double-check targeting
        if (targetPeerId && targetPeerId !== this.roomWebRTC.signalingManager.getCurrentPeerId()) return;

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
            // NOTE: WebRTC signaling (offer/answer/ICE) handled internally by PeerJS
            // No need for explicit handlers - PeerJS manages this automatically
            case 'fear-updated':
                this.handleFearUpdate(data, senderId);
                break;
            case 'countdown-updated':
                // MEDIUM FIX: Use debounced handler
                this.debouncedHandleCountdownUpdate(data, senderId);
                break;
            case 'countdown-deleted':
                this.handleCountdownDeleted(data, senderId);
                break;
            case 'gm-presence-changed':
                this.handleGmPresenceChanged(data, senderId);
                break;
            case 'session-marker-created':
                // MEDIUM FIX: Use debounced handler
                this.debouncedHandleMarkerCreated(data, senderId);
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
            this.roomWebRTC.signalingManager.publishToAbly('user-joined', {
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

        const currentPeerId = this.roomWebRTC.signalingManager.getCurrentPeerId();

        // If we're also in a slot, initiate WebRTC connection using SimplePeerManager
        if (this.roomWebRTC.isJoined && this.roomWebRTC.currentSlotId && this.roomWebRTC.currentSlotId !== data.slotId) {
            // Use lexicographic ordering to prevent simultaneous connection attempts
            // Only initiate if our peer ID is greater (matches room-webrtc.js pattern)
            if (currentPeerId && currentPeerId > senderId) {
                console.log(`🤝 Initiating PeerJS connection: ${currentPeerId} -> ${senderId} (lexicographic ordering)`);
                this.roomWebRTC.simplePeerManager.callPeer(senderId);
            } else {
                console.log(`⏳ Waiting for incoming PeerJS call from: ${senderId} (lexicographic ordering)`);
                
                // CRITICAL FIX: Track fallback timeout so it can be cleaned up
                // Clear any existing timeout for this peer
                if (this.connectionFallbackTimeouts.has(senderId)) {
                    clearTimeout(this.connectionFallbackTimeouts.get(senderId));
                }
                
                // FALLBACK: If no connection after 2 seconds, initiate anyway (VDO.Ninja pattern)
                const timeoutId = setTimeout(() => {
                    // Validate that we're still in the room and the conditions still apply
                    if (!this.roomWebRTC.isJoined || !this.roomWebRTC.currentSlotId) {
                        console.log(`⏳ Fallback cancelled: user no longer in room`);
                        this.connectionFallbackTimeouts.delete(senderId);
                        return;
                    }
                    
                    const isConnected = this.roomWebRTC.simplePeerManager.isConnectedTo(senderId);
                    if (!isConnected) {
                        console.log(`🔄 Fallback: ${currentPeerId} -> ${senderId} (timeout override)`);
                        this.roomWebRTC.simplePeerManager.callPeer(senderId);
                    } else {
                        console.log(`✅ Connection already established with ${senderId}, fallback not needed`);
                    }
                    
                    // Clean up the timeout reference
                    this.connectionFallbackTimeouts.delete(senderId);
                }, 2000);
                
                this.connectionFallbackTimeouts.set(senderId, timeoutId);
            }
        }
        
        // Show character overlay immediately when someone joins
        this.roomWebRTC.slotManager.showCharacterOverlay(
            document.querySelector(`[data-slot-id="${data.slotId}"]`), 
            data.participantData
        );
    }

    /**
     * Handles user left messages
     */
    handleUserLeft(data, senderId) {
        console.log('👋 User left slot:', data.slotId);
        
        // CRITICAL FIX: Clear any pending fallback timeouts for this peer
        this.clearConnectionFallbackTimeout(senderId);
        
        // Remove from slot occupancy
        this.roomWebRTC.slotOccupants.delete(data.slotId);
        
        // Close peer connection if exists (SimplePeerManager will handle cleanup)
        this.roomWebRTC.simplePeerManager.closeCall(senderId);
    }
    
    /**
     * CRITICAL FIX: Clears connection fallback timeout for a specific peer
     */
    clearConnectionFallbackTimeout(peerId) {
        if (this.connectionFallbackTimeouts.has(peerId)) {
            clearTimeout(this.connectionFallbackTimeouts.get(peerId));
            this.connectionFallbackTimeouts.delete(peerId);
            console.log(`🧹 Cleared fallback timeout for peer: ${peerId}`);
        }
    }
    
    /**
     * CRITICAL FIX: Clears all connection fallback timeouts
     * Call this when leaving the room or during cleanup
     */
    clearAllConnectionFallbackTimeouts() {
        console.log(`🧹 Clearing ${this.connectionFallbackTimeouts.size} fallback timeouts`);
        this.connectionFallbackTimeouts.forEach((timeoutId, peerId) => {
            clearTimeout(timeoutId);
        });
        this.connectionFallbackTimeouts.clear();
    }

    // WebRTC signaling (offer/answer/ICE) is handled internally by PeerJS
    // These methods are no longer needed with SimplePeerManager

    /**
     * Handles fear level update messages
     */
    handleFearUpdate(data, senderId) {
        console.log('🎭 Fear level updated via Ably:', data, 'from:', senderId);
        
        if (this.roomWebRTC.fearCountdownManager) {
            this.roomWebRTC.fearCountdownManager.handleFearUpdate(data);
        }
    }

    /**
     * MEDIUM FIX: Internal handler for countdown updates (called via debounce)
     */
    _handleCountdownUpdate(data, senderId) {
        console.log('🎭 Countdown tracker updated via Ably (debounced):', data, 'from:', senderId);
        
        if (this.roomWebRTC.fearCountdownManager) {
            this.roomWebRTC.fearCountdownManager.handleCountdownUpdate(data);
        }
    }

    /**
     * Handles countdown tracker deletion messages
     */
    handleCountdownDeleted(data, senderId) {
        console.log('🎭 Countdown tracker deleted via Ably:', data, 'from:', senderId);
        
        if (this.roomWebRTC.fearCountdownManager) {
            this.roomWebRTC.fearCountdownManager.handleCountdownDeletion(data);
        }
    }

    /**
     * Handles GM presence change messages
     */
    handleGmPresenceChanged(data, senderId) {
        console.log('🎭 GM presence changed via Ably:', data, 'from:', senderId);
        
        if (this.roomWebRTC.fearCountdownManager) {
            this.roomWebRTC.fearCountdownManager.handleGmPresenceChanged(data);
        }
    }

    /**
     * MEDIUM FIX: Internal handler for marker creation (called via debounce)
     */
    _handleSessionMarkerCreated(data, senderId) {
        console.log('🏷️ Session marker created via Ably (debounced):', data, 'from:', senderId);
        
        // Don't show notification for markers we created ourselves
        if (data.creator_id === this.roomWebRTC.currentUserId) {
            console.log('🏷️ Ignoring our own marker creation');
            return;
        }
        
        if (this.roomWebRTC.markerManager) {
            this.roomWebRTC.markerManager.handleMarkerAblyMessage(data);
        }
    }
}
