/**
 * Character Level Up AlpineJS Component
 * Handles character leveling state management and multi-step progression
 */
export function characterLevelUpComponent($wire, options = {}) {
    return {
        // Livewire entangled properties
        currentStep: $wire.entangle('current_step'),
        firstAdvancement: $wire.entangle('first_advancement'),
        secondAdvancement: $wire.entangle('second_advancement'),
        advancementChoices: $wire.entangle('advancement_choices'),
        
        // Local state
        availableSlots: options.availableSlots || 2,
        showValidationError: false,
        
        /**
         * Initialize the component
         */
        init() {
            // Component is ready
            console.log('Character Level Up component initialized');
        },

        /**
         * Step navigation methods
         */
        goToStep(step) {
            this.currentStep = step;
        },
        
        canGoNext() {
            if (this.currentStep === 'first_advancement') {
                return this.firstAdvancement !== null;
            }
            if (this.currentStep === 'second_advancement') {
                return this.secondAdvancement !== null;
            }
            return true;
        },
        
        async nextStep() {
            if (this.currentStep === 'tier_achievements') {
                // Client-side validation first (more reliable than server state)
                const hasExperience = this.advancementChoices?.tier_experience && 
                                    typeof this.advancementChoices.tier_experience === 'object' &&
                                    this.advancementChoices.tier_experience.name && 
                                    this.advancementChoices.tier_experience.name.trim() !== '';
                
                const hasDomainCard = this.advancementChoices?.tier_domain_card && 
                                    typeof this.advancementChoices.tier_domain_card === 'string' &&
                                    this.advancementChoices.tier_domain_card.trim() !== '';
                
                if (!hasExperience || !hasDomainCard) {
                    this.showValidationError = true;
                    this.scrollToValidationError();
                    this.autoHideValidationError();
                    return;
                }
                
                this.currentStep = 'first_advancement';
            } else if (this.currentStep === 'first_advancement' && this.firstAdvancement !== null) {
                this.currentStep = 'second_advancement';
            } else if (this.currentStep === 'second_advancement' && this.secondAdvancement !== null) {
                this.currentStep = 'confirmation';
            }
        },
        
        previousStep() {
            if (this.currentStep === 'first_advancement') {
                this.currentStep = 'tier_achievements';
            } else if (this.currentStep === 'second_advancement') {
                this.currentStep = 'first_advancement';
            } else if (this.currentStep === 'confirmation') {
                this.currentStep = 'second_advancement';
            }
        },

        /**
         * Advancement selection methods
         */
        selectAdvancement(optionIndex, step) {
            if (step === 'first') {
                this.firstAdvancement = optionIndex;
                $wire.selectFirstAdvancement(optionIndex);
            } else if (step === 'second') {
                this.secondAdvancement = optionIndex;
                $wire.selectSecondAdvancement(optionIndex);
            }
        },
        
        getSelectedAdvancements() {
            return [this.firstAdvancement, this.secondAdvancement].filter(x => x !== null);
        },

        /**
         * Validation error handling
         */
        scrollToValidationError() {
            setTimeout(() => {
                const footer = document.querySelector('.border-t.border-slate-700');
                if (footer) {
                    footer.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'end' 
                    });
                }
            }, 100);
        },
        
        autoHideValidationError() {
            setTimeout(() => {
                this.showValidationError = false;
            }, 5000);
        },

        /**
         * Experience creation methods (for tier achievements)
         */
        createTierExperience(experienceName, experienceDescription) {
            if (!experienceName || experienceName.trim() === '') {
                return { success: false, error: 'Experience name is required.' };
            }
            
            if (experienceName.length > 100) {
                return { success: false, error: 'Experience name must be 100 characters or less.' };
            }
            
            // Store locally without server call
            this.advancementChoices.tier_experience = {
                name: experienceName.trim(),
                description: experienceDescription.trim(),
                modifier: 2
            };
            
            return { success: true };
        },
        
        removeTierExperience() {
            this.advancementChoices.tier_experience = null;
        },

        /**
         * Domain card selection methods (for tier achievements)
         */
        selectTierDomainCard(cardKey) {
            this.advancementChoices.tier_domain_card = cardKey;
        },
        
        removeTierDomainCard() {
            this.advancementChoices.tier_domain_card = null;
        }
    };
}
