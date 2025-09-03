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

// Register tall-toasts plugin with Alpine before Livewire starts
Alpine.plugin(ToastComponent);

// Make Alpine available globally for individual components
window.Alpine = Alpine;

Livewire.start();