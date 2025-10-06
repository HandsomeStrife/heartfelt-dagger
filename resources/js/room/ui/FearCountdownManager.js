/**
 * FearCountdownManager - Manages fear tracker and countdown timers in rooms
 * 
 * Handles real-time updates and UI synchronization for fear levels and countdown trackers
 * across all participants in a room session. Integrates with Ably for live updates.
 * 
 * Features:
 * - Real-time fear level updates
 * - Dynamic countdown tracker management
 * - UI updates for both GM and player views
 * - Integration with Ably messaging system
 * - Local state management with server synchronization
 */

export class FearCountdownManager {
    constructor(roomWebRTC) {
        this.roomWebRTC = roomWebRTC;
        this.roomData = roomWebRTC.roomData;
        
        // Current state
        this.fearLevel = 0;
        this.countdownTrackers = new Map(); // Map of id -> tracker data
        this.sourceType = 'room'; // 'campaign' or 'room'
        this.sourceId = null;
        this.gmJoined = false; // Track if GM is currently joined to a slot
        this.currentUserId = window.currentUserId;
        
        // UI elements cache
        this.fearDisplayElements = new Map();
        this.countdownDisplayElements = new Map();
        this.gameStateOverlays = [];
        
        // console.log('🎭 FearCountdownManager initialized for room:', this.roomData.name);
        
        this.init();
    }

    async init() {
        try {
            // Initialize with current game state from server
            await this.loadInitialGameState();
            
            // Set up UI elements
            this.setupUIElements();
            
            // Set up Ably message handlers
            this.setupAblyHandlers();
            
            // Initial UI update
            this.updateAllUI();
            
            // console.log('🎭 FearCountdownManager ready');
        } catch (error) {
            console.error('🎭 ❌ Failed to initialize FearCountdownManager:', error);
        }
    }

    /**
     * Load initial game state from the server
     */
    async loadInitialGameState() {
        // For now, use data passed from the server in roomData
        // In future implementation, this would fetch from API
        if (this.roomData.game_state) {
            this.updateLocalState(this.roomData.game_state);
        } else {
            // Default empty state
            this.fearLevel = 0;
            this.countdownTrackers.clear();
            this.sourceType = this.roomData.campaign_id ? 'campaign' : 'room';
            this.sourceId = this.roomData.campaign_id || this.roomData.id;
        }
    }

    /**
     * Set up UI elements for fear and countdown display
     */
    setupUIElements() {
        // Initial scan for UI elements
        this.refreshUIElementsCache();
        
        // Set up MutationObserver to detect new overlays being added to DOM
        this.setupOverlayObserver();
        
        // Set up slot monitoring to track GM presence
        this.setupSlotMonitoring();
        
        // Schedule a delayed refresh to catch any late-loading elements
        setTimeout(() => {
            console.log('🎭 Performing delayed UI elements refresh...');
            this.refreshUIElementsCache();
            this.updateGameStateOverlayVisibility();
        }, 500);
    }

    /**
     * Refresh the cache of UI elements
     */
    refreshUIElementsCache() {
        // Clear existing caches
        this.fearDisplayElements.clear();
        this.countdownDisplayElements.clear();
        
        // Find all fear display elements
        const fearElements = document.querySelectorAll('[data-fear-display]');
        fearElements.forEach(element => {
            const type = element.dataset.fearDisplay;
            if (!this.fearDisplayElements.has(type)) {
                this.fearDisplayElements.set(type, []);
            }
            this.fearDisplayElements.get(type).push(element);
        });

        // Find all countdown display elements
        const countdownElements = document.querySelectorAll('[data-countdown-display]');
        countdownElements.forEach(element => {
            const type = element.dataset.countdownDisplay;
            if (!this.countdownDisplayElements.has(type)) {
                this.countdownDisplayElements.set(type, []);
            }
            this.countdownDisplayElements.get(type).push(element);
        });

        // Find all game state overlay elements
        const newOverlays = Array.from(document.querySelectorAll('[data-game-state-overlay]'));
        
        // Only update if count changed
        if (newOverlays.length !== this.gameStateOverlays.length) {
            console.log(`🎭 Overlay cache updated: ${this.gameStateOverlays.length} → ${newOverlays.length} overlays`);
            this.gameStateOverlays = newOverlays;
        }

        console.log(`🎭 UI cache refreshed: ${fearElements.length} fear, ${countdownElements.length} countdown, ${this.gameStateOverlays.length} overlays`);
    }

    /**
     * Set up MutationObserver to detect when new overlays are added to DOM
     */
    setupOverlayObserver() {
        // Create observer to watch for DOM changes
        this.overlayObserver = new MutationObserver((mutations) => {
            let shouldRefresh = false;
            
            // Check if any mutations added nodes with game-state-overlay attribute
            for (const mutation of mutations) {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach(node => {
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            // Check if the node itself is an overlay
                            if (node.hasAttribute && node.hasAttribute('data-game-state-overlay')) {
                                shouldRefresh = true;
                            }
                            // Check if any children are overlays
                            if (node.querySelectorAll) {
                                const overlaysInNode = node.querySelectorAll('[data-game-state-overlay]');
                                if (overlaysInNode.length > 0) {
                                    shouldRefresh = true;
                                }
                            }
                        }
                    });
                }
            }
            
            if (shouldRefresh) {
                console.log('🎭 DOM mutation detected - refreshing overlay cache');
                this.refreshUIElementsCache();
                this.updateGameStateOverlayVisibility();
            }
        });
        
        // Start observing the document body for changes
        this.overlayObserver.observe(document.body, {
            childList: true,
            subtree: true
        });
        
        console.log('🎭 MutationObserver set up to detect new game state overlays');
    }

    /**
     * Set up Ably message handlers for real-time updates
     */
    setupAblyHandlers() {
        // Set up Livewire event listeners
        this.setupLivewireHandlers();

        if (!this.roomWebRTC.ablyManager) {
            console.warn('🎭 No Ably manager available for real-time updates');
            return;
        }

        // For now, we'll just set up the event listeners
        // Ably integration will be handled in the main message handler
        // console.log('🎭 Ably handlers setup completed (using Livewire events for now)');
    }

    /**
     * Set up Livewire event handlers for GM controls
     */
    setupLivewireHandlers() {
        // Listen for fear level updates from Livewire
        window.addEventListener('fear-level-updated', (event) => {
            //console.log('🎭 Livewire fear level updated:', event.detail);
            this.handleFearUpdate(event.detail);
        });

        // Listen for countdown tracker updates from Livewire
        window.addEventListener('countdown-tracker-updated', (event) => {
            // console.log('🎭 Livewire countdown tracker updated:', event.detail);
            this.handleCountdownUpdate(event.detail);
        });

        // Listen for countdown tracker deletion from Livewire
        window.addEventListener('countdown-tracker-deleted', (event) => {
            //console.log('🎭 Livewire countdown tracker deleted:', event.detail);
            this.handleCountdownDeletion(event.detail);
        });

        // Listen for Ably message dispatch requests from Livewire
        window.addEventListener('send-ably-message', (event) => {
            //console.log('🎭 Sending Ably message:', event.detail);
            this.sendAblyMessage(event.detail);
        });
    }

    /**
     * Set up monitoring for slot occupancy to track GM presence
     */
    setupSlotMonitoring() {
        // Check initial state
        this.checkGmPresence();
        
        // Monitor slot changes via RoomWebRTC events
        // We'll tap into the existing slot management system
        if (this.roomWebRTC.slotOccupants) {
            // Store original methods for cleanup (prevent memory leak)
            this.originalSlotOccupantsSet = this.roomWebRTC.slotOccupants.set.bind(this.roomWebRTC.slotOccupants);
            this.originalSlotOccupantsDelete = this.roomWebRTC.slotOccupants.delete.bind(this.roomWebRTC.slotOccupants);
            
            // Override methods to monitor slot changes
            this.roomWebRTC.slotOccupants.set = (...args) => {
                const result = this.originalSlotOccupantsSet(...args);
                // Use setTimeout to ensure the change is processed before checking GM presence
                setTimeout(() => this.checkGmPresence(), 100);
                return result;
            };
            
            this.roomWebRTC.slotOccupants.delete = (...args) => {
                const result = this.originalSlotOccupantsDelete(...args);
                // Use setTimeout to ensure the change is processed before checking GM presence
                setTimeout(() => this.checkGmPresence(), 100);
                return result;
            };
        }

        // Set up one-time delayed check as fallback for initialization
        setTimeout(() => {
            this.checkGmPresence();
        }, 1000);
    }


    /**
     * Check if the GM is currently present in any slot
     */
    checkGmPresence() {
        const wasGmJoined = this.gmJoined;
        
        const isCurrentUserGm = this.roomData.creator_id === this.currentUserId;
        const isCurrentUserJoined = this.roomWebRTC.isJoined && this.roomWebRTC.currentSlotId;
        
        // Check if any slot contains the GM user
        let gmInAnySlot = false;
        for (const [slotId, occupant] of this.roomWebRTC.slotOccupants) {
            if (occupant.participantData && occupant.participantData.user_id === this.roomData.creator_id) {
                gmInAnySlot = true;
                break;
            }
        }
        
        // GM is considered joined if they are the current user and joined, OR if GM user is in any slot
        this.gmJoined = (isCurrentUserGm && isCurrentUserJoined) || gmInAnySlot;
        
        // If GM presence changed, update overlay visibility and notify others
        if (this.gmJoined !== wasGmJoined) {
            // console.log(`🎭 GM presence changed: ${wasGmJoined} → ${this.gmJoined}`);
            this.updateGameStateOverlayVisibility();
            
            // Send Ably message to notify other participants
            this.sendAblyMessage({
                type: 'gm-presence-changed',
                data: {
                    gm_present: this.gmJoined,
                    gm_slot_id: this.findGmSlotId()
                }
            });
        }
    }

    /**
     * Show or hide game state overlays based on GM presence - only on GM's slot for participants, all slots for viewers
     */
    updateGameStateOverlayVisibility() {
        // console.log(`🎭 Updating game state overlay visibility. GM joined: ${this.gmJoined}`);
        
        // Find which slot the GM is in
        const gmSlotId = this.findGmSlotId();
        if (gmSlotId) {
            // console.log(`🎭 GM found in slot ${gmSlotId}`);
        }
        
        // Check if we're in viewer mode
        const isViewerMode = this.roomWebRTC.roomData.viewer_mode;
        
        // Update visibility for all overlays
        this.gameStateOverlays.forEach((overlay, index) => {
            const slotContainer = overlay.closest('[data-slot-id]');
            const slotId = slotContainer ? parseInt(slotContainer.dataset.slotId) : null;
            
            // Show only on GM's slot when GM is present (same for participants and viewers)
            if (this.gmJoined && slotId === gmSlotId) {
                overlay.classList.remove('hidden');
                console.log(`🎭 Showing game state overlay on GM slot ${slotId} ${isViewerMode ? '(viewer mode)' : '(participant mode)'}`);
            } else {
                overlay.classList.add('hidden');
                //console.log(`🎭 Hiding game state overlay on slot ${slotId} (${isViewerMode ? 'viewer mode - ' : ''}not GM slot or GM not present)`);
            }
        });
    }

    /**
     * Handle fear level update
     */
    handleFearUpdate(data) {
        //console.log('🎭 Processing fear update:', data);
        
        // Handle Livewire event data format (array wrapper)
        let fearData = data;
        if (Array.isArray(data) && data.length > 0) {
            fearData = data[0];
        }
        
        const fearLevel = fearData.fear_level || 0;
        const sourceType = fearData.source_type || (this.roomData.campaign_id ? 'campaign' : 'room');
        const sourceId = fearData.source_id || (this.roomData.campaign_id || this.roomData.id);
        
        this.fearLevel = fearLevel;
        this.sourceType = sourceType;
        this.sourceId = sourceId;
        
        this.updateFearUI();
    }

    /**
     * Handle countdown tracker update
     */
    handleCountdownUpdate(data) {
        // console.log('🎭 Processing countdown update:', data);
        
        // Handle Livewire event data format (array wrapper)
        let trackerData = data;
        if (Array.isArray(data) && data.length > 0) {
            trackerData = data[0];
        }
        
        // Extract tracker from the data
        const tracker = trackerData.tracker;
        
        if (!tracker || !tracker.id) {
            console.warn('🎭 Invalid tracker data received:', data);
            return;
        }
        
        // Check if this is an update to an existing tracker to determine rotation direction
        const existingTracker = this.countdownTrackers.get(tracker.id);
        let rotationDirection = null;
        
        if (existingTracker && existingTracker.value !== tracker.value) {
            rotationDirection = tracker.value > existingTracker.value ? 'right' : 'left';
            // console.log(`🎭 Tracker ${tracker.id} value changed from ${existingTracker.value} to ${tracker.value} - rotating ${rotationDirection}`);
        }
        
        this.countdownTrackers.set(tracker.id, tracker);
        this.updateCountdownUI(rotationDirection, tracker.id);
    }

    /**
     * Handle countdown tracker deletion
     */
    handleCountdownDeletion(data) {
        // console.log('🎭 Processing countdown deletion:', data);
        
        // Handle Livewire event data format (array wrapper)
        let deletionData = data;
        if (Array.isArray(data) && data.length > 0) {
            deletionData = data[0];
        }
        
        const trackerId = deletionData.tracker_id;
        
        if (!trackerId) {
            console.warn('🎭 Invalid tracker deletion data received:', data);
            return;
        }
        
        this.countdownTrackers.delete(trackerId);
        this.updateCountdownUI();
    }

    /**
     * Handle GM presence change from Ably
     */
    handleGmPresenceChanged(data) {
        // console.log('🎭 Processing GM presence change:', data);
        
        const { gm_present, gm_slot_id } = data;
        this.gmJoined = gm_present;
        
        // Update overlay visibility without triggering another Ably message
        this.updateGameStateOverlayVisibility();
    }

    /**
     * Find which slot the GM is currently in
     */
    findGmSlotId() {
        for (const [slotId, occupant] of this.roomWebRTC.slotOccupants) {
            if (occupant.participantData && occupant.participantData.user_id === this.roomData.creator_id) {
                return slotId;
            }
        }
        return null;
    }

    /**
     * Update local state with new game state data
     */
    updateLocalState(gameState) {
        this.fearLevel = gameState.fear_tracker?.fear_level || 0;
        this.sourceType = gameState.source_type || 'room';
        this.sourceId = gameState.source_id || this.roomData.id;
        
        // Update countdown trackers
        this.countdownTrackers.clear();
        if (gameState.countdown_trackers) {
            gameState.countdown_trackers.forEach(tracker => {
                this.countdownTrackers.set(tracker.id, tracker);
            });
        }
    }

    /**
     * Update all UI elements
     */
    updateAllUI() {
        this.updateFearUI();
        this.updateCountdownUI();
        this.updateGameStateOverlayVisibility();
    }

    /**
     * Update fear level display in UI
     */
    updateFearUI() {
        const currentFearLevel = this.fearLevel || 0;
        
        // Update fear level displays
        const fearDisplays = this.fearDisplayElements.get('level') || [];
        fearDisplays.forEach(element => {
            if (element) {
                element.textContent = currentFearLevel.toString();
            }
        });

        // Update fear indicators/badges
        const fearIndicators = this.fearDisplayElements.get('indicator') || [];
        fearIndicators.forEach(element => {
            if (element) {
                element.textContent = currentFearLevel.toString();
                
                // Add visual styling based on fear level
                element.className = element.className.replace(/fear-level-\d+/g, '');
                element.classList.add(`fear-level-${Math.min(currentFearLevel, 10)}`);
            }
        });

        // console.log(`🎭 Updated fear UI to level ${currentFearLevel}`);
    }

    /**
     * Update countdown trackers display in UI
     */
    updateCountdownUI(rotationDirection = null, trackerId = null) {
        const countdownContainers = this.countdownDisplayElements.get('container') || [];
        
        countdownContainers.forEach(container => {
            // If we have a specific tracker to update, try to update it in place
            if (trackerId && rotationDirection) {
                const existingElement = container.querySelector(`[data-tracker-id="${trackerId}"]`);
                if (existingElement) {
                    // Update the value and apply rotation
                    const tracker = this.countdownTrackers.get(trackerId);
                    if (tracker) {
                        const valueElement = existingElement.querySelector('.countdown-value');
                        if (valueElement) {
                            valueElement.textContent = tracker.value;
                        }
                        this.rotateHexagon(existingElement, rotationDirection);
                        return; // Exit early since we updated in place
                    }
                }
            }
            
            // Fallback: Clear and rebuild all trackers
            container.innerHTML = '';
            
            // Add each countdown tracker
            this.countdownTrackers.forEach(tracker => {
                const trackerElement = this.createCountdownTrackerElement(tracker);
                container.appendChild(trackerElement);
                
                // Apply rotation if this is the tracker that changed
                if (trackerId === tracker.id && rotationDirection) {
                    // Apply rotation after a small delay to ensure element is in DOM
                    setTimeout(() => this.rotateHexagon(trackerElement, rotationDirection), 10);
                }
            });
        });

        // console.log(`🎭 Updated countdown UI with ${this.countdownTrackers.size} trackers`);
    }

    /**
     * Rotate hexagon background by 45 degrees (1/8th rotation)
     */
    rotateHexagon(trackerElement, direction) {
        const hexagonBackground = trackerElement.querySelector('.hexagon-background');
        if (!hexagonBackground) return;
        
        // Get current rotation or default to 0
        const currentRotation = parseInt(trackerElement.dataset.currentRotation || '0');
        
        // Calculate new rotation (45 degrees = 1/8th of 360)
        // This means after 8 rotations, the hexagon returns to original position
        const rotationAmount = direction === 'right' ? 45 : -45;
        const newRotation = currentRotation + rotationAmount;
        
        // Update the rotation
        trackerElement.dataset.currentRotation = newRotation.toString();
        hexagonBackground.style.transform = `rotate(${newRotation}deg)`;
        
        // console.log(`🎭 Rotated hexagon ${direction} to ${newRotation}°`);
    }

    /**
     * Create a countdown tracker display element
     */
    createCountdownTrackerElement(tracker) {
        const element = document.createElement('div');
        element.className = 'countdown-tracker relative w-12 h-12 flex items-center justify-center';
        element.dataset.trackerId = tracker.id;
        element.dataset.currentRotation = '0'; // Track current rotation
        
        element.innerHTML = `
            <div class="hexagon-background absolute inset-0 bg-black border-2 border-gray-700 transition-transform duration-300 ease-in-out" style="
                clip-path: polygon(30% 0%, 70% 0%, 100% 30%, 100% 70%, 70% 100%, 30% 100%, 0% 70%, 0% 30%);
                transform: rotate(0deg);
            "></div>
            <div class="countdown-value relative text-white font-bold text-lg z-10">${tracker.value}</div>
        `;
        
        return element;
    }

    /**
     * Send an Ably message for real-time synchronization
     */
    sendAblyMessage(eventData) {
        try {
            // Handle Livewire event data format (array wrapper)
            let messageData = eventData;
            if (Array.isArray(eventData) && eventData.length > 0) {
                messageData = eventData[0];
            }
            
            const { type, data } = messageData;
            
            if (!this.roomWebRTC.ablyManager) {
                console.warn('🎭 No Ably manager available for message:', type);
                return;
            }

            // console.log(`🎭 Publishing Ably message: ${type}`, data);
            
            // Send the message to all room participants
            this.roomWebRTC.ablyManager.publishToAbly(type, data);
            
        } catch (error) {
            console.error('🎭 ❌ Failed to send Ably message:', error);
        }
    }

    /**
     * Send fear level update to server (legacy method - kept for compatibility)
     */
    async updateFearLevel(newLevel) {
        try {
            // console.log(`🎭 Updating fear level to ${newLevel}`);
            
            // Optimistic update
            this.fearLevel = newLevel;
            this.updateFearUI();
            
            // Send to server via Ably
            this.sendAblyMessage({
                type: 'fear-updated',
                data: {
                    fear_level: newLevel,
                    source_type: this.sourceType,
                    source_id: this.sourceId
                }
            });
            
        } catch (error) {
            console.error('🎭 ❌ Failed to update fear level:', error);
            // Revert optimistic update on error
            await this.loadInitialGameState();
            this.updateFearUI();
        }
    }

    /**
     * Send countdown tracker update to server (legacy method - kept for compatibility)
     */
    async updateCountdownTracker(trackerId, name, value) {
        try {
            // console.log(`🎭 Updating countdown tracker ${trackerId}: ${name} = ${value}`);
            
            // Optimistic update
            const tracker = { id: trackerId, name, value, updated_at: new Date().toISOString() };
            this.countdownTrackers.set(trackerId, tracker);
            this.updateCountdownUI();
            
            // Send to server via Ably
            this.sendAblyMessage({
                type: 'countdown-updated',
                data: {
                    tracker: tracker,
                    action: 'updated'
                }
            });
            
        } catch (error) {
            console.error('🎭 ❌ Failed to update countdown tracker:', error);
            // Revert optimistic update on error
            await this.loadInitialGameState();
            this.updateCountdownUI();
        }
    }

    /**
     * Send countdown tracker creation request to server (legacy method - kept for compatibility)
     */
    async createCountdownTracker(name, value) {
        try {
            // console.log(`🎭 Creating countdown tracker: ${name} = ${value}`);
            
            // Create the tracker object
            const tracker = { 
                id: `countdown_${Date.now()}`, 
                name, 
                value, 
                updated_at: new Date().toISOString() 
            };
            
            // Send to server via Ably
            this.sendAblyMessage({
                type: 'countdown-updated',
                data: {
                    tracker: tracker,
                    action: 'created'
                }
            });
            
        } catch (error) {
            console.error('🎭 ❌ Failed to create countdown tracker:', error);
        }
    }

    /**
     * Send countdown tracker deletion request to server (legacy method - kept for compatibility)
     */
    async deleteCountdownTracker(trackerId) {
        try {
            // console.log(`🎭 Deleting countdown tracker ${trackerId}`);
            
            // Optimistic update
            this.countdownTrackers.delete(trackerId);
            this.updateCountdownUI();
            
            // Send to server via Ably
            this.sendAblyMessage({
                type: 'countdown-deleted',
                data: {
                    tracker_id: trackerId
                }
            });
            
        } catch (error) {
            console.error('🎭 ❌ Failed to delete countdown tracker:', error);
            // Revert optimistic update on error
            await this.loadInitialGameState();
            this.updateCountdownUI();
        }
    }

    /**
     * Get current fear level
     */
    getFearLevel() {
        return this.fearLevel;
    }

    /**
     * Get all countdown trackers
     */
    getCountdownTrackers() {
        return Array.from(this.countdownTrackers.values());
    }

    /**
     * Get a specific countdown tracker
     */
    getCountdownTracker(id) {
        return this.countdownTrackers.get(id);
    }

    /**
     * Check if there are any active countdown trackers
     */
    hasCountdownTrackers() {
        return this.countdownTrackers.size > 0;
    }

    /**
     * Get the current game state
     */
    getGameState() {
        return {
            fear_level: this.fearLevel,
            countdown_trackers: this.getCountdownTrackers(),
            source_type: this.sourceType,
            source_id: this.sourceId
        };
    }

    /**
     * Debug method - can be called from browser console
     */
    debugGmPresence() {
        console.log('🎭 === DEBUG GM PRESENCE ===');
        console.log('Current user ID:', this.currentUserId);
        console.log('Room creator ID:', this.roomData.creator_id);
        console.log('Is current user GM:', this.roomData.creator_id === this.currentUserId);
        console.log('RoomWebRTC isJoined:', this.roomWebRTC.isJoined);
        console.log('RoomWebRTC currentSlotId:', this.roomWebRTC.currentSlotId);
        console.log('SlotOccupants Map:', this.roomWebRTC.slotOccupants);
        console.log('GM joined status:', this.gmJoined);
        console.log('Game state overlays found:', this.gameStateOverlays.length);
        console.log('Overlays:', this.gameStateOverlays);
        
        // Force a check
        this.checkGmPresence();
        
        return {
            currentUserId: this.currentUserId,
            roomCreatorId: this.roomData.creator_id,
            isGm: this.roomData.creator_id === this.currentUserId,
            isJoined: this.roomWebRTC.isJoined,
            currentSlotId: this.roomWebRTC.currentSlotId,
            gmJoined: this.gmJoined,
            overlaysCount: this.gameStateOverlays.length
        };
    }

    /**
     * Force show overlays (debug method)
     */
    forceShowOverlays() {
        console.log('🎭 Force showing overlays...');
        this.gmJoined = true;
        this.updateGameStateOverlayVisibility();
    }

    /**
     * Force hide overlays (debug method)
     */
    forceHideOverlays() {
        console.log('🎭 Force hiding overlays...');
        this.gmJoined = false;
        this.updateGameStateOverlayVisibility();
    }

    /**
     * Cleanup method - disconnect observers and restore original methods
     */
    cleanup() {
        console.log('🎭 Cleaning up FearCountdownManager...');
        
        // Disconnect MutationObserver
        if (this.overlayObserver) {
            this.overlayObserver.disconnect();
            this.overlayObserver = null;
        }
        
        // Restore original Map methods to prevent memory leaks
        if (this.roomWebRTC.slotOccupants && this.originalSlotOccupantsSet && this.originalSlotOccupantsDelete) {
            this.roomWebRTC.slotOccupants.set = this.originalSlotOccupantsSet;
            this.roomWebRTC.slotOccupants.delete = this.originalSlotOccupantsDelete;
            console.log('🎭 Restored original Map methods');
        }
        
        // Clear caches
        this.fearDisplayElements.clear();
        this.countdownDisplayElements.clear();
        this.gameStateOverlays = [];
    }
}
