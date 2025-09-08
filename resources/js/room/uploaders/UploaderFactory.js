import { WasabiUploader } from './WasabiUploader.js';
import { GoogleDriveUploader } from './GoogleDriveUploader.js';
import { LocalUploader } from './LocalUploader.js';

/**
 * UploaderFactory - Factory class for creating upload provider instances
 * 
 * Centralizes the creation of uploader instances based on storage provider
 */
export class UploaderFactory {
    /**
     * Create an uploader instance for the specified provider
     * @param {string} provider - Storage provider name
     * @param {Object} roomData - Room configuration data
     * @param {Object} recordingSettings - Recording settings
     * @returns {BaseUploader}
     */
    static createUploader(provider, roomData, recordingSettings) {
        switch (provider) {
            case 'wasabi':
                return new WasabiUploader(roomData, recordingSettings);
                
            case 'google_drive':
                return new GoogleDriveUploader(roomData, recordingSettings);
                
            case 'local_device':
            case 'local':
                return new LocalUploader(roomData, recordingSettings);
                
            default:
                throw new Error(`Unsupported storage provider: ${provider}`);
        }
    }

    /**
     * Get list of supported storage providers
     * @returns {Array<string>}
     */
    static getSupportedProviders() {
        return ['wasabi', 'google_drive', 'local_device'];
    }

    /**
     * Check if a provider is supported
     * @param {string} provider - Storage provider name
     * @returns {boolean}
     */
    static isProviderSupported(provider) {
        return this.getSupportedProviders().includes(provider);
    }

    /**
     * Get provider display names for UI
     * @returns {Object}
     */
    static getProviderDisplayNames() {
        return {
            'wasabi': 'Wasabi Cloud Storage',
            'google_drive': 'Google Drive',
            'local_device': 'Local Device Download'
        };
    }

    /**
     * Get provider descriptions for UI
     * @returns {Object}
     */
    static getProviderDescriptions() {
        return {
            'wasabi': 'Upload directly to Wasabi S3-compatible cloud storage',
            'google_drive': 'Upload directly to your Google Drive account',
            'local_device': 'Download recording file to your local device'
        };
    }

    /**
     * Get provider capabilities
     * @returns {Object}
     */
    static getProviderCapabilities() {
        return {
            'wasabi': {
                supportsMultipart: true,
                requiresAuth: true,
                serverBandwidth: false,
                chunkSize: '30s',
                reliability: 'high'
            },
            'google_drive': {
                supportsMultipart: false,
                supportsResumable: true,
                requiresAuth: true,
                serverBandwidth: false,
                chunkSize: '30s',
                reliability: 'medium'
            },
            'local_device': {
                supportsMultipart: false,
                requiresAuth: false,
                serverBandwidth: false,
                chunkSize: '5s',
                reliability: 'high'
            }
        };
    }
}
