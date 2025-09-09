/**
 * SlotManager - Manages video slot UI and interactions
 * 
 * Handles slot occupancy display, character overlays, loading states,
 * and slot-related UI updates.
 */

export class SlotManager {
    constructor(roomWebRTC) {
        this.roomWebRTC = roomWebRTC;
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
}
