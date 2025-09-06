/**
 * StatusBarManager - Manages the bottom status bar UI for room sessions
 * 
 * Handles the persistent bottom status bar that shows recording status,
 * provides controls for viewing transcript, stopping recording, and leaving the room.
 */
export class StatusBarManager {
    constructor(roomWebRTC) {
        this.roomWebRTC = roomWebRTC;
        this.statusBar = null;
        this.recordingTimer = null;
        this.recordingStartTime = null;
        
        this.setupStatusBar();
    }

    /**
     * Sets up the status bar controls (always visible with leave button)
     */
    setupStatusBar() {
        // Create status bar container
        this.statusBar = document.createElement('div');
        this.statusBar.id = 'room-status-bar';
        this.statusBar.className = 'fixed bottom-0 left-0 right-0 bg-black/90 backdrop-blur-sm border-t border-slate-700 px-4 py-2 z-40';
        this.statusBar.style.height = '60px'; // Fixed height to prevent layout shift
        
        // Create status bar content
        this.statusBar.innerHTML = `
            <div class="flex items-center justify-between h-full max-w-7xl mx-auto">
                <!-- Left side: Recording status -->
                <div class="flex items-center space-x-4">
                    <div id="recording-status" class="hidden flex items-center space-x-2">
                        <div class="w-3 h-3 bg-red-500 rounded-full animate-pulse"></div>
                        <span class="text-white text-sm font-medium">Recording</span>
                        <span id="recording-timer" class="text-slate-300 text-sm">00:00</span>
                    </div>
                    <div id="idle-status" class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-slate-500 rounded-full"></div>
                        <span class="text-slate-400 text-sm">Ready</span>
                    </div>
                </div>
                
                <!-- Center: Action buttons (shown when recording) -->
                <div id="recording-actions" class="hidden flex items-center space-x-3">
                    <button id="view-transcript-btn" 
                            class="px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-white text-sm rounded-md transition-colors duration-200 flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>View Transcript</span>
                    </button>
                    <button id="stop-save-btn" 
                            class="px-4 py-1.5 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md transition-colors duration-200 flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>
                        </svg>
                        <span>Stop & Save</span>
                    </button>
                </div>
                
                <!-- Right side: Leave button (always visible) -->
                <div class="flex items-center">
                    <button id="leave-room-btn" 
                            class="px-4 py-1.5 bg-slate-700 hover:bg-slate-600 text-white text-sm font-medium rounded-md transition-colors duration-200 flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span>Leave Room</span>
                    </button>
                </div>
            </div>
        `;
        
        // Append to body
        document.body.appendChild(this.statusBar);
        
        // Add bottom padding to main content to prevent overlap
        const mainContent = document.querySelector('main') || document.body;
        if (mainContent) {
            mainContent.style.paddingBottom = '60px';
        }
        
        this.setupEventListeners();
    }

    /**
     * Sets up event listeners for status bar buttons
     */
    setupEventListeners() {
        // View transcript button
        const viewTranscriptBtn = this.statusBar.querySelector('#view-transcript-btn');
        if (viewTranscriptBtn) {
            viewTranscriptBtn.addEventListener('click', () => {
                this.showTranscriptModal();
            });
        }
        
        // Stop and save button
        const stopSaveBtn = this.statusBar.querySelector('#stop-save-btn');
        if (stopSaveBtn) {
            stopSaveBtn.addEventListener('click', () => {
                this.stopAndSaveRecording();
            });
        }
        
        // Leave room button
        const leaveBtn = this.statusBar.querySelector('#leave-room-btn');
        if (leaveBtn) {
            leaveBtn.addEventListener('click', () => {
                this.leaveRoom();
            });
        }
    }

    /**
     * Shows the recording status bar
     */
    showRecordingStatusBar() {
        if (!this.statusBar) return;
        
        const recordingStatus = this.statusBar.querySelector('#recording-status');
        const idleStatus = this.statusBar.querySelector('#idle-status');
        const recordingActions = this.statusBar.querySelector('#recording-actions');
        
        if (recordingStatus) recordingStatus.classList.remove('hidden');
        if (idleStatus) idleStatus.classList.add('hidden');
        if (recordingActions) recordingActions.classList.remove('hidden');
        
        // Start recording timer
        this.startRecordingTimer();
    }

    /**
     * Hides the recording status bar
     */
    hideRecordingStatusBar() {
        if (!this.statusBar) return;
        
        const recordingStatus = this.statusBar.querySelector('#recording-status');
        const idleStatus = this.statusBar.querySelector('#idle-status');
        const recordingActions = this.statusBar.querySelector('#recording-actions');
        
        if (recordingStatus) recordingStatus.classList.add('hidden');
        if (idleStatus) idleStatus.classList.remove('hidden');
        if (recordingActions) recordingActions.classList.add('hidden');
        
        // Stop recording timer
        this.stopRecordingTimer();
    }

    /**
     * Starts the recording timer display
     */
    startRecordingTimer() {
        this.recordingStartTime = Date.now();
        this.recordingTimer = setInterval(() => {
            this.updateRecordingTimer();
        }, 1000);
        this.updateRecordingTimer(); // Update immediately
    }

    /**
     * Stops the recording timer display
     */
    stopRecordingTimer() {
        if (this.recordingTimer) {
            clearInterval(this.recordingTimer);
            this.recordingTimer = null;
        }
        this.recordingStartTime = null;
    }

    /**
     * Updates the recording timer display
     */
    updateRecordingTimer() {
        if (!this.recordingStartTime) return;
        
        const timerElement = this.statusBar?.querySelector('#recording-timer');
        if (!timerElement) return;
        
        const elapsed = Math.floor((Date.now() - this.recordingStartTime) / 1000);
        const minutes = Math.floor(elapsed / 60);
        const seconds = elapsed % 60;
        
        timerElement.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    }

    /**
     * Updates recording status with additional information
     */
    updateRecordingStatus(info = {}) {
        if (!this.statusBar) return;
        
        const recordingStatus = this.statusBar.querySelector('#recording-status span');
        if (!recordingStatus) return;
        
        let statusText = 'Recording';
        if (info.totalSize) {
            const sizeMB = (info.totalSize / 1024 / 1024).toFixed(1);
            statusText += ` (${sizeMB} MB)`;
        }
        if (info.chunkCount) {
            statusText += ` • ${info.chunkCount} chunks`;
        }
        
        recordingStatus.textContent = statusText;
    }

    /**
     * Shows the transcript modal
     */
    showTranscriptModal() {
        // This would integrate with the transcript system
        console.log('📝 Opening transcript modal...');
        
        // For now, just show a simple alert
        // In a full implementation, this would open a modal with the current transcript
        if (this.roomWebRTC.speechBuffer && this.roomWebRTC.speechBuffer.length > 0) {
            const transcript = this.roomWebRTC.speechBuffer.join(' ');
            alert(`Current Transcript:\\n\\n${transcript}`);
        } else {
            alert('No transcript available yet. Speech recognition may not be enabled or no speech has been detected.');
        }
    }

    /**
     * Stops recording and saves the file
     */
    stopAndSaveRecording() {
        console.log('🛑 Stop and save recording requested from status bar');
        
        if (this.roomWebRTC.isRecording) {
            this.roomWebRTC.stopVideoRecording();
        }
        
        // Also leave the room after stopping recording
        setTimeout(() => {
            this.leaveRoom();
        }, 1000); // Give a moment for the recording to stop
    }

    /**
     * Leaves the room
     */
    leaveRoom() {
        console.log('🚪 Leave room requested from status bar');
        
        // Check if recording is in progress
        if (this.roomWebRTC.isRecording) {
            const confirmLeave = confirm('Recording is in progress. Stop recording and leave room?');
            if (!confirmLeave) return;
            
            // Stop recording first
            this.roomWebRTC.stopVideoRecording();
        }
        
        // Navigate back to dashboard or previous page
        if (document.referrer && !document.referrer.includes(window.location.pathname)) {
            window.location.href = document.referrer;
        } else {
            window.location.href = '/dashboard';
        }
    }

    /**
     * Cleans up the status bar
     */
    destroy() {
        this.stopRecordingTimer();
        
        if (this.statusBar) {
            this.statusBar.remove();
            this.statusBar = null;
        }
        
        // Remove bottom padding from main content
        const mainContent = document.querySelector('main') || document.body;
        if (mainContent) {
            mainContent.style.paddingBottom = '';
        }
    }
}
