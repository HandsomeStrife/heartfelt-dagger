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
        
        console.log(`🎥 Streaming download initialized: ${this.recordingFilename}`);
        console.log(`🎥 Recording will be saved as single continuous file`);
    }

    /**
     * Collects chunks for single streaming download
     */
    updateDownload(newChunk) {
        if (!this.isStreamingDownloadActive) return;
        
        // Store chunk for continuous recording
        this.recordedChunks.push(newChunk);
        
        // Also add to video recorder for compatibility
        this.roomWebRTC.videoRecorder.addRecordedChunk(newChunk);
        
        const totalSize = this.recordedChunks.reduce((sum, chunk) => sum + chunk.size, 0);
        console.log(`📊 Recording chunk collected: ${(newChunk.size / 1024 / 1024).toFixed(2)} MB`);
        console.log(`🎥 Total recording size: ${(totalSize / 1024 / 1024).toFixed(2)} MB (${this.recordedChunks.length} chunks)`);
        
        // Update status bar if it exists
        this.roomWebRTC.statusBarManager.updateRecordingStatus();
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
    }
}
