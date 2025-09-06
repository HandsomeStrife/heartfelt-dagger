/**
 * SpeechManager - Speech recognition orchestrator
 * 
 * Manages speech recognition providers, handles provider switching,
 * coordinates transcription state, and provides a unified interface
 * for both browser and AssemblyAI speech recognition.
 */
export class SpeechManager {
    constructor(roomWebRTC) {
        this.roomWebRTC = roomWebRTC;
        this.currentProvider = null;
        this.browserSTT = null;
        this.assemblyAISTT = null;
        this.isInitialized = false;
        this.isListening = false;
        this.transcriptionBuffer = [];
        this.lastTranscriptTime = null;
        
        // Speech configuration
        this.sttConfig = null;
        this.supportedProviders = ['browser', 'assemblyai'];
        this.fallbackProvider = 'browser';
        
        // Event handlers
        this.onTranscriptHandlers = [];
        this.onErrorHandlers = [];
        this.onStatusChangeHandlers = [];
        
        this.setupSpeechCapabilities();
    }

    /**
     * Sets up speech recognition capabilities
     */
    setupSpeechCapabilities() {
        console.log('🎤 Setting up speech recognition capabilities...');
        
        // Check browser speech support
        const hasBrowserSupport = 'webkitSpeechRecognition' in window || 'SpeechRecognition' in window;
        
        // Check AssemblyAI support (requires API key and network)
        const hasAssemblyAISupport = typeof window.assemblyai !== 'undefined';
        
        console.log('🎤 Speech capabilities:', {
            browserSupport: hasBrowserSupport,
            assemblyAISupport: hasAssemblyAISupport,
            supportedProviders: this.supportedProviders
        });
    }

    /**
     * Initializes speech recognition
     */
    async initializeSpeechRecognition() {
        if (this.isInitialized) {
            console.warn('🎤 Speech recognition already initialized');
            return;
        }

        console.log('🎤 === Speech Recognition Initialization Starting ===');

        try {
            // Get STT configuration
            this.sttConfig = await this.getSttConfig();
            
            if (!this.sttConfig.stt_enabled) {
                console.log('🎤 STT disabled for this room');
                return;
            }

            console.log(`🎤 ✅ STT enabled for this room`);
            console.log(`🎤 STT Provider: ${this.sttConfig.stt_provider}`);

            // Initialize the appropriate provider
            await this.initializeProvider(this.sttConfig.stt_provider);
            
            this.isInitialized = true;
            this.notifyStatusChange('initialized');
            
            console.log('🎤 === Speech Recognition Initialization Complete ===');
        } catch (error) {
            console.error('🎤 Failed to initialize speech recognition:', error);
            await this.handleInitializationError(error);
        }
    }

    /**
     * Gets STT configuration from the server
     */
    async getSttConfig() {
        try {
            const response = await fetch(`/api/rooms/${this.roomWebRTC.roomData.id}/stt-config`);
            if (!response.ok) {
                throw new Error(`Failed to get STT config: ${response.status}`);
            }
            const config = await response.json();
            console.log('🎤 ✅ STT config retrieved', config);
            return config;
        } catch (error) {
            console.error('🎤 Failed to get STT config:', error);
            throw error;
        }
    }

    /**
     * Initializes the specified speech provider
     */
    async initializeProvider(provider) {
        console.log(`🎤 Initializing ${provider} speech recognition...`);
        
        switch (provider) {
            case 'assemblyai':
                await this.initializeAssemblyAI();
                break;
            case 'browser':
            default:
                await this.initializeBrowser();
                break;
        }
        
        this.currentProvider = provider;
        console.log(`🎤 ✅ ${provider} speech recognition initialized`);
    }

    /**
     * Initializes AssemblyAI speech recognition
     */
    async initializeAssemblyAI() {
        console.log('🎤 === AssemblyAI Speech Recognition Initialization ===');
        
        try {
            // Import AssemblyAI STT component
            const { AssemblyAISTT } = await import('./AssemblyAISTT.js');
            this.assemblyAISTT = new AssemblyAISTT(this);
            
            // Initialize AssemblyAI
            await this.assemblyAISTT.initialize(this.sttConfig);
            
            console.log('🎤 ✅ AssemblyAI speech recognition ready');
        } catch (error) {
            console.error('🎤 ❌ Failed to initialize AssemblyAI:', error);
            throw error;
        }
    }

    /**
     * Initializes browser speech recognition
     */
    async initializeBrowser() {
        console.log('🎤 === Browser Speech Recognition Initialization ===');
        
        try {
            // Import Browser STT component
            const { BrowserSTT } = await import('./BrowserSTT.js');
            this.browserSTT = new BrowserSTT(this);
            
            // Initialize browser STT
            await this.browserSTT.initialize(this.sttConfig);
            
            console.log('🎤 ✅ Browser speech recognition ready');
        } catch (error) {
            console.error('🎤 ❌ Failed to initialize browser STT:', error);
            throw error;
        }
    }

    /**
     * Handles initialization errors with fallback
     */
    async handleInitializationError(error) {
        console.error('🎤 Speech initialization error:', error);
        
        // Try fallback provider if current provider failed
        if (this.sttConfig?.stt_provider !== this.fallbackProvider) {
            console.log(`🎤 Falling back to ${this.fallbackProvider} speech recognition`);
            
            try {
                await this.initializeProvider(this.fallbackProvider);
                this.isInitialized = true;
                this.notifyStatusChange('initialized_fallback');
                console.log('🎤 ✅ Fallback speech recognition initialized');
            } catch (fallbackError) {
                console.error('🎤 ❌ Fallback initialization also failed:', fallbackError);
                this.notifyError(fallbackError);
            }
        } else {
            this.notifyError(error);
        }
    }

    /**
     * Starts speech recognition
     */
    async startSpeechRecognition() {
        if (!this.isInitialized) {
            console.warn('🎤 Speech recognition not initialized');
            return false;
        }

        if (this.isListening) {
            console.warn('🎤 Speech recognition already listening');
            return true;
        }

        console.log('🎤 === Starting Speech Recognition ===');

        try {
            const provider = this.getCurrentProvider();
            if (!provider) {
                throw new Error('No speech provider available');
            }

            await provider.startListening();
            this.isListening = true;
            this.notifyStatusChange('listening');
            
            console.log('🎤 ✅ Speech recognition started');
            return true;
        } catch (error) {
            console.error('🎤 ❌ Failed to start speech recognition:', error);
            this.notifyError(error);
            return false;
        }
    }

    /**
     * Stops speech recognition
     */
    async stopSpeechRecognition() {
        if (!this.isListening) {
            console.warn('🎤 Speech recognition not listening');
            return true;
        }

        console.log('🎤 Stopping speech recognition...');

        try {
            const provider = this.getCurrentProvider();
            if (provider) {
                await provider.stopListening();
            }
            
            this.isListening = false;
            this.notifyStatusChange('stopped');
            
            console.log('🎤 ✅ Speech recognition stopped');
            return true;
        } catch (error) {
            console.error('🎤 ❌ Failed to stop speech recognition:', error);
            this.notifyError(error);
            return false;
        }
    }

    /**
     * Restarts speech recognition
     */
    async restartSpeechRecognition() {
        console.log('🎤 Restarting speech recognition...');
        
        await this.stopSpeechRecognition();
        
        // Wait a moment before restarting
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        return await this.startSpeechRecognition();
    }

    /**
     * Gets the current active provider
     */
    getCurrentProvider() {
        switch (this.currentProvider) {
            case 'assemblyai':
                return this.assemblyAISTT;
            case 'browser':
                return this.browserSTT;
            default:
                return null;
        }
    }

    /**
     * Handles transcript from providers
     */
    handleTranscript(transcript, isFinal = false, confidence = null) {
        const transcriptData = {
            text: transcript,
            isFinal: isFinal,
            confidence: confidence,
            provider: this.currentProvider,
            timestamp: Date.now()
        };
        
        console.log('🎤 📝 Transcript:', transcriptData);
        
        // Add to buffer
        if (isFinal) {
            this.transcriptionBuffer.push(transcriptData);
            this.lastTranscriptTime = Date.now();
        }
        
        // Notify handlers
        this.notifyTranscript(transcriptData);
        
        // Update UI
        this.updateTranscriptUI(transcriptData);
    }

    /**
     * Updates transcript UI
     */
    updateTranscriptUI(transcriptData) {
        // Find transcript display elements
        const transcriptElements = document.querySelectorAll('.transcript-display, [data-transcript-display]');
        
        transcriptElements.forEach(element => {
            if (transcriptData.isFinal) {
                // Add final transcript
                const transcriptLine = document.createElement('div');
                transcriptLine.className = 'transcript-line final';
                transcriptLine.textContent = transcriptData.text;
                element.appendChild(transcriptLine);
                
                // Scroll to bottom
                element.scrollTop = element.scrollHeight;
            } else {
                // Update interim transcript
                let interimElement = element.querySelector('.transcript-line.interim');
                if (!interimElement) {
                    interimElement = document.createElement('div');
                    interimElement.className = 'transcript-line interim';
                    element.appendChild(interimElement);
                }
                interimElement.textContent = transcriptData.text;
            }
        });
    }

    /**
     * Gets transcription history
     */
    getTranscriptionHistory() {
        return {
            buffer: [...this.transcriptionBuffer],
            totalTranscripts: this.transcriptionBuffer.length,
            lastTranscriptTime: this.lastTranscriptTime,
            currentProvider: this.currentProvider,
            isListening: this.isListening
        };
    }

    /**
     * Clears transcription buffer
     */
    clearTranscriptionBuffer() {
        console.log('🎤 Clearing transcription buffer');
        this.transcriptionBuffer = [];
        this.lastTranscriptTime = null;
        
        // Clear UI
        const transcriptElements = document.querySelectorAll('.transcript-display, [data-transcript-display]');
        transcriptElements.forEach(element => {
            element.innerHTML = '';
        });
    }

    /**
     * Gets speech recognition status
     */
    getStatus() {
        return {
            isInitialized: this.isInitialized,
            isListening: this.isListening,
            currentProvider: this.currentProvider,
            sttEnabled: this.sttConfig?.stt_enabled || false,
            transcriptCount: this.transcriptionBuffer.length,
            lastTranscriptTime: this.lastTranscriptTime,
            supportedProviders: this.supportedProviders
        };
    }

    /**
     * Event handler registration
     */
    onTranscript(handler) {
        this.onTranscriptHandlers.push(handler);
    }

    onError(handler) {
        this.onErrorHandlers.push(handler);
    }

    onStatusChange(handler) {
        this.onStatusChangeHandlers.push(handler);
    }

    /**
     * Event notifications
     */
    notifyTranscript(transcriptData) {
        this.onTranscriptHandlers.forEach(handler => {
            try {
                handler(transcriptData);
            } catch (error) {
                console.error('🎤 Error in transcript handler:', error);
            }
        });
    }

    notifyError(error) {
        this.onErrorHandlers.forEach(handler => {
            try {
                handler(error);
            } catch (handlerError) {
                console.error('🎤 Error in error handler:', handlerError);
            }
        });
    }

    notifyStatusChange(status) {
        this.onStatusChangeHandlers.forEach(handler => {
            try {
                handler(status);
            } catch (error) {
                console.error('🎤 Error in status change handler:', error);
            }
        });
    }

    /**
     * Runs speech diagnostics
     */
    async runDiagnostics() {
        console.log('🎤 === Running Speech Recognition Diagnostics ===');
        
        try {
            const diagnostics = await this.roomWebRTC.diagnosticsRunner.runSpeechDiagnostics();
            console.log('🎤 Diagnostics completed:', diagnostics);
            return diagnostics;
        } catch (error) {
            console.error('🎤 Diagnostics failed:', error);
            return { error: error.message };
        }
    }

    /**
     * Destroys the speech manager
     */
    destroy() {
        console.log('🎤 Destroying SpeechManager');
        
        // Stop listening
        if (this.isListening) {
            this.stopSpeechRecognition();
        }
        
        // Destroy providers
        if (this.browserSTT) {
            this.browserSTT.destroy();
            this.browserSTT = null;
        }
        
        if (this.assemblyAISTT) {
            this.assemblyAISTT.destroy();
            this.assemblyAISTT = null;
        }
        
        // Clear handlers
        this.onTranscriptHandlers = [];
        this.onErrorHandlers = [];
        this.onStatusChangeHandlers = [];
        
        // Clear state
        this.isInitialized = false;
        this.isListening = false;
        this.currentProvider = null;
        this.transcriptionBuffer = [];
        
        console.log('🎤 SpeechManager destroyed');
    }
}
