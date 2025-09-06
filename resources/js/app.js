import './bootstrap';

// Import TipTap editor setup
import './editor';

// Import room functionality for video conferencing
import RoomUppy from './room-uppy';
import RoomWebRTC from './room-webrtc';

// Make room classes globally available for room sessions
console.log('🎬 Loading RoomUppy and RoomWebRTC...');
window.RoomUppy = RoomUppy;
window.RoomWebRTC = RoomWebRTC;
console.log('✅ RoomWebRTC available:', typeof window.RoomWebRTC);

// Character Viewer state module (Alpine)
import { characterViewerState } from './character';
window.characterViewerState = characterViewerState;

// Character Builder component (Alpine)
import { characterBuilderComponent } from './character-builder';
window.characterBuilderComponent = characterBuilderComponent;

// Character Image Uppy (for profile image uploads)
import SimpleImageUploader from './character-image-simple';
window.SimpleImageUploader = SimpleImageUploader;

// Last Saved Timestamp component (Alpine)
import { lastSavedTimestampComponent } from './last-saved-timestamp';
window.lastSavedTimestampComponent = lastSavedTimestampComponent;

// Import Alpine and Livewire
import { Alpine, Livewire } from '../../vendor/livewire/livewire/dist/livewire.esm';

// Import tall-toasts
import ToastComponent from '../../vendor/usernotnull/tall-toasts/resources/js/tall-toasts';

// Make Alpine available globally for individual components
window.Alpine = Alpine;

// Register tall-toasts plugin with Alpine before Livewire starts
console.log('🔧 Registering ToastComponent plugin...');
Alpine.plugin(ToastComponent);

console.log('🚀 Starting Livewire...');

// Add error handling for Livewire start to catch plugin conflicts
try {
    Livewire.start();
} catch (error) {
    console.error('❌ Error starting Livewire:', error);
    // Try to start without plugins if there's a conflict
    console.log('🔄 Attempting to start Livewire without conflicting plugins...');
    // Note: This is a fallback - the real fix should prevent the conflict
}