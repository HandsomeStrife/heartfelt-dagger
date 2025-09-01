import './bootstrap';

// Import TipTap editor setup
import './editor';

// Import room functionality for video conferencing
import RoomUppy from './room-uppy';
import RoomWebRTC from './room-webrtc';

// Make room classes globally available for room sessions
window.RoomUppy = RoomUppy;
window.RoomWebRTC = RoomWebRTC;

// Character Viewer state module (Alpine)
import { characterViewerState } from './character';
window.characterViewerState = characterViewerState;