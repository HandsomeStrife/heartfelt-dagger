/**
 * Transcript Uploader - Common functionality for uploading transcripts to the backend
 * 
 * Handles uploading speech-to-text transcripts to the room_transcripts table
 * with proper character attribution and provider tracking.
 */

export default class TranscriptUploader {
    constructor(roomData, currentUserId) {
        this.roomData = roomData;
        this.currentUserId = currentUserId;
    }

    /**
     * Get current user's character information from room participants
     */
    getCurrentUserCharacterInfo() {
        if (!this.currentUserId || !this.roomData.participants) {
            return { character_id: null, character_name: null, character_class: null };
        }

        const participant = this.roomData.participants.find(p => p.user_id === this.currentUserId);
        if (!participant) {
            return { character_id: null, character_name: null, character_class: null };
        }

        return {
            character_id: participant.character_id || null,
            character_name: participant.character_name || null,
            character_class: participant.character_class || (participant.is_host ? 'GM' : null)
        };
    }

    /**
     * Upload transcript chunk to server
     * 
     * @param {Object} transcriptData - The transcript data to upload
     * @param {Array} transcriptData.speechBuffer - Array of speech items with text, confidence, timestamp
     * @param {number} transcriptData.chunkStartedAt - When this chunk started (ms timestamp)
     * @param {string} transcriptData.provider - STT provider ('browser' or 'assemblyai')
     * @param {string} [transcriptData.language] - Language code (optional, defaults to room setting)
     * @returns {Promise<boolean>} - True if upload successful, false otherwise
     */
    async uploadTranscriptChunk(transcriptData) {
        const { speechBuffer, chunkStartedAt, provider, language } = transcriptData;

        if (!speechBuffer || !speechBuffer.length || !this.currentUserId) {
            console.log('📤 No transcript data to upload');
            return false;
        }

        const chunkEndedAt = Date.now();
        const combinedText = speechBuffer.map(item => item.text).join(' ');
        const averageConfidence = speechBuffer.reduce((sum, item) => sum + (item.confidence || 0), 0) / speechBuffer.length;

        // Get character information for the current user
        const characterInfo = this.getCurrentUserCharacterInfo();

        const payload = {
            room_id: this.roomData.id,
            user_id: this.currentUserId,
            character_id: characterInfo.character_id,
            character_name: characterInfo.character_name,
            character_class: characterInfo.character_class,
            started_at_ms: chunkStartedAt,
            ended_at_ms: chunkEndedAt,
            text: combinedText,
            language: language || this.roomData.stt_lang || 'en-GB',
            confidence: averageConfidence || null,
            provider: provider
        };

        console.log('📤 Uploading transcript chunk:', {
            provider,
            text_length: combinedText.length,
            character_name: characterInfo.character_name,
            character_class: characterInfo.character_class,
            confidence: averageConfidence
        });

        try {
            const response = await fetch(`/api/rooms/${this.roomData.id}/transcripts`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify(payload)
            });

            if (response.ok) {
                console.log('📤 ✅ Transcript chunk uploaded successfully');
                return true;
            } else {
                const errorText = await response.text();
                console.error('📤 ❌ Failed to upload transcript chunk:', response.status, errorText);
                return false;
            }
        } catch (error) {
            console.error('📤 ❌ Error uploading transcript chunk:', error);
            return false;
        }
    }

    /**
     * Upload multiple transcript chunks in batch
     * 
     * @param {Array} chunks - Array of transcript chunk data
     * @returns {Promise<Object>} - Results with success/failure counts
     */
    async uploadTranscriptChunks(chunks) {
        console.log(`📤 Uploading ${chunks.length} transcript chunks in batch`);
        
        const results = {
            total: chunks.length,
            successful: 0,
            failed: 0,
            errors: []
        };

        for (const chunk of chunks) {
            try {
                const success = await this.uploadTranscriptChunk(chunk);
                if (success) {
                    results.successful++;
                } else {
                    results.failed++;
                }
            } catch (error) {
                results.failed++;
                results.errors.push(error.message);
                console.error('📤 ❌ Batch upload error:', error);
            }
        }

        console.log(`📤 Batch upload complete: ${results.successful}/${results.total} successful`);
        return results;
    }

    /**
     * Validate transcript data before upload
     * 
     * @param {Object} transcriptData - The transcript data to validate
     * @returns {Object} - Validation result with isValid and errors
     */
    validateTranscriptData(transcriptData) {
        const errors = [];

        if (!transcriptData.speechBuffer || !Array.isArray(transcriptData.speechBuffer)) {
            errors.push('speechBuffer must be an array');
        } else if (transcriptData.speechBuffer.length === 0) {
            errors.push('speechBuffer cannot be empty');
        }

        if (!transcriptData.chunkStartedAt || typeof transcriptData.chunkStartedAt !== 'number') {
            errors.push('chunkStartedAt must be a number (timestamp)');
        }

        if (!transcriptData.provider || typeof transcriptData.provider !== 'string') {
            errors.push('provider must be a string');
        } else if (!['browser', 'assemblyai'].includes(transcriptData.provider)) {
            errors.push('provider must be either "browser" or "assemblyai"');
        }

        if (transcriptData.language && typeof transcriptData.language !== 'string') {
            errors.push('language must be a string if provided');
        }

        return {
            isValid: errors.length === 0,
            errors
        };
    }

    /**
     * Get upload statistics for debugging
     * 
     * @returns {Object} - Current uploader state and configuration
     */
    getUploadStats() {
        return {
            roomId: this.roomData.id,
            userId: this.currentUserId,
            characterInfo: this.getCurrentUserCharacterInfo(),
            roomLanguage: this.roomData.stt_lang,
            participantCount: this.roomData.participants ? this.roomData.participants.length : 0
        };
    }
}
