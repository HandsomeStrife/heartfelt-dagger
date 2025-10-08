/**
 * FeatureFlags - Runtime feature toggle system
 * 
 * Allows enabling/disabling WebRTC features without deployments.
 * Useful for A/B testing, gradual rollouts, and emergency killswitches.
 * 
 * ARCHITECTURE: Provides centralized feature management
 */

/**
 * Available feature flags
 */
export const Features = {
    // Core features
    WEBRTC_ENABLED: 'webrtc.enabled',
    PEER_CONNECTIONS: 'webrtc.peer-connections',
    
    // Media features
    VIDEO_ENABLED: 'media.video',
    AUDIO_ENABLED: 'media.audio',
    SCREEN_SHARE: 'media.screen-share',
    
    // Recording features
    RECORDING_ENABLED: 'recording.enabled',
    LOCAL_RECORDING: 'recording.local',
    CLOUD_RECORDING: 'recording.cloud',
    AUTO_DOWNLOAD: 'recording.auto-download',
    
    // STT features
    STT_ENABLED: 'stt.enabled',
    BROWSER_STT: 'stt.browser',
    ASSEMBLYAI_STT: 'stt.assemblyai',
    
    // Advanced features
    CONNECTION_HEALTH_MONITORING: 'advanced.health-monitoring',
    DIAGNOSTICS_LOGGING: 'advanced.diagnostics',
    ERROR_REPORTING: 'advanced.error-reporting',
    STATE_VALIDATION: 'advanced.state-validation',
    
    // Performance features
    DOM_CACHING: 'performance.dom-caching',
    RATE_LIMITING: 'performance.rate-limiting',
    RETRY_BUDGET: 'performance.retry-budget',
    
    // UI features
    CHARACTER_OVERLAYS: 'ui.character-overlays',
    VIDEO_CONTROLS: 'ui.video-controls',
    STATUS_BAR: 'ui.status-bar',
    
    // Experimental features
    EXPERIMENTAL_CODEC: 'experimental.codec',
    EXPERIMENTAL_BANDWIDTH: 'experimental.bandwidth-control'
} as const;

export type Feature = typeof Features[keyof typeof Features];

/**
 * Feature flag configuration
 */
interface FeatureConfig {
    enabled: boolean;
    description?: string;
    requiresConsent?: boolean;
    dependencies?: Feature[];
}

/**
 * FeatureFlags class
 */
export class FeatureFlags {
    private flags: Map<Feature, FeatureConfig> = new Map();
    private overrides: Map<Feature, boolean> = new Map();
    
    constructor() {
        this.initializeDefaults();
        this.loadFromLocalStorage();
    }
    
    /**
     * Initialize default feature flags
     */
    private initializeDefaults(): void {
        // Core features (enabled by default)
        this.setFlag(Features.WEBRTC_ENABLED, {
            enabled: true,
            description: 'Enable WebRTC functionality'
        });
        
        this.setFlag(Features.PEER_CONNECTIONS, {
            enabled: true,
            description: 'Enable peer-to-peer connections',
            dependencies: [Features.WEBRTC_ENABLED]
        });
        
        // Media features
        this.setFlag(Features.VIDEO_ENABLED, {
            enabled: true,
            description: 'Enable video streaming'
        });
        
        this.setFlag(Features.AUDIO_ENABLED, {
            enabled: true,
            description: 'Enable audio streaming'
        });
        
        this.setFlag(Features.SCREEN_SHARE, {
            enabled: true,
            description: 'Enable screen sharing'
        });
        
        // Recording features
        this.setFlag(Features.RECORDING_ENABLED, {
            enabled: true,
            description: 'Enable recording functionality',
            requiresConsent: true
        });
        
        this.setFlag(Features.LOCAL_RECORDING, {
            enabled: true,
            description: 'Enable local device recording',
            dependencies: [Features.RECORDING_ENABLED]
        });
        
        this.setFlag(Features.CLOUD_RECORDING, {
            enabled: true,
            description: 'Enable cloud recording',
            dependencies: [Features.RECORDING_ENABLED]
        });
        
        this.setFlag(Features.AUTO_DOWNLOAD, {
            enabled: true,
            description: 'Auto-download recordings after session',
            dependencies: [Features.RECORDING_ENABLED]
        });
        
        // STT features
        this.setFlag(Features.STT_ENABLED, {
            enabled: true,
            description: 'Enable speech-to-text',
            requiresConsent: true
        });
        
        this.setFlag(Features.BROWSER_STT, {
            enabled: true,
            description: 'Enable browser-based STT',
            dependencies: [Features.STT_ENABLED]
        });
        
        this.setFlag(Features.ASSEMBLYAI_STT, {
            enabled: true,
            description: 'Enable AssemblyAI STT',
            dependencies: [Features.STT_ENABLED]
        });
        
        // Advanced features
        this.setFlag(Features.CONNECTION_HEALTH_MONITORING, {
            enabled: true,
            description: 'Monitor connection health'
        });
        
        this.setFlag(Features.DIAGNOSTICS_LOGGING, {
            enabled: true,
            description: 'Enable diagnostic logging'
        });
        
        this.setFlag(Features.ERROR_REPORTING, {
            enabled: true,
            description: 'Enable error reporting'
        });
        
        this.setFlag(Features.STATE_VALIDATION, {
            enabled: true,
            description: 'Validate peer state consistency'
        });
        
        // Performance features
        this.setFlag(Features.DOM_CACHING, {
            enabled: true,
            description: 'Cache DOM element references'
        });
        
        this.setFlag(Features.RATE_LIMITING, {
            enabled: true,
            description: 'Rate limit signaling messages'
        });
        
        this.setFlag(Features.RETRY_BUDGET, {
            enabled: true,
            description: 'Global retry budget for connections'
        });
        
        // UI features
        this.setFlag(Features.CHARACTER_OVERLAYS, {
            enabled: true,
            description: 'Show character overlays on video'
        });
        
        this.setFlag(Features.VIDEO_CONTROLS, {
            enabled: true,
            description: 'Show video control buttons'
        });
        
        this.setFlag(Features.STATUS_BAR, {
            enabled: true,
            description: 'Show status bar'
        });
        
        // Experimental features (disabled by default)
        this.setFlag(Features.EXPERIMENTAL_CODEC, {
            enabled: false,
            description: 'Experimental codec settings'
        });
        
        this.setFlag(Features.EXPERIMENTAL_BANDWIDTH, {
            enabled: false,
            description: 'Experimental bandwidth control'
        });
    }
    
    /**
     * Set a feature flag
     */
    private setFlag(feature: Feature, config: FeatureConfig): void {
        this.flags.set(feature, config);
    }
    
    /**
     * Check if a feature is enabled
     */
    isEnabled(feature: Feature): boolean {
        // Check for override first
        if (this.overrides.has(feature)) {
            return this.overrides.get(feature)!;
        }
        
        const config = this.flags.get(feature);
        if (!config) {
            console.warn(`Feature flag not found: ${feature}`);
            return false;
        }
        
        // Check if feature is enabled
        if (!config.enabled) {
            return false;
        }
        
        // Check dependencies
        if (config.dependencies) {
            for (const dependency of config.dependencies) {
                if (!this.isEnabled(dependency)) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Enable a feature
     */
    enable(feature: Feature): void {
        const config = this.flags.get(feature);
        if (config) {
            config.enabled = true;
            this.saveToLocalStorage();
            console.log(`✅ Feature enabled: ${feature}`);
        }
    }
    
    /**
     * Disable a feature
     */
    disable(feature: Feature): void {
        const config = this.flags.get(feature);
        if (config) {
            config.enabled = false;
            this.saveToLocalStorage();
            console.log(`❌ Feature disabled: ${feature}`);
        }
    }
    
    /**
     * Override a feature flag (runtime only, not persisted)
     */
    override(feature: Feature, enabled: boolean): void {
        this.overrides.set(feature, enabled);
        console.log(`🔧 Feature override: ${feature} = ${enabled}`);
    }
    
    /**
     * Clear override
     */
    clearOverride(feature: Feature): void {
        this.overrides.delete(feature);
        console.log(`🔧 Feature override cleared: ${feature}`);
    }
    
    /**
     * Get all features and their states
     */
    getAllFeatures(): Array<{ feature: Feature; enabled: boolean; config: FeatureConfig }> {
        const features: Array<{ feature: Feature; enabled: boolean; config: FeatureConfig }> = [];
        
        for (const [feature, config] of this.flags.entries()) {
            features.push({
                feature,
                enabled: this.isEnabled(feature),
                config
            });
        }
        
        return features;
    }
    
    /**
     * Save to localStorage
     */
    private saveToLocalStorage(): void {
        try {
            const data: Record<string, boolean> = {};
            for (const [feature, config] of this.flags.entries()) {
                data[feature] = config.enabled;
            }
            localStorage.setItem('room.feature-flags', JSON.stringify(data));
        } catch (error) {
            console.warn('Failed to save feature flags:', error);
        }
    }
    
    /**
     * Load from localStorage
     */
    private loadFromLocalStorage(): void {
        try {
            const data = localStorage.getItem('room.feature-flags');
            if (data) {
                const parsed = JSON.parse(data) as Record<string, boolean>;
                for (const [feature, enabled] of Object.entries(parsed)) {
                    const config = this.flags.get(feature as Feature);
                    if (config) {
                        config.enabled = enabled;
                    }
                }
                console.log('📥 Feature flags loaded from localStorage');
            }
        } catch (error) {
            console.warn('Failed to load feature flags:', error);
        }
    }
    
    /**
     * Reset to defaults
     */
    reset(): void {
        this.flags.clear();
        this.overrides.clear();
        this.initializeDefaults();
        this.saveToLocalStorage();
        console.log('🔄 Feature flags reset to defaults');
    }
    
    /**
     * Export configuration
     */
    export(): string {
        const data: Record<string, boolean> = {};
        for (const [feature, config] of this.flags.entries()) {
            data[feature] = config.enabled;
        }
        return JSON.stringify(data, null, 2);
    }
    
    /**
     * Import configuration
     */
    import(json: string): void {
        try {
            const data = JSON.parse(json) as Record<string, boolean>;
            for (const [feature, enabled] of Object.entries(data)) {
                const config = this.flags.get(feature as Feature);
                if (config) {
                    config.enabled = enabled;
                }
            }
            this.saveToLocalStorage();
            console.log('📥 Feature flags imported successfully');
        } catch (error) {
            console.error('Failed to import feature flags:', error);
        }
    }
}

/**
 * Global feature flags instance
 */
export const featureFlags = new FeatureFlags();

/**
 * Helper function to check if feature is enabled
 */
export function isFeatureEnabled(feature: Feature): boolean {
    return featureFlags.isEnabled(feature);
}

