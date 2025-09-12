/**
 * CloudUploader - Manages cloud storage uploads for video recording
 * 
 * Handles uploading video chunks to cloud storage providers
 * like Wasabi, Google Drive, etc. via Uppy or direct upload.
 */

export class CloudUploader {
    constructor(roomWebRTC) {
        this.roomWebRTC = roomWebRTC;
    }

    /**
     * Uploads a video chunk to storage (cloud or local)
     */
    async uploadChunk(blob, recordingData) {
        try {
            // Check if this is a local save request
            if (recordingData.isLocalSave) {
                // For local saving, use the streaming downloader for better user experience
                if (this.roomWebRTC.streamingDownloader) {
                    this.roomWebRTC.streamingDownloader.addChunk(blob, recordingData);
                    console.log('🎬 Video chunk added to local streaming download');
                } else {
                    // Fallback to direct download method
                    await this.saveVideoChunkLocally(blob, recordingData);
                }
                return;
            }
            
            // For cloud uploads, use Uppy for advanced upload handling
            if (window.roomUppy) {
                await window.roomUppy.uploadVideoBlob(blob, recordingData);
                console.log('🎬 Video chunk queued for upload via Uppy');
            } else {
                throw new Error('RoomUppy not available - direct uploads to storage providers required');
            }
        } catch (error) {
            console.error('🎬 Error uploading video chunk:', error);
            throw error;
        }
    }


    /**
     * Saves video chunk directly to user's computer as a download (legacy method)
     */
    async saveVideoChunkLocally(blob, recordingData) {
        try {
            // Create a download link for the video chunk
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = recordingData.filename;
            
            // Add to document, click, and remove
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            // Clean up the object URL
            URL.revokeObjectURL(url);
            
            console.log(`💾 Video chunk saved locally: ${recordingData.filename}`);
        } catch (error) {
            console.error('💾 Error saving video chunk locally:', error);
            throw error;
        }
    }
}
