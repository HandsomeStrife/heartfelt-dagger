/**
 * VideoSlotControls - Handles user interactions with video slot control buttons
 * 
 * Manages refresh connections, kick participants, and other slot-specific actions.
 */

export class VideoSlotControls {
    constructor(roomWebRTC) {
        this.roomWebRTC = roomWebRTC;
        this.setupEventListeners();
    }

    /**
     * Set up event listeners for video slot control buttons
     */
    setupEventListeners() {
        console.log('🎛️ Setting up video slot control event listeners');
        
        // Refresh connection buttons
        document.addEventListener('click', (event) => {
            const refreshBtn = event.target.closest('.refresh-connection-btn');
            if (refreshBtn) {
                console.log('🔄 Refresh button clicked:', {
                    target: event.target,
                    button: refreshBtn,
                    peerId: refreshBtn.dataset.peerId,
                    participantName: refreshBtn.dataset.participantName,
                    buttonClasses: Array.from(refreshBtn.classList),
                    parentClasses: Array.from(refreshBtn.parentElement.classList)
                });
                
                event.preventDefault();
                this.handleRefreshConnection(refreshBtn);
            }
        });

        // Kick participant buttons (existing functionality - could be moved here)
        document.addEventListener('click', (event) => {
            const kickBtn = event.target.closest('.kick-participant-btn');
            if (kickBtn) {
                console.log('👢 Kick button clicked:', {
                    target: event.target,
                    button: kickBtn,
                    participantId: kickBtn.dataset.participantId,
                    participantName: kickBtn.dataset.participantName
                });
                
                event.preventDefault();
                this.handleKickParticipant(kickBtn);
            }
        });
        
        // Add hover logging to debug visibility issues
        document.addEventListener('mouseenter', (event) => {
            if (event.target.closest('.video-slot')) {
                const slot = event.target.closest('.video-slot');
                const controls = slot.querySelector('.video-controls');
                const slotId = slot.dataset.slotId;
                
                console.log(`🐭 Mouse entered slot ${slotId}:`, {
                    hasControls: !!controls,
                    controlsVisible: controls ? !controls.classList.contains('hidden') : false,
                    controlsClasses: controls ? Array.from(controls.classList) : 'no-controls'
                });
            }
        }, true);
    }

    /**
     * Handle refresh connection button click
     */
    async handleRefreshConnection(button) {
        const peerId = button.dataset.peerId;
        const participantName = button.dataset.participantName || 'Unknown';

        console.log(`🔄 Starting refresh connection:`, {
            peerId,
            participantName,
            button,
            buttonEnabled: !button.disabled,
            buttonVisible: getComputedStyle(button).display !== 'none'
        });

        // Show loading state
        this.setButtonLoading(button, true);

        try {
            if (!peerId) {
                // If no peer ID, this might be our own slot - refresh all connections
                console.log(`🔄 Refreshing all connections (no specific peer ID)`);
                await this.refreshAllConnections();
            } else {
                // Refresh specific peer connection
                console.log(`🔄 Refreshing connection for ${participantName} (${peerId})`);
                await this.roomWebRTC.peerConnectionManager.refreshConnection(peerId);
            }
            
            // Show success feedback
            console.log(`✅ Refresh successful for ${participantName}`);
            this.showRefreshFeedback(button, 'success');
            
        } catch (error) {
            console.error(`❌ Failed to refresh connection for ${participantName}:`, error);
            this.showRefreshFeedback(button, 'error');
        } finally {
            // Remove loading state after a moment
            setTimeout(() => {
                this.setButtonLoading(button, false);
            }, 1000);
        }
    }

    /**
     * Refresh all peer connections (useful when refreshing own slot)
     */
    async refreshAllConnections() {
        const connections = this.roomWebRTC.peerConnectionManager.getPeerConnections();
        const refreshPromises = [];

        for (const [peerId] of connections) {
            console.log(`🔄 Refreshing connection to ${peerId}`);
            refreshPromises.push(this.roomWebRTC.peerConnectionManager.refreshConnection(peerId));
        }

        if (refreshPromises.length === 0) {
            console.log('🔄 No existing connections to refresh');
            return;
        }

        await Promise.allSettled(refreshPromises);
        console.log(`✅ Refreshed ${refreshPromises.length} connections`);
    }

    /**
     * Handle kick participant button click
     */
    async handleKickParticipant(button) {
        const participantId = button.dataset.participantId;
        const participantName = button.dataset.participantName || 'Unknown';

        if (!participantId) {
            console.warn('⚠️ No participant ID found for kick button');
            return;
        }

        // Confirm the action
        if (!confirm(`Are you sure you want to kick ${participantName} from the room?`)) {
            return;
        }

        console.log(`👢 Kicking participant ${participantName} (${participantId})`);

        // Show loading state
        this.setButtonLoading(button, true);

        try {
            // Make API request to kick participant
            const response = await fetch(`/rooms/${this.roomWebRTC.roomData.id}/kick-participant`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    participant_id: participantId
                })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                console.log(`✅ Successfully kicked ${participantName}`);
                // The UI will update automatically via Ably messages
            } else {
                throw new Error(data.error || 'Failed to kick participant');
            }

        } catch (error) {
            console.error(`❌ Failed to kick participant:`, error);
            alert(`Failed to kick ${participantName}: ${error.message}`);
        } finally {
            this.setButtonLoading(button, false);
        }
    }

    /**
     * Set button loading state
     */
    setButtonLoading(button, loading) {
        if (loading) {
            button.disabled = true;
            button.classList.add('opacity-50', 'cursor-not-allowed');
            
            // Add spinner if not already present
            const spinner = button.querySelector('.loading-spinner');
            if (!spinner) {
                const spinnerElement = document.createElement('div');
                spinnerElement.className = 'loading-spinner w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin';
                button.insertBefore(spinnerElement, button.firstChild);
            }
        } else {
            button.disabled = false;
            button.classList.remove('opacity-50', 'cursor-not-allowed');
            
            // Remove spinner
            const spinner = button.querySelector('.loading-spinner');
            if (spinner) {
                spinner.remove();
            }
        }
    }

    /**
     * Show visual feedback for refresh action
     */
    showRefreshFeedback(button, type) {
        const originalBg = button.className.match(/bg-\w+-\d+/)[0];
        
        if (type === 'success') {
            button.classList.remove(originalBg);
            button.classList.add('bg-green-600');
            
            setTimeout(() => {
                button.classList.remove('bg-green-600');
                button.classList.add(originalBg);
            }, 2000);
        } else if (type === 'error') {
            button.classList.remove(originalBg);
            button.classList.add('bg-red-600');
            
            setTimeout(() => {
                button.classList.remove('bg-red-600');
                button.classList.add(originalBg);
            }, 2000);
        }
    }
}
