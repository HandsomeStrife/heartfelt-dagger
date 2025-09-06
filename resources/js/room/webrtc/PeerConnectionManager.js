/**
 * PeerConnectionManager - Manages WebRTC peer connections
 * 
 * Handles RTCPeerConnection instances, connection lifecycle, offer/answer exchange,
 * ICE candidate handling, and peer-to-peer connection coordination.
 */
export class PeerConnectionManager {
    constructor(roomWebRTC) {
        this.roomWebRTC = roomWebRTC;
        this.peerConnections = new Map(); // Map of peerId -> RTCPeerConnection
        this.pendingIce = new Map(); // Map<peerId, RTCIceCandidateInit[]>
        this.connectionStates = new Map(); // Map of peerId -> connection state
        
        this.setupEventHandlers();
    }

    /**
     * Sets up event handlers for peer connection management
     */
    setupEventHandlers() {
        // Listen for ICE config updates
        this.roomWebRTC.iceManager.onConfigUpdate((config) => {
            this.updateAllPeerConnections(config);
        });
    }

    /**
     * Creates and configures a new peer connection
     */
    createPeerConnection(peerId) {
        console.log(`🔗 Creating peer connection for: ${peerId}`);
        
        // Use ICE configuration from ICE manager
        const peerConnection = new RTCPeerConnection({
            ...this.roomWebRTC.iceManager.getConfig(),
            // IMPORTANT: Do NOT set iceTransportPolicy:'relay' to allow natural candidate preference
        });
        
        this.peerConnections.set(peerId, peerConnection);
        this.connectionStates.set(peerId, 'new');
        
        // Add local stream tracks if available
        if (this.roomWebRTC.localStream) {
            this.roomWebRTC.localStream.getTracks().forEach(track => {
                peerConnection.addTrack(track, this.roomWebRTC.localStream);
            });
        }
        
        this.setupPeerConnectionEventHandlers(peerConnection, peerId);
        
        // If ICE config isn't ready yet, update this connection when it arrives
        if (!this.roomWebRTC.iceManager.isReady()) {
            this.roomWebRTC.iceManager.onConfigUpdate(() => {
                this.roomWebRTC.iceManager.updatePeerConnection(peerConnection, peerId);
            });
        }
        
        return peerConnection;
    }

    /**
     * Sets up event handlers for a specific peer connection
     */
    setupPeerConnectionEventHandlers(peerConnection, peerId) {
        // Handle ICE candidates
        peerConnection.onicecandidate = (event) => {
            if (event.candidate) {
                console.log(`🧊 ICE candidate for ${peerId}:`, event.candidate);
                this.roomWebRTC.ablyManager.sendIceCandidate(peerId, event.candidate);
            } else {
                console.log(`🧊 ICE gathering complete for ${peerId}`);
            }
        };

        // Handle remote stream
        peerConnection.ontrack = (event) => {
            console.log(`🎥 Remote stream received from ${peerId}:`, event.streams[0]);
            this.handleRemoteStream(peerId, event.streams[0]);
        };

        // Monitor ICE connection state
        peerConnection.oniceconnectionstatechange = () => {
            const state = peerConnection.iceConnectionState;
            console.log(`🧊 ICE connection state: ${state} (${peerId})`);
            
            this.connectionStates.set(peerId, state);
            
            if (state === 'failed' || state === 'disconnected') {
                this.handleConnectionFailure(peerId, state);
            } else if (state === 'connected' || state === 'completed') {
                this.handleConnectionSuccess(peerId, state);
            }
        };

        // Monitor connection state for cleanup and telemetry
        peerConnection.onconnectionstatechange = async () => {
            const state = peerConnection.connectionState;
            console.log(`🔗 Peer connection state: ${state} (${peerId})`);
            
            // Log candidate pair information when connected for telemetry
            if (state === 'connected' || state === 'completed') {
                this.roomWebRTC.iceManager.logCandidatePairStats(peerConnection, peerId);
            }
            
            // Handle disconnections with retry logic
            if (state === 'disconnected') {
                this.handleDisconnection(peerId, peerConnection);
            } else if (state === 'failed' || state === 'closed') {
                this.cleanupPeerConnection(peerId);
            }
        };

        // Handle data channel (if needed for future features)
        peerConnection.ondatachannel = (event) => {
            console.log(`📡 Data channel received from ${peerId}:`, event.channel);
            this.setupDataChannel(event.channel, peerId);
        };
    }

    /**
     * Handles remote stream from a peer
     */
    handleRemoteStream(peerId, stream) {
        // Find the slot for this peer
        const slotData = Array.from(this.roomWebRTC.slotOccupants.entries())
            .find(([slotId, occupant]) => occupant.peerId === peerId);
        
        if (slotData) {
            const [slotId, occupant] = slotData;
            
            // Update slot occupant with stream
            occupant.stream = stream;
            
            // Update slot display with video stream
            this.roomWebRTC.slotManager.updateSlotOccupied(slotId, occupant.participantData, stream);
            
            console.log(`🎥 Remote stream connected for ${peerId} in slot ${slotId}`);
        } else {
            console.warn(`🎥 No slot found for peer ${peerId} with remote stream`);
        }
    }

    /**
     * Handles connection success
     */
    handleConnectionSuccess(peerId, state) {
        console.log(`✅ Connection established with ${peerId}: ${state}`);
        
        // Clear any pending reconnection attempts
        const peerConnection = this.peerConnections.get(peerId);
        if (peerConnection && peerConnection._disconnectTimeout) {
            clearTimeout(peerConnection._disconnectTimeout);
            delete peerConnection._disconnectTimeout;
        }
    }

    /**
     * Handles connection failure
     */
    handleConnectionFailure(peerId, state) {
        console.warn(`⚠️ Connection issue with ${peerId}: ${state}`);
        
        // For ICE failures, we might want to restart ICE or recreate the connection
        if (state === 'failed') {
            console.log(`🔄 Attempting to restart ICE for ${peerId}`);
            this.restartIce(peerId);
        }
    }

    /**
     * Handles disconnection with retry logic
     */
    handleDisconnection(peerId, peerConnection) {
        // Clear any existing timeout
        if (peerConnection._disconnectTimeout) {
            clearTimeout(peerConnection._disconnectTimeout);
        }
        
        // Set a timeout to cleanup if connection doesn't recover
        peerConnection._disconnectTimeout = setTimeout(() => {
            if (peerConnection.connectionState === 'disconnected') {
                console.warn(`🔌 Connection to ${peerId} remained disconnected, cleaning up`);
                this.cleanupPeerConnection(peerId);
            }
        }, 10000); // 10 second timeout
    }

    /**
     * Restarts ICE for a specific peer connection
     */
    async restartIce(peerId) {
        const peerConnection = this.peerConnections.get(peerId);
        if (!peerConnection) return;
        
        try {
            await peerConnection.restartIce();
            console.log(`🔄 ICE restart initiated for ${peerId}`);
        } catch (error) {
            console.error(`🔄 Failed to restart ICE for ${peerId}:`, error);
            // If restart fails, cleanup and let the peer reconnect
            this.cleanupPeerConnection(peerId);
        }
    }

    /**
     * Sets up data channel for future features
     */
    setupDataChannel(channel, peerId) {
        channel.onopen = () => {
            console.log(`📡 Data channel opened with ${peerId}`);
        };
        
        channel.onmessage = (event) => {
            console.log(`📡 Data channel message from ${peerId}:`, event.data);
            // Handle data channel messages (future feature)
        };
        
        channel.onclose = () => {
            console.log(`📡 Data channel closed with ${peerId}`);
        };
        
        channel.onerror = (error) => {
            console.error(`📡 Data channel error with ${peerId}:`, error);
        };
    }

    /**
     * Initiates WebRTC connection with a peer
     */
    async initiateConnection(peerId) {
        console.log(`🤝 Initiating WebRTC connection with: ${peerId}`);
        
        try {
            const peerConnection = this.createPeerConnection(peerId);
            
            // Create and send offer
            const offer = await peerConnection.createOffer({
                offerToReceiveAudio: true,
                offerToReceiveVideo: true
            });
            
            await peerConnection.setLocalDescription(offer);
            
            // Send offer via Ably
            await this.roomWebRTC.ablyManager.sendOffer(peerId, offer);
            
            console.log(`📤 Offer sent to ${peerId}`);
        } catch (error) {
            console.error(`❌ Failed to initiate connection with ${peerId}:`, error);
            this.cleanupPeerConnection(peerId);
        }
    }

    /**
     * Handles incoming WebRTC offer
     */
    async handleOffer(data, senderId) {
        console.log(`📥 Received offer from: ${senderId}`);
        
        try {
            let peerConnection = this.peerConnections.get(senderId);
            
            // Create connection if it doesn't exist
            if (!peerConnection) {
                peerConnection = this.createPeerConnection(senderId);
            }
            
            // Set remote description
            await peerConnection.setRemoteDescription(new RTCSessionDescription(data.offer));
            
            // Process any queued ICE candidates
            await this.processQueuedIceCandidates(senderId);
            
            // Create and send answer
            const answer = await peerConnection.createAnswer();
            await peerConnection.setLocalDescription(answer);
            
            // Send answer via Ably
            await this.roomWebRTC.ablyManager.sendAnswer(senderId, answer);
            
            console.log(`📤 Answer sent to ${senderId}`);
        } catch (error) {
            console.error(`❌ Failed to handle offer from ${senderId}:`, error);
            this.cleanupPeerConnection(senderId);
        }
    }

    /**
     * Handles incoming WebRTC answer
     */
    async handleAnswer(data, senderId) {
        console.log(`📥 Received answer from: ${senderId}`);
        
        try {
            const peerConnection = this.peerConnections.get(senderId);
            if (!peerConnection) {
                console.warn(`❌ No peer connection found for answer from: ${senderId}`);
                return;
            }
            
            // Set remote description
            await peerConnection.setRemoteDescription(new RTCSessionDescription(data.answer));
            
            // Process any queued ICE candidates
            await this.processQueuedIceCandidates(senderId);
            
            console.log(`✅ Answer processed from ${senderId}`);
        } catch (error) {
            console.error(`❌ Failed to handle answer from ${senderId}:`, error);
            this.cleanupPeerConnection(senderId);
        }
    }

    /**
     * Handles incoming ICE candidate
     */
    async handleIceCandidate(data, senderId) {
        console.log(`🧊 Received ICE candidate from: ${senderId}`);
        
        const peerConnection = this.peerConnections.get(senderId);
        
        if (!peerConnection) {
            console.log(`🧊 Queueing ICE candidate for ${senderId} (no connection yet)`);
            this.queueIceCandidate(senderId, data.candidate);
            return;
        }
        
        if (peerConnection.remoteDescription) {
            try {
                await peerConnection.addIceCandidate(new RTCIceCandidate(data.candidate));
                console.log(`🧊 ICE candidate added for ${senderId}`);
            } catch (error) {
                console.error(`🧊 Failed to add ICE candidate for ${senderId}:`, error);
            }
        } else {
            console.log(`🧊 Queueing ICE candidate for ${senderId} (no remote description)`);
            this.queueIceCandidate(senderId, data.candidate);
        }
    }

    /**
     * Queues ICE candidate for later processing
     */
    queueIceCandidate(peerId, candidate) {
        if (!this.pendingIce.has(peerId)) {
            this.pendingIce.set(peerId, []);
        }
        this.pendingIce.get(peerId).push(candidate);
    }

    /**
     * Processes queued ICE candidates
     */
    async processQueuedIceCandidates(peerId) {
        const candidates = this.pendingIce.get(peerId);
        if (!candidates || candidates.length === 0) return;
        
        const peerConnection = this.peerConnections.get(peerId);
        if (!peerConnection || !peerConnection.remoteDescription) return;
        
        console.log(`🧊 Processing ${candidates.length} queued ICE candidates for ${peerId}`);
        
        for (const candidate of candidates) {
            try {
                await peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
            } catch (error) {
                console.error(`🧊 Failed to add queued ICE candidate for ${peerId}:`, error);
            }
        }
        
        // Clear the queue
        this.pendingIce.delete(peerId);
    }

    /**
     * Updates all peer connections with new ICE configuration
     */
    updateAllPeerConnections(config) {
        if (this.peerConnections.size === 0) return;
        
        console.log('🧊 Updating all peer connections with new ICE configuration');
        
        this.peerConnections.forEach((connection, peerId) => {
            this.roomWebRTC.iceManager.updatePeerConnection(connection, peerId);
        });
    }

    /**
     * Cleans up a peer connection and associated resources
     */
    cleanupPeerConnection(peerId) {
        console.log(`🧹 Cleaning up peer connection: ${peerId}`);
        
        const connection = this.peerConnections.get(peerId);
        if (connection) {
            // Clear any timeouts
            if (connection._disconnectTimeout) {
                clearTimeout(connection._disconnectTimeout);
                delete connection._disconnectTimeout;
            }
            
            // Close connection
            connection.close();
            this.peerConnections.delete(peerId);
        }
        
        // Clear connection state
        this.connectionStates.delete(peerId);
        
        // Clear pending ICE candidates
        this.pendingIce.delete(peerId);
        
        console.log(`✅ Peer connection cleaned up: ${peerId}`);
    }

    /**
     * Cleans up all peer connections
     */
    cleanupAllConnections() {
        console.log('🧹 Cleaning up all peer connections');
        
        this.peerConnections.forEach((connection, peerId) => {
            this.cleanupPeerConnection(peerId);
        });
        
        this.peerConnections.clear();
        this.connectionStates.clear();
        this.pendingIce.clear();
    }

    /**
     * Gets connection state for a specific peer
     */
    getConnectionState(peerId) {
        return this.connectionStates.get(peerId) || 'unknown';
    }

    /**
     * Gets all active peer connections
     */
    getActivePeers() {
        return Array.from(this.peerConnections.keys());
    }

    /**
     * Checks if connected to a specific peer
     */
    isConnectedToPeer(peerId) {
        const state = this.getConnectionState(peerId);
        return state === 'connected' || state === 'completed';
    }

    /**
     * Gets connection statistics for monitoring
     */
    async getConnectionStats() {
        const stats = {};
        
        for (const [peerId, connection] of this.peerConnections) {
            try {
                const rtcStats = await connection.getStats();
                stats[peerId] = {
                    connectionState: connection.connectionState,
                    iceConnectionState: connection.iceConnectionState,
                    stats: rtcStats
                };
            } catch (error) {
                console.error(`Failed to get stats for ${peerId}:`, error);
                stats[peerId] = { error: error.message };
            }
        }
        
        return stats;
    }

    /**
     * Destroys the peer connection manager
     */
    destroy() {
        this.cleanupAllConnections();
        console.log('🔗 PeerConnectionManager destroyed');
    }
}
