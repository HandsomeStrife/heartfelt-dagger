/**
 * ConsentManager - Manages consent requirements and status
 * 
 * Handles checking consent status, managing consent flow,
 * and coordinating consent dialogs for STT and recording features.
 * 
 * MEDIUM FIX: Uses robust CSRF token utility
 */

import { getCSRFToken } from '../utils/csrf';

export class ConsentManager {
    constructor(roomWebRTC) {
        this.roomWebRTC = roomWebRTC;
        
        // Check if local save consent is applicable (recording enabled + remote storage)
        const recordingEnabled = roomWebRTC.roomData.recording_enabled;
        const isRemoteStorage = recordingEnabled && 
            roomWebRTC.roomData.recording_settings?.storage_provider !== 'local_device';
            
        this.consentData = {
            stt: { status: null, enabled: roomWebRTC.roomData.stt_enabled },
            recording: { status: null, enabled: recordingEnabled },
            localSave: { status: null, enabled: isRemoteStorage }
        };
        
        // MEDIUM FIX: Consent status cache with 5-minute TTL
        this.consentCache = {
            stt: { status: null, timestamp: null, ttl: 300000 }, // 5 minutes
            recording: { status: null, timestamp: null, ttl: 300000 }
            // localSave doesn't need caching as it's session-only
        };
    }
    
    /**
     * MEDIUM FIX: Checks if cached consent is still valid
     */
    isCacheValid(type) {
        const cache = this.consentCache[type];
        if (!cache || !cache.timestamp) return false;
        
        const now = Date.now();
        const age = now - cache.timestamp;
        return age < cache.ttl;
    }
    
    /**
     * MEDIUM FIX: Gets cached consent status if valid
     */
    getCachedConsent(type) {
        if (this.isCacheValid(type)) {
            console.log(`🔒 Using cached ${type} consent (${Math.floor((Date.now() - this.consentCache[type].timestamp) / 1000)}s old)`);
            return this.consentCache[type].status;
        }
        return null;
    }
    
    /**
     * MEDIUM FIX: Caches consent status
     */
    cacheConsentStatus(type, status) {
        if (this.consentCache[type]) {
            this.consentCache[type].status = status;
            this.consentCache[type].timestamp = Date.now();
            console.log(`🔒 Cached ${type} consent status`);
        }
    }

    /**
     * Checks consent requirements immediately upon entering the room
     */
    async checkInitialConsentRequirements() {
        console.log('🔒 Checking initial consent requirements...');
        
        const needsSttConsent = this.consentData.stt.enabled;
        const needsRecordingConsent = this.consentData.recording.enabled;
        const needsLocalSaveConsent = this.consentData.localSave.enabled;

        if (!needsSttConsent && !needsRecordingConsent && !needsLocalSaveConsent) {
            console.log('🔒 No consent requirements for this room');
            return;
        }

        // Disable UI until consent is resolved
        this.roomWebRTC.uiStateManager.disableJoinUIForConsent();

        try {
            // Check consent statuses in parallel
            const consentChecks = [];
            
            if (needsSttConsent) {
                consentChecks.push(this.checkConsentStatus('stt'));
            }
            
            if (needsRecordingConsent) {
                consentChecks.push(this.checkConsentStatus('recording'));
            }
            
            if (needsLocalSaveConsent) {
                consentChecks.push(this.checkConsentStatus('localSave'));
            }

            await Promise.all(consentChecks);

            // Process consent results and show dialogs if needed
            await this.processInitialConsentResults();

        } catch (error) {
            console.error('❌ Error checking initial consent requirements:', error);
            // Enable UI on error to prevent deadlock
            this.roomWebRTC.uiStateManager.enableJoinUI();
        }
    }

    /**
     * Handles all consent requirements in a unified flow
     */
    async handleConsentRequirements() {
        const needsSttConsent = this.consentData.stt.enabled;
        const needsRecordingConsent = this.consentData.recording.enabled;
        const needsLocalSaveConsent = this.consentData.localSave.enabled;

        if (!needsSttConsent && !needsRecordingConsent && !needsLocalSaveConsent) {
            // No consent needed, enable UI immediately
            this.roomWebRTC.uiStateManager.enableJoinUI();
            return;
        }

        // Disable UI until consent is resolved
        this.roomWebRTC.uiStateManager.disableJoinUIForConsent();

        try {
            // Check consent statuses in parallel
            const consentChecks = [];
            
            if (needsSttConsent) {
                consentChecks.push(this.checkConsentStatus('stt'));
            }
            
            if (needsRecordingConsent) {
                consentChecks.push(this.checkConsentStatus('recording'));
            }
            
            if (needsLocalSaveConsent) {
                consentChecks.push(this.checkConsentStatus('localSave'));
            }

            // CRITICAL FIX: Add 5-second timeout to prevent UI deadlock
            await this.withTimeout(
                Promise.all(consentChecks),
                5000,
                'Consent checks timed out after 5 seconds'
            );

            // Process consent results
            await this.processConsentResults();

        } catch (error) {
            console.error('❌ Error handling consent requirements:', error);
            this.roomWebRTC.uiStateManager.enableJoinUI(); // Enable UI on error to prevent deadlock
        }
    }

    /**
     * Checks consent status for a specific feature type
     */
    async checkConsentStatus(type) {
        // LOCAL SAVE CONSENT: ALWAYS prompt on page refresh (never fetch from backend)
        if (type === 'localSave') {
            console.log('🔒 Local save consent will be prompted on EVERY page load');
            
            // CRITICAL FIX: Don't reset consent if user has already made a decision in this session
            const currentConsent = this.consentData.localSave.status;
            const hasExistingDecision = currentConsent && (currentConsent.consent_given === true || currentConsent.consent_denied === true);
            
            if (hasExistingDecision) {
                console.log('🔒 Local save consent already decided in this session:', {
                    consent_given: currentConsent.consent_given,
                    consent_denied: currentConsent.consent_denied
                });
                return; // Don't reset if user has already made a decision
            }
            
            this.consentData.localSave.status = {
                local_save_enabled: true,
                requires_consent: true, // ALWAYS require fresh consent
                consent_given: false,
                consent_denied: false
            };
            return;
        }
        
        let endpoint;
        if (type === 'stt') {
            endpoint = 'stt-consent';
        } else if (type === 'recording') {
            endpoint = 'recording-consent';
        } else {
            console.error(`🔒 Unknown consent type: ${type}`);
            return;
        }
        
        // MEDIUM FIX: Check cache first
        const cachedStatus = this.getCachedConsent(type);
        if (cachedStatus) {
            this.consentData[type].status = cachedStatus;
            return;
        }
        
        try {
            const response = await fetch(`/api/rooms/${this.roomWebRTC.roomData.id}/${endpoint}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': getCSRFToken() // MEDIUM FIX: Use robust CSRF utility
                }
            });

            if (response.ok) {
                const status = await response.json();
                this.consentData[type].status = status;
                // MEDIUM FIX: Cache the result
                this.cacheConsentStatus(type, status);
                console.log(`🔒 ${type.toUpperCase()} consent status:`, status);
            } else {
                console.warn(`🔒 Failed to check ${type} consent status:`, response.status);
            }
        } catch (error) {
            console.error(`🔒 Error checking ${type} consent:`, error);
        }
    }

    /**
     * Processes initial consent results and shows dialogs if needed
     */
    async processInitialConsentResults() {
        const sttStatus = this.consentData.stt.status;
        const recordingStatus = this.consentData.recording.status;
        const localSaveStatus = this.consentData.localSave.status;

        // Collect consent dialogs needed
        const dialogsNeeded = [];
        
        if (sttStatus?.requires_consent) {
            dialogsNeeded.push('stt');
        }
        
        if (recordingStatus?.requires_consent) {
            dialogsNeeded.push('recording');
        }
        
        // Local save consent comes after recording consent
        if (localSaveStatus?.requires_consent) {
            dialogsNeeded.push('localSave');
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
                this.roomWebRTC.uiStateManager.enableJoinUI();
            }
        }
    }

    /**
     * Processes consent results and shows dialogs or starts features as needed
     */
    async processConsentResults() {
        const sttStatus = this.consentData.stt.status;
        const recordingStatus = this.consentData.recording.status;

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
                this.roomWebRTC.consentDialog.showDialog(type, resolve);
            });
        }
    }

    /**
     * Handles actions when no consent dialogs are needed
     */
    handleAutoConsentActions() {
        const sttStatus = this.consentData.stt.status;
        const recordingStatus = this.consentData.recording.status;

        // Check for any denials that require redirection
        if (sttStatus?.consent_denied || recordingStatus?.consent_denied) {
            this.handleConsentDenied();
            return;
        }

        // Don't start features automatically after consent - wait for user to join a slot
        // Features will be started when the user actually joins a slot and has media access
        console.log('🔒 Consent resolved - features will start when user joins a slot');

        // Enable UI so user can join
        this.roomWebRTC.uiStateManager.enableJoinUI();
    }

    /**
     * Handles consent decision for any feature type
     */
    async handleConsentDecision(type, consentGiven, onComplete) {
        // LOCAL SAVE CONSENT: Store in memory only (don't persist to backend)
        if (type === 'localSave') {
            console.log(`🔒 LOCAL SAVE consent ${consentGiven ? 'granted' : 'denied'} (session only, not persisted)`);
            
            // Update in-memory consent status
            this.consentData.localSave.status = {
                local_save_enabled: true,
                requires_consent: false, // No longer requires consent after decision
                consent_given: consentGiven,
                consent_denied: !consentGiven
            };
            
            if (consentGiven) {
                console.log('🔒 Local save enabled for this session - video will be saved to device');
            } else {
                console.log('🔒 Local save declined - only remote recording will occur');
            }
            
            // Complete flow immediately
            onComplete();
            this.checkAllConsentsResolved();
            return;
        }
        
        let endpoint;
        if (type === 'stt') {
            endpoint = 'stt-consent';
        } else if (type === 'recording') {
            endpoint = 'recording-consent';
        } else {
            console.error(`🔒 Unknown consent type: ${type}`);
            return;
        }
        
        try {
            const response = await fetch(`/api/rooms/${this.roomWebRTC.roomData.id}/${endpoint}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCSRFToken() // MEDIUM FIX: Use robust CSRF utility
                },
                body: JSON.stringify({
                    consent_given: consentGiven
                })
            });

            if (response.ok) {
                const result = await response.json();
                console.log(`🔒 ${type.toUpperCase()} consent decision saved:`, result);

                // Update local consent status
                this.consentData[type].status = result;
                
                // MEDIUM FIX: Update cache with new status
                this.cacheConsentStatus(type, result);

                if (consentGiven) {
                    // Don't start features immediately - they will start when user joins a slot
                    console.log(`🔒 ${type.toUpperCase()} consent granted - feature will start when user joins a slot`);
                    
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
                // Complete flow even on error to prevent deadlock
                onComplete();
                this.roomWebRTC.uiStateManager.enableJoinUI();
            }
        } catch (error) {
            console.error(`🔒 Error saving ${type} consent decision:`, error);
            alert('Failed to save consent decision. Please try again.');
            // Complete flow even on error to prevent deadlock
            onComplete();
            this.roomWebRTC.uiStateManager.enableJoinUI();
        }
    }

    /**
     * Checks if all required consents are resolved and enables UI
     */
    checkAllConsentsResolved() {
        const allResolved = Object.values(this.consentData).every(consent => 
            !consent.enabled || (consent.status && (consent.status.consent_given || consent.status.consent_denied))
        );
        
        if (allResolved) {
            this.roomWebRTC.uiStateManager.enableJoinUI();
        }
    }

    /**
     * Handles consent denial based on whether it's required or optional
     */
    handleConsentDenial(type) {
        const status = this.consentData[type].status;
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
                window.location.href = `/rooms/${this.roomWebRTC.roomData.invite_code || ''}`;
            }
        }, 1000);
    }

    /**
     * Gets consent status for a feature type
     */
    getConsentStatus(type) {
        return this.consentData[type]?.status;
    }

    /**
     * Checks if a feature is enabled
     */
    isFeatureEnabled(type) {
        return this.consentData[type]?.enabled || false;
    }

    /**
     * Checks if consent is given for a feature
     */
    isConsentGiven(type) {
        return this.consentData[type]?.status?.consent_given || false;
    }
    
    /**
     * Wraps a promise with a timeout to prevent deadlocks
     * @param {Promise} promise - The promise to wrap
     * @param {number} timeoutMs - Timeout in milliseconds
     * @param {string} errorMessage - Error message if timeout occurs
     */
    withTimeout(promise, timeoutMs, errorMessage) {
        return Promise.race([
            promise,
            new Promise((_, reject) => 
                setTimeout(() => reject(new Error(errorMessage)), timeoutMs)
            )
        ]);
    }
}
