class WebRTCVideoSlots {
    constructor() {
        this.localStream = null;
        this.peerConnections = new Map(); // Map of peerId -> RTCPeerConnection
        this.slotOccupants = new Map(); // Map of slotId -> {peerId, stream}
        this.currentSlotId = null;
        this.currentPeerId = null;
        this.ablyChannel = null;
        this.isJoined = false;
        
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

        // Initialize character sheet interactions
        this.initCharacterSheetHandlers();

        // Connect to Ably channel immediately
        this.connectToAblyChannel();
    }

    initCharacterSheetHandlers() {
        // Health box click handlers
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('health-box')) {
                const clickedValue = parseInt(e.target.dataset.value);
                const healthTrack = e.target.parentElement;
                const healthBoxes = healthTrack.querySelectorAll('.health-box');
                
                // Toggle health state - if clicking on a filled box, clear from that point
                // If clicking on empty box, fill up to that point
                const isFilled = e.target.classList.contains('bg-red-500');
                
                healthBoxes.forEach((box, index) => {
                    const boxValue = parseInt(box.dataset.value);
                    if (isFilled && boxValue >= clickedValue) {
                        // Clear from clicked point onwards
                        box.classList.remove('bg-red-500');
                        box.classList.add('bg-gray-600');
                    } else if (!isFilled && boxValue <= clickedValue) {
                        // Fill up to clicked point
                        box.classList.remove('bg-gray-600');
                        box.classList.add('bg-red-500');
                    }
                });
            }
        });

        // Stress box click handlers
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('stress-box')) {
                const clickedValue = parseInt(e.target.dataset.value);
                const stressTrack = e.target.parentElement;
                const stressBoxes = stressTrack.querySelectorAll('.stress-box');
                
                const isFilled = e.target.classList.contains('bg-purple-500');
                
                stressBoxes.forEach((box, index) => {
                    const boxValue = parseInt(box.dataset.value);
                    if (isFilled && boxValue >= clickedValue) {
                        box.classList.remove('bg-purple-500');
                        box.classList.add('bg-gray-600');
                    } else if (!isFilled && boxValue <= clickedValue) {
                        box.classList.remove('bg-gray-600');
                        box.classList.add('bg-purple-500');
                    }
                });
            }
        });

        // Hope diamond click handlers
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('hope-diamond')) {
                const clickedValue = parseInt(e.target.dataset.value);
                const hopeTrack = e.target.parentElement;
                const hopeDiamonds = hopeTrack.querySelectorAll('.hope-diamond');
                
                const isFilled = e.target.classList.contains('bg-amber-400');
                
                hopeDiamonds.forEach((diamond, index) => {
                    const diamondValue = parseInt(diamond.dataset.value);
                    if (isFilled && diamondValue >= clickedValue) {
                        // Clear from clicked point onwards
                        diamond.classList.remove('bg-amber-400');
                        diamond.classList.add('bg-gray-600');
                    } else if (!isFilled && diamondValue <= clickedValue) {
                        // Fill up to clicked point
                        diamond.classList.remove('bg-gray-600');
                        diamond.classList.add('bg-amber-400');
                    }
                });
            }
        });
    }

    generatePeerId() {
        return Math.random().toString(36).substr(2, 9);
    }

    async joinSlot(slotId, slotContainer) {
        try {
            this.currentSlotId = slotId;
            // Use existing peer ID if we have one, otherwise generate new one
            if (!this.currentPeerId) {
                this.currentPeerId = this.generatePeerId();
            }
            this.currentSlotContainer = slotContainer;
            
            // Show loading spinner
            const spinner = slotContainer.querySelector('.loading-spinner');
            const joinBtn = slotContainer.querySelector('.join-btn');
            spinner.classList.remove('hidden');
            spinner.style.display = 'flex';
            joinBtn.innerHTML = '<span class="flex items-center gap-2">Connecting...</span>';

            // Get user media
            await this.getUserMedia();
            
            // Send join notification directly via Ably
            console.log(`🚀 Joining slot ${slotId} with peerId ${this.currentPeerId}`);
            this.publishToAbly('join-slot', {
                slotId: slotId,
                peerId: this.currentPeerId
            });
            
            // Update UI
            this.updateSlotUI(slotContainer, true);
            this.updateSlotStatus(slotContainer, 'You');
            this.isJoined = true;

            // Create peer connections to any existing viewers after a short delay
            setTimeout(() => {
                console.log(`🔍 Checking for existing viewers to send video to...`);
                console.log(`📊 Current peer connections:`, Array.from(this.peerConnections.keys()));
                
                // Request current viewers by sending a "joined-slot" announcement
                this.publishToAbly('announce-join', {
                    slotId: slotId,
                    peerId: this.currentPeerId
                });
            }, 1000);

        } catch (error) {
            console.error('Error joining slot:', error);
            this.handleError(slotContainer, 'Failed to join slot. Please check camera/microphone permissions.');
        }
    }

    async getUserMedia() {
        try {
            this.localStream = await navigator.mediaDevices.getUserMedia({
                video: true,
                audio: true
            });
        } catch (error) {
            console.error('Error accessing media devices:', error);
            throw new Error('Could not access camera and microphone');
        }
    }

    connectToAblyChannel() {
        console.log('🔗 Connecting to Ably channel for video room signaling...');
        
        // Wait for Ably client to be ready
        const connectWhenReady = () => {
            if (window.AblyClient && window.AblyClient.connection.state === 'connected') {
                this.setupAblyChannel();
            } else if (window.AblyClient) {
                window.AblyClient.connection.once('connected', () => {
                    this.setupAblyChannel();
                });
            } else {
                // Wait for Ably client to be initialized
                setTimeout(connectWhenReady, 100);
            }
        };
        
        connectWhenReady();
    }

    setupAblyChannel() {
        this.ablyChannel = window.AblyClient.channels.get('video-room-main');
        
        // Subscribe to all signaling messages
        this.ablyChannel.subscribe((message) => {
            // Filter out our own messages early
            if (message.data?.senderId === this.currentPeerId) {
                return;
            }
            
            console.log('📨 Message type:', message.data?.type, 'from:', message.data?.senderId);
            this.handleAblyMessage(message);
        });
        
        console.log('✅ Connected to Ably channel');
        
        // Request current state from other users
        setTimeout(() => {
            console.log('📡 Requesting current room state via Ably...');
            // Ensure we have a peer ID even if we're just viewing
            if (!this.currentPeerId) {
                this.currentPeerId = this.generatePeerId();
                console.log(`🆔 Generated viewer peer ID: ${this.currentPeerId}`);
            }
            this.publishToAbly('request-state', {
                requesterId: this.currentPeerId
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
            targetPeerId: targetPeerId,
            timestamp: Date.now()
        };
        
        this.ablyChannel.publish('signaling', message, (err) => {
            if (err) {
                console.error('❌ Failed to publish to Ably:', err);
            }
        });
    }

    async handleAblyMessage(message) {
        const { type, data, senderId, targetPeerId } = message.data;
        
        // Ignore messages not meant for us (self-filtering already done in subscribe)
        if (targetPeerId && targetPeerId !== this.currentPeerId) {
            return;
        }

        try {
            switch (type) {
                                case 'request-state':
                    // Someone is requesting current room state
                    console.log(`📡 State requested by ${senderId} (our ID: ${this.currentPeerId}, joined: ${this.isJoined})`);
                    if (this.isJoined) {
                        // Send our current slot occupation
                        console.log(`📤 Sending our state: slot ${this.currentSlotId} to ${senderId}`);
                        this.publishToAbly('join-slot', {
                            slotId: this.currentSlotId,
                            peerId: this.currentPeerId
                        });

                        // Create peer connection to send our video to the new viewer (with delay to ensure they're ready)
                        if (!this.peerConnections.has(senderId)) {
                            console.log(`🤝 Creating peer connection to send video to new viewer ${senderId}`);
                            setTimeout(() => {
                                console.log(`⏰ Creating delayed peer connection with offer for ${senderId}`);
                                this.createPeerConnection(senderId, true); // true = we send offer
                            }, 500); // Small delay to ensure the viewer has processed the join-slot message
                        } else {
                            console.log(`⚠️ Peer connection already exists for ${senderId}`);
                        }
                    } else {
                        console.log(`📡 Not joined, ignoring state request from ${senderId}`);
                    }
                    break;

                case 'announce-join':
                    // Someone announced they joined a slot and wants to connect to existing viewers
                    console.log(`📢 User ${senderId} announced joining slot ${data.slotId}`);
                    
                    // If we're a viewer (not joined but have peer ID), respond so they can send us video
                    if (!this.isJoined && this.currentPeerId) {
                        console.log(`👁️ We're a viewer, responding to ${senderId} so they can send us video`);
                        // Small delay to avoid race condition
                        setTimeout(() => {
                            this.publishToAbly('viewer-response', {
                                viewerId: this.currentPeerId
                            }, senderId); // Send directly to the joiner
                        }, 100);
                    }
                    break;
                
                case 'join-slot':
                    // Someone joined a slot
                    const { slotId, peerId } = data;
                    console.log(`👤 User ${senderId} joined slot ${slotId}`);
                    this.slotOccupants.set(slotId, { peerId: senderId, stream: null });
                    
                    // Update the slot to show it's occupied
                    const slotContainer = document.querySelector(`[data-slot-id="${slotId}"]`);
                    if (slotContainer) {
                        this.updateSlotStatus(slotContainer, 'Occupied');
                    }
                    
                    // ALWAYS create peer connection to view their video (regardless of our join status)
                    if (!this.peerConnections.has(senderId)) {
                        console.log(`🤝 Creating peer connection with ${senderId} to receive their video`);
                        this.createPeerConnection(senderId, false); // false = we don't send offer, they will
                    }
                    this.updateAllSlotCounts();
                    break;

                case 'viewer-response':
                    // A viewer responded to our join announcement
                    console.log(`👁️ Viewer ${senderId} responded to our join`);
                    
                    // Create peer connection to send our video to this viewer
                    if (this.isJoined && !this.peerConnections.has(senderId)) {
                        console.log(`🤝 Creating peer connection to send video to viewer ${senderId}`);
                        setTimeout(() => {
                            this.createPeerConnection(senderId, true); // true = we send offer
                        }, 200);
                    }
                    break;

                case 'leave-slot':
                    // Someone left their slot
                    console.log(`👋 User ${senderId} left their slot`);
                    this.handleUserLeaving(senderId);
                    break;

                case 'offer':
                    console.log(`📞 Received offer from ${senderId}`);
                    const offerPeerConnection = this.peerConnections.get(senderId);
                    if (!offerPeerConnection) {
                        console.warn('❌ No peer connection found for offer from:', senderId);
                        return;
                    }

                    await offerPeerConnection.setRemoteDescription(data.offer);
                    console.log(`✅ Set remote description (offer) from ${senderId}`);
                    
                    const answer = await offerPeerConnection.createAnswer();
                    await offerPeerConnection.setLocalDescription(answer);
                    console.log(`📤 Sending answer to ${senderId}`);
                    
                    this.publishToAbly('answer', {
                        answer: answer
                    }, senderId);
                    break;

                case 'answer':
                    console.log(`📞 Received answer from ${senderId}`);
                    const answerPeerConnection = this.peerConnections.get(senderId);
                    if (!answerPeerConnection) {
                        console.warn('❌ No peer connection found for answer from:', senderId);
                        return;
                    }

                    await answerPeerConnection.setRemoteDescription(data.answer);
                    console.log(`✅ Set remote description (answer) from ${senderId}`);
                    break;

                case 'ice-candidate':
                    const icePeerConnection = this.peerConnections.get(senderId);
                    if (!icePeerConnection) {
                        console.warn('❌ No peer connection found for ICE candidate from:', senderId);
                        return;
                    }

                    await icePeerConnection.addIceCandidate(data.candidate);
                    break;
            }
        } catch (error) {
            console.error('Error handling Ably message:', error);
        }
    }

    async createPeerConnection(peerId, shouldCreateOffer) {
        console.log(`🔗 Creating peer connection with ${peerId}, shouldCreateOffer: ${shouldCreateOffer}`);
        const peerConnection = new RTCPeerConnection(this.iceServers);
        this.peerConnections.set(peerId, peerConnection);

        // Add local stream to peer connection ONLY if we have joined a slot
        if (this.localStream && this.isJoined) {
            console.log(`📹 Adding our local stream to peer connection with ${peerId}`);
            this.localStream.getTracks().forEach(track => {
                peerConnection.addTrack(track, this.localStream);
            });
        } else {
            console.log(`👁️ Creating receive-only peer connection with ${peerId}`);
        }

        // Handle remote stream
        peerConnection.ontrack = (event) => {
            console.log(`📺 ontrack event from ${peerId}:`, event);
            console.log(`📺 Streams in event:`, event.streams);
            console.log(`📺 Track:`, event.track);
            if (event.streams && event.streams[0]) {
                this.handleRemoteStream(peerId, event.streams[0]);
            } else {
                console.warn(`⚠️ No streams in ontrack event from ${peerId}`);
            }
        };

        // Handle connection state changes
        peerConnection.onconnectionstatechange = () => {
            console.log(`🔗 Connection state with ${peerId}:`, peerConnection.connectionState);
        };

        peerConnection.oniceconnectionstatechange = () => {
            console.log(`🧊 ICE connection state with ${peerId}:`, peerConnection.iceConnectionState);
        };

        // Handle ICE candidates
        peerConnection.onicecandidate = (event) => {
            if (event.candidate) {
                this.publishToAbly('ice-candidate', {
                    candidate: event.candidate
                }, peerId);
            }
        };

        // Create offer if we're the caller
        if (shouldCreateOffer) {
            try {
                console.log(`📞 Creating offer for ${peerId}`);
                const offer = await peerConnection.createOffer();
                await peerConnection.setLocalDescription(offer);
                
                this.publishToAbly('offer', {
                    offer: offer
                }, peerId);
            } catch (error) {
                console.error('Error creating offer:', error);
            }
        }
    }

    handleRemoteStream(peerId, stream) {
        console.log(`🎥 Handling remote stream from ${peerId}`, stream);
        console.log(`📊 Stream tracks:`, stream.getTracks());
        
        // Find which slot this user is in
        let userSlotId = null;
        for (let [slotId, occupant] of this.slotOccupants) {
            if (occupant.peerId === peerId) {
                userSlotId = slotId;
                occupant.stream = stream;
                console.log(`📍 Found ${peerId} in slot ${slotId}`);
                break;
            }
        }

        if (userSlotId) {
            // Display the stream in the correct slot
            const slotContainer = document.querySelector(`[data-slot-id="${userSlotId}"]`);
            if (slotContainer) {
                const videoElement = slotContainer.querySelector('.local-video');
                console.log(`📺 Attaching stream to video element in slot ${userSlotId}`, videoElement);
                
                videoElement.srcObject = stream;
                videoElement.classList.remove('hidden');
                
                // Add event listeners to track video loading
                videoElement.onloadedmetadata = () => {
                    console.log(`✅ Video metadata loaded for slot ${userSlotId}`);
                };
                videoElement.oncanplay = () => {
                    console.log(`▶️ Video can start playing for slot ${userSlotId}`);
                };
                videoElement.onerror = (error) => {
                    console.error(`❌ Video error for slot ${userSlotId}:`, error);
                };
                
                // Update the slot UI to show it's occupied
                this.updateSlotUI(slotContainer, true, false); // true for occupied, false for not own slot
                console.log(`✅ Updated UI for occupied slot ${userSlotId}`);
            } else {
                console.error(`❌ Could not find slot container for slot ${userSlotId}`);
            }
        } else {
            console.error(`❌ Could not find slot for peer ${peerId} in slotOccupants:`, this.slotOccupants);
        }
    }

    updateSlotUI(slotContainer, isOccupied, isOwnSlot = true) {
        const joinBtn = slotContainer.querySelector('.join-btn');
        const leaveBtn = slotContainer.querySelector('.leave-btn');
        const localVideo = slotContainer.querySelector('.local-video');
        const spinner = slotContainer.querySelector('.loading-spinner');
        const characterOverlay = slotContainer.querySelector('.character-overlay');

        if (isOccupied) {
            // Show video
            if (isOwnSlot && this.localStream) {
                localVideo.srcObject = this.localStream;
            }
            localVideo.classList.remove('hidden');
            
            // Show character overlay
            if (characterOverlay) {
                characterOverlay.classList.remove('hidden');
            }
            
            // Update buttons - only show leave for own slot
            if (isOwnSlot) {
                joinBtn.classList.add('hidden');
                leaveBtn.classList.remove('hidden');
            } else {
                joinBtn.classList.add('hidden');
                leaveBtn.classList.add('hidden');
            }
            
            // Hide spinner
            spinner.classList.add('hidden');
            spinner.style.display = 'none';
        } else {
            // Hide video
            localVideo.classList.add('hidden');
            localVideo.srcObject = null;
            
            // Hide character overlay
            if (characterOverlay) {
                characterOverlay.classList.add('hidden');
            }
            
            // Show join button and reset text
            joinBtn.classList.remove('hidden');
            joinBtn.innerHTML = `<span class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2L13.5 8.5L20 10L13.5 11.5L12 18L10.5 11.5L4 10L10.5 8.5L12 2Z"/>
                </svg>
                Join Quest
            </span>`;
            leaveBtn.classList.add('hidden');
            
            // Hide spinner
            spinner.classList.add('hidden');
            spinner.style.display = 'none';
        }
    }

    updateAllSlotCounts() {
        const totalOccupied = this.slotOccupants.size + (this.isJoined ? 1 : 0);
        console.log(`📊 Total occupied slots: ${totalOccupied}/6`);
        // We don't need a global count anymore since each slot shows its own status
    }

    updateSlotStatus(slotContainer, status) {
        const statusElement = slotContainer.querySelector('.slot-status');
        if (statusElement) {
            statusElement.textContent = status;
        }
    }

    handleUserLeaving(peerId) {
        // Remove from slot occupants
        for (let [slotId, occupant] of this.slotOccupants) {
            if (occupant.peerId === peerId) {
                this.slotOccupants.delete(slotId);
                
                // Clear the slot UI
                const slotContainer = document.querySelector(`[data-slot-id="${slotId}"]`);
                if (slotContainer) {
                    this.updateSlotUI(slotContainer, false);
                    this.updateSlotStatus(slotContainer, 'Available');
                }
                break;
            }
        }

        // Close peer connection
        this.closePeerConnection(peerId);
        this.updateAllSlotCounts();
    }

    closePeerConnection(peerId) {
        const peerConnection = this.peerConnections.get(peerId);
        if (peerConnection) {
            peerConnection.close();
            this.peerConnections.delete(peerId);
        }
    }

    leaveSlot() {
        if (!this.isJoined) return;

        // Send leave notification via Ably
        this.publishToAbly('leave-slot', {
            slotId: this.currentSlotId,
            peerId: this.currentPeerId
        });

        // Close all peer connections
        this.peerConnections.forEach((pc, peerId) => {
            pc.close();
        });
        this.peerConnections.clear();

        // Stop local stream
        if (this.localStream) {
            this.localStream.getTracks().forEach(track => track.stop());
            this.localStream = null;
        }

        // Update UI
        if (this.currentSlotContainer) {
            this.updateSlotUI(this.currentSlotContainer, false);
            this.updateSlotStatus(this.currentSlotContainer, 'Available');
        }

        // Reset state
        this.currentSlotId = null;
        this.currentPeerId = null;
        this.currentSlotContainer = null;
        this.isJoined = false;
        this.updateAllSlotCounts();
    }

    handleError(slotContainer, message) {
        const spinner = slotContainer.querySelector('.loading-spinner');
        const joinBtn = slotContainer.querySelector('.join-btn');
        
        spinner.classList.add('hidden');
        spinner.style.display = 'none';
        joinBtn.innerHTML = `<span class="flex items-center gap-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2L13.5 8.5L20 10L13.5 11.5L12 18L10.5 11.5L4 10L10.5 8.5L12 2Z"/>
            </svg>
            Join Quest
        </span>`;
        
        alert(message);
    }
}

// Initialize WebRTC when the page loads
document.addEventListener('DOMContentLoaded', () => {
    new WebRTCVideoSlots();
});