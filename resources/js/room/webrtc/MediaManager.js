/**
 * MediaManager - Manages local and remote media streams
 * 
 * Handles camera/microphone access, stream management, media constraints,
 * and provides unified interface for media operations across the room system.
 */
export class MediaManager {
    constructor(roomWebRTC) {
        this.roomWebRTC = roomWebRTC;
        this.localStream = null;
        this.remoteStreams = new Map(); // Map of peerId -> MediaStream
        this.mediaConstraints = {
            audio: true,
            video: {
                width: { ideal: 1280, max: 1920 },
                height: { ideal: 720, max: 1080 },
                frameRate: { ideal: 30, max: 60 }
            }
        };
        this.deviceInfo = {
            cameras: [],
            microphones: [],
            speakers: []
        };
        
        this.setupEventHandlers();
    }

    /**
     * Sets up event handlers for media management
     */
    setupEventHandlers() {
        // Listen for device changes
        if (navigator.mediaDevices && navigator.mediaDevices.addEventListener) {
            navigator.mediaDevices.addEventListener('devicechange', () => {
                this.updateDeviceList();
            });
        }
    }

    /**
     * Gets user media with specified constraints
     */
    async getUserMedia(constraints = null) {
        try {
            console.log('🎥 Requesting user media access...');
            
            const mediaConstraints = constraints || this.mediaConstraints;
            console.log('🎥 Media constraints:', mediaConstraints);
            
            // Check if getUserMedia is available
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                throw new Error('getUserMedia is not supported in this browser');
            }
            
            // Request media access
            const stream = await navigator.mediaDevices.getUserMedia(mediaConstraints);
            
            console.log('🎥 ✅ Media access granted');
            console.log(`🎥 Stream tracks: ${stream.getTracks().length}`);
            
            // Log track details
            stream.getTracks().forEach((track, index) => {
                console.log(`🎥 Track ${index}: ${track.kind} (${track.label}) - enabled: ${track.enabled}, muted: ${track.muted}`);
            });
            
            // Store as local stream
            this.localStream = stream;
            
            // Update device list
            await this.updateDeviceList();
            
            return stream;
        } catch (error) {
            console.error('🎥 ❌ Failed to get user media:', error);
            this.handleMediaError(error);
            throw error;
        }
    }

    /**
     * Gets audio-only media stream
     */
    async getAudioOnlyMedia() {
        const audioConstraints = {
            audio: true,
            video: false
        };
        
        return await this.getUserMedia(audioConstraints);
    }

    /**
     * Gets video-only media stream
     */
    async getVideoOnlyMedia() {
        const videoConstraints = {
            audio: false,
            video: this.mediaConstraints.video
        };
        
        return await this.getUserMedia(videoConstraints);
    }

    /**
     * Handles media access errors
     */
    handleMediaError(error) {
        console.error('🎥 Media error details:', {
            name: error.name,
            message: error.message,
            constraint: error.constraint
        });
        
        switch (error.name) {
            case 'NotAllowedError':
            case 'PermissionDeniedError':
                console.error('🎥 ❌ Media access denied by user');
                this.showMediaPermissionError();
                break;
                
            case 'NotFoundError':
            case 'DevicesNotFoundError':
                console.error('🎥 ❌ No media devices found');
                this.showNoDevicesError();
                break;
                
            case 'NotReadableError':
            case 'TrackStartError':
                console.error('🎥 ❌ Media device is already in use');
                this.showDeviceInUseError();
                break;
                
            case 'OverconstrainedError':
            case 'ConstraintNotSatisfiedError':
                console.error('🎥 ❌ Media constraints cannot be satisfied');
                this.showConstraintError(error);
                break;
                
            case 'NotSupportedError':
                console.error('🎥 ❌ Media not supported in this browser');
                this.showNotSupportedError();
                break;
                
            default:
                console.error('🎥 ❌ Unknown media error:', error);
                this.showGenericMediaError(error);
        }
    }

    /**
     * Shows media permission error to user
     */
    showMediaPermissionError() {
        // This could integrate with a notification system
        console.warn('🎥 Please allow camera and microphone access to participate in the room');
    }

    /**
     * Shows no devices error to user
     */
    showNoDevicesError() {
        console.warn('🎥 No camera or microphone found. Please connect media devices to participate');
    }

    /**
     * Shows device in use error to user
     */
    showDeviceInUseError() {
        console.warn('🎥 Camera or microphone is being used by another application. Please close other applications and try again');
    }

    /**
     * Shows constraint error to user
     */
    showConstraintError(error) {
        console.warn(`🎥 Media constraint error: ${error.constraint}. Trying with reduced quality...`);
        // Could attempt fallback with lower quality constraints
    }

    /**
     * Shows not supported error to user
     */
    showNotSupportedError() {
        console.warn('🎥 Media access is not supported in this browser. Please use a modern browser like Chrome, Firefox, or Safari');
    }

    /**
     * Shows generic media error to user
     */
    showGenericMediaError(error) {
        console.warn(`🎥 Media access failed: ${error.message}`);
    }

    /**
     * Updates the list of available media devices
     */
    async updateDeviceList() {
        try {
            if (!navigator.mediaDevices || !navigator.mediaDevices.enumerateDevices) {
                console.warn('🎥 Device enumeration not supported');
                return;
            }
            
            const devices = await navigator.mediaDevices.enumerateDevices();
            
            this.deviceInfo.cameras = devices.filter(device => device.kind === 'videoinput');
            this.deviceInfo.microphones = devices.filter(device => device.kind === 'audioinput');
            this.deviceInfo.speakers = devices.filter(device => device.kind === 'audiooutput');
            
            console.log('🎥 Available devices:', {
                cameras: this.deviceInfo.cameras.length,
                microphones: this.deviceInfo.microphones.length,
                speakers: this.deviceInfo.speakers.length
            });
            
            return this.deviceInfo;
        } catch (error) {
            console.error('🎥 Failed to enumerate devices:', error);
            return this.deviceInfo;
        }
    }

    /**
     * Switches to a different camera
     */
    async switchCamera(deviceId) {
        try {
            console.log(`🎥 Switching to camera: ${deviceId}`);
            
            const newConstraints = {
                ...this.mediaConstraints,
                video: {
                    ...this.mediaConstraints.video,
                    deviceId: { exact: deviceId }
                }
            };
            
            const newStream = await this.getUserMedia(newConstraints);
            
            // Replace tracks in existing peer connections
            await this.replaceVideoTrack(newStream.getVideoTracks()[0]);
            
            console.log('🎥 ✅ Camera switched successfully');
            return newStream;
        } catch (error) {
            console.error('🎥 ❌ Failed to switch camera:', error);
            throw error;
        }
    }

    /**
     * Switches to a different microphone
     */
    async switchMicrophone(deviceId) {
        try {
            console.log(`🎤 Switching to microphone: ${deviceId}`);
            
            const newConstraints = {
                ...this.mediaConstraints,
                audio: {
                    deviceId: { exact: deviceId }
                }
            };
            
            const newStream = await this.getUserMedia(newConstraints);
            
            // Replace tracks in existing peer connections
            await this.replaceAudioTrack(newStream.getAudioTracks()[0]);
            
            console.log('🎤 ✅ Microphone switched successfully');
            return newStream;
        } catch (error) {
            console.error('🎤 ❌ Failed to switch microphone:', error);
            throw error;
        }
    }

    /**
     * Replaces video track in all peer connections
     */
    async replaceVideoTrack(newTrack) {
        const peerConnections = this.roomWebRTC.peerConnectionManager.peerConnections;
        
        for (const [peerId, peerConnection] of peerConnections) {
            try {
                const sender = peerConnection.getSenders().find(s => 
                    s.track && s.track.kind === 'video'
                );
                
                if (sender) {
                    await sender.replaceTrack(newTrack);
                    console.log(`🎥 Video track replaced for peer: ${peerId}`);
                }
            } catch (error) {
                console.error(`🎥 Failed to replace video track for peer ${peerId}:`, error);
            }
        }
    }

    /**
     * Replaces audio track in all peer connections
     */
    async replaceAudioTrack(newTrack) {
        const peerConnections = this.roomWebRTC.peerConnectionManager.peerConnections;
        
        for (const [peerId, peerConnection] of peerConnections) {
            try {
                const sender = peerConnection.getSenders().find(s => 
                    s.track && s.track.kind === 'audio'
                );
                
                if (sender) {
                    await sender.replaceTrack(newTrack);
                    console.log(`🎤 Audio track replaced for peer: ${peerId}`);
                }
            } catch (error) {
                console.error(`🎤 Failed to replace audio track for peer ${peerId}:`, error);
            }
        }
    }

    /**
     * Toggles video track enabled state
     */
    toggleVideo(enabled = null) {
        if (!this.localStream) {
            console.warn('🎥 No local stream available to toggle video');
            return false;
        }
        
        const videoTrack = this.localStream.getVideoTracks()[0];
        if (!videoTrack) {
            console.warn('🎥 No video track available to toggle');
            return false;
        }
        
        const newState = enabled !== null ? enabled : !videoTrack.enabled;
        videoTrack.enabled = newState;
        
        console.log(`🎥 Video ${newState ? 'enabled' : 'disabled'}`);
        return newState;
    }

    /**
     * Toggles audio track enabled state
     */
    toggleAudio(enabled = null) {
        if (!this.localStream) {
            console.warn('🎤 No local stream available to toggle audio');
            return false;
        }
        
        const audioTrack = this.localStream.getAudioTracks()[0];
        if (!audioTrack) {
            console.warn('🎤 No audio track available to toggle');
            return false;
        }
        
        const newState = enabled !== null ? enabled : !audioTrack.enabled;
        audioTrack.enabled = newState;
        
        console.log(`🎤 Audio ${newState ? 'enabled' : 'disabled'}`);
        return newState;
    }

    /**
     * Gets current media state
     */
    getMediaState() {
        if (!this.localStream) {
            return {
                hasVideo: false,
                hasAudio: false,
                videoEnabled: false,
                audioEnabled: false
            };
        }
        
        const videoTrack = this.localStream.getVideoTracks()[0];
        const audioTrack = this.localStream.getAudioTracks()[0];
        
        return {
            hasVideo: !!videoTrack,
            hasAudio: !!audioTrack,
            videoEnabled: videoTrack ? videoTrack.enabled : false,
            audioEnabled: audioTrack ? audioTrack.enabled : false
        };
    }

    /**
     * Adds remote stream for a peer
     */
    addRemoteStream(peerId, stream) {
        console.log(`🎥 Adding remote stream for peer: ${peerId}`);
        this.remoteStreams.set(peerId, stream);
        
        // Set up stream event handlers
        stream.addEventListener('addtrack', (event) => {
            console.log(`🎥 Track added to remote stream from ${peerId}:`, event.track);
        });
        
        stream.addEventListener('removetrack', (event) => {
            console.log(`🎥 Track removed from remote stream from ${peerId}:`, event.track);
        });
    }

    /**
     * Removes remote stream for a peer
     */
    removeRemoteStream(peerId) {
        console.log(`🎥 Removing remote stream for peer: ${peerId}`);
        this.remoteStreams.delete(peerId);
    }

    /**
     * Gets remote stream for a peer
     */
    getRemoteStream(peerId) {
        return this.remoteStreams.get(peerId);
    }

    /**
     * Gets all remote streams
     */
    getAllRemoteStreams() {
        return new Map(this.remoteStreams);
    }

    /**
     * Stops local media stream
     */
    stopLocalStream() {
        if (this.localStream) {
            console.log('🎥 Stopping local media stream');
            
            this.localStream.getTracks().forEach(track => {
                track.stop();
                console.log(`🎥 Stopped ${track.kind} track: ${track.label}`);
            });
            
            this.localStream = null;
        }
    }

    /**
     * Stops all remote streams
     */
    stopAllRemoteStreams() {
        console.log('🎥 Stopping all remote streams');
        
        this.remoteStreams.forEach((stream, peerId) => {
            stream.getTracks().forEach(track => {
                track.stop();
            });
        });
        
        this.remoteStreams.clear();
    }

    /**
     * Gets media stream statistics
     */
    getMediaStats() {
        const stats = {
            local: {
                stream: !!this.localStream,
                tracks: this.localStream ? this.localStream.getTracks().length : 0,
                video: this.localStream ? this.localStream.getVideoTracks().length : 0,
                audio: this.localStream ? this.localStream.getAudioTracks().length : 0
            },
            remote: {
                streams: this.remoteStreams.size,
                peers: Array.from(this.remoteStreams.keys())
            },
            devices: {
                cameras: this.deviceInfo.cameras.length,
                microphones: this.deviceInfo.microphones.length,
                speakers: this.deviceInfo.speakers.length
            }
        };
        
        return stats;
    }

    /**
     * Destroys the media manager and stops all streams
     */
    destroy() {
        console.log('🎥 Destroying MediaManager');
        
        this.stopLocalStream();
        this.stopAllRemoteStreams();
        
        // Clear device info
        this.deviceInfo = {
            cameras: [],
            microphones: [],
            speakers: []
        };
        
        console.log('🎥 MediaManager destroyed');
    }
}
