/**
 * Centralized Logger - Respects LOG_LEVEL environment variable
 * 
 * Log Levels (from least to most restrictive):
 * - debug: All logs (debug, info, warn, error)
 * - info: Info, warnings, and errors only
 * - warn: Warnings and errors only
 * - error: Errors only
 * - none: No logging
 * 
 * Default: 'info' in production, 'debug' in development
 */

const LOG_LEVELS = {
    debug: 0,
    info: 1,
    warn: 2,
    error: 3,
    none: 4
};

class Logger {
    constructor() {
        // Get log level from Vite environment variable or default to 'info'
        const envLogLevel = import.meta.env.VITE_LOG_LEVEL || import.meta.env.MODE === 'development' ? 'debug' : 'info';
        this.logLevel = LOG_LEVELS[envLogLevel.toLowerCase()] ?? LOG_LEVELS.info;
        
        // Show startup message in debug mode
        if (this.logLevel === LOG_LEVELS.debug) {
            console.log(`🔧 Logger initialized with level: ${envLogLevel}`);
        }
    }
    
    /**
     * Set log level programmatically
     */
    setLogLevel(level) {
        if (LOG_LEVELS[level.toLowerCase()] !== undefined) {
            this.logLevel = LOG_LEVELS[level.toLowerCase()];
            console.log(`🔧 Logger level changed to: ${level}`);
        } else {
            console.warn(`⚠️ Invalid log level: ${level}. Valid levels: debug, info, warn, error, none`);
        }
    }
    
    /**
     * Get current log level name
     */
    getLogLevel() {
        return Object.keys(LOG_LEVELS).find(key => LOG_LEVELS[key] === this.logLevel) || 'unknown';
    }
    
    /**
     * Debug logs (most verbose)
     */
    debug(...args) {
        if (this.logLevel <= LOG_LEVELS.debug) {
            console.log(...args);
        }
    }
    
    /**
     * Info logs (normal operation)
     */
    info(...args) {
        if (this.logLevel <= LOG_LEVELS.info) {
            console.log(...args);
        }
    }
    
    /**
     * Warning logs (potential issues)
     */
    warn(...args) {
        if (this.logLevel <= LOG_LEVELS.warn) {
            console.warn(...args);
        }
    }
    
    /**
     * Error logs (always shown unless level is 'none')
     */
    error(...args) {
        if (this.logLevel <= LOG_LEVELS.error) {
            console.error(...args);
        }
    }
    
    /**
     * Group logs (collapsed by default)
     */
    group(label, collapsed = true) {
        if (this.logLevel <= LOG_LEVELS.info) {
            if (collapsed) {
                console.groupCollapsed(label);
            } else {
                console.group(label);
            }
        }
    }
    
    /**
     * End log group
     */
    groupEnd() {
        if (this.logLevel <= LOG_LEVELS.info) {
            console.groupEnd();
        }
    }
    
    /**
     * Table logs (for structured data)
     */
    table(data) {
        if (this.logLevel <= LOG_LEVELS.debug) {
            console.table(data);
        }
    }
}

// Create singleton instance
const logger = new Logger();

// Make it available globally for console debugging
if (typeof window !== 'undefined') {
    window.logger = logger;
}

export default logger;

