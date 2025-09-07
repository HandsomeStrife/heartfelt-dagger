/**
 * DiagnosticsRunner - Runs diagnostic tests for speech recognition and WebRTC
 * 
 * Provides comprehensive diagnostics for troubleshooting speech recognition,
 * network connectivity, and browser compatibility issues.
 */

export class DiagnosticsRunner {
    constructor(roomWebRTC) {
        this.roomWebRTC = roomWebRTC;
    }

    /**
     * Runs comprehensive diagnostics on Speech Recognition
     */
    runSpeechDiagnostics() {
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
    diagnoseSpeechNetworkIssue() {
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
     * Runs WebRTC diagnostics
     */
    runWebRTCDiagnostics() {
        console.log('🔗 === Running WebRTC Diagnostics ===');
        
        // Test 1: RTCPeerConnection availability
        console.log('🔗 Test 1: RTCPeerConnection Availability');
        if (typeof RTCPeerConnection !== 'undefined') {
            console.log('  - ✅ RTCPeerConnection available');
            
            try {
                const testPC = new RTCPeerConnection();
                console.log('  - ✅ Can create RTCPeerConnection instance');
                testPC.close();
            } catch (e) {
                console.error('  - ❌ Cannot create RTCPeerConnection:', e);
            }
        } else {
            console.error('  - ❌ RTCPeerConnection not available');
        }
        
        // Test 2: ICE servers
        console.log('🔗 Test 2: ICE Configuration');
        const iceConfig = this.roomWebRTC.iceManager.getIceConfig();
        console.log(`  - ICE servers count: ${iceConfig.iceServers?.length || 0}`);
        console.log(`  - ICE ready: ${this.roomWebRTC.iceManager.isReady()}`);
        
        // Test 3: Media devices
        console.log('🔗 Test 3: Media Devices');
        if (navigator.mediaDevices) {
            navigator.mediaDevices.enumerateDevices()
                .then(devices => {
                    const audioInputs = devices.filter(d => d.kind === 'audioinput');
                    const videoInputs = devices.filter(d => d.kind === 'videoinput');
                    console.log(`  - Audio input devices: ${audioInputs.length}`);
                    console.log(`  - Video input devices: ${videoInputs.length}`);
                })
                .catch(err => {
                    console.error('  - ❌ Cannot enumerate devices:', err);
                });
        } else {
            console.error('  - ❌ MediaDevices API not available');
        }
        
        console.log('🔗 === WebRTC Diagnostics Complete ===');
    }

    /**
     * Runs browser compatibility diagnostics
     */
    runBrowserDiagnostics() {
        console.log('🌐 === Running Browser Diagnostics ===');
        
        const userAgent = navigator.userAgent;
        const isChrome = /Chrome/.test(userAgent);
        const isFirefox = /Firefox/.test(userAgent);
        const isSafari = /Safari/.test(userAgent) && !/Chrome/.test(userAgent);
        const isEdge = /Edg/.test(userAgent);
        
        console.log('🌐 Browser Detection:');
        console.log(`  - User Agent: ${userAgent}`);
        console.log(`  - Chrome: ${isChrome}`);
        console.log(`  - Firefox: ${isFirefox}`);
        console.log(`  - Safari: ${isSafari}`);
        console.log(`  - Edge: ${isEdge}`);
        
        // Feature support
        console.log('🌐 Feature Support:');
        console.log(`  - WebRTC: ${typeof RTCPeerConnection !== 'undefined'}`);
        console.log(`  - MediaRecorder: ${typeof MediaRecorder !== 'undefined'}`);
        console.log(`  - Speech Recognition: ${typeof (window.SpeechRecognition || window.webkitSpeechRecognition) !== 'undefined'}`);
        console.log(`  - getUserMedia: ${!!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia)}`);
        console.log(`  - WebSockets: ${typeof WebSocket !== 'undefined'}`);
        
        console.log('🌐 === Browser Diagnostics Complete ===');
    }

    /**
     * Runs all diagnostics
     */
    runAllDiagnostics() {
        console.log('🔍 === Running Complete Diagnostics Suite ===');
        
        this.runBrowserDiagnostics();
        this.runWebRTCDiagnostics();
        this.runSpeechDiagnostics();
        
        console.log('🔍 === All Diagnostics Complete ===');
    }

    /**
     * Tests media permissions
     */
    async testMediaPermissions() {
        console.log('🎥 === Testing Media Permissions ===');
        
        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: true,
                audio: true
            });
            
            console.log('🎥 ✅ Full media access granted');
            console.log(`  - Video tracks: ${stream.getVideoTracks().length}`);
            console.log(`  - Audio tracks: ${stream.getAudioTracks().length}`);
            
            // Clean up
            stream.getTracks().forEach(track => track.stop());
            
            return true;
        } catch (error) {
            console.error('🎥 ❌ Media access failed:', error);
            
            // Try audio only
            try {
                const audioStream = await navigator.mediaDevices.getUserMedia({
                    audio: true
                });
                
                console.log('🎥 ⚠️ Audio-only access granted');
                audioStream.getTracks().forEach(track => track.stop());
                
                return 'audio-only';
            } catch (audioError) {
                console.error('🎥 ❌ No media access available:', audioError);
                return false;
            }
        }
    }
}
