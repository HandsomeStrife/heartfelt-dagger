/**
 * Diagnostic Exporter - Automatically exports system state on critical failures
 * 
 * Captures comprehensive diagnostic data including:
 * - All module logs from LoggerRegistry
 * - Current room state
 * - PeerJS connection state
 * - Media device state
 * - Browser information
 * - Error stack traces
 * 
 * Triggered automatically on:
 * - Uncaught errors
 * - Unhandled promise rejections
 * - Critical failures in key subsystems
 * 
 * Can also be triggered manually via console: window.exportDiagnostics()
 */

import { LoggerRegistry } from '../utils/Logger';
import logger from '../../utils/logger';

export class DiagnosticExporter {
    constructor(roomWebRTC) {
        this.roomWebRTC = roomWebRTC;
        this.lastExportTime = 0;
        this.minExportInterval = 10000; // Minimum 10 seconds between auto-exports
        this.setupGlobalHandlers();
        this.makeGloballyAccessible();
    }
    
    /**
     * Setup global error handlers for automatic export
     */
    setupGlobalHandlers() {
        // Uncaught errors
        window.addEventListener('error', (event) => {
            logger.error('🚨 Uncaught error detected:', event.error);
            this.exportOnCriticalFailure('uncaught-error', {
                message: event.message,
                filename: event.filename,
                lineno: event.lineno,
                colno: event.colno,
                error: event.error
            });
        });
        
        // Unhandled promise rejections
        window.addEventListener('unhandledrejection', (event) => {
            logger.error('🚨 Unhandled promise rejection:', event.reason);
            this.exportOnCriticalFailure('unhandled-rejection', {
                reason: event.reason,
                promise: event.promise
            });
        });
    }
    
    /**
     * Make diagnostic export accessible globally
     */
    makeGloballyAccessible() {
        window.exportDiagnostics = () => {
            logger.info('📊 Manual diagnostic export triggered');
            this.exportDiagnostics('manual-export');
        };
    }
    
    /**
     * Export diagnostics on critical failure (with rate limiting)
     */
    exportOnCriticalFailure(reason, errorData) {
        const now = Date.now();
        
        // Rate limit automatic exports to prevent spam
        if (now - this.lastExportTime < this.minExportInterval) {
            logger.warn('⏰ Diagnostic export rate limited - too soon after last export');
            return;
        }
        
        this.lastExportTime = now;
        this.exportDiagnostics(reason, errorData);
    }
    
    /**
     * Collect comprehensive diagnostic data
     */
    collectDiagnosticData(reason, errorData = {}) {
        const diagnostics = {
            export_reason: reason,
            timestamp: new Date().toISOString(),
            timestamp_unix: Date.now(),
            
            // Error information
            error: errorData,
            
            // Browser information
            browser: {
                userAgent: navigator.userAgent,
                platform: navigator.platform,
                language: navigator.language,
                onLine: navigator.onLine,
                cookieEnabled: navigator.cookieEnabled,
                doNotTrack: navigator.doNotTrack,
                hardwareConcurrency: navigator.hardwareConcurrency,
                maxTouchPoints: navigator.maxTouchPoints,
                vendor: navigator.vendor,
                vendorSub: navigator.vendorSub,
                appVersion: navigator.appVersion
            },
            
            // Screen information
            screen: {
                width: window.screen.width,
                height: window.screen.height,
                availWidth: window.screen.availWidth,
                availHeight: window.screen.availHeight,
                colorDepth: window.screen.colorDepth,
                pixelDepth: window.screen.pixelDepth,
                orientation: window.screen.orientation?.type
            },
            
            // Window information
            window: {
                innerWidth: window.innerWidth,
                innerHeight: window.innerHeight,
                outerWidth: window.outerWidth,
                outerHeight: window.outerHeight,
                devicePixelRatio: window.devicePixelRatio,
                isSecureContext: window.isSecureContext
            },
            
            // Room state
            room: this.collectRoomState(),
            
            // PeerJS state
            peers: this.collectPeerState(),
            
            // Media state
            media: this.collectMediaState(),
            
            // Recording state
            recording: this.collectRecordingState(),
            
            // All module logs
            logs: LoggerRegistry.getAllHistory()
        };
        
        return diagnostics;
    }
    
    /**
     * Collect room state information
     */
    collectRoomState() {
        if (!this.roomWebRTC) {
            return { error: 'RoomWebRTC not available' };
        }
        
        try {
            return {
                roomId: this.roomWebRTC.roomData?.id,
                roomName: this.roomWebRTC.roomData?.name,
                inviteCode: this.roomWebRTC.roomData?.invite_code,
                currentUserId: this.roomWebRTC.currentUserId,
                currentSlotId: this.roomWebRTC.currentSlotId,
                isJoined: this.roomWebRTC.isJoined,
                slotOccupants: Array.from(this.roomWebRTC.slotOccupants?.entries() || []),
                sttEnabled: this.roomWebRTC.roomData?.stt_enabled,
                recordingEnabled: this.roomWebRTC.roomData?.recording_enabled
            };
        } catch (error) {
            return { error: 'Failed to collect room state', details: error.message };
        }
    }
    
    /**
     * Collect PeerJS connection state
     */
    collectPeerState() {
        if (!this.roomWebRTC?.simplePeerManager) {
            return { error: 'SimplePeerManager not available' };
        }
        
        try {
            const manager = this.roomWebRTC.simplePeerManager;
            return {
                peerId: manager.peerId,
                isConnected: manager.peer?.disconnected === false,
                peerOpen: manager.peer?.open || false,
                peerDestroyed: manager.peer?.destroyed || false,
                activePeers: manager.getActivePeerIds ? manager.getActivePeerIds() : [],
                peerCount: manager.connections?.size || 0,
                connectionStates: this.collectConnectionStates(manager)
            };
        } catch (error) {
            return { error: 'Failed to collect peer state', details: error.message };
        }
    }
    
    /**
     * Collect individual connection states
     */
    collectConnectionStates(manager) {
        const states = [];
        if (manager.connections) {
            for (const [peerId, conn] of manager.connections.entries()) {
                states.push({
                    peerId,
                    open: conn.open,
                    type: conn.type,
                    reliable: conn.reliable,
                    serialization: conn.serialization
                });
            }
        }
        return states;
    }
    
    /**
     * Collect media device and stream state
     */
    collectMediaState() {
        if (!this.roomWebRTC?.mediaManager) {
            return { error: 'MediaManager not available' };
        }
        
        try {
            const manager = this.roomWebRTC.mediaManager;
            return {
                hasLocalStream: !!manager.localStream,
                audioTracks: manager.localStream?.getAudioTracks().map(t => ({
                    id: t.id,
                    kind: t.kind,
                    label: t.label,
                    enabled: t.enabled,
                    muted: t.muted,
                    readyState: t.readyState
                })) || [],
                videoTracks: manager.localStream?.getVideoTracks().map(t => ({
                    id: t.id,
                    kind: t.kind,
                    label: t.label,
                    enabled: t.enabled,
                    muted: t.muted,
                    readyState: t.readyState,
                    settings: t.getSettings()
                })) || [],
                isMicrophoneMuted: manager.isMicrophoneMuted,
                isVideoHidden: manager.isVideoHidden
            };
        } catch (error) {
            return { error: 'Failed to collect media state', details: error.message };
        }
    }
    
    /**
     * Collect recording state
     */
    collectRecordingState() {
        if (!this.roomWebRTC?.videoRecorder) {
            return { error: 'VideoRecorder not available' };
        }
        
        try {
            const recorder = this.roomWebRTC.videoRecorder;
            return {
                isRecording: recorder.isCurrentlyRecording(),
                state: recorder.getState ? recorder.getState() : 'unknown',
                mimeType: recorder.getRecordingMimeType ? recorder.getRecordingMimeType() : null,
                chunkCount: recorder.recordedChunks?.length || 0,
                totalBytes: recorder.recordedChunks?.reduce((sum, chunk) => sum + chunk.size, 0) || 0,
                storageProvider: this.roomWebRTC.roomData?.recording_settings?.storage_provider
            };
        } catch (error) {
            return { error: 'Failed to collect recording state', details: error.message };
        }
    }
    
    /**
     * Export diagnostics to JSON file
     */
    exportDiagnostics(reason, errorData = {}) {
        try {
            logger.info(`📊 Exporting diagnostics for: ${reason}`);
            
            // Collect all diagnostic data
            const diagnostics = this.collectDiagnosticData(reason, errorData);
            
            // Create JSON blob
            const json = JSON.stringify(diagnostics, null, 2);
            const blob = new Blob([json], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            
            // Create download link
            const a = document.createElement('a');
            a.href = url;
            a.download = `diagnostics-${reason}-${Date.now()}.json`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            
            logger.info(`✅ Diagnostics exported successfully: ${a.download}`);
            
            // Also log a summary to console
            this.logDiagnosticSummary(diagnostics);
        } catch (error) {
            logger.error('❌ Failed to export diagnostics:', error);
        }
    }
    
    /**
     * Log a summary of diagnostic data to console
     */
    logDiagnosticSummary(diagnostics) {
        logger.group('📊 Diagnostic Summary', false);
        logger.info(`Reason: ${diagnostics.export_reason}`);
        logger.info(`Timestamp: ${diagnostics.timestamp}`);
        logger.info(`Room: ${diagnostics.room?.roomName} (ID: ${diagnostics.room?.roomId})`);
        logger.info(`User: ${diagnostics.room?.currentUserId}`);
        logger.info(`Peer ID: ${diagnostics.peers?.peerId}`);
        logger.info(`Active Peers: ${diagnostics.peers?.activePeers?.length || 0}`);
        logger.info(`Recording: ${diagnostics.recording?.isRecording ? 'Active' : 'Inactive'}`);
        logger.info(`Total Logs: ${diagnostics.logs?.length || 0} modules`);
        logger.groupEnd();
    }
}

