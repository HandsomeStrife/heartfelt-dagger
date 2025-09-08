/**
 * UIStateManager - Manages overall UI state and interactions
 * 
 * Handles enabling/disabling UI elements, managing button states,
 * and coordinating UI updates across the application.
 */

export class UIStateManager {
    constructor(roomWebRTC) {
        this.roomWebRTC = roomWebRTC;
        this.setupSlotEventListeners();
    }

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
    disableJoinUIForConsent() {
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
     * Sets up page refresh protection
     */
    setupPageRefreshProtection() {
        // Warn user if they try to leave/refresh while recording
        window.addEventListener('beforeunload', (event) => {
            console.log('🚨 Page unload detected');
            console.log(`  - Is recording: ${this.roomWebRTC.videoRecorder.isCurrentlyRecording()}`);
            const recordedChunks = this.roomWebRTC.videoRecorder.getRecordedChunks();
            console.log(`  - Recorded chunks: ${recordedChunks ? recordedChunks.length : 0}`);
            
            if (this.roomWebRTC.videoRecorder.isCurrentlyRecording() && recordedChunks && recordedChunks.length > 0) {
                const message = 'Recording in progress! If you leave now, your recording will be lost. Stop recording first to save your video.';
                console.log('🚨 Showing page unload warning');
                event.preventDefault();
                event.returnValue = message;
                return message;
            } else {
                console.log('🚨 No warning needed - not recording or no data');
            }
        });

        // Attempt to save recording if page is being unloaded
        window.addEventListener('unload', () => {
            const recordedChunks = this.roomWebRTC.videoRecorder.getRecordedChunks();
            if (this.roomWebRTC.videoRecorder.isCurrentlyRecording() && recordedChunks.length > 0) {
                // Try to quickly save the recording before leaving
                this.emergencySaveRecording();
            }
        });
    }

    /**
     * Emergency save when page is being closed
     */
    emergencySaveRecording() {
        try {
            const recordedChunks = this.roomWebRTC.videoRecorder.getRecordedChunks();
            if (recordedChunks.length === 0) return;
            
            console.warn('🚨 Emergency save: Page closing with active recording');
            
            // Create emergency download
            const mimeType = this.roomWebRTC.videoRecorder.getRecordingMimeType();
            const combinedBlob = new Blob(recordedChunks, { type: mimeType });
            const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
            const ext = mimeType.includes('webm') ? 'webm' : 'mp4';
            const emergencyFilename = `room-recording-EMERGENCY-${timestamp}.${ext}`;
            
            // Use Navigator.sendBeacon if available for more reliable delivery
            if (navigator.sendBeacon) {
                // Can't use sendBeacon for downloads, but we can at least log the attempt
                console.warn('🚨 Recording data exists but cannot be saved during page unload');
                console.warn('🚨 Please stop recording properly before leaving the page');
            } else {
                // Fallback: try immediate download (may not work)
                const url = URL.createObjectURL(combinedBlob);
                const link = document.createElement('a');
                link.href = url;
                link.download = emergencyFilename;
                link.click();
                console.warn(`🚨 Emergency download attempted: ${emergencyFilename}`);
            }
        } catch (error) {
            console.error('🚨 Emergency save failed:', error);
        }
    }

    /**
     * Updates UI based on current room state
     */
    updateUIState() {
        // Update slot displays
        this.roomWebRTC.slotManager.updateAllSlots();
        
        // Update recording UI if recording
        if (this.roomWebRTC.videoRecorder.isCurrentlyRecording()) {
            this.roomWebRTC.statusBarManager.updateRecordingStatus();
        }
    }

    /**
     * Resets all UI to initial state
     */
    resetUI() {
        // Enable join UI
        this.enableJoinUI();
        
        // Clear all slots
        this.roomWebRTC.slotManager.clearAllSlots();
        
        // Hide recording status
        this.roomWebRTC.statusBarManager.hideRecordingStatus();
    }

    /**
     * Shows error message to user
     */
    showError(message, title = 'Error') {
        // Simple alert for now - could be enhanced with custom modal
        alert(`${title}\n\n${message}`);
    }

    /**
     * Shows success message to user
     */
    showSuccess(message, title = 'Success') {
        // Simple alert for now - could be enhanced with custom modal
        alert(`${title}\n\n${message}`);
    }

    /**
     * Shows loading indicator
     */
    showLoading(message = 'Loading...') {
        // Could be enhanced with custom loading overlay
        console.log(`🔄 Loading: ${message}`);
    }

    /**
     * Hides loading indicator
     */
    hideLoading() {
        console.log('🔄 Loading complete');
    }

    // ===========================================
    // SLOT STATE MANAGEMENT
    // ===========================================

    /**
     * Sets up event listeners for slot interactions
     */
    setupSlotEventListeners() {
        // This will be called after DOM is ready, so we need to wait
        document.addEventListener('DOMContentLoaded', () => {
            this.attachSlotEventListeners();
        });
        
        // If DOM is already ready
        if (document.readyState !== 'loading') {
            this.attachSlotEventListeners();
        }
    }

    /**
     * Attaches click event listeners to slot buttons
     */
    attachSlotEventListeners() {
        // Attach GM join button listeners
        document.querySelectorAll('.slot-gm-join').forEach(button => {
            button.addEventListener('click', (e) => {
                const slotId = e.target.closest('[data-slot-id]')?.getAttribute('data-slot-id');
                if (slotId) {
                    this.handleGmJoin(parseInt(slotId));
                }
            });
        });

        // Attach player join button listeners
        document.querySelectorAll('.slot-player-join').forEach(button => {
            button.addEventListener('click', (e) => {
                const slotId = e.target.closest('[data-slot-id]')?.getAttribute('data-slot-id');
                if (slotId) {
                    this.handlePlayerJoin(parseInt(slotId));
                }
            });
        });
    }

    /**
     * Sets the state of a specific slot
     */
    setSlotState(slotId, state) {
        const slot = document.querySelector(`[data-slot-id="${slotId}"]`);
        if (!slot) return;
        
        // Hide all slot states
        const allStates = slot.querySelectorAll('.slot-state');
        allStates.forEach(stateEl => stateEl.classList.add('hidden'));
        
        // Show the requested state
        const targetState = slot.querySelector(`.slot-${state}`);
        if (targetState) {
            targetState.classList.remove('hidden');
        }
    }

    /**
     * Shows loading state for a slot
     */
    showSlotLoadingState(slotId) {
        const slot = document.querySelector(`[data-slot-id="${slotId}"]`);
        if (!slot) return;
        
        // Hide all slot states
        const allStates = slot.querySelectorAll('.slot-state');
        allStates.forEach(stateEl => stateEl.classList.add('hidden'));
        
        // Show loading spinner
        const loadingSpinner = slot.querySelector('.loading-spinner');
        if (loadingSpinner) {
            loadingSpinner.classList.remove('hidden');
            loadingSpinner.classList.add('flex');
        }
    }

    /**
     * Hides loading state for a slot
     */
    hideSlotLoadingState(slotId) {
        const slot = document.querySelector(`[data-slot-id="${slotId}"]`);
        if (!slot) return;
        
        const loadingSpinner = slot.querySelector('.loading-spinner');
        if (loadingSpinner) {
            loadingSpinner.classList.add('hidden');
            loadingSpinner.classList.remove('flex');
        }
    }

    /**
     * Handles GM joining a slot
     */
    handleGmJoin(slotId) {
        this.showSlotLoadingState(slotId);
        
        // Hide all player join buttons and show "Waiting for Player" instead
        const allSlots = document.querySelectorAll('[data-slot-id]');
        allSlots.forEach(slot => {
            const slotIdValue = slot.getAttribute('data-slot-id');
            const playerJoinBtn = slot.querySelector('.slot-player-join');
            
            // If this is a player join button, hide it and show waiting
            if (playerJoinBtn && !playerJoinBtn.classList.contains('hidden')) {
                this.setSlotState(slotIdValue, 'waiting');
            }
        });
        
        // Simulate joining process
        setTimeout(() => {
            this.hideSlotLoadingState(slotId);
            
            // Get GM participant data
            const currentParticipant = window.roomData?.participants?.find(p => p.user_id === window.currentUserId);
            const participantData = {
                user_id: window.currentUserId,
                username: currentParticipant?.username || 'Game Master',
                character_name: 'GAME MASTER',
                character_class: 'NARRATOR OF TALES',
                character_subclass: '',
                character_ancestry: '',
                character_community: '',
                is_host: true
            };
            
            this.setSlotToOccupied(slotId, participantData);
            
            // Integrate with WebRTC system
            if (this.roomWebRTC && this.roomWebRTC.joinSlot) {
                const slotContainer = document.querySelector(`[data-slot-id="${slotId}"]`);
                this.roomWebRTC.joinSlot(slotId, slotContainer);
            }
        }, 1000);
        
        console.log(`GM attempting to join slot ${slotId}`);
    }

    /**
     * Handles player joining a slot
     */
    handlePlayerJoin(slotId) {
        this.showSlotLoadingState(slotId);
        
        // Hide all player join buttons and show "Waiting for Player" instead
        const allSlots = document.querySelectorAll('[data-slot-id]');
        allSlots.forEach(slot => {
            const slotIdValue = slot.getAttribute('data-slot-id');
            const playerJoinBtn = slot.querySelector('.slot-player-join');
            
            // If this is a player join button (not the slot being joined), hide it and show waiting
            if (playerJoinBtn && !playerJoinBtn.classList.contains('hidden') && slotIdValue !== slotId.toString()) {
                this.setSlotState(slotIdValue, 'waiting');
            }
        });
        
        // Simulate joining process
        setTimeout(() => {
            this.hideSlotLoadingState(slotId);
            
            // Get participant data from roomData for current user
            const currentParticipant = window.roomData?.participants?.find(p => p.user_id === window.currentUserId);
            const participantData = {
                user_id: window.currentUserId,
                username: currentParticipant?.username || 'Unknown Player',
                character_name: currentParticipant?.character_name || 'Unknown Character',
                character_class: currentParticipant?.character_class || 'Unknown',
                character_subclass: currentParticipant?.character_subclass || '',
                character_ancestry: currentParticipant?.character_ancestry || '',
                character_community: currentParticipant?.character_community || '',
                is_host: false
            };
            
            this.setSlotToOccupied(slotId, participantData);
            
            // Integrate with WebRTC system
            if (this.roomWebRTC && this.roomWebRTC.joinSlot) {
                const slotContainer = document.querySelector(`[data-slot-id="${slotId}"]`);
                this.roomWebRTC.joinSlot(slotId, slotContainer);
            }
        }, 1000);
        
        console.log(`Player attempting to join slot ${slotId}`);
    }

    /**
     * Sets a slot to occupied state with participant data
     */
    setSlotToOccupied(slotId, participantData) {
        const slot = document.querySelector(`[data-slot-id="${slotId}"]`);
        if (!slot) return;
        
        // Hide all slot states (join buttons, waiting text, etc.)
        const allStates = slot.querySelectorAll('.slot-state');
        allStates.forEach(stateEl => stateEl.classList.add('hidden'));
        
        // Add class banner if not host and has class
        if (!participantData.is_host && participantData.character_class && participantData.character_class !== 'Unknown') {
            this.addClassBannerToSlot(slot, participantData.character_class);
        }
        
        // Show nameplate overlay with participant data
        this.showNameplateForSlot(slotId, participantData);
    }

    /**
     * Shows nameplate when user joins a video slot
     */
    showNameplateForSlot(slotId, participantData) {
        const slot = document.querySelector(`[data-slot-id="${slotId}"]`);
        if (slot) {
            const overlay = slot.querySelector('.character-overlay');
            if (overlay && participantData) {
                // Update nameplate content with actual participant data
                const nameElement = overlay.querySelector('.character-name');
                const classElement = overlay.querySelector('.character-class');
                const subclassElement = overlay.querySelector('.character-subclass');
                
                // Update character name
                if (nameElement) {
                    if (participantData.is_host) {
                        nameElement.textContent = 'GAME MASTER';
                    } else {
                        nameElement.textContent = participantData.character_name || participantData.username || 'Unknown Player';
                    }
                }
                
                // Update character class
                if (classElement) {
                    if (participantData.is_host) {
                        classElement.textContent = 'NARRATOR OF TALES';
                    } else {
                        classElement.textContent = participantData.character_class || 'NO CLASS';
                    }
                }
                
                // Update subclass if exists
                if (participantData.character_subclass && !participantData.is_host) {
                    const subclassElement = overlay.querySelector('.character-subclass');
                    const subclassSeparator = overlay.querySelector('.character-subclass-separator');
                    
                    if (subclassElement) {
                        subclassElement.textContent = participantData.character_subclass;
                        subclassElement.classList.remove('hidden');
                    }
                    if (subclassSeparator) {
                        subclassSeparator.classList.remove('hidden');
                    }
                }
                
                // Add class banner if not host and has class
                if (!participantData.is_host && participantData.character_class && participantData.character_class !== 'Unknown') {
                    this.addClassBannerToSlot(slot, participantData.character_class);
                }
                
                overlay.classList.remove('hidden');
            }
            
            // Hide the join button for this slot
            const joinBtn = slot.querySelector('.join-btn');
            if (joinBtn) {
                joinBtn.style.display = 'none';
            }
        }
    }

    /**
     * Hides nameplate when user leaves a video slot
     */
    hideNameplateForSlot(slotId) {
        const slot = document.querySelector(`[data-slot-id="${slotId}"]`);
        if (slot) {
            const overlay = slot.querySelector('.character-overlay');
            if (overlay) {
                overlay.classList.add('hidden');
                
                // Reset nameplate content
                const nameElement = overlay.querySelector('.character-name');
                const classElement = overlay.querySelector('.character-class');
                const subclassElement = overlay.querySelector('.character-subclass');
                const subclassSeparator = overlay.querySelector('.character-subclass-separator');
                
                if (nameElement) nameElement.textContent = '';
                if (classElement) classElement.textContent = '';
                if (subclassElement) {
                    subclassElement.textContent = '';
                    subclassElement.classList.add('hidden');
                }
                if (subclassSeparator) {
                    subclassSeparator.classList.add('hidden');
                }
            }
            
            // Remove class banner and reset padding
            this.removeClassBannerFromSlot(slot);
            
            // Show the join button again if this isn't a GM slot for non-GM users
            const joinBtn = slot.querySelector('.join-btn');
            if (joinBtn) {
                joinBtn.style.display = 'block';
            }
        }
    }

    /**
     * Adds class banner to a slot
     */
    addClassBannerToSlot(slot, characterClass) {
        const normalizedClass = characterClass.toLowerCase();
        const hiddenBanner = document.querySelector(`#hidden-class-banners .hidden-banner-${normalizedClass}`);
        if (hiddenBanner) {
            // Target the banner container in the main slot area (not the nameplate overlay)
            const bannerContainer = slot.querySelector('.character-banner-container');
            if (bannerContainer) {
                // Clear existing banner
                bannerContainer.innerHTML = '';
                
                // Clone the hidden banner
                const clonedBanner = hiddenBanner.cloneNode(true);
                clonedBanner.classList.remove('hidden');
                bannerContainer.appendChild(clonedBanner);
                
                // Adjust the character info padding to account for banner
                const characterInfo = slot.querySelector('.character-info');
                if (characterInfo) {
                    characterInfo.classList.remove('px-4');
                    characterInfo.classList.add('ml-9', 'pl-4');
                }
            }
        }
    }

    /**
     * Removes class banner from a slot
     */
    removeClassBannerFromSlot(slot) {
        const bannerContainer = slot.querySelector('.character-banner-container');
        if (bannerContainer) {
            bannerContainer.innerHTML = '';
        }
        
        // Reset character info padding
        const characterInfo = slot.querySelector('.character-info');
        if (characterInfo) {
            characterInfo.classList.remove('ml-9', 'pl-4');
            characterInfo.classList.add('px-4');
        }
    }

    /**
     * Handles when other participants join via Ably
     */
    handleRemoteParticipantJoin(slotId, participantData) {
        this.setSlotToOccupied(slotId, participantData);
    }

    /**
     * Handles when participants leave via Ably
     */
    handleRemoteParticipantLeave(slotId, isGmSlot, userIsCreator) {
        // Remove banner and reset padding
        const slot = document.querySelector(`[data-slot-id="${slotId}"]`);
        if (slot) {
            this.removeClassBannerFromSlot(slot);
        }
        
        // Reset to appropriate empty state
        if (isGmSlot) {
            if (userIsCreator) {
                this.setSlotState(slotId, 'gm-join');
            } else {
                this.setSlotState(slotId, 'gm-reserved');
            }
        } else {
            if (userIsCreator) {
                this.setSlotState(slotId, 'waiting');
            } else {
                this.setSlotState(slotId, 'player-join');
            }
        }
        
        // Hide nameplate
        this.hideNameplateForSlot(slotId);
    }
}
