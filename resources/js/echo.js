import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

try {
    // Initialize Laravel Echo with Reverb (uses Pusher protocol)
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
        wsPort: import.meta.env.VITE_REVERB_PORT || 8080,
        wssPort: import.meta.env.VITE_REVERB_PORT || 8080,
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
        // Generate a unique client ID for this connection
        auth: {
            headers: {
                'X-Socket-ID': 'video-user-' + Math.random().toString(36).substr(2, 9)
            }
        }
    });

    // Track if this is initial connection or reconnection
    let hasConnectedBefore = false;

    // Monitor connection states
    window.Echo.connector.pusher.connection.bind('connected', () => {
        console.log('✅ Connected to Reverb successfully');
        
        // If this is a reconnection, trigger state recovery in RoomWebRTC
        if (hasConnectedBefore && window.roomWebRTC && window.roomWebRTC.isInitialized) {
            console.log('🔄 Reverb reconnected - triggering state recovery');
            window.roomWebRTC.handleAblyReconnected(); // Keep same method name for compatibility
        }
        
        hasConnectedBefore = true;
    });

    window.Echo.connector.pusher.connection.bind('disconnected', () => {
        console.warn('⚠️ Reverb connection disconnected');
        
        // Notify RoomWebRTC of disconnection
        if (window.roomWebRTC && window.roomWebRTC.isInitialized) {
            window.roomWebRTC.handleAblyConnectionLost({ message: 'Reverb disconnected' });
        }
    });

    window.Echo.connector.pusher.connection.bind('failed', () => {
        console.error('❌ Failed to connect to Reverb');
        
        // Notify RoomWebRTC of failure
        if (window.roomWebRTC && window.roomWebRTC.isInitialized) {
            window.roomWebRTC.handleAblyConnectionFailed({ message: 'Reverb connection failed' });
        }
    });

    window.Echo.connector.pusher.connection.bind('unavailable', () => {
        console.warn('⚠️ Reverb connection unavailable (suspended)');
        
        // Notify RoomWebRTC that signaling is suspended
        if (window.roomWebRTC && window.roomWebRTC.isInitialized) {
            window.roomWebRTC.handleAblyConnectionSuspended({ message: 'Reverb unavailable' });
        }
    });

    window.Echo.connector.pusher.connection.bind('state_change', (states) => {
        console.log(`🔌 Reverb connection state: ${states.previous} -> ${states.current}`);
    });
    
    console.log('✅ Reverb/Echo client initialized successfully');
} catch (error) {
    console.error('❌ Failed to initialize Reverb client:', error);
}
