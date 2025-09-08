import { BaseUploader } from './BaseUploader.js';

/**
 * GoogleDriveUploader - Google Drive resumable upload implementation
 * 
 * Handles continuous recording uploads to Google Drive using resumable upload API
 * Implementation pattern based on VDO.Ninja approach
 */
export class GoogleDriveUploader extends BaseUploader {
    constructor(roomData, recordingSettings) {
        super(roomData, recordingSettings);
        
        // Google Drive resumable upload state
        this.currentSessionUri = null;
        this.accessToken = null; // Will be set during initialization
        this.uploadedBytes = 0;
        // Use 2GB session size to align with backend validation and avoid premature limits
        this.sessionFileSize = 2 * 1024 * 1024 * 1024;
        this.retryCount = 0;
        this.maxRetries = 3;
        this.chunkTimeout = 60000; // 1 minute timeout per chunk
        this.uploadQueue = Promise.resolve(); // Serialize uploads to prevent race conditions

        // Buffer the latest chunk so we can finalize with a known total size
        // We keep one chunk in hand and always upload the previous one.
        this.pendingBlob = null;
    }

    /**
     * Get the provider name
     * @returns {string}
     */
    getProviderName() {
        return 'google_drive';
    }

    /**
     * Get authorization header for Google Drive requests
     * @returns {string}
     */
    getAuthHeader() {
        if (!this.accessToken) {
            throw new Error('Missing Google access token');
        }
        return `Bearer ${this.accessToken}`;
    }

    /**
     * Initialize Google Drive resumable upload session
     * @param {Object} metadata - Recording metadata
     * @param {Blob} firstBlob - First video chunk
     */
    async initialize(metadata, firstBlob) {
        console.log('🎯 INITIALIZING GOOGLE DRIVE RESUMABLE UPLOAD SESSION');
        
        // Get resumable upload session URI from our backend
        const response = await fetch(`/api/rooms/${this.roomData.id}/recordings/google-drive-upload-url`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.getCsrfToken(),
            },
            body: JSON.stringify({
                filename: metadata.filename,
                content_type: this.getCleanMimeType(firstBlob.type),
                size: this.sessionFileSize, // Estimated file size
                metadata: {
                    started_at_ms: metadata.started_at_ms,
                    ended_at_ms: metadata.ended_at_ms,
                }
            })
        });

        if (!response.ok) {
            throw new Error(`Failed to get Google Drive upload URL: ${response.status}`);
        }

        const data = await response.json();
        this.currentSessionUri = data.session_uri;
        this.accessToken = data.access_token; // Store access token for requests
        this.currentRecordingFilename = metadata.filename;
        this.recordingStartedAt = metadata.started_at_ms || Date.now();
        this.uploadedBytes = 0;
        this.isUploading = true;
        this.retryCount = 0;
        
        console.log('🎯 GOOGLE DRIVE SESSION INITIALIZED:', this.currentSessionUri);
        
        // Start recording session in database
        await this.startRecordingSession(
            metadata.filename,
            this.currentSessionUri,
            this.currentSessionUri, // Use session URI as provider file ID initially
            metadata.started_at_ms || Date.now(),
            firstBlob.type
        );
    }

    /**
     * Upload a video chunk to the Google Drive resumable session
     * @param {Blob} blob - Video chunk to upload
     * @param {Object} options - Upload options
     * @param {boolean} options.isFinal - Whether this is the final chunk
     */
    async uploadChunk(blob, options = {}) {
        // Queue uploads and maintain one-chunk buffer so the final PUT includes the total size
        return this.uploadQueue = this.uploadQueue.then(async () => {
            // If we don't have a pending blob yet, store this one and wait for the next chunk
            if (!this.pendingBlob) {
                this.pendingBlob = blob;
                return;
            }

            // Upload the previous blob; keep the newest one buffered
            const blobToUpload = this.pendingBlob;
            this.pendingBlob = blob;
            return this.doUploadChunk(blobToUpload, false);
        });
    }

    async doUploadChunk(blob, isFinal = false) {
        if (!this.currentSessionUri) {
            throw new Error('Google Drive resumable upload not initialized');
        }

        // Skip proactive health check; rely on server responses for resilience

        const startByte = this.uploadedBytes;
        const endByte = this.uploadedBytes + blob.size - 1;
        const totalSize = isFinal ? (endByte + 1) : '*'; // Use final total for last chunk

        console.log(`🎯 UPLOADING GOOGLE DRIVE CHUNK: ${blob.size} bytes (${startByte}-${endByte}/${totalSize})`);

        try {
            // Use XMLHttpRequest instead of fetch to avoid CORS issues
            const uploadResponse = await this.uploadChunkXHR(blob, startByte, endByte, totalSize);

            if (uploadResponse.status === 308) {
                // Resumable upload in progress - good!
                console.log(`🎯 GOOGLE DRIVE CHUNK UPLOADED: ${blob.size} bytes`);
                this.uploadedBytes += blob.size;
                this.totalChunks++;
                this.retryCount = 0; // Reset retry count on success

                // Update progress in database
                await this.updateRecordingProgress({
                    chunk_size_bytes: blob.size,
                    total_uploaded_bytes: this.uploadedBytes,
                    ended_at_ms: Date.now()
                });

            } else if (uploadResponse.status === 200 || uploadResponse.status === 201) {
                // Upload complete (shouldn't happen during streaming, but handle it)
                console.log('🎯 GOOGLE DRIVE UPLOAD COMPLETED UNEXPECTEDLY');
                this.uploadedBytes += blob.size;
                this.totalChunks++;
                
                // Try to get file info from response
                const responseText = uploadResponse.response || '';
                let fileInfo = null;
                try {
                    fileInfo = JSON.parse(responseText);
                } catch (e) {
                    console.warn('Could not parse Google Drive response:', responseText);
                }

                if (fileInfo?.id) {
                    await this.confirmUploadCompletion(fileInfo.id);
                }

            } else if (uploadResponse.status >= 400) {
                // Client error - session might be invalid
                console.error(`🎯 GOOGLE DRIVE UPLOAD ERROR: ${uploadResponse.status}`);
                
                if (uploadResponse.status === 404 || uploadResponse.status === 410) {
                    // Session expired/invalid - invalidate it
                    this.currentSessionUri = null;
                }
                
                throw new Error(`Google Drive upload failed: ${uploadResponse.status}`);
            }

        } catch (error) {
            console.error('🎯 GOOGLE DRIVE UPLOAD ERROR:', error);
            
            // Retry logic with exponential backoff
            if (this.retryCount < this.maxRetries) {
                this.retryCount++;
                const delay = Math.pow(2, this.retryCount) * 1000; // 2s, 4s, 8s
                console.log(`🎯 RETRYING GOOGLE DRIVE UPLOAD (${this.retryCount}/${this.maxRetries}) after ${delay}ms`);
                
                await new Promise(resolve => setTimeout(resolve, delay));
                return this.uploadChunk(blob); // Recursive retry
            }
            
            throw error;
        }
    }

    /**
     * Upload chunk using XMLHttpRequest (avoids CORS issues)
     * @param {Blob} blob 
     * @param {number} startByte 
     * @param {number} endByte 
     * @param {string} totalSize 
     * @param {AbortSignal} signal 
     * @returns {Promise<{status: number, response: any}>}
     */
    async uploadChunkXHR(blob, startByte, endByte, totalSize) {
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            
            xhr.open('PUT', this.currentSessionUri);
            xhr.setRequestHeader('Authorization', this.getAuthHeader());
            xhr.setRequestHeader('Content-Range', `bytes ${startByte}-${endByte}/${totalSize}`);
            xhr.setRequestHeader('Content-Type', this.getCleanMimeType(blob.type));

            xhr.onload = () => {
                // Don't try to access Range header due to CORS restrictions
                resolve({
                    status: xhr.status,
                    response: xhr.responseText || xhr.response,
                    statusText: xhr.statusText
                });
            };

            xhr.onerror = () => {
                reject(new Error(`Google Drive upload failed: ${xhr.status}`));
            };

            xhr.ontimeout = () => {
                reject(new Error('Google Drive upload timeout - chunk took too long to upload'));
            };

            // Set timeout
            xhr.timeout = this.chunkTimeout;

            xhr.send(blob);
        });
    }

    /**
     * Check session health using XMLHttpRequest
     * @returns {Promise<{status: number}>}
     */
    async checkSessionHealthXHR() {
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            
            xhr.open('PUT', this.currentSessionUri);
            xhr.setRequestHeader('Authorization', this.getAuthHeader());
            xhr.setRequestHeader('Content-Range', 'bytes */*');
            // Do not set Content-Length; browsers forbid it. Empty body implies 0 length.

            xhr.onload = () => {
                resolve({
                    status: xhr.status,
                    response: xhr.response
                });
            };

            xhr.onerror = () => {
                reject(new Error(`Session health check failed: ${xhr.status}`));
            };

            xhr.timeout = 10000; // 10 second timeout for health checks
            xhr.ontimeout = () => {
                reject(new Error('Session health check timeout'));
            };

            xhr.send(null); // No body for health check
        });
    }

    /**
     * Check if the current Google Drive session is still healthy
     * @returns {Promise<boolean>}
     */
    async checkSessionHealth() {
        if (!this.currentSessionUri) {
            return false;
        }
        try {
            const response = await this.checkSessionHealthXHR();
            return response.status === 308 || (response.status >= 200 && response.status < 300);
        } catch (error) {
            console.warn('🎯 GOOGLE DRIVE SESSION HEALTH CHECK FAILED:', error);
            return false;
        }
    }

    /**
     * Recreate the Google Drive session (recovery mechanism)
     * @param {Blob} nextBlob - The next blob that will be uploaded
     */
    async recreateSession(nextBlob) {
        console.log('🎯 RECREATING GOOGLE DRIVE SESSION');
        
        // Create new session with updated start position
        const metadata = {
            filename: this.currentRecordingFilename,
            started_at_ms: this.recordingStartedAt,
            ended_at_ms: Date.now()
        };
        
        // Reset session URI
        this.currentSessionUri = null;
        
        // Initialize new session
        await this.initialize(metadata, nextBlob);
        
        console.log('🎯 GOOGLE DRIVE SESSION RECREATED');
    }

    /**
     * Finalize the Google Drive upload by sending final empty chunk
     */
    async finalize() {
        if (!this.currentSessionUri) {
            console.log('🎯 NO GOOGLE DRIVE SESSION TO FINALIZE');
            return;
        }
        
        try {
            // Flush any pending chunk as the final chunk with known total size
            await (this.uploadQueue = this.uploadQueue.then(async () => {
                if (this.pendingBlob) {
                    const finalBlob = this.pendingBlob;
                    this.pendingBlob = null;
                    await this.doUploadChunk(finalBlob, true);
                }
            }));

            console.log('🎯 GOOGLE DRIVE RECORDING SESSION FINALIZED (final chunk sent)');
        } finally {
            this.reset();
        }

    }

    /**
     * Confirm upload completion with our backend
     * @param {string} fileId - Google Drive file ID
     */
    // (Removed earlier duplicate confirmUploadCompletion implementation)

    /**
     * Handle the completion of the upload and cleanup
     */
    handleUploadCompletion(fileInfo) {
        // Emit success event
        this.emitRecordingEvent('recording-upload-success', {
            recording_id: this.currentRecordingId,
            provider_file_id: fileInfo?.id || 'unknown',
            filename: this.currentRecordingFilename,
            web_view_link: fileInfo?.webViewLink
        });

        console.log('🎯 GOOGLE DRIVE UPLOAD COMPLETED SUCCESSFULLY');
        this.reset();
    }

    /**
     * Original broken method - will replace properly
                const responseText = await finalResponse.text();
                let fileInfo = null;
                
                try {
                    fileInfo = JSON.parse(responseText);
                } catch (e) {
                    console.warn('Could not parse final Google Drive response:', responseText);
                }

                if (fileInfo?.id) {
                    await this.confirmUploadCompletion(fileInfo.id);
                } else {
                    console.warn('No file ID returned from Google Drive');
                }

                // Emit success event
                this.emitRecordingEvent('recording-upload-success', {
                    recording_id: this.currentRecordingId,
                    provider_file_id: fileInfo?.id || 'unknown',
                    filename: this.currentRecordingFilename,
                    web_view_link: fileInfo?.webViewLink
                });

            } else {
                throw new Error(`Failed to finalize Google Drive upload: ${finalResponse.status}`);
            }

        } catch (error) {
            console.error('🎯 ERROR finalizing Google Drive upload:', error);
            
            // Emit error event
            this.emitRecordingEvent('recording-upload-error', {
                filename: this.currentRecordingFilename,
                error: error.message
            });
            
            throw error;
        } finally {
            // Reset state
            this.reset();
            console.log('🎯 GOOGLE DRIVE RECORDING SESSION FINALIZED');
        }
    }

    /**
     * Confirm upload completion with our backend
     * @param {string} fileId - Google Drive file ID
     */
    async confirmUploadCompletion(fileId) {
        try {
            console.log('✅ Google Drive upload completed, confirming with server...');
            
            const confirmResponse = await fetch(`/api/rooms/${this.roomData.id}/recordings/confirm-google-drive`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.getCsrfToken(),
                },
                body: JSON.stringify({
                    session_uri: this.currentSessionUri,
                    file_id: fileId,
                    metadata: {
                        filename: this.currentRecordingFilename,
                        size_bytes: this.uploadedBytes
                    }
                })
            });

            if (!confirmResponse.ok) {
                throw new Error(`Failed to confirm Google Drive upload: ${confirmResponse.status}`);
            }

            const confirmData = await confirmResponse.json();
            console.log('✅ Google Drive upload confirmed:', confirmData);

            // Emit success event for UI/listeners
            this.emitRecordingEvent('recording-upload-success', {
                recording_id: confirmData.recording_id,
                provider_file_id: confirmData.provider_file_id || fileId,
                filename: this.currentRecordingFilename,
                web_view_link: confirmData.web_view_link
            });
        } catch (error) {
            console.error('❌ Error confirming Google Drive upload:', error);
            this.emitRecordingEvent('recording-upload-error', {
                filename: this.currentRecordingFilename,
                error: error.message
            });
            throw error;
        }
    }

    /**
     * Abort the Google Drive upload (no specific API call needed)
     */
    async abort() {
        console.log('🎯 ABORTING GOOGLE DRIVE UPLOAD');
        
        // Google Drive doesn't require explicit abort API call
        // Just reset our state
        this.reset();
    }

    /**
     * Reset Google Drive-specific state
     */
    reset() {
        super.reset();
        this.currentSessionUri = null;
        this.retryCount = 0;
        this.pendingBlob = null;
    }

    /**
     * Get Google Drive-specific upload statistics
     * @returns {Object}
     */
    getUploadStats() {
        return {
            ...super.getUploadStats(),
            sessionUri: this.currentSessionUri ? 'active' : 'none',
            retryCount: this.retryCount
        };
    }
}
