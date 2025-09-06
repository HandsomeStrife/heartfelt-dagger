/**
 * BrowserSTT - Web Speech API implementation
 * 
 * Handles browser-based speech recognition using the Web Speech API,
 * with browser-specific optimizations, error handling, and fallback logic.
 */
export class BrowserSTT {
    constructor(speechManager) {
        this.speechManager = speechManager;
        this.recognition = null;
        this.isListening = false;
        this.isInitialized = false;
        this.restartAttempts = 0;
        this.maxRestartAttempts = 3;
        this.restartDelay = 2000;
        
        // Configuration
        this.config = {
            continuous: true,
            interimResults: false,
            maxAlternatives: 1,
            language: 'en-GB'
        };
        
        // Browser compatibility
        this.SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        this.isSupported = !!this.SpeechRecognition;
        
        // State tracking
        this.lastResultTime = null;
        this.silenceTimeout = null;
        this.silenceThreshold = 10000; // 10 seconds of silence before restart
        
        this.analyzeBrowserSupport();
    }

    /**
     * Analyzes browser support for speech recognition
     */
    analyzeBrowserSupport() {
        console.log('🎤 === Browser Speech Recognition Analysis ===');
        
        const userAgent = navigator.userAgent;
        const isChrome = /Chrome/.test(userAgent) && /Google Inc/.test(navigator.vendor);
        const isFirefox = /Firefox/.test(userAgent);
        const isSafari = /Safari/.test(userAgent) && !/Chrome/.test(userAgent);
        const isEdge = /Edg/.test(userAgent);
        
        console.log('🎤 Browser Support Analysis:');
        console.log(`- User Agent: ${userAgent}`);
        console.log(`- Chrome: ${isChrome}`);
        console.log(`- Firefox: ${isFirefox}`);
        console.log(`- Safari: ${isSafari}`);
        console.log(`- Edge: ${isEdge}`);
        console.log(`- webkitSpeechRecognition: ${'webkitSpeechRecognition' in window}`);
        console.log(`- SpeechRecognition: ${'SpeechRecognition' in window}`);
        console.log(`- Overall Support: ${this.isSupported}`);
        
        // Browser-specific optimizations
        if (isChrome) {
            this.config.continuous = true;
            this.config.interimResults = false; // Chrome handles this well
        } else if (isFirefox) {
            // Firefox has limited support
            console.warn('🎤 ⚠️ Firefox has limited Web Speech API support');
        } else if (isSafari) {
            // Safari support varies
            console.warn('🎤 ⚠️ Safari Web Speech API support may be limited');
        }
    }

    /**
     * Initializes browser speech recognition
     */
    async initialize(sttConfig) {
        if (this.isInitialized) {
            console.warn('🎤 Browser STT already initialized');
            return;
        }

        console.log('🎤 === Browser Speech Recognition Initialization ===');

        if (!this.isSupported) {
            throw new Error('Web Speech API not supported in this browser');
        }

        try {
            // Create recognition instance
            this.recognition = new this.SpeechRecognition();
            console.log('🎤 ✅ SpeechRecognition instance created successfully');
            
            // Configure recognition
            this.configureRecognition(sttConfig);
            
            // Set up event handlers
            this.setupEventHandlers();
            
            // Test recognition capability
            await this.testRecognitionCapability();
            
            this.isInitialized = true;
            console.log('🎤 ✅ Browser speech recognition initialized');
        } catch (error) {
            console.error('🎤 ❌ Failed to initialize browser STT:', error);
            throw error;
        }
    }

    /**
     * Configures speech recognition settings
     */
    configureRecognition(sttConfig) {
        // Apply configuration
        this.recognition.continuous = this.config.continuous;
        this.recognition.interimResults = this.config.interimResults;
        this.recognition.maxAlternatives = this.config.maxAlternatives;
        
        // Set language
        const roomLanguage = sttConfig?.stt_language;
        const navigatorLanguage = navigator.language || navigator.userLanguage;
        this.recognition.lang = roomLanguage || navigatorLanguage || this.config.language;
        
        console.log('🎤 Configuration:');
        console.log(`- Language: ${this.recognition.lang}`);
        console.log(`- Continuous: ${this.recognition.continuous}`);
        console.log(`- Interim Results: ${this.recognition.interimResults}`);
        console.log(`- Max Alternatives: ${this.recognition.maxAlternatives}`);
        console.log(`- Room STT Lang: ${roomLanguage || 'not set'}`);
        console.log(`- Navigator Language: ${navigatorLanguage}`);
    }

    /**
     * Sets up event handlers for speech recognition
     */
    setupEventHandlers() {
        // Recognition start
        this.recognition.onstart = () => {
            console.log('🎤 ✅ Browser speech recognition started');
            this.isListening = true;
            this.restartAttempts = 0;
            this.startSilenceMonitoring();
        };

        // Recognition end
        this.recognition.onend = () => {
            console.log('🎤 Browser speech recognition ended');
            this.isListening = false;
            this.stopSilenceMonitoring();
            
            // Auto-restart if we should still be listening
            if (this.shouldAutoRestart()) {
                this.handleAutoRestart();
            }
        };

        // Recognition results
        this.recognition.onresult = (event) => {
            this.handleRecognitionResult(event);
        };

        // Recognition errors
        this.recognition.onerror = (event) => {
            this.handleRecognitionError(event);
        };

        // Audio start
        this.recognition.onaudiostart = () => {
            console.log('🎤 🎵 Audio capture started');
        };

        // Audio end
        this.recognition.onaudioend = () => {
            console.log('🎤 🎵 Audio capture ended');
        };

        // Sound start
        this.recognition.onsoundstart = () => {
            console.log('🎤 🔊 Sound detected');
            this.lastResultTime = Date.now();
        };

        // Sound end
        this.recognition.onsoundend = () => {
            console.log('🎤 🔇 Sound ended');
        };

        // Speech start
        this.recognition.onspeechstart = () => {
            console.log('🎤 🗣️ Speech detected');
            this.lastResultTime = Date.now();
        };

        // Speech end
        this.recognition.onspeechend = () => {
            console.log('🎤 🗣️ Speech ended');
        };
    }

    /**
     * Tests recognition capability
     */
    async testRecognitionCapability() {
        console.log('🎤 Testing recognition capability...');
        
        try {
            // Check microphone permissions
            const permissionStatus = await this.checkMicrophonePermissions();
            console.log('🎤 Permission Status:', permissionStatus);
            
            // Test recognition creation
            const testRecognition = new this.SpeechRecognition();
            testRecognition.lang = this.recognition.lang;
            
            console.log('🎤 ✅ Recognition capability test passed');
        } catch (error) {
            console.error('🎤 ❌ Recognition capability test failed:', error);
            throw error;
        }
    }

    /**
     * Checks microphone permissions
     */
    async checkMicrophonePermissions() {
        try {
            if ('permissions' in navigator) {
                const permission = await navigator.permissions.query({ name: 'microphone' });
                return permission.state;
            } else {
                console.warn('🎤 Permissions API not available');
                return 'unknown';
            }
        } catch (error) {
            console.warn('🎤 Could not check microphone permissions:', error);
            return 'unknown';
        }
    }

    /**
     * Starts listening for speech
     */
    async startListening() {
        if (!this.isInitialized) {
            throw new Error('Browser STT not initialized');
        }

        if (this.isListening) {
            console.warn('🎤 Already listening');
            return;
        }

        console.log('🎤 Starting browser speech recognition...');

        try {
            this.recognition.start();
            console.log('🎤 ✅ Browser speech recognition start() called');
        } catch (error) {
            console.error('🎤 ❌ Failed to start browser speech recognition:', error);
            throw error;
        }
    }

    /**
     * Stops listening for speech
     */
    async stopListening() {
        if (!this.isListening) {
            console.warn('🎤 Not currently listening');
            return;
        }

        console.log('🎤 Stopping browser speech recognition...');

        try {
            this.recognition.stop();
            this.stopSilenceMonitoring();
            console.log('🎤 ✅ Browser speech recognition stop() called');
        } catch (error) {
            console.error('🎤 ❌ Failed to stop browser speech recognition:', error);
            throw error;
        }
    }

    /**
     * Handles recognition results
     */
    handleRecognitionResult(event) {
        console.log('🎤 📝 Recognition result event:', event);
        
        this.lastResultTime = Date.now();
        
        for (let i = event.resultIndex; i < event.results.length; i++) {
            const result = event.results[i];
            const transcript = result[0].transcript.trim();
            const confidence = result[0].confidence;
            const isFinal = result.isFinal;
            
            if (transcript) {
                console.log(`🎤 📝 Transcript (${isFinal ? 'final' : 'interim'}): "${transcript}" (confidence: ${confidence})`);
                
                // Send to speech manager
                this.speechManager.handleTranscript(transcript, isFinal, confidence);
            }
        }
    }

    /**
     * Handles recognition errors
     */
    handleRecognitionError(event) {
        console.error('🎤 ❌ Browser speech recognition error:', event.error);
        
        const errorMessage = this.getErrorMessage(event.error);
        console.error('🎤 Error details:', errorMessage);
        
        // Handle specific errors
        switch (event.error) {
            case 'network':
                console.error('🎤 🌐 Network error - checking connectivity...');
                this.handleNetworkError();
                break;
            case 'not-allowed':
                console.error('🎤 🚫 Microphone permission denied');
                this.handlePermissionError();
                break;
            case 'no-speech':
                console.warn('🎤 🔇 No speech detected - will auto-restart');
                break;
            case 'audio-capture':
                console.error('🎤 🎵 Audio capture error');
                this.handleAudioError();
                break;
            case 'service-not-allowed':
                console.error('🎤 🚫 Speech service not allowed');
                break;
            default:
                console.error(`🎤 ❌ Unknown error: ${event.error}`);
        }
        
        // Notify speech manager
        this.speechManager.notifyError(new Error(errorMessage));
    }

    /**
     * Gets human-readable error message
     */
    getErrorMessage(errorCode) {
        const errorMessages = {
            'network': 'Network connectivity issue',
            'not-allowed': 'Microphone access denied',
            'no-speech': 'No speech detected',
            'audio-capture': 'Audio capture failed',
            'service-not-allowed': 'Speech service not allowed',
            'aborted': 'Recognition aborted',
            'language-not-supported': 'Language not supported',
            'bad-grammar': 'Grammar error'
        };
        
        return errorMessages[errorCode] || `Unknown error: ${errorCode}`;
    }

    /**
     * Handles network errors
     */
    async handleNetworkError() {
        console.log('🎤 🌐 Diagnosing network issue...');
        
        try {
            const diagnosis = await this.speechManager.roomWebRTC.diagnosticsRunner.diagnoseSpeechNetworkIssue();
            console.log('🎤 Network diagnosis:', diagnosis);
        } catch (error) {
            console.error('🎤 Failed to diagnose network issue:', error);
        }
    }

    /**
     * Handles permission errors
     */
    handlePermissionError() {
        console.error('🎤 🚫 Microphone permission required for speech recognition');
        
        // Could show UI to request permissions
        // For now, just log the issue
    }

    /**
     * Handles audio capture errors
     */
    handleAudioError() {
        console.error('🎤 🎵 Audio capture failed - checking audio setup...');
        
        // Could run audio diagnostics here
    }

    /**
     * Starts silence monitoring
     */
    startSilenceMonitoring() {
        this.stopSilenceMonitoring(); // Clear any existing timeout
        
        this.silenceTimeout = setTimeout(() => {
            console.warn('🎤 🔇 Silence threshold reached - restarting recognition');
            this.handleSilenceTimeout();
        }, this.silenceThreshold);
    }

    /**
     * Stops silence monitoring
     */
    stopSilenceMonitoring() {
        if (this.silenceTimeout) {
            clearTimeout(this.silenceTimeout);
            this.silenceTimeout = null;
        }
    }

    /**
     * Handles silence timeout
     */
    handleSilenceTimeout() {
        if (this.isListening) {
            console.log('🎤 🔄 Restarting due to silence timeout');
            this.restartRecognition();
        }
    }

    /**
     * Determines if auto-restart should happen
     */
    shouldAutoRestart() {
        return this.restartAttempts < this.maxRestartAttempts;
    }

    /**
     * Handles auto-restart logic
     */
    handleAutoRestart() {
        if (this.restartAttempts >= this.maxRestartAttempts) {
            console.error('🎤 💀 Max restart attempts reached');
            this.speechManager.notifyError(new Error('Speech recognition failed after multiple restart attempts'));
            return;
        }

        this.restartAttempts++;
        console.log(`🎤 🔄 Auto-restarting recognition (attempt ${this.restartAttempts}/${this.maxRestartAttempts})`);
        
        setTimeout(() => {
            this.restartRecognition();
        }, this.restartDelay);
    }

    /**
     * Restarts recognition
     */
    async restartRecognition() {
        try {
            console.log('🎤 🔄 Restarting browser speech recognition...');
            
            if (this.isListening) {
                await this.stopListening();
                // Wait a moment before restarting
                await new Promise(resolve => setTimeout(resolve, 500));
            }
            
            await this.startListening();
            console.log('🎤 ✅ Recognition restarted successfully');
        } catch (error) {
            console.error('🎤 ❌ Failed to restart recognition:', error);
            this.speechManager.notifyError(error);
        }
    }

    /**
     * Gets current status
     */
    getStatus() {
        return {
            isSupported: this.isSupported,
            isInitialized: this.isInitialized,
            isListening: this.isListening,
            language: this.recognition?.lang,
            restartAttempts: this.restartAttempts,
            lastResultTime: this.lastResultTime,
            config: { ...this.config }
        };
    }

    /**
     * Destroys the browser STT
     */
    destroy() {
        console.log('🎤 Destroying BrowserSTT');
        
        // Stop listening
        if (this.isListening) {
            this.stopListening();
        }
        
        // Stop silence monitoring
        this.stopSilenceMonitoring();
        
        // Clean up recognition
        if (this.recognition) {
            // Remove event listeners
            this.recognition.onstart = null;
            this.recognition.onend = null;
            this.recognition.onresult = null;
            this.recognition.onerror = null;
            this.recognition.onaudiostart = null;
            this.recognition.onaudioend = null;
            this.recognition.onsoundstart = null;
            this.recognition.onsoundend = null;
            this.recognition.onspeechstart = null;
            this.recognition.onspeechend = null;
            
            this.recognition = null;
        }
        
        // Reset state
        this.isInitialized = false;
        this.isListening = false;
        this.restartAttempts = 0;
        this.lastResultTime = null;
        
        console.log('🎤 BrowserSTT destroyed');
    }
}
