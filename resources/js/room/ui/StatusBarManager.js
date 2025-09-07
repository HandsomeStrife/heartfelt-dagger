/**
 * StatusBarManager - Manages status bar display and controls
 * 
 * Handles showing/hiding recording status, updating recording information,
 * and managing status bar control event listeners.
 */

export class StatusBarManager {
    constructor(roomWebRTC) {
        this.roomWebRTC = roomWebRTC;
        this.recordingTimer = null;
    }

    /**
     * Shows the recording status bar
     */
    showRecordingStatus() {
        console.log('🎥 === Showing Recording Status ===');
        
        // Show recording status elements and hide room info
        const recordingStatus = document.getElementById('recording-status') || document.getElementById('recording-status-normal');
        const recordingControls = document.getElementById('recording-controls') || document.getElementById('recording-controls-normal');
        const roomInfo = document.getElementById('room-info') || document.getElementById('room-info-normal');
        
        console.log(`🎥 Recording status element found: ${!!recordingStatus}`);
        console.log(`🎥 Recording controls element found: ${!!recordingControls}`);
        console.log(`🎥 Room info element found: ${!!roomInfo}`);
        
        if (recordingStatus) {
            recordingStatus.classList.remove('hidden');
            recordingStatus.classList.add('flex');
            console.log('🎥 ✅ Recording status shown');
        }
        
        if (recordingControls) {
            recordingControls.classList.remove('hidden');
            recordingControls.classList.add('flex');
            console.log('🎥 ✅ Recording controls shown');
        }
        
        if (roomInfo) {
            roomInfo.classList.add('hidden');
            console.log('🎥 ✅ Room info hidden');
        }
        
        this.setupStatusBarControls();
        this.startRecordingTimer();
    }

    /**
     * Hides the recording status bar
     */
    hideRecordingStatus() {
        console.log('🎥 === Hiding Recording Status ===');
        
        // Hide recording status elements and show room info
        const recordingStatus = document.getElementById('recording-status') || document.getElementById('recording-status-normal');
        const recordingControls = document.getElementById('recording-controls') || document.getElementById('recording-controls-normal');
        const roomInfo = document.getElementById('room-info') || document.getElementById('room-info-normal');
        
        if (recordingStatus) {
            recordingStatus.classList.add('hidden');
            recordingStatus.classList.remove('flex');
            console.log('🎥 ✅ Recording status hidden');
        }
        
        if (recordingControls) {
            recordingControls.classList.add('hidden');
            recordingControls.classList.remove('flex');
            console.log('🎥 ✅ Recording controls hidden');
        }
        
        if (roomInfo) {
            roomInfo.classList.remove('hidden');
            console.log('🎥 ✅ Room info shown');
        }
        
        this.stopRecordingTimer();
    }

    /**
     * Sets up status bar control event listeners
     */
    setupStatusBarControls() {
        // Stop recording buttons (both layouts)
        const stopBtn = document.getElementById('stop-recording-btn');
        const stopBtnNormal = document.getElementById('stop-recording-btn-normal');
        
        if (stopBtn) {
            stopBtn.onclick = () => this.roomWebRTC.videoRecorder.stopRecording();
        }
        if (stopBtnNormal) {
            stopBtnNormal.onclick = () => this.roomWebRTC.videoRecorder.stopRecording();
        }

        // View transcript buttons (both layouts)
        const transcriptBtn = document.getElementById('view-transcript-btn');
        const transcriptBtnNormal = document.getElementById('view-transcript-btn-normal');
        
        if (transcriptBtn) {
            transcriptBtn.onclick = () => this.showTranscriptModal();
        }
        if (transcriptBtnNormal) {
            transcriptBtnNormal.onclick = () => this.showTranscriptModal();
        }

        // Leave room buttons (always visible)
        const leaveBtn = document.getElementById('leave-room-btn');
        const leaveBtnNormal = document.getElementById('leave-room-btn-normal');
        
        if (leaveBtn) {
            leaveBtn.onclick = () => this.roomWebRTC.leaveRoom();
        }
        if (leaveBtnNormal) {
            leaveBtnNormal.onclick = () => this.roomWebRTC.leaveRoom();
        }
    }

    /**
     * Starts the recording timer for status bar
     */
    startRecordingTimer() {
        if (this.recordingTimer) return;
        
        this.recordingTimer = setInterval(() => {
            this.updateRecordingStatus();
        }, 1000); // Update every second
    }

    /**
     * Stops the recording timer
     */
    stopRecordingTimer() {
        if (this.recordingTimer) {
            clearInterval(this.recordingTimer);
            this.recordingTimer = null;
        }
    }

    /**
     * Updates the recording status display
     */
    updateRecordingStatus() {
        const originalStartTime = this.roomWebRTC.videoRecorder.getOriginalRecordingStartTime();
        
        if (!this.roomWebRTC.videoRecorder.isCurrentlyRecording() || !originalStartTime) return;

        // Calculate total recording duration (from original start time, never reset)
        const duration = Math.floor((Date.now() - originalStartTime) / 1000);
        const minutes = Math.floor(duration / 60);
        const seconds = duration % 60;
        const durationText = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

        // Get storage provider to determine what to show
        const storageProvider = this.roomWebRTC.roomData.recording_settings?.storage_provider || 'local_device';
        
        // Get cumulative statistics
        const totalSize = this.roomWebRTC.videoRecorder.getTotalRecordedSize();
        const totalChunks = this.roomWebRTC.videoRecorder.getTotalChunks();
        const cumulativeStats = this.roomWebRTC.videoRecorder.getCumulativeStats();
        
        // Estimate current recording size based on duration and bitrate
        const estimatedCurrentSize = this.estimateCurrentRecordingSize(duration, totalSize);
        const sizeText = `${(estimatedCurrentSize / 1024 / 1024).toFixed(1)} MB`;
        
        // For cloud storage, show upload progress; for local storage, show nothing extra
        let displayText = '';
        if (storageProvider !== 'local_device') {
            const uploadedSize = cumulativeStats.totalUploadedBytes;
            displayText = `${(uploadedSize / 1024 / 1024).toFixed(1)} MB uploaded`;
        }

        // Update DOM elements (try both campaign and normal layout IDs)
        const durationEl = document.getElementById('recording-duration') || document.getElementById('recording-duration-normal');
        const sizeEl = document.getElementById('recording-size') || document.getElementById('recording-size-normal');
        const chunksEl = document.getElementById('recording-chunks') || document.getElementById('recording-chunks-normal');

        if (durationEl) durationEl.textContent = durationText;
        if (sizeEl) sizeEl.textContent = sizeText;
        if (chunksEl) chunksEl.textContent = displayText;
    }

    /**
     * Estimates current recording size based on duration and existing data
     */
    estimateCurrentRecordingSize(currentDurationSeconds, recordedSize) {
        if (currentDurationSeconds === 0) return 0;
        
        // If we have recorded data, calculate average bitrate
        if (recordedSize > 0) {
            const recordedDuration = this.roomWebRTC.videoRecorder.getCumulativeStats().totalChunks * 15; // 15 seconds per chunk
            if (recordedDuration > 0) {
                const avgBytesPerSecond = recordedSize / recordedDuration;
                return recordedSize + (avgBytesPerSecond * (currentDurationSeconds - recordedDuration));
            }
        }
        
        // Fallback: estimate based on typical WebM bitrate (1-2 Mbps for video + audio)
        const estimatedBytesPerSecond = 200000; // ~1.6 Mbps average
        return currentDurationSeconds * estimatedBytesPerSecond;
    }

    /**
     * Shows transcript modal (placeholder for now)
     */
    showTranscriptModal() {
        // TODO: Implement transcript modal
        alert('Transcript feature coming soon!\n\nFor now, check the browser console for STT output.');
        console.log('🎤 Current speech buffer:', this.roomWebRTC.currentSpeechModule?.getSpeechBuffer?.() || []);
    }
}
