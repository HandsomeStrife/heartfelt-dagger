/**
 * ICEConfigManager - Manages ICE server configuration for WebRTC connections
 * 
 * Handles loading and updating ICE configuration from backend API
 * with Cloudflare STUN/TURN support and fallback to public STUN servers.
 */

export class ICEConfigManager {
    constructor() {
        this.iceConfig = {
            iceServers: [
                { urls: ['stun:stun.cloudflare.com:3478', 'stun:stun.l.google.com:19302'] }
            ]
        };
        this.iceReady = false;
        this.peerConnections = new Map(); // Reference to peer connections for updates
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
                    
                    // Update any existing peer connections with new configuration
                    this.updateExistingPeerConnections();
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
     * Updates existing peer connections with new ICE configuration
     */
    updateExistingPeerConnections() {
        if (this.peerConnections.size === 0) return;
        
        console.log('🧊 Updating existing peer connections with new ICE configuration');
        
        this.peerConnections.forEach((connection, peerId) => {
            try {
                connection.setConfiguration(this.iceConfig);
                console.log(`🧊 Updated ICE config for peer: ${peerId}`);
            } catch (error) {
                console.warn(`🧊 Failed to update ICE config for peer ${peerId}:`, error);
            }
        });
    }

    /**
     * Sets reference to peer connections map for updates
     */
    setPeerConnections(peerConnections) {
        this.peerConnections = peerConnections;
    }

    /**
     * Gets current ICE configuration
     */
    getIceConfig() {
        return this.iceConfig;
    }

    /**
     * Checks if ICE configuration is ready
     */
    isReady() {
        return this.iceReady;
    }

    /**
     * Updates a specific peer connection with current ICE config
     */
    updatePeerConnection(peerId, peerConnection) {
        if (!this.iceReady) {
            // Load ICE config and update this connection when ready
            this.loadIceServers().then(() => {
                try {
                    peerConnection.setConfiguration(this.iceConfig);
                    console.log(`🧊 Updated late-arriving ICE config for peer: ${peerId}`);
                } catch (error) {
                    console.warn(`🧊 Failed to update late-arriving ICE config for peer ${peerId}:`, error);
                }
            }).catch(() => {
                // Silently fail - connection will work with fallback STUN
            });
        }
    }
}
