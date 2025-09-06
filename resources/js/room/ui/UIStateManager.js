/**
 * UIStateManager - Manages UI enable/disable states
 * 
 * Controls UI element states based on consent, loading states, feature availability,
 * and system status. Provides centralized state management for all interactive elements.
 */
export class UIStateManager {
    constructor(roomWebRTC) {
        this.roomWebRTC = roomWebRTC;
        this.states = new Map(); // Map of stateId -> state object
        this.elements = new Map(); // Map of selector -> element references
        this.watchers = new Map(); // Map of stateId -> watcher functions
        
        // State categories
        this.stateCategories = {
            consent: new Set(),
            loading: new Set(),
            connection: new Set(),
            feature: new Set(),
            recording: new Set(),
            speech: new Set()
        };
        
        // Default states
        this.defaultStates = {
            // Consent states
            'consent.stt': { enabled: false, reason: 'STT consent required' },
            'consent.recording': { enabled: false, reason: 'Recording consent required' },
            
            // Loading states
            'loading.initialization': { enabled: false, reason: 'System initializing' },
            'loading.connection': { enabled: false, reason: 'Connecting to room' },
            'loading.media': { enabled: false, reason: 'Setting up media' },
            
            // Connection states
            'connection.ably': { enabled: false, reason: 'Not connected to Ably' },
            'connection.webrtc': { enabled: false, reason: 'WebRTC not connected' },
            
            // Feature states
            'feature.stt': { enabled: false, reason: 'STT not available' },
            'feature.recording': { enabled: false, reason: 'Recording not available' },
            'feature.video': { enabled: false, reason: 'Video not available' },
            'feature.audio': { enabled: false, reason: 'Audio not available' },
            
            // Recording states
            'recording.active': { enabled: false, reason: 'Not recording' },
            'recording.processing': { enabled: false, reason: 'Not processing' },
            
            // Speech states
            'speech.listening': { enabled: false, reason: 'Not listening' },
            'speech.processing': { enabled: false, reason: 'Not processing speech' }
        };
        
        this.initializeStates();
        this.setupElementSelectors();
    }

    /**
     * Initializes default states
     */
    initializeStates() {
        console.log('🎛️ Initializing UI state manager...');
        
        // Set default states
        Object.entries(this.defaultStates).forEach(([stateId, state]) => {
            this.states.set(stateId, { ...state });
            
            // Categorize states
            const category = stateId.split('.')[0];
            if (this.stateCategories[category]) {
                this.stateCategories[category].add(stateId);
            }
        });
        
        console.log('🎛️ ✅ UI states initialized:', this.states.size);
    }

    /**
     * Sets up element selectors for UI management
     */
    setupElementSelectors() {
        // Define element selectors and their associated states
        this.elementSelectors = {
            // Join/Leave buttons
            '.join-room-btn, [data-join-room]': ['loading.initialization', 'loading.connection', 'consent.stt', 'consent.recording'],
            '.leave-room-btn, [data-leave-room]': ['connection.ably'],
            
            // Recording controls
            '.start-recording-btn, [data-start-recording]': ['consent.recording', 'feature.recording', 'connection.webrtc'],
            '.stop-recording-btn, [data-stop-recording]': ['recording.active'],
            '.pause-recording-btn, [data-pause-recording]': ['recording.active'],
            '.resume-recording-btn, [data-resume-recording]': ['recording.active'],
            
            // Speech controls
            '.start-stt-btn, [data-start-stt]': ['consent.stt', 'feature.stt', 'connection.ably'],
            '.stop-stt-btn, [data-stop-stt]': ['speech.listening'],
            '.transcript-btn, [data-transcript]': ['feature.stt'],
            
            // Media controls
            '.toggle-video-btn, [data-toggle-video]': ['feature.video', 'connection.webrtc'],
            '.toggle-audio-btn, [data-toggle-audio]': ['feature.audio', 'connection.webrtc'],
            
            // Status indicators
            '.connection-status, [data-connection-status]': ['connection.ably', 'connection.webrtc'],
            '.recording-status, [data-recording-status]': ['recording.active', 'recording.processing'],
            '.speech-status, [data-speech-status]': ['speech.listening', 'speech.processing'],
            
            // Interactive elements
            '.room-controls, [data-room-controls]': ['loading.initialization'],
            '.media-controls, [data-media-controls]': ['loading.media', 'connection.webrtc'],
            '.consent-required, [data-consent-required]': ['consent.stt', 'consent.recording']
        };
        
        console.log('🎛️ Element selectors configured:', Object.keys(this.elementSelectors).length);
    }

    /**
     * Sets a state
     */
    setState(stateId, enabled, reason = null) {
        const previousState = this.states.get(stateId);
        const newState = {
            enabled: enabled,
            reason: reason || (enabled ? 'Enabled' : 'Disabled'),
            timestamp: Date.now()
        };
        
        this.states.set(stateId, newState);
        
        // Log state change
        if (!previousState || previousState.enabled !== enabled) {
            console.log(`🎛️ State changed: ${stateId} = ${enabled ? 'enabled' : 'disabled'} (${newState.reason})`);
        }
        
        // Update UI elements
        this.updateUIElements();
        
        // Notify watchers
        this.notifyWatchers(stateId, newState, previousState);
    }

    /**
     * Gets a state
     */
    getState(stateId) {
        return this.states.get(stateId) || { enabled: false, reason: 'State not found' };
    }

    /**
     * Checks if a state is enabled
     */
    isEnabled(stateId) {
        const state = this.getState(stateId);
        return state.enabled;
    }

    /**
     * Sets multiple states at once
     */
    setStates(stateUpdates) {
        Object.entries(stateUpdates).forEach(([stateId, update]) => {
            if (typeof update === 'boolean') {
                this.setState(stateId, update);
            } else {
                this.setState(stateId, update.enabled, update.reason);
            }
        });
    }

    /**
     * Updates UI elements based on current states
     */
    updateUIElements() {
        Object.entries(this.elementSelectors).forEach(([selector, requiredStates]) => {
            const elements = document.querySelectorAll(selector);
            
            elements.forEach(element => {
                const shouldEnable = this.shouldEnableElement(requiredStates);
                const disabledReason = this.getDisabledReason(requiredStates);
                
                this.updateElement(element, shouldEnable, disabledReason);
            });
        });
    }

    /**
     * Determines if an element should be enabled based on required states
     */
    shouldEnableElement(requiredStates) {
        return requiredStates.every(stateId => this.isEnabled(stateId));
    }

    /**
     * Gets the reason why an element is disabled
     */
    getDisabledReason(requiredStates) {
        const disabledStates = requiredStates.filter(stateId => !this.isEnabled(stateId));
        
        if (disabledStates.length === 0) {
            return null;
        }
        
        // Return the first disabled state's reason
        const firstDisabledState = this.getState(disabledStates[0]);
        return firstDisabledState.reason;
    }

    /**
     * Updates a single element's state
     */
    updateElement(element, shouldEnable, disabledReason) {
        // Update disabled state
        if (element.tagName === 'BUTTON' || element.tagName === 'INPUT') {
            element.disabled = !shouldEnable;
        } else {
            element.classList.toggle('disabled', !shouldEnable);
            element.setAttribute('aria-disabled', !shouldEnable);
        }
        
        // Update visual state
        element.classList.toggle('ui-enabled', shouldEnable);
        element.classList.toggle('ui-disabled', !shouldEnable);
        
        // Update tooltip/title with reason
        if (disabledReason) {
            element.setAttribute('title', disabledReason);
            element.setAttribute('data-disabled-reason', disabledReason);
        } else {
            element.removeAttribute('data-disabled-reason');
            // Don't remove title as it might have other content
        }
        
        // Update ARIA attributes
        if (disabledReason) {
            element.setAttribute('aria-label', `${element.textContent || 'Button'} - ${disabledReason}`);
        }
        
        // Add loading indicators for loading states
        if (!shouldEnable && disabledReason && disabledReason.includes('loading')) {
            element.classList.add('loading');
        } else {
            element.classList.remove('loading');
        }
    }

    /**
     * Adds a state watcher
     */
    addWatcher(stateId, callback) {
        if (!this.watchers.has(stateId)) {
            this.watchers.set(stateId, []);
        }
        
        this.watchers.get(stateId).push(callback);
        console.log(`🎛️ Added watcher for state: ${stateId}`);
    }

    /**
     * Removes a state watcher
     */
    removeWatcher(stateId, callback) {
        const watchers = this.watchers.get(stateId);
        if (watchers) {
            const index = watchers.indexOf(callback);
            if (index > -1) {
                watchers.splice(index, 1);
                console.log(`🎛️ Removed watcher for state: ${stateId}`);
            }
        }
    }

    /**
     * Notifies watchers of state changes
     */
    notifyWatchers(stateId, newState, previousState) {
        const watchers = this.watchers.get(stateId);
        if (watchers) {
            watchers.forEach(callback => {
                try {
                    callback(newState, previousState, stateId);
                } catch (error) {
                    console.error(`🎛️ Error in state watcher for ${stateId}:`, error);
                }
            });
        }
    }

    /**
     * Gets states by category
     */
    getStatesByCategory(category) {
        const stateIds = this.stateCategories[category];
        if (!stateIds) {
            return {};
        }
        
        const states = {};
        stateIds.forEach(stateId => {
            states[stateId] = this.getState(stateId);
        });
        
        return states;
    }

    /**
     * Sets category states
     */
    setCategoryStates(category, enabled, reason = null) {
        const stateIds = this.stateCategories[category];
        if (!stateIds) {
            console.warn(`🎛️ Unknown state category: ${category}`);
            return;
        }
        
        const updates = {};
        stateIds.forEach(stateId => {
            updates[stateId] = { enabled, reason: reason || `${category} ${enabled ? 'enabled' : 'disabled'}` };
        });
        
        this.setStates(updates);
    }

    /**
     * Convenience methods for common state updates
     */
    
    // Consent states
    setConsentStates(consents) {
        const updates = {};
        if (consents.stt !== undefined) {
            updates['consent.stt'] = { enabled: consents.stt, reason: consents.stt ? 'STT consent granted' : 'STT consent required' };
        }
        if (consents.recording !== undefined) {
            updates['consent.recording'] = { enabled: consents.recording, reason: consents.recording ? 'Recording consent granted' : 'Recording consent required' };
        }
        this.setStates(updates);
    }
    
    // Loading states
    setLoadingState(type, loading, reason = null) {
        this.setState(`loading.${type}`, !loading, reason || (loading ? `${type} loading` : `${type} loaded`));
    }
    
    // Connection states
    setConnectionState(type, connected, reason = null) {
        this.setState(`connection.${type}`, connected, reason || (connected ? `${type} connected` : `${type} disconnected`));
    }
    
    // Feature states
    setFeatureState(type, available, reason = null) {
        this.setState(`feature.${type}`, available, reason || (available ? `${type} available` : `${type} not available`));
    }
    
    // Recording states
    setRecordingState(type, active, reason = null) {
        this.setState(`recording.${type}`, active, reason || (active ? `Recording ${type}` : `Not ${type}`));
    }
    
    // Speech states
    setSpeechState(type, active, reason = null) {
        this.setState(`speech.${type}`, active, reason || (active ? `Speech ${type}` : `Speech not ${type}`));
    }

    /**
     * Gets overall system status
     */
    getSystemStatus() {
        const categories = {};
        
        Object.keys(this.stateCategories).forEach(category => {
            const states = this.getStatesByCategory(category);
            const enabledCount = Object.values(states).filter(state => state.enabled).length;
            const totalCount = Object.keys(states).length;
            
            categories[category] = {
                enabled: enabledCount,
                total: totalCount,
                percentage: totalCount > 0 ? Math.round((enabledCount / totalCount) * 100) : 0,
                allEnabled: enabledCount === totalCount,
                states: states
            };
        });
        
        return {
            categories: categories,
            totalStates: this.states.size,
            enabledStates: Array.from(this.states.values()).filter(state => state.enabled).length,
            timestamp: Date.now()
        };
    }

    /**
     * Logs current system status
     */
    logSystemStatus() {
        const status = this.getSystemStatus();
        
        console.log('🎛️ === UI State System Status ===');
        console.log(`🎛️ Total States: ${status.enabledStates}/${status.totalStates} enabled`);
        
        Object.entries(status.categories).forEach(([category, info]) => {
            console.log(`🎛️ ${category}: ${info.enabled}/${info.total} (${info.percentage}%)`);
            
            Object.entries(info.states).forEach(([stateId, state]) => {
                const icon = state.enabled ? '✅' : '❌';
                console.log(`🎛️   ${icon} ${stateId}: ${state.reason}`);
            });
        });
    }

    /**
     * Resets all states to defaults
     */
    resetStates() {
        console.log('🎛️ Resetting all UI states to defaults');
        
        Object.entries(this.defaultStates).forEach(([stateId, state]) => {
            this.states.set(stateId, { ...state, timestamp: Date.now() });
        });
        
        this.updateUIElements();
    }

    /**
     * Destroys the UI state manager
     */
    destroy() {
        console.log('🎛️ Destroying UIStateManager');
        
        // Clear all watchers
        this.watchers.clear();
        
        // Clear all states
        this.states.clear();
        
        // Clear element references
        this.elements.clear();
        
        // Clear categories
        Object.keys(this.stateCategories).forEach(category => {
            this.stateCategories[category].clear();
        });
        
        console.log('🎛️ UIStateManager destroyed');
    }
}
