/**
 * MediaManager - Manages media streams and device controls
 * 
 * Handles user media access, stream management, and media control states
 * including microphone mute/unmute and video show/hide functionality.
 */

export class MediaManager {
    constructor(roomWebRTC) {
        this.roomWebRTC = roomWebRTC;
        this.localStream = null;
        this.isMicrophoneMuted = false;
        this.isVideoHidden = false;
    }

    /**
     * Gets user media with fallback for audio-only
     */
    async getUserMedia() {
        console.log('🎥 Requesting user media...');
        
        try {
            this.localStream = await navigator.mediaDevices.getUserMedia({
                video: {
                    width: { ideal: 1280 },
                    height: { ideal: 720 },
                    frameRate: { ideal: 30 }
                },
                audio: {
                    echoCancellation: true,
                    noiseSuppression: true,
                    sampleRate: 44100,
                    channelCount: 1 // Smaller payload for recording
                }
            });
        } catch (error) {
            if (error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError') {
                // Try audio-only if video is denied but mic is allowed
                console.log('🎥 Video denied, trying audio-only...');
                try {
                    this.localStream = await navigator.mediaDevices.getUserMedia({
                        video: false,
                        audio: {
                            echoCancellation: true,
                            noiseSuppression: true,
                            sampleRate: 44100,
                            channelCount: 1
                        }
                    });
                    console.log('🎤 Audio-only stream obtained');
                } catch (audioError) {
                    throw error; // Throw original error if both fail
                }
            } else {
                throw error;
            }
        }

        return this.localStream;
    }

    /**
     * Sets up local video display
     */
    setupLocalVideo(slotContainer, stream, participantData) {
        const videoElement = slotContainer.querySelector('.local-video');
        videoElement.srcObject = stream;
        videoElement.playsInline = true; // Avoid fullscreen on iOS
        videoElement.muted = true;       // Avoid autoplay blocks and feedback loops
        videoElement.style.display = 'block';
        
        // Check if stream has video tracks
        const hasVideo = stream.getVideoTracks().length > 0;
        const hasAudio = stream.getAudioTracks().length > 0;
        
        if (!hasVideo && hasAudio) {
            // Audio-only: show audio indicator instead of video
            videoElement.style.display = 'none';
            let audioIndicator = slotContainer.querySelector('.audio-only-indicator');
            if (!audioIndicator) {
                audioIndicator = document.createElement('div');
                audioIndicator.className = 'audio-only-indicator absolute inset-0 flex items-center justify-center bg-slate-800 rounded-lg';
                audioIndicator.innerHTML = `
                    <div class="text-center">
                        <div class="w-16 h-16 bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-2">
                            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                            </svg>
                        </div>
                        <p class="text-slate-400 text-sm">Audio Only</p>
                    </div>
                `;
                slotContainer.appendChild(audioIndicator);
            }
        }
        
        // Show character overlay with participant data
        this.roomWebRTC.slotManager.showCharacterOverlay(slotContainer, participantData);
        
        console.log(`📹 Local ${hasVideo ? 'video' : 'audio-only'} set up for participant:`, participantData?.character_name || participantData?.username);
    }

    /**
     * Sets up remote video display
     */
    setupRemoteVideo(slotContainer, stream, participantData) {
        // Create or get remote video element
        let videoElement = slotContainer.querySelector('.remote-video');
        if (!videoElement) {
            videoElement = document.createElement('video');
            videoElement.className = 'remote-video w-full h-full object-cover';
            videoElement.autoplay = true;
            videoElement.playsInline = true;
            
            const remoteContainer = slotContainer.querySelector('.remote-videos');
            if (remoteContainer) {
                remoteContainer.appendChild(videoElement);
                remoteContainer.classList.remove('hidden');
            }
        }

        // Fix: Set dataset.peerId for proper cleanup
        const targetSlotId = parseInt(slotContainer.dataset.slotId);
        const peerId = this.roomWebRTC.slotOccupants.get(targetSlotId)?.peerId;
        if (peerId) {
            videoElement.dataset.peerId = peerId;
        }

        videoElement.srcObject = stream;
        
        // Hide join button since slot is occupied
        const joinBtn = slotContainer.querySelector('.join-btn');
        if (joinBtn) {
            joinBtn.style.display = 'none';
        }
    }

    /**
     * Clears remote video for a specific peer
     */
    clearRemoteVideo(senderId) {
        // Find and clear remote video for this peer
        const remoteVideos = document.querySelectorAll('.remote-video');
        remoteVideos.forEach(video => {
            if (video.dataset.peerId === senderId) {
                video.remove();
            }
        });
        
        // Hide empty remote-videos containers
        document.querySelectorAll('.remote-videos').forEach(container => {
            if (!container.querySelector('.remote-video')) {
                container.classList.add('hidden');
            }
        });
    }

    /**
     * Toggles microphone mute/unmute state
     * @returns {boolean} - true if muted, false if unmuted
     */
    toggleMicrophone() {
        console.log('🎤 === Toggling Microphone ===');
        console.log(`  - Current state: ${this.isMicrophoneMuted ? 'muted' : 'unmuted'}`);
        console.log(`  - Has local stream: ${!!this.localStream}`);

        if (!this.localStream) {
            console.warn('🎤 ⚠️ No local stream available for microphone toggle');
            return this.isMicrophoneMuted;
        }

        const audioTracks = this.localStream.getAudioTracks();
        console.log(`🎤 Audio tracks found: ${audioTracks.length}`);

        if (audioTracks.length === 0) {
            console.warn('🎤 ⚠️ No audio tracks available for microphone toggle');
            return this.isMicrophoneMuted;
        }

        // Toggle the mute state
        this.isMicrophoneMuted = !this.isMicrophoneMuted;

        // Apply the mute state to all audio tracks
        audioTracks.forEach((track, index) => {
            track.enabled = !this.isMicrophoneMuted;
            console.log(`🎤 Track ${index}: ${track.label} - enabled: ${track.enabled}`);
        });

        console.log(`🎤 ✅ Microphone ${this.isMicrophoneMuted ? 'muted' : 'unmuted'}`);
        
        // Update visual indicators
        this.updateMicrophoneIndicators();

        return this.isMicrophoneMuted;
    }

    /**
     * Toggles video show/hide state
     * @returns {boolean} - true if hidden, false if visible
     */
    toggleVideo() {
        console.log('📹 === Toggling Video ===');
        console.log(`  - Current state: ${this.isVideoHidden ? 'hidden' : 'visible'}`);
        console.log(`  - Has local stream: ${!!this.localStream}`);

        if (!this.localStream) {
            console.warn('📹 ⚠️ No local stream available for video toggle');
            return this.isVideoHidden;
        }

        const videoTracks = this.localStream.getVideoTracks();
        console.log(`📹 Video tracks found: ${videoTracks.length}`);

        if (videoTracks.length === 0) {
            console.warn('📹 ⚠️ No video tracks available for video toggle');
            return this.isVideoHidden;
        }

        // Toggle the video state
        this.isVideoHidden = !this.isVideoHidden;

        // Apply the video state to all video tracks
        videoTracks.forEach((track, index) => {
            track.enabled = !this.isVideoHidden;
            console.log(`📹 Track ${index}: ${track.label} - enabled: ${track.enabled}`);
        });

        console.log(`📹 ✅ Video ${this.isVideoHidden ? 'hidden' : 'visible'}`);
        
        // Update visual indicators
        this.updateVideoIndicators();

        return this.isVideoHidden;
    }

    /**
     * Updates visual indicators for microphone state
     */
    updateMicrophoneIndicators() {
        if (!this.roomWebRTC.currentSlotId) return;

        const slotContainer = document.querySelector(`[data-slot-id="${this.roomWebRTC.currentSlotId}"]`);
        if (!slotContainer) return;

        // Find or create microphone indicator
        let micIndicator = slotContainer.querySelector('.microphone-indicator');
        if (!micIndicator) {
            micIndicator = document.createElement('div');
            micIndicator.className = 'microphone-indicator absolute top-2 left-2 text-xs px-2 py-1 rounded z-10';
            slotContainer.appendChild(micIndicator);
        }

        if (this.isMicrophoneMuted) {
            micIndicator.classList.add('bg-red-500', 'text-white');
            micIndicator.classList.remove('bg-green-500');
            micIndicator.innerHTML = '🔇 MUTED';
            micIndicator.style.display = 'block';
        } else {
            micIndicator.style.display = 'none';
        }
    }

    /**
     * Updates visual indicators for video state
     */
    updateVideoIndicators() {
        if (!this.roomWebRTC.currentSlotId) return;

        const slotContainer = document.querySelector(`[data-slot-id="${this.roomWebRTC.currentSlotId}"]`);
        if (!slotContainer) return;

        const videoElement = slotContainer.querySelector('.local-video');
        
        if (this.isVideoHidden) {
            // Keep video element visible for the user (they should see their own video)
            // The video tracks are disabled so others won't see it, but user can still see themselves
            if (videoElement) {
                videoElement.style.display = 'block';
            }

            // Find or create video-hidden indicator (overlay to show video is hidden from others)
            let videoHiddenIndicator = slotContainer.querySelector('.video-hidden-indicator');
            if (!videoHiddenIndicator) {
                videoHiddenIndicator = document.createElement('div');
                videoHiddenIndicator.className = 'video-hidden-indicator absolute top-2 right-2 bg-red-600 text-white text-xs px-2 py-1 rounded z-20';
                videoHiddenIndicator.innerHTML = `
                    <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728" />
                    </svg>
                    Hidden
                `;
                slotContainer.appendChild(videoHiddenIndicator);
            }
            videoHiddenIndicator.style.display = 'block';

            // Remove any video-off indicator (since we're showing the video locally)
            const videoOffIndicator = slotContainer.querySelector('.video-off-indicator');
            if (videoOffIndicator) {
                videoOffIndicator.style.display = 'none';
            }
        } else {
            // Show video element normally
            if (videoElement) {
                videoElement.style.display = 'block';
            }

            // Hide both indicators when video is visible to others
            const videoHiddenIndicator = slotContainer.querySelector('.video-hidden-indicator');
            if (videoHiddenIndicator) {
                videoHiddenIndicator.style.display = 'none';
            }

            const videoOffIndicator = slotContainer.querySelector('.video-off-indicator');
            if (videoOffIndicator) {
                videoOffIndicator.style.display = 'none';
            }
        }
    }

    /**
     * Stops all tracks in the local stream
     */
    stopLocalStream() {
        if (this.localStream) {
            this.localStream.getTracks().forEach(track => {
                track.stop();
            });
            this.localStream = null;
        }
    }

    /**
     * Resets media control state
     */
    resetMediaState() {
        this.isMicrophoneMuted = false;
        this.isVideoHidden = false;
    }

    /**
     * Gets current microphone mute state
     */
    getMicrophoneMutedState() {
        return this.isMicrophoneMuted;
    }

    /**
     * Gets current video hidden state
     */
    getVideoHiddenState() {
        return this.isVideoHidden;
    }

    /**
     * Sets microphone mute state directly
     */
    setMicrophoneMuted(muted) {
        if (this.isMicrophoneMuted === muted) return;
        this.toggleMicrophone();
    }

    /**
     * Sets video hidden state directly
     */
    setVideoHidden(hidden) {
        if (this.isVideoHidden === hidden) return;
        this.toggleVideo();
    }

    /**
     * Gets the current local stream
     */
    getLocalStream() {
        return this.localStream;
    }
}
