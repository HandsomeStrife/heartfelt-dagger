/**
 * BaseUploader - Abstract base class for video upload providers
 * 
 * Defines the common interface that all upload providers must implement
 */
export class BaseUploader {
    constructor(roomData, recordingSettings) {
        if (this.constructor === BaseUploader) {
            throw new Error('BaseUploader is abstract and cannot be instantiated directly');
        }
        
        this.roomData = roomData;
        this.recordingSettings = recordingSettings;
        this.currentRecordingId = null;
        this.currentRecordingFilename = null;
        this.recordingStartedAt = null;
        
        // Upload session state
        this.isUploading = false;
        this.uploadedBytes = 0;
        this.totalChunks = 0;
    }

    /**
     * Initialize the uploader (called once when recording starts)
     * @param {Object} metadata - Recording metadata
     * @param {Blob} firstBlob - First video chunk
     * @returns {Promise}
     */
    async initialize(metadata, firstBlob) {
        throw new Error('initialize() must be implemented by subclass');
    }

    /**
     * Upload a video chunk
     * @param {Blob} blob - Video chunk to upload
     * @returns {Promise}
     */
    async uploadChunk(blob) {
        throw new Error('uploadChunk() must be implemented by subclass');
    }

    /**
     * Finalize the upload session (called when recording stops)
     * @returns {Promise}
     */
    async finalize() {
        throw new Error('finalize() must be implemented by subclass');
    }

    /**
     * Abort/cleanup the upload session
     * @returns {Promise}
     */
    async abort() {
        throw new Error('abort() must be implemented by subclass');
    }

    /**
     * Get the provider name
     * @returns {string}
     */
    getProviderName() {
        throw new Error('getProviderName() must be implemented by subclass');
    }

    /**
     * Check if this uploader is currently uploading
     * @returns {boolean}
     */
    isCurrentlyUploading() {
        return this.isUploading;
    }

    /**
     * Get upload progress statistics
     * @returns {Object}
     */
    getUploadStats() {
        return {
            uploadedBytes: this.uploadedBytes,
            totalChunks: this.totalChunks,
            isUploading: this.isUploading
        };
    }

    /**
     * Reset uploader state
     */
    reset() {
        this.currentRecordingId = null;
        this.currentRecordingFilename = null;
        this.recordingStartedAt = null;
        this.isUploading = false;
        this.uploadedBytes = 0;
        this.totalChunks = 0;
    }

    /**
     * Get CSRF token for API requests
     * @returns {string}
     */
    getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    }

    /**
     * Clean MIME type to remove codec specifications
     * @param {string} mimeType
     * @returns {string}
     */
    getCleanMimeType(mimeType) {
        if (!mimeType) return 'video/webm';
        
        // Remove codec specifications, keep only base MIME type
        if (mimeType.includes('video/webm')) return 'video/webm';
        if (mimeType.includes('video/mp4')) return 'video/mp4';
        if (mimeType.includes('video/quicktime')) return 'video/quicktime';
        
        // Default fallback
        return 'video/webm';
    }

    /**
     * Emit a custom recording event
     * @param {string} eventType
     * @param {Object} detail
     */
    emitRecordingEvent(eventType, detail) {
        const event = new CustomEvent(eventType, { 
            detail: { 
                ...detail, 
                provider: this.getProviderName() 
            } 
        });
        document.dispatchEvent(event);
    }

    /**
     * Start a recording session in the database
     * @param {string} filename
     * @param {string} sessionId
     * @param {string} providerFileId
     * @param {number} startedAtMs
     * @param {string} mimeType
     * @returns {Promise}
     */
    async startRecordingSession(filename, sessionId, providerFileId, startedAtMs, mimeType) {
        try {
            const response = await fetch(`/api/rooms/${this.roomData.id}/recordings/start-session`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.getCsrfToken()
                },
                body: JSON.stringify({
                    filename,
                    multipart_upload_id: sessionId,
                    provider_file_id: providerFileId,
                    started_at_ms: startedAtMs,
                    mime_type: mimeType
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            this.currentRecordingId = data.recording_id;
            
            console.log(`🎥 Recording session started for ${this.getProviderName()}:`, {
                recording_id: this.currentRecordingId,
                filename,
                session_id: sessionId
            });

            return data;
        } catch (error) {
            console.error(`❌ Failed to start ${this.getProviderName()} recording session:`, error);
            throw error;
        }
    }

    /**
     * Update recording progress in the database
     * @param {Object} progressData
     * @returns {Promise}
     */
    async updateRecordingProgress(progressData) {
        if (!this.currentRecordingId) {
            console.warn(`⚠️ No current recording ID to update progress for ${this.getProviderName()}`);
            return;
        }

        try {
            const response = await fetch(`/api/rooms/${this.roomData.id}/recordings/${this.currentRecordingId}/progress`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.getCsrfToken()
                },
                body: JSON.stringify(progressData)
            });

            if (!response.ok) {
                console.warn(`⚠️ Failed to update ${this.getProviderName()} recording progress:`, response.status);
            } else {
                console.log(`📊 ${this.getProviderName()} recording progress updated:`, {
                    recording_id: this.currentRecordingId,
                    ...progressData
                });
            }
        } catch (error) {
            console.error(`❌ Failed to update ${this.getProviderName()} recording progress:`, error);
        }
    }
}
