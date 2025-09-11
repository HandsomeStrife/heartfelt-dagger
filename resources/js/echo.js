import * as Ably from 'ably';

try {
    // Initialize Ably client directly with rate limiting protection
    window.AblyClient = new Ably.Realtime({
        key: import.meta.env.VITE_ABLY_PUBLIC_KEY,
        clientId: 'video-slots-user-' + Math.random().toString(36).substr(2, 9),
        // Add rate limiting protection
        maxMessageSize: 65536, // 64KB max message size
        requestTimeout: 15000, // 15 second timeout
        realtimeRequestTimeout: 15000
    });
    
    window.AblyClient.connection.on('connected', () => {
        console.log('✅ Connected to Ably successfully');
    });
    
    window.AblyClient.connection.on('failed', (error) => {
        console.error('❌ Failed to connect to Ably:', error);
        if (error.message && error.message.includes('rate limit')) {
            console.warn('⚠️ Ably connection rate limited - video features may be affected');
        }
    });

    window.AblyClient.connection.on('suspended', (error) => {
        console.warn('⚠️ Ably connection suspended:', error);
    });

    window.AblyClient.connection.on('disconnected', (error) => {
        console.warn('⚠️ Ably connection disconnected:', error);
    });
    
    console.log('✅ Ably client initialized successfully');
} catch (error) {
    console.error('❌ Failed to initialize Ably client:', error);
}
