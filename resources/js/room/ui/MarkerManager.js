/**
 * MarkerManager - Handles session marker creation and UI interactions
 * 
 * Manages the Add Marker button, popup interactions, timing calculations,
 * and communication with the backend API and Ably messaging system.
 */

export class MarkerManager {
    constructor(roomWebRTC) {
        this.roomWebRTC = roomWebRTC;
        this.isPopupOpen = false;
        this.currentLayout = null; // 'campaign' or 'normal'
        
        // Bind methods to preserve context
        this.togglePopup = this.togglePopup.bind(this);
        this.hidePopup = this.hidePopup.bind(this);
        this.handlePresetClick = this.handlePresetClick.bind(this);
        this.handleCreateMarker = this.handleCreateMarker.bind(this);
        this.handleDocumentClick = this.handleDocumentClick.bind(this);
        this.handleKeyPress = this.handleKeyPress.bind(this);
        
        this.setupEventListeners();
    }

    /**
     * Set up all event listeners for marker functionality
     */
    setupEventListeners() {
        console.log('🏷️ Setting up MarkerManager event listeners');
        
        // Set up listeners for both layouts (campaign and normal)
        this.setupLayoutEventListeners('campaign', '');
        this.setupLayoutEventListeners('normal', '-normal');
        
        // Global event listeners
        document.addEventListener('click', this.handleDocumentClick);
        document.addEventListener('keydown', this.handleKeyPress);
    }

    /**
     * Set up event listeners for a specific layout
     */
    setupLayoutEventListeners(layout, suffix) {
        // Add Marker button
        const addMarkerBtn = document.getElementById(`add-marker-btn${suffix}`);
        if (addMarkerBtn) {
            addMarkerBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.currentLayout = layout;
                this.togglePopup();
            });
        }

        // Preset buttons
        const presetButtons = document.querySelectorAll(`.marker-preset-btn${suffix}`);
        presetButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.currentLayout = layout;
                this.handlePresetClick(e.target.dataset.identifier);
            });
        });

        // Create marker button
        const createBtn = document.getElementById(`create-marker-btn${suffix}`);
        if (createBtn) {
            createBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.currentLayout = layout;
                this.handleCreateMarker();
            });
        }

        // Cancel button
        const cancelBtn = document.getElementById(`cancel-marker-btn${suffix}`);
        if (cancelBtn) {
            cancelBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.currentLayout = layout;
                this.hidePopup();
            });
        }

        // Custom input enter key
        const customInput = document.getElementById(`custom-marker-input${suffix}`);
        if (customInput) {
            customInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.stopPropagation();
                    this.currentLayout = layout;
                    this.handleCreateMarker();
                }
            });
        }
    }

    /**
     * Toggle the marker popup visibility
     */
    togglePopup() {
        if (this.isPopupOpen) {
            this.hidePopup();
        } else {
            this.showPopup();
        }
    }

    /**
     * Show the marker popup
     */
    showPopup() {
        console.log('🏷️ Showing marker popup for layout:', this.currentLayout);
        
        const suffix = this.currentLayout === 'normal' ? '-normal' : '';
        const popup = document.getElementById(`marker-popup${suffix}`);
        const customInput = document.getElementById(`custom-marker-input${suffix}`);
        
        if (popup) {
            popup.classList.remove('hidden');
            this.isPopupOpen = true;
            
            // Clear any previous custom input
            if (customInput) {
                customInput.value = '';
            }
            
            console.log('🏷️ ✅ Marker popup shown');
        }
    }

    /**
     * Hide the marker popup
     */
    hidePopup() {
        console.log('🏷️ Hiding marker popup for layout:', this.currentLayout);
        
        const suffix = this.currentLayout === 'normal' ? '-normal' : '';
        const popup = document.getElementById(`marker-popup${suffix}`);
        
        if (popup) {
            popup.classList.add('hidden');
            this.isPopupOpen = false;
            console.log('🏷️ ✅ Marker popup hidden');
        }
    }

    /**
     * Handle preset button clicks
     */
    handlePresetClick(identifier) {
        console.log('🏷️ Preset clicked:', identifier);
        
        const suffix = this.currentLayout === 'normal' ? '-normal' : '';
        const customInput = document.getElementById(`custom-marker-input${suffix}`);
        
        if (customInput) {
            customInput.value = identifier;
        }
    }

    /**
     * Handle create marker button click
     */
    async handleCreateMarker() {
        console.log('🏷️ === Creating Session Marker ===');
        
        const suffix = this.currentLayout === 'normal' ? '-normal' : '';
        const customInput = document.getElementById(`custom-marker-input${suffix}`);
        
        const identifier = customInput ? customInput.value.trim() : '';
        
        console.log(`🏷️ Identifier: "${identifier}"`);
        
        try {
            // Calculate timing information
            const timingInfo = this.calculateTimingInfo();
            console.log('🏷️ Timing info:', timingInfo);
            
            // Create the marker via API
            const response = await this.createMarkerViaAPI(identifier, timingInfo);
            
            if (response.success) {
                console.log('🏷️ ✅ Marker created successfully:', response.data);
                
                // Send Ably message to all participants
                this.sendMarkerAblyMessage(response.data);
                
                // Show success feedback
                this.showSuccessFeedback(identifier || 'Marker');
                
                // Hide popup
                this.hidePopup();
            } else {
                console.error('🏷️ ❌ Failed to create marker:', response.message);
                this.showErrorFeedback(response.message || 'Failed to create marker');
            }
            
        } catch (error) {
            console.error('🏷️ ❌ Error creating marker:', error);
            this.showErrorFeedback('Failed to create marker');
        }
    }

    /**
     * Calculate timing information for the marker
     */
    calculateTimingInfo() {
        const timingInfo = {
            video_time: null,
            stt_time: null
        };

        // Calculate video recording time if recording is active
        if (this.roomWebRTC.videoRecorder && this.roomWebRTC.videoRecorder.isCurrentlyRecording()) {
            const originalStartTime = this.roomWebRTC.videoRecorder.getOriginalRecordingStartTime();
            if (originalStartTime) {
                timingInfo.video_time = Math.floor((Date.now() - originalStartTime) / 1000);
                console.log('🏷️ Video recording time:', timingInfo.video_time, 'seconds');
            }
        }

        // Calculate STT time if STT is active
        if (this.roomWebRTC.isSpeechEnabled && this.roomWebRTC.currentSpeechModule) {
            const sttStartTime = this.roomWebRTC.currentSpeechModule.getStartTime?.();
            if (sttStartTime) {
                timingInfo.stt_time = Math.floor((Date.now() - sttStartTime) / 1000);
                console.log('🏷️ STT recording time:', timingInfo.stt_time, 'seconds');
            }
        }

        return timingInfo;
    }

    /**
     * Create marker via API call
     */
    async createMarkerViaAPI(identifier, timingInfo) {
        const payload = {
            room_id: this.roomWebRTC.roomData.id,
            identifier: identifier || null,
            video_time: timingInfo.video_time,
            stt_time: timingInfo.stt_time
        };

        console.log('🏷️ API payload:', payload);

        const response = await fetch('/api/session-markers', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify(payload)
        });

        return await response.json();
    }

    /**
     * Send Ably message to notify all participants about the new marker
     */
    sendMarkerAblyMessage(markerData) {
        console.log('🏷️ Sending Ably message for marker:', markerData);
        
        if (this.roomWebRTC.ablyManager) {
            this.roomWebRTC.ablyManager.publishToAbly('session-marker-created', {
                uuid: markerData.uuid,
                identifier: markerData.identifier,
                creator_id: this.roomWebRTC.currentUserId,
                video_time: markerData.video_time,
                stt_time: markerData.stt_time,
                created_at: new Date().toISOString()
            });
            
            console.log('🏷️ ✅ Ably message sent');
        } else {
            console.warn('🏷️ ⚠️ No Ably manager available');
        }
    }

    /**
     * Handle incoming Ably marker messages
     */
    handleMarkerAblyMessage(data) {
        console.log('🏷️ === Received Session Marker via Ably ===');
        console.log('🏷️ Marker data:', data);
        
        // Show notification to user
        this.showMarkerNotification(data);
        
        // Store marker locally if needed for future reference
        // This could be expanded to maintain a local cache of markers
    }

    /**
     * Show a notification when a marker is created by another user
     */
    showMarkerNotification(markerData) {
        const creatorName = this.getParticipantName(markerData.creator_id);
        const markerName = markerData.identifier || 'Marker';
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 bg-amber-600/90 backdrop-blur-xl text-white px-4 py-2 rounded-lg shadow-lg z-50 animate-pulse';
        notification.innerHTML = `
            <div class="flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                <div class="text-sm">
                    <div class="font-medium">${creatorName} added marker</div>
                    <div class="text-amber-100 text-xs">${markerName}</div>
                </div>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-remove after 3 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 3000);
    }

    /**
     * Get participant name by user ID
     */
    getParticipantName(userId) {
        const participant = this.roomWebRTC.roomData.participants.find(p => p.user_id === userId);
        return participant?.character_name || participant?.username || 'Unknown User';
    }

    /**
     * Show success feedback
     */
    showSuccessFeedback(markerName) {
        console.log('🏷️ ✅ Success:', `"${markerName}" marker created`);
        // Could implement a toast notification here
    }

    /**
     * Show error feedback
     */
    showErrorFeedback(message) {
        console.error('🏷️ ❌ Error:', message);
        // Could implement a toast notification here
        alert(`Error: ${message}`); // Temporary simple error display
    }

    /**
     * Handle document clicks to close popup when clicking outside
     */
    handleDocumentClick(event) {
        if (!this.isPopupOpen) return;
        
        // Check if click is outside the popup and button
        const suffix = this.currentLayout === 'normal' ? '-normal' : '';
        const popup = document.getElementById(`marker-popup${suffix}`);
        const button = document.getElementById(`add-marker-btn${suffix}`);
        
        if (popup && !popup.contains(event.target) && 
            button && !button.contains(event.target)) {
            this.hidePopup();
        }
    }

    /**
     * Handle escape key to close popup
     */
    handleKeyPress(event) {
        if (event.key === 'Escape' && this.isPopupOpen) {
            this.hidePopup();
        }
    }

    /**
     * Clean up event listeners
     */
    destroy() {
        document.removeEventListener('click', this.handleDocumentClick);
        document.removeEventListener('keydown', this.handleKeyPress);
    }
}
