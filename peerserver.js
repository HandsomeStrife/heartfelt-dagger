/**
 * PeerJS Server for DaggerHeart Video Rooms
 * 
 * This server handles WebRTC signaling for video connections.
 * Keep it running alongside Laravel for development and production.
 */

import { ExpressPeerServer } from 'peer';
import express from 'express';

const app = express();
const port = process.env.PEERJS_PORT || 9000;

// Create HTTP server
const server = app.listen(port, () => {
    console.log(`🎯 PeerJS Server listening on port ${port}`);
});

// Create PeerServer
const peerServer = ExpressPeerServer(server, {
    debug: process.env.NODE_ENV !== 'production',
    path: '/', // Path is relative to mount point
    // Allow CORS for local development
    allow_discovery: true,
    // Cleanup inactive peers after 5 minutes
    alive_timeout: 300000,
    // Configuration
    proxied: true, // We're behind nginx in production
    // TURN server configuration (optional - falls back to public STUN)
    config: {
        iceServers: [
            { urls: 'stun:stun.l.google.com:19302' },
            { urls: 'stun:stun1.l.google.com:19302' }
        ]
    }
});

// Mount PeerServer on Express at /peerjs
app.use('/peerjs', peerServer);

// Health check endpoint
app.get('/health', (req, res) => {
    res.json({ 
        status: 'ok', 
        service: 'peerjs-server',
        uptime: process.uptime()
    });
});

// Handle PeerServer events
peerServer.on('connection', (client) => {
    console.log(`✅ Client connected: ${client.getId()}`);
});

peerServer.on('disconnect', (client) => {
    console.log(`❌ Client disconnected: ${client.getId()}`);
});

// Graceful shutdown
process.on('SIGTERM', () => {
    console.log('SIGTERM signal received: closing PeerJS server');
    server.close(() => {
        console.log('PeerJS server closed');
        process.exit(0);
    });
});

console.log('🚀 PeerJS Server initialized');
console.log(`📡 WebSocket endpoint: ws://localhost:${port}/peerjs`);
console.log(`🏥 Health check: http://localhost:${port}/health`);
