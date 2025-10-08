/**
 * ErrorReporter - Interface for pluggable error tracking
 * 
 * Provides a consistent interface for error reporting that can be implemented
 * by various error tracking services (Sentry, Rollbar, custom solutions).
 * 
 * Usage:
 * ```javascript
 * // Development - logs to console
 * const errorReporter = new ConsoleErrorReporter();
 * 
 * // Production - use Sentry
 * const errorReporter = new SentryErrorReporter({ dsn: '...' });
 * 
 * // Report errors
 * errorReporter.captureError(error, { context: 'WebRTC connection' });
 * ```
 */

/**
 * Base ErrorReporter interface
 */
export class ErrorReporter {
    /**
     * Captures an error with optional context
     * @param {Error} error - The error to capture
     * @param {object} context - Additional context (tags, user info, etc.)
     */
    captureError(error, context = {}) {
        throw new Error('captureError() must be implemented by subclass');
    }

    /**
     * Captures a message (warning or info level)
     * @param {string} message - The message to capture
     * @param {string} level - Severity level (info, warning, error)
     * @param {object} context - Additional context
     */
    captureMessage(message, level = 'info', context = {}) {
        throw new Error('captureMessage() must be implemented by subclass');
    }

    /**
     * Sets user context for error tracking
     * @param {object} user - User information (id, username, email)
     */
    setUser(user) {
        throw new Error('setUser() must be implemented by subclass');
    }

    /**
     * Adds breadcrumb for error trail
     * @param {string} message - Breadcrumb message
     * @param {string} category - Breadcrumb category
     * @param {object} data - Additional data
     */
    addBreadcrumb(message, category = 'default', data = {}) {
        throw new Error('addBreadcrumb() must be implemented by subclass');
    }

    /**
     * Sets tags for filtering errors
     * @param {object} tags - Key-value pairs of tags
     */
    setTags(tags) {
        throw new Error('setTags() must be implemented by subclass');
    }

    /**
     * Sets additional context
     * @param {string} key - Context key
     * @param {object} value - Context value
     */
    setContext(key, value) {
        throw new Error('setContext() must be implemented by subclass');
    }
}

/**
 * Console-based error reporter for development
 */
export class ConsoleErrorReporter extends ErrorReporter {
    constructor() {
        super();
        this.user = null;
        this.tags = {};
        this.breadcrumbs = [];
    }

    captureError(error, context = {}) {
        console.error('❌ Error captured:', {
            error: error.message,
            stack: error.stack,
            context,
            tags: this.tags,
            user: this.user,
            breadcrumbs: this.breadcrumbs.slice(-10) // Last 10 breadcrumbs
        });
    }

    captureMessage(message, level = 'info', context = {}) {
        const logFn = level === 'error' ? console.error : 
                      level === 'warning' ? console.warn : 
                      console.log;
        
        logFn(`📝 [${level.toUpperCase()}] ${message}`, {
            context,
            tags: this.tags,
            user: this.user
        });
    }

    setUser(user) {
        this.user = user;
        console.log('👤 User context set:', user);
    }

    addBreadcrumb(message, category = 'default', data = {}) {
        this.breadcrumbs.push({
            timestamp: new Date().toISOString(),
            message,
            category,
            data
        });

        // Keep only last 50 breadcrumbs
        if (this.breadcrumbs.length > 50) {
            this.breadcrumbs.shift();
        }
    }

    setTags(tags) {
        this.tags = { ...this.tags, ...tags };
    }

    setContext(key, value) {
        console.log(`🏷️ Context set: ${key}`, value);
    }
}

/**
 * Sentry error reporter (placeholder - implement when Sentry is added)
 * 
 * Installation:
 * npm install @sentry/browser
 * 
 * Usage:
 * ```javascript
 * import * as Sentry from '@sentry/browser';
 * const errorReporter = new SentryErrorReporter({
 *     dsn: 'your-sentry-dsn',
 *     environment: 'production'
 * });
 * ```
 */
export class SentryErrorReporter extends ErrorReporter {
    constructor(config = {}) {
        super();
        this.config = config;
        this.initialized = false;
        
        // Placeholder - will be implemented when @sentry/browser is installed
        console.warn('⚠️ SentryErrorReporter: Sentry not installed, falling back to console');
        this.fallback = new ConsoleErrorReporter();
    }

    async init() {
        try {
            // When Sentry is installed, initialize it here:
            // const Sentry = await import('@sentry/browser');
            // Sentry.init({
            //     dsn: this.config.dsn,
            //     environment: this.config.environment || 'production',
            //     integrations: [new Sentry.BrowserTracing()],
            //     tracesSampleRate: 0.1,
            // });
            // this.initialized = true;
            
            console.log('📡 Sentry initialization placeholder');
        } catch (error) {
            console.warn('⚠️ Failed to initialize Sentry:', error);
        }
    }

    captureError(error, context = {}) {
        if (this.initialized) {
            // Sentry.captureException(error, { contexts: context });
        } else {
            this.fallback.captureError(error, context);
        }
    }

    captureMessage(message, level = 'info', context = {}) {
        if (this.initialized) {
            // Sentry.captureMessage(message, level, { contexts: context });
        } else {
            this.fallback.captureMessage(message, level, context);
        }
    }

    setUser(user) {
        if (this.initialized) {
            // Sentry.setUser(user);
        } else {
            this.fallback.setUser(user);
        }
    }

    addBreadcrumb(message, category = 'default', data = {}) {
        if (this.initialized) {
            // Sentry.addBreadcrumb({ message, category, data });
        } else {
            this.fallback.addBreadcrumb(message, category, data);
        }
    }

    setTags(tags) {
        if (this.initialized) {
            // Sentry.setTags(tags);
        } else {
            this.fallback.setTags(tags);
        }
    }

    setContext(key, value) {
        if (this.initialized) {
            // Sentry.setContext(key, value);
        } else {
            this.fallback.setContext(key, value);
        }
    }
}

/**
 * Rollbar error reporter (placeholder - implement when Rollbar is added)
 * 
 * Installation:
 * npm install rollbar
 * 
 * Usage:
 * ```javascript
 * const errorReporter = new RollbarErrorReporter({
 *     accessToken: 'your-rollbar-token',
 *     environment: 'production'
 * });
 * ```
 */
export class RollbarErrorReporter extends ErrorReporter {
    constructor(config = {}) {
        super();
        this.config = config;
        this.initialized = false;
        
        console.warn('⚠️ RollbarErrorReporter: Rollbar not installed, falling back to console');
        this.fallback = new ConsoleErrorReporter();
    }

    async init() {
        try {
            // When Rollbar is installed, initialize it here:
            // const Rollbar = await import('rollbar');
            // this.rollbar = new Rollbar({
            //     accessToken: this.config.accessToken,
            //     environment: this.config.environment || 'production',
            //     captureUncaught: true,
            //     captureUnhandledRejections: true,
            // });
            // this.initialized = true;
            
            console.log('📡 Rollbar initialization placeholder');
        } catch (error) {
            console.warn('⚠️ Failed to initialize Rollbar:', error);
        }
    }

    captureError(error, context = {}) {
        if (this.initialized) {
            // this.rollbar.error(error, context);
        } else {
            this.fallback.captureError(error, context);
        }
    }

    captureMessage(message, level = 'info', context = {}) {
        if (this.initialized) {
            // this.rollbar[level](message, context);
        } else {
            this.fallback.captureMessage(message, level, context);
        }
    }

    setUser(user) {
        if (this.initialized) {
            // this.rollbar.configure({ payload: { person: user } });
        } else {
            this.fallback.setUser(user);
        }
    }

    addBreadcrumb(message, category = 'default', data = {}) {
        // Rollbar doesn't have direct breadcrumb support, use custom data instead
        this.fallback.addBreadcrumb(message, category, data);
    }

    setTags(tags) {
        if (this.initialized) {
            // this.rollbar.configure({ payload: { tags } });
        } else {
            this.fallback.setTags(tags);
        }
    }

    setContext(key, value) {
        if (this.initialized) {
            // this.rollbar.configure({ payload: { [key]: value } });
        } else {
            this.fallback.setContext(key, value);
        }
    }
}

/**
 * Factory function to create error reporter based on environment
 * @param {string} environment - 'development', 'staging', 'production'
 * @param {object} config - Configuration for error reporter
 * @returns {ErrorReporter}
 */
export function createErrorReporter(environment = 'development', config = {}) {
    switch (environment) {
        case 'production':
            if (config.provider === 'sentry') {
                return new SentryErrorReporter(config.sentry || {});
            } else if (config.provider === 'rollbar') {
                return new RollbarErrorReporter(config.rollbar || {});
            }
            // Fallback to console even in production if no provider configured
            return new ConsoleErrorReporter();
            
        case 'staging':
            // Could use a different configuration for staging
            return new ConsoleErrorReporter();
            
        case 'development':
        default:
            return new ConsoleErrorReporter();
    }
}

/**
 * Global error reporter instance
 * Initialize this once in your application
 */
let globalErrorReporter = new ConsoleErrorReporter();

export function setGlobalErrorReporter(reporter) {
    globalErrorReporter = reporter;
}

export function getGlobalErrorReporter() {
    return globalErrorReporter;
}

/**
 * Convenience wrapper for WebRTC-specific error reporting
 */
export class WebRTCErrorReporter {
    constructor(errorReporter) {
        this.reporter = errorReporter;
        
        // Set WebRTC-specific tags
        this.reporter.setTags({
            component: 'webrtc',
            browser: this.detectBrowser(),
            webrtc_support: this.checkWebRTCSupport()
        });
    }

    detectBrowser() {
        const ua = navigator.userAgent;
        if (ua.includes('Chrome')) return 'chrome';
        if (ua.includes('Firefox')) return 'firefox';
        if (ua.includes('Safari')) return 'safari';
        if (ua.includes('Edge')) return 'edge';
        return 'unknown';
    }

    checkWebRTCSupport() {
        return {
            getUserMedia: !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia),
            RTCPeerConnection: !!window.RTCPeerConnection,
            MediaRecorder: !!window.MediaRecorder
        };
    }

    reportConnectionError(peerId, error, connectionState) {
        this.reporter.addBreadcrumb(
            `Connection error with peer ${peerId}`,
            'webrtc',
            { connectionState }
        );
        
        this.reporter.captureError(error, {
            webrtc: {
                peerId,
                connectionState,
                type: 'connection_error'
            }
        });
    }

    reportMediaError(error, mediaType) {
        this.reporter.addBreadcrumb(
            `Media error: ${mediaType}`,
            'webrtc',
            { mediaType }
        );
        
        this.reporter.captureError(error, {
            webrtc: {
                mediaType,
                type: 'media_error'
            }
        });
    }

    reportSignalingError(error, messageType) {
        this.reporter.addBreadcrumb(
            `Signaling error: ${messageType}`,
            'webrtc',
            { messageType }
        );
        
        this.reporter.captureError(error, {
            webrtc: {
                messageType,
                type: 'signaling_error'
            }
        });
    }
}


