/**
 * VDO.NINJA FIX: Diagnostic Logger with full context
 * 
 * Provides structured logging with state context for debugging.
 * Inspired by VDO.ninja's comprehensive logging approach.
 * 
 * TYPESCRIPT MIGRATION: Fully typed with strict mode compliance
 * 
 * Now respects centralized LOG_LEVEL configuration via VITE_LOG_LEVEL
 */

import type { LogEntry } from '../../types/room';
import baseLogger from '../../utils/logger';

/**
 * Logger context interface
 */
interface LogContext {
    timestamp: string;
    roomId?: number;
    userId?: number;
    currentSlotId: string | null;
    isJoined: boolean;
    peerServerId?: string;
    activePeers: string[];
    slotCount: number;
}

/**
 * Logger class for structured, contextual logging
 */
export class Logger {
    private moduleName: string;
    private roomWebRTC: any | null; // TODO: Type as RoomWebRTC once migrated
    private logHistory: LogEntry[];
    private maxHistorySize: number;
    
    constructor(moduleName: string, roomWebRTC: any = null) {
        this.moduleName = moduleName;
        this.roomWebRTC = roomWebRTC;
        this.logHistory = [];
        this.maxHistorySize = 100;
    }
    
    /**
     * Gets current context for logging
     */
    private getContext(): LogContext {
        if (!this.roomWebRTC) {
            return {
                timestamp: new Date().toISOString(),
                currentSlotId: null,
                isJoined: false,
                activePeers: [],
                slotCount: 0
            };
        }
        
        return {
            timestamp: new Date().toISOString(),
            roomId: this.roomWebRTC.roomData?.id,
            userId: this.roomWebRTC.currentUserId,
            currentSlotId: this.roomWebRTC.currentSlotId ?? null,
            isJoined: this.roomWebRTC.isJoined ?? false,
            peerServerId: this.roomWebRTC.simplePeerManager?.peerId,
            activePeers: this.roomWebRTC.simplePeerManager?.getActivePeerIds() || [],
            slotCount: this.roomWebRTC.slotOccupants?.size || 0
        };
    }
    
    /**
     * Logs with full context
     */
    log(message: string, data: Record<string, any> = {}): void {
        const logEntry: LogEntry = {
            level: 'info',
            module: this.moduleName,
            message,
            data,
            context: this.getContext(),
            timestamp: Date.now()
        };
        
        this.addToHistory(logEntry);
        baseLogger.info(`[${this.moduleName}] ${message}`, { data, context: logEntry.context });
    }
    
    /**
     * Warns with full context
     */
    warn(message: string, data: Record<string, any> = {}): void {
        const logEntry: LogEntry = {
            level: 'warn',
            module: this.moduleName,
            message,
            data,
            context: this.getContext(),
            timestamp: Date.now()
        };
        
        this.addToHistory(logEntry);
        baseLogger.warn(`⚠️ [${this.moduleName}] ${message}`, { data, context: logEntry.context });
    }
    
    /**
     * Debug logging (verbose, disabled in production)
     */
    debug(message: string, data: Record<string, any> = {}): void {
        const logEntry: LogEntry = {
            level: 'debug',
            module: this.moduleName,
            message,
            data,
            context: this.getContext(),
            timestamp: Date.now()
        };
        
        this.addToHistory(logEntry);
        baseLogger.debug(`🐛 [${this.moduleName}] ${message}`, { data, context: logEntry.context });
    }
    
    /**
     * Errors with full context
     */
    error(message: string, error: Error | null = null, data: Record<string, any> = {}): void {
        const logEntry: LogEntry = {
            level: 'error',
            module: this.moduleName,
            message,
            error: error ? {
                message: error.message,
                stack: error.stack,
                name: error.name
            } : undefined,
            data,
            context: this.getContext(),
            timestamp: Date.now()
        };
        
        this.addToHistory(logEntry);
        baseLogger.error(`❌ [${this.moduleName}] ${message}`, { 
            error, 
            data, 
            context: logEntry.context 
        });
    }
    
    /**
     * State change logs (VDO.ninja pattern)
     */
    stateChange(from: string, to: string, reason: string = '', data: Record<string, any> = {}): void {
        const logEntry: LogEntry = {
            level: 'state',
            module: this.moduleName,
            message: `State: ${from} → ${to}`,
            reason,
            data,
            context: this.getContext(),
            timestamp: Date.now()
        };
        
        this.addToHistory(logEntry);
        baseLogger.info(`🔄 [${this.moduleName}] ${from} → ${to}`, { 
            reason, 
            data, 
            context: logEntry.context 
        });
    }
    
    /**
     * Adds to circular log history
     */
    private addToHistory(logEntry: LogEntry): void {
        this.logHistory.push(logEntry);
        if (this.logHistory.length > this.maxHistorySize) {
            this.logHistory.shift();
        }
    }
    
    /**
     * Gets recent log history
     */
    getHistory(count: number = 50): LogEntry[] {
        return this.logHistory.slice(-count);
    }
    
    /**
     * Exports log history as JSON
     */
    exportHistory(): void {
        const blob = new Blob([JSON.stringify(this.logHistory, null, 2)], { 
            type: 'application/json' 
        });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `${this.moduleName}-logs-${Date.now()}.json`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
        
        baseLogger.info(`📥 Exported ${this.logHistory.length} log entries for ${this.moduleName}`);
    }
}

/**
 * Module history interface for registry
 */
interface ModuleHistory {
    module: string;
    logs: LogEntry[];
}

/**
 * Global logger registry for easy access
 */
export class LoggerRegistry {
    private static loggers: Map<string, Logger> = new Map();
    
    static getLogger(moduleName: string, roomWebRTC: any = null): Logger {
        if (!this.loggers.has(moduleName)) {
            this.loggers.set(moduleName, new Logger(moduleName, roomWebRTC));
        }
        return this.loggers.get(moduleName)!;
    }
    
    static getAllHistory(): ModuleHistory[] {
        const allHistory: ModuleHistory[] = [];
        for (const [moduleName, logger] of this.loggers.entries()) {
            allHistory.push({
                module: moduleName,
                logs: logger.getHistory()
            });
        }
        return allHistory;
    }
    
    static exportAllLogs(): void {
        const allHistory = this.getAllHistory();
        const blob = new Blob([JSON.stringify(allHistory, null, 2)], { 
            type: 'application/json' 
        });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `room-logs-${Date.now()}.json`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
        
        baseLogger.info('📥 Exported all module logs');
    }
}

