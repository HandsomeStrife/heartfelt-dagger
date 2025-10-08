/**
 * EventBus - Centralized event system for cross-module communication
 * 
 * Reduces tight coupling between modules by allowing them to communicate
 * through events instead of direct method calls.
 * 
 * ARCHITECTURE: Implements pub-sub pattern for WebRTC room
 */

type EventHandler = (...args: any[]) => void;

interface EventSubscription {
    event: string;
    handler: EventHandler;
    once: boolean;
}

/**
 * Room event types for type safety
 */
export const RoomEvents = {
    // Peer connection events
    PEER_CONNECTED: 'peer:connected',
    PEER_DISCONNECTED: 'peer:disconnected',
    PEER_STATE_CHANGED: 'peer:state-changed',
    
    // Media events
    MEDIA_STREAM_ADDED: 'media:stream-added',
    MEDIA_STREAM_REMOVED: 'media:stream-removed',
    LOCAL_STREAM_READY: 'media:local-stream-ready',
    MICROPHONE_TOGGLED: 'media:microphone-toggled',
    VIDEO_TOGGLED: 'media:video-toggled',
    
    // Recording events
    RECORDING_STARTED: 'recording:started',
    RECORDING_STOPPED: 'recording:stopped',
    RECORDING_PAUSED: 'recording:paused',
    RECORDING_RESUMED: 'recording:resumed',
    RECORDING_ERROR: 'recording:error',
    
    // STT events
    STT_STARTED: 'stt:started',
    STT_STOPPED: 'stt:stopped',
    STT_TRANSCRIPT: 'stt:transcript',
    STT_ERROR: 'stt:error',
    
    // Signaling events
    SIGNALING_CONNECTED: 'signaling:connected',
    SIGNALING_DISCONNECTED: 'signaling:disconnected',
    SIGNALING_RECONNECTED: 'signaling:reconnected',
    SIGNALING_MESSAGE: 'signaling:message',
    
    // Consent events
    CONSENT_REQUESTED: 'consent:requested',
    CONSENT_GRANTED: 'consent:granted',
    CONSENT_DENIED: 'consent:denied',
    
    // Room events
    ROOM_JOINED: 'room:joined',
    ROOM_LEFT: 'room:left',
    USER_JOINED: 'room:user-joined',
    USER_LEFT: 'room:user-left',
    
    // UI events
    UI_ERROR: 'ui:error',
    UI_WARNING: 'ui:warning',
    UI_INFO: 'ui:info',
    
    // Health monitoring
    HEALTH_CHECK: 'health:check',
    CONNECTION_UNHEALTHY: 'health:unhealthy',
    CONNECTION_RECOVERED: 'health:recovered'
} as const;

export type RoomEvent = typeof RoomEvents[keyof typeof RoomEvents];

/**
 * EventBus class for pub-sub pattern
 */
export class EventBus {
    private events: Map<string, EventSubscription[]> = new Map();
    private eventHistory: Array<{ event: string; data: any; timestamp: number }> = [];
    private maxHistorySize: number = 100;
    
    /**
     * Subscribe to an event
     */
    on(event: string, handler: EventHandler): () => void {
        if (!this.events.has(event)) {
            this.events.set(event, []);
        }
        
        const subscription: EventSubscription = {
            event,
            handler,
            once: false
        };
        
        this.events.get(event)!.push(subscription);
        
        // Return unsubscribe function
        return () => this.off(event, handler);
    }
    
    /**
     * Subscribe to an event (fires once then unsubscribes)
     */
    once(event: string, handler: EventHandler): () => void {
        if (!this.events.has(event)) {
            this.events.set(event, []);
        }
        
        const subscription: EventSubscription = {
            event,
            handler,
            once: true
        };
        
        this.events.get(event)!.push(subscription);
        
        // Return unsubscribe function
        return () => this.off(event, handler);
    }
    
    /**
     * Unsubscribe from an event
     */
    off(event: string, handler: EventHandler): void {
        const subscriptions = this.events.get(event);
        if (!subscriptions) return;
        
        const index = subscriptions.findIndex(sub => sub.handler === handler);
        if (index !== -1) {
            subscriptions.splice(index, 1);
        }
        
        // Clean up empty event arrays
        if (subscriptions.length === 0) {
            this.events.delete(event);
        }
    }
    
    /**
     * Emit an event
     */
    emit(event: string, ...args: any[]): void {
        // Add to history
        this.addToHistory(event, args);
        
        const subscriptions = this.events.get(event);
        if (!subscriptions) return;
        
        // Create a copy to allow handlers to unsubscribe during execution
        const handlersToCall = [...subscriptions];
        
        for (const subscription of handlersToCall) {
            try {
                subscription.handler(...args);
                
                // Remove if it was a once subscription
                if (subscription.once) {
                    this.off(event, subscription.handler);
                }
            } catch (error) {
                console.error(`Error in event handler for ${event}:`, error);
            }
        }
    }
    
    /**
     * Remove all event listeners
     */
    clear(): void {
        this.events.clear();
    }
    
    /**
     * Get all subscribed events
     */
    getEvents(): string[] {
        return Array.from(this.events.keys());
    }
    
    /**
     * Get subscriber count for an event
     */
    getSubscriberCount(event: string): number {
        return this.events.get(event)?.length || 0;
    }
    
    /**
     * Check if event has subscribers
     */
    hasSubscribers(event: string): boolean {
        return this.getSubscriberCount(event) > 0;
    }
    
    /**
     * Add event to history
     */
    private addToHistory(event: string, data: any[]): void {
        this.eventHistory.push({
            event,
            data,
            timestamp: Date.now()
        });
        
        // Limit history size
        if (this.eventHistory.length > this.maxHistorySize) {
            this.eventHistory.shift();
        }
    }
    
    /**
     * Get event history
     */
    getHistory(count: number = 50): Array<{ event: string; data: any; timestamp: number }> {
        return this.eventHistory.slice(-count);
    }
    
    /**
     * Export event history
     */
    exportHistory(): void {
        const blob = new Blob([JSON.stringify(this.eventHistory, null, 2)], {
            type: 'application/json'
        });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `event-history-${Date.now()}.json`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
        
        console.log(`📥 Exported ${this.eventHistory.length} events`);
    }
    
    /**
     * Get diagnostics
     */
    getDiagnostics(): {
        totalEvents: number;
        subscribedEvents: string[];
        subscriberCounts: Record<string, number>;
        recentEvents: Array<{ event: string; timestamp: number }>;
    } {
        const subscriberCounts: Record<string, number> = {};
        for (const [event, subscriptions] of this.events.entries()) {
            subscriberCounts[event] = subscriptions.length;
        }
        
        return {
            totalEvents: this.events.size,
            subscribedEvents: Array.from(this.events.keys()),
            subscriberCounts,
            recentEvents: this.eventHistory.slice(-10).map(e => ({
                event: e.event,
                timestamp: e.timestamp
            }))
        };
    }
}

/**
 * Global event bus instance
 */
export const eventBus = new EventBus();

/**
 * Typed event emitter helper
 */
export function emitRoomEvent(event: RoomEvent, ...args: any[]): void {
    eventBus.emit(event, ...args);
}

/**
 * Typed event subscriber helper
 */
export function onRoomEvent(event: RoomEvent, handler: EventHandler): () => void {
    return eventBus.on(event, handler);
}

