import Uppy from '@uppy/core';
import XHRUpload from '@uppy/xhr-upload';

/**
 * CharacterImageUppy - Specialized image upload handler for character profiles
 * 
 * Features:
 * - Single image upload with preview
 * - Progress indicators and loading states
 * - Integration with character builder state
 * - Automatic character save after upload
 */
class CharacterImageUppy {
    constructor(characterBuilderComponent, options = {}) {
        this.characterBuilder = characterBuilderComponent;
        this.storageKey = options.storageKey;
        this.uppy = null;
        this.uploadInProgress = false;
        
        this.initializeUppy();
    }

    /**
     * Initializes Uppy with character-specific configuration
     */
    initializeUppy() {
        console.log('Initializing Uppy with core:', Uppy);
        
        try {
            this.uppy = new Uppy({
                id: `character-${this.storageKey}-image-uploader`,
                autoProceed: true, // Auto-start upload when file is selected
                allowMultipleUploadBatches: false,
                debug: process.env.NODE_ENV === 'development',
                restrictions: {
                    maxFileSize: 2 * 1024 * 1024, // 2MB limit
                    maxNumberOfFiles: 1, // Only one profile image at a time
                    allowedFileTypes: ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'],
                },
                meta: {
                    character_key: this.storageKey,
                    upload_type: 'character_profile_image',
                },
            });

            // Create file input element for manual handling
            this.createFileInputElement();

            // Configure upload endpoint
            this.configureUpload();
            
            // Set up event handlers
            this.setupEventHandlers();

            console.log(`🖼️ Character image uploader initialized for ${this.storageKey}`);
        } catch (error) {
            console.error('Failed to initialize Uppy:', error);
            this.uppy = null;
        }
    }

    /**
     * Creates a hidden file input element for Uppy to attach to
     */
    createFileInputElement() {
        // Create a hidden container for the file input
        this.fileInputContainer = document.createElement('div');
        this.fileInputContainer.style.display = 'none';
        this.fileInputContainer.id = `uppy-file-input-${this.storageKey}`;
        
        // Create the actual file input
        this.fileInputElement = document.createElement('input');
        this.fileInputElement.type = 'file';
        this.fileInputElement.accept = 'image/jpeg,image/jpg,image/png,image/gif,image/webp';
        this.fileInputElement.id = `uppy-input-${this.storageKey}`;
        
        this.fileInputContainer.appendChild(this.fileInputElement);
        document.body.appendChild(this.fileInputContainer);
    }

    /**
     * Configures the upload strategy - use local endpoint for now
     */
    configureUpload() {
        this.uppy.use(XHRUpload, {
            id: 'CharacterImageXHR',
            endpoint: `/character-builder/${this.storageKey}/upload-image`,
            method: 'POST',
            formData: true,
            fieldName: 'profile_image',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                'Accept': 'application/json',
            },
            getResponseData: (responseText, response) => {
                try {
                    return JSON.parse(responseText);
                } catch (error) {
                    console.error('Failed to parse upload response:', error);
                    return { success: false, error: 'Invalid response format' };
                }
            }
        });
    }

    /**
     * Sets up event handlers for upload lifecycle
     */
    setupEventHandlers() {
        this.uppy.on('file-added', (file) => {
            console.log('📁 Character image added:', file.name);
            
            // Clear any existing files (single image only)
            const existingFiles = this.uppy.getFiles();
            existingFiles.forEach(existingFile => {
                if (existingFile.id !== file.id) {
                    this.uppy.removeFile(existingFile.id);
                }
            });
            
            // Show preview immediately
            this.showImagePreview(file);
        });

        this.uppy.on('upload-started', () => {
            console.log('📤 Character image upload started');
            this.uploadInProgress = true;
            this.characterBuilder.isUploadingImage = true;
            this.showUploadProgress();
        });

        this.uppy.on('upload-progress', (file, progress) => {
            const percentage = Math.round((progress.bytesUploaded / progress.bytesTotal) * 100);
            console.log(`📤 Upload progress: ${percentage}%`);
            this.updateUploadProgress(percentage);
        });

        this.uppy.on('upload-success', (file, response) => {
            console.log('✅ Character image upload successful:', response);
            this.handleUploadSuccess(file, response);
        });

        this.uppy.on('upload-error', (file, error, response) => {
            console.error('❌ Character image upload failed:', error);
            this.handleUploadError(file, error, response);
        });

        this.uppy.on('complete', (result) => {
            console.log('🎉 Character image upload completed:', result);
            this.uploadInProgress = false;
            this.characterBuilder.isUploadingImage = false;
            this.hideUploadProgress();
        });
    }

    /**
     * Triggers file selection dialog
     */
    openFileDialog() {
        // Check if Uppy is initialized
        if (!this.uppy || !this.fileInputElement) {
            console.error('Uppy or file input not initialized');
            Toast.danger('Upload system not ready. Please try again.');
            return;
        }
        
        // Trigger the hidden file input that Uppy is managing
        console.log('Triggering Uppy file input click');
        this.fileInputElement.click();
    }

    /**
     * Shows immediate image preview from blob
     */
    showImagePreview(file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            // Update the character builder with preview URL
            const previewContainer = document.querySelector('.character-image-preview');
            if (previewContainer) {
                const img = previewContainer.querySelector('img') || document.createElement('img');
                img.src = e.target.result;
                img.alt = 'Profile preview';
                img.className = 'w-full h-full object-cover';
                
                if (!previewContainer.querySelector('img')) {
                    previewContainer.appendChild(img);
                }
                
                // Hide upload area, show preview
                this.toggleUploadUI(false);
            }
        };
        reader.readAsDataURL(file.data);
    }

    /**
     * Shows upload progress overlay
     */
    showUploadProgress() {
        // The overlay is handled by the character builder component
        // We just need to make sure the state is set
        this.characterBuilder.isUploadingImage = true;
    }

    /**
     * Updates upload progress percentage
     */
    updateUploadProgress(percentage) {
        // Emit progress event for any listening components
        const progressEvent = new CustomEvent('character-image-upload-progress', {
            detail: { percentage }
        });
        document.dispatchEvent(progressEvent);
    }

    /**
     * Hides upload progress overlay
     */
    hideUploadProgress() {
        this.characterBuilder.isUploadingImage = false;
    }

    /**
     * Handles successful upload
     */
    async handleUploadSuccess(file, response) {
        try {
            if (response.body && response.body.success) {
                // Update character builder with new image path
                this.characterBuilder.profile_image_path = response.body.image_path;
                this.characterBuilder.markAsUnsaved();
                
                // Show success notification
                Toast.success('Image uploaded successfully!');
                
                // Clean up Uppy
                this.uppy.removeFile(file.id);
                
                // Emit success event
                const successEvent = new CustomEvent('character-image-upload-success', {
                    detail: { 
                        imagePath: response.body.image_path,
                        filename: file.name 
                    }
                });
                document.dispatchEvent(successEvent);
                
            } else {
                throw new Error(response.body?.error || 'Upload failed');
            }
        } catch (error) {
            console.error('Error handling upload success:', error);
            this.handleUploadError(file, error, response);
        }
    }

    /**
     * Handles upload errors
     */
    handleUploadError(file, error, response) {
        // Show error notification
        const errorMessage = error.message || response?.body?.error || 'Upload failed';
        Toast.danger('Failed to upload image: ' + errorMessage);
        
        // Reset upload state
        this.uploadInProgress = false;
        this.characterBuilder.isUploadingImage = false;
        
        // Remove failed file
        this.uppy.removeFile(file.id);
        
        // Emit error event
        const errorEvent = new CustomEvent('character-image-upload-error', {
            detail: { error: errorMessage, file }
        });
        document.dispatchEvent(errorEvent);
    }

    /**
     * Toggles between upload UI and image preview
     */
    toggleUploadUI(showUpload = true) {
        const uploadArea = document.querySelector('.character-image-upload-area');
        const previewArea = document.querySelector('.character-image-preview-area');
        
        if (uploadArea && previewArea) {
            if (showUpload) {
                uploadArea.style.display = 'block';
                previewArea.style.display = 'none';
            } else {
                uploadArea.style.display = 'none';
                previewArea.style.display = 'block';
            }
        }
    }

    /**
     * Clears current image and resets to upload state
     */
    clearImage() {
        // Clear Uppy files
        this.uppy.reset();
        
        // Reset character builder state
        this.characterBuilder.profile_image_path = null;
        this.characterBuilder.markAsUnsaved();
        
        // Reset UI
        this.toggleUploadUI(true);
        
        // Clear preview
        const previewContainer = document.querySelector('.character-image-preview');
        if (previewContainer) {
            const img = previewContainer.querySelector('img');
            if (img) {
                img.remove();
            }
        }
        
        console.log('🗑️ Character image cleared');
    }

    /**
     * Destroys Uppy instance and cleans up
     */
    destroy() {
        if (this.uppy) {
            this.uppy.destroy();
            this.uppy = null;
        }
        
        // Clean up file input elements
        if (this.fileInputContainer && this.fileInputContainer.parentNode) {
            this.fileInputContainer.parentNode.removeChild(this.fileInputContainer);
        }
        this.fileInputContainer = null;
        this.fileInputElement = null;
        
        this.uploadInProgress = false;
    }

    /**
     * Gets current upload state
     */
    isUploading() {
        return this.uploadInProgress;
    }
}

export default CharacterImageUppy;
