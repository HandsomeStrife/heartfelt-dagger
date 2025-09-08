import { BaseUploader } from './BaseUploader.js';

/**
 * LocalUploader - Local device download implementation
 * 
 * Handles recording for local device download (streaming download during recording)
 */
export class LocalUploader extends BaseUploader {
    constructor(roomData, recordingSettings) {
        super(roomData, recordingSettings);
        
        // Local recording state
        this.recordedChunks = [];
        this.streamingDownloader = null;
    }

    /**
     * Get the provider name
     * @returns {string}
     */
    getProviderName() {
        return 'local_device';
    }

    /**
     * Set the streaming downloader reference
     * @param {Object} streamingDownloader - Reference to streaming downloader
     */
    setStreamingDownloader(streamingDownloader) {
        this.streamingDownloader = streamingDownloader;
    }

    /**
     * Initialize local recording (no external upload needed)
     * @param {Object} metadata - Recording metadata
     * @param {Blob} firstBlob - First video chunk
     */
    async initialize(metadata, firstBlob) {
        console.log('🎯 INITIALIZING LOCAL DEVICE RECORDING');
        
        this.currentRecordingFilename = metadata.filename;
        this.recordingStartedAt = metadata.started_at_ms || Date.now();
        this.isUploading = true; // "Uploading" to local device
        this.recordedChunks = [];
        
        // Initialize streaming download if available
        if (this.streamingDownloader) {
            this.streamingDownloader.initializeDownload(firstBlob.type);
        }
        
        console.log('🎯 LOCAL DEVICE RECORDING INITIALIZED');
    }

    /**
     * Process a video chunk for local download
     * @param {Blob} blob - Video chunk to process
     */
    async uploadChunk(blob) {
        console.log(`🎯 PROCESSING LOCAL CHUNK: ${blob.size} bytes`);
        
        // Store chunk locally
        this.recordedChunks.push(blob);
        this.uploadedBytes += blob.size;
        this.totalChunks++;
        
        // Update streaming download if available
        if (this.streamingDownloader) {
            this.streamingDownloader.updateDownload(blob);
        }
        
        // Emit progress event
        this.emitRecordingEvent('recording-upload-progress', {
            uploadedBytes: this.uploadedBytes,
            totalChunks: this.totalChunks
        });
    }

    /**
     * Finalize local recording (trigger download)
     */
    async finalize() {
        console.log('🎯 FINALIZING LOCAL DEVICE RECORDING');
        
        try {
            // Finalize streaming download if available
            if (this.streamingDownloader) {
                this.streamingDownloader.finalizeDownload();
            } else {
                // Fallback: create blob and trigger download
                await this.downloadRecording();
            }
            
            // Emit success event
            this.emitRecordingEvent('recording-upload-success', {
                filename: this.currentRecordingFilename,
                size_bytes: this.uploadedBytes,
                chunks: this.totalChunks
            });
            
        } catch (error) {
            console.error('🎯 ERROR finalizing local recording:', error);
            
            // Emit error event
            this.emitRecordingEvent('recording-upload-error', {
                filename: this.currentRecordingFilename,
                error: error.message
            });
            
            throw error;
        } finally {
            // Reset state
            this.reset();
            console.log('🎯 LOCAL DEVICE RECORDING FINALIZED');
        }
    }

    /**
     * Create and download the complete recording file
     */
    async downloadRecording() {
        if (this.recordedChunks.length === 0) {
            console.warn('🎯 NO CHUNKS TO DOWNLOAD');
            return;
        }
        
        // Determine MIME type and extension
        const mimeType = this.getCleanMimeType(this.recordedChunks[0]?.type);
        const ext = mimeType.includes('mp4') ? 'mp4' : 'webm';
        
        // Create combined blob
        const combinedBlob = new Blob(this.recordedChunks, { type: mimeType });
        
        // Create download link
        const url = URL.createObjectURL(combinedBlob);
        const link = document.createElement('a');
        link.href = url;
        link.download = this.currentRecordingFilename || `recording.${ext}`;
        link.style.display = 'none';
        
        // Trigger download
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Clean up
        URL.revokeObjectURL(url);
        
        console.log(`💾 Local recording downloaded: ${link.download} (${(combinedBlob.size / 1024 / 1024).toFixed(2)} MB)`);
    }

    /**
     * Abort local recording (just cleanup)
     */
    async abort() {
        console.log('🎯 ABORTING LOCAL DEVICE RECORDING');
        this.reset();
    }

    /**
     * Reset local recording state
     */
    reset() {
        super.reset();
        this.recordedChunks = [];
    }

    /**
     * Get current recorded chunks (for external access)
     * @returns {Array<Blob>}
     */
    getRecordedChunks() {
        return this.recordedChunks;
    }

    /**
     * Download current recording without stopping (partial download)
     */
    async downloadCurrentRecording() {
        if (this.recordedChunks.length === 0) {
            console.warn('💾 No current recording available for download');
            return;
        }
        
        // Create partial download with timestamp
        const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
        const mimeType = this.getCleanMimeType(this.recordedChunks[0]?.type);
        const ext = mimeType.includes('mp4') ? 'mp4' : 'webm';
        const filename = `room-recording-partial-${timestamp}.${ext}`;
        
        const combinedBlob = new Blob(this.recordedChunks, { type: mimeType });
        const url = URL.createObjectURL(combinedBlob);
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        link.style.display = 'none';
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        URL.revokeObjectURL(url);
        
        console.log(`💾 Partial recording downloaded: ${filename} (${(combinedBlob.size / 1024 / 1024).toFixed(2)} MB)`);
        console.log(`💾 Contains ${this.recordedChunks.length} chunks so far`);
    }

    /**
     * Get local recording-specific statistics
     * @returns {Object}
     */
    getUploadStats() {
        return {
            ...super.getUploadStats(),
            chunksStored: this.recordedChunks.length,
            totalSizeMB: (this.uploadedBytes / 1024 / 1024).toFixed(2)
        };
    }
}
