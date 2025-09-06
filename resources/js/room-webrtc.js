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
        
        // Speech recognition properties
        this.speechRecognition = null;
        this.speechBuffer = [];
        this.speechChunkStartedAt = null;
        this.speechUploadInterval = null;
        this.isSpeechEnabled = false;

        // Video recording properties
        this.mediaRecorder = null;
        this.recordedChunks = [];
        this.recordingStartTime = null;
        this.isRecording = false;

        // Unified consent management
        this.consentManager = {
            stt: { status: null, enabled: this.roomData.stt_enabled },
            recording: { status: null, enabled: this.roomData.recording_enabled }
        };
        
        // ICE candidate queuing for reliable signaling
        this.pendingIce = new Map(); // Map<peerId, RTCIceCandidateInit[]>
        
        // Recording MIME type (chosen once, used everywhere)
        this.recMime = null;
        
        // ICE configuration management
        this.iceConfig = {
            iceServers: [
                { urls: ['stun:stun.cloudflare.com:3478', 'stun:stun.l.google.com:19302'] }
            ]
        };
        this.iceReady = false;

        this.init();
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

    async init() {
        console.log('🎬 Initializing Room WebRTC for room:', this.roomData.name);
        
        // Load ICE configuration early (don't await to avoid blocking UI)
        this.loadIceServers().catch(error => {
            console.warn('🧊 Non-blocking ICE config load failed:', error);
        });
        
        // Initialize speech recognition
        this.initializeSpeechRecognition();
        
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
        
        const needsSttConsent = this.consentManager.stt.enabled;
        const needsRecordingConsent = this.consentManager.recording.enabled;

        if (!needsSttConsent && !needsRecordingConsent) {
            console.log('🔒 No consent requirements for this room');
            return;
        }

        // Disable UI until consent is resolved
        this.disableJoinUI();

        try {
            // Check consent statuses in parallel
            const consentChecks = [];
            
            if (needsSttConsent) {
                consentChecks.push(this.checkConsentStatus('stt'));
            }
            
            if (needsRecordingConsent) {
                consentChecks.push(this.checkConsentStatus('recording'));
            }

            await Promise.all(consentChecks);

            // Process consent results and show dialogs if needed
            await this.processInitialConsentResults();

        } catch (error) {
            console.error('❌ Error checking initial consent requirements:', error);
        }
    }

    /**
     * Processes initial consent results and shows dialogs if needed
     */
    async processInitialConsentResults() {
        const sttStatus = this.consentManager.stt.status;
        const recordingStatus = this.consentManager.recording.status;

        // Collect consent dialogs needed
        const dialogsNeeded = [];
        
        if (sttStatus?.requires_consent) {
            dialogsNeeded.push('stt');
        }
        
        if (recordingStatus?.requires_consent) {
            dialogsNeeded.push('recording');
        }

        // Show consent dialogs sequentially if needed
        if (dialogsNeeded.length > 0) {
            console.log('🔒 Showing initial consent dialogs for:', dialogsNeeded);
            await this.showConsentDialogs(dialogsNeeded);
        } else {
            // Check for any denials that require redirection (only for required consent)
            if (sttStatus?.consent_denied && sttStatus?.consent_required) {
                this.handleConsentDenied();
            } else if (recordingStatus?.consent_denied && recordingStatus?.consent_required) {
                this.handleConsentDenied();
            } else {
                // All consents resolved (either given or optionally denied)
                this.enableJoinUI();
            }
        }
    }

    // ===========================================
    // ABLY REALTIME MESSAGING SYSTEM
    // ===========================================

    /**
     * Connects to the room-specific Ably channel when client is ready
     */
    connectToAblyChannel() {
        const connectWhenReady = () => {
            if (window.AblyClient) {
                this.setupAblyChannel();
            } else {
                // Wait for Ably client to be initialized
                setTimeout(connectWhenReady, 100);
            }
        };
        
        connectWhenReady();
    }

    /**
     * Sets up Ably channel subscriptions and requests current room state
     */
    setupAblyChannel() {
        // Use room-specific channel
        const channelName = `room-${this.roomData.id}`;
        this.ablyChannel = window.AblyClient.channels.get(channelName);
        
        // Subscribe to signaling messages only (filter out other app messages)
        this.ablyChannel.subscribe((message) => {
            // Fix #1: Gate Ably to signaling messages only
            if (message.name && message.name !== 'webrtc-signal') return;

            const payload = message.data;
            if (!payload) return;

            // Ignore if message is targeted to a different peer
            if (payload.targetPeerId && payload.targetPeerId !== this.currentPeerId) return;

            // Filter out our own messages
            if (payload.senderId === this.currentPeerId) return;
            
            console.log('📨 Room message type:', payload.type, 'from:', payload.senderId);
            this.handleAblyMessage(message);
        });
        
        console.log('✅ Connected to room Ably channel:', channelName);
        
        // Request current state from other users in this room
        setTimeout(() => {
            console.log('📡 Requesting current room state...');
            if (!this.currentPeerId) {
                this.currentPeerId = this.generatePeerId();
                console.log(`🆔 Generated viewer peer ID: ${this.currentPeerId}`);
            }
            this.publishToAbly('request-state', {
                requesterId: this.currentPeerId,
                userId: this.currentUserId
            });
        }, 500);
    }

    /**
     * Publishes a message to the Ably channel with proper structure
     */
    publishToAbly(type, data, targetPeerId = null) {
        if (!this.ablyChannel) {
            console.warn('❌ Ably channel not ready');
            return;
        }

        const message = {
            type: type,
            data: data,
            senderId: this.currentPeerId || 'anonymous',
            userId: this.currentUserId,
            roomId: this.roomData.id,
            targetPeerId: targetPeerId,
            timestamp: Date.now()
        };

        this.ablyChannel.publish('webrtc-signal', message);
        console.log(`📤 Published ${type} to room channel`);
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

            this.isJoined = true;

            // Announce our presence to the room
            this.publishToAbly('user-joined', {
                slotId: slotId,
                participantData: participantData
            });

            // Hide loading state and show controls
            this.hideLoadingState(slotContainer);
            this.showVideoControls(slotContainer);

            // Handle consent requirements
            await this.handleConsentRequirements();

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
        this.slotOccupants.delete(this.currentSlotId);

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
            ...this.iceConfig,
            // IMPORTANT: Do NOT set iceTransportPolicy:'relay' to allow natural candidate preference
            // iceCandidatePoolSize: 1, // Optional: pre-gather candidates
        });
        
        this.peerConnections.set(peerId, peerConnection);
        
        // If ICE config isn't ready yet, update this connection when it arrives
        if (!this.iceReady) {
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
                        roomId: this.roomData.id,
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

    // ===========================================
    // UNIFIED CONSENT MANAGEMENT SYSTEM
    // ===========================================

    /**
     * Handles all consent requirements (STT and Recording) in a unified flow
     */
    async handleConsentRequirements() {
        const needsSttConsent = this.consentManager.stt.enabled;
        const needsRecordingConsent = this.consentManager.recording.enabled;

        if (!needsSttConsent && !needsRecordingConsent) {
            // No consent needed, enable UI immediately
            this.enableJoinUI();
            return;
        }

        // Disable UI until consent is resolved
        this.disableJoinUI();

        try {
            // Check consent statuses in parallel
            const consentChecks = [];
            
            if (needsSttConsent) {
                consentChecks.push(this.checkConsentStatus('stt'));
            }
            
            if (needsRecordingConsent) {
                consentChecks.push(this.checkConsentStatus('recording'));
            }

            await Promise.all(consentChecks);

            // Process consent results
            await this.processConsentResults();

        } catch (error) {
            console.error('❌ Error handling consent requirements:', error);
            this.enableJoinUI(); // Enable UI on error to prevent deadlock
        }
    }

    /**
     * Checks consent status for a specific feature type
     */
    async checkConsentStatus(type) {
        const endpoint = type === 'stt' ? 'stt-consent' : 'recording-consent';
        
        try {
            const response = await fetch(`/api/rooms/${this.roomData.id}/${endpoint}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });

            if (response.ok) {
                this.consentManager[type].status = await response.json();
                console.log(`🔒 ${type.toUpperCase()} consent status:`, this.consentManager[type].status);
            } else {
                console.warn(`🔒 Failed to check ${type} consent status:`, response.status);
            }
        } catch (error) {
            console.error(`🔒 Error checking ${type} consent:`, error);
        }
    }

    /**
     * Processes consent results and shows dialogs or starts features as needed
     */
    async processConsentResults() {
        const sttStatus = this.consentManager.stt.status;
        const recordingStatus = this.consentManager.recording.status;

        // Collect consent dialogs needed
        const dialogsNeeded = [];
        
        if (sttStatus?.requires_consent) {
            dialogsNeeded.push('stt');
        }
        
        if (recordingStatus?.requires_consent) {
            dialogsNeeded.push('recording');
        }

        // Show consent dialogs sequentially if needed
        if (dialogsNeeded.length > 0) {
            await this.showConsentDialogs(dialogsNeeded);
        } else {
            // No dialogs needed, check for auto-start or redirect
            this.handleAutoConsentActions();
        }
    }

    /**
     * Shows consent dialogs sequentially for multiple consent types
     */
    async showConsentDialogs(types) {
        for (const type of types) {
            await new Promise((resolve) => {
                this.showConsentDialog(type, resolve);
            });
        }
    }

    /**
     * Handles actions when no consent dialogs are needed
     */
    handleAutoConsentActions() {
        const sttStatus = this.consentManager.stt.status;
        const recordingStatus = this.consentManager.recording.status;

        // Check for any denials that require redirection
        if (sttStatus?.consent_denied || recordingStatus?.consent_denied) {
            this.handleConsentDenied();
            return;
        }

        // Start features that have consent
        if (sttStatus?.consent_given) {
            this.startSpeechRecognition();
        }
        
        if (recordingStatus?.consent_given) {
            this.startVideoRecording();
        }

        // Enable UI
        this.enableJoinUI();
    }

    /**
     * Shows a unified consent dialog for any feature type
     */
    showConsentDialog(type, onComplete) {
        const config = this.getConsentConfig(type);
        
        // Create modal backdrop
        const backdrop = document.createElement('div');
        backdrop.id = `${type}-consent-backdrop`;
        backdrop.className = 'fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 consent-dialog';
        
        backdrop.innerHTML = `
            <div class="bg-slate-900 border border-slate-700 rounded-xl p-6 max-w-md mx-4 shadow-2xl">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 ${config.iconBg} rounded-full flex items-center justify-center mx-auto mb-4">
                        ${config.icon}
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">${config.title}</h3>
                    <p class="text-slate-300 text-sm leading-relaxed">${config.description}</p>
                </div>
                
                <div class="flex space-x-3">
                    <button id="${type}-consent-deny" 
                            class="flex-1 bg-red-500/20 hover:bg-red-500/30 text-red-400 border border-red-500/30 font-semibold py-3 px-4 rounded-lg transition-all duration-300">
                        No, Leave Room
                    </button>
                    <button id="${type}-consent-accept" 
                            class="flex-1 bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-400 border border-emerald-500/30 font-semibold py-3 px-4 rounded-lg transition-all duration-300">
                        Yes, I Consent
                    </button>
                </div>
                
                <div class="mt-4 text-xs text-slate-400 text-center">
                    <p>Your decision will be saved for this room session</p>
                </div>
            </div>
        `;

        document.body.appendChild(backdrop);

        // Add event listeners
        const acceptButton = document.getElementById(`${type}-consent-accept`);
        const declineButton = document.getElementById(`${type}-consent-deny`);
        acceptButton.addEventListener('click', () => {
            this.handleConsentDecision(type, true, backdrop, onComplete);
        });

        declineButton.addEventListener('click', () => {
            this.handleConsentDecision(type, false, backdrop, onComplete);
        });

        // Prevent closing by clicking backdrop
        backdrop.addEventListener('click', (e) => {
            if (e.target === backdrop) {
                e.preventDefault();
            }
        });
    }

    /**
     * Gets configuration for consent dialog based on type
     */
    getConsentConfig(type) {
        const configs = {
            stt: {
                title: 'Speech Recording Consent',
                description: 'This room has speech-to-text recording enabled. Your voice will be transcribed and saved. Do you consent to having your speech recorded and transcribed?',
                iconBg: 'bg-amber-500/20',
                icon: `<svg class="w-8 h-8 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                </svg>`
            },
            recording: {
                title: 'Video Recording Consent',
                description: 'This room has video recording enabled. Your video will be recorded and saved to the room owner\'s chosen storage service. Do you consent to having your video recorded?',
                iconBg: 'bg-red-500/20',
                icon: `<svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>`
            }
        };
        
        return configs[type];
    }

    /**
     * Handles consent decision for any feature type
     */
    async handleConsentDecision(type, consentGiven, backdrop, onComplete) {
        const endpoint = type === 'stt' ? 'stt-consent' : 'recording-consent';
        
        try {
            const response = await fetch(`/api/rooms/${this.roomData.id}/${endpoint}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify({
                    consent_given: consentGiven
                })
            });

            if (response.ok) {
                const result = await response.json();
                console.log(`🔒 ${type.toUpperCase()} consent decision saved:`, result);

                // Remove consent dialog
                backdrop.remove();

                if (consentGiven) {
                    // Start the appropriate feature
                    if (type === 'stt') {
                        this.startSpeechRecognition();
                    } else if (type === 'recording') {
                        this.startVideoRecording();
                    }
                    
                    // Complete this consent flow
                    onComplete();
                } else {
                    // Handle consent denial based on requirement type
                    this.handleConsentDenial(type);
                }
                
                // Check if all consents are resolved
                this.checkAllConsentsResolved();
                
            } else {
                console.error(`🔒 Failed to save ${type} consent decision:`, response.status);
                alert('Failed to save consent decision. Please try again.');
            }
        } catch (error) {
            console.error(`🔒 Error saving ${type} consent decision:`, error);
            alert('Failed to save consent decision. Please try again.');
        }
    }

    /**
     * Checks if all required consents are resolved and enables UI
     */
    checkAllConsentsResolved() {
        const allResolved = Object.values(this.consentManager).every(consent => 
            !consent.enabled || (consent.status && (consent.status.consent_given || consent.status.consent_denied))
        );
        
        if (allResolved) {
            this.enableJoinUI();
        }
    }

    /**
     * Handles consent denial based on whether it's required or optional
     */
    handleConsentDenial(type) {
        const status = this.consentManager[type].status;
        const isRequired = status?.consent_required;
        
        if (isRequired) {
            // Required consent denied - redirect user
            this.handleConsentDenied();
        } else {
            // Optional consent denied - allow user to continue
            console.log(`🔒 ${type.toUpperCase()} consent declined (optional) - user can continue`);
            this.checkAllConsentsResolved();
        }
    }

    /**
     * Handles when user denies required consent - shows unified denial message
     */
    handleConsentDenied() {
        const backdrop = document.createElement('div');
        backdrop.className = 'fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50';
        backdrop.innerHTML = `
            <div class="bg-slate-900 border border-slate-700 rounded-xl p-6 max-w-md mx-4 shadow-2xl text-center">
                <div class="w-16 h-16 bg-red-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Consent Required</h3>
                <p class="text-slate-300 text-sm mb-4">
                    You have declined the required permissions for this room. You will be redirected.
                </p>
                <p class="text-xs text-slate-400">Redirecting in <span id="consent-countdown">3</span> seconds...</p>
            </div>
        `;
        document.body.appendChild(backdrop);

        // Countdown and redirect
        let countdown = 3;
        const countdownElement = document.getElementById('consent-countdown');
        const countdownInterval = setInterval(() => {
            countdown--;
            if (countdownElement) {
                countdownElement.textContent = countdown;
            }
            
            if (countdown <= 0) {
                clearInterval(countdownInterval);
                window.location.href = `/rooms/${this.roomData.invite_code || ''}`;
            }
        }, 1000);
    }

    // ===========================================
    // SPEECH-TO-TEXT SYSTEM
    // ===========================================

    /**
     * Initializes speech recognition with browser compatibility checks
     */
    initializeSpeechRecognition() {
        // Check if speech recognition is supported
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        
        if (!SpeechRecognition) {
            console.warn('🎤 Speech recognition not supported in this browser');
            return;
        }

        // Check if STT is enabled for this room
        if (!this.roomData.stt_enabled) {
            console.log('🎤 Speech-to-text disabled for this room');
            return;
        }

        this.speechRecognition = new SpeechRecognition();
        this.speechRecognition.lang = this.roomData.stt_lang || navigator.language || 'en-GB';
        this.speechRecognition.continuous = true;
        this.speechRecognition.interimResults = false;
        this.speechRecognition.maxAlternatives = 1;

        let lastErrorAt = 0;

        this.speechRecognition.onresult = (event) => {
            for (let i = event.resultIndex; i < event.results.length; i++) {
                if (event.results[i].isFinal) {
                    const transcript = event.results[i][0].transcript.trim();
                    const confidence = event.results[i][0].confidence;
                    
                    if (transcript) {
                        this.speechBuffer.push({
                            text: transcript,
                            confidence: confidence,
                            timestamp: Date.now()
                        });
                        
                        console.log('🎤 Speech recognized:', transcript);
                        this.displayTranscript(transcript);
                    }
                }
            }
        };

        this.speechRecognition.onerror = (event) => {
            console.error('🎤 STT error:', event.error);
            const now = Date.now();
            
            // Guard against restart loops with time-based throttling
            if (this.isSpeechEnabled && 
                !['not-allowed', 'service-not-allowed'].includes(event.error) && 
                (now - lastErrorAt) > 1000) {
                lastErrorAt = now;
                setTimeout(() => {
                    if (this.speechRecognition && this.isSpeechEnabled) {
                        try {
                            this.speechRecognition.start();
                        } catch (e) {
                            console.warn('🎤 Failed to restart STT:', e);
                        }
                    }
                }, 800);
            }
        };

        this.speechRecognition.onend = () => {
            console.log('🎤 Speech recognition ended');
            
            // Restart if we're still supposed to be listening
            if (this.isSpeechEnabled) {
                setTimeout(() => {
                    if (this.speechRecognition) {
                        try {
                            this.speechRecognition.start();
                        } catch (e) {
                            console.warn('🎤 Failed to restart STT after end:', e);
                        }
                    }
                }, 100);
            }
        };

        console.log(`🎤 Speech recognition initialized with locale: ${this.speechRecognition.lang}`);
    }

    startSpeechRecognition() {
        if (!this.speechRecognition || this.isSpeechEnabled) {
            return;
        }

        try {
            this.isSpeechEnabled = true;
            this.speechBuffer = [];
            this.speechChunkStartedAt = Date.now();
            
            this.speechRecognition.start();
            console.log('🎤 Speech recognition started');

            // Set up 30-second upload interval
            this.speechUploadInterval = setInterval(() => {
                this.uploadTranscriptChunk();
            }, 30000);

        } catch (error) {
            console.error('🎤 Failed to start speech recognition:', error);
            this.isSpeechEnabled = false;
        }
    }

    stopSpeechRecognition() {
        if (!this.speechRecognition || !this.isSpeechEnabled) {
            return;
        }

        this.isSpeechEnabled = false;
        
        try {
            this.speechRecognition.stop();
        } catch (error) {
            console.warn('🎤 Error stopping speech recognition:', error);
        }

        // Clear upload interval
        if (this.speechUploadInterval) {
            clearInterval(this.speechUploadInterval);
            this.speechUploadInterval = null;
        }

        // Upload any remaining buffer
        this.uploadTranscriptChunk();

        console.log('🎤 Speech recognition stopped');
    }

    async uploadTranscriptChunk() {
        if (!this.speechBuffer.length || !this.currentUserId) {
            return;
        }

        const chunkEndedAt = Date.now();
        const combinedText = this.speechBuffer.map(item => item.text).join(' ');
        const averageConfidence = this.speechBuffer.reduce((sum, item) => sum + (item.confidence || 0), 0) / this.speechBuffer.length;

        const payload = {
            room_id: this.roomData.id,
            user_id: this.currentUserId,
            started_at_ms: this.speechChunkStartedAt,
            ended_at_ms: chunkEndedAt,
            text: combinedText,
            language: this.speechRecognition?.lang || this.roomData.stt_lang || 'en-GB',
            confidence: averageConfidence || null
        };

        try {
            const response = await fetch(`/api/rooms/${this.roomData.id}/transcripts`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify(payload)
            });

            if (response.ok) {
                console.log('📤 Transcript chunk uploaded successfully');
            } else {
                console.error('❌ Failed to upload transcript chunk:', response.status);
            }
        } catch (error) {
            console.error('❌ Error uploading transcript chunk:', error);
        }

        // Reset buffer for next chunk
        this.speechBuffer = [];
        this.speechChunkStartedAt = Date.now();
    }

    displayTranscript(text) {
        // Find or create transcript display area
        let transcriptDisplay = document.querySelector('.transcript-display');
        
        if (!transcriptDisplay) {
            transcriptDisplay = document.createElement('div');
            transcriptDisplay.className = 'transcript-display fixed bottom-4 left-4 bg-black bg-opacity-75 text-white p-3 rounded-lg max-w-md z-50';
            transcriptDisplay.innerHTML = `
                <div class="text-xs text-gray-300 mb-1">Live Transcript</div>
                <div class="transcript-content text-sm"></div>
            `;
            document.body.appendChild(transcriptDisplay);
        }

        const content = transcriptDisplay.querySelector('.transcript-content');
        const timestamp = new Date().toLocaleTimeString();
        
        // Add new transcript line
        const transcriptLine = document.createElement('div');
        transcriptLine.className = 'mb-1 opacity-75';
        transcriptLine.innerHTML = `<span class="text-xs text-gray-400">[${timestamp}]</span> ${text}`;
        content.appendChild(transcriptLine);

        // Keep only last 5 lines
        const lines = content.querySelectorAll('div');
        if (lines.length > 5) {
            lines[0].remove();
        }

        // Auto-hide after no speech for 10 seconds
        clearTimeout(this.transcriptHideTimeout);
        transcriptDisplay.style.display = 'block';
        
        this.transcriptHideTimeout = setTimeout(() => {
            transcriptDisplay.style.display = 'none';
        }, 10000);
    }

    // ===========================================
    // VIDEO RECORDING SYSTEM
    // ===========================================

    /**
     * Initializes video recording capabilities
     */
    initializeVideoRecording() {
        if (!this.roomData.recording_enabled) {
            console.log('🎥 Video recording disabled for this room');
            return;
        }

        // Fix #4: Choose MIME type once and use everywhere
        const pickType = (...types) => types.find(t => MediaRecorder.isTypeSupported(t));
        this.recMime = pickType(
            'video/webm;codecs=vp9,opus',
            'video/webm;codecs=vp8,opus',
            'video/webm',
            'video/mp4;codecs=h264,aac',
            'video/mp4'
        );

        if (!this.recMime) {
            console.warn('🎥 MediaRecorder supported types not found');
            return;
        }

        console.log('🎥 Video recording initialized with', this.recMime);
    }

    async startVideoRecording() {
        if (!this.localStream) {
            console.warn('🎥 No local stream available for recording');
            return;
        }

        // Fix #4: Use the chosen MIME type consistently
        if (!this.recMime) {
            console.warn('🎥 No recording MIME type available');
            return;
        }

        try {
            this.mediaRecorder = new MediaRecorder(this.localStream, { mimeType: this.recMime });
            this.recordingStartTime = Date.now();
            this.isRecording = true;
            this.recordedChunks = [];

            // Handle data available with each timeslice (every 30s)
            this.mediaRecorder.ondataavailable = async (event) => {
                if (!event.data || !event.data.size) return;
                
                const endTime = Date.now();
                const blob = event.data;
                
                // Fix #4: Use correct file extension based on MIME type
                const ext = (blob.type && blob.type.includes('mp4')) ? 'mp4' : 'webm';
                const recordingData = {
                    user_id: this.currentUserId,
                    started_at_ms: this.recordingStartTime,
                    ended_at_ms: endTime,
                    size_bytes: blob.size,
                    mime_type: blob.type || this.recMime,
                    filename: `recording_${this.currentUserId}_${this.recordingStartTime}.${ext}`
                };

                try {
                    // Wait until upload queue clears to avoid memory buildup
                    while (this.tooManyQueuedUploads()) {
                        console.warn('📦 Upload backlog; waiting...');
                        await new Promise(resolve => setTimeout(resolve, 1500));
                    }
                    
                    await this.uploadVideoChunk(blob, recordingData);
                    console.log('🎥 Video chunk uploaded successfully');
                } catch (error) {
                    console.error('🎥 Upload error:', error);
                }
                
                // Reset start time for next segment
                this.recordingStartTime = Date.now();
            };

            // Start recording with 30-second timeslices
            this.mediaRecorder.start(30000); // 30 seconds
            this.updateRecordingUI(true);
            console.log('🎥 Video recording started with timeslices');

        } catch (error) {
            console.error('🎥 Error starting MediaRecorder:', error);
            this.isRecording = false;
        }
    }

    stopVideoRecording() {
        if (this.mediaRecorder && this.isRecording) {
            this.isRecording = false;
            try {
                this.mediaRecorder.stop();
            } catch (error) {
                console.warn('🎥 Error stopping MediaRecorder:', error);
            }
            this.updateRecordingUI(false);
            console.log('🎥 Video recording stopped');
        }
    }

    // Helper method to check for upload backpressure
    tooManyQueuedUploads() {
        if (!window.roomUppy) return false;
        
        const state = window.roomUppy.getState();
        const files = Object.values(state.files || {});
        const inflight = files.filter(file => 
            file.progress?.uploadStarted && !file.progress?.uploadComplete
        ).length;
        
        return inflight >= 4; // Allow 4 concurrent segments
    }



    async uploadVideoChunk(blob, recordingData) {
        try {
            // Use Uppy for advanced upload handling
            if (window.roomUppy) {
                await window.roomUppy.uploadVideoBlob(blob, recordingData);
                console.log('🎬 Video chunk queued for upload via Uppy');
            } else {
                // Fallback to direct upload if Uppy not available
                await this.directUploadVideoChunk(blob, recordingData);
                console.log('🎬 Video chunk uploaded via direct method');
            }
        } catch (error) {
            console.error('🎬 Error uploading video chunk:', error);
            throw error;
        }
    }

    async directUploadVideoChunk(blob, recordingData) {
        // Fallback direct upload method
        const formData = new FormData();
        formData.append('video', blob, recordingData.filename);
        formData.append('metadata', JSON.stringify(recordingData));

        const response = await fetch(`/api/rooms/${this.roomData.id}/recordings`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            },
            body: formData
        });

        if (!response.ok) {
            throw new Error(`Upload failed: ${response.status}`);
        }

        return await response.json();
    }

    updateRecordingUI(isRecording) {
        // Update UI to show recording status
        const recordingIndicators = document.querySelectorAll('.recording-indicator');
        recordingIndicators.forEach(indicator => {
            if (isRecording) {
                indicator.classList.add('recording');
                indicator.textContent = '🔴 Recording';
            } else {
                indicator.classList.remove('recording');
                indicator.textContent = '';
            }
        });

        // Add recording indicator to current user's slot
        if (this.currentSlotId) {
            const slotContainer = document.querySelector(`[data-slot-id="${this.currentSlotId}"]`);
            if (slotContainer) {
                let indicator = slotContainer.querySelector('.recording-indicator');
                if (!indicator) {
                    indicator = document.createElement('div');
                    indicator.className = 'recording-indicator absolute top-2 right-2 text-xs px-2 py-1 bg-red-500 text-white rounded';
                    slotContainer.appendChild(indicator);
                }
                
                if (isRecording) {
                    indicator.classList.add('recording');
                    indicator.textContent = '🔴 REC';
                    indicator.style.display = 'block';
                } else {
                    indicator.style.display = 'none';
                }
            }
        }
    }
}
