import { BaseUploader } from './BaseUploader.js';

/**
 * WasabiUploader - S3-compatible multipart upload implementation
 * 
 * Handles continuous recording uploads to Wasabi using S3 multipart upload API
 */
export class WasabiUploader extends BaseUploader {
    constructor(roomData, recordingSettings) {
        super(roomData, recordingSettings);
        
        // S3 multipart upload state
        this.currentMultipartUploadId = null;
        this.currentSessionKey = null;
        this.currentPartNumber = 0;
        this.uploadedParts = [];
        this.partSizes = [];
    }

    /**
     * Get the provider name
     * @returns {string}
     */
    getProviderName() {
        return 'wasabi';
    }

    /**
     * Initialize Wasabi S3 multipart upload session
     * @param {Object} metadata - Recording metadata
     * @param {Blob} firstBlob - First video chunk
     */
    async initialize(metadata, firstBlob) {
        console.log('🎯 INITIALIZING WASABI MULTIPART UPLOAD SESSION');
        
        const response = await fetch('/api/uploads/s3/multipart/create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.getCsrfToken()
            },
            body: JSON.stringify({
                filename: metadata.filename,
                type: firstBlob.type,
                size: firstBlob.size, // Initial size estimate
                room_id: this.roomData.id,
                started_at_ms: metadata.started_at_ms,
                ended_at_ms: metadata.ended_at_ms
            })
        });

        if (!response.ok) {
            throw new Error(`Failed to create Wasabi multipart upload: ${response.status}`);
        }

        const data = await response.json();
        this.currentMultipartUploadId = data.uploadId;
        this.currentSessionKey = data.key;
        this.currentPartNumber = 0;
        this.uploadedParts = [];
        this.partSizes = [];
        this.currentRecordingFilename = metadata.filename;
        this.recordingStartedAt = metadata.started_at_ms || Date.now();
        this.isUploading = true;
        
        console.log('🎯 WASABI MULTIPART SESSION INITIALIZED:', data.uploadId);
        console.log('🎯 SESSION KEY:', data.key);
        
        // Start recording session in database
        await this.startRecordingSession(
            metadata.filename,
            data.uploadId,
            data.key,
            metadata.started_at_ms || Date.now(),
            firstBlob.type
        );
    }

    /**
     * Upload a video chunk as a part of the multipart upload
     * @param {Blob} blob - Video chunk to upload
     */
    async uploadChunk(blob) {
        if (!this.currentMultipartUploadId) {
            throw new Error('Wasabi multipart upload not initialized');
        }

        this.currentPartNumber++;
        const partNumber = this.currentPartNumber;
        
        console.log(`🎯 UPLOADING WASABI PART ${partNumber}:`, blob.size, 'bytes');
        
        // Get signed URL for this part
        const signResponse = await fetch('/api/uploads/s3/multipart/sign', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.getCsrfToken()
            },
            body: JSON.stringify({ 
                uploadId: this.currentMultipartUploadId, 
                key: this.currentSessionKey, 
                partNumber, 
                room_id: this.roomData.id 
            })
        });

        if (!signResponse.ok) {
            throw new Error(`Failed to sign Wasabi part ${partNumber}: ${signResponse.status}`);
        }

        const { url, headers } = await signResponse.json();
        console.log(`🎯 SIGNED URL FOR WASABI PART ${partNumber}:`, url);
        
        // Upload the part directly to Wasabi S3
        const uploadResponse = await fetch(url, {
            method: 'PUT',
            body: blob,
            headers: headers || {}
        });

        if (!uploadResponse.ok) {
            throw new Error(`Failed to upload Wasabi part ${partNumber}: ${uploadResponse.status}`);
        }

        // Extract ETag from response
        const etag = uploadResponse.headers.get('ETag') || uploadResponse.headers.get('etag');
        console.log(`🎯 WASABI PART ${partNumber} UPLOADED, ETAG:`, etag);
        
        // Store the part info for later completion
        this.uploadedParts.push({
            PartNumber: partNumber,
            ETag: etag
        });
        this.partSizes.push(blob.size);
        this.uploadedBytes += blob.size;
        this.totalChunks++;
        
        // Update recording progress in database
        if (etag && this.currentRecordingId) {
            await this.updateRecordingProgress({
                part_number: partNumber,
                etag: etag,
                part_size_bytes: blob.size,
                ended_at_ms: Date.now()
            });
        }
        
        console.log(`🎯 TOTAL WASABI PARTS UPLOADED: ${this.uploadedParts.length}`);
    }

    /**
     * Finalize the Wasabi S3 multipart upload
     */
    async finalize() {
        if (!this.currentMultipartUploadId || !this.uploadedParts || this.uploadedParts.length === 0) {
            console.log('🎯 NO WASABI MULTIPART UPLOAD TO FINALIZE');
            return;
        }
        
        console.log('🎯 FINALIZING WASABI MULTIPART UPLOAD:', this.currentMultipartUploadId);
        console.log('🎯 PARTS TO COMPLETE:', this.uploadedParts.length);
        
        const payload = {
            uploadId: this.currentMultipartUploadId,
            key: this.currentSessionKey,
            parts: this.uploadedParts,
            room_id: this.roomData.id,
            filename: this.currentRecordingFilename || 'recording.webm',
            size_bytes: this.partSizes.reduce((total, size) => total + size, 0),
            started_at_ms: this.recordingStartedAt || Date.now(),
            ended_at_ms: Date.now(),
            mime: 'video/webm'
        };
        
        console.log('🎯 WASABI COMPLETION REQUEST PAYLOAD:', payload);
        
        try {
            const response = await fetch('/api/uploads/s3/multipart/complete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.getCsrfToken()
                },
                body: JSON.stringify(payload)
            });

            console.log('🎯 WASABI COMPLETION RESPONSE STATUS:', response.status);
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('🎯 WASABI COMPLETION RESPONSE ERROR:', errorText);
                throw new Error(`Failed to complete Wasabi multipart upload: ${response.status} - ${errorText}`);
            }

            const result = await response.json();
            console.log('🎯 WASABI MULTIPART UPLOAD COMPLETED:', result);
            
            // Emit success event
            this.emitRecordingEvent('recording-upload-success', {
                recording_id: result.recording_id,
                provider_file_id: this.currentSessionKey,
                filename: this.currentRecordingFilename
            });
            
            // Reset state
            this.reset();
            
            console.log('🎯 WASABI RECORDING SESSION FINALIZED');
            
        } catch (error) {
            console.error('🎯 ERROR finalizing Wasabi multipart upload:', error);
            
            // Try to abort the upload to clean up
            await this.abort();
            
            // Emit error event
            this.emitRecordingEvent('recording-upload-error', {
                filename: this.currentRecordingFilename,
                error: error.message
            });
            
            throw error;
        }
    }

    /**
     * Abort the Wasabi S3 multipart upload
     */
    async abort() {
        if (!this.currentMultipartUploadId) {
            return;
        }

        try {
            await fetch('/api/uploads/s3/multipart/abort', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.getCsrfToken()
                },
                body: JSON.stringify({
                    uploadId: this.currentMultipartUploadId,
                    key: this.currentSessionKey,
                    room_id: this.roomData.id
                })
            });
            console.log('🎯 ABORTED WASABI MULTIPART UPLOAD');
        } catch (abortError) {
            console.error('🎯 ERROR aborting Wasabi multipart upload:', abortError);
        }
        
        // Reset state anyway
        this.reset();
    }

    /**
     * Reset Wasabi-specific state
     */
    reset() {
        super.reset();
        this.currentMultipartUploadId = null;
        this.currentSessionKey = null;
        this.currentPartNumber = 0;
        this.uploadedParts = [];
        this.partSizes = [];
    }

    /**
     * Get Wasabi-specific upload statistics
     * @returns {Object}
     */
    getUploadStats() {
        return {
            ...super.getUploadStats(),
            multipartUploadId: this.currentMultipartUploadId,
            partNumber: this.currentPartNumber,
            uploadedParts: this.uploadedParts.length
        };
    }
}
