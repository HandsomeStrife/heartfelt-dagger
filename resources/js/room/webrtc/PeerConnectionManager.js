/**
 * PeerConnectionManager - Manages WebRTC peer connections
 * 
 * Handles creation, configuration, and lifecycle management of RTCPeerConnection
 * instances for peer-to-peer communication.
 */

export class PeerConnectionManager {
    constructor(roomWebRTC) {
        this.roomWebRTC = roomWebRTC;
        this.peerConnections = new Map(); // Map of peerId -> RTCPeerConnection
        this.pendingIce = new Map(); // Map<peerId, RTCIceCandidateInit[]>
    }

    /**
     * Creates and configures a new peer connection with common setup
     */
    createPeerConnection(peerId) {
        // Use loaded ICE configuration, with fallback update if not ready yet
        const iceConfig = this.roomWebRTC.iceManager.getIceConfig();
        const peerConnection = new RTCPeerConnection({
            ...iceConfig,
            // IMPORTANT: Do NOT set iceTransportPolicy:'relay' to allow natural candidate preference
            // iceCandidatePoolSize: 1, // Optional: pre-gather candidates
        });
        
        this.peerConnections.set(peerId, peerConnection);
        
        // If ICE config isn't ready yet, update this connection when it arrives
        if (!this.roomWebRTC.iceManager.isReady()) {
            this.roomWebRTC.iceManager.updatePeerConnection(peerId, peerConnection);
        }

        // Add local stream tracks (only skip for viewers, participants always share)
        if (this.roomWebRTC.roomData.viewer_mode) {
            console.log('👁️ Viewer mode: Creating receive-only connection for', peerId);
        } else {
            const localStream = this.roomWebRTC.mediaManager.getLocalStream();
            if (localStream) {
                localStream.getTracks().forEach(track => {
                    peerConnection.addTrack(track, localStream);
                });
                console.log('📡 Added local tracks to connection for', peerId);
            } else {
                console.log('⚠️ No local stream available to share with', peerId);
            }
        }

        // Handle remote stream
        peerConnection.ontrack = (event) => {
            console.log('📡 Received remote stream from:', peerId);
            console.log('📡 Stream details:', {
                streamId: event.streams[0]?.id,
                trackCount: event.streams[0]?.getTracks().length,
                audioTracks: event.streams[0]?.getAudioTracks().length,
                videoTracks: event.streams[0]?.getVideoTracks().length
            });
            this.handleRemoteStream(event.streams[0], peerId);
        };

        // Handle ICE candidates with basic rate limiting
        let lastCandidateTime = 0;
        peerConnection.onicecandidate = (event) => {
            if (event.candidate) {
                const now = Date.now();
                // Basic rate limiting: max 10 candidates per second
                if (now - lastCandidateTime < 100) {
                    console.log(`🧊 Rate limiting ICE candidate for ${peerId}`);
                    return;
                }
                lastCandidateTime = now;
                
                console.log(`🧊 Sending ICE candidate to ${peerId}:`, event.candidate.type);
                this.roomWebRTC.ablyManager.publishToAbly('webrtc-ice-candidate', {
                    candidate: event.candidate
                }, peerId);
            } else {
                console.log(`🧊 ICE candidate gathering complete for ${peerId}`);
            }
        };

        // Monitor ICE connection state
        peerConnection.oniceconnectionstatechange = () => {
            const iceState = peerConnection.iceConnectionState;
            console.log(`🧊 ICE connection state for ${peerId}: ${iceState}`);
        };

        // Monitor connection state for cleanup and telemetry
        peerConnection.onconnectionstatechange = async () => {
            const state = peerConnection.connectionState;
            console.log(`🔗 Peer connection state: ${state} (${peerId})`);
            
            // Log candidate pair information when connected for telemetry
            if (state === 'connected' || state === 'completed') {
                this.logCandidatePairStats(peerConnection, peerId);
            }
            
            // Fix #6: Be less aggressive on transient "disconnected"
            if (state === 'disconnected') {
                // Clear any existing timeout
                clearTimeout(peerConnection._disconnectTimeout);
                // Delay cleanup to allow for reconnection
                peerConnection._disconnectTimeout = setTimeout(() => {
                    if (peerConnection.connectionState === 'disconnected') {
                        console.log(`🔗 Cleaning up peer connection after timeout: ${peerId}`);
                        this.cleanupPeerConnection(peerId);
                    }
                }, 4000); // 4 second delay
            } else if (['failed', 'closed'].includes(state)) {
                console.log(`🔗 Cleaning up peer connection: ${peerId}`);
                this.cleanupPeerConnection(peerId);
            } else if (state === 'connected') {
                // Clear disconnect timeout if we reconnect
                clearTimeout(peerConnection._disconnectTimeout);
            }
        };

        return peerConnection;
    }

    /**
     * Initiates a WebRTC connection by sending an offer to a remote peer
     */
    async initiateWebRTCConnection(remotePeerId) {
        console.log('🤝 Initiating WebRTC connection with:', remotePeerId);
        
        // Prevent duplicate connections
        if (this.peerConnections.has(remotePeerId)) {
            console.log('⚠️ Connection already exists for:', remotePeerId);
            return;
        }
        
        try {
            const peerConnection = this.createPeerConnection(remotePeerId);
            
            // Create and send offer
            console.log('📋 Creating offer for:', remotePeerId);
            const offerOptions = this.roomWebRTC.roomData.viewer_mode ? 
                { offerToReceiveAudio: true, offerToReceiveVideo: true } : {};
            const offer = await peerConnection.createOffer(offerOptions);
            console.log('📋 Setting local description for:', remotePeerId);
            await peerConnection.setLocalDescription(offer);
            console.log('📋 Local description set, publishing offer to:', remotePeerId);
            console.log('🔍 Peer connection state after offer:', peerConnection.connectionState);
            console.log('🔍 ICE gathering state after offer:', peerConnection.iceGatheringState);
            console.log('🔍 ICE connection state after offer:', peerConnection.iceConnectionState);
            
            // Add timeout for connection establishment
            setTimeout(() => {
                if (peerConnection.connectionState === 'new' || peerConnection.connectionState === 'connecting') {
                    console.warn(`⏰ WebRTC connection timeout for ${remotePeerId}, state: ${peerConnection.connectionState}`);
                }
            }, 15000); // 15 second timeout
            
            this.roomWebRTC.ablyManager.publishToAbly('webrtc-offer', { offer }, remotePeerId);
            
        } catch (error) {
            this.handleWebRTCError('Failed to initiate connection', error, remotePeerId);
        }
    }

    /**
     * Handles incoming WebRTC offer by creating answer
     */
    async handleOffer(data, senderId) {
        console.log('📞 Received WebRTC offer from:', senderId);

        try {
            const peerConnection = this.createPeerConnection(senderId);
            
            // Set remote description and create answer
            console.log('📋 Setting remote description for:', senderId);
            await peerConnection.setRemoteDescription(data.offer);
            console.log('📋 Remote description set for:', senderId);
            
            // Fix #3: Drain queued ICE candidates after setting remote description
            const drain = this.pendingIce.get(senderId);
            if (drain && drain.length) {
                console.log(`🧊 Draining ${drain.length} queued ICE candidates for ${senderId}`);
                for (const candidate of drain) {
                    try {
                        await peerConnection.addIceCandidate(candidate);
                    } catch (error) {
                        console.warn('Failed to add queued ICE candidate:', error);
                    }
                }
                this.pendingIce.delete(senderId);
            }
            
            console.log('📋 Creating answer for:', senderId);
            const answer = await peerConnection.createAnswer();
            console.log('📋 Setting local description for answer:', senderId);
            await peerConnection.setLocalDescription(answer);
            console.log('📋 Answer local description set, publishing answer to:', senderId);
            console.log('🔍 Peer connection state after answer:', peerConnection.connectionState);
            console.log('🔍 ICE gathering state after answer:', peerConnection.iceGatheringState);
            console.log('🔍 ICE connection state after answer:', peerConnection.iceConnectionState);

            this.roomWebRTC.ablyManager.publishToAbly('webrtc-answer', { answer }, senderId);
            
        } catch (error) {
            this.handleWebRTCError('Failed to handle offer', error, senderId);
        }
    }

    /**
     * Handles incoming WebRTC answer
     */
    async handleAnswer(data, senderId) {
        console.log('✅ Received WebRTC answer from:', senderId);

        try {
            const peerConnection = this.peerConnections.get(senderId);
            if (peerConnection) {
                console.log('📋 Setting remote description for answer:', senderId);
                await peerConnection.setRemoteDescription(data.answer);
                console.log('📋 Answer remote description set for:', senderId);
                
                // Fix #3: Drain queued ICE candidates after setting remote description
                const drain = this.pendingIce.get(senderId);
                if (drain && drain.length) {
                    console.log(`🧊 Draining ${drain.length} queued ICE candidates for ${senderId}`);
                    for (const candidate of drain) {
                        try {
                            await peerConnection.addIceCandidate(candidate);
                        } catch (error) {
                            console.warn('Failed to add queued ICE candidate:', error);
                        }
                    }
                    this.pendingIce.delete(senderId);
                }
            } else {
                console.warn(`⚠️ No peer connection found for ${senderId}`);
            }
        } catch (error) {
            this.handleWebRTCError('Failed to handle answer', error, senderId);
        }
    }

    /**
     * Handles incoming ICE candidates for peer connections
     */
    async handleIceCandidate(data, senderId) {
        try {
            console.log(`🧊 Received ICE candidate from ${senderId}:`, data.candidate.type);
            const peerConnection = this.peerConnections.get(senderId);
            if (!peerConnection) {
                console.warn(`⚠️ No peer connection found for ICE candidate from ${senderId}`);
                return;
            }

            // Fix #3: Queue ICE candidates until remote description is set
            if (!peerConnection.remoteDescription) {
                const queue = this.pendingIce.get(senderId) || [];
                queue.push(data.candidate);
                this.pendingIce.set(senderId, queue);
                console.log(`🧊 Queued ICE candidate for ${senderId} (${queue.length} pending)`);
                return;
            }

            await peerConnection.addIceCandidate(data.candidate);
            console.log(`🧊 Added ICE candidate from ${senderId}`);
        } catch (error) {
            this.handleWebRTCError('Failed to handle ICE candidate', error, senderId);
        }
    }

    /**
     * Handles remote stream from peer connection
     */
    handleRemoteStream(stream, senderId) {
        console.log('📺 Setting up remote video for peer:', senderId);

        // Find which slot this peer occupies
        let targetSlotId = null;
        for (const [slotId, occupant] of this.roomWebRTC.slotOccupants) {
            if (occupant.peerId === senderId) {
                targetSlotId = slotId;
                break;
            }
        }

        if (targetSlotId) {
            const slotContainer = document.querySelector(`[data-slot-id="${targetSlotId}"]`);
            if (slotContainer) {
                const participantData = this.roomWebRTC.slotOccupants.get(targetSlotId).participantData;
                this.roomWebRTC.mediaManager.setupRemoteVideo(slotContainer, stream, participantData);
                this.roomWebRTC.slotManager.showCharacterOverlay(slotContainer, participantData);
            }
        }
    }

    /**
     * Logs candidate pair statistics for telemetry and monitoring
     */
    async logCandidatePairStats(peerConnection, peerId) {
        try {
            const stats = await peerConnection.getStats();
            
            stats.forEach(report => {
                if (report.type === 'candidate-pair' && report.selected) {
                    // Find the local and remote candidate details
                    let localCandidate = null;
                    let remoteCandidate = null;
                    
                    stats.forEach(candidateReport => {
                        if (candidateReport.id === report.localCandidateId) {
                            localCandidate = candidateReport;
                        }
                        if (candidateReport.id === report.remoteCandidateId) {
                            remoteCandidate = candidateReport;
                        }
                    });
                    
                    const connectionType = {
                        local: localCandidate?.candidateType || 'unknown',
                        remote: remoteCandidate?.candidateType || 'unknown',
                        localProtocol: localCandidate?.protocol || 'unknown',
                        remoteProtocol: remoteCandidate?.protocol || 'unknown'
                    };
                    
                    // Determine if TURN is being used
                    const usingTURN = localCandidate?.candidateType === 'relay' || 
                                     remoteCandidate?.candidateType === 'relay';
                    
                    console.log(`🔗 Connection established for ${peerId}:`, {
                        usingTURN,
                        connectionType,
                        localAddress: localCandidate?.address,
                        remoteAddress: remoteCandidate?.address,
                        bytesReceived: report.bytesReceived,
                        bytesSent: report.bytesSent
                    });
                    
                    // Send telemetry beacon (optional - can be used for analytics)
                    this.sendConnectionTelemetry({
                        peerId,
                        roomId: this.roomWebRTC.roomData.id,
                        usingTURN,
                        localType: connectionType.local,
                        remoteType: connectionType.remote,
                        protocol: connectionType.localProtocol
                    });
                }
            });
        } catch (error) {
            console.warn(`🔗 Failed to get candidate pair stats for ${peerId}:`, error);
        }
    }

    /**
     * Sends connection telemetry for monitoring TURN usage
     */
    sendConnectionTelemetry(data) {
        // Optional: Send tiny beacon to analytics endpoint
        // This helps track when TURN is actually being used vs STUN-only connections
        try {
            // Use sendBeacon for non-blocking telemetry
            if (navigator.sendBeacon) {
                const payload = JSON.stringify({
                    type: 'webrtc_connection',
                    data: data,
                    timestamp: Date.now()
                });
                
                navigator.sendBeacon('/api/telemetry/webrtc', payload);
            }
        } catch (error) {
            // Silently fail - telemetry is optional
            console.debug('📊 Telemetry beacon failed (non-critical):', error);
        }
    }

    /**
     * Cleans up a peer connection and associated resources
     */
    cleanupPeerConnection(peerId) {
        const connection = this.peerConnections.get(peerId);
        if (connection) {
            // Clear any disconnect timeout
            clearTimeout(connection._disconnectTimeout);
            connection.close();
            this.peerConnections.delete(peerId);
        }
        
        // Clean up any pending ICE candidates
        this.pendingIce.delete(peerId);
        
        this.roomWebRTC.mediaManager.clearRemoteVideo(peerId);
    }

    /**
     * Standardized WebRTC error handling
     */
    handleWebRTCError(message, error, peerId) {
        console.error(`❌ WebRTC Error - ${message} (${peerId}):`, error);
        
        // Clean up connection on error
        if (peerId) {
            this.cleanupPeerConnection(peerId);
        }
    }

    /**
     * Closes all peer connections
     */
    closeAllConnections() {
        this.peerConnections.forEach(pc => {
            pc.close();
        });
        this.peerConnections.clear();
        this.pendingIce.clear();
    }

    /**
     * Gets the peer connections map
     */
    getPeerConnections() {
        return this.peerConnections;
    }

    /**
     * Checks if there's an active connection to a peer
     */
    hasActiveConnection(peerId) {
        const connection = this.peerConnections.get(peerId);
        return connection && (
            connection.connectionState === 'connected' || 
            connection.connectionState === 'connecting'
        );
    }
}
