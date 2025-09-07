/**
 * PageProtection - Manages page refresh and navigation protection
 * 
 * Handles protection against data loss during recording sessions,
 * emergency save functionality, and page unload warnings.
 */

export class PageProtection {
    constructor(roomWebRTC) {
        this.roomWebRTC = roomWebRTC;
        this.setupProtection();
    }

    /**
     * Sets up protection against page refresh data loss
     */
    setupProtection() {
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

        // Handle visibility change (tab switching, minimizing)
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                console.log('🚨 Page hidden - recording may be affected');
            } else {
                console.log('🚨 Page visible again');
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
     * Shows a warning dialog before leaving
     */
    showLeaveWarning(message = 'Are you sure you want to leave? Any unsaved data will be lost.') {
        return confirm(message);
    }

    /**
     * Temporarily disables page protection
     */
    disableProtection() {
        this.protectionDisabled = true;
        console.log('🚨 Page protection temporarily disabled');
    }

    /**
     * Re-enables page protection
     */
    enableProtection() {
        this.protectionDisabled = false;
        console.log('🚨 Page protection re-enabled');
    }

    /**
     * Checks if protection should be active
     */
    shouldProtect() {
        if (this.protectionDisabled) return false;
        
        const isRecording = this.roomWebRTC.videoRecorder.isCurrentlyRecording();
        const hasRecordedData = this.roomWebRTC.videoRecorder.getRecordedChunks().length > 0;
        
        return isRecording && hasRecordedData;
    }

    /**
     * Safe navigation helper
     */
    safeNavigate(url, forceNavigate = false) {
        if (!forceNavigate && this.shouldProtect()) {
            const shouldLeave = this.showLeaveWarning(
                'Recording in progress! If you leave now, your recording will be lost. Stop recording first to save your video.\n\nAre you sure you want to leave?'
            );
            
            if (!shouldLeave) {
                return false;
            }
        }
        
        window.location.href = url;
        return true;
    }

    /**
     * Safe reload helper
     */
    safeReload(forceReload = false) {
        if (!forceReload && this.shouldProtect()) {
            const shouldReload = this.showLeaveWarning(
                'Recording in progress! If you reload now, your recording will be lost. Stop recording first to save your video.\n\nAre you sure you want to reload?'
            );
            
            if (!shouldReload) {
                return false;
            }
        }
        
        window.location.reload();
        return true;
    }

    /**
     * Cleanup method to remove event listeners
     */
    cleanup() {
        // Note: beforeunload and unload listeners are automatically cleaned up
        // when the page unloads, but we could track them if needed for manual cleanup
        console.log('🚨 Page protection cleanup complete');
    }
}
