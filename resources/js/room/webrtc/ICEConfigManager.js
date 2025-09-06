/**
 * ICEConfigManager - Manages ICE server configuration for WebRTC
 * 
 * Handles loading ICE configuration from backend API with Cloudflare STUN/TURN support,
 * provides fallback configuration, and manages configuration updates for existing connections.
 */
export class ICEConfigManager {
    constructor() {
        // Default fallback ICE configuration
        this.iceConfig = {
            iceServers: [
                { urls: ['stun:stun.cloudflare.com:3478', 'stun:stun.l.google.com:19302'] }
            ]
        };
        this.iceReady = false;
        this.updateCallbacks = new Set();
    }

    /**
     * Gets the current ICE configuration
     */
    getConfig() {
        return this.iceConfig;
    }

    /**
     * Checks if ICE configuration is ready (loaded from backend)
     */
    isReady() {
        return this.iceReady;
    }

    /**
     * Registers a callback to be called when ICE config is updated
     */
    onConfigUpdate(callback) {
        this.updateCallbacks.add(callback);
        
        // If config is already ready, call immediately
        if (this.iceReady) {
            callback(this.iceConfig);
        }
        
        // Return unsubscribe function
        return () => {
            this.updateCallbacks.delete(callback);
        };
    }

    /**
     * Loads ICE configuration from backend API with Cloudflare STUN/TURN support
     */
    async loadIceServers() {
        try {
            console.log('🧊 Loading ICE configuration from backend...');
            
            const response = await fetch('/api/webrtc/ice-config', { 
                cache: 'no-store',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const config = await response.json();
                
                if (config && Array.isArray(config.iceServers) && config.iceServers.length > 0) {
                    this.iceConfig = config;
                    this.iceReady = true;
                    
                    console.log('🧊 ICE configuration loaded successfully:', {
                        serversCount: config.iceServers.length,
                        hasSTUN: config.iceServers.some(s => 
                            s.urls && s.urls.some(url => url.startsWith('stun:'))
                        ),
                        hasTURN: config.iceServers.some(s => 
                            s.urls && s.urls.some(url => url.startsWith('turn:'))
                        )
                    });
                    
                    // Notify all registered callbacks
                    this.notifyConfigUpdate();
                } else {
                    console.warn('🧊 Invalid ICE configuration received, using fallback');
                }
            } else {
                console.warn('🧊 Failed to load ICE configuration, using fallback STUN-only');
            }
        } catch (error) {
            console.warn('🧊 Error loading ICE configuration, using fallback STUN-only:', error);
        }
    }

    /**
     * Notifies all registered callbacks about config update
     */
    notifyConfigUpdate() {
        this.updateCallbacks.forEach(callback => {
            try {
                callback(this.iceConfig);
            } catch (error) {
                console.warn('🧊 Error in ICE config update callback:', error);
            }
        });
    }

    /**
     * Updates a specific peer connection with the current ICE configuration
     */
    updatePeerConnection(peerConnection, peerId) {
        try {
            peerConnection.setConfiguration(this.iceConfig);
            console.log(`🧊 Updated ICE config for peer: ${peerId}`);
            return true;
        } catch (error) {
            console.warn(`🧊 Failed to update ICE config for peer ${peerId}:`, error);
            return false;
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
}
