/**
 * VideoRecorder - Manages video recording functionality
 * 
 * Handles MediaRecorder setup, recording lifecycle, and coordination
 * between different storage providers (local device, cloud storage).
 */

import { LoggerRegistry } from '../utils/Logger';

export class VideoRecorder {
    constructor(roomWebRTC) {
        this.roomWebRTC = roomWebRTC;
        this.logger = LoggerRegistry.getLogger('VideoRecorder', roomWebRTC);
        this.mediaRecorder = null;
        this.recordedChunks = [];
        this.recordingStartTime = null;
        this.originalRecordingStartTime = null; // Never reset, used for total duration
        this.isRecording = false;
        this.recordingTimer = null;
        this.recMime = null;
        
        // MEMORY LEAK FIX: Store event handler references for proper cleanup
        this.eventHandlers = {
            onstop: null,
            ondataavailable: null
        };
        
        // Cumulative recording statistics for cloud storage
        this.cumulativeStats = {
            totalSizeBytes: 0,
            totalChunks: 0,
            totalUploadedBytes: 0
        };
        
        // Single recording session info for multipart upload
        this.recordingSession = {
            filename: null,
            multipartUploadId: null,
            partNumber: 0
        };
        
        this.initializeRecording();
    }

    /**
     * Initializes video recording capabilities
     */
    initializeRecording() {
        if (!this.roomWebRTC.roomData.recording_enabled) {
            console.log('🎥 Video recording disabled for this room');
            return;
        }

        // Fix #4: Choose MIME type once and use everywhere
        const pickType = (...types) => types.find(t => MediaRecorder.isTypeSupported(t));
        this.recMime = pickType(
            'video/webm;codecs=vp9,opus',
            'video/webm;codecs=vp8,opus',
            'video/webm',
            'video/mp4;codecs=h264,aac',
            'video/mp4'
        );

        if (!this.recMime) {
            console.warn('🎥 MediaRecorder supported types not found');
            return;
        }

        console.log('🎥 Video recording initialized with', this.recMime);
    }

    /**
     * Starts video recording
     */
    async startRecording() {
        const localStream = this.roomWebRTC.mediaManager.getLocalStream();
        if (!localStream) {
            console.warn('🎥 No local stream available for recording');
            return;
        }

        // Fix #4: Use the chosen MIME type consistently
        if (!this.recMime) {
            console.warn('🎥 No recording MIME type available');
            return;
        }

        try {
            // Determine storage provider once for the entire function
            const storageProvider = this.roomWebRTC.roomData.recording_settings?.storage_provider || 'local_device';
            
            this.mediaRecorder = new MediaRecorder(localStream, { mimeType: this.recMime });
            
            // CRITICAL FIX: Set timestamps BEFORE generating filename
            // generateRecordingFilename() relies on originalRecordingStartTime
            this.recordingStartTime = Date.now();
            this.originalRecordingStartTime = Date.now(); // Never reset, used for total duration
            this.isRecording = true;
            this.recordedChunks = [];
            
            // Reset cumulative statistics for new recording
            this.cumulativeStats = {
                totalSizeBytes: 0,
                totalChunks: 0,
                totalUploadedBytes: 0
            };
            
            // Generate single filename for entire recording session
            // NOW filename generation happens AFTER originalRecordingStartTime is set
            this.recordingSession = {
                filename: this.generateRecordingFilename(),
                multipartUploadId: null,
                partNumber: 0
            };

            // MEMORY LEAK FIX: Store event handlers for proper cleanup
            this.eventHandlers.onstop = () => {
                console.log('🎥 MediaRecorder stopped event triggered');
                
                // CRITICAL FIX: Read fresh storage provider state instead of using closure variable
                // This prevents using stale settings if they changed during recording
                const currentStorageProvider = this.roomWebRTC.roomData.recording_settings?.storage_provider || 'local_device';
                console.log('🎥 Storage provider on stop (fresh):', currentStorageProvider);
                
                // Check if local save was used (either primary or dual recording)
                const didSaveLocally = this.shouldSaveLocally(currentStorageProvider);
                
                if (didSaveLocally) {
                    console.log('🎥 Local save was active - finalizing streaming download');
                    // Finalize and trigger the streaming download
                    this.roomWebRTC.streamingDownloader.finalizeDownload();
                }
                
                if (currentStorageProvider !== 'local_device') {
                    console.log('🎥 Cloud storage recording completed');
                    // Cloud storage finalization is handled by the uploader
                }
            };
            this.mediaRecorder.onstop = this.eventHandlers.onstop;

            // MEMORY LEAK FIX: Store event handler for cleanup
            this.eventHandlers.ondataavailable = async (event) => {
                if (!event.data || !event.data.size) return;
                
                const endTime = Date.now();
                const blob = event.data;
                
                // Use session filename for all chunks (multipart upload)
                const recordingData = {
                    user_id: this.roomWebRTC.currentUserId,
                    started_at_ms: this.originalRecordingStartTime, // Use original start time for session
                    ended_at_ms: endTime,
                    size_bytes: blob.size,
                    mime_type: blob.type || this.recMime,
                    filename: this.recordingSession.filename,
                    partNumber: ++this.recordingSession.partNumber, // Increment part number for multipart
                    multipartUploadId: this.recordingSession.multipartUploadId
                };
                
                this.logger.debug(`🎥 ===== CHUNK GENERATED =====`);
                this.logger.debug(`🎥 Chunk size: ${(blob.size / 1024 / 1024).toFixed(2)} MB`);
                this.logger.debug(`🎥 Part number: ${recordingData.partNumber}`);
                this.logger.debug(`🎥 Storage provider: ${storageProvider}`);

                try {
                    // Update cumulative statistics
                    this.cumulativeStats.totalSizeBytes += blob.size;
                    this.cumulativeStats.totalChunks += 1;
                    
                    // Determine recording destinations based on storage provider and local save consent
                    const shouldSaveLocally = this.shouldSaveLocally(storageProvider);
                    const shouldSaveToCloud = this.shouldSaveToCloud(storageProvider);
                    
                    console.log('🎥 Recording destinations:', { 
                        storageProvider, 
                        shouldSaveLocally, 
                        shouldSaveToCloud 
                    });
                    
                    // Handle local saving (either primary local storage or dual recording)
                    if (shouldSaveLocally) {
                        try {
                            // Use StreamingDownloader for local save, not CloudUploader
                            this.roomWebRTC.streamingDownloader.addChunk(blob, recordingData);
                            console.log('🎥 Local device chunk processed successfully');
                        } catch (error) {
                            console.error('🎥 Error saving locally:', error);
                            // For dual recording, continue with cloud upload even if local fails
                            if (!shouldSaveToCloud) {
                                throw error;
                            }
                        }
                    }
                    
                    // Handle cloud saving (either primary cloud storage or dual recording)
                    if (shouldSaveToCloud) {
                        try {
                            // Wait for upload backlog to clear
                            while (this.tooManyQueuedUploads()) {
                                console.warn('📦 Upload backlog; waiting...');
                                await new Promise(resolve => setTimeout(resolve, 1500));
                            }
                            
                            await this.roomWebRTC.cloudUploader.uploadChunk(blob, {
                                ...recordingData,
                                isCloudSave: true
                            });
                            console.log('🎥 Video chunk uploaded to cloud successfully');
                            
                            // Track uploaded bytes for cloud storage (only on success)
                            this.cumulativeStats.totalUploadedBytes += blob.size;
                            
                            // Update session with upload ID if this was the first chunk
                            if (!this.recordingSession.multipartUploadId && window.roomUppy?.getCurrentUploader) {
                                const uploaderStats = window.roomUppy.getUploadStats();
                                if (uploaderStats.multipartUploadId || uploaderStats.sessionUri) {
                                    this.recordingSession.multipartUploadId = uploaderStats.multipartUploadId || uploaderStats.sessionUri;
                                    console.log('🎥 Stored upload session ID:', this.recordingSession.multipartUploadId);
                                }
                            }
                        } catch (error) {
                            console.error('🎥 Error uploading to cloud:', error);
                            // For dual recording, continue if local save succeeded
                            if (!shouldSaveLocally) {
                                throw error;
                            }
                        }
                    }
                } catch (error) {
                    console.error('🎥 Recording error:', error);
                }
                
                // Reset start time for next segment (only for cloud storage with timeslices)
                if (storageProvider !== 'local_device') {
                    this.recordingStartTime = Date.now();
                }
            };
            this.mediaRecorder.ondataavailable = this.eventHandlers.ondataavailable;

            // For local device recording, use small timeslices for streaming download
            // (storageProvider already declared above)
            
            if (storageProvider === 'local_device') {
                // Use small timeslices (5 seconds) for streaming download to prevent data loss
                this.mediaRecorder.start(5000); // 5 seconds - frequent enough to prevent loss
                console.log('🎥 Video recording started (streaming for local device)');
                this.roomWebRTC.streamingDownloader.initializeDownload(this.recMime);
            } else if (storageProvider === 'wasabi') {
                // Start recording with 30-second timeslices for Wasabi S3 upload (S3 requires 5MB minimum part size)
                this.mediaRecorder.start(30000); // 30 seconds - ensures chunks meet S3 5MB minimum requirement
                console.log('🎥 Video recording started with timeslices for Wasabi S3 upload');
            } else if (storageProvider === 'google_drive') {
                // Use smaller timeslices for Google Drive to reduce per-chunk size and timeouts
                this.mediaRecorder.start(10000); // 10 seconds
                console.log('🎥 Video recording started with 10s timeslices for Google Drive upload');
            } else {
                // Default for other cloud providers
                this.mediaRecorder.start(30000);
                console.log('🎥 Video recording started with timeslices for cloud upload');
            }
            
            // Show status bar for ALL recording types
            console.log('🎥 About to call showRecordingStatusBar()...');
            this.roomWebRTC.statusBarManager.showRecordingStatus();
            console.log('🎥 showRecordingStatusBar() called');
            this.updateRecordingUI(true);

        } catch (error) {
            console.error('🎥 Error starting MediaRecorder:', error);
            this.isRecording = false;
        }
    }

    /**
     * Stops video recording
     * Now async to properly wait for upload finalization
     * MEMORY LEAK FIX: Properly cleanup event listeners and MediaRecorder reference
     */
    async stopRecording() {
        if (this.mediaRecorder && this.isRecording) {
            this.isRecording = false;
            try {
                this.mediaRecorder.stop(); // This will trigger onstop event which handles download for local device
            } catch (error) {
                console.warn('🎥 Error stopping MediaRecorder:', error);
                
                // If stop fails but we have streaming download data, still try to download
                const stopStorageProvider = this.roomWebRTC.roomData.recording_settings?.storage_provider || 'local_device';
                if (stopStorageProvider === 'local_device') {
                    console.log('🎥 MediaRecorder stop failed, but finalizing streaming download anyway');
                    this.roomWebRTC.streamingDownloader.finalizeDownload();
                }
            }
            
            // MEMORY LEAK FIX: Remove event listeners before nullifying mediaRecorder
            if (this.mediaRecorder) {
                if (this.eventHandlers.onstop) {
                    this.mediaRecorder.removeEventListener('stop', this.eventHandlers.onstop);
                    this.eventHandlers.onstop = null;
                }
                if (this.eventHandlers.ondataavailable) {
                    this.mediaRecorder.removeEventListener('dataavailable', this.eventHandlers.ondataavailable);
                    this.eventHandlers.ondataavailable = null;
                }
            }
            
            // Finalize cloud upload if using cloud storage
            const stopStorageProvider = this.roomWebRTC.roomData.recording_settings?.storage_provider || 'local_device';
            if (stopStorageProvider !== 'local_device' && window.roomUppy) {
                console.log('🎥 Finalizing cloud multipart upload...');
                try {
                    await window.roomUppy.finalizeMultipartUpload();
                    console.log('🎥 ✅ Cloud upload finalized successfully');
                } catch (error) {
                    console.error('🎥 ❌ Error finalizing cloud upload:', error);
                    // Don't throw - we want to continue with cleanup
                }
            }
            
            this.updateRecordingUI(false);
            this.roomWebRTC.statusBarManager.hideRecordingStatus();
            console.log('🎥 Video recording stopped');
            
            // MEMORY LEAK FIX: Release MediaRecorder reference
            this.mediaRecorder = null;
            
            // Unjoin from video sharing after stopping recording (but stay in room)
            console.log('🎥 Unjoining from video sharing after stopping recording...');
            await this.roomWebRTC.leaveSlot();
        }
    }

    /**
     * Allows user to download current recording without stopping (for local device recording)
     */
    downloadCurrentRecording() {
        const currentStorageProvider = this.roomWebRTC.roomData.recording_settings?.storage_provider || 'local_device';
        if (currentStorageProvider === 'local_device' && this.recordedChunks && this.recordedChunks.length > 0) {
            // Create a partial recording download with current chunks
            const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
            const ext = this.recMime.includes('webm') ? 'webm' : 'mp4';
            const filename = `room-recording-partial-${timestamp}.${ext}`;
            
            const combinedBlob = new Blob(this.recordedChunks, { type: this.recMime });
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
        } else {
            console.warn('💾 No current recording available for download');
        }
    }

    /**
     * Helper method to check for upload backpressure
     */
    tooManyQueuedUploads() {
        if (!window.roomUppy) return false;
        
        const state = window.roomUppy.getState();
        const files = Object.values(state.files || {});
        const inflight = files.filter(file => 
            file.progress?.uploadStarted && !file.progress?.uploadComplete
        ).length;
        
        return inflight >= 4; // Allow 4 concurrent segments
    }

    /**
     * Updates recording UI indicators
     */
    updateRecordingUI(isRecording) {
        // Update UI to show recording status
        const recordingIndicators = document.querySelectorAll('.recording-indicator');
        recordingIndicators.forEach(indicator => {
            if (isRecording) {
                indicator.classList.add('recording');
                indicator.textContent = '🔴 Recording';
            } else {
                indicator.classList.remove('recording');
                indicator.textContent = '';
            }
        });

    }

    /**
     * Gets current recording state
     */
    isCurrentlyRecording() {
        return this.isRecording;
    }

    /**
     * Gets recorded chunks
     */
    getRecordedChunks() {
        return this.recordedChunks;
    }

    /**
     * Gets recording start time
     */
    getRecordingStartTime() {
        return this.recordingStartTime;
    }

    /**
     * Gets original recording start time (never reset, used for total duration)
     */
    getOriginalRecordingStartTime() {
        return this.originalRecordingStartTime;
    }

    /**
     * Gets recording MIME type
     */
    getRecordingMimeType() {
        return this.recMime;
    }

    /**
     * Adds a chunk to the recorded chunks array
     */
    addRecordedChunk(chunk) {
        this.recordedChunks.push(chunk);
    }

    /**
     * Gets cumulative recording statistics
     */
    getCumulativeStats() {
        return this.cumulativeStats;
    }

    /**
     * Gets total recorded size in bytes (cumulative for cloud storage, current chunks for local)
     */
    getTotalRecordedSize() {
        const storageProvider = this.roomWebRTC.roomData.recording_settings?.storage_provider || 'local_device';
        
        if (storageProvider === 'local_device') {
            // For local storage, use current chunks
            return this.recordedChunks.reduce((sum, chunk) => sum + chunk.size, 0);
        } else {
            // For cloud storage, use cumulative stats
            return this.cumulativeStats.totalSizeBytes;
        }
    }

    /**
     * Gets total number of segments/chunks
     */
    getTotalChunks() {
        const storageProvider = this.roomWebRTC.roomData.recording_settings?.storage_provider || 'local_device';
        
        if (storageProvider === 'local_device') {
            // For local storage, use current chunks
            return this.recordedChunks.length;
        } else {
            // For cloud storage, use cumulative stats
            return this.cumulativeStats.totalChunks;
        }
    }

    /**
     * Determines if recordings should be saved locally
     * This is true for:
     * 1. Local device storage (primary local)
     * 2. Remote storage with local save consent (dual recording)
     */
    shouldSaveLocally(storageProvider) {
        // Primary local storage
        if (storageProvider === 'local_device') {
            this.logger.debug('💾 shouldSaveLocally: TRUE (primary local storage)');
            return true;
        }
        
        // Dual recording: remote storage + local save consent
        const localSaveConsent = this.roomWebRTC.consentManager?.consentData?.localSave?.status;
        const consentGiven = localSaveConsent?.consent_given === true;
        
        this.logger.debug('💾 shouldSaveLocally check:', {
            storageProvider,
            hasConsentManager: !!this.roomWebRTC.consentManager,
            hasConsentData: !!this.roomWebRTC.consentManager?.consentData,
            hasLocalSave: !!this.roomWebRTC.consentManager?.consentData?.localSave,
            localSaveStatus: localSaveConsent,
            consentGiven
        });
        
        this.logger.debug('💾 DETAILED CONSENT CHECK:', {
            'consentData exists': !!this.roomWebRTC.consentManager?.consentData,
            'localSave exists': !!this.roomWebRTC.consentManager?.consentData?.localSave,
            'status exists': !!this.roomWebRTC.consentManager?.consentData?.localSave?.status,
            'consent_given value': this.roomWebRTC.consentManager?.consentData?.localSave?.status?.consent_given,
            'consent_given === true': this.roomWebRTC.consentManager?.consentData?.localSave?.status?.consent_given === true
        });
        
        return consentGiven;
    }

    /**
     * Determines if recordings should be saved to cloud
     * This is true for:
     * 1. Remote storage providers (Wasabi, Google Drive, etc.)
     * 2. Does NOT require local save consent - cloud save is primary for remote providers
     */
    shouldSaveToCloud(storageProvider) {
        // Cloud storage providers
        return storageProvider !== 'local_device';
    }

    /**
     * Tracks successful upload (called by upload success handlers)
     */
    trackSuccessfulUpload(sizeBytes) {
        this.cumulativeStats.totalUploadedBytes += sizeBytes;
        console.log(`🎥 Tracked successful upload: ${sizeBytes} bytes (total: ${this.cumulativeStats.totalUploadedBytes})`);
    }

    /**
     * Generates filename for recording session: {room_name}-{character_name}-{start_time_utc}
     */
    generateRecordingFilename() {
        const roomName = this.roomWebRTC.roomData.name || 'Unknown-Room';
        
        // Get character name from current participant
        const participants = this.roomWebRTC.roomData.participants || [];
        const currentParticipant = participants.find(p => p.user_id === this.roomWebRTC.currentUserId);
        const characterName = currentParticipant?.character_name || 'Unknown-Character';
        
        // Generate UTC timestamp
        const startTime = new Date(this.originalRecordingStartTime);
        const utcTimestamp = startTime.toISOString().replace(/[:.]/g, '-').replace('T', '_').slice(0, -5); // Remove milliseconds and Z
        
        // Sanitize names for filename (remove special characters)
        const sanitizedRoomName = roomName.replace(/[^a-zA-Z0-9-_]/g, '-');
        const sanitizedCharacterName = characterName.replace(/[^a-zA-Z0-9-_]/g, '-');
        
        // Determine file extension based on MIME type
        const ext = (this.recMime && this.recMime.includes('mp4')) ? 'mp4' : 'webm';
        
        return `${sanitizedRoomName}-${sanitizedCharacterName}-${utcTimestamp}.${ext}`;
    }
}
