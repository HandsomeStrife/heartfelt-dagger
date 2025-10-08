/**
 * SimplePeerManager - Simplified WebRTC management using PeerJS
 * 
 * Replaces the complex PeerConnectionManager with a much simpler
 * PeerJS-based approach. Handles all WebRTC complexity internally.
 * 
 * This reduces ~800 lines of complex WebRTC code to ~200 lines.
 * 
 * VDO.NINJA FIX: Enhanced with diagnostic logging and state tracking
 * TYPESCRIPT MIGRATION: Fully typed with strict mode compliance
 */

import Peer, { MediaConnection } from 'peerjs';
import { LoggerRegistry, Logger } from '../utils/Logger';
import type { ConnectionState, PeerStats, RetryBudget } from '../../types/room';

/**
 * Event handler references for cleanup
 */
interface EventHandlers {
    open: ((id: string) => void) | null;
    call: ((call: MediaConnection) => void) | null;
    disconnected: (() => void) | null;
    error: ((error: Error) => void) | null;
}

/**
 * ICE server configuration
 */
interface IceServer {
    urls: string | string[];
    username?: string;
    credential?: string;
}

/**
 * Connection state enum values
 */
const ConnectionStateEnum = {
    DISCONNECTED: 'disconnected',
    CONNECTING: 'connecting',
    CONNECTED: 'connected',
    RECONNECTING: 'reconnecting',
    FAILED: 'failed'
} as const;

export class SimplePeerManager {
    private roomWebRTC: any; // TODO: Type as RoomWebRTC once migrated
    private peer: Peer | null = null;
    private peerId: string | null = null;
    private calls: Map<string, MediaConnection> = new Map();
    private localStream: MediaStream | null = null;
    private isInitialized: boolean = false;
    
    private logger: Logger;
    
    public connectionStates: Map<string, ConnectionState> = new Map();
    public peerServerState: ConnectionState = ConnectionStateEnum.DISCONNECTED;
    
    private retryAttempts: Map<string, number> = new Map();
    private maxRetries: number = 3;
    private retryBaseDelay: number = 1000;
    
    public globalRetryBudget: RetryBudget;
    
    private eventHandlers: EventHandlers = {
        open: null,
        call: null,
        disconnected: null,
        error: null
    };
    
    constructor(roomWebRTC: any) {
        this.roomWebRTC = roomWebRTC;
        this.logger = LoggerRegistry.getLogger('SimplePeerManager', roomWebRTC);
        
        this.globalRetryBudget = {
            maxRetries: 10,
            timeWindow: 30000,
            retries: []
        };
    }
    
    setPeerConnectionState(peerId: string, state: ConnectionState): void {
        const oldState = this.connectionStates.get(peerId);
        if (oldState !== state) {
            this.connectionStates.set(peerId, state);
            
            this.logger.stateChange(
                oldState || 'none', 
                state, 
                `Peer ${peerId} connection state changed`,
                {
                    peerId,
                    activeCalls: this.calls.size,
                    hasLocalStream: !!this.localStream
                }
            );
            
            if (this.roomWebRTC.onPeerStateChange) {
                this.roomWebRTC.onPeerStateChange(peerId, state, oldState);
            }
        }
    }
    
    getPeerConnectionState(peerId: string): ConnectionState {
        return this.connectionStates.get(peerId) || ConnectionStateEnum.DISCONNECTED;
    }
    
    setPeerServerState(state: ConnectionState): void {
        const oldState = this.peerServerState;
        if (oldState !== state) {
            this.peerServerState = state;
            
            this.logger.stateChange(
                oldState,
                state,
                'PeerServer connection state changed',
                {
                    peerId: this.peerId,
                    activeCalls: this.calls.size,
                    isInitialized: this.isInitialized
                }
            );
        }
    }
    
    checkRetryBudget(): boolean {
        const now = Date.now();
        
        this.globalRetryBudget.retries = this.globalRetryBudget.retries.filter(
            timestamp => now - timestamp < this.globalRetryBudget.timeWindow
        );
        
        if (this.globalRetryBudget.retries.length < this.globalRetryBudget.maxRetries) {
            this.globalRetryBudget.retries.push(now);
            return true;
        }
        
        console.warn(`⚠️ Global retry budget exceeded (${this.globalRetryBudget.retries.length} retries in last 30s)`);
        return false;
    }

    async initialize(): Promise<void> {
        if (this.isInitialized) {
            console.warn('⚠️ SimplePeerManager already initialized');
            return;
        }

        this.peerId = this.generatePeerId();

        this.peer = new Peer(this.peerId, {
            host: import.meta.env['VITE_PEERJS_HOST'] || window.location.hostname,
            port: import.meta.env['VITE_PEERJS_PORT'] ? parseInt(import.meta.env['VITE_PEERJS_PORT']) : 443,
            path: '/peerjs',
            secure: (import.meta.env['VITE_PEERJS_SECURE'] || 'true') === 'true',
            debug: import.meta.env['DEV'] ? 2 : 0,
            config: {
                iceServers: await this.getIceServers()
            }
        });

        await this.waitForPeerOpen();
        
        this.setupEventHandlers();
        this.isInitialized = true;

        console.log(`🎯 PeerJS initialized and connected with ID: ${this.peerId}`);
    }
    
    private async waitForPeerOpen(): Promise<string> {
        return new Promise((resolve, reject) => {
            const timeout = setTimeout(() => {
                reject(new Error('PeerJS connection timeout after 10 seconds'));
            }, 10000);
            
            this.peer!.on('open', (id) => {
                clearTimeout(timeout);
                console.log('✅ PeerJS connection established:', id);
                resolve(id);
            });
            
            this.peer!.on('error', (error) => {
                clearTimeout(timeout);
                reject(error);
            });
        });
    }

    private async getIceServers(): Promise<IceServer[]> {
        try {
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

    private getDefaultIceServers(): IceServer[] {
        return [
            { urls: 'stun:stun.l.google.com:19302' },
            { urls: 'stun:stun1.l.google.com:19302' }
        ];
    }

    private setupEventHandlers(): void {
        if (!this.peer) return;
        
        this.eventHandlers.open = (id: string) => {
            console.log(`✅ Connected to PeerServer with ID: ${id}`);
            this.peerId = id;
            this.setPeerServerState(ConnectionStateEnum.CONNECTED);
        };
        this.peer.on('open', this.eventHandlers.open);

        this.eventHandlers.call = (call: MediaConnection) => {
            console.log(`📞 Incoming call from: ${call.peer}`);
            this.setPeerConnectionState(call.peer, ConnectionStateEnum.CONNECTING);
            this.handleIncomingCall(call);
        };
        this.peer.on('call', this.eventHandlers.call);

        this.eventHandlers.disconnected = () => {
            console.warn('⚠️ Disconnected from PeerServer');
            this.setPeerServerState(ConnectionStateEnum.RECONNECTING);
        };
        this.peer.on('disconnected', this.eventHandlers.disconnected);

        this.eventHandlers.error = (err: any) => {
            console.error('❌ PeerJS error:', err);
            
            switch (err.type) {
                case 'peer-unavailable':
                    console.warn(`Peer ${err.message} is not available`);
                    const peerId = err.message?.split(' ')[1];
                    if (peerId) {
                        this.setPeerConnectionState(peerId, ConnectionStateEnum.FAILED);
                    }
                    break;
                case 'network':
                    console.error('Network error - check PeerServer connection');
                    this.setPeerServerState(ConnectionStateEnum.FAILED);
                    break;
                case 'server-error':
                    console.error('PeerServer error:', err);
                    this.setPeerServerState(ConnectionStateEnum.FAILED);
                    break;
                default:
                    console.error('Unknown PeerJS error:', err);
            }
        };
        this.peer.on('error', this.eventHandlers.error);
    }

    private handleIncomingCall(call: MediaConnection): void {
        call.answer(this.localStream!);

        call.on('stream', (remoteStream: MediaStream) => {
            console.log(`📡 Received remote stream from: ${call.peer}`);
            this.handleRemoteStream(remoteStream, call.peer);
        });

        call.on('close', () => {
            console.log(`📴 Call closed with: ${call.peer}`);
            this.handleCallClose(call.peer);
        });

        call.on('error', (error: Error) => {
            console.error(`❌ Call error with ${call.peer}:`, error);
            this.handleCallClose(call.peer);
        });

        this.calls.set(call.peer, call);
    }

    callPeer(remotePeerId: string): void {
        if (!this.localStream) {
            console.error('❌ Cannot call peer - no local stream available');
            return;
        }

        if (this.calls.has(remotePeerId)) {
            console.warn(`⚠️ Already connected to ${remotePeerId}`);
            return;
        }

        console.log(`📞 Calling peer: ${remotePeerId}`);
        
        this.setPeerConnectionState(remotePeerId, ConnectionStateEnum.CONNECTING);

        const call = this.peer!.call(remotePeerId, this.localStream);

        call.on('stream', (remoteStream: MediaStream) => {
            console.log(`📡 Received remote stream from: ${remotePeerId}`);
            this.setPeerConnectionState(remotePeerId, ConnectionStateEnum.CONNECTED);
            this.handleRemoteStream(remoteStream, remotePeerId);
        });

        call.on('close', () => {
            console.log(`📴 Call closed with: ${remotePeerId}`);
            this.setPeerConnectionState(remotePeerId, ConnectionStateEnum.DISCONNECTED);
            this.handleCallClose(remotePeerId);
        });

        call.on('error', (error: Error) => {
            console.error(`❌ Call error with ${remotePeerId}:`, error);
            this.setPeerConnectionState(remotePeerId, ConnectionStateEnum.FAILED);
            this.handleCallError(remotePeerId, error);
        });

        this.calls.set(remotePeerId, call);
    }
    
    private handleCallError(peerId: string, _error: Error): void {
        this.handleCallClose(peerId);
        
        if (!this.checkRetryBudget()) {
            console.warn(`⚠️ Skipping retry for peer ${peerId} due to global retry budget`);
            this.retryAttempts.delete(peerId);
            return;
        }
        
        const retryCount = this.retryAttempts.get(peerId) || 0;
        
        if (retryCount < this.maxRetries) {
            this.setPeerConnectionState(peerId, ConnectionStateEnum.RECONNECTING);
            
            const delay = this.retryBaseDelay * Math.pow(2, retryCount);
            
            console.log(`🔄 Retrying connection to ${peerId} in ${delay}ms (attempt ${retryCount + 1}/${this.maxRetries})`);
            
            this.retryAttempts.set(peerId, retryCount + 1);
            
            setTimeout(() => {
                console.log(`🔄 Attempting retry ${retryCount + 1} for peer ${peerId}`);
                this.callPeer(peerId);
            }, delay);
        } else {
            console.error(`❌ Max retries (${this.maxRetries}) reached for peer ${peerId}, giving up`);
            
            this.setPeerConnectionState(peerId, ConnectionStateEnum.FAILED);
            this.retryAttempts.delete(peerId);
            
            if (this.roomWebRTC?.uiStateManager) {
                this.roomWebRTC.uiStateManager.showError(
                    `Failed to connect to peer after ${this.maxRetries} attempts. They may have connection issues.`
                );
            }
        }
    }

    private handleRemoteStream(remoteStream: MediaStream, peerId: string): void {
        if (this.roomWebRTC?.handleRemoteStream) {
            this.roomWebRTC.handleRemoteStream(remoteStream, peerId);
        }
    }

    private handleCallClose(peerId: string): void {
        this.calls.delete(peerId);
        this.connectionStates.delete(peerId);
        
        if (this.roomWebRTC?.handlePeerDisconnected) {
            this.roomWebRTC.handlePeerDisconnected(peerId);
        }
    }

    setLocalStream(stream: MediaStream): void {
        this.localStream = stream;
        console.log('🎥 Local stream set for PeerJS');
    }

    closeCall(peerId: string): void {
        const call = this.calls.get(peerId);
        if (call) {
            console.log(`📴 Closing call with: ${peerId}`);
            call.close();
            this.calls.delete(peerId);
        }
    }

    destroy(): void {
        console.log('🔌 Destroying SimplePeerManager...');

        this.calls.forEach((call, peerId) => {
            console.log(`📴 Closing call with: ${peerId}`);
            call.close();
        });
        this.calls.clear();

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
            
            this.peer.destroy();
            this.peer = null;
        }

        this.eventHandlers = {
            open: null,
            call: null,
            disconnected: null,
            error: null
        };

        this.isInitialized = false;
        console.log('✅ SimplePeerManager destroyed');
    }

    getPeerId(): string | null {
        return this.peerId;
    }

    private generatePeerId(): string {
        return `peer-${Math.random().toString(36).substr(2, 9)}-${Date.now()}`;
    }

    getActivePeerIds(): string[] {
        return Array.from(this.calls.keys());
    }

    isConnectedTo(peerId: string): boolean {
        return this.calls.has(peerId);
    }

    getStats(): PeerStats {
        return {
            peerId: this.peerId || '',
            isConnected: this.peerServerState === ConnectionStateEnum.CONNECTED,
            connectedPeers: Array.from(this.calls.keys()),
            totalCalls: this.calls.size
        };
    }
}

