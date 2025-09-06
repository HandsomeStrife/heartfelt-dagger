/**
 * SlotManager - Manages video slot UI and participant display
 * 
 * Handles the visual representation of participants in video slots,
 * manages slot occupancy, video stream display, and participant information.
 */
export class SlotManager {
    constructor(roomWebRTC) {
        this.roomWebRTC = roomWebRTC;
        this.slotElements = new Map(); // Map of slotId -> DOM element
        this.maxSlots = 6; // Default, can be updated based on room settings
        
        this.initializeSlots();
    }

    /**
     * Initializes video slots in the UI
     */
    initializeSlots() {
        // Find all video slot elements in the DOM
        const slots = document.querySelectorAll('[data-slot-id]');
        
        slots.forEach(slotElement => {
            const slotId = slotElement.getAttribute('data-slot-id');
            this.slotElements.set(slotId, slotElement);
            
            // Set up slot click handler for joining
            this.setupSlotClickHandler(slotElement, slotId);
        });
        
        console.log(`🎬 Initialized ${this.slotElements.size} video slots`);
        
        // Update max slots based on available slots
        this.maxSlots = Math.max(this.slotElements.size, 6);
    }

    /**
     * Sets up click handler for joining a slot
     */
    setupSlotClickHandler(slotElement, slotId) {
        const joinButton = slotElement.querySelector('.join-slot-btn, [data-join-slot]');
        if (joinButton) {
            joinButton.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleSlotJoin(slotId);
            });
        }
    }

    /**
     * Handles joining a specific slot
     */
    async handleSlotJoin(slotId) {
        console.log(`🎬 Attempting to join slot: ${slotId}`);
        
        // Check if slot is available
        if (this.roomWebRTC.slotOccupants.has(slotId)) {
            console.warn(`🎬 Slot ${slotId} is already occupied`);
            return;
        }
        
        // Check if user is already in a slot
        if (this.roomWebRTC.currentSlotId) {
            console.log(`🎬 Leaving current slot ${this.roomWebRTC.currentSlotId} before joining ${slotId}`);
            await this.leaveCurrentSlot();
        }
        
        // Join the new slot
        try {
            await this.roomWebRTC.joinSlot(slotId);
        } catch (error) {
            console.error(`🎬 Failed to join slot ${slotId}:`, error);
            this.showSlotError(slotId, 'Failed to join slot');
        }
    }

    /**
     * Leaves the current slot
     */
    async leaveCurrentSlot() {
        if (!this.roomWebRTC.currentSlotId) return;
        
        const slotId = this.roomWebRTC.currentSlotId;
        console.log(`🎬 Leaving slot: ${slotId}`);
        
        try {
            await this.roomWebRTC.leaveSlot();
        } catch (error) {
            console.error(`🎬 Failed to leave slot ${slotId}:`, error);
        }
    }

    /**
     * Updates slot display when a participant joins
     */
    updateSlotOccupied(slotId, participantData, stream = null) {
        const slotElement = this.slotElements.get(slotId);
        if (!slotElement) {
            console.warn(`🎬 Slot element not found for slot: ${slotId}`);
            return;
        }
        
        console.log(`🎬 Updating slot ${slotId} as occupied by:`, participantData.name);
        
        // Hide join button
        const joinButton = slotElement.querySelector('.join-slot-btn, [data-join-slot]');
        if (joinButton) {
            joinButton.style.display = 'none';
        }
        
        // Show participant info
        this.updateParticipantInfo(slotElement, participantData);
        
        // Set up video stream if provided
        if (stream) {
            this.setupVideoStream(slotElement, stream, participantData);
        }
        
        // Add occupied styling
        slotElement.classList.add('slot-occupied');
        slotElement.classList.remove('slot-empty');
        
        // Update slot counter
        this.updateSlotCounter();
    }

    /**
     * Updates slot display when a participant leaves
     */
    updateSlotEmpty(slotId) {
        const slotElement = this.slotElements.get(slotId);
        if (!slotElement) {
            console.warn(`🎬 Slot element not found for slot: ${slotId}`);
            return;
        }
        
        console.log(`🎬 Updating slot ${slotId} as empty`);
        
        // Show join button
        const joinButton = slotElement.querySelector('.join-slot-btn, [data-join-slot]');
        if (joinButton) {
            joinButton.style.display = '';
        }
        
        // Clear participant info
        this.clearParticipantInfo(slotElement);
        
        // Clear video stream
        this.clearVideoStream(slotElement);
        
        // Remove occupied styling
        slotElement.classList.remove('slot-occupied');
        slotElement.classList.add('slot-empty');
        
        // Update slot counter
        this.updateSlotCounter();
    }

    /**
     * Updates participant information display
     */
    updateParticipantInfo(slotElement, participantData) {
        // Update participant name
        const nameElement = slotElement.querySelector('.participant-name, [data-participant-name]');
        if (nameElement) {
            nameElement.textContent = participantData.name || 'Unknown';
        }
        
        // Update character name if available
        const characterElement = slotElement.querySelector('.character-name, [data-character-name]');
        if (characterElement && participantData.character_name) {
            characterElement.textContent = participantData.character_name;
            characterElement.style.display = '';
        } else if (characterElement) {
            characterElement.style.display = 'none';
        }
        
        // Update role/status
        const roleElement = slotElement.querySelector('.participant-role, [data-participant-role]');
        if (roleElement) {
            const role = participantData.is_creator ? 'Room Creator' : 'Participant';
            roleElement.textContent = role;
        }
        
        // Show participant info container
        const infoContainer = slotElement.querySelector('.participant-info, [data-participant-info]');
        if (infoContainer) {
            infoContainer.style.display = '';
        }
    }

    /**
     * Clears participant information display
     */
    clearParticipantInfo(slotElement) {
        // Clear participant name
        const nameElement = slotElement.querySelector('.participant-name, [data-participant-name]');
        if (nameElement) {
            nameElement.textContent = '';
        }
        
        // Clear character name
        const characterElement = slotElement.querySelector('.character-name, [data-character-name]');
        if (characterElement) {
            characterElement.textContent = '';
            characterElement.style.display = 'none';
        }
        
        // Clear role
        const roleElement = slotElement.querySelector('.participant-role, [data-participant-role]');
        if (roleElement) {
            roleElement.textContent = '';
        }
        
        // Hide participant info container
        const infoContainer = slotElement.querySelector('.participant-info, [data-participant-info]');
        if (infoContainer) {
            infoContainer.style.display = 'none';
        }
    }

    /**
     * Sets up video stream display in a slot
     */
    setupVideoStream(slotElement, stream, participantData) {
        const videoElement = slotElement.querySelector('video, [data-video]');
        if (!videoElement) {
            console.warn(`🎬 Video element not found in slot for ${participantData.name}`);
            return;
        }
        
        console.log(`🎬 Setting up video stream for ${participantData.name}`);
        
        // Set video stream
        videoElement.srcObject = stream;
        videoElement.autoplay = true;
        videoElement.playsInline = true;
        videoElement.muted = participantData.peerId === this.roomWebRTC.currentPeerId; // Mute own video
        
        // Show video element
        videoElement.style.display = '';
        
        // Hide placeholder/avatar if present
        const placeholder = slotElement.querySelector('.video-placeholder, [data-video-placeholder]');
        if (placeholder) {
            placeholder.style.display = 'none';
        }
        
        // Handle video load events
        videoElement.addEventListener('loadedmetadata', () => {
            console.log(`🎬 Video metadata loaded for ${participantData.name}`);
        });
        
        videoElement.addEventListener('error', (e) => {
            console.error(`🎬 Video error for ${participantData.name}:`, e);
            this.showVideoError(slotElement, 'Video stream error');
        });
    }

    /**
     * Clears video stream from a slot
     */
    clearVideoStream(slotElement) {
        const videoElement = slotElement.querySelector('video, [data-video]');
        if (videoElement) {
            videoElement.srcObject = null;
            videoElement.style.display = 'none';
        }
        
        // Show placeholder/avatar if present
        const placeholder = slotElement.querySelector('.video-placeholder, [data-video-placeholder]');
        if (placeholder) {
            placeholder.style.display = '';
        }
        
        // Clear any error states
        this.clearSlotError(slotElement);
    }

    /**
     * Updates the slot counter display
     */
    updateSlotCounter() {
        const occupiedSlots = this.roomWebRTC.slotOccupants.size;
        const totalSlots = this.maxSlots;
        
        // Update counter elements
        const counterElements = document.querySelectorAll('.slot-counter, [data-slot-counter]');
        counterElements.forEach(element => {
            element.textContent = `${occupiedSlots} of ${totalSlots} slots filled`;
        });
        
        // Update individual counter parts
        const occupiedElements = document.querySelectorAll('.slots-occupied, [data-slots-occupied]');
        occupiedElements.forEach(element => {
            element.textContent = occupiedSlots.toString();
        });
        
        const totalElements = document.querySelectorAll('.slots-total, [data-slots-total]');
        totalElements.forEach(element => {
            element.textContent = totalSlots.toString();
        });
        
        console.log(`🎬 Slot counter updated: ${occupiedSlots}/${totalSlots}`);
    }

    /**
     * Shows an error message for a specific slot
     */
    showSlotError(slotId, message) {
        const slotElement = this.slotElements.get(slotId);
        if (!slotElement) return;
        
        this.showErrorInSlot(slotElement, message);
    }

    /**
     * Shows an error message within a slot element
     */
    showErrorInSlot(slotElement, message) {
        // Find or create error element
        let errorElement = slotElement.querySelector('.slot-error, [data-slot-error]');
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.className = 'slot-error absolute inset-0 bg-red-500/20 border border-red-500 rounded-lg flex items-center justify-center';
            errorElement.setAttribute('data-slot-error', '');
            slotElement.appendChild(errorElement);
        }
        
        errorElement.innerHTML = `
            <div class="text-center p-4">
                <div class="text-red-400 text-sm font-medium">${message}</div>
                <button class="mt-2 px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-xs rounded transition-colors" 
                        onclick="this.closest('[data-slot-error]').style.display='none'">
                    Dismiss
                </button>
            </div>
        `;
        errorElement.style.display = '';
    }

    /**
     * Shows a video error within a slot element
     */
    showVideoError(slotElement, message) {
        this.showErrorInSlot(slotElement, message);
    }

    /**
     * Clears error state from a slot
     */
    clearSlotError(slotElement) {
        const errorElement = slotElement.querySelector('.slot-error, [data-slot-error]');
        if (errorElement) {
            errorElement.style.display = 'none';
        }
    }

    /**
     * Highlights the current user's slot
     */
    highlightCurrentUserSlot(slotId) {
        // Remove highlight from all slots
        this.slotElements.forEach((element) => {
            element.classList.remove('slot-current-user');
        });
        
        // Add highlight to current slot
        const slotElement = this.slotElements.get(slotId);
        if (slotElement) {
            slotElement.classList.add('slot-current-user');
        }
    }

    /**
     * Removes highlight from current user's slot
     */
    removeCurrentUserHighlight() {
        this.slotElements.forEach((element) => {
            element.classList.remove('slot-current-user');
        });
    }

    /**
     * Gets the number of available slots
     */
    getAvailableSlotCount() {
        return this.maxSlots - this.roomWebRTC.slotOccupants.size;
    }

    /**
     * Gets the total number of slots
     */
    getTotalSlotCount() {
        return this.maxSlots;
    }

    /**
     * Gets the number of occupied slots
     */
    getOccupiedSlotCount() {
        return this.roomWebRTC.slotOccupants.size;
    }

    /**
     * Updates the maximum number of slots
     */
    updateMaxSlots(maxSlots) {
        this.maxSlots = maxSlots;
        this.updateSlotCounter();
        console.log(`🎬 Max slots updated to: ${maxSlots}`);
    }

    /**
     * Cleans up slot manager resources
     */
    destroy() {
        // Clear all slot elements
        this.slotElements.forEach((element) => {
            this.clearVideoStream(element);
            this.clearParticipantInfo(element);
            this.clearSlotError(element);
        });
        
        this.slotElements.clear();
        console.log('🎬 SlotManager destroyed');
    }
}
