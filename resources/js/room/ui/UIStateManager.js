/**
 * UIStateManager - Manages overall UI state and interactions
 * 
 * Handles enabling/disabling UI elements, managing button states,
 * and coordinating UI updates across the application.
 */

export class UIStateManager {
    constructor(roomWebRTC) {
        this.roomWebRTC = roomWebRTC;
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
}
