/**
 * RoomSessionInitializer - Handles room session page initialization
 * 
 * This module sets up:
 * - Helper functions for UI modals and controls
 * - Event listeners for room controls
 * - Loading screen management
 * - System initialization (WebRTC, Dice, Uppy)
 */

export class RoomSessionInitializer {
    constructor(roomData, currentUserId) {
        this.roomData = roomData;
        this.currentUserId = currentUserId;
        this.diceInitialized = false;
        this.webrtcInitialized = false;
        this.webrtcInitAttempts = 0;
        this.maxWebrtcInitAttempts = 50; // Max 5 seconds of retries
        
        this.setupGlobalHelpers();
        this.setupEventListeners();
        this.setupRecordingEventListeners();
        this.startInitialization();
    }

    /**
     * Setup global helper functions for modal and UI management
     */
    setupGlobalHelpers() {
        // Participants modal toggle
        window.toggleParticipantsModal = () => {
            const modal = document.getElementById('participantsModal');
            if (modal.classList.contains('hidden')) {
                modal.classList.remove('hidden');
                modal.style.display = 'block';
            } else {
                modal.classList.add('hidden');
                modal.style.display = 'none';
            }
        };
        
        // Leaving room modal helpers
        window.showLeavingModal = (statusText = 'Finalizing recording...') => {
            const modal = document.getElementById('leavingRoomModal');
            const statusMain = document.getElementById('leaving-status-main');
            
            if (statusMain) {
                statusMain.textContent = statusText;
            }
            
            if (modal) {
                modal.classList.remove('hidden');
                modal.style.display = 'block';
            }
        };
        
        window.updateLeavingModalStatus = (mainText, subText = null) => {
            const statusMain = document.getElementById('leaving-status-main');
            const statusSub = document.getElementById('leaving-status-sub');
            
            if (statusMain && mainText) {
                statusMain.textContent = mainText;
            }
            
            if (statusSub && subText) {
                statusSub.textContent = subText;
            }
        };
        
        window.hideLeavingModal = () => {
            const modal = document.getElementById('leavingRoomModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.style.display = 'none';
            }
        };
        
        // UIStateManager proxy methods
        window.showNameplateForSlot = (slotId, participantData) => {
            if (window.roomWebRTC && window.roomWebRTC.uiStateManager) {
                window.roomWebRTC.uiStateManager.showNameplateForSlot(slotId, participantData);
            }
        };
        
        window.hideNameplateForSlot = (slotId) => {
            if (window.roomWebRTC && window.roomWebRTC.uiStateManager) {
                window.roomWebRTC.uiStateManager.hideNameplateForSlot(slotId);
            }
        };
        
        window.setSlotState = (slotId, state) => {
            if (window.roomWebRTC && window.roomWebRTC.uiStateManager) {
                window.roomWebRTC.uiStateManager.setSlotState(slotId, state);
            }
        };
        
        window.setSlotToOccupied = (slotId, participantData) => {
            if (window.roomWebRTC && window.roomWebRTC.uiStateManager) {
                window.roomWebRTC.uiStateManager.setSlotToOccupied(slotId, participantData);
            }
        };
        
        window.handleRemoteParticipantJoin = (slotId, participantData) => {
            if (window.roomWebRTC && window.roomWebRTC.uiStateManager) {
                window.roomWebRTC.uiStateManager.handleRemoteParticipantJoin(slotId, participantData);
            }
        };
        
        window.handleRemoteParticipantLeave = (slotId, isGmSlot, userIsCreator) => {
            if (window.roomWebRTC && window.roomWebRTC.uiStateManager) {
                window.roomWebRTC.uiStateManager.handleRemoteParticipantLeave(slotId, isGmSlot, userIsCreator);
            }
        };
    }
    
    /**
     * Setup event listeners for room controls
     */
    setupEventListeners() {
        // Close participants modal when clicking outside
        document.addEventListener('click', (event) => {
            const modal = document.getElementById('participantsModal');
            const modalContent = event.target.closest('.bg-slate-900\\/95');
            const button = event.target.closest('[onclick="toggleParticipantsModal()"]');
            
            if (modal && !modal.classList.contains('hidden') && !modalContent && !button) {
                modal.classList.add('hidden');
                modal.style.display = 'none';
            }
        });
        
        // Microphone and Video Toggle Event Listeners
        document.addEventListener('DOMContentLoaded', () => {
            // Campaign layout buttons
            const micToggleBtn = document.getElementById('mic-toggle-btn');
            const videoToggleBtn = document.getElementById('video-toggle-btn');
            
            // Normal layout buttons  
            const micToggleBtnNormal = document.getElementById('mic-toggle-btn-normal');
            const videoToggleBtnNormal = document.getElementById('video-toggle-btn-normal');
            
            // Call RoomWebRTC methods directly
            if (micToggleBtn) {
                micToggleBtn.addEventListener('click', () => window.roomWebRTC?.toggleMicrophone());
            }
            if (videoToggleBtn) {
                videoToggleBtn.addEventListener('click', () => window.roomWebRTC?.toggleVideo());
            }
            if (micToggleBtnNormal) {
                micToggleBtnNormal.addEventListener('click', () => window.roomWebRTC?.toggleMicrophone());
            }
            if (videoToggleBtnNormal) {
                videoToggleBtnNormal.addEventListener('click', () => window.roomWebRTC?.toggleVideo());
            }
        });
    }
    
    /**
     * Setup event listeners for recording events
     */
    setupRecordingEventListeners() {
        // Listen for recording upload errors
        document.addEventListener('recording-upload-error', (event) => {
            console.error('🎥 Recording upload error event received:', event.detail);
            const { filename, error, provider } = event.detail;
            
            if (window.roomWebRTC && window.roomWebRTC.statusBarManager) {
                window.roomWebRTC.statusBarManager.showUploadError(error, provider);
            } else {
                console.error('🎥 StatusBarManager not available to display error');
            }
        });

        // Listen for recording upload retries
        document.addEventListener('recording-upload-retrying', (event) => {
            const { retryCount, maxRetries, provider } = event.detail;
            
            if (window.roomWebRTC && window.roomWebRTC.statusBarManager) {
                window.roomWebRTC.statusBarManager.showUploadRetry(retryCount, maxRetries, provider);
            }
        });

        // Listen for recording upload success (individual chunks)
        document.addEventListener('recording-upload-chunk-success', (event) => {
            const { provider } = event.detail;
            
            if (window.roomWebRTC && window.roomWebRTC.statusBarManager) {
                window.roomWebRTC.statusBarManager.showUploadSuccess(provider);
            }
        });

        // Listen for complete recording upload success
        document.addEventListener('recording-upload-success', (event) => {
            const { recording_id, provider } = event.detail;
            
            if (window.roomWebRTC && window.roomWebRTC.statusBarManager) {
                window.roomWebRTC.statusBarManager.showUploadSuccess(provider);
            }
        });
    }
    
    /**
     * Hide loading screen when both systems are ready
     */
    hideLoadingScreen() {
        if (this.diceInitialized && this.webrtcInitialized) {
            const loadingScreen = document.getElementById('room-loading-screen');
            const mainContent = document.getElementById('room-main-content');
            
            if (loadingScreen && mainContent) {
                setTimeout(() => {
                    loadingScreen.style.opacity = '0';
                    mainContent.style.opacity = '1';
                    
                    setTimeout(() => {
                        loadingScreen.style.display = 'none';
                    }, 500);
                }, 500); // Small delay to ensure everything is ready
            }
        }
    }
    
    /**
     * Initialize WebRTC system
     */
    async initializeWebRTC() {
        if (this.roomData && window.RoomWebRTC) {
            window.roomWebRTC = new window.RoomWebRTC(this.roomData);
            
            // Make FearCountdownManager debug methods globally accessible for testing
            window.debugFearCountdown = () => window.roomWebRTC.fearCountdownManager.debugGmPresence();
            window.forceShowGameState = () => window.roomWebRTC.fearCountdownManager.forceShowOverlays();
            window.forceHideGameState = () => window.roomWebRTC.fearCountdownManager.forceHideOverlays();
            
            // Check consent requirements immediately upon entering the room
            window.roomWebRTC.checkInitialConsentRequirements();
            
            this.webrtcInitialized = true;
            this.hideLoadingScreen();
        } else if (this.roomData && !window.RoomWebRTC && this.webrtcInitAttempts < this.maxWebrtcInitAttempts) {
            this.webrtcInitAttempts++;
            console.warn(`🎬 RoomWebRTC not available - attempt ${this.webrtcInitAttempts}/${this.maxWebrtcInitAttempts}`);
            // Retry after a short delay in case the bundle is still loading
            setTimeout(() => this.initializeWebRTC(), 100);
        } else if (this.webrtcInitAttempts >= this.maxWebrtcInitAttempts) {
            console.error('❌ Failed to initialize RoomWebRTC after maximum attempts. Please refresh the page.');
            this.webrtcInitialized = true; // Mark as "initialized" to allow page to show
            this.hideLoadingScreen();
        } else {
            console.warn('⚠️ No room data found, WebRTC not initialized');
            this.webrtcInitialized = true; // Mark as "initialized" to allow page to show
            this.hideLoadingScreen();
        }
    }
    
    /**
     * Initialize Uppy for video recording
     */
    initializeUppy() {
        if (this.roomData && this.roomData.recording_enabled && window.RoomUppy) {
            try {
                window.roomUppy = new window.RoomUppy(this.roomData, this.roomData.recording_settings);
            } catch (error) {
                console.warn('🎬 Failed to initialize Uppy for video recording:', error);
                console.warn('🎬 Falling back to direct upload method');
            }
        }
    }
    
    /**
     * Initialize dice system
     */
    initializeDice() {
        setTimeout(() => {
            if (typeof window.initDiceBox !== 'undefined') {
                try {
                    window.initDiceBox('#dice-container');
                    if (typeof window.setupDiceCallbacks === 'function') {
                        window.setupDiceCallbacks((rollResult) => {
                            // Dice roll completed
                        });
                    }
                    this.diceInitialized = true;
                    this.hideLoadingScreen();
                } catch (error) {
                    console.error('Error initializing room dice system:', error);
                    this.diceInitialized = true; // Mark as initialized even on error
                    this.hideLoadingScreen();
                }
            } else {
                console.warn('Dice functions not available in room');
                this.diceInitialized = true; // Mark as initialized even if not available
                this.hideLoadingScreen();
            }
        }, 1000);
    }
    
    /**
     * Setup initialization timeout error handler
     */
    setupInitializationTimeout() {
        setTimeout(() => {
            if (!this.diceInitialized || !this.webrtcInitialized) {
                console.error('❌ System initialization timeout');
                
                const loadingScreen = document.getElementById('room-loading-screen');
                if (loadingScreen) {
                    loadingScreen.innerHTML = `
                        <div class="text-center max-w-md mx-auto p-6">
                            <div class="mb-6">
                                <svg class="w-16 h-16 mx-auto text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h2 class="text-2xl font-outfit font-bold text-red-400 mb-3">Initialization Failed</h2>
                            <p class="text-slate-300 mb-4">Failed to initialize room systems.</p>
                            <div class="text-left bg-slate-900/80 backdrop-blur-xl border border-slate-700 rounded-lg p-4 mb-6 text-sm">
                                <p class="text-slate-400 mb-2">This could be due to:</p>
                                <ul class="list-disc list-inside text-slate-400 space-y-1">
                                    <li>Network connectivity issues</li>
                                    <li>Browser compatibility problems</li>
                                    <li>Script loading failures</li>
                                </ul>
                            </div>
                            <button onclick="window.location.reload()" class="px-6 py-3 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-semibold rounded-lg transition-all duration-300 shadow-lg hover:shadow-xl">
                                Reload Page
                            </button>
                        </div>
                    `;
                }
            }
        }, 5000); // 5 second maximum loading time
    }
    
    /**
     * Start all initialization tasks
     */
    startInitialization() {
        // Setup timeout handler
        this.setupInitializationTimeout();
        
        // Initialize dice system
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                this.initializeDice();
            });
        } else {
            this.initializeDice();
        }
        
        // Initialize Uppy
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                this.initializeUppy();
            });
        } else {
            this.initializeUppy();
        }
        
        // Initialize WebRTC with ModuleLoader
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => this.initializeWebRTCWithModuleLoader(), 50);
            });
        } else {
            setTimeout(() => this.initializeWebRTCWithModuleLoader(), 50);
        }
    }
    
    /**
     * Initialize WebRTC using ModuleLoader for reliable loading
     */
    async initializeWebRTCWithModuleLoader() {
        if (!this.roomData) {
            console.warn('⚠️ No room data found, WebRTC not initialized');
            this.webrtcInitialized = true;
            this.hideLoadingScreen();
            return;
        }
        
        try {
            // Use ModuleLoader if available, otherwise fallback to polling
            if (window.moduleLoader) {
                console.log('📦 Using ModuleLoader to load RoomWebRTC...');
                const RoomWebRTC = await window.moduleLoader.waitFor('RoomWebRTC', 10000);
                this.initializeWebRTCInstance(RoomWebRTC);
            } else {
                console.warn('⚠️ ModuleLoader not available, using fallback method');
                this.initializeWebRTC(); // Fallback to old polling method
            }
        } catch (error) {
            console.error('❌ Failed to load RoomWebRTC:', error);
            this.webrtcInitialized = true;
            this.hideLoadingScreen();
        }
    }
    
    /**
     * Initialize WebRTC instance once loaded
     */
    initializeWebRTCInstance(RoomWebRTC) {
        try {
            console.log('🎬 Initializing Room WebRTC...');
            window.roomWebRTC = new RoomWebRTC(this.roomData);
            
            // Make FearCountdownManager debug methods globally accessible for testing
            window.debugFearCountdown = () => window.roomWebRTC.fearCountdownManager.debugGmPresence();
            window.forceShowGameState = () => window.roomWebRTC.fearCountdownManager.forceShowOverlays();
            window.forceHideGameState = () => window.roomWebRTC.fearCountdownManager.forceHideOverlays();
            
            // Check consent requirements immediately upon entering the room
            window.roomWebRTC.checkInitialConsentRequirements();
            
            this.webrtcInitialized = true;
            this.hideLoadingScreen();
            console.log('✅ Room WebRTC initialized successfully');
        } catch (error) {
            console.error('❌ Error initializing RoomWebRTC:', error);
            this.webrtcInitialized = true;
            this.hideLoadingScreen();
        }
    }
}

