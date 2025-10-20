/**
 * SignalingManager - Manages WebRTC signaling via Laravel Reverb
 * 
 * Handles connection to room-specific channels, message publishing,
 * and subscription management for WebRTC signaling using Laravel Echo.
 * 
 * Migrated from Ably to Reverb for better performance and no rate limits.
 * 
 * MEDIUM FIX: Uses robust CSRF token utility
 */

import { getCSRFToken } from '../utils/csrf';

export class SignalingManager {
    constructor(roomWebRTC) {
        this.roomWebRTC = roomWebRTC;
        this.channel = null;
        this.currentPeerId = null;
        
        // PERFORMANCE FIX: Token bucket rate limiter
        this.rateLimiter = {
            tokens: 10, // Start with 10 tokens
            maxTokens: 10, // Maximum token capacity
            refillRate: 5, // Refill 5 tokens per second
            lastRefill: Date.now()
        };
        
        // Message queuing system for failed deliveries
        this.messageQueue = {
            critical: [],   // ICE candidates, offers, answers - must succeed
            normal: [],     // User joined/left, state requests
            low: []         // Status updates, non-critical events
        };
        this.isProcessingQueue = false;
        this.messageRetryAttempts = new Map(); // Track retry counts per message ID
        this.maxRetries = 3;
        this.retryBaseDelay = 1000; // 1 second base delay
        
        // Message sequencing for drop detection
        this.messageSequence = 0;
        this.lastAckedSequence = 0;
    }
    
    /**
     * PERFORMANCE FIX: Token bucket rate limiter
     * Refills tokens based on elapsed time and checks if action is allowed
     */
    checkRateLimit() {
        const now = Date.now();
        const timePassed = (now - this.rateLimiter.lastRefill) / 1000; // in seconds
        
        // Refill tokens based on time passed
        const tokensToAdd = timePassed * this.rateLimiter.refillRate;
        this.rateLimiter.tokens = Math.min(
            this.rateLimiter.maxTokens,
            this.rateLimiter.tokens + tokensToAdd
        );
        this.rateLimiter.lastRefill = now;
        
        // Check if we have at least 1 token
        if (this.rateLimiter.tokens >= 1) {
            this.rateLimiter.tokens -= 1;
            return true;
        }
        
        return false;
    }
    
    /**
     * Determines message priority based on type
     * Critical messages bypass rate limiting and get priority in retry queue
     */
    getMessagePriority(type) {
        // Critical: WebRTC signaling that must arrive for connections to work
        const criticalTypes = [
            'webrtc-offer',
            'webrtc-answer', 
            'webrtc-ice-candidate',
            'request-state' // Initial state sync is critical
        ];
        
        // Low priority: Status updates and non-critical events
        const lowPriorityTypes = [
            'status-update',
            'typing-indicator',
            'presence-ping'
        ];
        
        if (criticalTypes.includes(type)) {
            return 'critical';
        } else if (lowPriorityTypes.includes(type)) {
            return 'low';
        } else {
            return 'normal';
        }
    }
    
    /**
     * Processes message queue with exponential backoff retries
     * Prioritizes critical messages (WebRTC signaling)
     */
    async processMessageQueue() {
        if (this.isProcessingQueue) {
            return; // Already processing
        }
        
        this.isProcessingQueue = true;
        
        try {
            // Process in priority order: critical → normal → low
            for (const priority of ['critical', 'normal', 'low']) {
                const queue = this.messageQueue[priority];
                
                while (queue.length > 0) {
                    const queuedMessage = queue.shift();
                    const { messageId, type, data, targetPeerId, retryCount } = queuedMessage;
                    
                    // Check if we've exceeded max retries
                    if (retryCount >= this.maxRetries) {
                        console.error(`❌ Message ${messageId} (${type}) exceeded max retries, dropping`);
                        this.messageRetryAttempts.delete(messageId);
                        
                        // For critical messages, surface error to user
                        if (priority === 'critical') {
                            console.error(`🚨 CRITICAL: WebRTC signaling message ${type} failed permanently`);
                            // TODO: Trigger UI notification for connection failure
                        }
                        continue;
                    }
                    
                    // Calculate exponential backoff delay
                    const delay = this.retryBaseDelay * Math.pow(2, retryCount);
                    await new Promise(resolve => setTimeout(resolve, delay));
                    
                    // Attempt to send
                    try {
                        await this.sendMessageDirect(type, data, targetPeerId);
                        console.log(`✅ Queued message ${messageId} (${type}) sent successfully after ${retryCount + 1} attempt(s)`);
                        this.messageRetryAttempts.delete(messageId);
                    } catch (error) {
                        console.warn(`⚠️ Retry ${retryCount + 1}/${this.maxRetries} failed for ${messageId} (${type}):`, error);
                        
                        // Re-queue with incremented retry count
                        queue.push({
                            ...queuedMessage,
                            retryCount: retryCount + 1
                        });
                        this.messageRetryAttempts.set(messageId, retryCount + 1);
                    }
                }
            }
        } finally {
            this.isProcessingQueue = false;
        }
    }

    /**
     * Connects to the room-specific Reverb channel when Echo is ready
     */
    connectToChannel() {
        const connectWhenReady = () => {
            if (window.Echo) {
                this.setupChannel();
            } else {
                // Wait for Echo/Reverb client to be initialized
                setTimeout(connectWhenReady, 100);
            }
        };
        
        connectWhenReady();
    }

    /**
     * Sets up Reverb channel subscriptions and requests current room state
     */
    setupChannel() {
        // Use room-specific private channel for WebRTC signaling
        const channelName = `room.${this.roomWebRTC.roomData.id}`;
        
        // For WebRTC signaling, we use presence channels to track who's in the room
        this.channel = window.Echo.join(channelName);
        
        // Listen for WebRTC signaling messages
        this.channel.listen('.webrtc-signal', (message) => {
            const payload = message.data || message;
            
            if (!payload) return;

            // Ignore if message is targeted to a different peer
            if (payload.targetPeerId && payload.targetPeerId !== this.currentPeerId) {
                return;
            }

            // Filter out our own messages
            if (payload.senderId === this.currentPeerId) {
                return;
            }
            
            console.log('📨 Room message type:', payload.type, 'from:', payload.senderId);
            
            // Adapt to old message format for backward compatibility
            this.roomWebRTC.messageHandler.handleAblyMessage({
                data: payload
            });
        });

        // Handle users joining the presence channel
        this.channel.here((users) => {
            console.log('👥 Current room members:', users);
        });

        this.channel.joining((user) => {
            console.log('📡 Presence: User subscribed to channel:', user);
            console.log('⚠️ NOTE: This does NOT mean they joined a video slot yet!');
        });

        this.channel.leaving((user) => {
            console.log('📡 Presence: User unsubscribed from channel:', user);
        });

        // Handle channel subscription errors
        this.channel.error((error) => {
            console.error('❌ Reverb channel error:', error);
        });
        
        console.log('✅ Connected to room Reverb channel:', channelName);
        
        // Request current state from other users in this room
        setTimeout(() => {
            console.log('📡 Requesting current room state...');
            
            // CRITICAL: Peer ID must be set BEFORE connecting to channel
            // Don't generate it here - it should already be set by room-webrtc.js
            if (!this.currentPeerId) {
                console.error('❌ CRITICAL: Peer ID not set before connecting to channel!');
                console.error('❌ This indicates an initialization order bug in room-webrtc.js');
                throw new Error('Peer ID must be set before connecting to Reverb channel');
            }
            
            console.log(`🆔 Using peer ID: ${this.currentPeerId}`);
            
            this.publishMessage('request-state', {
                requesterId: this.currentPeerId,
                userId: this.roomWebRTC.currentUserId
            });
        }, 500);
    }

    /**
     * Publishes a WebRTC signaling message to the Reverb channel via server broadcast
     * 
     * ARCHITECTURE DECISION:
     * - HTTP POST → Laravel Broadcast → Reverb WebSocket (reliable, server-authenticated)
     * - NO whisper fallback (unreliable, bypasses server, no delivery guarantee)
     * - Failed messages are queued with exponential backoff retry
     * 
     * This ensures:
     * 1. All messages are authenticated and logged server-side
     * 2. Delivery confirmation via HTTP response
     * 3. Failed messages are retried intelligently
     * 4. Critical WebRTC signaling (offers/answers/ICE) never silently fails
     * 
     * @param {string} type - Message type (e.g., 'user-joined', 'webrtc-offer')
     * @param {object} data - Message payload
     * @param {string|null} targetPeerId - Optional specific peer to target
     */
    async publishMessage(type, data, targetPeerId = null) {
        if (!this.channel) {
            console.warn('❌ Reverb channel not ready');
            throw new Error('Reverb channel not initialized');
        }

        const priority = this.getMessagePriority(type);
        
        // CRITICAL messages bypass rate limiting
        if (priority !== 'critical' && !this.checkRateLimit()) {
            console.warn(`⚠️ Rate limit exceeded for ${priority} message: ${type}, queuing`);
            
            const messageId = `${type}-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
            this.messageQueue[priority].push({
                messageId,
                type,
                data,
                targetPeerId,
                retryCount: 0
            });
            
            // Trigger queue processing if not already running
            if (!this.isProcessingQueue) {
                this.processMessageQueue().catch(err => {
                    console.error('❌ Queue processing error:', err);
                });
            }
            
            return;
        }

        try {
            await this.sendMessageDirect(type, data, targetPeerId);
            
            if (priority === 'critical') {
                console.log(`✅ CRITICAL message sent: ${type}`, targetPeerId ? `(to ${targetPeerId})` : '(broadcast)');
            } else {
                console.log(`✅ Signaling message sent: ${type}`, targetPeerId ? `(to ${targetPeerId})` : '(broadcast)');
            }
        } catch (error) {
            console.error(`❌ Failed to send ${priority} message ${type}:`, error);
            
            // Queue for retry instead of failing silently
            const messageId = `${type}-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
            this.messageQueue[priority].push({
                messageId,
                type,
                data,
                targetPeerId,
                retryCount: 0
            });
            
            if (priority === 'critical') {
                console.warn(`🚨 CRITICAL message ${type} queued for retry`);
            }
            
            // Trigger queue processing
            if (!this.isProcessingQueue) {
                this.processMessageQueue().catch(err => {
                    console.error('❌ Queue processing error:', err);
                });
            }
            
            // For critical messages, also throw to caller
            if (priority === 'critical') {
                throw error;
            }
        }
    }
    
    /**
     * Sends message directly via HTTP POST → Laravel Broadcast → Reverb
     * No retries, no fallbacks - throws on failure for caller to handle
     */
    async sendMessageDirect(type, data, targetPeerId = null) {
        const message = {
            type: type,
            data: data,
            senderId: this.currentPeerId || 'anonymous',
            targetPeerId: targetPeerId,
            sequence: ++this.messageSequence // Add sequence number for drop detection
        };

        const response = await fetch(`/api/rooms/${this.roomWebRTC.roomData.id}/webrtc-signal`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCSRFToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify(message)
        });

        if (!response.ok) {
            const errorText = await response.text().catch(() => 'Unknown error');
            throw new Error(`HTTP ${response.status}: ${errorText}`);
        }
        
        const result = await response.json().catch(() => null);
        
        // Update last acked sequence if server confirms
        if (result && result.sequence) {
            this.lastAckedSequence = result.sequence;
        }
        
        return result;
    }

    /**
     * Alias for backward compatibility with old Ably code
     */
    publishToAbly(type, data, targetPeerId = null) {
        return this.publishMessage(type, data, targetPeerId);
    }

    /**
     * Generates a collision-resistant peer ID
     * Combines user ID, timestamp, and random component for uniqueness
     */
    generatePeerId() {
        const userId = this.roomWebRTC.currentUserId || 'anon';
        const timestamp = Date.now();
        
        // Use crypto.randomUUID if available, otherwise fallback to polyfill
        let randomComponent;
        if (typeof crypto !== 'undefined' && crypto.randomUUID) {
            randomComponent = crypto.randomUUID().split('-')[0]; // Take first segment
        } else {
            // Fallback polyfill for UUID v4
            randomComponent = 'xxxxxxxx'.replace(/[x]/g, () => {
                return (Math.random() * 16 | 0).toString(16);
            });
        }
        
        return `${userId}-${timestamp}-${randomComponent}`;
    }

    /**
     * Sets the current peer ID
     */
    setCurrentPeerId(peerId) {
        this.currentPeerId = peerId;
    }

    /**
     * Gets the current peer ID
     */
    getCurrentPeerId() {
        return this.currentPeerId;
    }

    /**
     * Gets the Reverb channel instance
     */
    getChannel() {
        return this.channel;
    }

    /**
     * Disconnects from the Reverb channel
     */
    disconnect() {
        if (this.channel) {
            window.Echo.leave(`room.${this.roomWebRTC.roomData.id}`);
            this.channel = null;
        }
    }
}

// Note: SignalingManager is already exported as a class above (line 10)

