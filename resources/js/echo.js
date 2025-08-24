import * as Ably from 'ably';

try {
    // Initialize Ably client directly
    window.AblyClient = new Ably.Realtime({
        key: import.meta.env.VITE_ABLY_PUBLIC_KEY,
        clientId: 'video-slots-user-' + Math.random().toString(36).substr(2, 9)
    });
    
    window.AblyClient.connection.on('connected', () => {
        // console.log('✅ Connected to Ably directly');
    });
    
    window.AblyClient.connection.on('failed', (error) => {
        console.error('❌ Failed to connect to Ably:', error);
    });
    
    // console.log('✅ Ably client initialized successfully:', window.AblyClient);
} catch (error) {
    console.error('❌ Failed to initialize Ably client:', error);
}
