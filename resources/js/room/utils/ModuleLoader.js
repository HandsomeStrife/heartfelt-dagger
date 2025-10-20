/**
 * ModuleLoader - Promise-based module loading system
 * 
 * Replaces polling-based initialization with proper Promise resolution.
 * Provides reliable module loading with configurable timeouts and proper error handling.
 */

export class ModuleLoader {
    constructor() {
        this.modules = new Map();
        this.readyPromises = new Map();
    }
    
    /**
     * Register a module as loaded
     * @param {string} name - Module name
     * @param {*} module - The loaded module
     */
    register(name, module) {
        console.log(`📦 Module registered: ${name}`);
        this.modules.set(name, module);
        
        // Resolve any waiting promises
        const resolver = this.readyPromises.get(name);
        if (resolver) {
            resolver.resolve(module);
            this.readyPromises.delete(name);
        }
    }
    
    /**
     * Wait for a module to be loaded
     * @param {string} name - Module name to wait for
     * @param {number} timeout - Timeout in milliseconds (default: 10000)
     * @returns {Promise<*>} The loaded module
     */
    async waitFor(name, timeout = 10000) {
        // Already loaded?
        if (this.modules.has(name)) {
            console.log(`✅ Module '${name}' already loaded`);
            return Promise.resolve(this.modules.get(name));
        }
        
        console.log(`⏳ Waiting for module '${name}' to load (timeout: ${timeout}ms)...`);
        
        // Create promise if doesn't exist
        if (!this.readyPromises.has(name)) {
            let resolve, reject;
            const promise = new Promise((res, rej) => {
                resolve = res;
                reject = rej;
            });
            this.readyPromises.set(name, { promise, resolve, reject });
        }
        
        const { promise, reject } = this.readyPromises.get(name);
        
        // Add timeout
        const timeoutId = setTimeout(() => {
            reject(new Error(`Module '${name}' failed to load within ${timeout}ms`));
            this.readyPromises.delete(name);
        }, timeout);
        
        try {
            const module = await promise;
            clearTimeout(timeoutId);
            console.log(`✅ Module '${name}' loaded successfully`);
            return module;
        } catch (error) {
            clearTimeout(timeoutId);
            console.error(`❌ Failed to load module '${name}':`, error);
            throw error;
        }
    }
    
    /**
     * Check if a module is already loaded
     * @param {string} name - Module name
     * @returns {boolean}
     */
    isLoaded(name) {
        return this.modules.has(name);
    }
    
    /**
     * Get a loaded module (sync, returns undefined if not loaded)
     * @param {string} name - Module name
     * @returns {*|undefined}
     */
    get(name) {
        return this.modules.get(name);
    }
}

// Create and export global instance
if (typeof window !== 'undefined') {
    window.moduleLoader = new ModuleLoader();
    console.log('📦 ModuleLoader initialized');
}

export default ModuleLoader;

