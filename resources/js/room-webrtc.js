/**
 * RoomWebRTC - Modular WebRTC room management system
 * 
 * Orchestrates peer-to-peer video conferencing, recording, and speech-to-text
 * for DaggerHeart room sessions using a modular architecture.
 * 
 * Features:
 * - WebRTC peer-to-peer video/audio connections via Ably signaling
 * - Video recording with chunked uploads (30s segments)
 * - Speech-to-text with live transcription
 * - Unified consent management for recording and STT
 * - Backpressure handling for upload queues
 * - Audio-only fallback support
 */

// Import all modules
import { DiagnosticsRunner } from './room/utils/DiagnosticsRunner.js';
import { PageProtection } from './room/utils/PageProtection.js';
import { ICEConfigManager } from './room/webrtc/ICEConfigManager.js';
import { SimplePeerManager } from './room/webrtc/SimplePeerManager.js';
import { MediaManager } from './room/webrtc/MediaManager.js';
import { SignalingManager as AblyManager } from './room/messaging/SignalingManager.js';
import { MessageHandler } from './room/messaging/MessageHandler.js';
import { VideoRecorder } from './room/recording/VideoRecorder.js';
import { StreamingDownloader } from './room/recording/StreamingDownloader.js';
import { CloudUploader } from './room/recording/CloudUploader.js';
import BrowserSpeechRecognition from './room/speech/browser-speech.js';
import AssemblyAISpeechRecognition from './room/speech/assembly-ai.js';
import { StatusBarManager } from './room/ui/StatusBarManager.js';
import { SlotManager } from './room/ui/SlotManager.js';
import { UIStateManager } from './room/ui/UIStateManager.js';
import { FearCountdownManager } from './room/ui/FearCountdownManager.js';
import { MarkerManager } from './room/ui/MarkerManager.js';
import { VideoSlotControls } from './room/ui/VideoSlotControls.js';
import { ConsentManager } from './room/consent/ConsentManager.js';
import { ConsentDialog } from './room/consent/ConsentDialog.js';

export default class RoomWebRTC {
    constructor(roomData) {
        this.roomData = roomData;
        this.currentUserId = window.currentUserId; // Should be set by Blade template
        
        // Core state
        this.slotOccupants = new Map(); // Map of slotId -> {peerId, stream, participantData}
        this.currentSlotId = null;
        this.isJoined = false;
        
        // Speech recognition state
        this.currentSpeechModule = null;
        this.isSpeechEnabled = false;
        this.sttPausedForMute = false; // Track if STT was paused due to microphone mute
        this.sttTransitioning = false; // Prevent concurrent STT state transitions
        
        // Connection health state (for refresh rate limiting)
        this.refreshAttempts = new Map(); // peerId -> {count, lastAttempt}
        this.maxRefreshAttempts = 5;
        this.refreshBackoffBase = 1000; // 1 second base
        
        // Initialize core managers
        this.iceManager = new ICEConfigManager();
        this.simplePeerManager = new SimplePeerManager(this);
        this.mediaManager = new MediaManager(this);
        this.ablyManager = new AblyManager(this);
        this.messageHandler = new MessageHandler(this);
        
        // Initialize recording managers
        this.videoRecorder = new VideoRecorder(this);
        this.streamingDownloader = new StreamingDownloader(this);
        this.cloudUploader = new CloudUploader(this);
        
        // Initialize UI managers
        this.statusBarManager = new StatusBarManager(this);
        this.slotManager = new SlotManager(this);
        this.uiStateManager = new UIStateManager(this);
        this.fearCountdownManager = new FearCountdownManager(this);
        this.markerManager = new MarkerManager(this);
        this.videoSlotControls = new VideoSlotControls(this);
        
        // Initialize consent managers
        this.consentManager = new ConsentManager(this);
        this.consentDialog = new ConsentDialog(this);
        
        // Initialize utility managers
        this.diagnosticsRunner = new DiagnosticsRunner(this);
        this.pageProtection = new PageProtection(this);
        
        this.init();
    }

    async init() {
        console.log('🎬 Initializing Room WebRTC for room:', this.roomData.name);
        
        // Mark as initialized for reconnection logic
        this.isInitialized = false;
        
        // STEP 1: Initialize SimplePeerManager (generates unique peer ID)
        await this.simplePeerManager.initialize();
        
        // STEP 2: CRITICAL - Immediately sync peer ID to SignalingManager
        // This MUST happen BEFORE connecting to channel
        const peerId = this.simplePeerManager.getPeerId();
        this.ablyManager.setCurrentPeerId(peerId);
        console.log(`🆔 Peer ID synchronized: ${peerId}`);
        
        // STEP 3: Load ICE configuration (non-blocking)
        this.iceManager.loadIceServers().catch(error => {
            console.warn('🧊 Non-blocking ICE config load failed:', error);
        });
        
        // STEP 4: Initialize speech recognition
        await this.initializeSpeechRecognition();
        
        // STEP 5: Set up slot event listeners
        this.slotManager.setupSlotEventListeners();

        // STEP 6: Set up status bar controls (including always-visible leave button)
        this.statusBarManager.setupStatusBarControls();

        // STEP 7: Connect to room-specific Reverb channel
        // By this point, peer ID is already set in SignalingManager
        this.ablyManager.connectToChannel();
        
        // STEP 8: Mark initialization as complete
        this.isInitialized = true;
        
        // STEP 9: Start connection health monitoring
        this.startConnectionHealthMonitoring();
        
        console.log('✅ Room WebRTC initialization complete');
    }

    /**
     * Checks consent requirements immediately upon entering the room
     */
    async checkInitialConsentRequirements() {
        await this.consentManager.checkInitialConsentRequirements();
    }

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
            this.uiStateManager.disableJoinUI('Checking permissions...');

            // Show loading state
            this.slotManager.showLoadingState(slotContainer);

            // Generate peer ID if we don't have one
            if (!this.ablyManager.getCurrentPeerId()) {
                const peerId = this.ablyManager.generatePeerId();
                this.ablyManager.setCurrentPeerId(peerId);
                console.log(`🆔 Generated peer ID: ${peerId}`);
            }

            // Get user media
            await this.mediaManager.getUserMedia();

            // Find participant data for this user
            const participantData = this.roomData.participants.find(p => p.user_id === this.currentUserId);

            // Set up local video
            const localStream = this.mediaManager.getLocalStream();
            
            // CRITICAL FIX: Validate localStream before proceeding
            if (!localStream) {
                throw new Error('Failed to obtain media stream - getUserMedia returned null');
            }
            
            const audioTracks = localStream.getAudioTracks();
            const videoTracks = localStream.getVideoTracks();
            
            if (audioTracks.length === 0 && videoTracks.length === 0) {
                throw new Error('Media stream has no audio or video tracks');
            }
            
            console.log(`✅ Media stream validated: ${audioTracks.length} audio, ${videoTracks.length} video tracks`);
            
            this.mediaManager.setupLocalVideo(slotContainer, localStream, participantData);

            // Set local stream on SimplePeerManager for PeerJS
            this.simplePeerManager.setLocalStream(localStream);

            // Mark this slot as occupied by us
            this.slotOccupants.set(slotId, {
                peerId: this.ablyManager.getCurrentPeerId(),
                stream: localStream,
                participantData: participantData,
                isLocal: true
            });

            this.isJoined = true;

            // Start features that have consent now that we have media access
            this.startConsentedFeatures();

            // Announce our presence to the room
            this.ablyManager.publishToAbly('user-joined', {
                slotId: slotId,
                participantData: participantData
            });

            // CRITICAL FIX: Use lexicographic ordering to prevent connection storms
            // When multiple users join simultaneously, only the one with "greater" peer ID initiates
            const currentPeerId = this.ablyManager.getCurrentPeerId();
            for (const [existingSlotId, occupant] of this.slotOccupants) {
                if (existingSlotId !== slotId && !occupant.isLocal && occupant.peerId) {
                    // Only initiate connection if our peer ID is lexicographically greater
                    // This prevents both peers from initiating simultaneously
                    if (currentPeerId > occupant.peerId) {
                        console.log(`🤝 Initiating connection (ID ordering): ${currentPeerId} -> ${occupant.peerId}`);
                        this.simplePeerManager.callPeer(occupant.peerId);
                    } else {
                        console.log(`🤝 Waiting for peer to initiate: ${occupant.peerId} should call ${currentPeerId}`);
                    }
                }
            }

            // Hide loading state and show controls
            this.slotManager.hideLoadingState(slotContainer);
            this.slotManager.showVideoControls(slotContainer);

            // Create automatic join marker
            if (participantData) {
                const participantName = participantData.character_name || participantData.username || 'Unknown Player';
                try {
                    await this.markerManager.createAutomaticJoinMarker(participantName);
                } catch (markerError) {
                    console.warn('🏷️ Failed to create join marker (non-critical):', markerError);
                    // Continue - marker creation failure shouldn't prevent joining
                }
            }

            // Set up debug commands for connection troubleshooting
            this.setupDebugCommands();

        } catch (error) {
            console.error('❌ Error joining slot:', error);
            this.slotManager.hideLoadingState(slotContainer);
            this.uiStateManager.enableJoinUI();
            this.uiStateManager.showError('Failed to access camera/microphone. Please check permissions.');
        }
    }

    /**
     * Leaves the current slot, stopping media and cleaning up connections
     * Now properly async to handle marker creation and recording cleanup
     */
    async leaveSlot() {
        if (!this.isJoined || !this.currentSlotId) {
            console.log('❌ Not currently in a slot');
            return;
        }

        console.log('🚪 Leaving slot:', this.currentSlotId);

        // Create automatic leave marker before stopping anything
        const occupant = this.slotOccupants.get(this.currentSlotId);
        if (occupant && occupant.participantData) {
            const participantName = occupant.participantData.character_name || occupant.participantData.username || 'Unknown Player';
            try {
                await this.markerManager.createAutomaticLeaveMarker(participantName);
            } catch (error) {
                console.warn('🏷️ Could not create leave marker:', error);
                // Continue with leave process even if marker fails
            }
        }

        // Announce we're leaving
        this.ablyManager.publishToAbly('user-left', {
            slotId: this.currentSlotId
        });

        // Stop local stream
        this.mediaManager.stopLocalStream();

        // Close all peer connections (PeerJS)
        // Note: We don't fully destroy SimplePeerManager, just close active calls
        this.simplePeerManager.destroy();

        // Clear slot occupancy
        this.slotOccupants.delete(this.currentSlotId);

        // Reset state
        const slotContainer = document.querySelector(`[data-slot-id="${this.currentSlotId}"]`);
        this.slotManager.resetSlotUI(slotContainer);

        this.currentSlotId = null;
        this.isJoined = false;

        // Reset media control state
        this.mediaManager.resetMediaState();
        this.sttPausedForMute = false;

        // Stop speech recognition
        this.stopSpeechRecognition();
        
        // Stop video recording (only if not already stopped)
        if (this.videoRecorder.isCurrentlyRecording()) {
            console.log('🎥 Stopping video recording from leaveSlot...');
            await this.videoRecorder.stopRecording();
        }

        console.log('✅ Successfully left slot');
    }

    /**
     * Callback for SimplePeerManager when remote stream is received
     * @param {MediaStream} remoteStream - The remote peer's media stream
     * @param {string} peerId - The peer ID of the remote participant
     */
    handleRemoteStream(remoteStream, peerId) {
        console.log(`📡 Handling remote stream from peer: ${peerId}`);
        
        // Find the slot occupied by this peer
        let targetSlotId = null;
        for (const [slotId, occupant] of this.slotOccupants) {
            if (occupant.peerId === peerId && !occupant.isLocal) {
                targetSlotId = slotId;
                break;
            }
        }

        if (!targetSlotId) {
            console.warn(`⚠️ No slot found for peer ${peerId}`);
            return;
        }

        // Update the occupant with the stream
        const occupant = this.slotOccupants.get(targetSlotId);
        if (occupant) {
            occupant.stream = remoteStream;
            this.slotOccupants.set(targetSlotId, occupant);
        }

        // Display the remote video in the slot
        const slotContainer = document.querySelector(`[data-slot-id="${targetSlotId}"]`);
        if (slotContainer) {
            this.mediaManager.setupRemoteVideo(slotContainer, remoteStream, occupant.participantData);
        }
    }

    /**
     * Callback for SimplePeerManager when peer disconnects
     * @param {string} peerId - The peer ID that disconnected
     */
    handlePeerDisconnected(peerId) {
        console.log(`📴 Handling peer disconnection: ${peerId}`);
        
        // Find and clean up the slot
        for (const [slotId, occupant] of this.slotOccupants) {
            if (occupant.peerId === peerId && !occupant.isLocal) {
                console.log(`🧹 Cleaning up slot ${slotId} for disconnected peer ${peerId}`);
                
                // Get slot container before deleting from map
                const slotContainer = document.querySelector(`[data-slot-id="${slotId}"]`);
                
                // CRITICAL FIX: Clean up remote video streams properly
                if (slotContainer) {
                    this.mediaManager.cleanupRemoteVideo(slotContainer);
                    this.slotManager.resetSlotUI(slotContainer);
                }
                
                // Remove from occupants map
                this.slotOccupants.delete(slotId);
                
                break;
            }
        }
    }

    /**
     * Leaves the room entirely (redirects to room details page)
     * Now async to properly wait for cleanup operations with timeout
     */
    async leaveRoom() {
        console.log('🚪 Leaving room entirely...');
        
        // Show loading modal immediately
        if (window.showLeavingModal) {
            const hasRecording = this.videoRecorder.isCurrentlyRecording();
            const initialMessage = hasRecording ? 'Finalizing recording...' : 'Cleaning up session...';
            window.showLeavingModal(initialMessage);
        }
        
        // Create a timeout promise (max 5 seconds for cleanup)
        const timeout = new Promise((resolve) => {
            setTimeout(() => {
                console.warn('🚪 ⏰ Cleanup timeout - proceeding with redirect');
                if (window.updateLeavingModalStatus) {
                    window.updateLeavingModalStatus('Finishing up...', 'Almost done');
                }
                resolve();
            }, 5000);
        });
        
        // Create cleanup promise
        const cleanup = async () => {
            try {
                // Stop any ongoing recording first
                if (this.videoRecorder.isCurrentlyRecording()) {
                    console.log('🎥 Stopping recording before leaving room...');
                    if (window.updateLeavingModalStatus) {
                        window.updateLeavingModalStatus('Finalizing recording...', 'Uploading final segments');
                    }
                    await this.videoRecorder.stopRecording();
                }
                
                // Stop speech recognition
                this.stopSpeechRecognition();
                
                // Leave current slot if joined
                if (this.isJoined) {
                    if (window.updateLeavingModalStatus) {
                        window.updateLeavingModalStatus('Creating leave marker...', 'Updating session timeline');
                    }
                    await this.leaveSlot();
                }
                
                // Give a brief moment for any final operations to complete
                console.log('🚪 Waiting for final cleanup operations...');
                if (window.updateLeavingModalStatus) {
                    window.updateLeavingModalStatus('Completing cleanup...', 'Just a moment');
                }
                await new Promise(resolve => setTimeout(resolve, 500));
                
            } catch (error) {
                console.error('🚪 Error during room leave cleanup:', error);
                if (window.updateLeavingModalStatus) {
                    window.updateLeavingModalStatus('Finishing up...', 'Encountered issue, completing anyway');
                }
                // Continue with redirect even if cleanup fails
            }
        };
        
        // Race between cleanup and timeout
        await Promise.race([cleanup(), timeout]);
        
        console.log('🚪 Cleanup complete, redirecting...');
        if (window.updateLeavingModalStatus) {
            window.updateLeavingModalStatus('Complete!', 'Redirecting...');
        }
        
        // Redirect to room details page using invite_code
        const inviteCode = this.roomData.invite_code;
        if (inviteCode) {
            window.location.href = `/rooms/${inviteCode}`;
        } else {
            console.error('No invite code available for redirect');
            // Fallback to a safe page
            window.location.href = '/dashboard';
        }
    }

    /**
     * Starts features that have consent after user joins a slot and has media access
     */
    startConsentedFeatures() {
        console.log('🎤 === Starting Consented Features ===');
        
        const sttStatus = this.consentManager.getConsentStatus('stt');
        const recordingStatus = this.consentManager.getConsentStatus('recording');
        
        console.log('🎤 Consent Status:');
        console.log(`  - STT enabled in room: ${this.consentManager.isFeatureEnabled('stt')}`);
        console.log(`  - STT consent given: ${sttStatus?.consent_given || false}`);
        console.log(`  - Recording enabled: ${this.consentManager.isFeatureEnabled('recording')}`);
        console.log(`  - Recording consent given: ${recordingStatus?.consent_given || false}`);
        
        const localStream = this.mediaManager.getLocalStream();
        console.log('🎤 Media Status:');
        console.log(`  - Has local stream: ${!!localStream}`);
        console.log(`  - Audio tracks: ${localStream?.getAudioTracks()?.length || 0}`);
        console.log(`  - Video tracks: ${localStream?.getVideoTracks()?.length || 0}`);

        // Start STT if consent was given and we have audio
        if (sttStatus?.consent_given && localStream && localStream.getAudioTracks().length > 0) {
            console.log('🎤 ✅ All conditions met for STT - attempting to start...');
            setTimeout(() => {
                console.log('🎤 Delayed STT start (1s delay for media stability)...');
                this.startSpeechRecognition();
            }, 1000);
        } else {
            console.log('🎤 ❌ STT cannot start:');
            console.log(`  - STT consent given: ${sttStatus?.consent_given || false}`);
            console.log(`  - Has local stream: ${!!localStream}`);
            console.log(`  - Audio tracks available: ${localStream?.getAudioTracks()?.length || 0}`);
        }

        // Start video recording if consent was given and we have video
        if (recordingStatus?.consent_given && localStream) {
            console.log('🎥 Starting video recording - consent granted and stream available');
            this.videoRecorder.startRecording();
        }
    }

    // ===========================================
    // SPEECH-TO-TEXT SYSTEM
    // ===========================================

    /**
     * Initializes speech recognition with provider-specific setup
     */
    async initializeSpeechRecognition() {
        console.log('🎤 === Speech Recognition Initialization Starting ===');
        
        // Check if STT is enabled for this room
        if (!this.roomData.stt_enabled) {
            console.log('🎤 Speech-to-text disabled for this room');
            return;
        }

        console.log('🎤 ✅ STT enabled for this room');
        console.log(`🎤 STT Provider: ${this.roomData.stt_provider || 'browser'}`);

        // Initialize based on provider
        const provider = this.roomData.stt_provider || 'browser';
        
        if (provider === 'assemblyai') {
            await this.initializeAssemblyAISpeechRecognition();
        } else {
            await this.initializeBrowserSpeechRecognition();
        }
    }

    /**
     * Initializes browser-based speech recognition (Web Speech API)
     */
    async initializeBrowserSpeechRecognition() {
        console.log('🎤 === Browser Speech Recognition Initialization ===');
        
        try {
            // Create browser speech recognition instance
            const browserSpeech = new BrowserSpeechRecognition(this.roomData, this.currentUserId);
            
            // Set up event callbacks
            browserSpeech.setCallbacks({
                onTranscript: (text, confidence) => {
                    console.log('🎤 Browser speech transcript:', text);
                    this.displayTranscript(text);
                },
                onError: (error) => {
                    console.error('🎤 ❌ Browser speech error:', error);
                },
                onStatusChange: (status) => {
                    console.log(`🎤 Browser speech status: ${status}`);
                }
            });
            
            // Initialize the module
            const success = await browserSpeech.initialize();
            
            if (success) {
                this.currentSpeechModule = browserSpeech;
                console.log('🎤 ✅ Browser speech recognition initialized successfully');
            } else {
                console.error('🎤 ❌ Failed to initialize browser speech recognition');
            }
            
        } catch (error) {
            console.error('🎤 ❌ Error initializing browser speech recognition:', error);
        }
    }

    /**
     * Initializes AssemblyAI-based speech recognition
     */
    async initializeAssemblyAISpeechRecognition() {
        console.log('🎤 === AssemblyAI Speech Recognition Initialization ===');
        
        try {
            // Create AssemblyAI speech recognition instance
            const assemblyAISpeech = new AssemblyAISpeechRecognition(this.roomData, this.currentUserId);
            
            // Set up callbacks
            assemblyAISpeech.setCallbacks({
                onTranscript: (transcript, confidence) => {
                    console.log('🎤 AssemblyAI transcript:', transcript);
                    this.displayTranscript(transcript);
                },
                onError: (error) => {
                    console.error('🎤 ❌ AssemblyAI error from module:', error);
                    // Fallback to browser speech recognition
                    this.initializeBrowserSpeechRecognition();
                },
                onStatusChange: (status, data) => {
                    console.log(`🎤 AssemblyAI status: ${status}`, data);
                }
            });
            
            // Initialize the AssemblyAI module
            const initialized = await assemblyAISpeech.initialize();
            
            if (initialized) {
                this.currentSpeechModule = assemblyAISpeech;
                console.log('🎤 ✅ AssemblyAI speech recognition ready');
            } else {
                throw new Error('Failed to initialize AssemblyAI module');
            }

        } catch (error) {
            console.error('🎤 ❌ Failed to initialize AssemblyAI:', error);
            console.error('🎤 Falling back to browser speech recognition');
            
            // Initialize browser speech recognition as fallback
            await this.initializeBrowserSpeechRecognition();
        }
    }

    startSpeechRecognition() {
        console.log('🎤 === Starting Speech Recognition ===');
        
        if (!this.currentSpeechModule) {
            console.error('🎤 ❌ Cannot start - no speech recognition module');
            return;
        }
        
        if (this.isSpeechEnabled) {
            console.warn('🎤 ⚠️ Speech recognition already enabled, skipping start');
            return;
        }

        const localStream = this.mediaManager.getLocalStream();
        if (!localStream) {
            console.error('🎤 ❌ No local stream available for speech recognition');
            return;
        }

        this.isSpeechEnabled = true;
        this.currentSpeechModule.start(localStream).catch(error => {
            console.error('🎤 ❌ Failed to start speech recognition:', error);
            this.isSpeechEnabled = false;
        });
    }

    stopSpeechRecognition() {
        if (!this.currentSpeechModule || !this.isSpeechEnabled) {
            return;
        }

        this.isSpeechEnabled = false;
        this.currentSpeechModule.stop().catch(error => {
            console.warn('🎤 Error stopping speech recognition:', error);
        });

        console.log('🎤 Speech recognition stopped');
    }

    displayTranscript(text) {
        // Hide live transcript popup when STT is enabled
        if (this.roomData.stt_enabled) {
            console.log('🎤 Live transcript popup hidden - STT is enabled');
            return;
        }
        
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
    // MEDIA CONTROL METHODS
    // ===========================================

    /**
     * Toggles microphone mute/unmute state
     */
    toggleMicrophone() {
        const wasMuted = this.mediaManager.toggleMicrophone();
        
        // Handle STT integration based on microphone state
        this.handleSTTMicrophoneIntegration();
        
        return wasMuted;
    }

    /**
     * Toggles video show/hide state
     */
    toggleVideo() {
        return this.mediaManager.toggleVideo();
    }

    /**
     * Handles Speech-to-Text integration when microphone state changes
     */
    handleSTTMicrophoneIntegration() {
        console.log('🎤 === STT Microphone Integration ===');
        const isMuted = this.mediaManager.getMicrophoneMutedState();
        console.log(`  - Microphone muted: ${isMuted}`);
        console.log(`  - STT enabled: ${this.isSpeechEnabled}`);
        console.log(`  - Has speech module: ${!!this.currentSpeechModule}`);
        console.log(`  - Room STT enabled: ${this.roomData.stt_enabled}`);

        // Only handle STT if it's enabled for the room and we have speech recognition
        if (!this.roomData.stt_enabled || !this.currentSpeechModule) {
            console.log('🎤 STT not available - skipping integration');
            return;
        }

        if (isMuted) {
            // Microphone muted - pause STT if it's currently running
            if (this.isSpeechEnabled) {
                console.log('🎤 🔇 Microphone muted - pausing STT');
                this.pauseSTTForMute();
            } else {
                console.log('🎤 STT already stopped - no action needed');
            }
        } else {
            // Microphone unmuted - resume STT if we're in a slot and have consent
            if (this.isJoined && this.mediaManager.getLocalStream()) {
                console.log('🎤 🔊 Microphone unmuted - checking if STT should resume');
                this.resumeSTTFromMute();
            } else {
                console.log('🎤 Not joined or no stream - STT will start when user joins slot');
            }
        }
    }

    /**
     * Pauses STT when microphone is muted
     * CRITICAL FIX: Made async to prevent race conditions
     */
    async pauseSTTForMute() {
        // Prevent concurrent state transitions
        if (this.sttTransitioning) {
            console.log('🎤 STT transition already in progress, skipping pause');
            return;
        }
        
        this.sttTransitioning = true;
        console.log('🎤 === Pausing STT for Microphone Mute ===');
        
        try {
            // Store that STT was paused due to mute (not user action)
            this.sttPausedForMute = true;
            
            // Stop STT but don't clear the speech recognition instance
            if (this.currentSpeechModule && this.currentSpeechModule.isRunning()) {
                console.log('🎤 Pausing STT for mute');
                await this.currentSpeechModule.stop();
            }

            this.isSpeechEnabled = false;
            console.log('🎤 ✅ STT paused for microphone mute');
        } catch (error) {
            console.warn('🎤 Error stopping STT for mute:', error);
        } finally {
            this.sttTransitioning = false;
        }
    }

    /**
     * Resumes STT when microphone is unmuted
     * CRITICAL FIX: Made async to prevent race conditions
     */
    async resumeSTTFromMute() {
        // Prevent concurrent state transitions
        if (this.sttTransitioning) {
            console.log('🎤 STT transition already in progress, skipping resume');
            return;
        }
        
        this.sttTransitioning = true;
        console.log('🎤 === Resuming STT from Microphone Unmute ===');
        
        try {
            // Only resume if STT was paused due to mute (not user action)
            if (!this.sttPausedForMute) {
                console.log('🎤 STT was not paused for mute - not resuming automatically');
                return;
            }

            // Check if we have consent for STT
            const sttStatus = this.consentManager.getConsentStatus('stt');
            if (!sttStatus?.consent_given) {
                console.log('🎤 No STT consent - cannot resume');
                this.sttPausedForMute = false;
                return;
            }

            // Check if we have audio tracks
            const localStream = this.mediaManager.getLocalStream();
            if (!localStream || localStream.getAudioTracks().length === 0) {
                console.log('🎤 No audio tracks available - cannot resume STT');
                this.sttPausedForMute = false;
                return;
            }

            console.log('🎤 Conditions met - resuming STT from mute');
            
            // Clear the mute flag
            this.sttPausedForMute = false;
            
            // Resume STT
            if (this.currentSpeechModule) {
                console.log('🎤 Resuming STT from mute');
                this.isSpeechEnabled = true;
                await this.currentSpeechModule.start(localStream);
            }

            console.log('🎤 ✅ STT resumed from microphone unmute');
        } catch (error) {
            console.error('🎤 Error resuming STT from mute:', error);
            this.isSpeechEnabled = false;
        } finally {
            this.sttTransitioning = false;
        }
    }

    // ===========================================
    // GETTER METHODS FOR COMPATIBILITY
    // ===========================================

    getMicrophoneMutedState() {
        return this.mediaManager.getMicrophoneMutedState();
    }

    getVideoHiddenState() {
        return this.mediaManager.getVideoHiddenState();
    }

    setMicrophoneMuted(muted) {
        this.mediaManager.setMicrophoneMuted(muted);
    }

    setVideoHidden(hidden) {
        this.mediaManager.setVideoHidden(hidden);
    }

    /**
     * Set up debug commands for troubleshooting WebRTC connections (PeerJS version)
     */
    setupDebugCommands() {
        // Make debug methods available on window for manual testing
        window.roomDebug = {
            // Show current room state
            showState: () => {
                console.log('🏠 Room state:');
                console.log('  - Slot occupants:', Array.from(this.slotOccupants.entries()));
                console.log('  - PeerJS stats:', this.simplePeerManager.getStats());
                console.log('  - Current user joined:', this.isJoined);
                console.log('  - Current slot:', this.currentSlotId);
            },
            
            // Reconnect to a peer (close and reestablish)
            reconnectPeer: (peerId) => {
                console.log(`🔄 Reconnecting to peer: ${peerId}`);
                this.simplePeerManager.closeCall(peerId);
                
                // Wait a moment then reconnect
                setTimeout(() => {
                    this.simplePeerManager.callPeer(peerId);
                }, 1000);
            },
            
            // Debug video controls visibility
            checkVideoControls: () => {
                console.log('🎛️ Checking video controls status:');
                document.querySelectorAll('.video-slot').forEach(slot => {
                    const slotId = slot.dataset.slotId;
                    const controls = slot.querySelector('.video-controls');
                    const overlay = slot.querySelector('.character-overlay');
                    const refreshBtn = controls?.querySelector('.refresh-connection-btn');
                    
                    console.log(`🎛️ Slot ${slotId}:`, {
                        hasControls: !!controls,
                        controlsVisible: controls ? !controls.classList.contains('hidden') : false,
                        controlsClasses: controls ? Array.from(controls.classList) : 'no-controls',
                        hasOverlay: !!overlay,
                        overlayVisible: overlay ? !overlay.classList.contains('hidden') : false,
                        hasRefreshBtn: !!refreshBtn,
                        refreshBtnData: refreshBtn ? {
                            peerId: refreshBtn.dataset.peerId,
                            participantName: refreshBtn.dataset.participantName,
                            disabled: refreshBtn.disabled
                        } : 'no-button'
                    });
                });
            },
            
            // Show PeerJS connection stats
            showPeerStats: () => {
                const stats = this.simplePeerManager.getStats();
                console.log('📊 PeerJS Connection Stats:', stats);
                console.log('  - Peer ID:', stats.peerId);
                console.log('  - Active calls:', stats.activeCalls);
                console.log('  - Connected peers:', stats.connectedPeers);
                console.log('  - Has local stream:', stats.hasLocalStream);
            }
        };
        
        console.log('🐛 Debug commands available:');
        console.log('  - window.roomDebug.showState() - Show current room state');
        console.log('  - window.roomDebug.showPeerStats() - Show PeerJS connection stats');
        console.log('  - window.roomDebug.reconnectPeer(peerId) - Reconnect to specific peer');
        console.log('  - window.roomDebug.checkVideoControls() - Debug video controls visibility');
    }

    // ===========================================
    // ABLY CONNECTION LIFECYCLE MANAGEMENT
    // ===========================================

    /**
     * Handles Ably connection suspension
     */
    handleAblyConnectionSuspended(error) {
        console.warn('🔌 Signaling connection suspended - waiting for automatic reconnection');
        
        // Show warning to user via status bar
        if (this.statusBarManager) {
            this.statusBarManager.showConnectionWarning('Connection interrupted - reconnecting...');
        }
    }

    /**
     * Handles Ably connection loss
     */
    handleAblyConnectionLost(error) {
        console.error('🔌 Signaling connection lost - automatic reconnection will be attempted');
        
        // Show error to user
        if (this.statusBarManager) {
            this.statusBarManager.showConnectionError('Disconnected from server - reconnecting...');
        }
    }

    /**
     * Handles Ably connection failure
     */
    handleAblyConnectionFailed(error) {
        console.error('🔌 Signaling connection failed:', error);
        
        // Show critical error to user
        if (this.statusBarManager) {
            this.statusBarManager.showConnectionError('Connection failed - please refresh the page');
        }
    }

    /**
     * Handles Ably reconnection after suspension/disconnection
     */
    handleAblyReconnected() {
        console.log('🔌 Signaling connection restored - recovering room state');
        
        // Clear any connection warnings
        if (this.statusBarManager) {
            this.statusBarManager.clearConnectionWarnings();
        }
        
        // Step 1: Request current room state from other users
        console.log('🔄 Step 1: Requesting current room state');
        this.ablyManager.publishToAbly('request-state', {
            requesterId: this.ablyManager.getCurrentPeerId()
        });
        
        // Step 2: Re-announce our presence if we're in a slot
        if (this.isJoined && this.currentSlotId) {
            console.log('🔄 Step 2: Re-announcing our presence');
            const participantData = this.roomData.participants.find(p => p.user_id === this.currentUserId);
            
            setTimeout(() => {
                this.ablyManager.publishToAbly('user-joined', {
                    slotId: this.currentSlotId,
                    participantData: participantData
                });
            }, 500); // Small delay to let state requests process first
        }
        
        // Step 3: Check for missing peer connections and attempt to re-establish
        setTimeout(() => {
            console.log('🔄 Step 3: Checking for missing peer connections');
            this.verifyPeerConnections();
        }, 2000); // Delay to allow state sync
        
        console.log('✅ Ably reconnection recovery complete');
    }

    /**
     * Verifies that all expected peer connections exist and are healthy
     */
    verifyPeerConnections() {
        console.log('🔍 Verifying peer connections...');
        
        // Check each slot occupant
        for (const [slotId, occupant] of this.slotOccupants.entries()) {
            // Skip our own slot
            if (slotId === this.currentSlotId) continue;
            
            const peerId = occupant.peerId;
            const hasConnection = this.simplePeerManager.isConnectedTo(peerId);
            
            console.log(`  - Slot ${slotId} (${occupant.participantData?.character_name}): ${hasConnection ? 'Connected' : 'MISSING'}`);
            
            // If connection is missing, attempt to re-establish
            if (!hasConnection) {
                console.warn(`🔄 Re-establishing connection to slot ${slotId} (${peerId})`);
                
                // Small delay to avoid simultaneous reconnection attempts
                setTimeout(() => {
                    this.simplePeerManager.callPeer(peerId);
                }, Math.random() * 1000); // Random delay 0-1s to prevent race conditions
            }
        }
        
        console.log('✅ Peer connection verification complete');
    }

    /**
     * Starts connection health monitoring
     */
    startConnectionHealthMonitoring() {
        // Check connection health every 30 seconds
        this.connectionHealthInterval = setInterval(() => {
            this.checkConnectionHealth();
        }, 30000);
        
        console.log('🏥 Connection health monitoring started');
    }

    /**
     * Checks the health of all connections (PeerJS version)
     * PeerJS handles most connection health automatically, so this is simplified
     */
    checkConnectionHealth() {
        // Check Reverb/Echo connection
        const reverbState = window.Echo?.connector?.pusher?.connection?.state;
        if (reverbState !== 'connected') {
            console.warn('🏥 Health check: Reverb not connected:', reverbState);
            return; // Don't check peer connections if signaling is down
        }
        
        // Get PeerJS stats
        const stats = this.simplePeerManager.getStats();
        const activePeers = stats.connectedPeers.length;
        const expectedPeers = Array.from(this.slotOccupants.values()).filter(o => !o.isLocal).length;
        
        if (activePeers < expectedPeers) {
            console.warn(`🏥 Health check: Connected to ${activePeers}/${expectedPeers} expected peers`);
            
            // Try to reconnect to missing peers
            for (const [slotId, occupant] of this.slotOccupants) {
                if (!occupant.isLocal && occupant.peerId && !this.simplePeerManager.isConnectedTo(occupant.peerId)) {
                    console.log(`🔄 Attempting to reconnect to peer: ${occupant.peerId}`);
                    this.simplePeerManager.callPeer(occupant.peerId);
                }
            }
        } else {
            console.log(`🏥 Health check: All ${activePeers} peer connections healthy`);
        }
    }

    /**
     * Stops connection health monitoring
     */
    stopConnectionHealthMonitoring() {
        if (this.connectionHealthInterval) {
            clearInterval(this.connectionHealthInterval);
            this.connectionHealthInterval = null;
            console.log('🏥 Connection health monitoring stopped');
        }
    }

    /**
     * Cleanup method to stop monitoring when leaving room
     */
    cleanup() {
        this.stopConnectionHealthMonitoring();
        // ... other cleanup
    }
}
