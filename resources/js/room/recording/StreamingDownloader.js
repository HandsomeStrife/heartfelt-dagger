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
        this.firstChunkWritten = false; // Track if first chunk succeeded (user accepted save dialog)
        
        // Configure StreamSaver (VDO.Ninja setup)
        // mitm.html is for HTTPS - allows Service Worker to work in secure contexts
        streamSaver.mitm = '/mitm.html';
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
        this.firstChunkWritten = false;
        
        // Show user-friendly notification about upcoming save dialog
        if (window.Toast) {
            window.Toast.info('Recording started - you\'ll be prompted to choose a save location shortly');
        }
        
        try {
            // Create StreamSaver writable stream
            // This will trigger the browser save dialog and start streaming
            this.writableStream = streamSaver.createWriteStream(this.recordingFilename, {
                size: null, // Unknown size - streaming
                writableStrategy: undefined,
                readableStrategy: undefined
            });
            
            // Get writer for the stream
            this.streamWriter = this.writableStream.getWriter();
            
        } catch (error) {
            console.error('📥 ❌ StreamSaver.js failed:', error);
            console.error('📥 🔍 Error details:', {
                name: error.name,
                message: error.message,
                stack: error.stack
            });
            
            console.error('📥 🛑 Cannot initialize streaming - stopping recording');
            
            // Show error toast with instructions
            if (window.Toast) {
                window.Toast.danger('Recording failed to start. Please leave the room and try again.');
            }
            
            // Stop the recording - we can't continue without streaming
            if (this.roomWebRTC.videoRecorder.isCurrentlyRecording()) {
                this.roomWebRTC.videoRecorder.stopRecording().catch(err => {
                    console.error('Error stopping recording after StreamSaver failure:', err);
                });
            }
            
            throw error; // Propagate error to caller
        }
    }
    
    /**
     * Streams chunks directly via StreamSaver (no memory accumulation)
     */
    async updateDownload(newChunk) {
        if (!this.isStreamingDownloadActive) return;
        
        this.chunkCount++;
        this.totalBytesWritten += newChunk.size;
        
        try {
            if (this.streamWriter) {
                // StreamSaver streaming: convert Blob to Uint8Array
                // StreamSaver requires Uint8Array (not Blob)
                const arrayBuffer = await newChunk.arrayBuffer();
                const uint8Array = new Uint8Array(arrayBuffer);
                
                await this.streamWriter.write(uint8Array);
                
                // Track first successful write (but no toast - we can't detect when user clicks "Save")
                if (!this.firstChunkWritten) {
                    this.firstChunkWritten = true;
                }
            } else {
                console.error('📥 ❌ No stream writer available - cannot save chunk');
            }
        } catch (error) {
            console.error('📥 ❌ Stream write error:', error);
            
            // Handle error object that might be undefined or incomplete
            if (error) {
                console.error('📥 🔍 Error details:', {
                    name: error.name || 'Unknown',
                    message: error.message || 'No error message',
                    stack: error.stack || 'No stack trace'
                });
            } else {
                console.error('📥 🔍 Error object is undefined - likely user canceled save dialog');
            }
            
            // If this was the first chunk, user likely canceled the save dialog
            if (!this.firstChunkWritten) {
                console.error('📥 ❌ First chunk write failed - user may have canceled save dialog');
                console.error('📥 🛑 Stopping recording due to save dialog rejection');
                
                if (window.Toast) {
                    window.Toast.danger('Save dialog was canceled. Please refresh the page and try again.');
                }
                
                // Stop the recording gracefully
                if (this.roomWebRTC.videoRecorder.isCurrentlyRecording()) {
                    // Don't await - just trigger stop in the background
                    this.roomWebRTC.videoRecorder.stopRecording().catch(err => {
                        console.error('Error stopping recording after save dialog cancel:', err);
                    });
                }
                
                return; // Exit early
            }
            
            // If streaming fails after initial success, try to restart with a new file
            if (this.firstChunkWritten) {
                console.warn('📥 Stream failed, attempting recovery with new file...');
                
                // Close the failed stream
                if (this.streamWriter) {
                    try {
                        await this.streamWriter.abort();
                    } catch (abortError) {
                        console.error('📥 Error aborting failed stream:', abortError);
                    }
                    this.streamWriter = null;
                    this.writableStream = null;
                }
                
                // Try to restart streaming with a new file
                try {
                    const mimeType = this.roomWebRTC.videoRecorder.getRecordingMimeType();
                    const roomName = this.roomWebRTC.roomData.name.replace(/[^a-z0-9]/gi, '-');
                    const currentUserId = this.roomWebRTC.currentUserId;
                    const participantData = this.roomWebRTC.roomData.participants.find(p => p.user_id === currentUserId);
                    const participantName = (participantData?.character_name || participantData?.username || 'Unknown-Participant').replace(/[^a-z0-9]/gi, '-');
                    const timestamp = new Date().toISOString().replace(/[:.]/g, '-').replace('Z', '');
                    const ext = mimeType.includes('mp4') ? 'mp4' : 'webm';
                    
                    // New filename with "-recovery" suffix
                    this.recordingFilename = `${roomName}-${participantName}-${timestamp}-recovery.${ext}`;
                    this.firstChunkWritten = false; // Reset flag
                    
                    this.writableStream = streamSaver.createWriteStream(this.recordingFilename, {
                        size: null,
                        writableStrategy: undefined,
                        readableStrategy: undefined
                    });
                    
                    this.streamWriter = this.writableStream.getWriter();
                    
                    if (window.Toast) {
                        window.Toast.warning('Stream interrupted. A new save dialog will appear - previous chunks saved separately.');
                    }
                    
                    // Try to write the current chunk to the new stream
                    const arrayBuffer = await newChunk.arrayBuffer();
                    const uint8Array = new Uint8Array(arrayBuffer);
                    await this.streamWriter.write(uint8Array);
                    this.firstChunkWritten = true;
                    
                } catch (restartError) {
                    console.error('📥 ❌ Failed to restart streaming:', restartError);
                    
                    if (window.Toast) {
                        window.Toast.danger('Recording stream failed. Please refresh the page and try again.');
                    }
                    
                    // Stop the recording - we can't recover
                    if (this.roomWebRTC.videoRecorder.isCurrentlyRecording()) {
                        this.roomWebRTC.videoRecorder.stopRecording().catch(err => {
                            console.error('Error stopping recording after restart failure:', err);
                        });
                    }
                }
                
                return; // Exit early - either recovered or stopped
            }
            
            // If we reach here, something unexpected happened
            console.error('📥 ❌ Unexpected error state in updateDownload');
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
    }

    /**
     * Finalizes StreamSaver download
     * VDO.Ninja approach: Simply call writer.close() (async is fine, Service Worker handles it)
     */
    async finalizeDownload() {
        if (!this.recordingFilename) {
            return;
        }
        
        try {
            if (this.streamWriter) {
                // VDO.Ninja method: Just call close() - Service Worker completes download
                await this.streamWriter.close();
            } else {
                console.warn('📥 No stream writer to finalize - recording may have failed');
            }
        } catch (error) {
            console.error('📥 ❌ Finalize error:', error);
            throw error;
        } finally {
            // Clean up
            this.streamWriter = null;
            this.writableStream = null;
            this.recordingFilename = null;
            this.isStreamingDownloadActive = false;
            this.totalBytesWritten = 0;
            this.chunkCount = 0;
            
            // Update status bar
            this.roomWebRTC.statusBarManager?.updateRecordingStatus();
        }
    }

    /**
     * Gets current download state (used by PageProtection)
     */
    isDownloadActive() {
        return this.isStreamingDownloadActive;
    }

    /**
     * Gets recording filename (used by UI)
     */
    getRecordingFilename() {
        return this.recordingFilename;
    }
}
