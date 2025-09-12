/**
 * ConsentDialog - Manages consent dialog display and interactions
 * 
 * Handles showing consent dialogs for different feature types,
 * managing user interactions, and processing consent decisions.
 */

export class ConsentDialog {
    constructor(roomWebRTC) {
        this.roomWebRTC = roomWebRTC;
    }

    /**
     * Shows a unified consent dialog for any feature type
     */
    showDialog(type, onComplete) {
        const config = this.getConsentConfig(type);
        
        // Create modal backdrop
        const backdrop = document.createElement('div');
        backdrop.id = `${type}-consent-backdrop`;
        backdrop.className = 'fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 consent-dialog';
        
        backdrop.innerHTML = `
            <div class="bg-slate-900 border border-slate-700 rounded-xl p-6 max-w-md mx-4 shadow-2xl">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 ${config.iconBg} rounded-full flex items-center justify-center mx-auto mb-4">
                        ${config.icon}
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">${config.title}</h3>
                    <p class="text-slate-300 text-sm leading-relaxed">${config.description}</p>
                </div>
                
                <div class="flex space-x-3">
                    <button id="${type}-consent-deny" 
                            class="flex-1 bg-red-500/20 hover:bg-red-500/30 text-red-400 border border-red-500/30 font-semibold py-3 px-4 rounded-lg transition-all duration-300">
                        No, Leave Room
                    </button>
                    <button id="${type}-consent-accept" 
                            class="flex-1 bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-400 border border-emerald-500/30 font-semibold py-3 px-4 rounded-lg transition-all duration-300">
                        Yes, I Consent
                    </button>
                </div>
                
                <div class="mt-4 text-xs text-slate-400 text-center">
                    <p>Your decision will be saved for this room session</p>
                </div>
            </div>
        `;

        document.body.appendChild(backdrop);

        // Add event listeners
        const acceptButton = document.getElementById(`${type}-consent-accept`);
        const declineButton = document.getElementById(`${type}-consent-deny`);
        
        acceptButton.addEventListener('click', () => {
            this.handleConsentDecision(type, true, backdrop, onComplete);
        });

        declineButton.addEventListener('click', () => {
            this.handleConsentDecision(type, false, backdrop, onComplete);
        });

        // Prevent closing by clicking backdrop
        backdrop.addEventListener('click', (e) => {
            if (e.target === backdrop) {
                e.preventDefault();
            }
        });
    }

    /**
     * Gets configuration for consent dialog based on type
     */
    getConsentConfig(type) {
        const configs = {
            stt: {
                title: 'Speech Recording Consent',
                description: 'This room has speech-to-text recording enabled. Your voice will be transcribed and saved. Do you consent to having your speech recorded and transcribed?',
                iconBg: 'bg-amber-500/20',
                icon: `<svg class="w-8 h-8 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                </svg>`
            },
            recording: {
                title: 'Video Recording Consent',
                description: 'This room has video recording enabled. Your video will be recorded and saved to the room owner\'s chosen storage service. Do you consent to having your video recorded?',
                iconBg: 'bg-red-500/20',
                icon: `<svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>`
            },
            localSave: {
                title: 'Local Save Option',
                description: 'Video recording will be saved to the room owner\'s cloud storage. Would you also like to save a copy locally to your device?',
                iconBg: 'bg-blue-500/20',
                icon: `<svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>`
            }
        };
        
        return configs[type];
    }

    /**
     * Handles consent decision for any feature type
     */
    async handleConsentDecision(type, consentGiven, backdrop, onComplete) {
        try {
            // Remove consent dialog
            backdrop.remove();

            // Delegate to consent manager
            await this.roomWebRTC.consentManager.handleConsentDecision(type, consentGiven, onComplete);
            
        } catch (error) {
            console.error(`🔒 Error handling consent decision for ${type}:`, error);
            this.roomWebRTC.uiStateManager.showError('Failed to save consent decision. Please try again.');
        }
    }

    /**
     * Removes all consent dialogs
     */
    removeAllDialogs() {
        document.querySelectorAll('.consent-dialog').forEach(dialog => {
            dialog.remove();
        });
    }
}
