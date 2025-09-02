/**
 * Character Builder AlpineJS Component
 * Handles character creation state management and unsaved changes tracking
 */
export function characterBuilderComponent($wire) {
    return {
        // Livewire entangled properties
        selected_class: $wire.entangle('character.selected_class'),
        selected_subclass: $wire.entangle('character.selected_subclass'),
        selected_ancestry: $wire.entangle('character.selected_ancestry'),
        selected_community: $wire.entangle('character.selected_community'),
        assigned_traits: $wire.entangle('character.assigned_traits'),
        
        // Local state
        currentStep: 1,
        hasUnsavedChanges: false,
        lastSavedState: null,
        
        // Trait assignment data
        draggedValue: null,
        availableValues: [-1, 0, 0, 1, 1, 2],
        
        // Heritage selection data
        hasSelectedAncestry: false,
        hasSelectedCommunity: false,

        /**
         * Initialize the component
         */
        init() {
            this.hasSelectedAncestry = !!this.selected_ancestry;
            this.hasSelectedCommunity = !!this.selected_community;
            
            // Capture initial state for unsaved changes tracking
            this.captureCurrentState();
            
            // Set up beforeunload warning for unsaved changes
            window.addEventListener('beforeunload', (e) => {
                if (this.hasUnsavedChanges) {
                    e.preventDefault();
                    e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
                    return 'You have unsaved changes. Are you sure you want to leave?';
                }
            });
            
            // Listen for character save events to reset unsaved state
            this.$wire.$on('character-saved', () => {
                this.hasUnsavedChanges = false;
                // Recapture current state as the new baseline
                setTimeout(() => {
                    this.captureCurrentState();
                }, 100);
            });
            
            // Also listen for successful save notifications
            this.$wire.$on('notify', (data) => {
                if (data.type === 'success' && data.message === 'Character saved successfully!') {
                    this.hasUnsavedChanges = false;
                    setTimeout(() => {
                        this.captureCurrentState();
                    }, 100);
                }
            });
            
            // Watch for changes that should mark as unsaved
            this.$watch('selected_class', () => this.markAsUnsaved());
            this.$watch('selected_subclass', () => this.markAsUnsaved());
            this.$watch('selected_ancestry', (value) => {
                this.hasSelectedAncestry = !!value;
                this.markAsUnsaved();
            });
            this.$watch('selected_community', (value) => {
                this.hasSelectedCommunity = !!value;
                this.markAsUnsaved();
            });
            this.$watch('assigned_traits', () => this.markAsUnsaved(), { deep: true });
        },

        /**
         * Computed properties
         */
        get hasSelectedClass() {
            return !!this.selected_class;
        },

        get remainingValues() {
            let remaining = [...this.availableValues];
            Object.values(this.assigned_traits).forEach(value => {
                const index = remaining.indexOf(value);
                if (index > -1) remaining.splice(index, 1);
            });
            return remaining;
        },

        /**
         * Unsaved changes tracking methods
         */
        captureCurrentState() {
            this.lastSavedState = JSON.stringify({
                selected_class: this.selected_class,
                selected_subclass: this.selected_subclass,
                selected_ancestry: this.selected_ancestry,
                selected_community: this.selected_community,
                assigned_traits: this.assigned_traits
            });
        },

        markAsUnsaved() {
            // Only mark as unsaved if we have a baseline to compare against
            if (this.lastSavedState) {
                const currentState = JSON.stringify({
                    selected_class: this.selected_class,
                    selected_subclass: this.selected_subclass,
                    selected_ancestry: this.selected_ancestry,
                    selected_community: this.selected_community,
                    assigned_traits: this.assigned_traits
                });
                this.hasUnsavedChanges = currentState !== this.lastSavedState;
            }
        },

        /**
         * Character selection methods
         */
        selectClass(classKey) {
            this.selected_class = classKey;
            this.selected_subclass = null;
            this.$wire.selectClass(classKey);
            
            // Mark as unsaved immediately
            this.markAsUnsaved();
            
            // Scroll to top of content when selecting a class
            if (classKey) {
                document.getElementById('character-builder-content')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        },

        selectSubclass(subclassKey) {
            this.selected_subclass = subclassKey;
            this.$wire.selectSubclass(subclassKey);
            
            // Mark as unsaved immediately
            this.markAsUnsaved();
        },

        selectAncestry(ancestryKey) {
            this.selected_ancestry = ancestryKey;
            this.$wire.selectAncestry(ancestryKey);
            
            // Mark as unsaved immediately
            this.markAsUnsaved();
            
            if (ancestryKey) {
                document.getElementById('character-builder-content')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        },

        selectCommunity(communityKey) {
            this.selected_community = communityKey;
            this.$wire.selectCommunity(communityKey);
            
            // Mark as unsaved immediately
            this.markAsUnsaved();
            
            if (communityKey) {
                document.getElementById('character-builder-content')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        },

        /**
         * Navigation methods
         */
        goToStep(step) {
            this.currentStep = step;
        },

        /**
         * Trait assignment methods
         */
        startDrag(value) {
            this.draggedValue = value;
        },

        allowDrop(event) {
            event.preventDefault();
        },

        drop(event, traitKey) {
            event.preventDefault();
            if (this.draggedValue !== null) {
                this.$wire.assignTrait(traitKey, this.draggedValue);
                this.draggedValue = null;
            }
        },

        removeValue(traitKey) {
            this.$wire.assignTrait(traitKey, null);
        },

        /**
         * Additional trait assignment methods for drop functionality
         */
        canDropValue(traitKey, value) {
            return this.remainingValues.includes(value) || this.assigned_traits[traitKey] === value;
        },

        dropValue(traitKey, value) {
            if (this.canDropValue(traitKey, value)) {
                this.$wire.assignTrait(traitKey, value);
            }
        }
    };
}
