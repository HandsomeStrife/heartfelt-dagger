import Uppy from '@uppy/core';
import Dashboard from '@uppy/dashboard';
import AwsS3 from '@uppy/aws-s3';
import XHRUpload from '@uppy/xhr-upload';
import GoldenRetriever from '@uppy/golden-retriever';
import { UploaderFactory } from './room/uploaders/UploaderFactory.js';

/**
 * RoomUppy - Advanced upload handling for room video recording
 * 
 * Features:
 * - Multiple storage providers (Wasabi S3, Google Drive, Local)
 * - Crash recovery with service worker support  
 * - Chunked video upload with progress tracking
 * - Backpressure handling and queue management
 * - Event-driven upload status notifications
 */
class RoomUppy {
    constructor(roomData, recordingSettings) {
        this.roomData = roomData;
        this.recordingSettings = recordingSettings;
        this.uppy = null;
        this.uploadStrategy = this.recordingSettings?.storage_provider || 'local_device';
        
        // Create provider-specific uploader
        this.uploader = UploaderFactory.createUploader(
            this.uploadStrategy, 
            this.roomData, 
            this.recordingSettings
        );
        
        this.initializeUppy();
    }

    // ===========================================
    // UPPY CORE INITIALIZATION
    // ===========================================

    /**
     * Initializes Uppy with room-specific configuration and storage strategy
     */
    initializeUppy() {
        this.uppy = new Uppy({
            id: `room-${this.roomData.id}-uploader`,
            autoProceed: true, // Auto-start uploads when files are added
            allowMultipleUploadBatches: true,
            debug: process.env.NODE_ENV === 'development',
            restrictions: {
                maxFileSize: null, // Remove size limit for high-quality recordings
                maxNumberOfFiles: 1000, // Higher limit since we clean up completed files
                allowedFileTypes: ['video/webm', 'video/mp4', 'video/quicktime'],
            },
            meta: {
                room_id: this.roomData.id,
                user_id: window.currentUserId,
            },
        });

        // Configure upload strategy based on room settings
        this.configureUploadStrategy();

        // Add progress and completion handlers
        this.setupEventHandlers();

        // Add crash recovery (disable service worker until we register one)
        this.uppy.use(GoldenRetriever, {
            serviceWorker: false, // Disable until we register uppy-sw.js
        });

        console.log(`🎬 Uppy initialized for room ${this.roomData.id} with ${this.uploadStrategy} storage`);
    }

    // ===========================================
    // UPLOAD STRATEGY CONFIGURATION
    // ===========================================

    /**
     * Configures the appropriate upload strategy based on room settings
     */
    configureUploadStrategy() {
        switch (this.uploadStrategy) {
            case 'wasabi':
                this.configureWasabiUpload();
                break;
            case 'google_drive':
                this.configureGoogleDriveUpload();
                break;
            default:
                this.configureLocalUpload();
        }
    }

    /**
     * Configures Wasabi S3-compatible upload (now bypassing Uppy for continuous recording)
     */
    configureWasabiUpload() {
        // For continuous recording, we handle multipart uploads directly
        // Uppy is only used for single file uploads (if needed)
        console.log('🎯 Wasabi upload configured for direct multipart handling');
    }

    /**
     * Confirm Wasabi upload completion and create database record (for single-part uploads only)
     */
    async confirmWasabiUpload(file, response) {
        try {
            console.log('✅ Wasabi single-part upload completed, confirming with server...');

            const confirmResponse = await fetch(`/api/rooms/${this.roomData.id}/recordings/confirm-wasabi`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify({
                    provider_file_id: file.meta.wasabiKey,
                    filename: file.name,
                    size_bytes: file.size,
                    started_at_ms: file.meta.started_at_ms,
                    ended_at_ms: file.meta.ended_at_ms,
                    mime_type: file.type
                })
            });

            if (confirmResponse.ok) {
                const confirmData = await confirmResponse.json();
                console.log('✅ Wasabi upload confirmed:', confirmData);
                
                // Emit success event
                this.emitRecordingEvent('recording-upload-success', {
                    provider: 'wasabi',
                    recording_id: confirmData.recording_id,
                    provider_file_id: file.meta.wasabiKey,
                    filename: file.name,
                });
            } else {
                console.error('❌ Failed to confirm Wasabi upload:', confirmResponse.status);
                this.emitRecordingEvent('recording-upload-error', {
                    provider: 'wasabi',
                    filename: file.name,
                    error: 'Failed to confirm upload'
                });
            }
        } catch (error) {
            console.error('❌ Error confirming Wasabi upload:', error);
            this.emitRecordingEvent('recording-upload-error', {
                provider: 'wasabi',
                filename: file.name,
                error: error.message
            });
        }
    }



    /**
     * Configures Google Drive direct upload (bypasses server for better bandwidth efficiency)
     */
    configureGoogleDriveUpload() {
        // Capture roomData reference for use in callbacks
        const roomData = this.roomData;
        
        this.uppy.use(XHRUpload, {
            id: 'GoogleDriveDirectXHR',
            // Custom endpoint configuration for direct upload
            getUploadParameters: async (file) => {
                try {
                    // Step 1: Get direct upload URL from our server
                    const response = await fetch(`/api/rooms/${roomData.id}/recordings/google-drive-upload-url`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        },
                        body: JSON.stringify({
                            filename: file.name,
                            content_type: file.type,
                            size: file.size,
                            metadata: {
                                started_at_ms: file.meta.started_at_ms,
                                ended_at_ms: file.meta.ended_at_ms,
                            }
                        })
                    });

                    if (!response.ok) {
                        throw new Error(`Failed to get upload URL: ${response.status}`);
                    }

                    const data = await response.json();
                    
                    // Store metadata for confirmation step
                    file.meta.googleDriveSession = data.session_uri;
                    file.meta.uploadMetadata = data.metadata;
                    
                    console.log('📤 Got Google Drive direct upload URL:', data.upload_url);

                    // Return parameters for direct upload to Google Drive
                    return {
                        method: 'PUT',
                        url: data.upload_url,
                        headers: {
                            'Content-Type': file.type,
                        },
                        // Don't use form data for Google Drive resumable uploads
                        formData: false,
                    };
                } catch (error) {
                    console.error('❌ Failed to get Google Drive upload URL:', error);
                    throw error;
                }
            },

            // Custom response handler
            getResponseData: (responseText, response) => {
                // Google Drive returns empty body on success (200/201)
                if (response.status >= 200 && response.status < 300) {
                    return { success: true };
                }
                
                try {
                    return JSON.parse(responseText);
                } catch (error) {
                    return { success: false, error: `Upload failed with status ${response.status}` };
                }
            }
        });

        // Add success handler to confirm upload with our server
        this.uppy.on('upload-success', async (file, response) => {
            if (file.meta.googleDriveSession) {
                try {
                    console.log('✅ Google Drive upload completed, confirming with server...');
                    
                    // Step 2: Confirm upload completion with our server
                    const confirmResponse = await fetch(`/api/rooms/${this.roomData.id}/recordings/confirm-google-drive`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        },
                        body: JSON.stringify({
                            session_uri: file.meta.googleDriveSession,
                            metadata: file.meta.uploadMetadata || {}
                        })
                    });

                    if (confirmResponse.ok) {
                        const confirmData = await confirmResponse.json();
                        console.log('✅ Google Drive upload confirmed:', confirmData);
                        
                        // Emit custom success event with recording info
                        this.emitRecordingEvent('recording-upload-success', {
                            provider: 'google_drive',
                            recording_id: confirmData.recording_id,
                            provider_file_id: confirmData.provider_file_id,
                            filename: file.name,
                            web_view_link: confirmData.web_view_link,
                        });
                    } else {
                        console.error('❌ Failed to confirm Google Drive upload:', confirmResponse.status);
                        this.emitRecordingEvent('recording-upload-error', {
                            provider: 'google_drive',
                            filename: file.name,
                            error: 'Failed to confirm upload'
                        });
                    }
                } catch (error) {
                    console.error('❌ Error confirming Google Drive upload:', error);
                    this.emitRecordingEvent('recording-upload-error', {
                        provider: 'google_drive',
                        filename: file.name,
                        error: error.message
                    });
                }
            }
        });
    }

    /**
     * Configures local server upload via XHR
     */
    configureLocalUpload() {
        this.uppy.use(XHRUpload, {
            id: 'LocalXHR',
            endpoint: `/api/rooms/${this.roomData.id}/recordings`,
            method: 'POST',
            formData: true,
            fieldName: 'video',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            },
            getResponseData: (responseText, response) => {
                try {
                    return JSON.parse(responseText);
                } catch (error) {
                    console.error('Failed to parse local upload response:', error);
                    return { success: false, error: 'Invalid response format' };
                }
            }
        });
    }

    // ===========================================
    // EVENT HANDLING & PROGRESS TRACKING
    // ===========================================

    /**
     * Sets up comprehensive event handlers for upload lifecycle
     */
    setupEventHandlers() {
        this.uppy.on('file-added', (file) => {
            console.log('🎯 FILE ADDED TO QUEUE:', file.name, 'Size:', file.size, 'ID:', file.id);
            console.log('🎯 CURRENT QUEUE SIZE:', this.uppy.getFiles().length);
        });

        this.uppy.on('upload-progress', (file, progress) => {
            const percentage = Math.round((progress.bytesUploaded / progress.bytesTotal) * 100);
            this.updateUploadProgress(file.id, percentage);
        });

        this.uppy.on('upload-success', (file, response) => {
            console.log('🎯 S3 UPLOAD SUCCESS:', file.name);
            console.log('🎯 S3 Response Body:', JSON.stringify(response.body, null, 2));
            console.log('🎯 S3 Response Status:', response.status);
            console.log('🎯 S3 Upload URL:', response.uploadURL);
            
            // Track successful upload in VideoRecorder
            if (window.roomWebRTC?.videoRecorder?.trackSuccessfulUpload) {
                window.roomWebRTC.videoRecorder.trackSuccessfulUpload(file.size);
            }
            
            // Update recording progress in database
            this.updateRecordingProgress(file, response);
            
            this.handleUploadSuccess(file, response);
        });

        this.uppy.on('upload-error', (file, error, response) => {
            console.error('❌ Upload failed for:', file.name, error);
            this.handleUploadError(file, error, response);
        });

        this.uppy.on('complete', (result) => {
            console.log('🎉 All uploads completed:', result);
            this.handleUploadComplete(result);
        });
    }

    // ===========================================
    // VIDEO BLOB UPLOAD MANAGEMENT
    // ===========================================

    /**
     * Uploads a video blob using the configured storage provider
     */
    async uploadVideoBlob(blob, metadata) {
        console.log('🎯 UPLOADING VIDEO CHUNK:', blob.size, 'bytes');
        console.log('🎯 STORAGE PROVIDER:', this.uploadStrategy);
        
        try {
            // Initialize uploader if this is the first chunk
            if (!this.uploader.isCurrentlyUploading()) {
                await this.uploader.initialize(metadata, blob);
            }
            
            // Upload the chunk
            await this.uploader.uploadChunk(blob);
            
        } catch (error) {
            console.error('🎯 ERROR uploading video chunk:', error);
            throw error;
        }
    }




    /**
     * Finalizes the upload when recording stops
     */
    async finalizeMultipartUpload() {
        try {
            await this.uploader.finalize();
            console.log(`🎯 ${this.uploadStrategy.toUpperCase()} UPLOAD FINALIZED`);
        } catch (error) {
            console.error(`🎯 ERROR finalizing ${this.uploadStrategy} upload:`, error);
            // Try to abort on error
            await this.uploader.abort();
            throw error;
        }
    }



    updateUploadProgress(fileId, percentage) {
        // Update UI progress indicators
        const progressEvent = new CustomEvent('recording-upload-progress', {
            detail: { fileId, percentage }
        });
        document.dispatchEvent(progressEvent);
    }


    /**
     * Emits a custom recording event to the document
     */
    emitRecordingEvent(eventType, detail) {
        const event = new CustomEvent(eventType, { detail });
        document.dispatchEvent(event);
    }

    async handleUploadSuccess(file, response) {
        try {
            // Handle Wasabi uploads (both single-part and multipart)
            if (this.uploadStrategy === 'wasabi') {
                // Multipart uploads: server already created DB record in /complete endpoint
                // The response.body contains the recording info from our server
                if (response?.body?.key) {
                    console.log('✅ Wasabi multipart upload completed with server response:', response.body);
                    
                    this.emitRecordingEvent('recording-upload-success', {
                        provider: 'wasabi',
                        recording_id: response.body.recording_id,
                        provider_file_id: response.body.key,
                        filename: file.name,
                    });
                } else {
                    // Single-part uploads: confirm now using the cached metadata
                    console.log('✅ Wasabi single-part upload completed, confirming...');
                    await this.confirmWasabiUpload(file, response);
                }
            } else {
                // For non-Wasabi uploads, use the general success event
                const successEvent = new CustomEvent('recording-upload-success', {
                    detail: { 
                        file, 
                        response,
                        provider_file_id: file.meta.key || response.body?.key || file.name,
                        recording_id: response.body?.recording_id
                    }
                });
                document.dispatchEvent(successEvent);
            }

            // Clean up: remove file from Uppy queue
            this.uppy.removeFile(file.id);

        } catch (error) {
            console.error('Error handling upload success:', error);
            this.handleUploadError(file, error, response);
        }
    }

    handleUploadError(file, error, response) {
        // Notify UI of upload error
        const errorEvent = new CustomEvent('recording-upload-error', {
            detail: { file, error, response }
        });
        document.dispatchEvent(errorEvent);
    }

    handleUploadComplete(result) {
        // Notify UI that all uploads are complete
        const completeEvent = new CustomEvent('recording-upload-complete', {
            detail: result
        });
        document.dispatchEvent(completeEvent);
    }

    // ===========================================
    // UTILITY METHODS
    // ===========================================


    // ===========================================
    // QUEUE MANAGEMENT & UTILITIES
    // ===========================================

    /**
     * Pauses all active uploads
     */
    pause() {
        this.uppy.pauseAll();
    }

    /**
     * Resumes all paused uploads  
     */
    resume() {
        this.uppy.resumeAll();
    }

    /**
     * Cancels all uploads and clears queue
     */
    cancel() {
        this.uppy.cancelAll();
    }

    /**
     * Resets Uppy state and clears all files
     */
    reset() {
        this.uppy.reset();
    }

    /**
     * Destroys Uppy instance and cleans up resources
     */
    destroy() {
        if (this.uppy) {
            this.uppy.destroy();
            this.uppy = null;
        }
    }

    /**
     * Gets current upload queue state
     */
    getState() {
        return this.uppy.getState();
    }

    /**
     * Checks if any uploads are currently in progress
     */
    isUploading() {
        // Check both Uppy queue and our provider uploader
        const uppyState = this.getState();
        const uppyUploading = Object.keys(uppyState.files).some(fileId => {
            const file = uppyState.files[fileId];
            return file.progress && file.progress.uploadStarted && !file.progress.uploadComplete;
        });
        
        return uppyUploading || this.uploader.isCurrentlyUploading();
    }

    /**
     * Get current upload statistics from the provider uploader
     * @returns {Object}
     */
    getUploadStats() {
        return this.uploader.getUploadStats();
    }

    /**
     * Get the current uploader instance (for external access)
     * @returns {BaseUploader}
     */
    getCurrentUploader() {
        return this.uploader;
    }

    /**
     * Set streaming downloader reference for local uploads
     * @param {Object} streamingDownloader
     */
    setStreamingDownloader(streamingDownloader) {
        if (this.uploader.getProviderName() === 'local_device') {
            this.uploader.setStreamingDownloader(streamingDownloader);
        }
    }
}

export default RoomUppy;
