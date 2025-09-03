/**
 * SimpleImageUploader - Direct file upload handler that bypasses Uppy completely
 * 
 * Features:
 * - Direct fetch-based upload to our Laravel controller
 * - Progress tracking and loading states
 * - Error handling and user feedback
 * - Integration with AlpineJS character builder
 */
class SimpleImageUploader {
    constructor(characterBuilder, options = {}) {
        this.characterBuilder = characterBuilder;
        this.storageKey = options.storageKey;
        this.uploadInProgress = false;
        
        this.initializeUploader();
    }

    /**
     * Initialize the simple uploader
     */
    initializeUploader() {
        console.log('Initializing simple image uploader for:', this.storageKey);
        
        // Create file input element
        this.createFileInputElement();
        
        console.log(`📁 Simple image uploader ready for ${this.storageKey}`);
    }

    /**
     * Creates a hidden file input element
     */
    createFileInputElement() {
        // Create a hidden container for the file input
        this.fileInputContainer = document.createElement('div');
        this.fileInputContainer.style.display = 'none';
        this.fileInputContainer.id = `simple-file-input-${this.storageKey}`;
        
        // Create the actual file input
        this.fileInputElement = document.createElement('input');
        this.fileInputElement.type = 'file';
        this.fileInputElement.accept = 'image/jpeg,image/jpg,image/png,image/gif,image/webp';
        this.fileInputElement.id = `simple-input-${this.storageKey}`;
        
        // Set up file selection handler
        this.fileInputElement.onchange = (e) => {
            const file = e.target.files[0];
            if (file) {
                this.handleFileSelection(file);
            }
        };
        
        this.fileInputContainer.appendChild(this.fileInputElement);
        document.body.appendChild(this.fileInputContainer);
    }

    /**
     * Triggers file selection dialog
     */
    openFileDialog() {
        if (!this.fileInputElement) {
            console.error('File input not initialized');
            if (window.Toast) {
                Toast.danger('Upload system not ready. Please try again.');
            }
            return;
        }
        
        console.log('Opening file dialog');
        this.fileInputElement.click();
    }

    /**
     * Handles file selection and starts upload
     */
    async handleFileSelection(file) {
        console.log('File selected:', file.name, file.size, 'bytes');
        
        // Validate file
        if (!this.validateFile(file)) {
            return;
        }

        // Set uploading state
        this.characterBuilder.isUploadingImage = true;
        this.uploadInProgress = true;

        try {
            // Upload the file
            const result = await this.uploadFile(file);
            
            if (result.success) {
                // Update character builder with new image path
                this.characterBuilder.profile_image_path = result.image_path;
                this.characterBuilder.markAsUnsaved();
                
                // Refresh the Livewire component to update the image display
                if (this.characterBuilder.$wire && this.characterBuilder.$wire.refreshCharacter) {
                    this.characterBuilder.$wire.refreshCharacter();
                }
                
                // Show success message
                if (window.Toast) {
                    Toast.success('Image uploaded successfully!');
                }
                
                console.log('✅ Image upload successful:', result.image_path);
            } else {
                throw new Error(result.message || 'Upload failed');
            }
        } catch (error) {
            console.error('❌ Image upload failed:', error);
            
            if (window.Toast) {
                Toast.danger('Failed to upload image: ' + error.message);
            }
        } finally {
            // Reset uploading state
            this.characterBuilder.isUploadingImage = false;
            this.uploadInProgress = false;
        }
    }

    /**
     * Validates the selected file
     */
    validateFile(file) {
        // Check file size (2MB limit)
        const maxSize = 2 * 1024 * 1024; // 2MB
        if (file.size > maxSize) {
            if (window.Toast) {
                Toast.danger('Image must be smaller than 2MB');
            }
            return false;
        }

        // Check file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            if (window.Toast) {
                Toast.danger('Please select a valid image file (JPEG, PNG, GIF, or WebP)');
            }
            return false;
        }

        return true;
    }

    /**
     * Uploads the file using fetch API
     */
    async uploadFile(file) {
        const formData = new FormData();
        formData.append('profile_image', file);
        formData.append('character_key', this.storageKey);
        
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        const response = await fetch(`/character-builder/${this.storageKey}/upload-image`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: formData
        });

        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`Upload failed: ${response.status} ${errorText}`);
        }

        return await response.json();
    }

    /**
     * Clears the profile image
     */
    clearImage() {
        // Call Livewire method to clear the image
        if (this.characterBuilder.$wire && this.characterBuilder.$wire.clearProfileImage) {
            this.characterBuilder.$wire.clearProfileImage();
        }
        
        // Mark as unsaved
        this.characterBuilder.markAsUnsaved();
    }

    /**
     * Destroys the uploader and cleans up resources
     */
    destroy() {
        // Clean up file input elements
        if (this.fileInputContainer && this.fileInputContainer.parentNode) {
            this.fileInputContainer.parentNode.removeChild(this.fileInputContainer);
        }
        this.fileInputContainer = null;
        this.fileInputElement = null;
        
        this.uploadInProgress = false;
    }
}

export default SimpleImageUploader;
