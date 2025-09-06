/**
 * DiagnosticsRunner - Comprehensive diagnostics for Speech Recognition and WebRTC
 * 
 * Provides detailed analysis of browser capabilities, permissions, and network conditions
 * to help troubleshoot speech recognition and WebRTC issues.
 */
export class DiagnosticsRunner {
    /**
     * Runs comprehensive diagnostics on Speech Recognition
     */
    static runSpeechDiagnostics() {
        console.log('🎤 === Running Speech Recognition Diagnostics ===');
        
        // Test 1: API Availability
        console.log('🎤 Test 1: API Availability');
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        console.log(`  - SpeechRecognition constructor: ${typeof SpeechRecognition}`);
        
        if (SpeechRecognition) {
            try {
                const testInstance = new SpeechRecognition();
                console.log('  - ✅ Can create instance');
                console.log(`  - Default language: ${testInstance.lang || 'none'}`);
                console.log(`  - readyState: ${testInstance.readyState}`);
                testInstance.abort(); // Clean up test instance
            } catch (e) {
                console.error('  - ❌ Cannot create instance:', e);
            }
        }
        
        // Test 2: Media Permissions
        console.log('🎤 Test 2: Media Permissions');
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ audio: true })
                .then(stream => {
                    console.log('  - ✅ Microphone access granted');
                    console.log(`  - Audio tracks: ${stream.getAudioTracks().length}`);
                    stream.getTracks().forEach(track => track.stop()); // Clean up
                })
                .catch(err => {
                    console.error('  - ❌ Microphone access denied:', err);
                });
        } else {
            console.error('  - ❌ getUserMedia not available');
        }
        
        // Test 3: Network Connectivity
        console.log('🎤 Test 3: Network Connectivity');
        console.log(`  - Online status: ${navigator.onLine}`);
        console.log(`  - Connection type: ${navigator.connection?.effectiveType || 'unknown'}`);
        
        // Test 4: SSL/HTTPS
        console.log('🎤 Test 4: Security Context');
        console.log(`  - Protocol: ${window.location.protocol}`);
        console.log(`  - Is secure context: ${window.isSecureContext}`);
        
        if (window.location.protocol !== 'https:' && window.location.hostname !== 'localhost') {
            console.warn('  - ⚠️ Speech API requires HTTPS in production');
        }
        
        console.log('🎤 === Diagnostics Complete ===');
    }

    /**
     * Diagnoses specific network issues with Speech Recognition
     */
    static diagnoseSpeechNetworkIssue() {
        console.log('🎤 === Diagnosing Speech Network Issue ===');
        
        // Test basic connectivity
        console.log('🎤 Testing basic connectivity...');
        fetch('https://www.google.com/favicon.ico', { mode: 'no-cors' })
            .then(() => {
                console.log('  - ✅ Basic internet connectivity working');
                console.log('  - Issue likely with Google Speech API specifically');
                
                // Test if it's a CORS issue
                console.log('🎤 Potential solutions:');
                console.log('  1. Try using HTTPS instead of HTTP');
                console.log('  2. Check if corporate firewall blocks speech.googleapis.com');
                console.log('  3. Try different browser (Chrome works best)');
                console.log('  4. Check if ad blockers are interfering');
                
            })
            .catch(() => {
                console.error('  - ❌ No internet connectivity');
                console.error('  - Check your network connection');
            });
            
        // Check current protocol
        if (window.location.protocol === 'http:' && window.location.hostname !== 'localhost') {
            console.warn('🎤 ⚠️ Using HTTP in production may cause Speech API issues');
            console.warn('  - Web Speech API works better with HTTPS');
        }
        
        // Check for ad blockers or extensions that might interfere
        console.log('🎤 Checking for potential interference...');
        if (navigator.plugins && navigator.plugins.length === 0) {
            console.warn('  - ⚠️ No plugins detected - possible ad blocker interference');
        }
        
        console.log('🎤 === Network Diagnosis Complete ===');
    }

    /**
     * Analyzes browser support for speech recognition
     */
    static analyzeBrowserSupport() {
        const hasWebSpeechAPI = 'webkitSpeechRecognition' in window || 'SpeechRecognition' in window;
        const userAgent = navigator.userAgent;
        const isChrome = /Chrome/.test(userAgent);
        const isFirefox = /Firefox/.test(userAgent);
        const isSafari = /Safari/.test(userAgent) && !/Chrome/.test(userAgent);
        const isEdge = /Edg/.test(userAgent);
        
        console.log('🎤 Browser Support Analysis:');
        console.log(`  - User Agent: ${userAgent}`);
        console.log(`  - Chrome: ${isChrome}`);
        console.log(`  - Firefox: ${isFirefox}`);
        console.log(`  - Safari: ${isSafari}`);
        console.log(`  - Edge: ${isEdge}`);
        console.log(`  - webkitSpeechRecognition: ${'webkitSpeechRecognition' in window}`);
        console.log(`  - SpeechRecognition: ${'SpeechRecognition' in window}`);
        console.log(`  - Overall Support: ${hasWebSpeechAPI}`);

        return {
            hasWebSpeechAPI,
            isChrome,
            isFirefox,
            isSafari,
            isEdge,
            userAgent
        };
    }

    /**
     * Checks permission status for microphone access
     */
    static async checkPermissionStatus() {
        console.log('🎤 Permission Status:');
        if (navigator.permissions) {
            try {
                const result = await navigator.permissions.query({name: 'microphone'});
                console.log(`  - Microphone Permission: ${result.state}`);
                return result.state;
            } catch (err) {
                console.log(`  - Microphone Permission: Unable to check (${err.message})`);
                return 'unknown';
            }
        } else {
            console.log('  - Permissions API not available');
            return 'unavailable';
        }
    }
}
