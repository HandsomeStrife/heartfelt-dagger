/**
 * PageProtection - Manages page refresh and navigation protection
 * 
 * Handles protection against data loss during recording sessions,
 * emergency save functionality, and page unload warnings.
 */

export class PageProtection {
    constructor(roomWebRTC) {
        this.roomWebRTC = roomWebRTC;
        this.setupProtection();
    }

    /**
     * Sets up protection against page refresh data loss
     */
    setupProtection() {
        // Warn user if they try to leave/refresh while recording
        window.addEventListener('beforeunload', (event) => {
            console.log('🚨 Page unload detected - performing cleanup');
            const isRecording = this.roomWebRTC.videoRecorder.isCurrentlyRecording();
            const isJoined = this.roomWebRTC.isJoined;
            
            console.log(`  - Is recording: ${isRecording}`);
            console.log(`  - Is joined: ${isJoined}`);
            
            // CRITICAL FIX: Check if StreamSaver download is active (not chunk count)
            // StreamSaver streams directly to disk, so chunks aren't accumulated in memory
            const isStreamingDownloadActive = this.roomWebRTC.streamingDownloader?.isDownloadActive() || false;
            console.log(`  - Streaming download active: ${isStreamingDownloadActive}`);
            
            // CRITICAL: Perform cleanup even if not recording
            // This ensures other peers know we've left
            if (isJoined) {
                console.log('🚨 Broadcasting user-left message to other peers...');
                try {
                    // Broadcast that we're leaving (synchronous)
                    this.roomWebRTC.signalingManager.publishMessage('user-left', {
                        slotId: this.roomWebRTC.currentSlotId
                    });
                } catch (error) {
                    console.error('🚨 Error broadcasting user-left:', error);
                }
                
                // Stop speech recognition
                try {
                    this.roomWebRTC.stopSpeechRecognition();
                } catch (error) {
                    console.error('🚨 Error stopping speech recognition:', error);
                }
            }
            
            // CRITICAL: Show warning if recording/download is active
            // The actual finalization happens in the 'unload' event (VDO.Ninja approach)
            if (isRecording || isStreamingDownloadActive) {
                const message = 'Recording in progress! If you leave now, your recording may be incomplete. Stop recording first to ensure your video is saved.';
                console.log('🚨 Showing page unload warning');
                console.log('📝 Note: Download will finalize in unload event if user proceeds');
                
                event.preventDefault();
                event.returnValue = message;
                return message;
            } else {
                console.log('🚨 Cleanup complete - allowing page unload');
            }
        });

        // Attempt to save recording if page is being unloaded
        // CRITICAL: This event fires AFTER user confirms leaving (or no confirmation needed)
        // We have milliseconds to execute synchronous code before page dies
        window.addEventListener('unload', () => {
            const isStreamingDownloadActive = this.roomWebRTC.streamingDownloader?.isDownloadActive() || false;
            
            // CRITICAL FIX: VDO.Ninja approach - call writer.close() without await
            // This initiates the close, and the Service Worker continues even after page closes
            if (isStreamingDownloadActive && this.roomWebRTC.streamingDownloader.streamWriter) {
                console.log('💾 🚨 UNLOAD: Closing StreamSaver writer (VDO.Ninja method)...');
                try {
                    // VDO.Ninja: Just call close() without await - Service Worker handles it
                    this.roomWebRTC.streamingDownloader.streamWriter.close();
                    console.log('💾 ✅ Writer close initiated - Service Worker will complete download');
                } catch (error) {
                    console.error('💾 Error closing writer on unload:', error);
                }
            }
            
            // Emergency save for cloud recordings (local streaming is already handled above)
            const isRecording = this.roomWebRTC.videoRecorder.isCurrentlyRecording();
            const recordedChunks = this.roomWebRTC.videoRecorder.getRecordedChunks();
            if (isRecording && recordedChunks && recordedChunks.length > 0) {
                console.log('💾 Attempting emergency save for cloud recording...');
                this.emergencySaveRecording();
            }
        });

        // Add pagehide event as additional fallback (more reliable than unload, especially on mobile)
        window.addEventListener('pagehide', (event) => {
            console.log('🚨 Page hide detected');
            const isStreamingDownloadActive = this.roomWebRTC.streamingDownloader?.isDownloadActive() || false;
            
            if (event.persisted) {
                // Page is entering back/forward cache (bfcache) - will be restored
                console.log('🚨 Page entering bfcache, recording preserved');
            } else {
                // Page is being permanently destroyed - finalize streaming download
                console.log('🚨 Page being destroyed permanently');
                
                if (isStreamingDownloadActive && this.roomWebRTC.streamingDownloader.streamWriter) {
                    console.log('💾 pagehide: Closing StreamSaver writer...');
                    try {
                        this.roomWebRTC.streamingDownloader.streamWriter.close();
                    } catch (error) {
                        console.error('💾 pagehide: Error closing writer:', error);
                    }
                }
            }
        });
    }

    /**
     * Emergency save when page is being closed
     */
    emergencySaveRecording() {
        try {
            const recordedChunks = this.roomWebRTC.videoRecorder.getRecordedChunks();
            if (recordedChunks.length === 0) return;
            
            console.warn('🚨 Emergency save: Page closing with active recording');
            
            // Create emergency download
            const mimeType = this.roomWebRTC.videoRecorder.getRecordingMimeType();
            const combinedBlob = new Blob(recordedChunks, { type: mimeType });
            const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
            const ext = mimeType.includes('webm') ? 'webm' : 'mp4';
            const emergencyFilename = `room-recording-EMERGENCY-${timestamp}.${ext}`;
            
            // Use Navigator.sendBeacon if available for more reliable delivery
            if (navigator.sendBeacon) {
                // Can't use sendBeacon for downloads, but we can at least log the attempt
                console.warn('🚨 Recording data exists but cannot be saved during page unload');
                console.warn('🚨 Please stop recording properly before leaving the page');
            } else {
                // Fallback: try immediate download (may not work)
                const url = URL.createObjectURL(combinedBlob);
                const link = document.createElement('a');
                link.href = url;
                link.download = emergencyFilename;
                link.click();
                console.warn(`🚨 Emergency download attempted: ${emergencyFilename}`);
            }
        } catch (error) {
            console.error('🚨 Emergency save failed:', error);
        }
    }

}
