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
                    break;
                case 'no-speech':
                    console.log('🎤 No speech detected - this is normal');
                    break;
                case 'audio-capture':
                    console.error('🎤 Audio capture error - check microphone');
                    break;
                case 'service-not-allowed':
                    console.error('🎤 Speech service not allowed');
                    break;
                default:
                    console.error(`🎤 Unknown error: ${event.error}`);
            }
        };

        this.speechRecognition.onstart = () => {
            console.log('🎤 ✅ Speech recognition started');
            this.isActive = true;
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
                console.log('🎤 Auto-restarting speech recognition...');
                setTimeout(() => {
                    if (this.isSpeechEnabled) {
                        this.start();
                    }
                }, 100);
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
        
        console.log('🎤 ✅ Browser speech recognition stopped');
    }

    /**
     * Restart speech recognition
     */
    async restart() {
        console.log('🎤 === Restarting Browser Speech Recognition ===');
        
        try {
            await this.stop();
            
            // Wait a moment before restarting
            await new Promise(resolve => setTimeout(resolve, 1000));
            
            await this.start();
            
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
     * Check if browser supports speech recognition
     */
    static isSupported() {
        return 'webkitSpeechRecognition' in window || 'SpeechRecognition' in window;
    }
}
