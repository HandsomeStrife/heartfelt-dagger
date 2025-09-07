/**
 * AssemblyAI Speech-to-Text Integration
 * 
 * Handles real-time speech transcription using AssemblyAI's streaming API
 * with proper browser-compatible implementation.
 */

import TranscriptUploader from './transcript-uploader.js';

export default class AssemblyAISpeechRecognition {
    constructor(roomData, currentUserId) {
        this.roomData = roomData;
        this.currentUserId = currentUserId;
        this.transcriber = null;
        this.isActive = false;
        this.speechBuffer = [];
        this.speechChunkStartedAt = null;
        this.speechUploadInterval = null;
        this.audioContext = null;
        this.audioProcessor = null;
        this.audioSource = null;
        
        // Create transcript uploader instance
        this.transcriptUploader = new TranscriptUploader(roomData, currentUserId);
        
        // Event callbacks
        this.onTranscript = null;
        this.onError = null;
        this.onStatusChange = null;
    }

    /**
     * Initialize AssemblyAI speech recognition
     */
    async initialize() {
        console.log('🎤 === AssemblyAI Speech Recognition Initialization ===');
        
        try {
            // Get STT configuration from the server
            const config = await this.getSTTConfig();
            
            if (config.provider !== 'assemblyai') {
                throw new Error(`Unexpected provider: ${config.provider}`);
            }

            // Get temporary token from our backend for security
            const token = await this.getAssemblyAIToken(config.config.api_key);
            console.log('🎤 ✅ AssemblyAI token received, length:', token ? token.length : 'null');
            
            // For browser environments, create StreamingTranscriber directly with token
            // Import the StreamingTranscriber class specifically
            const { StreamingTranscriber } = await import('assemblyai');
            
            this.transcriber = new StreamingTranscriber({
                token: token, // Use the temporary token directly
                sampleRate: 16000
            });

            // Set up event handlers
            this.setupEventHandlers();

            console.log('🎤 ✅ AssemblyAI speech recognition initialized');
            return true;

        } catch (error) {
            console.error('🎤 ❌ Failed to initialize AssemblyAI:', error);
            if (this.onError) {
                this.onError(error);
            }
            return false;
        }
    }

    /**
     * Set up event handlers for the transcriber
     */
    setupEventHandlers() {
        this.transcriber.on('open', ({ id, expires_at }) => {
            console.log(`🎤 ✅ AssemblyAI session opened with ID: ${id}, expires: ${expires_at}`);
            if (this.onStatusChange) {
                this.onStatusChange('connected', { sessionId: id, expiresAt: expires_at });
            }
        });

        this.transcriber.on('error', (error) => {
            console.error('🎤 ❌ AssemblyAI error:', error);
            console.error('🎤 Error type:', typeof error);
            console.error('🎤 Error details:', JSON.stringify(error, null, 2));
            
            if (this.onError) {
                this.onError(error);
            }
            
            // Try to restart after a delay
            setTimeout(() => {
                if (this.isActive) {
                    console.log('🎤 Attempting to restart AssemblyAI...');
                    this.restart();
                }
            }, 1000);
        });

        this.transcriber.on('close', (code, reason) => {
            console.log(`🎤 AssemblyAI session closed: ${code} - ${reason}`);
            if (this.onStatusChange) {
                this.onStatusChange('closed', { code, reason });
            }
        });

        this.transcriber.on("transcript", (transcript) => {
            console.log('🎤 AssemblyAI transcript received:', transcript);
            
            // For now, we'll primarily rely on the "turn" events for final transcripts
            // The "transcript" events might be for partial/interim results
            if (transcript.text && transcript.text.trim()) {
                console.log('🎤 📝 Interim transcript:', transcript.text);
                // We could use this for live display but not for saving to buffer
                // Only trigger callback for live display, don't save to buffer yet
                // if (this.onTranscript) {
                //     this.onTranscript(transcript.text, transcript.confidence || 1.0);
                // }
            }
        });

        this.transcriber.on("turn", (turn) => {
            if (!turn.transcript) {
                return;
            }
            
            console.log("🎤 AssemblyAI turn:", turn.transcript);
            console.log("🎤 Turn details - end_of_turn:", turn.end_of_turn, "turn_is_formatted:", turn.turn_is_formatted);
            
            // Only process complete, formatted turns to avoid duplicates
            if (turn.end_of_turn && turn.turn_is_formatted) {
                console.log("🎤 ✅ Complete turn received, processing:", turn.transcript);
                
                // Add to speech buffer
                this.speechBuffer.push({
                    text: turn.transcript,
                    confidence: turn.confidence || 1.0,
                    timestamp: Date.now()
                });

                // Trigger callback
                if (this.onTranscript) {
                    this.onTranscript(turn.transcript, turn.confidence || 1.0);
                }
            } else {
                console.log("🎤 ⏳ Partial turn, waiting for completion...");
            }
        });
    }

    /**
     * Start speech recognition
     */
    async start(mediaStream) {
        if (!this.transcriber) {
            throw new Error('AssemblyAI not initialized. Call initialize() first.');
        }

        if (this.isActive) {
            console.warn('🎤 ⚠️ AssemblyAI already active, skipping start');
            return;
        }

        if (!mediaStream) {
            throw new Error('Media stream is required for AssemblyAI speech recognition');
        }

        console.log('🎤 === Starting AssemblyAI Speech Recognition ===');

        try {
            this.isActive = true;
            this.speechBuffer = [];
            this.speechChunkStartedAt = Date.now();

            // Connect to AssemblyAI streaming service
            console.log("🎤 Connecting to streaming transcript service");
            await this.transcriber.connect();
            console.log('🎤 ✅ Connected to AssemblyAI');

            // Set up audio processing to stream to AssemblyAI
            console.log("🎤 Starting audio recording and streaming");
            await this.setupAudioStreaming(mediaStream);

            // Set up periodic transcript upload (every 10 seconds)
            this.speechUploadInterval = setInterval(() => {
                this.uploadTranscriptChunk();
            }, 10000);

            console.log('🎤 ✅ AssemblyAI speech recognition started successfully');

        } catch (error) {
            console.error('🎤 ❌ Failed to start AssemblyAI speech recognition:', error);
            this.isActive = false;
            throw error;
        }
    }

    /**
     * Set up audio streaming to AssemblyAI using Web Audio API
     */
    async setupAudioStreaming(mediaStream) {
        // Create audio context with the sample rate AssemblyAI expects
        this.audioContext = new (window.AudioContext || window.webkitAudioContext)({
            sampleRate: 16000
        });

        // Create media stream source
        this.audioSource = this.audioContext.createMediaStreamSource(mediaStream);

        // Create script processor for audio data (deprecated but still works)
        // Note: We'll use ScriptProcessorNode for now, but this should eventually be replaced with AudioWorklet
        this.audioProcessor = this.audioContext.createScriptProcessor(4096, 1, 1);

        // Process audio data and send to AssemblyAI
        this.audioProcessor.onaudioprocess = (event) => {
            if (!this.isActive || !this.transcriber) return;

            const inputBuffer = event.inputBuffer;
            const inputData = inputBuffer.getChannelData(0);

            // Convert Float32Array to Int16Array (PCM 16-bit) as AssemblyAI expects
            const pcmData = new Int16Array(inputData.length);
            for (let i = 0; i < inputData.length; i++) {
                // Clamp values to 16-bit signed integer range
                const sample = Math.max(-1, Math.min(1, inputData[i]));
                pcmData[i] = sample * 32767;
            }

            // Send PCM data to AssemblyAI transcriber
            try {
                // Create a readable stream and pipe it to the transcriber
                const uint8Array = new Uint8Array(pcmData.buffer);
                this.transcriber.sendAudio(uint8Array);
            } catch (error) {
                console.warn('🎤 Failed to send audio data to AssemblyAI:', error);
            }
        };

        // Connect the audio pipeline
        this.audioSource.connect(this.audioProcessor);
        this.audioProcessor.connect(this.audioContext.destination);

        console.log('🎤 ✅ Audio streaming pipeline established');
    }


    /**
     * Stop speech recognition
     */
    async stop() {
        if (!this.isActive) {
            return;
        }

        console.log('🎤 === Stopping AssemblyAI Speech Recognition ===');

        this.isActive = false;

        // Clear upload interval
        if (this.speechUploadInterval) {
            clearInterval(this.speechUploadInterval);
            this.speechUploadInterval = null;
        }

        // Clean up audio processing
        if (this.audioProcessor) {
            this.audioProcessor.disconnect();
            this.audioProcessor = null;
        }

        if (this.audioSource) {
            this.audioSource.disconnect();
            this.audioSource = null;
        }

        if (this.audioContext) {
            await this.audioContext.close();
            this.audioContext = null;
        }

        // Close AssemblyAI connection
        if (this.transcriber) {
            try {
                console.log("🎤 Closing streaming transcript connection");
                await this.transcriber.close();
                console.log('🎤 ✅ AssemblyAI connection closed');
            } catch (error) {
                console.warn('🎤 Error closing AssemblyAI connection:', error);
            }
        }

        // Upload any remaining buffer
        await this.uploadTranscriptChunk();

        console.log('🎤 ✅ AssemblyAI speech recognition stopped');
    }

    /**
     * Restart speech recognition
     */
    async restart() {
        console.log('🎤 === Restarting AssemblyAI Speech Recognition ===');
        
        try {
            // Stop current session
            await this.stop();
            
            // Wait a moment before restarting
            await new Promise(resolve => setTimeout(resolve, 1000));
            
            // Re-initialize
            await this.initialize();
            
        } catch (error) {
            console.error('🎤 ❌ Failed to restart AssemblyAI:', error);
            if (this.onError) {
                this.onError(error);
            }
        }
    }

    /**
     * Get STT configuration from the server
     */
    async getSTTConfig() {
        const response = await fetch(`/api/rooms/${this.roomData.id}/stt-config`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });

        if (!response.ok) {
            throw new Error(`Failed to get STT config: ${response.status} ${response.statusText}`);
        }

        return await response.json();
    }

    /**
     * Get a temporary AssemblyAI token from our backend
     */
    async getAssemblyAIToken(apiKey) {
        try {
            console.log('🎤 Requesting AssemblyAI token from backend...');
            const response = await fetch('/api/assemblyai/token', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ api_key: apiKey })
            });

            console.log('🎤 Token request response status:', response.status);

            if (!response.ok) {
                const errorText = await response.text();
                console.error('🎤 Token request failed:', errorText);
                throw new Error(`Failed to get AssemblyAI token: ${response.status} - ${errorText}`);
            }

            const data = await response.json();
            console.log('🎤 Token received successfully');
            return data.token;
        } catch (error) {
            console.error('🎤 ❌ Failed to get AssemblyAI token:', error);
            throw error;
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
            provider: 'assemblyai',
            language: this.roomData.stt_lang
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
     * Check if AssemblyAI is currently active
     */
    isRunning() {
        return this.isActive;
    }

    /**
     * Get current speech buffer
     */
    getSpeechBuffer() {
        return [...this.speechBuffer];
    }
}
