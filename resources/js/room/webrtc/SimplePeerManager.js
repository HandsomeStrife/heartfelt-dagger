/**
 * SimplePeerManager - Simplified WebRTC management using PeerJS
 * 
 * Replaces the complex PeerConnectionManager with a much simpler
 * PeerJS-based approach. Handles all WebRTC complexity internally.
 * 
 * This reduces ~800 lines of complex WebRTC code to ~200 lines.
 */

import Peer from 'peerjs';

export class SimplePeerManager {
    constructor(roomWebRTC) {
        this.roomWebRTC = roomWebRTC;
        this.peer = null;
        this.peerId = null;
        this.calls = new Map(); // Map of peerId -> MediaConnection
        this.localStream = null;
        this.isInitialized = false;
        
        // Retry logic state
        this.retryAttempts = new Map(); // Map of peerId -> attempt count
        this.maxRetries = 3;
        this.retryBaseDelay = 1000; // 1 second base delay
        
        // CRITICAL FIX: Store event handler references for proper cleanup
        this.eventHandlers = {
            open: null,
            call: null,
            disconnected: null,
            error: null
        };
    }

    /**
     * Initialize PeerJS client
     */
    async initialize() {
        if (this.isInitialized) {
            console.warn('⚠️ SimplePeerManager already initialized');
            return;
        }

        // Generate a unique peer ID for this session
        this.peerId = this.generatePeerId();

        // Create PeerJS instance
        this.peer = new Peer(this.peerId, {
            host: import.meta.env.VITE_PEERJS_HOST || window.location.hostname,
            port: import.meta.env.VITE_PEERJS_PORT ? parseInt(import.meta.env.VITE_PEERJS_PORT) : 443,
            path: '/peerjs',
            secure: (import.meta.env.VITE_PEERJS_SECURE || 'true') === 'true',
            debug: import.meta.env.DEV ? 2 : 0, // Debug level (0-3)
            config: {
                // Use Cloudflare TURN servers if configured
                iceServers: await this.getIceServers()
            }
        });

        this.setupEventHandlers();
        this.isInitialized = true;

        console.log(`🎯 PeerJS initialized with ID: ${this.peerId}`);
    }

    /**
     * Get ICE servers (STUN/TURN) configuration
     */
    async getIceServers() {
        try {
            // Fetch from ICE config endpoint
            const response = await fetch('/api/webrtc/ice-config');
            if (response.ok) {
                const data = await response.json();
                return data.iceServers || this.getDefaultIceServers();
            }
        } catch (error) {
            console.warn('Failed to fetch ICE config, using defaults:', error);
        }
        
        return this.getDefaultIceServers();
    }

    /**
     * Default ICE servers (public STUN servers)
     */
    getDefaultIceServers() {
        return [
            { urls: 'stun:stun.l.google.com:19302' },
            { urls: 'stun:stun1.l.google.com:19302' }
        ];
    }

    /**
     * Setup PeerJS event handlers
     * CRITICAL FIX: Store handler references for proper cleanup
     */
    setupEventHandlers() {
        // Connection to PeerServer opened
        this.eventHandlers.open = (id) => {
            console.log(`✅ Connected to PeerServer with ID: ${id}`);
            this.peerId = id;
        };
        this.peer.on('open', this.eventHandlers.open);

        // Incoming call from another peer
        this.eventHandlers.call = (call) => {
            console.log(`📞 Incoming call from: ${call.peer}`);
            this.handleIncomingCall(call);
        };
        this.peer.on('call', this.eventHandlers.call);

        // Connection to PeerServer closed
        this.eventHandlers.disconnected = () => {
            console.warn('⚠️ Disconnected from PeerServer - attempting reconnect...');
            
            // CRITICAL FIX: PeerJS doesn't always auto-reconnect
            // We must manually call reconnect() after a brief delay
            setTimeout(() => {
                // Check if still disconnected
                if (this.peer && this.peer.disconnected) {
                    console.log('🔄 Manually reconnecting to PeerServer...');
                    try {
                        this.peer.reconnect();
                    } catch (error) {
                        console.error('❌ Manual reconnection failed:', error);
                    }
                } else {
                    console.log('✅ Already reconnected automatically');
                }
            }, 1000); // Wait 1 second before attempting manual reconnect
        };
        this.peer.on('disconnected', this.eventHandlers.disconnected);

        // Error handling
        this.eventHandlers.error = (error) => {
            console.error('❌ PeerJS error:', error);
            
            // Handle specific error types
            switch (error.type) {
                case 'peer-unavailable':
                    console.warn(`Peer ${error.message} is not available`);
                    break;
                case 'network':
                    console.error('Network error - check PeerServer connection');
                    break;
                case 'server-error':
                    console.error('PeerServer error:', error);
                    break;
                default:
                    console.error('Unknown PeerJS error:', error);
            }
        };
        this.peer.on('error', this.eventHandlers.error);
    }

    /**
     * Handle incoming call from another peer
     */
    handleIncomingCall(call) {
        // Answer with our local stream
        call.answer(this.localStream);

        // Listen for remote stream
        call.on('stream', (remoteStream) => {
            console.log(`📡 Received remote stream from: ${call.peer}`);
            this.handleRemoteStream(remoteStream, call.peer);
        });

        // Handle call close
        call.on('close', () => {
            console.log(`📴 Call closed with: ${call.peer}`);
            this.handleCallClose(call.peer);
        });

        // Handle call errors
        call.on('error', (error) => {
            console.error(`❌ Call error with ${call.peer}:`, error);
            this.handleCallClose(call.peer);
        });

        // Store the call
        this.calls.set(call.peer, call);
    }

    /**
     * Call another peer
     */
    callPeer(remotePeerId) {
        if (!this.localStream) {
            console.error('❌ Cannot call peer - no local stream available');
            return;
        }

        if (this.calls.has(remotePeerId)) {
            console.warn(`⚠️ Already connected to ${remotePeerId}`);
            return;
        }

        console.log(`📞 Calling peer: ${remotePeerId}`);

        // Make the call with our local stream
        const call = this.peer.call(remotePeerId, this.localStream);

        // Listen for remote stream
        call.on('stream', (remoteStream) => {
            console.log(`📡 Received remote stream from: ${remotePeerId}`);
            this.handleRemoteStream(remoteStream, remotePeerId);
        });

        // Handle call close
        call.on('close', () => {
            console.log(`📴 Call closed with: ${remotePeerId}`);
            this.handleCallClose(remotePeerId);
        });

        // Handle call errors with retry logic
        call.on('error', (error) => {
            console.error(`❌ Call error with ${remotePeerId}:`, error);
            this.handleCallError(remotePeerId, error);
        });

        // Store the call
        this.calls.set(remotePeerId, call);
    }
    
    /**
     * Handles call errors with exponential backoff retry
     * @param {string} peerId - The peer that had an error
     * @param {Error} error - The error that occurred
     */
    handleCallError(peerId, error) {
        // Clean up the failed call
        this.handleCallClose(peerId);
        
        // Get current retry count
        const retryCount = this.retryAttempts.get(peerId) || 0;
        
        // Check if we should retry
        if (retryCount < this.maxRetries) {
            // Calculate exponential backoff delay
            const delay = this.retryBaseDelay * Math.pow(2, retryCount);
            
            console.log(`🔄 Retrying connection to ${peerId} in ${delay}ms (attempt ${retryCount + 1}/${this.maxRetries})`);
            
            // Increment retry count
            this.retryAttempts.set(peerId, retryCount + 1);
            
            // Schedule retry
            setTimeout(() => {
                console.log(`🔄 Attempting retry ${retryCount + 1} for peer ${peerId}`);
                this.callPeer(peerId);
            }, delay);
        } else {
            console.error(`❌ Max retries (${this.maxRetries}) reached for peer ${peerId}, giving up`);
            
            // Reset retry count
            this.retryAttempts.delete(peerId);
            
            // Notify user of permanent failure
            if (this.roomWebRTC && this.roomWebRTC.uiStateManager) {
                this.roomWebRTC.uiStateManager.showError(
                    `Failed to connect to peer after ${this.maxRetries} attempts. They may have connection issues.`
                );
            }
        }
    }

    /**
     * Handle remote stream received
     */
    handleRemoteStream(remoteStream, peerId) {
        // Delegate to MediaManager or parent
        if (this.roomWebRTC && this.roomWebRTC.handleRemoteStream) {
            this.roomWebRTC.handleRemoteStream(remoteStream, peerId);
        }
    }

    /**
     * Handle call close/cleanup
     */
    handleCallClose(peerId) {
        this.calls.delete(peerId);
        
        // Notify parent of disconnection
        if (this.roomWebRTC && this.roomWebRTC.handlePeerDisconnected) {
            this.roomWebRTC.handlePeerDisconnected(peerId);
        }
    }

    /**
     * Set local media stream
     */
    setLocalStream(stream) {
        this.localStream = stream;
        console.log('🎥 Local stream set for PeerJS');
    }

    /**
     * Close call with specific peer
     */
    closeCall(peerId) {
        const call = this.calls.get(peerId);
        if (call) {
            console.log(`📴 Closing call with: ${peerId}`);
            call.close();
            this.calls.delete(peerId);
        }
    }

    /**
     * Close all calls and disconnect
     * CRITICAL FIX: Properly remove event listeners to prevent memory leaks
     */
    destroy() {
        console.log('🔌 Destroying SimplePeerManager...');

        // Close all active calls
        this.calls.forEach((call, peerId) => {
            console.log(`📴 Closing call with: ${peerId}`);
            call.close();
        });
        this.calls.clear();

        // CRITICAL FIX: Remove event listeners before destroying peer
        if (this.peer) {
            if (this.eventHandlers.open) {
                this.peer.off('open', this.eventHandlers.open);
            }
            if (this.eventHandlers.call) {
                this.peer.off('call', this.eventHandlers.call);
            }
            if (this.eventHandlers.disconnected) {
                this.peer.off('disconnected', this.eventHandlers.disconnected);
            }
            if (this.eventHandlers.error) {
                this.peer.off('error', this.eventHandlers.error);
            }
            
            console.log('🧹 Event listeners removed');
            
            // Destroy peer connection
            this.peer.destroy();
            this.peer = null;
        }

        // Clear event handler references
        this.eventHandlers = {
            open: null,
            call: null,
            disconnected: null,
            error: null
        };

        this.isInitialized = false;
        console.log('✅ SimplePeerManager destroyed');
    }

    /**
     * Get current peer ID
     */
    getPeerId() {
        return this.peerId;
    }

    /**
     * Generate unique peer ID
     */
    generatePeerId() {
        return `peer-${Math.random().toString(36).substr(2, 9)}-${Date.now()}`;
    }

    /**
     * Get all active peer IDs
     */
    getActivePeerIds() {
        return Array.from(this.calls.keys());
    }

    /**
     * Check if connected to specific peer
     */
    isConnectedTo(peerId) {
        return this.calls.has(peerId);
    }

    /**
     * Get connection stats (for debugging)
     */
    getStats() {
        return {
            peerId: this.peerId,
            activeCalls: this.calls.size,
            connectedPeers: Array.from(this.calls.keys()),
            hasLocalStream: !!this.localStream,
            isInitialized: this.isInitialized
        };
    }
}
