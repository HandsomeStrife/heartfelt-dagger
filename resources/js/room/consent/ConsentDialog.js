/**
 * ConsentDialog - Reusable consent dialog UI components
 * 
 * Provides customizable consent dialogs for STT, video recording, and other features
 * with consistent styling, messaging, and interaction patterns.
 */
export class ConsentDialog {
    constructor() {
        this.activeDialogs = new Map(); // Map of dialogId -> dialog element
        this.dialogCounter = 0;
        
        // Default configuration
        this.defaultConfig = {
            backdrop: true,
            closable: true,
            persistent: false,
            animation: 'fade',
            position: 'center',
            theme: 'default'
        };
        
        this.setupDialogStyles();
    }

    /**
     * Sets up dialog styles and animations
     */
    setupDialogStyles() {
        // Check if styles are already injected
        if (document.getElementById('consent-dialog-styles')) {
            return;
        }

        const styles = `
            <style id="consent-dialog-styles">
                .consent-dialog-backdrop {
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(0, 0, 0, 0.5);
                    backdrop-filter: blur(4px);
                    z-index: 9999;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    opacity: 0;
                    transition: opacity 0.3s ease;
                }
                
                .consent-dialog-backdrop.show {
                    opacity: 1;
                }
                
                .consent-dialog {
                    background: white;
                    border-radius: 12px;
                    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
                    max-width: 500px;
                    width: 90%;
                    max-height: 90vh;
                    overflow-y: auto;
                    transform: scale(0.95) translateY(20px);
                    transition: transform 0.3s ease;
                }
                
                .consent-dialog-backdrop.show .consent-dialog {
                    transform: scale(1) translateY(0);
                }
                
                .consent-dialog-header {
                    padding: 24px 24px 0 24px;
                    border-bottom: 1px solid #e5e7eb;
                    margin-bottom: 20px;
                }
                
                .consent-dialog-title {
                    font-size: 1.25rem;
                    font-weight: 600;
                    color: #111827;
                    margin: 0 0 8px 0;
                }
                
                .consent-dialog-subtitle {
                    font-size: 0.875rem;
                    color: #6b7280;
                    margin: 0;
                }
                
                .consent-dialog-body {
                    padding: 0 24px 24px 24px;
                }
                
                .consent-dialog-content {
                    font-size: 0.875rem;
                    line-height: 1.5;
                    color: #374151;
                    margin-bottom: 24px;
                }
                
                .consent-dialog-actions {
                    display: flex;
                    gap: 12px;
                    justify-content: flex-end;
                }
                
                .consent-dialog-button {
                    padding: 8px 16px;
                    border-radius: 6px;
                    font-size: 0.875rem;
                    font-weight: 500;
                    cursor: pointer;
                    transition: all 0.2s ease;
                    border: 1px solid transparent;
                }
                
                .consent-dialog-button-primary {
                    background: #3b82f6;
                    color: white;
                }
                
                .consent-dialog-button-primary:hover {
                    background: #2563eb;
                }
                
                .consent-dialog-button-secondary {
                    background: #f3f4f6;
                    color: #374151;
                    border-color: #d1d5db;
                }
                
                .consent-dialog-button-secondary:hover {
                    background: #e5e7eb;
                }
                
                .consent-dialog-button-danger {
                    background: #ef4444;
                    color: white;
                }
                
                .consent-dialog-button-danger:hover {
                    background: #dc2626;
                }
                
                .consent-dialog-icon {
                    width: 48px;
                    height: 48px;
                    margin: 0 auto 16px auto;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 24px;
                }
                
                .consent-dialog-icon-info {
                    background: #dbeafe;
                    color: #3b82f6;
                }
                
                .consent-dialog-icon-warning {
                    background: #fef3c7;
                    color: #f59e0b;
                }
                
                .consent-dialog-icon-error {
                    background: #fee2e2;
                    color: #ef4444;
                }
                
                .consent-dialog-icon-success {
                    background: #d1fae5;
                    color: #10b981;
                }
                
                @media (max-width: 640px) {
                    .consent-dialog {
                        width: 95%;
                        margin: 20px;
                    }
                    
                    .consent-dialog-actions {
                        flex-direction: column;
                    }
                    
                    .consent-dialog-button {
                        width: 100%;
                        justify-content: center;
                    }
                }
            </style>
        `;
        
        document.head.insertAdjacentHTML('beforeend', styles);
    }

    /**
     * Shows a consent dialog
     */
    showConsentDialog(options) {
        const dialogId = `consent-dialog-${++this.dialogCounter}`;
        const config = { ...this.defaultConfig, ...options };
        
        console.log('🔒 Showing consent dialog:', dialogId, config);
        
        // Create dialog HTML
        const dialogHTML = this.createDialogHTML(dialogId, config);
        
        // Add to DOM
        document.body.insertAdjacentHTML('beforeend', dialogHTML);
        
        // Get dialog element
        const dialogElement = document.getElementById(dialogId);
        this.activeDialogs.set(dialogId, dialogElement);
        
        // Set up event handlers
        this.setupDialogHandlers(dialogId, config);
        
        // Show dialog with animation
        requestAnimationFrame(() => {
            dialogElement.classList.add('show');
        });
        
        // Return promise that resolves with user choice
        return new Promise((resolve, reject) => {
            dialogElement.addEventListener('consent-result', (event) => {
                resolve(event.detail);
            });
            
            dialogElement.addEventListener('consent-error', (event) => {
                reject(event.detail);
            });
        });
    }

    /**
     * Creates dialog HTML
     */
    createDialogHTML(dialogId, config) {
        const iconHTML = config.icon ? `
            <div class="consent-dialog-icon consent-dialog-icon-${config.iconType || 'info'}">
                ${config.icon}
            </div>
        ` : '';
        
        const subtitleHTML = config.subtitle ? `
            <p class="consent-dialog-subtitle">${config.subtitle}</p>
        ` : '';
        
        const actionsHTML = this.createActionsHTML(config.actions || []);
        
        return `
            <div id="${dialogId}" class="consent-dialog-backdrop">
                <div class="consent-dialog" role="dialog" aria-modal="true" aria-labelledby="${dialogId}-title">
                    <div class="consent-dialog-header">
                        ${iconHTML}
                        <h2 id="${dialogId}-title" class="consent-dialog-title">${config.title || 'Consent Required'}</h2>
                        ${subtitleHTML}
                    </div>
                    <div class="consent-dialog-body">
                        <div class="consent-dialog-content">
                            ${config.content || 'Please provide your consent to continue.'}
                        </div>
                        <div class="consent-dialog-actions">
                            ${actionsHTML}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Creates actions HTML
     */
    createActionsHTML(actions) {
        if (actions.length === 0) {
            // Default actions
            actions = [
                { text: 'Deny', value: false, type: 'secondary' },
                { text: 'Allow', value: true, type: 'primary' }
            ];
        }
        
        return actions.map(action => {
            const buttonClass = `consent-dialog-button consent-dialog-button-${action.type || 'secondary'}`;
            return `
                <button 
                    class="${buttonClass}" 
                    data-action="${action.value}"
                    ${action.disabled ? 'disabled' : ''}
                >
                    ${action.text}
                </button>
            `;
        }).join('');
    }

    /**
     * Sets up dialog event handlers
     */
    setupDialogHandlers(dialogId, config) {
        const dialogElement = document.getElementById(dialogId);
        
        // Handle action buttons
        dialogElement.addEventListener('click', (event) => {
            if (event.target.matches('[data-action]')) {
                const actionValue = event.target.getAttribute('data-action');
                const result = actionValue === 'true' ? true : (actionValue === 'false' ? false : actionValue);
                
                this.closeDialog(dialogId, result);
            }
        });
        
        // Handle backdrop click
        if (config.backdrop && config.closable) {
            dialogElement.addEventListener('click', (event) => {
                if (event.target === dialogElement) {
                    this.closeDialog(dialogId, null);
                }
            });
        }
        
        // Handle escape key
        if (config.closable) {
            const escapeHandler = (event) => {
                if (event.key === 'Escape') {
                    this.closeDialog(dialogId, null);
                    document.removeEventListener('keydown', escapeHandler);
                }
            };
            
            document.addEventListener('keydown', escapeHandler);
        }
        
        // Focus management
        this.setupFocusManagement(dialogElement);
    }

    /**
     * Sets up focus management for accessibility
     */
    setupFocusManagement(dialogElement) {
        // Focus first button
        const firstButton = dialogElement.querySelector('.consent-dialog-button');
        if (firstButton) {
            firstButton.focus();
        }
        
        // Trap focus within dialog
        const focusableElements = dialogElement.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        
        const firstFocusable = focusableElements[0];
        const lastFocusable = focusableElements[focusableElements.length - 1];
        
        dialogElement.addEventListener('keydown', (event) => {
            if (event.key === 'Tab') {
                if (event.shiftKey) {
                    if (document.activeElement === firstFocusable) {
                        event.preventDefault();
                        lastFocusable.focus();
                    }
                } else {
                    if (document.activeElement === lastFocusable) {
                        event.preventDefault();
                        firstFocusable.focus();
                    }
                }
            }
        });
    }

    /**
     * Closes a dialog
     */
    closeDialog(dialogId, result = null) {
        const dialogElement = this.activeDialogs.get(dialogId);
        if (!dialogElement) {
            console.warn('🔒 Dialog not found:', dialogId);
            return;
        }
        
        console.log('🔒 Closing consent dialog:', dialogId, 'Result:', result);
        
        // Dispatch result event
        const eventType = result !== null ? 'consent-result' : 'consent-error';
        const eventDetail = result !== null ? result : new Error('Dialog closed without result');
        
        dialogElement.dispatchEvent(new CustomEvent(eventType, { detail: eventDetail }));
        
        // Hide with animation
        dialogElement.classList.remove('show');
        
        // Remove from DOM after animation
        setTimeout(() => {
            if (dialogElement.parentNode) {
                dialogElement.parentNode.removeChild(dialogElement);
            }
            this.activeDialogs.delete(dialogId);
        }, 300);
    }

    /**
     * Shows STT consent dialog
     */
    showSTTConsentDialog(options = {}) {
        return this.showConsentDialog({
            title: 'Speech-to-Text Permission',
            subtitle: 'Enable live transcription',
            icon: '🎤',
            iconType: 'info',
            content: `
                <p>This room has speech-to-text enabled. We'll use your microphone to provide live transcription of the conversation.</p>
                <p><strong>Your privacy:</strong></p>
                <ul style="margin: 8px 0; padding-left: 20px;">
                    <li>Audio is processed in real-time</li>
                    <li>No audio recordings are stored</li>
                    <li>Transcripts are temporary and session-only</li>
                    <li>You can disable this at any time</li>
                </ul>
            `,
            actions: [
                { text: 'Deny', value: false, type: 'secondary' },
                { text: 'Allow Transcription', value: true, type: 'primary' }
            ],
            ...options
        });
    }

    /**
     * Shows video recording consent dialog
     */
    showVideoRecordingConsentDialog(options = {}) {
        return this.showConsentDialog({
            title: 'Video Recording Permission',
            subtitle: 'Record this session',
            icon: '🎥',
            iconType: 'warning',
            content: `
                <p>This room has video recording enabled. Your video and audio will be recorded for this session.</p>
                <p><strong>Recording details:</strong></p>
                <ul style="margin: 8px 0; padding-left: 20px;">
                    <li>Video and audio will be captured</li>
                    <li>Recording starts when you join</li>
                    <li>Files are saved according to room settings</li>
                    <li>You can leave at any time to stop your participation</li>
                </ul>
            `,
            actions: [
                { text: 'Deny', value: false, type: 'secondary' },
                { text: 'Allow Recording', value: true, type: 'danger' }
            ],
            ...options
        });
    }

    /**
     * Shows generic permission dialog
     */
    showPermissionDialog(feature, options = {}) {
        return this.showConsentDialog({
            title: `${feature} Permission`,
            subtitle: 'Permission required',
            icon: '🔒',
            iconType: 'info',
            content: `
                <p>This room requires permission to use <strong>${feature.toLowerCase()}</strong>.</p>
                <p>Please allow access to continue with full functionality.</p>
            `,
            actions: [
                { text: 'Deny', value: false, type: 'secondary' },
                { text: 'Allow', value: true, type: 'primary' }
            ],
            ...options
        });
    }

    /**
     * Shows error dialog
     */
    showErrorDialog(error, options = {}) {
        return this.showConsentDialog({
            title: 'Error',
            subtitle: 'Something went wrong',
            icon: '❌',
            iconType: 'error',
            content: `
                <p>An error occurred:</p>
                <p style="background: #fee2e2; padding: 12px; border-radius: 6px; font-family: monospace; font-size: 0.8rem;">
                    ${error.message || error}
                </p>
            `,
            actions: [
                { text: 'OK', value: true, type: 'primary' }
            ],
            ...options
        });
    }

    /**
     * Shows confirmation dialog
     */
    showConfirmationDialog(message, options = {}) {
        return this.showConsentDialog({
            title: 'Confirm Action',
            subtitle: 'Please confirm',
            icon: '❓',
            iconType: 'warning',
            content: `<p>${message}</p>`,
            actions: [
                { text: 'Cancel', value: false, type: 'secondary' },
                { text: 'Confirm', value: true, type: 'primary' }
            ],
            ...options
        });
    }

    /**
     * Closes all active dialogs
     */
    closeAllDialogs() {
        console.log('🔒 Closing all consent dialogs');
        
        const dialogIds = Array.from(this.activeDialogs.keys());
        dialogIds.forEach(dialogId => {
            this.closeDialog(dialogId, null);
        });
    }

    /**
     * Gets active dialog count
     */
    getActiveDialogCount() {
        return this.activeDialogs.size;
    }

    /**
     * Checks if any dialogs are active
     */
    hasActiveDialogs() {
        return this.activeDialogs.size > 0;
    }

    /**
     * Destroys the consent dialog manager
     */
    destroy() {
        console.log('🔒 Destroying ConsentDialog');
        
        // Close all dialogs
        this.closeAllDialogs();
        
        // Remove styles
        const styleElement = document.getElementById('consent-dialog-styles');
        if (styleElement) {
            styleElement.remove();
        }
        
        // Clear state
        this.activeDialogs.clear();
        this.dialogCounter = 0;
        
        console.log('🔒 ConsentDialog destroyed');
    }
}
