class RoomWebRTC {
    constructor(roomData) {
        this.roomData = roomData;
        this.localStream = null;
        this.peerConnections = new Map(); // Map of peerId -> RTCPeerConnection
        this.slotOccupants = new Map(); // Map of slotId -> {peerId, stream, participantData}
        this.currentSlotId = null;
        this.currentPeerId = null;
        this.ablyChannel = null;
        this.isJoined = false;
        this.currentUserId = window.currentUserId; // Should be set by Blade template
        
        // ICE servers for peer-to-peer connections
        this.iceServers = {
            iceServers: [
                { urls: 'stun:stun.l.google.com:19302' },
                { urls: 'stun:stun1.l.google.com:19302' }
            ]
        };

        this.init();
    }

    init() {
        console.log('🎬 Initializing Room WebRTC for room:', this.roomData.name);
        
        // Add click handlers to all join buttons
        document.querySelectorAll('.join-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                const slotContainer = e.target.closest('.video-slot');
                const slotId = parseInt(slotContainer.dataset.slotId);
                
                if (!this.isJoined) {
                    this.joinSlot(slotId, slotContainer);
                }
            });
        });

        // Add click handlers to all leave buttons
        document.querySelectorAll('.leave-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                this.leaveSlot();
            });
        });

        // Connect to room-specific Ably channel
        this.connectToAblyChannel();
    }

    connectToAblyChannel() {
        const connectWhenReady = () => {
            if (window.AblyClient) {
                this.setupAblyChannel();
            } else {
                // Wait for Ably client to be initialized
                setTimeout(connectWhenReady, 100);
            }
        };
        
        connectWhenReady();
    }

    setupAblyChannel() {
        // Use room-specific channel
        const channelName = `room-${this.roomData.id}`;
        this.ablyChannel = window.AblyClient.channels.get(channelName);
        
        // Subscribe to all signaling messages
        this.ablyChannel.subscribe((message) => {
            // Filter out our own messages early
            if (message.data?.senderId === this.currentPeerId) {
                return;
            }
            
            console.log('📨 Room message type:', message.data?.type, 'from:', message.data?.senderId);
            this.handleAblyMessage(message);
        });
        
        console.log('✅ Connected to room Ably channel:', channelName);
        
        // Request current state from other users in this room
        setTimeout(() => {
            console.log('📡 Requesting current room state...');
            if (!this.currentPeerId) {
                this.currentPeerId = this.generatePeerId();
                console.log(`🆔 Generated viewer peer ID: ${this.currentPeerId}`);
            }
            this.publishToAbly('request-state', {
                requesterId: this.currentPeerId,
                userId: this.currentUserId
            });
        }, 500);
    }

    publishToAbly(type, data, targetPeerId = null) {
        if (!this.ablyChannel) {
            console.warn('❌ Ably channel not ready');
            return;
        }

        const message = {
            type: type,
            data: data,
            senderId: this.currentPeerId || 'anonymous',
            userId: this.currentUserId,
            roomId: this.roomData.id,
            targetPeerId: targetPeerId,
            timestamp: Date.now()
        };

        this.ablyChannel.publish('webrtc-signal', message);
        console.log(`📤 Published ${type} to room channel`);
    }

    generatePeerId() {
        return Math.random().toString(36).substr(2, 9);
    }

    async joinSlot(slotId, slotContainer) {
        try {
            this.currentSlotId = slotId;
            
            // Check if slot is already occupied
            if (this.slotOccupants.has(slotId)) {
                console.log('⚠️ Slot already occupied');
                return;
            }

            // Show loading state
            this.showLoadingState(slotContainer);

            // Generate peer ID if we don't have one
            if (!this.currentPeerId) {
                this.currentPeerId = this.generatePeerId();
                console.log(`🆔 Generated peer ID: ${this.currentPeerId}`);
            }

            // Get user media
            console.log('🎥 Requesting user media...');
            this.localStream = await navigator.mediaDevices.getUserMedia({
                video: {
                    width: { ideal: 1280 },
                    height: { ideal: 720 },
                    frameRate: { ideal: 30 }
                },
                audio: {
                    echoCancellation: true,
                    noiseSuppression: true,
                    sampleRate: 44100
                }
            });

            // Find participant data for this user
            const participantData = this.roomData.participants.find(p => p.user_id === this.currentUserId);

            // Set up local video
            this.setupLocalVideo(slotContainer, this.localStream, participantData);

            // Mark this slot as occupied by us
            this.slotOccupants.set(slotId, {
                peerId: this.currentPeerId,
                stream: this.localStream,
                participantData: participantData,
                isLocal: true
            });

            this.isJoined = true;

            // Announce our presence to the room
            this.publishToAbly('user-joined', {
                slotId: slotId,
                participantData: participantData
            });

            // Hide loading state and show controls
            this.hideLoadingState(slotContainer);
            this.showVideoControls(slotContainer);

        } catch (error) {
            console.error('❌ Error joining slot:', error);
            this.hideLoadingState(slotContainer);
            alert('Failed to access camera/microphone. Please check permissions.');
        }
    }

    setupLocalVideo(slotContainer, stream, participantData) {
        const videoElement = slotContainer.querySelector('.local-video');
        videoElement.srcObject = stream;
        videoElement.style.display = 'block';
        
        // Show character overlay with participant data
        this.showCharacterOverlay(slotContainer, participantData);
        
        console.log('📹 Local video set up for participant:', participantData?.character_name || participantData?.username);
    }

    showCharacterOverlay(slotContainer, participantData) {
        const overlay = slotContainer.querySelector('.character-overlay');
        if (overlay && participantData) {
            // Update character name
            const nameElement = overlay.querySelector('.character-name');
            if (nameElement) {
                nameElement.textContent = participantData.character_name || participantData.username;
            }

            // Update character class
            const classElement = overlay.querySelector('.character-class');
            if (classElement) {
                classElement.textContent = participantData.character_class || 'No Class';
            }

            overlay.classList.remove('hidden');
        }
    }

    showLoadingState(slotContainer) {
        const loadingSpinner = slotContainer.querySelector('.loading-spinner');
        const joinBtn = slotContainer.querySelector('.join-btn');
        
        if (loadingSpinner) {
            loadingSpinner.classList.remove('hidden');
            loadingSpinner.style.display = 'flex';
        }
        if (joinBtn) {
            joinBtn.style.display = 'none';
        }
    }

    hideLoadingState(slotContainer) {
        const loadingSpinner = slotContainer.querySelector('.loading-spinner');
        if (loadingSpinner) {
            loadingSpinner.classList.add('hidden');
            loadingSpinner.style.display = 'none';
        }
    }

    showVideoControls(slotContainer) {
        const leaveBtn = slotContainer.querySelector('.leave-btn');
        if (leaveBtn) {
            leaveBtn.style.display = 'block';
            leaveBtn.classList.remove('hidden');
        }
    }

    async leaveSlot() {
        if (!this.isJoined || !this.currentSlotId) {
            console.log('❌ Not currently in a slot');
            return;
        }

        console.log('🚪 Leaving slot:', this.currentSlotId);

        // Announce we're leaving
        this.publishToAbly('user-left', {
            slotId: this.currentSlotId
        });

        // Stop local stream
        if (this.localStream) {
            this.localStream.getTracks().forEach(track => {
                track.stop();
            });
            this.localStream = null;
        }

        // Close all peer connections
        this.peerConnections.forEach(pc => {
            pc.close();
        });
        this.peerConnections.clear();

        // Clear slot occupancy
        this.slotOccupants.delete(this.currentSlotId);

        // Reset state
        const slotContainer = document.querySelector(`[data-slot-id="${this.currentSlotId}"]`);
        this.resetSlotUI(slotContainer);

        this.currentSlotId = null;
        this.isJoined = false;

        console.log('✅ Successfully left slot');
    }

    resetSlotUI(slotContainer) {
        if (!slotContainer) return;

        // Hide video
        const videoElement = slotContainer.querySelector('.local-video');
        if (videoElement) {
            videoElement.style.display = 'none';
            videoElement.srcObject = null;
        }

        // Hide character overlay
        const overlay = slotContainer.querySelector('.character-overlay');
        if (overlay) {
            overlay.classList.add('hidden');
        }

        // Show join button, hide leave button
        const joinBtn = slotContainer.querySelector('.join-btn');
        const leaveBtn = slotContainer.querySelector('.leave-btn');
        
        if (joinBtn) joinBtn.style.display = 'block';
        if (leaveBtn) {
            leaveBtn.style.display = 'none';
            leaveBtn.classList.add('hidden');
        }

        // Hide loading state
        this.hideLoadingState(slotContainer);
    }

    handleAblyMessage(message) {
        const { type, data, senderId } = message.data;

        console.log('🎭 Handling room message:', type, 'from:', senderId);

        switch (type) {
            case 'request-state':
                this.handleStateRequest(data, senderId);
                break;
            case 'user-joined':
                this.handleUserJoined(data, senderId);
                break;
            case 'user-left':
                this.handleUserLeft(data, senderId);
                break;
            case 'webrtc-offer':
                this.handleOffer(data, senderId);
                break;
            case 'webrtc-answer':
                this.handleAnswer(data, senderId);
                break;
            case 'webrtc-ice-candidate':
                this.handleIceCandidate(data, senderId);
                break;
            default:
                console.log('🤷 Unknown room message type:', type);
        }
    }

    handleStateRequest(data, senderId) {
        // If we're in a slot, tell the requester about our presence
        if (this.isJoined && this.currentSlotId) {
            const participantData = this.roomData.participants.find(p => p.user_id === this.currentUserId);
            this.publishToAbly('user-joined', {
                slotId: this.currentSlotId,
                participantData: participantData
            }, senderId);
        }
    }

    handleUserJoined(data, senderId) {
        console.log('👋 User joined slot:', data.slotId, 'participant:', data.participantData?.character_name);
        
        // Update slot occupancy
        this.slotOccupants.set(data.slotId, {
            peerId: senderId,
            participantData: data.participantData,
            isLocal: false
        });

        // If we're also in a slot, initiate WebRTC connection
        if (this.isJoined && this.currentSlotId && this.currentSlotId !== data.slotId) {
            this.initiateWebRTCConnection(senderId);
        }
    }

    handleUserLeft(data, senderId) {
        console.log('👋 User left slot:', data.slotId);
        
        // Remove from slot occupancy
        this.slotOccupants.delete(data.slotId);
        
        // Close peer connection if exists
        if (this.peerConnections.has(senderId)) {
            this.peerConnections.get(senderId).close();
            this.peerConnections.delete(senderId);
        }

        // Clear remote video if displayed
        this.clearRemoteVideo(senderId);
    }

    async initiateWebRTCConnection(remotePeerId) {
        console.log('🤝 Initiating WebRTC connection with:', remotePeerId);

        try {
            const peerConnection = new RTCPeerConnection(this.iceServers);
            this.peerConnections.set(remotePeerId, peerConnection);

            // Add local stream to connection
            if (this.localStream) {
                this.localStream.getTracks().forEach(track => {
                    peerConnection.addTrack(track, this.localStream);
                });
            }

            // Handle remote stream
            peerConnection.ontrack = (event) => {
                console.log('📡 Received remote stream from:', remotePeerId);
                this.handleRemoteStream(event.streams[0], remotePeerId);
            };

            // Handle ICE candidates
            peerConnection.onicecandidate = (event) => {
                if (event.candidate) {
                    this.publishToAbly('webrtc-ice-candidate', {
                        candidate: event.candidate
                    }, remotePeerId);
                }
            };

            // Create and send offer
            const offer = await peerConnection.createOffer();
            await peerConnection.setLocalDescription(offer);
            
            this.publishToAbly('webrtc-offer', {
                offer: offer
            }, remotePeerId);

        } catch (error) {
            console.error('❌ Error initiating WebRTC connection:', error);
        }
    }

    async handleOffer(data, senderId) {
        console.log('📞 Received WebRTC offer from:', senderId);

        try {
            const peerConnection = new RTCPeerConnection(this.iceServers);
            this.peerConnections.set(senderId, peerConnection);

            // Add local stream to connection
            if (this.localStream) {
                this.localStream.getTracks().forEach(track => {
                    peerConnection.addTrack(track, this.localStream);
                });
            }

            // Handle remote stream
            peerConnection.ontrack = (event) => {
                console.log('📡 Received remote stream from:', senderId);
                this.handleRemoteStream(event.streams[0], senderId);
            };

            // Handle ICE candidates
            peerConnection.onicecandidate = (event) => {
                if (event.candidate) {
                    this.publishToAbly('webrtc-ice-candidate', {
                        candidate: event.candidate
                    }, senderId);
                }
            };

            // Set remote description and create answer
            await peerConnection.setRemoteDescription(data.offer);
            const answer = await peerConnection.createAnswer();
            await peerConnection.setLocalDescription(answer);

            this.publishToAbly('webrtc-answer', {
                answer: answer
            }, senderId);

        } catch (error) {
            console.error('❌ Error handling WebRTC offer:', error);
        }
    }

    async handleAnswer(data, senderId) {
        console.log('✅ Received WebRTC answer from:', senderId);

        try {
            const peerConnection = this.peerConnections.get(senderId);
            if (peerConnection) {
                await peerConnection.setRemoteDescription(data.answer);
            }
        } catch (error) {
            console.error('❌ Error handling WebRTC answer:', error);
        }
    }

    async handleIceCandidate(data, senderId) {
        try {
            const peerConnection = this.peerConnections.get(senderId);
            if (peerConnection) {
                await peerConnection.addIceCandidate(data.candidate);
            }
        } catch (error) {
            console.error('❌ Error handling ICE candidate:', error);
        }
    }

    handleRemoteStream(stream, senderId) {
        console.log('📺 Setting up remote video for peer:', senderId);

        // Find which slot this peer occupies
        let targetSlotId = null;
        for (const [slotId, occupant] of this.slotOccupants) {
            if (occupant.peerId === senderId) {
                targetSlotId = slotId;
                break;
            }
        }

        if (targetSlotId) {
            const slotContainer = document.querySelector(`[data-slot-id="${targetSlotId}"]`);
            if (slotContainer) {
                this.setupRemoteVideo(slotContainer, stream, this.slotOccupants.get(targetSlotId).participantData);
            }
        }
    }

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

        videoElement.srcObject = stream;
        
        // Show character overlay
        this.showCharacterOverlay(slotContainer, participantData);
        
        // Hide join button since slot is occupied
        const joinBtn = slotContainer.querySelector('.join-btn');
        if (joinBtn) {
            joinBtn.style.display = 'none';
        }
    }

    clearRemoteVideo(senderId) {
        // Find and clear remote video for this peer
        const remoteVideos = document.querySelectorAll('.remote-video');
        remoteVideos.forEach(video => {
            if (video.dataset.peerId === senderId) {
                video.remove();
            }
        });
    }
}

// Initialize when DOM is loaded and room data is available
document.addEventListener('DOMContentLoaded', () => {
    if (window.roomData) {
        console.log('🚀 Starting Room WebRTC system');
        window.roomWebRTC = new RoomWebRTC(window.roomData);
    } else {
        console.warn('⚠️ No room data found, WebRTC not initialized');
    }
});
