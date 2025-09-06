/**
 * CloudUploader - Handles uploads to cloud storage providers
 * 
 * Manages uploads to S3-compatible storage providers, handles upload queues,
 * retry logic, and coordinates with Uppy for advanced upload handling.
 */
export class CloudUploader {
    constructor(roomWebRTC) {
        this.roomWebRTC = roomWebRTC;
        this.uploadQueue = [];
        this.activeUploads = new Map(); // Map of uploadId -> upload info
        this.failedUploads = [];
        this.uploadStats = {
            totalUploads: 0,
            successfulUploads: 0,
            failedUploads: 0,
            totalBytesUploaded: 0
        };
        
        // Upload configuration
        this.maxConcurrentUploads = 3;
        this.maxRetries = 3;
        this.retryDelay = 1000; // Start with 1 second
        this.maxRetryDelay = 30000; // Max 30 seconds
        this.chunkTimeout = 60000; // 60 second timeout per chunk
        
        this.setupUploadHandling();
    }

    /**
     * Sets up upload handling capabilities
     */
    setupUploadHandling() {
        // Check if Uppy is available for advanced upload handling
        this.hasUppy = typeof window.roomUppy !== 'undefined' && window.roomUppy !== null;
        
        console.log('☁️ Cloud upload capabilities:', {
            hasUppy: this.hasUppy,
            maxConcurrentUploads: this.maxConcurrentUploads,
            maxRetries: this.maxRetries
        });
    }

    /**
     * Uploads a video chunk to cloud storage
     */
    async uploadVideoChunk(blob, recordingData) {
        const uploadId = this.generateUploadId();
        
        console.log(`☁️ Queuing video chunk upload: ${uploadId}`, {
            size: blob.size,
            filename: recordingData.filename,
            chunkIndex: recordingData.chunkIndex
        });
        
        const uploadTask = {
            id: uploadId,
            blob: blob,
            recordingData: recordingData,
            attempts: 0,
            queuedAt: Date.now(),
            status: 'queued'
        };
        
        this.uploadQueue.push(uploadTask);
        this.uploadStats.totalUploads++;
        
        // Process upload queue
        this.processUploadQueue();
        
        return uploadId;
    }

    /**
     * Processes the upload queue
     */
    async processUploadQueue() {
        // Check if we can start more uploads
        while (this.activeUploads.size < this.maxConcurrentUploads && this.uploadQueue.length > 0) {
            const uploadTask = this.uploadQueue.shift();
            this.startUpload(uploadTask);
        }
    }

    /**
     * Starts an individual upload
     */
    async startUpload(uploadTask) {
        uploadTask.status = 'uploading';
        uploadTask.startedAt = Date.now();
        this.activeUploads.set(uploadTask.id, uploadTask);
        
        console.log(`☁️ Starting upload: ${uploadTask.id} (attempt ${uploadTask.attempts + 1})`);
        
        try {
            if (this.hasUppy) {
                await this.uploadWithUppy(uploadTask);
            } else {
                await this.uploadWithFetch(uploadTask);
            }
            
            // Upload successful
            this.handleUploadSuccess(uploadTask);
        } catch (error) {
            // Upload failed
            this.handleUploadFailure(uploadTask, error);
        }
    }

    /**
     * Uploads using Uppy for advanced handling
     */
    async uploadWithUppy(uploadTask) {
        console.log(`☁️ Uploading with Uppy: ${uploadTask.id}`);
        
        try {
            await window.roomUppy.uploadVideoBlob(uploadTask.blob, uploadTask.recordingData);
            console.log(`☁️ ✅ Uppy upload completed: ${uploadTask.id}`);
        } catch (error) {
            console.error(`☁️ ❌ Uppy upload failed: ${uploadTask.id}`, error);
            throw error;
        }
    }

    /**
     * Uploads using direct fetch as fallback
     */
    async uploadWithFetch(uploadTask) {
        console.log(`☁️ Uploading with fetch: ${uploadTask.id}`);
        
        const formData = new FormData();
        formData.append('video', uploadTask.blob, uploadTask.recordingData.filename);
        formData.append('metadata', JSON.stringify(uploadTask.recordingData));
        
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), this.chunkTimeout);
        
        try {
            const response = await fetch(`/api/rooms/${this.roomWebRTC.roomData.id}/recordings`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData,
                signal: controller.signal
            });
            
            clearTimeout(timeoutId);
            
            if (!response.ok) {
                throw new Error(`Upload failed: ${response.status} ${response.statusText}`);
            }
            
            const result = await response.json();
            console.log(`☁️ ✅ Fetch upload completed: ${uploadTask.id}`, result);
        } catch (error) {
            clearTimeout(timeoutId);
            console.error(`☁️ ❌ Fetch upload failed: ${uploadTask.id}`, error);
            throw error;
        }
    }

    /**
     * Handles successful upload
     */
    handleUploadSuccess(uploadTask) {
        console.log(`☁️ ✅ Upload successful: ${uploadTask.id}`);
        
        uploadTask.status = 'completed';
        uploadTask.completedAt = Date.now();
        
        // Update stats
        this.uploadStats.successfulUploads++;
        this.uploadStats.totalBytesUploaded += uploadTask.blob.size;
        
        // Remove from active uploads
        this.activeUploads.delete(uploadTask.id);
        
        // Process next uploads in queue
        this.processUploadQueue();
        
        // Log progress
        this.logUploadProgress();
    }

    /**
     * Handles failed upload
     */
    async handleUploadFailure(uploadTask, error) {
        console.error(`☁️ ❌ Upload failed: ${uploadTask.id}`, error);
        
        uploadTask.attempts++;
        uploadTask.lastError = error;
        uploadTask.status = 'failed';
        
        // Remove from active uploads
        this.activeUploads.delete(uploadTask.id);
        
        // Check if we should retry
        if (uploadTask.attempts < this.maxRetries) {
            console.log(`☁️ 🔄 Retrying upload: ${uploadTask.id} (attempt ${uploadTask.attempts + 1}/${this.maxRetries})`);
            
            // Calculate retry delay with exponential backoff
            const delay = Math.min(
                this.retryDelay * Math.pow(2, uploadTask.attempts - 1),
                this.maxRetryDelay
            );
            
            // Schedule retry
            setTimeout(() => {
                uploadTask.status = 'queued';
                this.uploadQueue.unshift(uploadTask); // Add to front of queue
                this.processUploadQueue();
            }, delay);
        } else {
            // Max retries exceeded
            console.error(`☁️ 💀 Upload permanently failed: ${uploadTask.id}`);
            uploadTask.status = 'permanently_failed';
            this.failedUploads.push(uploadTask);
            this.uploadStats.failedUploads++;
        }
        
        // Process next uploads in queue
        this.processUploadQueue();
        
        // Log progress
        this.logUploadProgress();
    }

    /**
     * Logs upload progress
     */
    logUploadProgress() {
        const { totalUploads, successfulUploads, failedUploads, totalBytesUploaded } = this.uploadStats;
        const activeCount = this.activeUploads.size;
        const queuedCount = this.uploadQueue.length;
        
        console.log(`☁️ Upload Progress: ${successfulUploads}/${totalUploads} completed, ${failedUploads} failed, ${activeCount} active, ${queuedCount} queued`);
        console.log(`☁️ Total uploaded: ${(totalBytesUploaded / 1024 / 1024).toFixed(2)} MB`);
    }

    /**
     * Generates unique upload ID
     */
    generateUploadId() {
        return `upload_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
    }

    /**
     * Retries failed uploads
     */
    retryFailedUploads() {
        console.log(`☁️ Retrying ${this.failedUploads.length} failed uploads`);
        
        const uploadsToRetry = [...this.failedUploads];
        this.failedUploads = [];
        
        uploadsToRetry.forEach(uploadTask => {
            // Reset attempt count for manual retry
            uploadTask.attempts = 0;
            uploadTask.status = 'queued';
            uploadTask.lastError = null;
            
            this.uploadQueue.push(uploadTask);
        });
        
        this.processUploadQueue();
    }

    /**
     * Cancels all pending uploads
     */
    cancelAllUploads() {
        console.log('☁️ Cancelling all uploads');
        
        // Clear upload queue
        this.uploadQueue = [];
        
        // Cancel active uploads (if possible)
        this.activeUploads.forEach((uploadTask, uploadId) => {
            console.log(`☁️ Cancelling active upload: ${uploadId}`);
            uploadTask.status = 'cancelled';
        });
        
        this.activeUploads.clear();
        
        console.log('☁️ All uploads cancelled');
    }

    /**
     * Gets upload statistics
     */
    getUploadStats() {
        return {
            ...this.uploadStats,
            activeUploads: this.activeUploads.size,
            queuedUploads: this.uploadQueue.length,
            permanentlyFailedUploads: this.failedUploads.length,
            successRate: this.uploadStats.totalUploads > 0 ? 
                (this.uploadStats.successfulUploads / this.uploadStats.totalUploads) * 100 : 0
        };
    }

    /**
     * Gets detailed upload status
     */
    getUploadStatus() {
        const activeUploads = Array.from(this.activeUploads.values()).map(task => ({
            id: task.id,
            filename: task.recordingData.filename,
            size: task.blob.size,
            attempts: task.attempts,
            status: task.status,
            startedAt: task.startedAt,
            duration: task.startedAt ? Date.now() - task.startedAt : 0
        }));
        
        const queuedUploads = this.uploadQueue.map(task => ({
            id: task.id,
            filename: task.recordingData.filename,
            size: task.blob.size,
            attempts: task.attempts,
            status: task.status,
            queuedAt: task.queuedAt,
            waitTime: Date.now() - task.queuedAt
        }));
        
        const failedUploads = this.failedUploads.map(task => ({
            id: task.id,
            filename: task.recordingData.filename,
            size: task.blob.size,
            attempts: task.attempts,
            status: task.status,
            lastError: task.lastError?.message
        }));
        
        return {
            active: activeUploads,
            queued: queuedUploads,
            failed: failedUploads,
            stats: this.getUploadStats()
        };
    }

    /**
     * Waits for all uploads to complete
     */
    async waitForAllUploads(timeout = 300000) { // 5 minute default timeout
        return new Promise((resolve, reject) => {
            const startTime = Date.now();
            
            const checkCompletion = () => {
                const hasActiveOrQueued = this.activeUploads.size > 0 || this.uploadQueue.length > 0;
                const hasTimedOut = Date.now() - startTime > timeout;
                
                if (!hasActiveOrQueued) {
                    console.log('☁️ ✅ All uploads completed');
                    resolve(this.getUploadStats());
                } else if (hasTimedOut) {
                    console.warn('☁️ ⏰ Upload completion timeout');
                    reject(new Error('Upload completion timeout'));
                } else {
                    // Check again in 1 second
                    setTimeout(checkCompletion, 1000);
                }
            };
            
            checkCompletion();
        });
    }

    /**
     * Destroys the cloud uploader
     */
    destroy() {
        console.log('☁️ Destroying CloudUploader');
        
        // Cancel all uploads
        this.cancelAllUploads();
        
        // Clear all data
        this.failedUploads = [];
        this.uploadStats = {
            totalUploads: 0,
            successfulUploads: 0,
            failedUploads: 0,
            totalBytesUploaded: 0
        };
        
        console.log('☁️ CloudUploader destroyed');
    }
}
