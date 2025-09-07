/**
 * Client-side video thumbnail generator
 * Generates thumbnails from video URLs without server processing
 */

class VideoThumbnailGenerator {
    constructor() {
        this.canvas = null;
        this.context = null;
        this.initCanvas();
    }

    initCanvas() {
        this.canvas = document.createElement('canvas');
        this.context = this.canvas.getContext('2d');
        this.canvas.width = 320;  // Standard thumbnail width
        this.canvas.height = 180; // 16:9 aspect ratio
    }

    /**
     * Generate thumbnail from video URL
     * @param {string} videoUrl - The video URL
     * @param {number} timeOffset - Time in seconds to capture (default: 5)
     * @returns {Promise<string>} Base64 encoded thumbnail image
     */
    async generateThumbnail(videoUrl, timeOffset = 5) {
        return new Promise((resolve, reject) => {
            const video = document.createElement('video');
            video.crossOrigin = 'anonymous';
            video.muted = true;
            video.preload = 'metadata';

            video.onloadedmetadata = () => {
                // Ensure we don't seek beyond video duration
                const seekTime = Math.min(timeOffset, video.duration - 1);
                video.currentTime = seekTime;
            };

            video.onseeked = () => {
                try {
                    // Draw video frame to canvas
                    this.context.drawImage(video, 0, 0, this.canvas.width, this.canvas.height);
                    
                    // Convert canvas to base64 image
                    const thumbnail = this.canvas.toDataURL('image/jpeg', 0.8);
                    
                    // Clean up
                    video.remove();
                    
                    resolve(thumbnail);
                } catch (error) {
                    reject(new Error(`Failed to generate thumbnail: ${error.message}`));
                }
            };

            video.onerror = (error) => {
                reject(new Error(`Video loading failed: ${error.message || 'Unknown error'}`));
            };

            video.ontimeupdate = () => {
                // Fallback if onseeked doesn't fire
                if (Math.abs(video.currentTime - timeOffset) < 0.5) {
                    video.ontimeupdate = null;
                    video.onseeked();
                }
            };

            // Start loading the video
            video.src = videoUrl;
            video.load();
        });
    }

    /**
     * Generate multiple thumbnails at different time points
     * @param {string} videoUrl - The video URL
     * @param {number[]} timeOffsets - Array of time offsets in seconds
     * @returns {Promise<string[]>} Array of base64 encoded thumbnails
     */
    async generateMultipleThumbnails(videoUrl, timeOffsets = [5, 15, 30]) {
        const thumbnails = [];
        
        for (const offset of timeOffsets) {
            try {
                const thumbnail = await this.generateThumbnail(videoUrl, offset);
                thumbnails.push(thumbnail);
            } catch (error) {
                console.warn(`Failed to generate thumbnail at ${offset}s:`, error);
                thumbnails.push(null);
            }
        }
        
        return thumbnails;
    }

    /**
     * Upload thumbnail to server
     * @param {string} thumbnailBase64 - Base64 encoded thumbnail
     * @param {number} recordingId - Recording ID
     * @returns {Promise<string>} Thumbnail URL
     */
    async uploadThumbnail(thumbnailBase64, recordingId) {
        try {
            const response = await fetch('/api/recordings/thumbnail', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    recording_id: recordingId,
                    thumbnail: thumbnailBase64
                })
            });

            if (!response.ok) {
                throw new Error(`Upload failed: ${response.statusText}`);
            }

            const result = await response.json();
            return result.thumbnail_url;
        } catch (error) {
            console.error('Thumbnail upload failed:', error);
            throw error;
        }
    }

    /**
     * Process recording for thumbnail generation
     * @param {number} recordingId - Recording ID
     * @param {string} videoUrl - Video URL for thumbnail generation
     * @returns {Promise<string>} Thumbnail URL
     */
    async processRecording(recordingId, videoUrl) {
        try {
            console.log(`Generating thumbnail for recording ${recordingId}`);
            
            // Generate thumbnail
            const thumbnail = await this.generateThumbnail(videoUrl);
            
            // Upload to server
            const thumbnailUrl = await this.uploadThumbnail(thumbnail, recordingId);
            
            console.log(`Thumbnail generated and uploaded for recording ${recordingId}`);
            return thumbnailUrl;
            
        } catch (error) {
            console.error(`Failed to process recording ${recordingId}:`, error);
            throw error;
        }
    }
}

// Export for use in other modules
window.VideoThumbnailGenerator = VideoThumbnailGenerator;

// Create global instance
window.videoThumbnailGenerator = new VideoThumbnailGenerator();

export default VideoThumbnailGenerator;
