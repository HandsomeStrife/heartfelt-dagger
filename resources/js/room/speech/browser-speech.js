/**
 * Browser Speech Recognition - Web Speech API Integration
 * 
 * Handles browser-based speech-to-text using the Web Speech API
 * with proper microphone mute integration and transcript uploading.
 */

import TranscriptUploader from './transcript-uploader.js';

export default class BrowserSpeechRecognition {
    constructor(roomData, currentUserId) {
        this.roomData = roomData;
        this.currentUserId = currentUserId;
        this.speechRecognition = null;
        this.speechBuffer = [];
        this.speechChunkStartedAt = null;
        this.speechUploadInterval = null;
        this.isSpeechEnabled = false;
        this.isActive = false;
        
        // Restart loop prevention
        this.consecutiveErrors = 0;
        this.maxRestartAttempts = 5;
        this.restartAttempts = 0;
        this.backoffDelay = 1000; // Start at 1 second
        this.maxBackoffDelay = 30000; // Max 30 seconds
        this.lastSuccessfulStartTime = null;
        
        // Create transcript uploader instance
        this.transcriptUploader = new TranscriptUploader(roomData, currentUserId);
        
        // Event callbacks
        this.onTranscript = null;
        this.onError = null;
        this.onStatusChange = null;
    }

    /**
     * Initialize browser speech recognition
     */
    async initialize() {
        console.log('🎤 === Browser Speech Recognition Initialization ===');
        
        // Comprehensive browser support detection
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
        
        // Check if speech recognition is supported
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        
        if (!SpeechRecognition) {
            console.error('🎤 ❌ Speech recognition not supported in this browser');
            console.error('🎤 Web Speech API requires Chrome, Edge, or Safari');
            console.error('🎤 Firefox does not support Web Speech API');
            this.speechRecognition = null;
            if (this.onError) {
                this.onError(new Error('Browser speech recognition not supported'));
            }
            return false;
        }

        console.log('🎤 ✅ Speech Recognition API available');

        try {
            this.speechRecognition = new SpeechRecognition();
            console.log('🎤 ✅ SpeechRecognition instance created successfully');
        } catch (error) {
            console.error('🎤 ❌ Failed to create SpeechRecognition instance:', error);
            this.speechRecognition = null;
            if (this.onError) {
                this.onError(error);
            }
            return false;
        }

        // Configure speech recognition parameters
        const targetLang = this.roomData.stt_lang || navigator.language || 'en-GB';
        this.speechRecognition.lang = targetLang;
        this.speechRecognition.continuous = true;
        this.speechRecognition.interimResults = false;
        this.speechRecognition.maxAlternatives = 1;
        
        console.log('🎤 Configuration:');
        console.log(`  - Language: ${targetLang}`);
        console.log(`  - Continuous: ${this.speechRecognition.continuous}`);
        console.log(`  - Interim Results: ${this.speechRecognition.interimResults}`);
        console.log(`  - Max Alternatives: ${this.speechRecognition.maxAlternatives}`);
        console.log(`  - Room STT Lang: ${this.roomData.stt_lang || 'not set'}`);
        console.log(`  - Navigator Language: ${navigator.language || 'not available'}`);
        
        // Check for permission requirements
        console.log('🎤 Permission Status:');
        if (navigator.permissions) {
            try {
                const result = await navigator.permissions.query({name: 'microphone'});
                console.log(`  - Microphone Permission: ${result.state}`);
            } catch (err) {
                console.log(`  - Microphone Permission: Unable to check (${err.message})`);
            }
        } else {
            console.log('  - Permissions API not available');
        }

        this.setupEventHandlers();
        
        console.log('🎤 ✅ Browser speech recognition initialized');
        return true;
    }

    /**
     * Set up event handlers for speech recognition
     */
    setupEventHandlers() {
        let lastErrorAt = 0;

        this.speechRecognition.onresult = (event) => {
            for (let i = event.resultIndex; i < event.results.length; i++) {
                if (event.results[i].isFinal) {
                    const transcript = event.results[i][0].transcript.trim();
                    const confidence = event.results[i][0].confidence;
                    
                    if (transcript) {
                        this.speechBuffer.push({
                            text: transcript,
                            confidence: confidence,
                            timestamp: Date.now()
                        });
                        
                        console.log('🎤 Speech recognized:', transcript);
                        
                        // Reset error counters on successful transcription
                        this.consecutiveErrors = 0;
                        this.restartAttempts = 0;
                        this.backoffDelay = 1000; // Reset to 1 second
                        
                        // Trigger callback for live display
                        if (this.onTranscript) {
                            this.onTranscript(transcript, confidence);
                        }
                    }
                }
            }
        };

        this.speechRecognition.onerror = (event) => {
            const now = Date.now();
            console.error('🎤 ❌ Speech recognition error:', event.error);
            console.error('🎤 Error details:', event);
            
            // Prevent error spam
            if (now - lastErrorAt < 1000) {
                console.log('🎤 Suppressing duplicate error (within 1 second)');
                return;
            }
            lastErrorAt = now;
            
            // Track consecutive errors (but not for 'no-speech' which is normal)
            if (event.error !== 'no-speech' && event.error !== 'aborted') {
                this.consecutiveErrors++;
                console.warn(`🎤 Consecutive errors: ${this.consecutiveErrors}/${this.maxRestartAttempts}`);
            }
            
            if (this.onError) {
                this.onError(new Error(`Speech recognition error: ${event.error}`));
            }
            
            // Handle specific error types
            switch (event.error) {
                case 'network':
                    console.error('🎤 Network error - check internet connection');
                    break;
                case 'not-allowed':
                    console.error('🎤 Microphone access denied');
                    this.isSpeechEnabled = false; // Stop trying to restart
                    break;
                case 'no-speech':
                    console.log('🎤 No speech detected - this is normal');
                    break;
                case 'audio-capture':
                    console.error('🎤 Audio capture error - check microphone');
                    break;
                case 'service-not-allowed':
                    console.error('🎤 Speech service not allowed');
                    this.isSpeechEnabled = false; // Stop trying to restart
                    break;
                default:
                    console.error(`🎤 Unknown error: ${event.error}`);
            }
        };

        this.speechRecognition.onstart = () => {
            console.log('🎤 ✅ Speech recognition started');
            this.isActive = true;
            this.lastSuccessfulStartTime = Date.now();
            
            // Reset restart attempts on successful start (but keep consecutiveErrors for pattern tracking)
            this.restartAttempts = 0;
            
            if (this.onStatusChange) {
                this.onStatusChange('started');
            }
        };

        this.speechRecognition.onend = () => {
            console.log('🎤 Speech recognition ended');
            this.isActive = false;
            if (this.onStatusChange) {
                this.onStatusChange('ended');
            }
            
            // Auto-restart if speech is still enabled
            if (this.isSpeechEnabled) {
                // Check if we've exceeded maximum consecutive errors
                if (this.consecutiveErrors >= this.maxRestartAttempts) {
                    console.error(`🎤 ❌ Maximum consecutive errors reached (${this.consecutiveErrors})`);
                    console.error('🎤 ❌ Stopping auto-restart to prevent infinite loop');
                    console.error('🎤 ℹ️ User can manually restart speech recognition if needed');
                    this.isSpeechEnabled = false;
                    
                    // Notify via callback
                    if (this.onError) {
                        this.onError(new Error('Speech recognition failed after maximum retry attempts'));
                    }
                    
                    return;
                }
                
                // Increment restart attempt counter
                this.restartAttempts++;
                
                // Calculate exponential backoff delay
                const currentDelay = Math.min(
                    this.backoffDelay * Math.pow(2, this.restartAttempts - 1),
                    this.maxBackoffDelay
                );
                
                console.log(`🎤 Auto-restarting speech recognition in ${currentDelay}ms (attempt ${this.restartAttempts})`);
                console.log(`🎤 Consecutive errors: ${this.consecutiveErrors}/${this.maxRestartAttempts}`);
                
                setTimeout(() => {
                    if (this.isSpeechEnabled) {
                        try {
                            this.start();
                        } catch (error) {
                            console.error('🎤 ❌ Failed to restart:', error);
                            this.consecutiveErrors++;
                        }
                    }
                }, currentDelay);
            }
        };
    }

    /**
     * Start speech recognition
     */
    async start(mediaStream = null) {
        if (!this.speechRecognition) {
            throw new Error('Browser speech recognition not initialized. Call initialize() first.');
        }

        console.log('🎤 === Starting Browser Speech Recognition ===');

        // Check if speech recognition is supported and available
        if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
            console.error('🎤 ❌ Speech recognition not supported in this browser');
            throw new Error('Speech recognition not supported in this browser');
        }

        // Log microphone access info if mediaStream provided
        if (mediaStream) {
            const audioTracks = mediaStream.getAudioTracks();
            console.log(`🎤 Audio tracks available: ${audioTracks.length}`);
            audioTracks.forEach((track, index) => {
                console.log(`  - Track ${index}: ${track.label} (enabled: ${track.enabled}, muted: ${track.muted})`);
            });
        } else {
            console.warn('🎤 ⚠️ No media stream provided - microphone may not be accessible');
        }

        // Check current ready state before starting
        if (this.speechRecognition.readyState === 1) {
            console.warn('🎤 ⚠️ Speech recognition already running');
            return;
        }

        try {
            this.isSpeechEnabled = true;
            this.speechBuffer = [];
            this.speechChunkStartedAt = Date.now();
            
            console.log('🎤 Starting speech recognition...');
            this.speechRecognition.start();
            
            // Set up periodic transcript upload (every 10 seconds)
            this.speechUploadInterval = setInterval(() => {
                this.uploadTranscriptChunk();
            }, 10000);
            
            console.log('🎤 ✅ Browser speech recognition started successfully');
            
        } catch (error) {
            console.error('🎤 ❌ Failed to start browser speech recognition:', error);
            this.isSpeechEnabled = false;
            throw error;
        }
    }

    /**
     * Stop speech recognition
     */
    async stop() {
        console.log('🎤 === Stopping Browser Speech Recognition ===');
        
        this.isSpeechEnabled = false;
        
        // Clear upload interval
        if (this.speechUploadInterval) {
            clearInterval(this.speechUploadInterval);
            this.speechUploadInterval = null;
        }
        
        // Stop speech recognition
        if (this.speechRecognition) {
            try {
                this.speechRecognition.stop();
                console.log('🎤 ✅ Browser speech recognition stopped');
            } catch (error) {
                console.warn('🎤 Error stopping browser speech recognition:', error);
            }
        }
        
        // Upload any remaining buffer
        await this.uploadTranscriptChunk();
        
        // Reset error counters on manual stop (user initiated, not error-driven)
        this.consecutiveErrors = 0;
        this.restartAttempts = 0;
        this.backoffDelay = 1000;
        
        console.log('🎤 ✅ Browser speech recognition stopped and error counters reset');
    }

    /**
     * Restart speech recognition (manual restart resets error counters)
     */
    async restart() {
        console.log('🎤 === Restarting Browser Speech Recognition ===');
        
        try {
            await this.stop();
            
            // Reset error counters for manual restart (stop already does this, but being explicit)
            this.resetErrorCounters();
            
            // Wait a moment before restarting
            await new Promise(resolve => setTimeout(resolve, 1000));
            
            await this.start();
            
            console.log('🎤 ✅ Speech recognition restarted successfully');
            
        } catch (error) {
            console.error('🎤 ❌ Failed to restart browser speech recognition:', error);
            if (this.onError) {
                this.onError(error);
            }
        }
    }

    /**
     * Upload transcript chunk using the common uploader
     */
    async uploadTranscriptChunk() {
        if (!this.speechBuffer.length) {
            return;
        }

        const success = await this.transcriptUploader.uploadTranscriptChunk({
            speechBuffer: this.speechBuffer,
            chunkStartedAt: this.speechChunkStartedAt,
            provider: 'browser',
            language: this.speechRecognition?.lang
        });

        if (success) {
            // Reset buffer for next chunk
            this.speechBuffer = [];
            this.speechChunkStartedAt = Date.now();
        }
    }

    /**
     * Set event callbacks
     */
    setCallbacks({ onTranscript, onError, onStatusChange }) {
        this.onTranscript = onTranscript;
        this.onError = onError;
        this.onStatusChange = onStatusChange;
    }

    /**
     * Check if browser speech recognition is currently active
     */
    isRunning() {
        return this.isActive && this.isSpeechEnabled;
    }

    /**
     * Get current speech buffer
     */
    getSpeechBuffer() {
        return [...this.speechBuffer];
    }

    /**
     * Get speech recognition language
     */
    getLanguage() {
        return this.speechRecognition?.lang || this.roomData.stt_lang || 'en-GB';
    }

    /**
     * Get the speech recognition start time for timing calculations
     */
    getStartTime() {
        return this.speechChunkStartedAt;
    }

    /**
     * Manually reset error counters (useful after resolving underlying issues)
     */
    resetErrorCounters() {
        console.log('🎤 Manually resetting error counters');
        this.consecutiveErrors = 0;
        this.restartAttempts = 0;
        this.backoffDelay = 1000;
        console.log('🎤 ✅ Error counters reset - ready to restart if needed');
    }

    /**
     * Get current error state for debugging
     */
    getErrorState() {
        return {
            consecutiveErrors: this.consecutiveErrors,
            restartAttempts: this.restartAttempts,
            currentBackoffDelay: this.backoffDelay * Math.pow(2, this.restartAttempts),
            maxRestartAttempts: this.maxRestartAttempts,
            isSpeechEnabled: this.isSpeechEnabled,
            isActive: this.isActive,
            lastSuccessfulStartTime: this.lastSuccessfulStartTime
        };
    }

    /**
     * Check if browser supports speech recognition
     */
    static isSupported() {
        return 'webkitSpeechRecognition' in window || 'SpeechRecognition' in window;
    }
}
