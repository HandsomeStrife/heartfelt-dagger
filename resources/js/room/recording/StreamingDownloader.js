/**
 * StreamingDownloader - Manages local device streaming downloads
 * 
 * Provides VDO.ninja style single file downloads that stream content continuously
 * to prevent data loss if users leave early. Creates a single download that grows
 * over time rather than multiple chunk files.
 */
export class StreamingDownloader {
    constructor(roomWebRTC) {
        this.roomWebRTC = roomWebRTC;
        this.isStreamingDownloadActive = false;
        this.downloadLink = null;
        this.recordingFilename = null;
        this.writableStream = null;
        this.fileWriter = null;
        
        // Streaming configuration
        this.bufferSize = 1024 * 1024; // 1MB buffer
        this.flushInterval = 5000; // Flush every 5 seconds
        this.flushTimer = null;
        
        this.setupStreamingCapabilities();
    }

    /**
     * Sets up streaming download capabilities
     */
    setupStreamingCapabilities() {
        // Check for File System Access API support
        this.hasFileSystemAccess = 'showSaveFilePicker' in window;
        
        // Check for Streams API support
        this.hasStreamsAPI = 'WritableStream' in window;
        
        console.log('💾 Streaming download capabilities:', {
            fileSystemAccess: this.hasFileSystemAccess,
            streamsAPI: this.hasStreamsAPI,
            supportsStreaming: this.supportsStreaming()
        });
    }

    /**
     * Checks if streaming downloads are supported
     */
    supportsStreaming() {
        return this.hasFileSystemAccess && this.hasStreamsAPI;
    }

    /**
     * Initializes streaming download for local device recording
     */
    async initializeStreamingDownload() {
        if (this.isStreamingDownloadActive) {
            console.warn('💾 Streaming download already active');
            return;
        }

        console.log('💾 Initializing streaming download...');

        try {
            // Generate filename
            this.recordingFilename = this.generateRecordingFilename();
            
            if (this.supportsStreaming()) {
                await this.initializeFileSystemStreaming();
            } else {
                await this.initializeFallbackStreaming();
            }

            this.isStreamingDownloadActive = true;
            console.log('💾 ✅ Streaming download initialized');
        } catch (error) {
            console.error('💾 Failed to initialize streaming download:', error);
            throw error;
        }
    }

    /**
     * Initializes File System Access API streaming
     */
    async initializeFileSystemStreaming() {
        console.log('💾 Using File System Access API for streaming');
        
        try {
            // Show save file picker
            const fileHandle = await window.showSaveFilePicker({
                suggestedName: this.recordingFilename,
                types: [{
                    description: 'Video files',
                    accept: {
                        'video/webm': ['.webm'],
                        'video/mp4': ['.mp4']
                    }
                }]
            });

            // Create writable stream
            this.writableStream = await fileHandle.createWritable();
            console.log('💾 ✅ File system writable stream created');
            
            // Set up periodic flush
            this.setupPeriodicFlush();
        } catch (error) {
            if (error.name === 'AbortError') {
                console.log('💾 User cancelled file save dialog');
                throw new Error('File save cancelled by user');
            }
            throw error;
        }
    }

    /**
     * Initializes fallback streaming using Blob URLs
     */
    async initializeFallbackStreaming() {
        console.log('💾 Using fallback streaming with Blob URLs');
        
        // Create download link element
        this.downloadLink = document.createElement('a');
        this.downloadLink.style.display = 'none';
        document.body.appendChild(this.downloadLink);
        
        // Initialize empty blob
        this.recordingBlob = new Blob([], { type: this.roomWebRTC.recMime });
        this.updateDownloadLink();
        
        console.log('💾 ✅ Fallback streaming initialized');
    }

    /**
     * Sets up periodic flush for File System Access API
     */
    setupPeriodicFlush() {
        if (this.flushTimer) {
            clearInterval(this.flushTimer);
        }
        
        this.flushTimer = setInterval(async () => {
            if (this.writableStream) {
                try {
                    // Flush any pending writes
                    console.log('💾 Flushing stream buffer...');
                    // Note: The actual flush happens automatically with each write
                    // This is just for logging/monitoring
                } catch (error) {
                    console.error('💾 Error flushing stream:', error);
                }
            }
        }, this.flushInterval);
    }

    /**
     * Updates streaming download with new chunk
     */
    async updateStreamingDownload(newChunk) {
        if (!this.isStreamingDownloadActive) {
            console.warn('💾 Streaming download not active');
            return;
        }
        
        try {
            if (this.writableStream) {
                // File System Access API - write directly to file
                await this.writableStream.write(newChunk);
                console.log(`💾 Chunk written to file: ${(newChunk.size / 1024 / 1024).toFixed(2)} MB`);
            } else {
                // Fallback - accumulate in memory and update blob URL
                await this.updateFallbackDownload(newChunk);
            }
            
            // Store chunk for page protection
            this.roomWebRTC.recordedChunks.push(newChunk);
            
            // Update page protection
            this.roomWebRTC.pageProtection.updateRecordingState(
                this.roomWebRTC.isRecording, 
                this.roomWebRTC.recordedChunks, 
                this.roomWebRTC.recMime
            );
            
            // Update status
            const totalSize = this.roomWebRTC.recordedChunks.reduce((sum, chunk) => sum + chunk.size, 0);
            console.log(`💾 Total recording size: ${(totalSize / 1024 / 1024).toFixed(2)} MB (${this.roomWebRTC.recordedChunks.length} chunks)`);
            
            // Update status bar
            this.roomWebRTC.statusBarManager.updateRecordingStatus({
                totalSize: totalSize,
                chunkCount: this.roomWebRTC.recordedChunks.length
            });
        } catch (error) {
            console.error('💾 Error updating streaming download:', error);
            throw error;
        }
    }

    /**
     * Updates fallback download with new chunk
     */
    async updateFallbackDownload(newChunk) {
        // Create new blob with accumulated data
        const chunks = [...this.roomWebRTC.recordedChunks, newChunk];
        this.recordingBlob = new Blob(chunks, { type: this.roomWebRTC.recMime });
        
        // Update download link
        this.updateDownloadLink();
        
        console.log(`💾 Fallback blob updated: ${(this.recordingBlob.size / 1024 / 1024).toFixed(2)} MB`);
    }

    /**
     * Updates the download link for fallback method
     */
    updateDownloadLink() {
        if (!this.downloadLink || !this.recordingBlob) return;
        
        // Revoke previous URL to prevent memory leaks
        if (this.downloadLink.href && this.downloadLink.href.startsWith('blob:')) {
            URL.revokeObjectURL(this.downloadLink.href);
        }
        
        // Create new blob URL
        const url = URL.createObjectURL(this.recordingBlob);
        this.downloadLink.href = url;
        this.downloadLink.download = this.recordingFilename;
    }

    /**
     * Finalizes streaming download
     */
    async finalizeStreamingDownload() {
        if (!this.isStreamingDownloadActive) {
            console.warn('💾 No active streaming download to finalize');
            return;
        }

        console.log('💾 Finalizing streaming download...');

        try {
            if (this.writableStream) {
                // Close File System Access API stream
                await this.writableStream.close();
                console.log('💾 ✅ File system stream closed');
            } else if (this.downloadLink && this.recordingBlob) {
                // Trigger fallback download
                this.downloadLink.click();
                console.log('💾 ✅ Fallback download triggered');
            }
            
            // Clean up
            this.cleanupStreamingDownload();
            
            console.log('💾 ✅ Streaming download finalized');
        } catch (error) {
            console.error('💾 Error finalizing streaming download:', error);
            // Try emergency save
            this.emergencyDownload();
        }
    }

    /**
     * Performs emergency download if finalization fails
     */
    emergencyDownload() {
        console.warn('💾 🚨 Performing emergency download...');
        
        try {
            if (this.roomWebRTC.recordedChunks.length === 0) {
                console.warn('💾 No chunks to download');
                return;
            }
            
            // Create emergency blob
            const emergencyBlob = new Blob(this.roomWebRTC.recordedChunks, { 
                type: this.roomWebRTC.recMime 
            });
            
            // Create emergency download
            const emergencyUrl = URL.createObjectURL(emergencyBlob);
            const emergencyLink = document.createElement('a');
            emergencyLink.href = emergencyUrl;
            emergencyLink.download = this.recordingFilename.replace('.', '-EMERGENCY.');
            emergencyLink.style.display = 'none';
            
            document.body.appendChild(emergencyLink);
            emergencyLink.click();
            document.body.removeChild(emergencyLink);
            
            // Clean up URL
            setTimeout(() => URL.revokeObjectURL(emergencyUrl), 1000);
            
            console.warn('💾 🚨 Emergency download completed');
        } catch (error) {
            console.error('💾 🚨 Emergency download failed:', error);
        }
    }

    /**
     * Generates recording filename
     */
    generateRecordingFilename() {
        const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
        const roomName = this.roomWebRTC.roomData.name?.replace(/[^a-zA-Z0-9]/g, '-') || 'room';
        const ext = this.roomWebRTC.recMime && this.roomWebRTC.recMime.includes('webm') ? 'webm' : 'mp4';
        
        return `${roomName}-recording-${timestamp}.${ext}`;
    }

    /**
     * Gets download progress information
     */
    getDownloadProgress() {
        const totalSize = this.roomWebRTC.recordedChunks.reduce((sum, chunk) => sum + chunk.size, 0);
        const chunkCount = this.roomWebRTC.recordedChunks.length;
        const duration = this.roomWebRTC.recordingStartTime ? 
            Date.now() - this.roomWebRTC.recordingStartTime : 0;
        
        return {
            isActive: this.isStreamingDownloadActive,
            filename: this.recordingFilename,
            totalSize: totalSize,
            chunkCount: chunkCount,
            duration: duration,
            averageChunkSize: chunkCount > 0 ? totalSize / chunkCount : 0,
            recordingRate: duration > 0 ? totalSize / (duration / 1000) : 0, // bytes per second
            method: this.writableStream ? 'file-system-access' : 'blob-url'
        };
    }

    /**
     * Cleans up streaming download resources
     */
    cleanupStreamingDownload() {
        console.log('💾 Cleaning up streaming download resources');
        
        // Clear flush timer
        if (this.flushTimer) {
            clearInterval(this.flushTimer);
            this.flushTimer = null;
        }
        
        // Clean up File System Access API resources
        if (this.writableStream) {
            // Stream should already be closed in finalize
            this.writableStream = null;
        }
        
        // Clean up fallback resources
        if (this.downloadLink) {
            if (this.downloadLink.href && this.downloadLink.href.startsWith('blob:')) {
                URL.revokeObjectURL(this.downloadLink.href);
            }
            if (this.downloadLink.parentNode) {
                this.downloadLink.parentNode.removeChild(this.downloadLink);
            }
            this.downloadLink = null;
        }
        
        // Clear state
        this.isStreamingDownloadActive = false;
        this.recordingFilename = null;
        this.recordingBlob = null;
        
        console.log('💾 ✅ Streaming download cleanup completed');
    }

    /**
     * Destroys the streaming downloader
     */
    destroy() {
        console.log('💾 Destroying StreamingDownloader');
        
        if (this.isStreamingDownloadActive) {
            this.finalizeStreamingDownload();
        } else {
            this.cleanupStreamingDownload();
        }
        
        console.log('💾 StreamingDownloader destroyed');
    }
}
