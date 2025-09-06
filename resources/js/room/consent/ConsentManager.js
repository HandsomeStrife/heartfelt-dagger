/**
 * ConsentManager - Manages consent dialogs for STT and video recording
 * 
 * Handles the display and management of consent popups for speech-to-text
 * and video recording features, including required vs optional consent logic.
 */
export class ConsentManager {
    constructor(roomWebRTC) {
        this.roomWebRTC = roomWebRTC;
        this.consentModal = null;
        this.consentCallbacks = new Map(); // Map of feature -> {resolve, reject}
        
        this.setupConsentModal();
    }

    /**
     * Sets up the consent modal UI
     */
    setupConsentModal() {
        // Create modal container
        this.consentModal = document.createElement('div');
        this.consentModal.id = 'consent-modal';
        this.consentModal.className = 'fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 hidden';
        
        this.consentModal.innerHTML = `
            <div class="bg-slate-900 border border-slate-700 rounded-xl p-6 max-w-md mx-4 shadow-2xl">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="w-10 h-10 bg-amber-500/20 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.9-.833-2.664 0L4.232 15.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-white font-semibold text-lg" id="consent-title">Permission Required</h3>
                        <p class="text-slate-400 text-sm" id="consent-subtitle">Please review and respond</p>
                    </div>
                </div>
                
                <div class="mb-6">
                    <p class="text-slate-300 text-sm leading-relaxed" id="consent-message">
                        This feature requires your permission to proceed.
                    </p>
                </div>
                
                <div class="flex space-x-3">
                    <button id="consent-deny" 
                            class="flex-1 px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white text-sm font-medium rounded-md transition-colors duration-200">
                        Decline
                    </button>
                    <button id="consent-allow" 
                            class="flex-1 px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white text-sm font-medium rounded-md transition-colors duration-200">
                        Allow
                    </button>
                </div>
                
                <div class="mt-4 text-xs text-slate-500 text-center" id="consent-note">
                    You can change these permissions later in room settings.
                </div>
            </div>
        `;
        
        document.body.appendChild(this.consentModal);
        this.setupEventListeners();
    }

    /**
     * Sets up event listeners for consent modal
     */
    setupEventListeners() {
        const allowBtn = this.consentModal.querySelector('#consent-allow');
        const denyBtn = this.consentModal.querySelector('#consent-deny');
        
        allowBtn.addEventListener('click', () => {
            this.handleConsentResponse(true);
        });
        
        denyBtn.addEventListener('click', () => {
            this.handleConsentResponse(false);
        });
        
        // Close on backdrop click (only if not required)
        this.consentModal.addEventListener('click', (e) => {
            if (e.target === this.consentModal && !this.isCurrentConsentRequired()) {
                this.handleConsentResponse(false);
            }
        });
        
        // Handle escape key (only if not required)
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !this.consentModal.classList.contains('hidden') && !this.isCurrentConsentRequired()) {
                this.handleConsentResponse(false);
            }
        });
    }

    /**
     * Requests consent for a specific feature
     */
    async requestConsent(feature, options = {}) {
        return new Promise((resolve, reject) => {
            // Store callback for this feature
            this.consentCallbacks.set(feature, { resolve, reject });
            
            // Configure modal for this feature
            this.configureModalForFeature(feature, options);
            
            // Show modal
            this.showModal();
        });
    }

    /**
     * Configures the modal content for a specific feature
     */
    configureModalForFeature(feature, options) {
        const title = this.consentModal.querySelector('#consent-title');
        const subtitle = this.consentModal.querySelector('#consent-subtitle');
        const message = this.consentModal.querySelector('#consent-message');
        const note = this.consentModal.querySelector('#consent-note');
        const denyBtn = this.consentModal.querySelector('#consent-deny');
        
        switch (feature) {
            case 'stt':
                title.textContent = 'Speech-to-Text Permission';
                subtitle.textContent = 'Microphone access required';
                message.innerHTML = `
                    <strong>This room wants to use speech-to-text transcription.</strong><br><br>
                    This feature will:
                    <ul class="list-disc list-inside mt-2 space-y-1 text-slate-400">
                        <li>Access your microphone to capture speech</li>
                        <li>Convert your speech to text in real-time</li>
                        <li>Display transcriptions to all participants</li>
                    </ul>
                `;
                
                if (options.required) {
                    note.textContent = 'This permission is required to participate in this room.';
                    denyBtn.textContent = 'Leave Room';
                } else {
                    note.textContent = 'You can participate without speech-to-text if you prefer.';
                    denyBtn.textContent = 'Skip';
                }
                break;
                
            case 'recording':
                title.textContent = 'Video Recording Permission';
                subtitle.textContent = 'Camera and microphone access required';
                message.innerHTML = `
                    <strong>This room wants to record video sessions.</strong><br><br>
                    This feature will:
                    <ul class="list-disc list-inside mt-2 space-y-1 text-slate-400">
                        <li>Access your camera and microphone</li>
                        <li>Record video and audio during sessions</li>
                        <li>Save recordings for later playback</li>
                    </ul>
                `;
                
                if (options.required) {
                    note.textContent = 'This permission is required to participate in this room.';
                    denyBtn.textContent = 'Leave Room';
                } else {
                    note.textContent = 'You can participate without being recorded if you prefer.';
                    denyBtn.textContent = 'Skip';
                }
                break;
                
            default:
                title.textContent = 'Permission Required';
                subtitle.textContent = 'Please review and respond';
                message.textContent = options.message || 'This feature requires your permission to proceed.';
                note.textContent = options.note || 'You can change these permissions later in room settings.';
                denyBtn.textContent = options.required ? 'Leave Room' : 'Decline';
        }
        
        // Store current feature and options for reference
        this.currentFeature = feature;
        this.currentOptions = options;
    }

    /**
     * Shows the consent modal
     */
    showModal() {
        this.consentModal.classList.remove('hidden');
        
        // Focus the allow button for accessibility
        const allowBtn = this.consentModal.querySelector('#consent-allow');
        setTimeout(() => allowBtn.focus(), 100);
    }

    /**
     * Hides the consent modal
     */
    hideModal() {
        this.consentModal.classList.add('hidden');
        this.currentFeature = null;
        this.currentOptions = null;
    }

    /**
     * Handles consent response (allow/deny)
     */
    async handleConsentResponse(allowed) {
        const feature = this.currentFeature;
        const options = this.currentOptions;
        
        if (!feature) return;
        
        console.log(`🔒 Consent ${allowed ? 'granted' : 'denied'} for ${feature}`);
        
        // Hide modal
        this.hideModal();
        
        // Get callback for this feature
        const callback = this.consentCallbacks.get(feature);
        if (!callback) return;
        
        // Clear callback
        this.consentCallbacks.delete(feature);
        
        try {
            if (allowed) {
                // Update consent status
                await this.updateConsentStatus(feature, 'granted');
                callback.resolve(true);
            } else {
                // Update consent status
                await this.updateConsentStatus(feature, 'denied');
                
                // If required and denied, redirect to dashboard
                if (options.required) {
                    console.log(`🔒 Required consent denied for ${feature}, redirecting to dashboard`);
                    this.redirectToDashboard();
                    callback.reject(new Error(`Required consent denied for ${feature}`));
                } else {
                    callback.resolve(false);
                }
            }
        } catch (error) {
            console.error(`🔒 Error handling consent response for ${feature}:`, error);
            callback.reject(error);
        }
    }

    /**
     * Updates consent status via API
     */
    async updateConsentStatus(feature, status) {
        try {
            const endpoint = feature === 'stt' ? 'stt-consent' : 'recording-consent';
            const response = await fetch(`/api/rooms/${this.roomWebRTC.roomData.id}/${endpoint}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ 
                    consent: status === 'granted' ? 'granted' : 'denied'
                })
            });

            if (!response.ok) {
                throw new Error(`Failed to update ${feature} consent: ${response.status}`);
            }

            console.log(`🔒 ${feature} consent status updated: ${status}`);
        } catch (error) {
            console.error(`🔒 Failed to update ${feature} consent status:`, error);
            throw error;
        }
    }

    /**
     * Checks if the current consent request is required
     */
    isCurrentConsentRequired() {
        return this.currentOptions && this.currentOptions.required;
    }

    /**
     * Redirects to dashboard when required consent is denied
     */
    redirectToDashboard() {
        // Show a brief message before redirecting
        const message = document.createElement('div');
        message.className = 'fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg z-50';
        message.textContent = 'Required permissions denied. Returning to dashboard...';
        document.body.appendChild(message);
        
        setTimeout(() => {
            if (document.referrer && !document.referrer.includes(window.location.pathname)) {
                window.location.href = document.referrer;
            } else {
                window.location.href = '/dashboard';
            }
        }, 2000);
    }

    /**
     * Checks initial consent requirements and shows popups if needed
     */
    async checkInitialConsent() {
        console.log('🔒 Checking initial consent requirements...');
        
        const roomData = this.roomWebRTC.roomData;
        const consentPromises = [];
        
        // Check STT consent
        if (roomData.stt_enabled) {
            const sttRequired = roomData.stt_consent_requirement === 'required';
            console.log(`🔒 STT consent required: ${sttRequired}`);
            
            consentPromises.push(
                this.requestConsent('stt', { required: sttRequired })
                    .then(granted => {
                        this.roomWebRTC.consentManager.stt.consent_given = granted;
                        return { feature: 'stt', granted };
                    })
                    .catch(error => {
                        console.error('🔒 STT consent error:', error);
                        return { feature: 'stt', granted: false, error };
                    })
            );
        }
        
        // Check recording consent
        if (roomData.recording_enabled) {
            const recordingRequired = roomData.recording_consent_requirement === 'required';
            console.log(`🔒 Recording consent required: ${recordingRequired}`);
            
            consentPromises.push(
                this.requestConsent('recording', { required: recordingRequired })
                    .then(granted => {
                        this.roomWebRTC.consentManager.recording.consent_given = granted;
                        return { feature: 'recording', granted };
                    })
                    .catch(error => {
                        console.error('🔒 Recording consent error:', error);
                        return { feature: 'recording', granted: false, error };
                    })
            );
        }
        
        // Wait for all consent requests to complete
        if (consentPromises.length > 0) {
            const results = await Promise.allSettled(consentPromises);
            console.log('🔒 Consent results:', results);
            return results;
        }
        
        return [];
    }

    /**
     * Cleans up consent manager resources
     */
    destroy() {
        if (this.consentModal) {
            this.consentModal.remove();
            this.consentModal = null;
        }
        
        this.consentCallbacks.clear();
        console.log('🔒 ConsentManager destroyed');
    }
}
