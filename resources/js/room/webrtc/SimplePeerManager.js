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
     */
    setupEventHandlers() {
        // Connection to PeerServer opened
        this.peer.on('open', (id) => {
            console.log(`✅ Connected to PeerServer with ID: ${id}`);
            this.peerId = id;
        });

        // Incoming call from another peer
        this.peer.on('call', (call) => {
            console.log(`📞 Incoming call from: ${call.peer}`);
            this.handleIncomingCall(call);
        });

        // Connection to PeerServer closed
        this.peer.on('disconnected', () => {
            console.warn('⚠️ Disconnected from PeerServer - attempting reconnect...');
            // PeerJS will automatically attempt to reconnect
        });

        // Error handling
        this.peer.on('error', (error) => {
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
        });
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

        // Handle call errors
        call.on('error', (error) => {
            console.error(`❌ Call error with ${remotePeerId}:`, error);
            this.handleCallClose(remotePeerId);
        });

        // Store the call
        this.calls.set(remotePeerId, call);
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
     */
    destroy() {
        console.log('🔌 Destroying SimplePeerManager...');

        // Close all active calls
        this.calls.forEach((call, peerId) => {
            console.log(`📴 Closing call with: ${peerId}`);
            call.close();
        });
        this.calls.clear();

        // Destroy peer connection
        if (this.peer) {
            this.peer.destroy();
            this.peer = null;
        }

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
