/**
 * PageProtection - Protects against data loss during page refresh/navigation
 * 
 * Provides warnings and emergency save functionality when users try to leave
 * the page while recording is in progress.
 */
export class PageProtection {
    constructor() {
        this.isRecording = false;
        this.recordedChunks = [];
        this.recMime = null;
        this.onEmergencySave = null;
        
        this.setupEventListeners();
    }

    /**
     * Updates the recording state for protection logic
     */
    updateRecordingState(isRecording, recordedChunks = [], recMime = null) {
        this.isRecording = isRecording;
        this.recordedChunks = recordedChunks;
        this.recMime = recMime;
    }

    /**
     * Sets callback for emergency save functionality
     */
    setEmergencySaveCallback(callback) {
        this.onEmergencySave = callback;
    }

    /**
     * Sets up protection against page refresh data loss
     */
    setupEventListeners() {
        // Warn user if they try to leave/refresh while recording
        window.addEventListener('beforeunload', (event) => {
            console.log('🚨 Page unload detected');
            console.log(`  - Is recording: ${this.isRecording}`);
            console.log(`  - Recorded chunks: ${this.recordedChunks ? this.recordedChunks.length : 0}`);
            
            if (this.isRecording && this.recordedChunks && this.recordedChunks.length > 0) {
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
            if (this.isRecording && this.recordedChunks.length > 0) {
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
            if (this.recordedChunks.length === 0) return;
            
            console.warn('🚨 Emergency save: Page closing with active recording');
            
            // Call the emergency save callback if provided
            if (this.onEmergencySave) {
                this.onEmergencySave(this.recordedChunks, this.recMime);
                return;
            }
            
            // Fallback emergency save logic
            const combinedBlob = new Blob(this.recordedChunks, { type: this.recMime });
            const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
            const ext = this.recMime && this.recMime.includes('webm') ? 'webm' : 'mp4';
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
     * Manually trigger emergency save (for testing or manual calls)
     */
    triggerEmergencySave() {
        this.emergencySaveRecording();
    }

    /**
     * Clean up event listeners
     */
    destroy() {
        // Note: We can't remove beforeunload/unload listeners easily
        // They're automatically cleaned up when the page unloads
        this.onEmergencySave = null;
    }
}
