/**
 * VideoRecorder - Core video recording functionality
 * 
 * Handles MediaRecorder API operations, recording state management, chunking,
 * and coordinates with StreamingDownloader and CloudUploader for output.
 */
export class VideoRecorder {
    constructor(roomWebRTC) {
        this.roomWebRTC = roomWebRTC;
        this.mediaRecorder = null;
        this.recordedChunks = [];
        this.recordingStartTime = null;
        this.isRecording = false;
        this.recordingBlob = null;
        
        // Recording configuration
        this.recMime = null; // Determined at runtime
        this.chunkInterval = 30000; // 30 seconds for cloud uploads
        this.supportedMimeTypes = [
            'video/webm;codecs=vp9,opus',
            'video/webm;codecs=vp8,opus', 
            'video/webm;codecs=h264,opus',
            'video/webm',
            'video/mp4'
        ];
        
        this.setupEventHandlers();
    }

    /**
     * Sets up event handlers for recording management
     */
    setupEventHandlers() {
        // Listen for media stream changes
        // This could be expanded to handle stream updates
    }

    /**
     * Initializes video recording with optimal MIME type
     */
    initializeRecording() {
        console.log('🎥 Initializing video recording...');
        
        // Determine best MIME type
        this.recMime = this.determineBestMimeType();
        console.log(`🎥 Selected MIME type: ${this.recMime}`);
        
        // Log recording capabilities
        this.logRecordingCapabilities();
    }

    /**
     * Determines the best available MIME type for recording
     */
    determineBestMimeType() {
        for (const mimeType of this.supportedMimeTypes) {
            if (MediaRecorder.isTypeSupported(mimeType)) {
                console.log(`🎥 ✅ MIME type supported: ${mimeType}`);
                return mimeType;
            } else {
                console.log(`🎥 ❌ MIME type not supported: ${mimeType}`);
            }
        }
        
        // Fallback to default
        console.warn('🎥 ⚠️ No preferred MIME types supported, using default');
        return 'video/webm';
    }

    /**
     * Logs recording capabilities for debugging
     */
    logRecordingCapabilities() {
        console.log('🎥 === Recording Capabilities ===');
        console.log(`🎥 MediaRecorder supported: ${typeof MediaRecorder !== 'undefined'}`);
        console.log(`🎥 Selected MIME type: ${this.recMime}`);
        console.log(`🎥 Chunk interval: ${this.chunkInterval}ms`);
        
        // Test recording options
        const testOptions = { mimeType: this.recMime };
        try {
            const testRecorder = new MediaRecorder(new MediaStream(), testOptions);
            console.log('🎥 ✅ MediaRecorder can be created with selected options');
            testRecorder = null; // Clean up
        } catch (error) {
            console.warn('🎥 ⚠️ MediaRecorder creation test failed:', error);
        }
    }

    /**
     * Starts video recording
     */
    async startRecording(storageProvider = 'local_device') {
        if (this.isRecording) {
            console.warn('🎥 Recording already in progress');
            return false;
        }

        if (!this.roomWebRTC.localStream) {
            console.error('🎥 No local stream available for recording');
            throw new Error('No local stream available for recording');
        }

        console.log(`🎥 Starting video recording with storage: ${storageProvider}`);

        try {
            // Initialize recording if not done
            if (!this.recMime) {
                this.initializeRecording();
            }

            // Create MediaRecorder
            this.mediaRecorder = new MediaRecorder(this.roomWebRTC.localStream, { 
                mimeType: this.recMime 
            });
            
            this.recordingStartTime = Date.now();
            this.isRecording = true;
            this.recordedChunks = [];
            
            // Update page protection
            this.roomWebRTC.pageProtection.updateRecordingState(
                this.isRecording, 
                this.recordedChunks, 
                this.recMime
            );

            // Set up MediaRecorder event handlers
            this.setupMediaRecorderHandlers(storageProvider);

            // Start recording based on storage provider
            if (storageProvider === 'local_device') {
                // For local device, record continuously
                this.mediaRecorder.start();
                console.log('🎥 Video recording started for local device download');
                
                // Initialize streaming download
                this.roomWebRTC.streamingDownloader?.initializeStreamingDownload();
            } else {
                // For cloud storage, use chunked recording
                this.mediaRecorder.start(this.chunkInterval);
                console.log(`🎥 Video recording started with ${this.chunkInterval}ms chunks for cloud upload`);
            }

            // Show status bar
            this.roomWebRTC.statusBarManager.showRecordingStatusBar();
            this.updateRecordingUI(true);

            return true;
        } catch (error) {
            console.error('🎥 Error starting MediaRecorder:', error);
            this.isRecording = false;
            this.roomWebRTC.pageProtection.updateRecordingState(
                this.isRecording, 
                this.recordedChunks, 
                this.recMime
            );
            throw error;
        }
    }

    /**
     * Sets up MediaRecorder event handlers
     */
    setupMediaRecorderHandlers(storageProvider) {
        // Handle recording stop event
        this.mediaRecorder.onstop = () => {
            console.log('🎥 MediaRecorder stopped event triggered');
            console.log('🎥 Storage provider on stop:', storageProvider);
            
            if (storageProvider === 'local_device') {
                // Finalize streaming download
                this.roomWebRTC.streamingDownloader?.finalizeStreamingDownload();
            } else {
                // Handle final chunk for cloud upload
                if (this.recordedChunks.length > 0) {
                    this.processRecordingChunk(this.recordedChunks[this.recordedChunks.length - 1], storageProvider);
                }
            }
            
            this.updateRecordingUI(false);
            this.roomWebRTC.statusBarManager.hideRecordingStatusBar();
            console.log('🎥 Video recording stopped');
        };

        // Handle data available event
        this.mediaRecorder.ondataavailable = (event) => {
            if (event.data && event.data.size > 0) {
                console.log(`🎥 Recording chunk available: ${(event.data.size / 1024 / 1024).toFixed(2)} MB`);
                
                if (storageProvider === 'local_device') {
                    // For local device, add to streaming download
                    this.roomWebRTC.streamingDownloader?.updateStreamingDownload(event.data);
                } else {
                    // For cloud storage, process chunk
                    this.processRecordingChunk(event.data, storageProvider);
                }
            }
        };

        // Handle errors
        this.mediaRecorder.onerror = (event) => {
            console.error('🎥 MediaRecorder error:', event.error);
            this.handleRecordingError(event.error);
        };

        // Handle start event
        this.mediaRecorder.onstart = () => {
            console.log('🎥 MediaRecorder started successfully');
        };

        // Handle pause event
        this.mediaRecorder.onpause = () => {
            console.log('🎥 MediaRecorder paused');
        };

        // Handle resume event
        this.mediaRecorder.onresume = () => {
            console.log('🎥 MediaRecorder resumed');
        };
    }

    /**
     * Processes a recording chunk
     */
    async processRecordingChunk(chunk, storageProvider) {
        // Store chunk
        this.recordedChunks.push(chunk);
        
        // Update page protection
        this.roomWebRTC.pageProtection.updateRecordingState(
            this.isRecording, 
            this.recordedChunks, 
            this.recMime
        );

        if (storageProvider !== 'local_device') {
            // For cloud storage, upload chunk
            try {
                const recordingData = this.generateRecordingMetadata(chunk);
                await this.roomWebRTC.cloudUploader?.uploadVideoChunk(chunk, recordingData);
            } catch (error) {
                console.error('🎥 Failed to upload chunk:', error);
                // Continue recording even if upload fails
            }
        }

        // Update status bar
        const totalSize = this.recordedChunks.reduce((sum, chunk) => sum + chunk.size, 0);
        this.roomWebRTC.statusBarManager.updateRecordingStatus({
            totalSize: totalSize,
            chunkCount: this.recordedChunks.length
        });
    }

    /**
     * Generates metadata for recording chunks
     */
    generateRecordingMetadata(chunk) {
        const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
        const chunkIndex = this.recordedChunks.length;
        const ext = this.recMime && this.recMime.includes('webm') ? 'webm' : 'mp4';
        
        return {
            filename: `room-${this.roomWebRTC.roomData.id}-chunk-${chunkIndex}-${timestamp}.${ext}`,
            mimeType: this.recMime,
            chunkIndex: chunkIndex,
            timestamp: timestamp,
            roomId: this.roomWebRTC.roomData.id,
            userId: this.roomWebRTC.currentUserId,
            size: chunk.size
        };
    }

    /**
     * Stops video recording
     */
    stopRecording() {
        if (!this.mediaRecorder || !this.isRecording) {
            console.warn('🎥 No active recording to stop');
            return false;
        }

        console.log('🎥 Stopping video recording...');
        
        this.isRecording = false;
        
        // Update page protection
        this.roomWebRTC.pageProtection.updateRecordingState(
            this.isRecording, 
            this.recordedChunks, 
            this.recMime
        );

        try {
            this.mediaRecorder.stop(); // This will trigger onstop event
            console.log('🎥 MediaRecorder stop() called');
            return true;
        } catch (error) {
            console.warn('🎥 Error stopping MediaRecorder:', error);
            // Force cleanup
            this.cleanupRecording();
            return false;
        }
    }

    /**
     * Pauses video recording
     */
    pauseRecording() {
        if (!this.mediaRecorder || !this.isRecording) {
            console.warn('🎥 No active recording to pause');
            return false;
        }

        if (this.mediaRecorder.state === 'recording') {
            this.mediaRecorder.pause();
            console.log('🎥 Recording paused');
            return true;
        }

        return false;
    }

    /**
     * Resumes video recording
     */
    resumeRecording() {
        if (!this.mediaRecorder || !this.isRecording) {
            console.warn('🎥 No active recording to resume');
            return false;
        }

        if (this.mediaRecorder.state === 'paused') {
            this.mediaRecorder.resume();
            console.log('🎥 Recording resumed');
            return true;
        }

        return false;
    }

    /**
     * Handles recording errors
     */
    handleRecordingError(error) {
        console.error('🎥 Recording error:', error);
        
        // Stop recording on error
        this.isRecording = false;
        this.cleanupRecording();
        
        // Update UI
        this.updateRecordingUI(false);
        this.roomWebRTC.statusBarManager.hideRecordingStatusBar();
        
        // Show error to user
        this.showRecordingError(error);
    }

    /**
     * Shows recording error to user
     */
    showRecordingError(error) {
        const errorMessage = `Recording failed: ${error.message || 'Unknown error'}`;
        console.error('🎥 ' + errorMessage);
        
        // This could integrate with a notification system
        // For now, just log the error
    }

    /**
     * Updates recording UI state
     */
    updateRecordingUI(isRecording) {
        // Find recording buttons and update their state
        const startButtons = document.querySelectorAll('.start-recording-btn, [data-start-recording]');
        const stopButtons = document.querySelectorAll('.stop-recording-btn, [data-stop-recording]');
        
        startButtons.forEach(btn => {
            btn.disabled = isRecording;
            btn.style.display = isRecording ? 'none' : '';
        });
        
        stopButtons.forEach(btn => {
            btn.disabled = !isRecording;
            btn.style.display = isRecording ? '' : 'none';
        });
        
        // Update recording indicators
        const indicators = document.querySelectorAll('.recording-indicator, [data-recording-indicator]');
        indicators.forEach(indicator => {
            if (isRecording) {
                indicator.classList.add('active', 'animate-pulse');
                indicator.classList.remove('hidden');
            } else {
                indicator.classList.remove('active', 'animate-pulse');
                indicator.classList.add('hidden');
            }
        });
    }

    /**
     * Gets current recording state
     */
    getRecordingState() {
        return {
            isRecording: this.isRecording,
            startTime: this.recordingStartTime,
            duration: this.recordingStartTime ? Date.now() - this.recordingStartTime : 0,
            chunksCount: this.recordedChunks.length,
            totalSize: this.recordedChunks.reduce((sum, chunk) => sum + chunk.size, 0),
            mimeType: this.recMime,
            mediaRecorderState: this.mediaRecorder?.state || 'inactive'
        };
    }

    /**
     * Gets recording statistics
     */
    getRecordingStats() {
        const state = this.getRecordingState();
        
        return {
            ...state,
            averageChunkSize: state.chunksCount > 0 ? state.totalSize / state.chunksCount : 0,
            recordingRate: state.duration > 0 ? state.totalSize / (state.duration / 1000) : 0, // bytes per second
            estimatedBitrate: state.duration > 0 ? (state.totalSize * 8) / (state.duration / 1000) : 0 // bits per second
        };
    }

    /**
     * Cleans up recording resources
     */
    cleanupRecording() {
        console.log('🎥 Cleaning up recording resources');
        
        if (this.mediaRecorder) {
            // Remove event listeners to prevent memory leaks
            this.mediaRecorder.onstop = null;
            this.mediaRecorder.ondataavailable = null;
            this.mediaRecorder.onerror = null;
            this.mediaRecorder.onstart = null;
            this.mediaRecorder.onpause = null;
            this.mediaRecorder.onresume = null;
            
            this.mediaRecorder = null;
        }
        
        // Clear recording data
        this.recordedChunks = [];
        this.recordingStartTime = null;
        this.recordingBlob = null;
        
        // Update page protection
        this.roomWebRTC.pageProtection.updateRecordingState(false, [], null);
    }

    /**
     * Destroys the video recorder
     */
    destroy() {
        console.log('🎥 Destroying VideoRecorder');
        
        if (this.isRecording) {
            this.stopRecording();
        }
        
        this.cleanupRecording();
        console.log('🎥 VideoRecorder destroyed');
    }
}
