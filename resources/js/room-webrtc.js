/**
 * RoomWebRTC - Handles peer-to-peer video conferencing, recording, and speech-to-text
 * for DaggerHeart room sessions.
 * 
 * Features:
 * - WebRTC peer-to-peer video/audio connections via Ably signaling
 * - Video recording with chunked uploads (30s segments)
 * - Speech-to-text with live transcription
 * - Unified consent management for recording and STT
 * - Backpressure handling for upload queues
 * - Audio-only fallback support
 */
import { DiagnosticsRunner } from './room/utils/DiagnosticsRunner.js';
import { PageProtection } from './room/utils/PageProtection.js';
import { ICEConfigManager } from './room/webrtc/ICEConfigManager.js';
import { PeerConnectionManager } from './room/webrtc/PeerConnectionManager.js';
import { MediaManager } from './room/webrtc/MediaManager.js';
import { AblyManager } from './room/messaging/AblyManager.js';
import { MessageHandler } from './room/messaging/MessageHandler.js';
import { VideoRecorder } from './room/recording/VideoRecorder.js';
import { StreamingDownloader } from './room/recording/StreamingDownloader.js';
import { CloudUploader } from './room/recording/CloudUploader.js';
import { SpeechManager } from './room/speech/SpeechManager.js';
import { StatusBarManager } from './room/ui/StatusBarManager.js';
import { SlotManager } from './room/ui/SlotManager.js';
import { UIStateManager } from './room/ui/UIStateManager.js';
import { ConsentManager } from './room/consent/ConsentManager.js';
import { ConsentDialog } from './room/consent/ConsentDialog.js';

export default class RoomWebRTC {
    constructor(roomData) {
        this.roomData = roomData;
        this.localStream = null;
        this.peerConnections = new Map(); // Map of peerId -> RTCPeerConnection
        this.slotOccupants = new Map(); // Map of slotId -> {peerId, stream, participantData}
        this.currentSlotId = null;
        this.currentPeerId = null;
        this.ablyChannel = null;
        this.isJoined = false;
        this.currentUserId = window.currentUserId; // Should be set by Blade template
        
        // Core WebRTC properties (kept for backward compatibility)
        this.pendingIce = new Map(); // Map<peerId, RTCIceCandidateInit[]>
        
        // Initialize core managers
        this.iceManager = new ICEConfigManager();
        this.peerConnectionManager = new PeerConnectionManager(this);
        this.mediaManager = new MediaManager(this);
        this.ablyManager = new AblyManager(this);
        this.messageHandler = new MessageHandler(this);
        
        // Initialize recording managers
        this.videoRecorder = new VideoRecorder(this);
        this.streamingDownloader = new StreamingDownloader(this);
        this.cloudUploader = new CloudUploader(this);
        
        // Initialize speech managers
        this.speechManager = new SpeechManager(this);
        
        // Initialize UI managers
        this.pageProtection = new PageProtection();
        this.statusBarManager = new StatusBarManager(this);
        this.slotManager = new SlotManager(this);
        this.uiStateManager = new UIStateManager(this);
        this.consentManagerUI = new ConsentManager(this);
        this.consentDialog = new ConsentDialog();
        
        // Setup page refresh protection
        this.pageProtection.setEmergencySaveCallback((chunks, mimeType) => {
            this.emergencySaveRecording(chunks, mimeType);
        });

        this.init();
    }

    /**
     * Updates existing peer connections with new ICE configuration
     */
    updateExistingPeerConnections() {
        if (this.peerConnections.size === 0) return;
        
        console.log('🧊 Updating existing peer connections with new ICE configuration');
        
        this.peerConnections.forEach((connection, peerId) => {
            this.iceManager.updatePeerConnection(connection, peerId);
        });
    }

    async init() {
        console.log('🎬 Initializing Room WebRTC for room:', this.roomData.name);
        
        // Load ICE configuration early (don't await to avoid blocking UI)
        this.iceManager.loadIceServers().catch(error => {
            console.warn('🧊 Non-blocking ICE config load failed:', error);
        });
        
        // Set up ICE config update callback for existing connections
        this.iceManager.onConfigUpdate(() => {
            this.updateExistingPeerConnections();
        });
        
        // Initialize speech recognition
        this.initializeSpeechRecognition().catch(error => {
            console.warn('🎤 Non-blocking speech initialization failed:', error);
        });
        
        // Initialize video recording
        this.initializeVideoRecording();
        
        // Add click handlers to all join buttons
        document.querySelectorAll('.join-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                const slotContainer = e.target.closest('.video-slot');
                const slotId = parseInt(slotContainer.dataset.slotId);
                
                if (!this.isJoined) {
                    this.joinSlot(slotId, slotContainer);
                }
            });
        });

        // Add click handlers to all leave buttons
        document.querySelectorAll('.leave-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                this.leaveSlot();
            });
        });

        // Connect to room-specific Ably channel
        this.connectToAblyChannel();
    }

    /**
     * Checks consent requirements immediately upon entering the room
     * This shows consent dialogs before the user tries to join a slot
     */
    async checkInitialConsentRequirements() {
        console.log('🔒 Checking initial consent requirements...');
        
        // Delegate to the modular ConsentManager
        return this.consentManagerUI.checkInitialConsent();
    }

    // ===========================================
    // ABLY REALTIME MESSAGING SYSTEM
    // ===========================================

    /**
     * Connects to the room-specific Ably channel when client is ready
     */
    async connectToAblyChannel() {
        // Generate peer ID if we don't have one
        if (!this.currentPeerId) {
            this.currentPeerId = this.generatePeerId();
            console.log(`🆔 Generated viewer peer ID: ${this.currentPeerId}`);
        }
        
        // Wait for Ably to be available and connect
        const connectWhenReady = async () => {
            if (window.ably) {
                try {
                    console.log('🔗 Connecting to Ably via AblyManager...');
                    await this.ablyManager.connectToChannel();
                    
                    // Request current room state after connection is established
                    setTimeout(() => {
                        console.log('📡 Requesting current room state...');
                        this.ablyManager.publishMessage('request-state', {
                            requesterId: this.currentPeerId,
                            userId: this.currentUserId
                        });
                    }, 500);
                } catch (error) {
                    console.error('🔗 Failed to connect to Ably:', error);
                }
            } else {
                // Wait for Ably client to be initialized
                setTimeout(connectWhenReady, 100);
            }
        };
        
        connectWhenReady();
    }

    /**
     * Generates a unique peer ID for this session
     */
    generatePeerId() {
        return Math.random().toString(36).substr(2, 9);
    }

    // ===========================================
    // ROOM SLOT MANAGEMENT SYSTEM
    // ===========================================

    /**
     * Joins a video slot with media access, consent handling, and peer connections
     */
    async joinSlot(slotId, slotContainer) {
        try {
            this.currentSlotId = slotId;
            
            // Check if slot is already occupied
            if (this.slotOccupants.has(slotId)) {
                console.log('⚠️ Slot already occupied');
                return;
            }

            // Disable all join buttons until consent is resolved
            this.disableJoinUI('Checking permissions...');

            // Show loading state
            this.showLoadingState(slotContainer);

            // Generate peer ID if we don't have one
            if (!this.currentPeerId) {
                this.currentPeerId = this.generatePeerId();
                console.log(`🆔 Generated peer ID: ${this.currentPeerId}`);
            }

            // Get user media with fallback for audio-only
            console.log('🎥 Requesting user media...');
            try {
                this.localStream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        width: { ideal: 1280 },
                        height: { ideal: 720 },
                        frameRate: { ideal: 30 }
                    },
                    audio: {
                        echoCancellation: true,
                        noiseSuppression: true,
                        sampleRate: 44100,
                        channelCount: 1 // Smaller payload for recording
                    }
                });
            } catch (error) {
                if (error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError') {
                    // Try audio-only if video is denied but mic is allowed
                    console.log('🎥 Video denied, trying audio-only...');
                    try {
                        this.localStream = await navigator.mediaDevices.getUserMedia({
                            video: false,
                            audio: {
                                echoCancellation: true,
                                noiseSuppression: true,
                                sampleRate: 44100,
                                channelCount: 1
                            }
                        });
                        console.log('🎤 Audio-only stream obtained');
                    } catch (audioError) {
                        throw error; // Throw original error if both fail
                    }
                } else {
                    throw error;
                }
            }

            // Find participant data for this user
            const participantData = this.roomData.participants.find(p => p.user_id === this.currentUserId);

            // Set up local video
            this.setupLocalVideo(slotContainer, this.localStream, participantData);

            // Mark this slot as occupied by us
            this.slotOccupants.set(slotId, {
                peerId: this.currentPeerId,
                stream: this.localStream,
                participantData: participantData,
                isLocal: true
            });
            
            // Update slot display
            this.slotManager.updateSlotOccupied(slotId, participantData, this.localStream);
            this.slotManager.highlightCurrentUserSlot(slotId);

            this.isJoined = true;

            // Start features that have consent now that we have media access
            this.startConsentedFeatures();

            // Announce our presence to the room
            this.publishToAbly('user-joined', {
                slotId: slotId,
                participantData: participantData
            });

            // Hide loading state and show controls
            this.hideLoadingState(slotContainer);
            this.showVideoControls(slotContainer);

            // Consent requirements are already handled by checkInitialConsentRequirements
            console.log('🔒 Consent requirements already handled during initialization');

        } catch (error) {
            console.error('❌ Error joining slot:', error);
            this.hideLoadingState(slotContainer);
            // Fix #5: Re-enable join UI on permission failure
            this.enableJoinUI();
            alert('Failed to access camera/microphone. Please check permissions.');
        }
    }

    setupLocalVideo(slotContainer, stream, participantData) {
        const videoElement = slotContainer.querySelector('.local-video');
        videoElement.srcObject = stream;
        videoElement.playsInline = true; // Avoid fullscreen on iOS
        videoElement.muted = true;       // Avoid autoplay blocks and feedback loops
        videoElement.style.display = 'block';
        
        // Check if stream has video tracks
        const hasVideo = stream.getVideoTracks().length > 0;
        const hasAudio = stream.getAudioTracks().length > 0;
        
        if (!hasVideo && hasAudio) {
            // Audio-only: show audio indicator instead of video
            videoElement.style.display = 'none';
            let audioIndicator = slotContainer.querySelector('.audio-only-indicator');
            if (!audioIndicator) {
                audioIndicator = document.createElement('div');
                audioIndicator.className = 'audio-only-indicator absolute inset-0 flex items-center justify-center bg-slate-800 rounded-lg';
                audioIndicator.innerHTML = `
                    <div class="text-center">
                        <div class="w-16 h-16 bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-2">
                            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                            </svg>
                        </div>
                        <p class="text-slate-400 text-sm">Audio Only</p>
                    </div>
                `;
                slotContainer.appendChild(audioIndicator);
            }
        }
        
        // Show character overlay with participant data
        this.showCharacterOverlay(slotContainer, participantData);
        
        console.log(`📹 Local ${hasVideo ? 'video' : 'audio-only'} set up for participant:`, participantData?.character_name || participantData?.username);
    }

    showCharacterOverlay(slotContainer, participantData) {
        const overlay = slotContainer.querySelector('.character-overlay');
        if (overlay && participantData) {
            // Update character name
            const nameElement = overlay.querySelector('.character-name');
            if (nameElement) {
                nameElement.textContent = participantData.character_name || participantData.username;
            }

            // Update character class
            const classElement = overlay.querySelector('.character-class');
            if (classElement) {
                classElement.textContent = participantData.character_class || 'No Class';
            }

            overlay.classList.remove('hidden');
        }
    }

    showLoadingState(slotContainer) {
        const loadingSpinner = slotContainer.querySelector('.loading-spinner');
        const joinBtn = slotContainer.querySelector('.join-btn');
        
        if (loadingSpinner) {
            loadingSpinner.classList.remove('hidden');
            loadingSpinner.style.display = 'flex';
        }
        if (joinBtn) {
            joinBtn.style.display = 'none';
        }
    }

    hideLoadingState(slotContainer) {
        const loadingSpinner = slotContainer.querySelector('.loading-spinner');
        if (loadingSpinner) {
            loadingSpinner.classList.add('hidden');
            loadingSpinner.style.display = 'none';
        }
    }

    showVideoControls(slotContainer) {
        const leaveBtn = slotContainer.querySelector('.leave-btn');
        if (leaveBtn) {
            leaveBtn.style.display = 'block';
            leaveBtn.classList.remove('hidden');
        }
    }

    /**
     * Leaves the current slot, stopping media and cleaning up connections
     */
    async leaveSlot() {
        if (!this.isJoined || !this.currentSlotId) {
            console.log('❌ Not currently in a slot');
            return;
        }

        console.log('🚪 Leaving slot:', this.currentSlotId);

        // Announce we're leaving
        this.publishToAbly('user-left', {
            slotId: this.currentSlotId
        });

        // Stop local stream
        if (this.localStream) {
            this.localStream.getTracks().forEach(track => {
                track.stop();
            });
            this.localStream = null;
        }

        // Close all peer connections
        this.peerConnections.forEach(pc => {
            pc.close();
        });
        this.peerConnections.clear();

        // Clear slot occupancy
        const leavingSlotId = this.currentSlotId;
        this.slotOccupants.delete(this.currentSlotId);
        
        // Update slot display
        this.slotManager.updateSlotEmpty(leavingSlotId);
        this.slotManager.removeCurrentUserHighlight();

        // Reset state
        const slotContainer = document.querySelector(`[data-slot-id="${this.currentSlotId}"]`);
        this.resetSlotUI(slotContainer);

        this.currentSlotId = null;
        this.isJoined = false;

        // Stop speech recognition
        this.stopSpeechRecognition();
        
        // Stop video recording
        this.stopVideoRecording();

        console.log('✅ Successfully left slot');
    }

    resetSlotUI(slotContainer) {
        if (!slotContainer) return;

        // Hide video
        const videoElement = slotContainer.querySelector('.local-video');
        if (videoElement) {
            videoElement.style.display = 'none';
            videoElement.srcObject = null;
        }

        // Hide character overlay
        const overlay = slotContainer.querySelector('.character-overlay');
        if (overlay) {
            overlay.classList.add('hidden');
        }

        // Show join button, hide leave button
        const joinBtn = slotContainer.querySelector('.join-btn');
        const leaveBtn = slotContainer.querySelector('.leave-btn');
        
        if (joinBtn) joinBtn.style.display = 'block';
        if (leaveBtn) {
            leaveBtn.style.display = 'none';
            leaveBtn.classList.add('hidden');
        }

        // Hide loading state
        this.hideLoadingState(slotContainer);
    }

    handleAblyMessage(message) {
        const { type, data, senderId, targetPeerId } = message.data;

        // Defensive: double-check targeting
        if (targetPeerId && targetPeerId !== this.currentPeerId) return;

        console.log('🎭 Handling room message:', type, 'from:', senderId);

        switch (type) {
            case 'request-state':
                this.handleStateRequest(data, senderId);
                break;
            case 'user-joined':
                this.handleUserJoined(data, senderId);
                break;
            case 'user-left':
                this.handleUserLeft(data, senderId);
                break;
            case 'webrtc-offer':
                this.handleOffer(data, senderId);
                break;
            case 'webrtc-answer':
                this.handleAnswer(data, senderId);
                break;
            case 'webrtc-ice-candidate':
                this.handleIceCandidate(data, senderId);
                break;
            default:
                console.log('🤷 Unknown room message type:', type);
        }
    }

    handleStateRequest(data, senderId) {
        // If we're in a slot, tell the specific requester about our presence
        if (this.isJoined && this.currentSlotId) {
            const participantData = this.roomData.participants.find(p => p.user_id === this.currentUserId);
            // Scope reply to specific requester to reduce chatter
            this.publishToAbly('user-joined', {
                slotId: this.currentSlotId,
                participantData: participantData
            }, data.requesterId || senderId); // Use requesterId if available
        }
    }

    handleUserJoined(data, senderId) {
        console.log('👋 User joined slot:', data.slotId, 'participant:', data.participantData?.character_name);
        
        // Update slot occupancy
        this.slotOccupants.set(data.slotId, {
            peerId: senderId,
            participantData: data.participantData,
            isLocal: false
        });
        
        // Update slot display
        this.slotManager.updateSlotOccupied(data.slotId, data.participantData);

        // If we're also in a slot, initiate WebRTC connection
        // Fix #2: Prevent offer "glare" - only lower peerId initiates
        if (this.isJoined && this.currentSlotId && this.currentSlotId !== data.slotId) {
            if (this.currentPeerId && this.currentPeerId < senderId) {
                this.initiateWebRTCConnection(senderId);
            }
        }
    }

    handleUserLeft(data, senderId) {
        console.log('👋 User left slot:', data.slotId);
        
        // Remove from slot occupancy
        this.slotOccupants.delete(data.slotId);
        
        // Update slot display
        this.slotManager.updateSlotEmpty(data.slotId);
        
        // Close peer connection if exists
        if (this.peerConnections.has(senderId)) {
            this.peerConnections.get(senderId).close();
            this.peerConnections.delete(senderId);
        }

        // Clear remote video if displayed
        this.clearRemoteVideo(senderId);
    }

    // ===========================================
    // WEBRTC PEER CONNECTION MANAGEMENT
    // ===========================================

    /**
     * Initiates a WebRTC connection by sending an offer to a remote peer
     */
    async initiateWebRTCConnection(remotePeerId) {
        console.log('🤝 Initiating WebRTC connection with:', remotePeerId);
        
        try {
            const peerConnection = this.createPeerConnection(remotePeerId);
            
            // Create and send offer
            const offer = await peerConnection.createOffer();
            await peerConnection.setLocalDescription(offer);
            
            this.publishToAbly('webrtc-offer', { offer }, remotePeerId);
            
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
            await peerConnection.setRemoteDescription(data.offer);
            
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
            
            const answer = await peerConnection.createAnswer();
            await peerConnection.setLocalDescription(answer);

            this.publishToAbly('webrtc-answer', { answer }, senderId);
            
        } catch (error) {
            this.handleWebRTCError('Failed to handle offer', error, senderId);
        }
    }

    /**
     * Creates and configures a new peer connection with common setup
     */
    createPeerConnection(peerId) {
        // Use loaded ICE configuration, with fallback update if not ready yet
        const peerConnection = new RTCPeerConnection({
            ...this.iceManager.getConfig(),
            // IMPORTANT: Do NOT set iceTransportPolicy:'relay' to allow natural candidate preference
            // iceCandidatePoolSize: 1, // Optional: pre-gather candidates
        });
        
        this.peerConnections.set(peerId, peerConnection);
        
        // If ICE config isn't ready yet, update this connection when it arrives
        if (!this.iceManager.isReady()) {
            this.iceManager.onConfigUpdate((config) => {
                this.iceManager.updatePeerConnection(peerConnection, peerId);
            });
        }

        // Add local stream tracks
        if (this.localStream) {
            this.localStream.getTracks().forEach(track => {
                peerConnection.addTrack(track, this.localStream);
            });
        }

        // Handle remote stream
        peerConnection.ontrack = (event) => {
            console.log('📡 Received remote stream from:', peerId);
            this.handleRemoteStream(event.streams[0], peerId);
        };

        // Handle ICE candidates
        peerConnection.onicecandidate = (event) => {
            if (event.candidate) {
                this.publishToAbly('webrtc-ice-candidate', {
                    candidate: event.candidate
                }, peerId);
            }
        };

        // Monitor connection state for cleanup and telemetry
        peerConnection.onconnectionstatechange = async () => {
            const state = peerConnection.connectionState;
            console.log(`🔗 Peer connection state: ${state} (${peerId})`);
            
            // Log candidate pair information when connected for telemetry
            if (state === 'connected' || state === 'completed') {
                this.iceManager.logCandidatePairStats(peerConnection, peerId);
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
        
        this.clearRemoteVideo(peerId);
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
     * Handles incoming WebRTC answer
     */
    async handleAnswer(data, senderId) {
        console.log('✅ Received WebRTC answer from:', senderId);

        try {
            const peerConnection = this.peerConnections.get(senderId);
            if (peerConnection) {
                await peerConnection.setRemoteDescription(data.answer);
                
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

    handleRemoteStream(stream, senderId) {
        console.log('📺 Setting up remote video for peer:', senderId);

        // Find which slot this peer occupies
        let targetSlotId = null;
        for (const [slotId, occupant] of this.slotOccupants) {
            if (occupant.peerId === senderId) {
                targetSlotId = slotId;
                break;
            }
        }

        if (targetSlotId) {
            const slotContainer = document.querySelector(`[data-slot-id="${targetSlotId}"]`);
            if (slotContainer) {
                this.setupRemoteVideo(slotContainer, stream, this.slotOccupants.get(targetSlotId).participantData);
            }
        }
    }

    setupRemoteVideo(slotContainer, stream, participantData) {
        // Create or get remote video element
        let videoElement = slotContainer.querySelector('.remote-video');
        if (!videoElement) {
            videoElement = document.createElement('video');
            videoElement.className = 'remote-video w-full h-full object-cover';
            videoElement.autoplay = true;
            videoElement.playsInline = true;
            
            const remoteContainer = slotContainer.querySelector('.remote-videos');
            if (remoteContainer) {
                remoteContainer.appendChild(videoElement);
                remoteContainer.classList.remove('hidden');
            }
        }

        // Fix: Set dataset.peerId for proper cleanup
        const targetSlotId = parseInt(slotContainer.dataset.slotId);
        const peerId = this.slotOccupants.get(targetSlotId)?.peerId;
        if (peerId) {
            videoElement.dataset.peerId = peerId;
        }

        videoElement.srcObject = stream;
        
        // Show character overlay
        this.showCharacterOverlay(slotContainer, participantData);
        
        // Hide join button since slot is occupied
        const joinBtn = slotContainer.querySelector('.join-btn');
        if (joinBtn) {
            joinBtn.style.display = 'none';
        }
    }

    clearRemoteVideo(senderId) {
        // Find and clear remote video for this peer
        const remoteVideos = document.querySelectorAll('.remote-video');
        remoteVideos.forEach(video => {
            if (video.dataset.peerId === senderId) {
                video.remove();
            }
        });
        
        // Hide empty remote-videos containers
        document.querySelectorAll('.remote-videos').forEach(container => {
            if (!container.querySelector('.remote-video')) {
                container.classList.add('hidden');
            }
        });
    }

    // ===========================================
    // UI STATE MANAGEMENT
    // ===========================================

    /**
     * Disables all join buttons with a custom message
     */
    disableJoinUI(message = 'Please wait...') {
        document.querySelectorAll('.join-btn').forEach(button => {
            button.disabled = true;
            button.style.opacity = '0.5';
            button.style.cursor = 'not-allowed';
            const originalText = button.textContent;
            button.dataset.originalText = originalText;
            button.textContent = message;
        });
    }

    /**
     * Disables all join buttons and UI interactions until consent is resolved
     */
    disableJoinUI() {
        document.querySelectorAll('.join-btn').forEach(button => {
            button.disabled = true;
            button.style.opacity = '0.5';
            button.style.cursor = 'not-allowed';
            if (!button.dataset.originalText) {
                button.dataset.originalText = button.textContent;
            }
            button.textContent = 'Awaiting Consent...';
        });
        
        // Also disable other interactive elements
        document.querySelectorAll('[data-testid="participant-count"], [data-testid="leave-room-button"]').forEach(element => {
            element.disabled = true;
            element.style.opacity = '0.5';
            element.style.pointerEvents = 'none';
        });
    }

    /**
     * Re-enables all join buttons with their original text
     */
    enableJoinUI() {
        document.querySelectorAll('.join-btn').forEach(button => {
            button.disabled = false;
            button.style.opacity = '';
            button.style.cursor = '';
            if (button.dataset.originalText) {
                button.textContent = button.dataset.originalText;
                delete button.dataset.originalText;
            }
        });
        
        // Re-enable other interactive elements
        document.querySelectorAll('[data-testid="participant-count"], [data-testid="leave-room-button"]').forEach(element => {
            element.disabled = false;
            element.style.opacity = '';
            element.style.pointerEvents = '';
        });
    }

    /**
     * Starts features that have consent after user joins a slot and has media access
     */
    startConsentedFeatures() {
        console.log('🎤 === Starting Consented Features ===');
        
        // Check if STT is enabled and has consent, then start it
        if (this.roomData.stt_enabled && this.localStream && this.localStream.getAudioTracks().length > 0) {
            console.log('🎤 Starting speech recognition - STT enabled and audio available');
            setTimeout(() => {
                this.startSpeechRecognition();
            }, 1000);
        }
        
        // Check if recording is enabled and has consent, then start it
        if (this.roomData.recording_enabled && this.localStream) {
            console.log('🎥 Starting video recording - recording enabled and stream available');
            this.startVideoRecording();
        }
    }

    // ===========================================
    // UNIFIED CONSENT MANAGEMENT SYSTEM (Modular)
    // ===========================================

    /**
     * Handles all consent requirements - delegated to checkInitialConsentRequirements
     */
    async handleConsentRequirements() {
        // This is handled by checkInitialConsentRequirements during initialization
        console.log('🔒 Consent requirements handled during initialization');
        return Promise.resolve();
    }

    /**
     * Checks consent status using the modular ConsentManager
     */
    async checkConsentStatus(type) {
        return this.consentManagerUI.checkConsentStatus(type);
    }

    /**
     * Disables join UI using the modular UIStateManager
     */
    disableJoinUI(message = 'Please wait...') {
        // Use the UIStateManager's state system to disable join buttons
        this.uiStateManager.setLoadingState('join', true, message);
        
        // Also directly disable join buttons for immediate feedback
        document.querySelectorAll('.join-btn').forEach(button => {
            button.disabled = true;
            button.style.opacity = '0.5';
            button.style.cursor = 'not-allowed';
            const originalText = button.textContent;
            button.dataset.originalText = originalText;
            button.textContent = message;
        });
    }

    /**
     * Enables join UI using the modular UIStateManager
     */
    enableJoinUI() {
        // Use the UIStateManager's state system to enable join buttons
        this.uiStateManager.setLoadingState('join', false);
        
        // Also directly enable join buttons for immediate feedback
        document.querySelectorAll('.join-btn').forEach(button => {
            button.disabled = false;
            button.style.opacity = '';
            button.style.cursor = '';
            if (button.dataset.originalText) {
                button.textContent = button.dataset.originalText;
                delete button.dataset.originalText;
            }
        });
    }

    // ===========================================
    // ===========================================
    // SPEECH-TO-TEXT SYSTEM (Modular)
    // ===========================================

    /**
     * Initializes speech recognition using the modular SpeechManager
     */
    async initializeSpeechRecognition() {
        try {
            await this.speechManager.initializeSpeechRecognition();
        } catch (error) {
            console.error('🎤 Failed to initialize speech recognition:', error);
        }
    }

    /**
     * Starts speech recognition using the modular SpeechManager
     */
    async startSpeechRecognition() {
        try {
            await this.speechManager.startSpeechRecognition();
        } catch (error) {
            console.error('🎤 Failed to start speech recognition:', error);
        }
    }

    /**
     * Stops speech recognition using the modular SpeechManager
     */
    async stopSpeechRecognition() {
        try {
            await this.speechManager.stopSpeechRecognition();
        } catch (error) {
            console.error('🎤 Failed to stop speech recognition:', error);
        }
    }

    // ===========================================
    // VIDEO RECORDING SYSTEM (Modular)
    // ===========================================

    /**
     * Initializes video recording using the modular VideoRecorder
     */
    async initializeVideoRecording() {
        try {
            await this.videoRecorder.initializeRecording();
        } catch (error) {
            console.error('🎬 Failed to initialize video recording:', error);
        }
    }

    /**
     * Starts video recording using the modular VideoRecorder
     */
    async startVideoRecording() {
        try {
            const storageProvider = this.roomData.recording_settings?.storage_provider || 'local_device';
            await this.videoRecorder.startRecording(storageProvider);
        } catch (error) {
            console.error('🎬 Failed to start video recording:', error);
        }
    }

    /**
     * Stops video recording using the modular VideoRecorder
     */
    async stopVideoRecording() {
        try {
            await this.videoRecorder.stopRecording();
            // Also leave the room after stopping recording
            await this.leaveRoom();
        } catch (error) {
            console.error('🎬 Failed to stop video recording:', error);
        }
    }

    // ===========================================
    // WEBRTC PEER CONNECTION MANAGEMENT (Modular)
    // ===========================================

    /**
     * Creates a new peer connection using the modular PeerConnectionManager
     */
    createPeerConnection(peerId) {
        return this.peerConnectionManager.createPeerConnection(peerId);
    }

    /**
     * Handles WebRTC offers using the modular PeerConnectionManager
     */
    async handleOffer(data) {
        return this.peerConnectionManager.handleOffer(data);
    }

    /**
     * Handles WebRTC answers using the modular PeerConnectionManager
     */
    async handleAnswer(data) {
        return this.peerConnectionManager.handleAnswer(data);
    }

    /**
     * Handles ICE candidates using the modular PeerConnectionManager
     */
    async handleIceCandidate(data) {
        return this.peerConnectionManager.handleIceCandidate(data);
    }

    /**
     * Publishes messages to Ably using the modular AblyManager
     */
    async publishToAbly(eventName, data, targetPeerId = null) {
        return this.ablyManager.publishMessage(eventName, data, targetPeerId);
    }

    /**
     * Emergency save for recordings during page unload
     */
    emergencySaveRecording(chunks, mimeType) {
        if (chunks && chunks.length > 0) {
            const blob = new Blob(chunks, { type: mimeType });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `emergency-recording-${Date.now()}.webm`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }
    }

    /**
     * Logs connection telemetry using the modular ICEConfigManager
     */
    logCandidatePairStats() {
        return this.iceManager.logCandidatePairStats(this.peerConnections);
    }

}
