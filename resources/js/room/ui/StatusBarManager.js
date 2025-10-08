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
        // Note: "Stop and Save Recording" buttons removed - recording now automatically 
        // finalizes when user clicks "Leave Room" button
        
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
        const cumulativeStats = this.roomWebRTC.videoRecorder.getCumulativeStats();
        
        // For cloud storage, show upload progress; for local storage, show nothing extra
        let displayText = '';
        if (storageProvider !== 'local_device') {
            const uploadedSize = cumulativeStats.totalUploadedBytes;
            displayText = `${(uploadedSize / 1024 / 1024).toFixed(1)} MB uploaded`;
        }

        // Update DOM elements (try both campaign and normal layout IDs)
        const durationEl = document.getElementById('recording-duration') || document.getElementById('recording-duration-normal');
        const chunksEl = document.getElementById('recording-chunks') || document.getElementById('recording-chunks-normal');

        if (durationEl) durationEl.textContent = durationText;
        if (chunksEl) chunksEl.textContent = displayText;
    }

    /**
     * Shows transcript modal (placeholder for now)
     */
    showTranscriptModal() {
        // TODO: Implement transcript modal
        alert('Transcript feature coming soon!\n\nFor now, check the browser console for STT output.');
        console.log('🎤 Current speech buffer:', this.roomWebRTC.currentSpeechModule?.getSpeechBuffer?.() || []);
    }

    /**
     * Shows upload error in status bar
     * @param {string} errorMessage - Error message to display
     * @param {string} provider - Storage provider name
     */
    showUploadError(errorMessage, provider) {
        console.error(`🎥 UPLOAD ERROR (${provider}):`, errorMessage);
        
        // Update status elements for both layouts
        this.updateErrorDisplay(errorMessage, provider, 'error');
        
        // Show toast notification for critical errors
        this.showErrorToast(provider, errorMessage);
    }

    /**
     * Shows retry status in status bar
     * @param {number} retryCount - Current retry attempt
     * @param {number} maxRetries - Maximum retry attempts
     * @param {string} provider - Storage provider name
     */
    showUploadRetry(retryCount, maxRetries, provider) {
        console.log(`🎥 UPLOAD RETRY (${provider}): ${retryCount}/${maxRetries}`);
        
        const retryMessage = `Retrying upload (${retryCount}/${maxRetries})...`;
        this.updateErrorDisplay(retryMessage, provider, 'retry');
    }

    /**
     * Shows upload success (clears error state)
     * @param {string} provider - Storage provider name
     */
    showUploadSuccess(provider) {
        console.log(`🎥 UPLOAD SUCCESS (${provider})`);
        
        // Clear error state
        this.clearErrorDisplay();
        
        // Restore normal status display
        this.updateRecordingStatus();
    }

    /**
     * Updates error display in status bar
     * @param {string} message - Message to display
     * @param {string} provider - Storage provider name
     * @param {string} type - Error type: 'error' or 'retry'
     */
    updateErrorDisplay(message, provider, type) {
        const chunksEl = document.getElementById('recording-chunks') || 
                        document.getElementById('recording-chunks-normal');
        
        if (!chunksEl) return;
        
        // Format message based on type
        let displayMessage = '';
        let classes = [];
        
        if (type === 'error') {
            displayMessage = `⚠️ ${this.truncateError(message)}`;
            classes = ['text-red-400', 'font-semibold'];
        } else if (type === 'retry') {
            displayMessage = `🔄 ${message}`;
            classes = ['text-yellow-400', 'font-semibold'];
        }
        
        // Remove old state classes
        chunksEl.classList.remove('text-red-400', 'text-yellow-400', 'text-slate-400', 'font-semibold');
        
        // Add new state classes
        classes.forEach(cls => chunksEl.classList.add(cls));
        
        // Update content
        chunksEl.textContent = displayMessage;
    }

    /**
     * Clears error display from status bar
     */
    clearErrorDisplay() {
        const chunksEl = document.getElementById('recording-chunks') || 
                        document.getElementById('recording-chunks-normal');
        
        if (!chunksEl) return;
        
        // Remove error state classes
        chunksEl.classList.remove('text-red-400', 'text-yellow-400', 'font-semibold');
        chunksEl.classList.add('text-slate-400');
    }

    /**
     * Shows error toast notification
     * @param {string} provider - Storage provider name
     * @param {string} errorMessage - Error message
     */
    showErrorToast(provider, errorMessage) {
        const providerName = this.getProviderDisplayName(provider);
        const truncatedMessage = this.truncateError(errorMessage, 100);
        
        // Use Livewire toast if available
        if (window.Livewire) {
            try {
                window.Livewire.dispatch('show-toast', {
                    type: 'error',
                    message: `Recording upload to ${providerName} failed: ${truncatedMessage}`,
                    duration: 10000
                });
            } catch (e) {
                console.warn('Failed to show Livewire toast:', e);
                // Fallback to console
                console.error(`Recording upload to ${providerName} failed: ${truncatedMessage}`);
            }
        } else {
            console.error(`Recording upload to ${providerName} failed: ${truncatedMessage}`);
        }
    }

    /**
     * Gets human-readable provider name
     * @param {string} provider - Provider identifier
     * @returns {string}
     */
    getProviderDisplayName(provider) {
        const names = {
            'google_drive': 'Google Drive',
            'wasabi': 'Wasabi',
            'local_device': 'Local Device',
            'local': 'Local Device'
        };
        return names[provider] || provider;
    }

    /**
     * Truncates error message for display
     * @param {string} message - Error message
     * @param {number} maxLength - Maximum length
     * @returns {string}
     */
    truncateError(message, maxLength = 50) {
        if (!message) return 'Unknown error';
        if (message.length <= maxLength) return message;
        return message.substring(0, maxLength) + '...';
    }

    /**
     * Shows connection warning in status bar
     * @param {string} message - Warning message
     */
    showConnectionWarning(message) {
        const statusElement = document.getElementById('recording-status') || document.getElementById('recording-status-normal');
        if (!statusElement) return;
        
        // Find or create warning element
        let warningElement = statusElement.querySelector('.connection-warning');
        if (!warningElement) {
            warningElement = document.createElement('span');
            warningElement.className = 'connection-warning text-yellow-400 text-sm ml-2';
            statusElement.appendChild(warningElement);
        }
        
        warningElement.textContent = message;
        console.warn('🔌 Connection warning:', message);
    }

    /**
     * Shows connection error in status bar
     * @param {string} message - Error message
     */
    showConnectionError(message) {
        const statusElement = document.getElementById('recording-status') || document.getElementById('recording-status-normal');
        if (!statusElement) return;
        
        // Find or create error element
        let errorElement = statusElement.querySelector('.connection-error');
        if (!errorElement) {
            errorElement = document.createElement('span');
            errorElement.className = 'connection-error text-red-400 text-sm ml-2';
            statusElement.appendChild(errorElement);
        }
        
        errorElement.textContent = message;
        console.error('🔌 Connection error:', message);
    }

    /**
     * Clears connection warnings/errors from status bar
     */
    clearConnectionWarnings() {
        const statusElement = document.getElementById('recording-status') || document.getElementById('recording-status-normal');
        if (!statusElement) return;
        
        // Remove warning and error elements
        const warningElement = statusElement.querySelector('.connection-warning');
        const errorElement = statusElement.querySelector('.connection-error');
        
        if (warningElement) warningElement.remove();
        if (errorElement) errorElement.remove();
        
        console.log('✅ Connection warnings cleared');
    }
}
