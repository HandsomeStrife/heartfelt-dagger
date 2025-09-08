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
        this.chunkTimeout = 300000; // 5 minutes timeout per chunk (large networks)
        this.uploadQueue = Promise.resolve(); // Serialize uploads to prevent race conditions

        // One-chunk buffer with timed flush to ensure proper finalization and single-chunk uploads
        this.pendingBlob = null;
        this.flushTimer = null;
        this.flushDelayMs = 2000; // flush pending chunk if no new chunk within 2s

        // Initialize statistics explicitly (BaseUploader also sets this, but keep explicit)
        this.totalChunks = 0;

        // Google Drive requires non-final chunks to be multiples of 256 KiB
        this.GRAN = 256 * 1024; // 256 KiB granularity
        this.leftover = null; // bytes not yet sent to a 256 KiB boundary
    }

    /**
     * Get the provider name
     * @returns {string}
     */
    getProviderName() {
        return 'google_drive';
    }

    /**
     * Parse Google Drive Range header (e.g., "bytes=0-2883583")
     * @param {string} rangeHeader
     * @returns {Object|null} {start, end} or null
     */
    parseRange(rangeHeader) {
        if (!rangeHeader) return null;
        const match = /bytes=(\d+)-(\d+)/.exec(rangeHeader);
        return match ? { start: Number(match[1]), end: Number(match[2]) } : null;
    }

    /**
     * Build payload for Google Drive upload, ensuring 256 KiB alignment for non-final chunks
     * @param {Blob} blob - New blob to send
     * @param {boolean} isFinal - Whether this is the final chunk
     * @returns {Object|null} {payload: Blob, isFinal: boolean} or null if not ready to send
     */
    buildPayload(blob, isFinal) {
        // Combine leftover bytes with new blob
        let combined = this.leftover ? new Blob([this.leftover, blob]) : blob;
        this.leftover = null;

        // For final chunks, send everything
        if (isFinal) {
            return { payload: combined, isFinal: true };
        }

        // For non-final chunks, only send multiples of 256 KiB
        const sendSize = Math.floor(combined.size / this.GRAN) * this.GRAN;
        if (sendSize === 0) {
            // Not enough data to send a 256 KiB chunk; buffer it
            this.leftover = combined;
            return null;
        }

        const payload = combined.slice(0, sendSize);
        const tail = combined.size > sendSize ? combined.slice(sendSize) : null;
        if (tail) {
            this.leftover = tail;
        }

        return { payload, isFinal: false };
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
        
        // Step 1: Get OAuth access token (and keep server-side validation)
        const response = await fetch(`/api/rooms/${this.roomData.id}/recordings/google-drive-upload-url`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.getCsrfToken(),
            },
            body: JSON.stringify({
                filename: metadata.filename,
                content_type: this.getCleanMimeType(firstBlob.type),
                // Size is required by backend validation, but we will create the Drive session in-browser
                size: this.sessionFileSize,
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
        this.accessToken = data.access_token; // Store access token for requests
        this.targetFolderId = (data.metadata && data.metadata.folder_id) ? data.metadata.folder_id : null;
        console.log('🔐 Google token acquired (len):', this.accessToken ? this.accessToken.length : 0);
        if (this.targetFolderId) {
            console.log('📁 Target Google Drive folder:', this.targetFolderId);
        } else {
            console.log('📁 No target folder provided; file will go to My Drive (root)');
        }
        this.currentRecordingFilename = metadata.filename;
        this.recordingStartedAt = metadata.started_at_ms || Date.now();
        this.uploadedBytes = 0;
        this.isUploading = true;
        this.retryCount = 0;
        
        // Step 2: Try to create the resumable upload session in the browser; fallback to server session
        try {
            this.currentSessionUri = await this.createBrowserResumableSession(this.currentRecordingFilename, firstBlob.type);
            console.log('🎯 GOOGLE DRIVE SESSION INITIALIZED (browser):', this.currentSessionUri);
        } catch (error) {
            console.warn('🎯 Browser session creation failed, using server session:', error.message);
            // Fallback to server-provided session URI if browser creation fails
            if (data.session_uri) {
                this.currentSessionUri = data.session_uri;
                console.log('🎯 GOOGLE DRIVE SESSION INITIALIZED (server fallback):', this.currentSessionUri);
            } else {
                throw new Error('Both browser and server session creation failed');
            }
        }
        
        // Start recording session in database
        await this.startRecordingSession(
            metadata.filename,
            this.currentSessionUri,
            null, // Leave provider_file_id null initially - will be set after finalization
            metadata.started_at_ms || Date.now(),
            firstBlob.type
        );
    }

    /**
     * Create a resumable upload session directly in the browser
     * @param {string} filename
     * @param {string} mimeType
     * @returns {Promise<string>} session URI
     */
    async createBrowserResumableSession(filename, mimeType) {
        if (!this.accessToken) {
            throw new Error('Missing Google access token for session creation');
        }

        const cleanType = this.getCleanMimeType(mimeType);
        
        // Get current origin to ensure CORS consistency
        const currentOrigin = window.location.origin;
        console.log('🌐 Creating session from origin:', currentOrigin);
        
        const initRes = await fetch('https://www.googleapis.com/upload/drive/v3/files?uploadType=resumable&supportsAllDrives=true', {
            method: 'POST',
            headers: {
                'Authorization': this.getAuthHeader(),
                'Content-Type': 'application/json; charset=UTF-8',
                'Origin': currentOrigin,
                // Do not set X-Upload-Content-Length; let Drive infer from final chunk
                'X-Upload-Content-Type': cleanType,
            },
            body: JSON.stringify({
                name: filename,
                mimeType: cleanType,
                ...(this.targetFolderId ? { parents: [this.targetFolderId] } : {})
            })
        });

        if (!initRes.ok) {
            const text = await initRes.text();
            throw new Error(`Failed to create resumable session: ${initRes.status} ${text}`);
        }

        const sessionUri = initRes.headers.get('Location');
        if (!sessionUri) {
            throw new Error('No Location header returned from Google for resumable session');
        }
        
        console.log('✅ Session created successfully for origin:', currentOrigin);
        return sessionUri;
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
            const isFinal = options.isFinal || false;

            // If final flag is explicitly set, flush pending (if any) and then upload this final chunk with known total
            if (isFinal) {
                if (this.flushTimer) {
                    clearTimeout(this.flushTimer);
                    this.flushTimer = null;
                }
                if (this.pendingBlob) {
                    const prev = this.pendingBlob;
                    this.pendingBlob = null;
                    const builtPrev = this.buildPayload(prev, false);
                    if (builtPrev) {
                        await this.doUploadChunk(builtPrev.payload, builtPrev.isFinal);
                    }
                }
                const builtFinal = this.buildPayload(blob, true);
                if (builtFinal) {
                    return this.doUploadChunk(builtFinal.payload, builtFinal.isFinal);
                }
                return;
            }

            // Non-final chunks: buffer the latest blob and flush the previous one
            if (!this.pendingBlob) {
                this.pendingBlob = blob;
                // Set a short flush timer so single-chunk recordings still upload
                this.flushTimer = setTimeout(async () => {
                    if (this.pendingBlob) {
                        const toSend = this.pendingBlob;
                        this.pendingBlob = null;
                        console.log('⏱️ Flushing pending Google Drive chunk (size):', toSend.size);
                        try {
                            const built = this.buildPayload(toSend, false);
                            if (built) {
                                await this.doUploadChunk(built.payload, built.isFinal);
                            }
                        } catch (e) {
                            console.error('🎯 Error flushing pending Google Drive chunk:', e);
                        }
                    }
                }, this.flushDelayMs);
                return;
            }

            // We have a previous pending chunk; send it now and keep the newest one pending
            if (this.flushTimer) {
                clearTimeout(this.flushTimer);
                this.flushTimer = null;
            }
            const toUpload = this.pendingBlob;
            this.pendingBlob = blob;
            console.log('📤 Sending previous pending chunk (bytes):', toUpload.size, 'Next pending size:', this.pendingBlob.size);
            const built = this.buildPayload(toUpload, false);
            if (built) {
                await this.doUploadChunk(built.payload, built.isFinal);
            }
            // Schedule flush for the new pending blob
            this.flushTimer = setTimeout(async () => {
                if (this.pendingBlob) {
                    const toSend = this.pendingBlob;
                    this.pendingBlob = null;
                    console.log('⏱️ Flushing (rescheduled) pending Google Drive chunk (size):', toSend.size);
                        try {
                            const built = this.buildPayload(toSend, false);
                            if (built) {
                                await this.doUploadChunk(built.payload, built.isFinal);
                            }
                        } catch (e) {
                            console.error('🎯 Error flushing pending Google Drive chunk:', e);
                        }
                }
            }, this.flushDelayMs);
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

        console.log(`🎯 UPLOADING GOOGLE DRIVE CHUNK: ${blob.size} bytes (${startByte}-${endByte}/${totalSize}) | isFinal=${isFinal}`);

        try {
            // Use XMLHttpRequest instead of fetch to avoid CORS issues
            const uploadResponse = await this.uploadChunkXHR(blob, startByte, endByte, totalSize);

            if (uploadResponse.status === 308) {
                // Parse what Drive actually committed
                const range = this.parseRange(uploadResponse.range);
                const committed = range ? (range.end + 1) : this.uploadedBytes;
                
                // How much of THIS blob did Drive actually accept?
                const acceptedOfThisBlob = Math.max(0, committed - startByte);
                
                // Update uploadedBytes to what the server says, not our guess
                this.uploadedBytes = committed;
                
                if (acceptedOfThisBlob < blob.size) {
                    // Buffer the remainder instead of immediately resending (Drive needs 256 KiB alignment)
                    const remainder = blob.slice(acceptedOfThisBlob);
                    this.leftover = this.leftover ? new Blob([this.leftover, remainder]) : remainder;
                    console.log(`🧩 Buffered remainder ${remainder.size} bytes; will merge with next chunk`);
                    return; // wait for next blob
                }
                
                console.log(`🎯 GOOGLE DRIVE CHUNK ACK: ${acceptedOfThisBlob} bytes | uploadedBytes now: ${this.uploadedBytes}`);
                this.totalChunks++;
                this.retryCount = 0; // Reset retry count on success

                // Update progress in database
                await this.updateRecordingProgress({
                    chunk_size_bytes: acceptedOfThisBlob,
                    total_uploaded_bytes: this.uploadedBytes,
                    ended_at_ms: Date.now()
                });

            } else if (uploadResponse.status === 200 || uploadResponse.status === 201) {
                // Upload complete (shouldn't happen during streaming, but handle it)
                console.log('🎯 GOOGLE DRIVE FINAL RESPONSE (200/201)');
                this.uploadedBytes += blob.size;
                console.log('📈 uploadedBytes now (final):', this.uploadedBytes);
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
                    console.log('🆔 Final file id:', fileInfo.id);
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
                return this.uploadChunk(blob, { isFinal }); // Preserve finality on retry
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
                // Try to read Range header if CORS allows (often blocked)
                let rangeHeader = null;
                try {
                    rangeHeader = xhr.getResponseHeader('Range');
                } catch (e) {}
                console.log('📥 XHR onload status:', xhr.status, xhr.statusText, '| Range:', rangeHeader || 'n/a', '| respLen:', (xhr.responseText || '').length);
                resolve({
                    status: xhr.status,
                    response: xhr.responseText || xhr.response,
                    statusText: xhr.statusText,
                    range: rangeHeader
                });
            };

            xhr.onerror = () => {
                console.error('🛑 XHR onerror', xhr.status, xhr.statusText, 'readyState:', xhr.readyState);
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
                let rangeHeader = null;
                try {
                    rangeHeader = xhr.getResponseHeader('Range');
                } catch (e) {}
                console.log('🩺 Session health status:', xhr.status, '| Range:', rangeHeader || 'n/a');
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
     * Finalize the Google Drive upload by ensuring the session is properly committed
     */
    async finalize() {
        if (!this.currentSessionUri) {
            console.log('🎯 NO GOOGLE DRIVE SESSION TO FINALIZE');
            return;
        }
        
        try {
            // Finalize the upload session - handle all cases
            await (this.uploadQueue = this.uploadQueue.then(async () => {
                if (this.flushTimer) {
                    clearTimeout(this.flushTimer);
                    this.flushTimer = null;
                }

                let sentSomething = false;

                // 1) If there's a pending blob, send it as final
                if (this.pendingBlob) {
                    const built = this.buildPayload(this.pendingBlob, true);
                    this.pendingBlob = null;
                    if (built) {
                        await this.doUploadChunk(built.payload, true);
                        sentSomething = true;
                    }
                }

                // 2) If there's leftover data but no pending blob, send leftover as final
                if (!sentSomething && this.leftover && this.leftover.size > 0) {
                    const built = this.buildPayload(new Blob([]), true); // This consumes this.leftover
                    if (built) {
                        await this.doUploadChunk(built.payload, true);
                        sentSomething = true;
                    }
                }

                // 3) If nothing was sent but we have uploaded bytes, send empty finalize
                if (!sentSomething && this.uploadedBytes > 0) {
                    console.log(`🏁 Sending empty finalize request for ${this.uploadedBytes} bytes`);
                    try {
                        const response = await fetch(this.currentSessionUri, {
                            method: 'PUT',
                            headers: {
                                'Authorization': this.getAuthHeader(),
                                'Content-Range': `bytes */${this.uploadedBytes}`,
                                'Content-Type': 'application/octet-stream',
                                'Content-Length': '0'
                            },
                            body: new Blob([])
                        });

                        console.log(`🏁 Empty finalize response: ${response.status}`);
                        
                        if (response.status === 200 || response.status === 201) {
                            const fileData = await response.json();
                            console.log('🎯 Google Drive file finalized:', fileData);
                            
                            // Confirm with backend
                            await this.confirmUploadCompletion(fileData.id || null);
                            sentSomething = true;
                        } else if (response.status === 308) {
                            console.warn('🏁 Empty finalize still returned 308 - upload may be incomplete');
                        }
                    } catch (e) {
                        console.error('🏁 Error sending empty finalize request:', e);
                    }
                }
            }));

            // If we didn't send anything, still try to confirm with backend
            if (this.uploadedBytes === 0) {
                try {
                    await this.confirmUploadCompletion(null);
                } catch (e) {
                    console.error('🎯 Error confirming Google Drive upload completion:', e);
                }
            }

            console.log('🎯 GOOGLE DRIVE RECORDING SESSION FINALIZED');
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

            console.log('📤 Confirm request payload:', {
                session_uri: this.currentSessionUri,
                file_id: fileId,
                filename: this.currentRecordingFilename,
                uploadedBytes: this.uploadedBytes
            });

            if (!confirmResponse.ok) {
                const text = await confirmResponse.text();
                console.error('❌ Confirm failed:', confirmResponse.status, text);
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
        if (this.flushTimer) {
            clearTimeout(this.flushTimer);
            this.flushTimer = null;
        }
        this.pendingBlob = null;
        this.leftover = null;
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
