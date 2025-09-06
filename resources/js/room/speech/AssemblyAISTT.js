/**
 * AssemblyAISTT - AssemblyAI streaming transcription implementation
 * 
 * Handles AssemblyAI streaming speech recognition with token management,
 * real-time processing, and robust error handling.
 */
export class AssemblyAISTT {
    constructor(speechManager) {
        this.speechManager = speechManager;
        this.transcriber = null;
        this.isListening = false;
        this.isInitialized = false;
        this.audioContext = null;
        this.mediaStreamSource = null;
        this.processorNode = null;
        
        // Configuration
        this.config = {
            sampleRate: 16000,
            encoding: 'pcm_s16le',
            channels: 1
        };
        
        // Token management
        this.token = null;
        this.tokenExpiry = null;
        this.tokenRefreshBuffer = 60000; // Refresh 1 minute before expiry
        
        // Connection management
        this.connectionAttempts = 0;
        this.maxConnectionAttempts = 3;
        this.reconnectDelay = 2000;
        this.isConnecting = false;
        
        // Audio processing
        this.audioBuffer = [];
        this.bufferSize = 4096;
        
        this.checkAssemblyAIAvailability();
    }

    /**
     * Checks if AssemblyAI SDK is available
     */
    checkAssemblyAIAvailability() {
        this.isSDKAvailable = typeof window.assemblyai !== 'undefined';
        
        console.log('🎤 AssemblyAI availability:', {
            sdkAvailable: this.isSDKAvailable,
            streamingClient: this.isSDKAvailable ? typeof window.assemblyai.StreamingClient : 'undefined'
        });
        
        if (!this.isSDKAvailable) {
            console.warn('🎤 ⚠️ AssemblyAI SDK not loaded');
        }
    }

    /**
     * Initializes AssemblyAI speech recognition
     */
    async initialize(sttConfig) {
        if (this.isInitialized) {
            console.warn('🎤 AssemblyAI STT already initialized');
            return;
        }

        console.log('🎤 === AssemblyAI Speech Recognition Initialization ===');

        if (!this.isSDKAvailable) {
            throw new Error('AssemblyAI SDK not available');
        }

        try {
            // Get authentication token
            await this.getAuthToken();
            
            // Initialize audio context
            await this.initializeAudioContext();
            
            // Create transcriber
            await this.createTranscriber();
            
            this.isInitialized = true;
            console.log('🎤 ✅ AssemblyAI speech recognition initialized');
        } catch (error) {
            console.error('🎤 ❌ Failed to initialize AssemblyAI STT:', error);
            throw error;
        }
    }

    /**
     * Gets authentication token from server
     */
    async getAuthToken() {
        console.log('🎤 Getting AssemblyAI authentication token...');
        
        try {
            const response = await fetch('/api/assemblyai/token', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (!response.ok) {
                throw new Error(`Failed to get AssemblyAI token: ${response.status}`);
            }

            const data = await response.json();
            this.token = data.token;
            
            // Calculate expiry time (token expires in seconds, convert to milliseconds)
            this.tokenExpiry = Date.now() + (data.expires_in_seconds * 1000);
            
            console.log('🎤 ✅ AssemblyAI token obtained');
            console.log(`🎤 Token expires in ${data.expires_in_seconds} seconds`);
        } catch (error) {
            console.error('🎤 ❌ Failed to get AssemblyAI token:', error);
            throw error;
        }
    }

    /**
     * Checks if token needs refresh
     */
    needsTokenRefresh() {
        if (!this.token || !this.tokenExpiry) {
            return true;
        }
        
        return Date.now() > (this.tokenExpiry - this.tokenRefreshBuffer);
    }

    /**
     * Refreshes authentication token if needed
     */
    async refreshTokenIfNeeded() {
        if (this.needsTokenRefresh()) {
            console.log('🎤 🔄 Refreshing AssemblyAI token...');
            await this.getAuthToken();
        }
    }

    /**
     * Initializes Web Audio API context
     */
    async initializeAudioContext() {
        console.log('🎤 Initializing audio context for AssemblyAI...');
        
        try {
            // Create audio context
            this.audioContext = new (window.AudioContext || window.webkitAudioContext)({
                sampleRate: this.config.sampleRate
            });
            
            console.log(`🎤 ✅ Audio context created (sample rate: ${this.audioContext.sampleRate})`);
        } catch (error) {
            console.error('🎤 ❌ Failed to create audio context:', error);
            throw error;
        }
    }

    /**
     * Creates AssemblyAI transcriber
     */
    async createTranscriber() {
        console.log('🎤 Creating AssemblyAI transcriber...');
        
        try {
            // Create streaming client
            const client = new window.assemblyai.StreamingClient({
                token: this.token,
                sampleRate: this.config.sampleRate,
                encoding: this.config.encoding
            });
            
            // Create transcriber
            this.transcriber = client.transcriber({
                realtimeTranscription: true,
                partialTranscripts: true
            });
            
            // Set up event handlers
            this.setupTranscriberHandlers();
            
            console.log('🎤 ✅ AssemblyAI transcriber created');
        } catch (error) {
            console.error('🎤 ❌ Failed to create AssemblyAI transcriber:', error);
            throw error;
        }
    }

    /**
     * Sets up transcriber event handlers
     */
    setupTranscriberHandlers() {
        // Transcript events
        this.transcriber.on('transcript', (transcript) => {
            this.handleTranscript(transcript);
        });

        // Partial transcript events
        this.transcriber.on('partial-transcript', (transcript) => {
            this.handlePartialTranscript(transcript);
        });

        // Error events
        this.transcriber.on('error', (error) => {
            this.handleTranscriberError(error);
        });

        // Connection events
        this.transcriber.on('open', () => {
            console.log('🎤 ✅ AssemblyAI connection opened');
            this.isConnecting = false;
            this.connectionAttempts = 0;
        });

        this.transcriber.on('close', (event) => {
            console.log('🎤 AssemblyAI connection closed:', event);
            this.handleConnectionClose(event);
        });

        console.log('🎤 ✅ AssemblyAI transcriber event handlers set up');
    }

    /**
     * Starts listening for speech
     */
    async startListening() {
        if (!this.isInitialized) {
            throw new Error('AssemblyAI STT not initialized');
        }

        if (this.isListening) {
            console.warn('🎤 Already listening');
            return;
        }

        console.log('🎤 === Starting AssemblyAI Speech Recognition ===');

        try {
            // Refresh token if needed
            await this.refreshTokenIfNeeded();
            
            // Get user media
            const stream = await this.getUserMedia();
            
            // Set up audio processing
            await this.setupAudioProcessing(stream);
            
            // Connect to AssemblyAI
            await this.connectToAssemblyAI();
            
            this.isListening = true;
            console.log('🎤 ✅ AssemblyAI speech recognition started');
        } catch (error) {
            console.error('🎤 ❌ Failed to start AssemblyAI speech recognition:', error);
            throw error;
        }
    }

    /**
     * Gets user media stream
     */
    async getUserMedia() {
        console.log('🎤 Getting user media for AssemblyAI...');
        
        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                audio: {
                    sampleRate: this.config.sampleRate,
                    channelCount: this.config.channels,
                    echoCancellation: true,
                    noiseSuppression: true,
                    autoGainControl: true
                }
            });
            
            console.log('🎤 ✅ User media obtained');
            
            // Log audio tracks
            const audioTracks = stream.getAudioTracks();
            console.log(`🎤 Audio tracks available: ${audioTracks.length}`);
            audioTracks.forEach((track, index) => {
                console.log(`- Track ${index}: ${track.label} (enabled: ${track.enabled}, muted: ${track.muted})`);
            });
            
            return stream;
        } catch (error) {
            console.error('🎤 ❌ Failed to get user media:', error);
            throw error;
        }
    }

    /**
     * Sets up audio processing pipeline
     */
    async setupAudioProcessing(stream) {
        console.log('🎤 Setting up audio processing pipeline...');
        
        try {
            // Create media stream source
            this.mediaStreamSource = this.audioContext.createMediaStreamSource(stream);
            
            // Create script processor node
            this.processorNode = this.audioContext.createScriptProcessor(this.bufferSize, 1, 1);
            
            // Set up audio processing
            this.processorNode.onaudioprocess = (event) => {
                this.processAudioData(event);
            };
            
            // Connect audio nodes
            this.mediaStreamSource.connect(this.processorNode);
            this.processorNode.connect(this.audioContext.destination);
            
            console.log('🎤 ✅ Audio processing pipeline set up');
        } catch (error) {
            console.error('🎤 ❌ Failed to set up audio processing:', error);
            throw error;
        }
    }

    /**
     * Processes audio data and sends to AssemblyAI
     */
    processAudioData(event) {
        const inputBuffer = event.inputBuffer.getChannelData(0);
        
        // Convert float32 to int16
        const int16Buffer = new Int16Array(inputBuffer.length);
        for (let i = 0; i < inputBuffer.length; i++) {
            int16Buffer[i] = Math.max(-32768, Math.min(32767, inputBuffer[i] * 32768));
        }
        
        // Send to AssemblyAI
        if (this.transcriber && this.isListening) {
            try {
                this.transcriber.stream(int16Buffer.buffer);
            } catch (error) {
                console.error('🎤 ❌ Failed to stream audio data:', error);
            }
        }
    }

    /**
     * Connects to AssemblyAI streaming service
     */
    async connectToAssemblyAI() {
        if (this.isConnecting) {
            console.warn('🎤 Already connecting to AssemblyAI');
            return;
        }

        console.log('🎤 Connecting to AssemblyAI streaming service...');
        this.isConnecting = true;

        try {
            await this.transcriber.connect();
            console.log('🎤 ✅ Connected to AssemblyAI');
        } catch (error) {
            this.isConnecting = false;
            console.error('🎤 ❌ Failed to connect to AssemblyAI:', error);
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

        console.log('🎤 Stopping AssemblyAI speech recognition...');

        try {
            this.isListening = false;
            
            // Disconnect from AssemblyAI
            if (this.transcriber) {
                await this.transcriber.close();
            }
            
            // Clean up audio processing
            this.cleanupAudioProcessing();
            
            console.log('🎤 ✅ AssemblyAI speech recognition stopped');
        } catch (error) {
            console.error('🎤 ❌ Failed to stop AssemblyAI speech recognition:', error);
            throw error;
        }
    }

    /**
     * Cleans up audio processing resources
     */
    cleanupAudioProcessing() {
        console.log('🎤 Cleaning up audio processing...');
        
        try {
            if (this.processorNode) {
                this.processorNode.disconnect();
                this.processorNode.onaudioprocess = null;
                this.processorNode = null;
            }
            
            if (this.mediaStreamSource) {
                this.mediaStreamSource.disconnect();
                this.mediaStreamSource = null;
            }
            
            console.log('🎤 ✅ Audio processing cleaned up');
        } catch (error) {
            console.error('🎤 ❌ Error cleaning up audio processing:', error);
        }
    }

    /**
     * Handles transcript from AssemblyAI
     */
    handleTranscript(transcript) {
        if (transcript.text && transcript.text.trim()) {
            console.log('🎤 📝 AssemblyAI final transcript:', transcript.text);
            
            // Send to speech manager
            this.speechManager.handleTranscript(
                transcript.text.trim(),
                true, // isFinal
                transcript.confidence
            );
        }
    }

    /**
     * Handles partial transcript from AssemblyAI
     */
    handlePartialTranscript(transcript) {
        if (transcript.text && transcript.text.trim()) {
            console.log('🎤 📝 AssemblyAI partial transcript:', transcript.text);
            
            // Send to speech manager
            this.speechManager.handleTranscript(
                transcript.text.trim(),
                false, // isFinal
                transcript.confidence
            );
        }
    }

    /**
     * Handles transcriber errors
     */
    handleTranscriberError(error) {
        console.error('🎤 ❌ AssemblyAI transcriber error:', error);
        
        // Notify speech manager
        this.speechManager.notifyError(error);
        
        // Attempt reconnection if appropriate
        if (this.shouldAttemptReconnection(error)) {
            this.attemptReconnection();
        }
    }

    /**
     * Handles connection close events
     */
    handleConnectionClose(event) {
        console.log('🎤 AssemblyAI connection closed:', event);
        
        if (this.isListening && this.shouldAttemptReconnection()) {
            this.attemptReconnection();
        }
    }

    /**
     * Determines if reconnection should be attempted
     */
    shouldAttemptReconnection(error = null) {
        if (this.connectionAttempts >= this.maxConnectionAttempts) {
            console.warn('🎤 Max reconnection attempts reached');
            return false;
        }
        
        if (error && error.code === 'UNAUTHORIZED') {
            console.warn('🎤 Unauthorized error - token may be expired');
            return true; // Try to refresh token and reconnect
        }
        
        return this.isListening;
    }

    /**
     * Attempts to reconnect to AssemblyAI
     */
    async attemptReconnection() {
        this.connectionAttempts++;
        console.log(`🎤 🔄 Attempting AssemblyAI reconnection (${this.connectionAttempts}/${this.maxConnectionAttempts})`);
        
        try {
            // Wait before reconnecting
            await new Promise(resolve => setTimeout(resolve, this.reconnectDelay));
            
            // Refresh token
            await this.refreshTokenIfNeeded();
            
            // Recreate transcriber
            await this.createTranscriber();
            
            // Reconnect
            await this.connectToAssemblyAI();
            
            console.log('🎤 ✅ AssemblyAI reconnection successful');
        } catch (error) {
            console.error('🎤 ❌ AssemblyAI reconnection failed:', error);
            
            if (this.connectionAttempts < this.maxConnectionAttempts) {
                // Try again
                this.attemptReconnection();
            } else {
                // Give up and notify
                console.error('🎤 💀 AssemblyAI reconnection failed permanently');
                this.speechManager.notifyError(new Error('AssemblyAI connection failed after multiple attempts'));
            }
        }
    }

    /**
     * Gets current status
     */
    getStatus() {
        return {
            isSDKAvailable: this.isSDKAvailable,
            isInitialized: this.isInitialized,
            isListening: this.isListening,
            isConnecting: this.isConnecting,
            hasToken: !!this.token,
            tokenExpiry: this.tokenExpiry,
            connectionAttempts: this.connectionAttempts,
            audioContextState: this.audioContext?.state,
            config: { ...this.config }
        };
    }

    /**
     * Destroys the AssemblyAI STT
     */
    destroy() {
        console.log('🎤 Destroying AssemblyAISTT');
        
        // Stop listening
        if (this.isListening) {
            this.stopListening();
        }
        
        // Clean up audio processing
        this.cleanupAudioProcessing();
        
        // Close audio context
        if (this.audioContext) {
            this.audioContext.close();
            this.audioContext = null;
        }
        
        // Clean up transcriber
        if (this.transcriber) {
            this.transcriber.removeAllListeners();
            this.transcriber = null;
        }
        
        // Reset state
        this.isInitialized = false;
        this.isListening = false;
        this.isConnecting = false;
        this.connectionAttempts = 0;
        this.token = null;
        this.tokenExpiry = null;
        
        console.log('🎤 AssemblyAISTT destroyed');
    }
}
