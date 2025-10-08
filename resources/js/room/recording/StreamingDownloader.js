/**
 * StreamingDownloader - Manages streaming downloads for local device recording
 * 
 * Uses StreamSaver.js (same as VDO.Ninja) for true streaming downloads of 7GB+ videos
 * without memory accumulation.
 * 
 * StreamSaver.js works by:
 * 1. Registering a Service Worker that intercepts downloads
 * 2. Creating a WritableStream that pipes directly to browser download
 * 3. Streaming chunks as Uint8Array directly to disk (no memory accumulation)
 * 4. Works on localhost, HTTP, and HTTPS via Service Worker or MITM iframe
 */

import streamSaver from 'streamsaver';

export class StreamingDownloader {
    constructor(roomWebRTC) {
        this.roomWebRTC = roomWebRTC;
        this.recordingFilename = null;
        this.isStreamingDownloadActive = false;
        
        // StreamSaver state
        this.writableStream = null;
        this.streamWriter = null;
        this.totalBytesWritten = 0;
        this.chunkCount = 0;
        
        // Fallback buffer (only if StreamSaver fails)
        this.fallbackChunks = [];
        this.useFallback = false;
        
        // Configure StreamSaver (VDO.Ninja setup)
        // mitm.html is for HTTPS - allows Service Worker to work in secure contexts
        streamSaver.mitm = '/mitm.html';
        
        console.log('📥 ✅ StreamSaver.js imported (VDO.Ninja method)');
        console.log('📥 ✅ Service Worker will intercept downloads for streaming');
    }

    /**
     * Initializes StreamSaver writable stream for streaming download
     * 
     * This creates a download stream that:
     * - Shows browser save dialog immediately
     * - Streams chunks directly to disk via Service Worker
     * - No memory accumulation (unlimited file size)
     * - Same method VDO.Ninja uses
     */
    async initializeDownload(mimeType) {
        // Generate timestamped filename with room/participant info
        const roomName = this.roomWebRTC.roomData.name.replace(/[^a-z0-9]/gi, '-');
        
        // Find current participant's name from the participants array
        const currentUserId = this.roomWebRTC.currentUserId;
        const participantData = this.roomWebRTC.roomData.participants.find(p => p.user_id === currentUserId);
        const participantName = (participantData?.character_name || participantData?.username || 'Unknown-Participant').replace(/[^a-z0-9]/gi, '-');
        
        const timestamp = new Date().toISOString().replace(/[:.]/g, '-').replace('Z', '');
        const ext = mimeType.includes('mp4') ? 'mp4' : 'webm';
        
        this.recordingFilename = `${roomName}-${participantName}-${timestamp}.${ext}`;
        this.isStreamingDownloadActive = true;
        
        // Reset streaming state
        this.totalBytesWritten = 0;
        this.chunkCount = 0;
        this.fallbackChunks = [];
        
        console.log(`📥 Streaming download initialized: ${this.recordingFilename}`);
        console.log(`📥 Using StreamSaver.js (VDO.Ninja method)`);
        
        try {
            // Create StreamSaver writable stream
            // This will trigger the browser save dialog and start streaming
            console.log(`📥 ⏳ Creating StreamSaver writable stream...`);
            
            this.writableStream = streamSaver.createWriteStream(this.recordingFilename, {
                size: null, // Unknown size - streaming
                writableStrategy: undefined,
                readableStrategy: undefined
            });
            
            // Get writer for the stream
            this.streamWriter = this.writableStream.getWriter();
            
            console.log(`📥 ✅ StreamSaver stream created: ${this.recordingFilename}`);
            console.log(`📥 ✅ Browser should show save dialog and download progress`);
            console.log(`📥 ✅ Ready to stream unlimited file size (7GB+)`);
            
        } catch (error) {
            console.error('📥 ❌ StreamSaver.js failed:', error);
            console.error('📥 🔍 Error details:', {
                name: error.name,
                message: error.message,
                stack: error.stack
            });
            
            console.warn('📥 📦 Falling back to memory buffer (limit: ~1GB)');
            console.warn('📥 ⚠️ Large recordings may fail in this mode');
            
            this.useFallback = true;
            
            // FALLBACK MODE: Prepare for memory accumulation
            console.log('📥 🎬 Fallback mode: Recording will accumulate in memory');
            console.log('📥 ℹ️ Download will be triggered when you stop recording');
        }
    }
    
    /**
     * Streams chunks directly via StreamSaver (no memory accumulation)
     */
    async updateDownload(newChunk) {
        if (!this.isStreamingDownloadActive) return;
        
        this.chunkCount++;
        this.totalBytesWritten += newChunk.size;
        
        const chunkMB = (newChunk.size / 1024 / 1024).toFixed(2);
        const totalGB = (this.totalBytesWritten / 1024 / 1024 / 1024).toFixed(2);
        
        console.log(`📥 Chunk ${this.chunkCount}: ${chunkMB}MB (Total: ${totalGB}GB)`);
        
        try {
            if (this.useFallback) {
                // Fallback: accumulate in memory (old behavior with 1GB limit)
                this.fallbackChunks.push(newChunk);
                
                // Warn at 1GB for fallback mode
                if (this.totalBytesWritten > 1024 * 1024 * 1024) {
                    console.warn('⚠️ Fallback mode: Recording exceeds 1GB in memory');
                    console.warn('⚠️ Browser may crash. Consider stopping and downloading.');
                }
            } else if (this.streamWriter) {
                // StreamSaver streaming: convert Blob to Uint8Array
                // StreamSaver requires Uint8Array (not Blob)
                const arrayBuffer = await newChunk.arrayBuffer();
                const uint8Array = new Uint8Array(arrayBuffer);
                
                await this.streamWriter.write(uint8Array);
                console.log(`📥 ✅ Streamed to disk (${totalGB}GB total, no memory accumulation)`);
            }
        } catch (error) {
            console.error('📥 ❌ Stream write error:', error);
            console.error('📥 🔍 Error details:', {
                name: error.name,
                message: error.message,
                stack: error.stack
            });
            
            // Switch to fallback mode on error
            if (!this.useFallback) {
                console.warn('📥 Switching to fallback buffer mode due to stream error');
                this.useFallback = true;
                this.fallbackChunks.push(newChunk);
            }
        }
        
        // Update status bar
        this.roomWebRTC.statusBarManager?.updateRecordingStatus();
    }

    /**
     * Adds a chunk for local save (used in dual recording)
     */
    async addChunk(blob, recordingData) {
        // Initialize download if not already active (for dual recording scenario)
        if (!this.isStreamingDownloadActive) {
            await this.initializeDownload(recordingData.mime_type);
        }
        
        // Stream the chunk
        await this.updateDownload(blob);
        
        console.log(`💾 Local save chunk streamed: ${recordingData.filename} (part ${recordingData.partNumber})`);
    }

    /**
     * Finalizes StreamSaver download
     * @param {boolean} synchronous - If true, use synchronous close (for page unload)
     */
    async finalizeDownload(synchronous = false) {
        if (!this.recordingFilename) {
            console.warn('📥 No recording to finalize');
            return;
        }
        
        const totalGB = (this.totalBytesWritten / 1024 / 1024 / 1024).toFixed(2);
        
        console.log(`📥 ⏳ Finalizing recording: ${this.recordingFilename} (synchronous: ${synchronous})`);
        
        try {
            if (this.streamWriter && !this.useFallback) {
                // Close StreamSaver writer (completes download)
                console.log(`📥 ⏳ Closing StreamSaver writer...`);
                
                if (synchronous) {
                    // CRITICAL FIX: For page unload, we can't wait for async close
                    // Instead, release the writer lock which allows the stream to finish
                    console.log(`📥 🚨 Synchronous close: Releasing writer lock`);
                    try {
                        this.streamWriter.releaseLock();
                        console.log(`📥 ✅ Writer lock released - download will complete`);
                    } catch (releaseLockError) {
                        console.warn(`📥 Error releasing lock, attempting abort:`, releaseLockError);
                        // Last resort: abort the stream (this at least closes it)
                        try {
                            await this.streamWriter.abort();
                        } catch (abortError) {
                            console.error(`📥 ❌ Abort failed:`, abortError);
                        }
                    }
                } else {
                    // Normal async close
                    await this.streamWriter.close();
                    console.log(`📥 ✅ StreamSaver writer closed`);
                }
                
                console.log(`📥 ✅ Recording saved: ${this.recordingFilename} (${totalGB}GB)`);
                console.log(`📥 ✅ ${this.chunkCount} chunks streamed directly to disk`);
            } else if (this.useFallback && this.fallbackChunks.length > 0) {
                // FALLBACK: Create blob from accumulated chunks and trigger download
                console.log(`📥 ⏳ Creating fallback download...`);
                const mimeType = this.roomWebRTC.videoRecorder.getRecordingMimeType();
                const combinedBlob = new Blob(this.fallbackChunks, { type: mimeType });
                
                // Create download link
                const url = URL.createObjectURL(combinedBlob);
                const link = document.createElement('a');
                link.href = url;
                link.download = this.recordingFilename;
                link.style.display = 'none';
                
                document.body.appendChild(link);
                link.click();
                
                // Clean up after a short delay
                setTimeout(() => {
                    document.body.removeChild(link);
                    URL.revokeObjectURL(url);
                }, 1000);
                
                console.log(`📥 ✅ Fallback download triggered: ${this.recordingFilename} (${totalGB}GB)`);
                console.log(`📥 ✅ ${this.chunkCount} chunks combined from memory`);
            }
        } catch (error) {
            console.error('📥 ❌ Finalize error:', error);
            console.error('📥 🔍 Error details:', {
                name: error.name,
                message: error.message,
                stack: error.stack
            });
            throw error;
        } finally {
            // Clean up
            console.log(`📥 🧹 Cleaning up resources...`);
            this.streamWriter = null;
            this.writableStream = null;
            this.fallbackChunks = [];
            this.recordingFilename = null;
            this.isStreamingDownloadActive = false;
            this.totalBytesWritten = 0;
            this.chunkCount = 0;
            
            // Update status bar
            this.roomWebRTC.statusBarManager?.updateRecordingStatus();
        }
    }

    /**
     * Downloads the complete recording as a single file (fallback method)
     */
    downloadCompleteRecording() {
        try {
            console.log('💾 Attempting to download complete recording...');
            console.log('💾 Storage provider:', this.roomWebRTC.roomData.recording_settings?.storage_provider);
            console.log('💾 Recorded chunks:', this.recordedChunks.length);
            
            if (this.recordedChunks.length === 0) {
                console.warn('💾 No recorded chunks to download');
                return;
            }

            // Combine all chunks into a single blob
            const mimeType = this.roomWebRTC.videoRecorder.getRecordingMimeType();
            const completeBlob = new Blob(this.recordedChunks, { type: mimeType });
            
            // Generate filename with timestamp
            const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
            const ext = mimeType.includes('webm') ? 'webm' : 'mp4';
            const filename = `room-recording-${timestamp}.${ext}`;
            
            // Create download link
            const url = URL.createObjectURL(completeBlob);
            const link = document.createElement('a');
            link.href = url;
            link.download = filename;
            
            // Trigger download
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            // Clean up
            URL.revokeObjectURL(url);
            this.recordedChunks = []; // Clear chunks after download
            this.totalRecordedSize = 0;
            this.hasShownMemoryWarning = false;
            
            console.log(`💾 Complete recording downloaded: ${filename} (${(completeBlob.size / 1024 / 1024).toFixed(2)} MB)`);
        } catch (error) {
            console.error('💾 Error downloading complete recording:', error);
        }
    }

    /**
     * Gets current download state
     */
    isDownloadActive() {
        return this.isStreamingDownloadActive;
    }

    /**
     * Gets recorded chunks
     */
    getRecordedChunks() {
        return this.recordedChunks;
    }

    /**
     * Gets recording filename
     */
    getRecordingFilename() {
        return this.recordingFilename;
    }

    /**
     * Resets download state
     */
    reset() {
        this.recordedChunks = [];
        this.recordingFilename = null;
        this.isStreamingDownloadActive = false;
        this.totalRecordedSize = 0;
        this.hasShownMemoryWarning = false;
    }
}
