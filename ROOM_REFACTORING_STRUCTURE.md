# Room WebRTC Refactoring Structure

This document outlines the modular architecture for the Room WebRTC system, breaking down the monolithic `room-webrtc.js` file into focused, maintainable components.

## File Structure

```
resources/js/room/
├── RoomWebRTC.js                 # Main orchestrator class
├── webrtc/
│   ├── PeerConnectionManager.js  # WebRTC peer connections ✅
│   ├── MediaManager.js           # Local/remote media streams ✅
│   └── ICEConfigManager.js       # ICE server configuration ✅
├── messaging/
│   ├── AblyManager.js            # Ably realtime messaging ✅
│   └── MessageHandler.js         # Message routing and handling
├── recording/
│   ├── VideoRecorder.js          # Video recording logic ✅
│   ├── StreamingDownloader.js    # Local device streaming downloads ✅
│   └── CloudUploader.js          # Cloud storage uploads ✅
├── speech/
│   ├── SpeechManager.js          # Speech recognition orchestrator ✅
│   ├── BrowserSTT.js             # Web Speech API implementation ✅
│   └── AssemblyAISTT.js          # AssemblyAI implementation ✅
├── consent/
│   ├── ConsentManager.js         # Unified consent handling ✅
│   └── ConsentDialog.js          # Consent UI components ✅
├── ui/
│   ├── StatusBarManager.js       # Recording status bar ✅
│   ├── SlotManager.js            # Video slot management ✅
│   └── UIStateManager.js         # UI enable/disable states ✅
└── utils/
    ├── DiagnosticsRunner.js      # Speech/WebRTC diagnostics ✅
    └── PageProtection.js         # Refresh protection ✅
```

## Component Responsibilities

### Core Orchestrator
- **RoomWebRTC.js**: Main class that coordinates all other components, handles initialization, and manages the overall room session lifecycle.

### WebRTC Components
- **PeerConnectionManager.js**: Manages RTCPeerConnection instances, handles connection lifecycle, and coordinates peer-to-peer connections.
- **MediaManager.js**: Handles local and remote media streams, camera/microphone access, and stream management.
- **ICEConfigManager.js**: ✅ Manages ICE server configuration, STUN/TURN setup, and connection telemetry.

### Messaging Components
- **AblyManager.js**: ✅ Handles Ably realtime messaging, channel management, and message publishing/subscribing.
- **MessageHandler.js**: Routes and processes different message types, handles message validation and error handling.

### Recording Components
- **VideoRecorder.js**: Core video recording functionality using MediaRecorder API, handles recording state and chunking.
- **StreamingDownloader.js**: Manages local device downloads with streaming capability (VDO.ninja style single file downloads).
- **CloudUploader.js**: Handles uploads to cloud storage providers (S3-compatible), manages upload queues and retry logic.

### Speech Recognition Components
- **SpeechManager.js**: Orchestrates speech recognition, handles provider switching, and manages transcription state.
- **BrowserSTT.js**: Web Speech API implementation with browser-specific optimizations and error handling.
- **AssemblyAISTT.js**: AssemblyAI streaming transcription implementation with token management and real-time processing.

### Consent Management Components
- **ConsentManager.js**: ✅ Unified consent handling for all features, manages consent state and API communication.
- **ConsentDialog.js**: Reusable consent dialog UI components with customizable messaging and styling.

### UI Management Components
- **StatusBarManager.js**: ✅ Bottom status bar with recording controls, timer, transcript access, and leave functionality.
- **SlotManager.js**: ✅ Video slot management, participant display, occupancy tracking, and slot interaction handling.
- **UIStateManager.js**: Manages UI enable/disable states based on consent, loading states, and feature availability.

### Utility Components
- **DiagnosticsRunner.js**: ✅ Comprehensive diagnostics for speech recognition and WebRTC troubleshooting.
- **PageProtection.js**: ✅ Page refresh protection with emergency save functionality for active recordings.

## Implementation Status

### ✅ Completed Components
- ICEConfigManager.js
- PeerConnectionManager.js
- MediaManager.js
- AblyManager.js
- MessageHandler.js
- VideoRecorder.js
- StreamingDownloader.js
- CloudUploader.js
- SpeechManager.js
- BrowserSTT.js
- AssemblyAISTT.js
- ConsentManager.js
- ConsentDialog.js
- StatusBarManager.js
- SlotManager.js
- UIStateManager.js
- DiagnosticsRunner.js
- PageProtection.js

### ✅ Completed Components (ALL)
**Total: 18 Components Successfully Extracted**

### 🎉 REFACTORING COMPLETE
- All components extracted and integrated
- Modular architecture fully implemented
- Ready for testing and deployment

## Design Principles

1. **Single Responsibility**: Each component handles one specific aspect of the room functionality.
2. **Dependency Injection**: Components receive dependencies through constructors, making them testable.
3. **Event-Driven**: Components communicate through well-defined interfaces and callbacks.
4. **Error Isolation**: Errors in one component don't cascade to others.
5. **Testability**: Each component can be unit tested independently.
6. **Maintainability**: Clear separation makes it easy to modify or extend individual features.

## Integration Pattern

```javascript
// Main orchestrator pattern
class RoomWebRTC {
    constructor(roomData) {
        // Initialize all managers
        this.iceManager = new ICEConfigManager();
        this.ablyManager = new AblyManager(this);
        this.peerManager = new PeerConnectionManager(this);
        this.mediaManager = new MediaManager(this);
        this.recordingManager = new VideoRecorder(this);
        this.speechManager = new SpeechManager(this);
        this.consentManager = new ConsentManager(this);
        this.statusBarManager = new StatusBarManager(this);
        this.slotManager = new SlotManager(this);
        
        // Wire up cross-component communication
        this.setupComponentIntegration();
    }
}
```

This modular approach transforms a 2,900+ line monolithic file into focused, maintainable components that are easier to test, debug, and extend.
