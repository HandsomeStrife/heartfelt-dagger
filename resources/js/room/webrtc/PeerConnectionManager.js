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
        this.connectionRetryTimers = new Map(); // Track retry attempts
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
            iceCandidatePoolSize: 0, // Don't pre-gather candidates (VDO.Ninja pattern)
            bundlePolicy: 'max-bundle', // Optimize media bundling (WebRTC best practice)
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

        // Handle ICE candidates - send immediately (VDO.Ninja pattern: no batching)
        this.setupIceCandidateHandler(peerConnection, peerId);

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
            
            // Handle transient "disconnected" with grace period (consistent with ICE state handling)
            if (state === 'disconnected') {
                // Clear any existing timeout
                if (peerConnection._disconnectTimeout) {
                    clearTimeout(peerConnection._disconnectTimeout);
                }
                // Delay refresh attempt to allow for reconnection
                peerConnection._disconnectTimeout = setTimeout(() => {
                    if (peerConnection.connectionState === 'disconnected') {
                        console.warn(`🔗 Connection still disconnected after grace period for ${peerId} - attempting refresh`);
                        this.refreshConnection(peerId);
                    }
                }, 4000); // 4 second grace period
            } else if (['failed', 'closed'].includes(state)) {
                console.warn(`🔗 Connection ${state} for ${peerId} - cleaning up`);
                this.cleanupPeerConnection(peerId);
            } else if (state === 'connected') {
                // Clear disconnect timeout if we reconnect
                if (peerConnection._disconnectTimeout) {
                    clearTimeout(peerConnection._disconnectTimeout);
                    peerConnection._disconnectTimeout = null;
                }
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
            setTimeout(async () => {
                if (peerConnection.connectionState === 'new' || peerConnection.connectionState === 'connecting') {
                    console.warn(`⏰ WebRTC connection timeout for ${remotePeerId}, state: ${peerConnection.connectionState}`);
                    
                    // Run diagnostics to understand why connection failed
                    await this.diagnoseConnection(remotePeerId);
                    
                    // Don't automatically force TURN - let health check system handle retries
                    // TURN-only will be tried only after multiple failed attempts (see health check)
                    console.log(`🔄 Initial connection attempt timed out - will retry via health check`);
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
            // Check if we already have a connection for this peer
            let peerConnection = this.peerConnections.get(senderId);
            
            if (peerConnection) {
                console.log(`🔄 Existing connection found for ${senderId}, state: ${peerConnection.signalingState}`);
                
                // GLARE STATE DETECTION: Check if we're also trying to send an offer
                if (peerConnection.signalingState === 'have-local-offer') {
                    console.warn(`🔄 Glare state detected with ${senderId} - both peers sent offers simultaneously`);
                    
                    // Use peer ID as tie-breaker: lower peer ID wins
                    const currentPeerId = this.roomWebRTC.ablyManager.getCurrentPeerId();
                    if (currentPeerId < senderId) {
                        console.log(`✅ We win glare tie-breaker (${currentPeerId} < ${senderId}) - ignoring their offer`);
                        return; // Ignore their offer, keep ours
                    } else {
                        console.log(`❌ They win glare tie-breaker (${senderId} < ${currentPeerId}) - accepting their offer`);
                        // Rollback our offer and accept theirs
                        this.cleanupPeerConnection(senderId);
                        peerConnection = this.createPeerConnection(senderId);
                    }
                }
                // If connection is closed or failed, create a new one
                else if (peerConnection.signalingState === 'closed' || peerConnection.connectionState === 'failed') {
                    console.log(`🔄 Cleaning up failed connection for ${senderId}`);
                    this.cleanupPeerConnection(senderId);
                    peerConnection = this.createPeerConnection(senderId);
                }
            } else {
                peerConnection = this.createPeerConnection(senderId);
            }
            
            // Set remote description and create answer
            console.log('📋 Setting remote description for:', senderId);
            console.log(`📋 Signaling state before offer: ${peerConnection.signalingState}`);
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
            if (!peerConnection) {
                console.warn(`⚠️ No peer connection found for ${senderId}`);
                return;
            }

            // Check signaling state before setting remote description
            console.log(`📋 Signaling state before answer: ${peerConnection.signalingState}`);
            
            if (peerConnection.signalingState !== 'have-local-offer') {
                console.warn(`⚠️ Unexpected signaling state for answer: ${peerConnection.signalingState}`);
                
                // If we're in a bad state, try to recover by creating a new connection
                if (peerConnection.signalingState === 'closed') {
                    console.log(`🔄 Recreating closed connection for ${senderId}`);
                    this.cleanupPeerConnection(senderId);
                    return;
                }
            }

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
        } catch (error) {
            console.error(`❌ Failed to handle answer from ${senderId}:`, error);
            
            // If setRemoteDescription fails, it might be a signaling race condition
            if (error.name === 'InvalidStateError' || error.name === 'OperationError') {
                console.log(`🔄 Signaling error, cleaning up connection for retry: ${senderId}`);
                this.cleanupPeerConnection(senderId);
            } else {
                this.handleWebRTCError('Failed to handle answer', error, senderId);
            }
        }
    }

    /**
     * Handles incoming ICE candidate for peer connection
     */
    async handleIceCandidate(data, senderId) {
        try {
            const candidate = data.candidate;
            console.log(`🧊 Received ICE candidate from ${senderId}:`, candidate.type);
            
            const peerConnection = this.peerConnections.get(senderId);
            if (!peerConnection) {
                console.warn(`⚠️ No peer connection found for ICE candidate from ${senderId}`);
                return;
            }

            // Queue ICE candidates until remote description is set
            if (!peerConnection.remoteDescription) {
                const queue = this.pendingIce.get(senderId) || [];
                queue.push(candidate);
                this.pendingIce.set(senderId, queue);
                console.log(`🧊 Queued ICE candidate for ${senderId} (${queue.length} total pending)`);
                return;
            }

            // Add candidate immediately if remote description is set
            await peerConnection.addIceCandidate(candidate);
            console.log(`🧊 Added ICE candidate from ${senderId}:`, candidate.type);
        } catch (error) {
            console.warn(`⚠️ Failed to add ICE candidate from ${senderId}:`, error);
            // Non-fatal error - connection establishment may still succeed with other candidates
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
                    
                    // Start periodic quality monitoring for this connection
                    this.startConnectionQualityMonitoring(peerConnection, peerId);
                }
            });
        } catch (error) {
            console.warn(`🔗 Failed to get candidate pair stats for ${peerId}:`, error);
        }
    }
    
    /**
     * Monitors connection quality metrics (RTT, packet loss, bandwidth)
     */
    async startConnectionQualityMonitoring(peerConnection, peerId) {
        // Clear any existing quality monitoring interval
        if (peerConnection._qualityMonitorInterval) {
            clearInterval(peerConnection._qualityMonitorInterval);
        }
        
        // Monitor quality every 10 seconds
        peerConnection._qualityMonitorInterval = setInterval(async () => {
            try {
                const stats = await peerConnection.getStats();
                const qualityMetrics = this.extractQualityMetrics(stats);
                
                if (qualityMetrics) {
                    console.log(`📊 Connection quality for ${peerId}:`, qualityMetrics);
                    
                    // Warn about poor connection quality
                    if (qualityMetrics.rtt > 300) {
                        console.warn(`⚠️ High latency detected for ${peerId}: ${qualityMetrics.rtt}ms RTT`);
                    }
                    if (qualityMetrics.packetLoss > 5) {
                        console.warn(`⚠️ High packet loss detected for ${peerId}: ${qualityMetrics.packetLoss}%`);
                    }
                }
            } catch (error) {
                console.debug(`📊 Failed to get quality metrics for ${peerId}:`, error);
            }
        }, 10000); // Check every 10 seconds
    }
    
    /**
     * Extracts quality metrics from WebRTC stats
     */
    extractQualityMetrics(stats) {
        let rtt = null;
        let packetLoss = null;
        let bandwidth = { audio: 0, video: 0 };
        let jitter = null;
        
        stats.forEach(report => {
            // RTT from candidate pair
            if (report.type === 'candidate-pair' && report.selected && report.currentRoundTripTime) {
                rtt = Math.round(report.currentRoundTripTime * 1000); // Convert to ms
            }
            
            // Inbound RTP stats (receiving)
            if (report.type === 'inbound-rtp') {
                if (report.packetsLost && report.packetsReceived) {
                    const totalPackets = report.packetsLost + report.packetsReceived;
                    packetLoss = ((report.packetsLost / totalPackets) * 100).toFixed(2);
                }
                
                if (report.jitter) {
                    jitter = Math.round(report.jitter * 1000); // Convert to ms
                }
                
                // Calculate bandwidth (bytes per second)
                if (report.bytesReceived && report.timestamp) {
                    const kind = report.kind || report.mediaType;
                    if (kind === 'audio') {
                        bandwidth.audio = Math.round(report.bytesReceived / 1024); // KB
                    } else if (kind === 'video') {
                        bandwidth.video = Math.round(report.bytesReceived / 1024); // KB
                    }
                }
            }
        });
        
        // Only return if we have at least some data
        if (rtt !== null || packetLoss !== null) {
            return {
                rtt: rtt || 'N/A',
                packetLoss: packetLoss || 'N/A',
                jitter: jitter || 'N/A',
                bandwidth: bandwidth
            };
        }
        
        return null;
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
            // Clear all timeout references stored on the peer connection object
            if (connection._disconnectTimeout) {
                clearTimeout(connection._disconnectTimeout);
                connection._disconnectTimeout = null;
            }
            if (connection._iceDisconnectTimeout) {
                clearTimeout(connection._iceDisconnectTimeout);
                connection._iceDisconnectTimeout = null;
            }
            // Clear quality monitoring interval
            if (connection._qualityMonitorInterval) {
                clearInterval(connection._qualityMonitorInterval);
                connection._qualityMonitorInterval = null;
            }
            connection.close();
            this.peerConnections.delete(peerId);
        }
        
        // Clean up any pending ICE candidates
        this.pendingIce.delete(peerId);
        
        // Clean up retry timers
        this.connectionRetryTimers.delete(peerId);
        
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

    /**
     * Gets the connection state for a peer
     */
    getPeerConnectionState(peerId) {
        const connection = this.peerConnections.get(peerId);
        return connection ? connection.connectionState : null;
    }

    /**
     * Get the peer connections map (for debugging and management)
     */
    getPeerConnections() {
        return this.peerConnections;
    }

    /**
     * Retry connection with TURN-only configuration for difficult network conditions
     */
    async retryConnectionWithTurnOnly(remotePeerId, failedConnection) {
        try {
            console.log(`🔄 Retrying connection to ${remotePeerId} with TURN-only mode`);
            
            // Close the failed connection
            if (failedConnection) {
                failedConnection.close();
            }
            this.peerConnections.delete(remotePeerId);
            
            // Create new connection with TURN-only ICE config
            const turnOnlyConfig = {
                ...this.roomWebRTC.iceManager.getIceConfig(),
                iceTransportPolicy: 'relay', // Force TURN-only
                iceCandidatePoolSize: 0,
                bundlePolicy: 'max-bundle'
            };
            
            const peerConnection = new RTCPeerConnection(turnOnlyConfig);
            this.peerConnections.set(remotePeerId, peerConnection);
            
            // Set up event handlers (reuse the setup logic)
            this.setupPeerConnectionEventHandlers(peerConnection, remotePeerId);
            
            // Add local tracks if not in viewer mode
            if (!this.roomWebRTC.roomData.viewer_mode) {
                const localStream = this.roomWebRTC.mediaManager.getLocalStream();
                if (localStream) {
                    localStream.getTracks().forEach(track => {
                        peerConnection.addTrack(track, localStream);
                    });
                    console.log('📡 Added local tracks to TURN-only connection for', remotePeerId);
                }
            }
            
            // Create and send new offer
            const offerOptions = this.roomWebRTC.roomData.viewer_mode ? 
                { offerToReceiveAudio: true, offerToReceiveVideo: true } : {};
            const offer = await peerConnection.createOffer(offerOptions);
            await peerConnection.setLocalDescription(offer);
            
            console.log(`🔄 Sending TURN-only retry offer to ${remotePeerId}`);
            this.roomWebRTC.ablyManager.publishToAbly('webrtc-offer', { offer, retryWithTurn: true }, remotePeerId);
            
        } catch (error) {
            console.error(`❌ TURN-only retry failed for ${remotePeerId}:`, error);
        }
    }

    /**
     * Sets up ICE candidate handler - sends candidates immediately (VDO.Ninja pattern)
     * This method is called for all peer connections to ensure consistent behavior
     */
    setupIceCandidateHandler(peerConnection, peerId) {
        peerConnection.onicecandidate = (event) => {
            if (event.candidate) {
                console.log(`🧊 Sending ICE candidate immediately to ${peerId}:`, event.candidate.type);
                
                // Send immediately - no batching delay (VDO.Ninja pattern)
                this.roomWebRTC.ablyManager.publishToAbly('webrtc-ice-candidate', {
                    candidate: event.candidate
                }, peerId);
            } else {
                console.log(`🧊 ICE candidate gathering complete for ${peerId}`);
            }
        };
    }

    /**
     * Manually refresh/reconnect to a specific peer
     */
    async refreshConnection(peerId) {
        console.log(`🔄 Manually refreshing connection to ${peerId}`);
        
        // Clean up existing connection
        this.cleanupPeerConnection(peerId);
        
        // Wait a moment for cleanup
        await new Promise(resolve => setTimeout(resolve, 100));
        
        // Initiate new connection
        this.initiateWebRTCConnection(peerId);
        
        console.log(`✅ Connection refresh initiated for ${peerId}`);
    }
    
    /**
     * Performs ICE restart to recover stale connections without full reconnection
     * This is lighter weight than refreshConnection() and preserves the peer connection
     */
    async restartIce(peerId) {
        console.log(`🔄 Attempting ICE restart for ${peerId}`);
        
        const peerConnection = this.peerConnections.get(peerId);
        if (!peerConnection) {
            console.warn(`⚠️ No peer connection found for ICE restart: ${peerId}`);
            return false;
        }
        
        try {
            // Check if we're the offerer (have local description with type 'offer')
            if (peerConnection.localDescription?.type === 'offer') {
                console.log(`🔄 We are offerer - creating new offer with ICE restart`);
                
                // Create new offer with ICE restart flag
                const offerOptions = {
                    iceRestart: true,
                    ...(this.roomWebRTC.roomData.viewer_mode ? 
                        { offerToReceiveAudio: true, offerToReceiveVideo: true } : {})
                };
                
                const offer = await peerConnection.createOffer(offerOptions);
                await peerConnection.setLocalDescription(offer);
                
                // Send the new offer to the peer
                this.roomWebRTC.ablyManager.publishToAbly('webrtc-offer', { 
                    offer,
                    iceRestart: true 
                }, peerId);
                
                console.log(`✅ ICE restart offer sent to ${peerId}`);
                return true;
            } else {
                console.log(`ℹ️ We are answerer - cannot initiate ICE restart, need full refresh`);
                return false;
            }
        } catch (error) {
            console.error(`❌ ICE restart failed for ${peerId}:`, error);
            return false;
        }
    }

    /**
     * Enhanced connection diagnostics to help debug issues
     */
    async diagnoseConnection(peerId) {
        const connection = this.peerConnections.get(peerId);
        if (!connection) {
            console.warn(`🔍 No connection found for ${peerId}`);
            return;
        }

        console.log(`🔍 Connection diagnostics for ${peerId}:`);
        console.log(`  - Connection state: ${connection.connectionState}`);
        console.log(`  - ICE connection state: ${connection.iceConnectionState}`);
        console.log(`  - ICE gathering state: ${connection.iceGatheringState}`);
        console.log(`  - Signaling state: ${connection.signalingState}`);
        
        // Check if we have local/remote descriptions
        console.log(`  - Local description: ${connection.localDescription ? 'Set' : 'Missing'}`);
        console.log(`  - Remote description: ${connection.remoteDescription ? 'Set' : 'Missing'}`);
        
        // Check ICE candidates
        try {
            const stats = await connection.getStats();
            let localCandidates = 0;
            let remoteCandidates = 0;
            let activePairs = 0;
            
            stats.forEach((report) => {
                if (report.type === 'local-candidate') localCandidates++;
                if (report.type === 'remote-candidate') remoteCandidates++;
                if (report.type === 'candidate-pair' && report.state === 'succeeded') activePairs++;
            });
            
            console.log(`  - Local candidates: ${localCandidates}`);
            console.log(`  - Remote candidates: ${remoteCandidates}`);
            console.log(`  - Active candidate pairs: ${activePairs}`);
            
            if (activePairs === 0 && localCandidates > 0 && remoteCandidates > 0) {
                console.warn(`⚠️ ${peerId}: ICE candidates present but no active pairs - connectivity issue!`);
            }
            
        } catch (error) {
            console.warn(`⚠️ Could not get stats for ${peerId}:`, error);
        }
    }

    /**
     * Set up event handlers for a peer connection (extracted for reuse in TURN retry)
     * Note: ICE candidate handler is set up separately via setupIceCandidateHandler()
     */
    setupPeerConnectionEventHandlers(peerConnection, peerId) {
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

        // Set up ICE candidate handler (consistent with createPeerConnection)
        this.setupIceCandidateHandler(peerConnection, peerId);

        // Monitor ICE connection states with grace period for transient disconnections
        peerConnection.oniceconnectionstatechange = () => {
            const iceState = peerConnection.iceConnectionState;
            console.log(`🧊 ICE connection state for ${peerId}: ${iceState}`);
            
            // Handle transient disconnections gracefully
            if (iceState === 'disconnected') {
                // Clear any existing timeout
                if (peerConnection._iceDisconnectTimeout) {
                    clearTimeout(peerConnection._iceDisconnectTimeout);
                }
                
                // Wait 3 seconds before taking action
                peerConnection._iceDisconnectTimeout = setTimeout(() => {
                    // Check if still disconnected
                    if (peerConnection.iceConnectionState === 'disconnected') {
                        console.warn(`🧊 ICE still disconnected for ${peerId} - attempting refresh`);
                        this.refreshConnection(peerId);
                    }
                }, 3000);
            } else if (iceState === 'failed') {
                // Immediate action for failed ICE
                console.error(`🧊 ICE connection failed for ${peerId} - attempting refresh`);
                this.refreshConnection(peerId);
            } else if (iceState === 'connected' || iceState === 'completed') {
                // Clear any disconnect timeouts if we reconnect
                if (peerConnection._iceDisconnectTimeout) {
                    clearTimeout(peerConnection._iceDisconnectTimeout);
                    peerConnection._iceDisconnectTimeout = null;
                }
                console.log(`✅ ICE connection ${iceState} for ${peerId}`);
            }
        };

        // Monitor general connection state (consistent with createPeerConnection)
        peerConnection.onconnectionstatechange = async () => {
            const state = peerConnection.connectionState;
            console.log(`🔗 Peer connection state: ${state} (${peerId})`);
            
            // Log candidate pair information when connected for telemetry
            if (state === 'connected' || state === 'completed') {
                this.logCandidatePairStats(peerConnection, peerId);
            }
            
            // Handle transient "disconnected" with grace period
            if (state === 'disconnected') {
                if (peerConnection._disconnectTimeout) {
                    clearTimeout(peerConnection._disconnectTimeout);
                }
                peerConnection._disconnectTimeout = setTimeout(() => {
                    if (peerConnection.connectionState === 'disconnected') {
                        console.warn(`🔗 Connection still disconnected after grace period for ${peerId} - attempting refresh`);
                        this.refreshConnection(peerId);
                    }
                }, 4000);
            } else if (['failed', 'closed'].includes(state)) {
                console.warn(`🔗 Connection ${state} for ${peerId} - cleaning up`);
                this.cleanupPeerConnection(peerId);
            } else if (state === 'connected') {
                if (peerConnection._disconnectTimeout) {
                    clearTimeout(peerConnection._disconnectTimeout);
                    peerConnection._disconnectTimeout = null;
                }
            }
        };
    }
}
