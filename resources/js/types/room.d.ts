/**
 * TypeScript type definitions for DaggerHeart Room System
 * 
 * These types ensure type safety across the WebRTC room implementation.
 */

// ===========================================
// CORE DATA STRUCTURES
// ===========================================

/**
 * Room configuration and state
 */
export interface RoomData {
  id: number;
  name: string;
  invite_code: string;
  campaign_id: number | null;
  created_by_user_id: number;
  
  // Feature flags
  recording_enabled: boolean;
  stt_enabled: boolean;
  stt_provider?: 'browser' | 'assemblyai';
  
  // Recording settings
  recording_settings?: RecordingSettings;
  
  // Metadata
  created_at: string;
  updated_at: string;
}

/**
 * Recording configuration
 */
export interface RecordingSettings {
  storage_provider: 'local' | 'wasabi' | 'google_drive';
  auto_download_enabled: boolean;
  save_copy_to_device: boolean;
  wasabi_bucket?: string;
  google_drive_folder_id?: string;
}

/**
 * Participant information
 */
export interface ParticipantData {
  user_id: number;
  username: string;
  character_id?: number;
  character_name?: string;
  character_image_url?: string;
  is_gm: boolean;
}

/**
 * Slot occupant information
 */
export interface SlotOccupant {
  peerId: string;
  stream?: MediaStream;
  participantData: ParticipantData;
  isLocal: boolean;
}

/**
 * Consent data for features
 */
export interface ConsentData {
  stt: ConsentStatus;
  recording: ConsentStatus;
  localSave: ConsentStatus;
}

/**
 * Individual consent status
 */
export interface ConsentStatus {
  status: 'not_asked' | 'pending' | 'consent_given' | 'consent_denied';
  consent_given?: boolean;
  timestamp?: string;
}

// ===========================================
// PEER CONNECTION TYPES
// ===========================================

/**
 * Connection states for peer connections
 */
export type ConnectionState = 
  | 'disconnected'
  | 'connecting'
  | 'connected'
  | 'reconnecting'
  | 'failed';

/**
 * PeerJS connection statistics
 */
export interface PeerStats {
  peerId: string;
  isConnected: boolean;
  connectedPeers: string[];
  totalCalls: number;
}

/**
 * Peer state validation result
 */
export interface PeerStateValidation {
  isValid: boolean;
  issues: string[];
}

// ===========================================
// SIGNALING TYPES
// ===========================================

/**
 * Signaling message types
 */
export type SignalingMessageType =
  | 'user-joined'
  | 'user-left'
  | 'state-request'
  | 'state-response'
  | 'game-state-updated'
  | 'countdown-updated'
  | 'session-marker-created';

/**
 * Base signaling message
 */
export interface SignalingMessage {
  type: SignalingMessageType;
  data: Record<string, any>;
  targetPeerId?: string;
}

// ===========================================
// RECORDING TYPES
// ===========================================

/**
 * Recording session information
 */
export interface RecordingSession {
  filename: string;
  startTime: number;
  storageProvider: RecordingSettings['storage_provider'];
  isPaused: boolean;
  chunks: Blob[];
}

/**
 * Cloud upload progress
 */
export interface UploadProgress {
  loaded: number;
  total: number;
  percentage: number;
}

// ===========================================
// SPEECH-TO-TEXT TYPES
// ===========================================

/**
 * STT transcript result
 */
export interface TranscriptResult {
  text: string;
  isFinal: boolean;
  confidence?: number;
  timestamp: number;
}

/**
 * STT module interface
 */
export interface SpeechModule {
  start(stream: MediaStream): Promise<void>;
  stop(): Promise<void>;
  isActive(): boolean;
}

// ===========================================
// DIAGNOSTIC TYPES
// ===========================================

/**
 * Diagnostic snapshot
 */
export interface DiagnosticsSnapshot {
  timestamp: string;
  roomId?: number;
  currentUserId?: number;
  
  state: {
    isJoined: boolean;
    currentSlotId: string | null;
    slotOccupantCount: number;
    isSpeechEnabled: boolean;
    sttPausedForMute: boolean;
    sttTransitioning: boolean;
  };
  
  peerJs: PeerStats;
  peerServerState: ConnectionState;
  
  peerConnections: Array<{
    peerId: string;
    state: ConnectionState;
    retryAttempts: number;
  }>;
  
  reverb: {
    state: string;
    channelName: string | null;
    socketId: string | null;
  };
  
  media: {
    hasLocalStream: boolean;
    isMicrophoneMuted: boolean;
    isVideoHidden: boolean;
    localStreamTracks: {
      audio: number;
      video: number;
    } | null;
  };
  
  recording: {
    isRecording: boolean;
    isPaused: boolean;
    recordedChunks: number;
    recordingDuration: number;
    storageProvider?: string;
  };
  
  slots: Array<{
    slotId: string;
    peerId?: string;
    userId?: number;
    characterName?: string;
    isLocal: boolean;
    hasStream: boolean;
    streamTracks: {
      audio: number;
      video: number;
    } | null;
  }>;
  
  consent: {
    stt: string;
    recording: string;
    localSave: string;
  };
  
  browser: {
    userAgent: string;
    platform: string;
    language: string;
    online: boolean;
    cookieEnabled: boolean;
  };
  
  performance: {
    memoryUsage: string | {
      usedJSHeapSize: string;
      totalJSHeapSize: string;
      jsHeapSizeLimit: string;
    };
    uptime: string;
  };
  
  features: {
    recordingEnabled?: boolean;
    sttEnabled?: boolean;
    autoDownloadEnabled?: boolean;
    saveCopyToDevice?: boolean;
  };
}

/**
 * Logger log entry
 */
export interface LogEntry {
  level: 'info' | 'warn' | 'error' | 'debug' | 'state';
  module: string;
  message: string;
  data?: Record<string, any>;
  error?: {
    message: string;
    stack?: string;
    name: string;
  };
  reason?: string;
  context: Record<string, any>;
  timestamp: number;
}

// ===========================================
// UTILITY TYPES
// ===========================================

/**
 * Rate limiter token bucket
 */
export interface TokenBucket {
  tokens: number;
  maxTokens: number;
  refillRate: number;
  lastRefill: number;
}

/**
 * Retry budget tracking
 */
export interface RetryBudget {
  maxRetries: number;
  timeWindow: number;
  retries: number[];
}

/**
 * Health monitoring state
 */
export interface HealthMonitoring {
  interval: NodeJS.Timeout | null;
  currentDelay: number;
  minDelay: number;
  maxDelay: number;
  consecutiveHealthy: number;
  healthyThreshold: number;
}

/**
 * Consent cache entry
 */
export interface ConsentCacheEntry {
  status: string | null;
  timestamp: number | null;
  ttl: number;
}

// ===========================================
// GLOBAL TYPES
// ===========================================

/**
 * Global window extensions
 */
declare global {
  interface Window {
    roomWebRTC?: any; // Will be typed as RoomWebRTC once migrated
    Echo?: any; // Laravel Echo
    currentUserId?: number;
    showLeavingModal?: (message: string) => void;
    updateLeavingModalStatus?: (status: string, message: string) => void;
  }
}

export {};

