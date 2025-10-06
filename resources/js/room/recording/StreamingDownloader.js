/**
 * StreamingDownloader - Manages streaming downloads for local device recording
 * 
 * Handles collection of recording chunks and creates a single combined
 * download file for local device storage.
 */

export class StreamingDownloader {
    constructor(roomWebRTC) {
        this.roomWebRTC = roomWebRTC;
        this.recordingFilename = null;
        this.recordedChunks = [];
        this.isStreamingDownloadActive = false;
        
        // Memory management
        this.totalRecordedSize = 0; // Track size incrementally for O(1) performance
        this.hasShownMemoryWarning = false;
        
        // Memory limits (in bytes)
        this.MEMORY_WARNING_THRESHOLD = 500 * 1024 * 1024; // 500 MB
        this.MEMORY_HARD_LIMIT = 1024 * 1024 * 1024; // 1 GB
    }

    /**
     * Initializes single streaming download for local device recording
     */
    initializeDownload(mimeType) {
        // Generate filename with timestamp
        const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
        const ext = mimeType.includes('webm') ? 'webm' : 'mp4';
        this.recordingFilename = `room-recording-${timestamp}.${ext}`;
        this.recordedChunks = []; // Collect chunks for single download
        this.isStreamingDownloadActive = true;
        
        // Reset memory tracking
        this.totalRecordedSize = 0;
        this.hasShownMemoryWarning = false;
        
        console.log(`🎥 Streaming download initialized: ${this.recordingFilename}`);
        console.log(`🎥 Recording will be saved as single continuous file`);
        console.log(`🎥 Memory limits: ${(this.MEMORY_WARNING_THRESHOLD / 1024 / 1024).toFixed(0)}MB warning, ${(this.MEMORY_HARD_LIMIT / 1024 / 1024).toFixed(0)}MB hard limit`);
    }

    /**
     * Collects chunks for single streaming download
     */
    updateDownload(newChunk) {
        if (!this.isStreamingDownloadActive) return;
        
        // Store chunk for continuous recording
        this.recordedChunks.push(newChunk);
        
        // Track size incrementally (O(1) instead of O(n))
        this.totalRecordedSize += newChunk.size;
        const totalMB = this.totalRecordedSize / 1024 / 1024;
        
        console.log(`📊 Recording chunk collected: ${(newChunk.size / 1024 / 1024).toFixed(2)} MB`);
        console.log(`🎥 Total recording size: ${totalMB.toFixed(2)} MB (${this.recordedChunks.length} chunks)`);
        
        // Memory management: Warn at 500MB threshold
        if (this.totalRecordedSize > this.MEMORY_WARNING_THRESHOLD && !this.hasShownMemoryWarning) {
            this.hasShownMemoryWarning = true;
            const warningMB = (this.MEMORY_WARNING_THRESHOLD / 1024 / 1024).toFixed(0);
            console.warn(`⚠️ Local recording has accumulated ${warningMB}+ MB in browser memory`);
            console.warn(`⚠️ Consider stopping and saving recording periodically to prevent memory issues`);
            console.warn(`⚠️ Hard limit: ${(this.MEMORY_HARD_LIMIT / 1024 / 1024).toFixed(0)}MB`);
            
            // Show user notification
            if (window.Livewire && this.roomWebRTC.roomData?.id) {
                window.Livewire.dispatch('toast', [{
                    type: 'warning',
                    message: `Recording is large (${warningMB}+ MB). Consider stopping to save periodically.`,
                    duration: 15000
                }]);
            }
        }
        
        // Memory management: Hard limit at 1GB - force save
        if (this.totalRecordedSize > this.MEMORY_HARD_LIMIT) {
            const limitGB = (this.MEMORY_HARD_LIMIT / 1024 / 1024 / 1024).toFixed(2);
            const actualGB = (this.totalRecordedSize / 1024 / 1024 / 1024).toFixed(2);
            console.error(`🚨 Recording exceeded ${limitGB}GB memory limit! (${actualGB}GB accumulated)`);
            console.error(`🚨 Auto-saving to prevent browser crash...`);
            
            // Show critical notification
            if (window.Livewire) {
                window.Livewire.dispatch('toast', [{
                    type: 'error',
                    message: `Recording exceeded ${limitGB}GB memory limit. Auto-saving to prevent crash.`,
                    duration: 20000
                }]);
            }
            
            // Force save the recording
            try {
                this.finalizeDownload();
                
                // Stop recording after save
                if (this.roomWebRTC.videoRecorder?.isCurrentlyRecording()) {
                    this.roomWebRTC.videoRecorder.stopRecording();
                }
                
                throw new Error(`Recording memory limit exceeded (${limitGB}GB) - file auto-saved and recording stopped`);
            } catch (finalizeError) {
                console.error('🚨 Failed to auto-save recording:', finalizeError);
                throw finalizeError;
            }
        }
        
        // Also add to video recorder for compatibility
        this.roomWebRTC.videoRecorder.addRecordedChunk(newChunk);
        
        // Update status bar if it exists
        this.roomWebRTC.statusBarManager.updateRecordingStatus();
    }

    /**
     * Adds a chunk for local save (used in dual recording)
     */
    addChunk(blob, recordingData) {
        // Initialize download if not already active (for dual recording scenario)
        if (!this.isStreamingDownloadActive) {
            this.initializeDownload(recordingData.mime_type);
        }
        
        // Add the chunk
        this.updateDownload(blob);
        
        console.log(`💾 Local save chunk added: ${recordingData.filename} (part ${recordingData.partNumber})`);
    }

    /**
     * Finalizes streaming download by creating single combined file
     */
    finalizeDownload() {
        if (!this.recordingFilename || this.recordedChunks.length === 0) {
            console.warn('💾 No recording data to finalize');
            return;
        }
        
        // Create single combined file from all chunks
        const mimeType = this.roomWebRTC.videoRecorder.getRecordingMimeType();
        const combinedBlob = new Blob(this.recordedChunks, { type: mimeType });
        
        const url = URL.createObjectURL(combinedBlob);
        const link = document.createElement('a');
        link.href = url;
        link.download = this.recordingFilename;
        link.style.display = 'none';
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        URL.revokeObjectURL(url);
        
        console.log(`💾 Recording downloaded: ${this.recordingFilename} (${(combinedBlob.size / 1024 / 1024).toFixed(2)} MB)`);
        console.log(`💾 Combined from ${this.recordedChunks.length} chunks`);
        
        // Clean up
        this.recordedChunks = [];
        this.recordingFilename = null;
        this.isStreamingDownloadActive = false;
        this.totalRecordedSize = 0;
        this.hasShownMemoryWarning = false;
        
        // Update status bar
        this.roomWebRTC.statusBarManager.updateRecordingStatus();
    }

    /**
     * Downloads the complete recording as a single file (fallback method)
     */
    downloadCompleteRecording() {
        try {
            console.log('💾 Attempting to download complete recording...');
            console.log('💾 Storage provider:', this.roomWebRTC.roomData.recording_settings?.storage_provider);
            console.log('💾 Recorded chunks:', this.recordedChunks.length);
            
            if (this.recordedChunks.length === 0) {
                console.warn('💾 No recorded chunks to download');
                return;
            }

            // Combine all chunks into a single blob
            const mimeType = this.roomWebRTC.videoRecorder.getRecordingMimeType();
            const completeBlob = new Blob(this.recordedChunks, { type: mimeType });
            
            // Generate filename with timestamp
            const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
            const ext = mimeType.includes('webm') ? 'webm' : 'mp4';
            const filename = `room-recording-${timestamp}.${ext}`;
            
            // Create download link
            const url = URL.createObjectURL(completeBlob);
            const link = document.createElement('a');
            link.href = url;
            link.download = filename;
            
            // Trigger download
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            // Clean up
            URL.revokeObjectURL(url);
            this.recordedChunks = []; // Clear chunks after download
            this.totalRecordedSize = 0;
            this.hasShownMemoryWarning = false;
            
            console.log(`💾 Complete recording downloaded: ${filename} (${(completeBlob.size / 1024 / 1024).toFixed(2)} MB)`);
        } catch (error) {
            console.error('💾 Error downloading complete recording:', error);
        }
    }

    /**
     * Gets current download state
     */
    isDownloadActive() {
        return this.isStreamingDownloadActive;
    }

    /**
     * Gets recorded chunks
     */
    getRecordedChunks() {
        return this.recordedChunks;
    }

    /**
     * Gets recording filename
     */
    getRecordingFilename() {
        return this.recordingFilename;
    }

    /**
     * Resets download state
     */
    reset() {
        this.recordedChunks = [];
        this.recordingFilename = null;
        this.isStreamingDownloadActive = false;
        this.totalRecordedSize = 0;
        this.hasShownMemoryWarning = false;
    }
}
