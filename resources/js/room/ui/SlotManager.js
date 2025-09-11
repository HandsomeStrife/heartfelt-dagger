/**
 * SlotManager - Manages video slot UI and interactions
 * 
 * Handles slot occupancy display, character overlays, loading states,
 * and slot-related UI updates.
 */

export class SlotManager {
    constructor(roomWebRTC) {
        this.roomWebRTC = roomWebRTC;
        
        // Initialize video controls for any existing server-side participants
        this.initializeExistingVideoControls();
    }

    /**
     * Initialize video controls for slots that already have server-side participant data
     */
    initializeExistingVideoControls() {
        console.log('🎛️ Initializing existing video controls...');
        
        // Wait for DOM to be ready
        setTimeout(() => {
            const slots = document.querySelectorAll('.video-slot');
            console.log(`🎛️ Found ${slots.length} video slots to check`);
            
            slots.forEach(slotContainer => {
                const slotId = slotContainer.dataset.slotId;
                const characterOverlay = slotContainer.querySelector('.character-overlay');
                
                console.log(`🎛️ Checking slot ${slotId}:`, {
                    hasOverlay: !!characterOverlay,
                    overlayHidden: characterOverlay ? characterOverlay.classList.contains('hidden') : 'no-overlay'
                });
                
                // If character overlay exists and is not hidden, show video controls
                if (characterOverlay && !characterOverlay.classList.contains('hidden')) {
                    console.log(`🎛️ Slot ${slotId} has visible character overlay, showing video controls`);
                    this.showVideoControls(slotContainer);
                } else {
                    console.log(`🎛️ Slot ${slotId} has no visible character overlay, skipping`);
                }
            });
        }, 100);
    }

    /**
     * Shows character overlay with participant data
     */
    showCharacterOverlay(slotContainer, participantData) {
        const overlay = slotContainer.querySelector('.character-overlay');
        if (overlay && participantData) {
            // Update character name
            const nameElement = overlay.querySelector('.character-name');
            if (nameElement) {
                if (participantData.is_host) {
                    nameElement.textContent = 'GAME MASTER';
                } else {
                    nameElement.textContent = participantData.character_name || participantData.username || 'Unknown Player';
                }
            }

            // Update character class
            const classElement = overlay.querySelector('.character-class');
            if (classElement) {
                if (participantData.is_host) {
                    classElement.textContent = 'NARRATOR OF TALES';
                } else {
                    classElement.textContent = participantData.character_class || 'NO CLASS';
                }
            }

            overlay.classList.remove('hidden');
            
            // Show video controls since slot is now occupied
            this.showVideoControls(slotContainer);
        }
    }

    /**
     * Shows loading state for a slot
     */
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

    /**
     * Hides loading state for a slot
     */
    hideLoadingState(slotContainer) {
        const loadingSpinner = slotContainer.querySelector('.loading-spinner');
        if (loadingSpinner) {
            loadingSpinner.classList.add('hidden');
            loadingSpinner.style.display = 'none';
        }
    }

    /**
     * Shows video controls for a slot
     */
    showVideoControls(slotContainer) {
        const leaveBtn = slotContainer.querySelector('.leave-btn');
        if (leaveBtn) {
            leaveBtn.style.display = 'block';
            leaveBtn.classList.remove('hidden');
        }
    }

    /**
     * Resets slot UI to default state
     */
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

        // Hide video controls since slot is now empty
        this.hideVideoControls(slotContainer);

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

        // Clean up media control indicators
        const micIndicator = slotContainer.querySelector('.microphone-indicator');
        const videoOffIndicator = slotContainer.querySelector('.video-off-indicator');
        
        if (micIndicator) micIndicator.remove();
        if (videoOffIndicator) videoOffIndicator.remove();
    }

    /**
     * Updates slot occupancy display
     */
    updateSlotOccupancy(slotId, occupantData) {
        const slotContainer = document.querySelector(`[data-slot-id="${slotId}"]`);
        if (!slotContainer) return;

        if (occupantData) {
            // Show occupant
            this.showCharacterOverlay(slotContainer, occupantData.participantData);
            
            // Hide join button
            const joinBtn = slotContainer.querySelector('.join-btn');
            if (joinBtn) {
                joinBtn.style.display = 'none';
            }
        } else {
            // Clear occupant
            this.resetSlotUI(slotContainer);
        }
    }

    /**
     * Gets slot container by ID
     */
    getSlotContainer(slotId) {
        return document.querySelector(`[data-slot-id="${slotId}"]`);
    }

    /**
     * Sets up slot event listeners (leave buttons only - join buttons handled by UIStateManager)
     */
    setupSlotEventListeners() {
        // Add click handlers to all leave buttons
        document.querySelectorAll('.leave-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                this.roomWebRTC.leaveSlot();
            });
        });
    }

    /**
     * Updates all slot displays based on current occupancy
     */
    updateAllSlots() {
        this.roomWebRTC.slotOccupants.forEach((occupantData, slotId) => {
            this.updateSlotOccupancy(slotId, occupantData);
        });
    }

    /**
     * Clears all slot occupancy
     */
    clearAllSlots() {
        document.querySelectorAll('.video-slot').forEach(slotContainer => {
            const slotId = parseInt(slotContainer.dataset.slotId);
            this.resetSlotUI(slotContainer);
        });
    }

    /**
     * Shows video controls for an occupied slot
     */
    showVideoControls(slotContainer) {
        const slotId = slotContainer.dataset.slotId;
        console.log(`🎛️ Showing video controls for slot ${slotId}`);
        
        const videoControls = slotContainer.querySelector('.video-controls');
        if (videoControls) {
            console.log(`🎛️ Video controls element found for slot ${slotId}`);
            
            videoControls.classList.remove('hidden');
            videoControls.classList.add('flex');
            
            console.log(`🎛️ Video controls classes updated for slot ${slotId}:`, {
                hidden: videoControls.classList.contains('hidden'),
                flex: videoControls.classList.contains('flex'),
                allClasses: Array.from(videoControls.classList)
            });
            
            // Update refresh button with current slot data if available
            const refreshBtn = videoControls.querySelector('.refresh-connection-btn');
            if (refreshBtn) {
                console.log(`🔄 Refresh button found for slot ${slotId}`);
                
                // Try to get peer ID from slot occupant data
                const slotIdNum = parseInt(slotContainer.dataset.slotId);
                const occupantData = this.roomWebRTC.slotOccupants.get(slotIdNum);
                
                if (occupantData) {
                    const peerId = occupantData.peerId || '';
                    const participantName = this.getParticipantDisplayName(occupantData.participantData);
                    
                    refreshBtn.dataset.peerId = peerId;
                    refreshBtn.dataset.participantName = participantName;
                    refreshBtn.title = `Refresh video connection for ${participantName}`;
                    
                    console.log(`🔄 Refresh button data updated for slot ${slotId}:`, {
                        peerId,
                        participantName,
                        title: refreshBtn.title
                    });
                } else {
                    console.log(`⚠️ No occupant data found for slot ${slotId}`);
                }
            } else {
                console.log(`❌ Refresh button not found for slot ${slotId}`);
            }
        } else {
            console.log(`❌ Video controls element not found for slot ${slotId}`);
        }
    }

    /**
     * Hides video controls for an empty slot
     */
    hideVideoControls(slotContainer) {
        const slotId = slotContainer.dataset.slotId;
        console.log(`🎛️ Hiding video controls for slot ${slotId}`);
        
        const videoControls = slotContainer.querySelector('.video-controls');
        if (videoControls) {
            console.log(`🎛️ Video controls element found for hiding on slot ${slotId}`);
            
            videoControls.classList.add('hidden');
            videoControls.classList.remove('flex');
            
            console.log(`🎛️ Video controls hidden for slot ${slotId}:`, {
                hidden: videoControls.classList.contains('hidden'),
                flex: videoControls.classList.contains('flex'),
                allClasses: Array.from(videoControls.classList)
            });
        } else {
            console.log(`❌ Video controls element not found for hiding on slot ${slotId}`);
        }
    }

    /**
     * Gets display name for a participant
     */
    getParticipantDisplayName(participantData) {
        if (!participantData) return 'Unknown';
        
        if (participantData.is_host) {
            return 'Game Master';
        }
        
        return participantData.character_name || participantData.username || 'Unknown Player';
    }
}
