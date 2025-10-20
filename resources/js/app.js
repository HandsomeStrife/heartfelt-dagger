import './bootstrap';

// Import ModuleLoader first (creates window.moduleLoader)
import './room/utils/ModuleLoader';

// Import TipTap editor setup
import './editor';

// Import room functionality for video conferencing
import RoomUppy from './room-uppy';
import RoomWebRTC from './room-webrtc';
import { RoomSessionInitializer } from './room/RoomSessionInitializer';

// Import video thumbnail generator
import VideoThumbnailGenerator from './video-thumbnail-generator';

// Make room classes globally available for room sessions
window.RoomUppy = RoomUppy;
window.RoomWebRTC = RoomWebRTC;
window.RoomSessionInitializer = RoomSessionInitializer;
window.VideoThumbnailGenerator = VideoThumbnailGenerator;

// Register modules with ModuleLoader for reliable loading
if (window.moduleLoader) {
    window.moduleLoader.register('RoomWebRTC', RoomWebRTC);
    window.moduleLoader.register('RoomUppy', RoomUppy);
    window.moduleLoader.register('RoomSessionInitializer', RoomSessionInitializer);
    window.moduleLoader.register('VideoThumbnailGenerator', VideoThumbnailGenerator);
}

// Create global video thumbnail generator instance
window.videoThumbnailGenerator = new VideoThumbnailGenerator();

// Character Viewer state module (Alpine)
import { characterViewerState } from './character';
window.characterViewerState = characterViewerState;

// Character Builder component (Alpine)
import { characterBuilderComponent } from './character-builder';
window.characterBuilderComponent = characterBuilderComponent;

// Character Level Up component (Alpine)
import { characterLevelUpComponent } from './character-level-up';
window.characterLevelUpComponent = characterLevelUpComponent;

// Character Image Uppy (for profile image uploads)
import SimpleImageUploader from './character-image-simple';
window.SimpleImageUploader = SimpleImageUploader;

// Last Saved Timestamp component (Alpine)
import { lastSavedTimestampComponent } from './last-saved-timestamp';
window.lastSavedTimestampComponent = lastSavedTimestampComponent;

// Import dice system to expose window.initDiceBox and related helpers
import './dice';

// Import Alpine and Livewire
import { Alpine, Livewire } from '../../vendor/livewire/livewire/dist/livewire.esm';

// Import tall-toasts
import ToastComponent from '../../vendor/usernotnull/tall-toasts/resources/js/tall-toasts';

// Make Alpine available globally for individual components
window.Alpine = Alpine;

// Register tall-toasts plugin with Alpine before Livewire starts
Alpine.plugin(ToastComponent);

// Add error handling for Livewire start to catch plugin conflicts
try {
    Livewire.start();
} catch (error) {
    console.error('❌ Error starting Livewire:', error);
    // Try to start without plugins if there's a conflict
    console.log('🔄 Attempting to start Livewire without conflicting plugins...');
    // Note: This is a fallback - the real fix should prevent the conflict
}