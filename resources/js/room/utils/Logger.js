/**
 * Logger - Simple log level system for production vs development
 * 
 * Reduces console spam in production while preserving debug info in development.
 * Uses environment detection or can be manually configured.
 */

export class Logger {
    static LOG_LEVELS = {
        DEBUG: 0,
        INFO: 1,
        WARN: 2,
        ERROR: 3,
        NONE: 4
    };

    constructor(context = '', level = null) {
        this.context = context;
        
        // Auto-detect environment if level not specified
        if (level === null) {
            // Check if we're in production
            const isProduction = 
                window.location.hostname !== 'localhost' &&
                window.location.hostname !== '127.0.0.1' &&
                !window.location.hostname.includes('.test') &&
                !window.location.hostname.includes('.local');
            
            // Production: WARN and above, Development: DEBUG and above
            this.level = isProduction ? Logger.LOG_LEVELS.WARN : Logger.LOG_LEVELS.DEBUG;
        } else {
            this.level = level;
        }
        
        // Show initialization message
        if (this.level <= Logger.LOG_LEVELS.INFO) {
            console.log(`🔧 Logger initialized for "${context}" at level: ${this.getLevelName()}`);
        }
    }

    /**
     * Gets the name of the current log level
     */
    getLevelName() {
        return Object.keys(Logger.LOG_LEVELS).find(
            key => Logger.LOG_LEVELS[key] === this.level
        );
    }

    /**
     * Sets the log level
     */
    setLevel(level) {
        this.level = level;
    }

    /**
     * Formats a message with context prefix
     */
    formatMessage(message, ...args) {
        const prefix = this.context ? `[${this.context}]` : '';
        return [prefix, message, ...args].filter(Boolean);
    }

    /**
     * DEBUG level logging - detailed information for debugging
     */
    debug(message, ...args) {
        if (this.level <= Logger.LOG_LEVELS.DEBUG) {
            console.log(...this.formatMessage(message, ...args));
        }
    }

    /**
     * INFO level logging - general informational messages
     */
    info(message, ...args) {
        if (this.level <= Logger.LOG_LEVELS.INFO) {
            console.log(...this.formatMessage(message, ...args));
        }
    }

    /**
     * WARN level logging - warning messages
     */
    warn(message, ...args) {
        if (this.level <= Logger.LOG_LEVELS.WARN) {
            console.warn(...this.formatMessage(message, ...args));
        }
    }

    /**
     * ERROR level logging - error messages
     */
    error(message, ...args) {
        if (this.level <= Logger.LOG_LEVELS.ERROR) {
            console.error(...this.formatMessage(message, ...args));
        }
    }

    /**
     * Creates a child logger with a sub-context
     */
    child(subContext) {
        const childContext = this.context ? `${this.context}:${subContext}` : subContext;
        const childLogger = new Logger(childContext, this.level);
        return childLogger;
    }

    /**
     * Creates a logger with a specific context and level
     */
    static create(context, level = null) {
        return new Logger(context, level);
    }

    /**
     * Global logger instance (can be used as singleton)
     */
    static global = new Logger('RoomWebRTC');
}

