/**
 * Character Builder AlpineJS Component
 * Handles character creation state management and unsaved changes tracking
 */
export function characterBuilderComponent($wire, gameData = {}) {
    return {
        // Livewire entangled properties
        selected_class: $wire.entangle('character.selected_class'),
        selected_subclass: $wire.entangle('character.selected_subclass'),
        selected_ancestry: $wire.entangle('character.selected_ancestry'),
        selected_community: $wire.entangle('character.selected_community'),
        assigned_traits: $wire.entangle('character.assigned_traits'),
        background_answers: $wire.entangle('character.background_answers'),
        physical_description: $wire.entangle('character.physical_description'),
        personality_traits: $wire.entangle('character.personality_traits'),
        personal_history: $wire.entangle('character.personal_history'),
        motivations: $wire.entangle('character.motivations'),
        experiences: $wire.entangle('character.experiences'),
        clank_bonus_experience: $wire.entangle('character.clank_bonus_experience'),
        
        // Higher-level character creation properties
        starting_level: $wire.entangle('character.starting_level'),
        creation_advancements: $wire.entangle('character.creation_advancements'),
        creation_tier_experiences: $wire.entangle('character.creation_tier_experiences'),
        creation_domain_cards: $wire.entangle('character.creation_domain_cards'),
        // Initialize creation_advancement_cards as empty object
        // Entangling happens after initialization
        creation_advancement_cards: {},
        
        // Client-side experience editing properties
        new_experience_name: '',
        new_experience_description: '',
        editing_experience: null,
        edit_experience_description: '',
        connection_answers: $wire.entangle('character.connection_answers'),
        selected_domain_cards: $wire.entangle('character.selected_domain_cards'),
        selected_equipment: $wire.entangle('character.selected_equipment'),
        name: $wire.entangle('character.name'),
        pronouns: $wire.entangle('pronouns'),
        profile_image_path: $wire.entangle('character.profile_image_path'),
        profile_image: $wire.entangle('profile_image'),
        
        // Local state
        currentStep: 1,
        hasUnsavedChanges: false,
        lastSavedState: null,
        isSaving: false,
        isUploadingImage: false,
        
        // Image upload handler
        imageUploader: null,
        
        // Game data (loaded from JSON files)
        gameData: gameData,
        
        // Performance optimization: cache frequently accessed computed values
        _cachedClassData: null,
        _cachedAncestryData: null,
        _cachedCommunityData: null,
        
        // Trait assignment data
        draggedValue: null,
        selectedValue: null,
        availableValues: [-1, 0, 0, 1, 1, 2],
        
        // Heritage selection data
        hasSelectedAncestry: false,
        hasSelectedCommunity: false,

        /**
         * Initialize the component
         */
        async init() {
            this.hasSelectedAncestry = !!this.selected_ancestry;
            this.hasSelectedCommunity = !!this.selected_community;
            
            // Entangle creation_advancement_cards after component is initialized
            this.$nextTick(() => {
                if (this.$wire && this.$wire.entangle) {
                    try {
                        this.creation_advancement_cards = this.$wire.entangle('character.creation_advancement_cards');
                    } catch (e) {
                        console.warn('Could not entangle creation_advancement_cards:', e);
                        this.creation_advancement_cards = {};
                    }
                }
            });
            
            // Initialize image uploader
            if (window.SimpleImageUploader) {
                console.log('Initializing simple image uploader with storage key:', this.$wire.storage_key);
                this.imageUploader = new window.SimpleImageUploader(this, {
                    storageKey: this.$wire.storage_key
                });
            } else {
                console.error('SimpleImageUploader not available on window');
            }
            
            // Load advancements from database for existing characters
            if (this.starting_level > 1) {
                await this.loadAdvancementsFromDatabase();
            }
            
            // Capture initial state for unsaved changes tracking
            this.captureCurrentState();
            
            // Set up beforeunload warning for unsaved changes
            // Skip in development/local environments to allow easier testing
            const isDevelopment = window.location.hostname === 'localhost' || 
                                 window.location.hostname === '127.0.0.1' ||
                                 window.location.hostname.includes('.local');
            
            if (!isDevelopment) {
                window.addEventListener('beforeunload', (e) => {
                    if (this.hasUnsavedChanges) {
                        e.preventDefault();
                        e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
                        return 'You have unsaved changes. Are you sure you want to leave?';
                    }
                });
            }
            
            // Listen for character save events to reset unsaved state
            this.$wire.$on('character-saved', () => {
                this.hasUnsavedChanges = false;
                this.isSaving = false;
                // Recapture current state as the new baseline after a delay to ensure all entangled properties have synced
                setTimeout(() => {
                    this.captureCurrentState();
                }, 100); // Reduced delay since entangled properties should sync faster
            });
            
            // Listen for image upload events
            this.$wire.$on('upload:start', () => {
                this.isUploadingImage = true;
            });
            
            this.$wire.$on('upload:finish', () => {
                this.isUploadingImage = false;
                this.markAsUnsaved(); // Image upload should trigger unsaved state
            });
            
            this.$wire.$on('upload:error', () => {
                this.isUploadingImage = false;
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
            this.$watch('selected_domain_cards', () => this.markAsUnsaved(), { deep: true });
            this.$watch('selected_equipment', () => this.markAsUnsaved(), { deep: true });
            this.$watch('name', () => this.markAsUnsaved());
            this.$watch('pronouns', () => this.markAsUnsaved());
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
         * Client-side filtering methods (moved from Livewire)
         */
        get availableSubclasses() {
            if (!this.selected_class || !this.gameData.classes?.[this.selected_class]?.subclasses) {
                return {};
            }
            
            const classSubclasses = this.gameData.classes[this.selected_class].subclasses;
            const filtered = {};
            
            classSubclasses.forEach(subclassKey => {
                if (this.gameData.subclasses?.[subclassKey]) {
                    filtered[subclassKey] = this.gameData.subclasses[subclassKey];
                }
            });
            
            return filtered;
        },

        get selectedClassData() {
            if (!this.selected_class) {
                this._cachedClassData = null;
                return null;
            }
            // Cache the result to avoid repeated lookups during rendering
            if (this._cachedClassData?.key !== this.selected_class) {
                this._cachedClassData = {
                    key: this.selected_class,
                    data: this.gameData.classes?.[this.selected_class] || null
                };
            }
            return this._cachedClassData.data;
        },

        get selectedSubclassData() {
            return this.selected_subclass && this.gameData.subclasses?.[this.selected_subclass]
                ? this.gameData.subclasses[this.selected_subclass]
                : null;
        },

        get selectedAncestryData() {
            if (!this.selected_ancestry) {
                this._cachedAncestryData = null;
                return null;
            }
            // Cache ancestry data
            if (this._cachedAncestryData?.key !== this.selected_ancestry) {
                this._cachedAncestryData = {
                    key: this.selected_ancestry,
                    data: this.gameData.ancestries?.[this.selected_ancestry] || null
                };
            }
            return this._cachedAncestryData.data;
        },

        get selectedCommunityData() {
            if (!this.selected_community) {
                this._cachedCommunityData = null;
                return null;
            }
            // Cache community data
            if (this._cachedCommunityData?.key !== this.selected_community) {
                this._cachedCommunityData = {
                    key: this.selected_community,
                    data: this.gameData.communities?.[this.selected_community] || null
                };
            }
            return this._cachedCommunityData.data;
        },

        get suggestedPrimaryWeapon() {
            if (!this.selectedClassData?.suggestedWeapons?.primary) return null;
            
            const suggestion = this.selectedClassData.suggestedWeapons.primary;
            const weaponKey = suggestion.name.toLowerCase();
            const weaponData = this.gameData.weapons?.[weaponKey];
            
            return weaponData ? { weaponKey, weaponData, suggestion } : null;
        },

        get availableWeapons() {
            return this.gameData.weapons || {};
        },

        get availableArmor() {
            return this.gameData.armor || {};
        },

        /**
         * Equipment filtering methods
         */
        get suggestedSecondaryWeapon() {
            if (!this.selectedClassData?.suggestedWeapons?.secondary) return null;
            
            const suggestion = this.selectedClassData.suggestedWeapons.secondary;
            const weaponKey = suggestion.name.toLowerCase();
            const weaponData = this.gameData.weapons?.[weaponKey];
            
            return weaponData ? { weaponKey, weaponData, suggestion } : null;
        },

        get suggestedArmor() {
            if (!this.selectedClassData?.suggestedArmor) return null;
            
            const suggestion = this.selectedClassData.suggestedArmor;
            const armorKey = suggestion.name.toLowerCase();
            const armorData = this.gameData.armor?.[armorKey];
            
            return armorData ? { armorKey, armorData, suggestion } : null;
        },

        get tier1PrimaryWeapons() {
            const weapons = {};
            // Get the suggested weapon key to exclude it from the list
            const suggestedKey = this.suggestedPrimaryWeapon?.weaponKey;
            
            for (const [key, weapon] of Object.entries(this.gameData.weapons || {})) {
                if ((weapon.tier || 1) === 1 && (weapon.type || 'Primary') === 'Primary' && key !== suggestedKey) {
                    weapons[key] = weapon;
                }
            }
            return weapons;
        },

        get tier1SecondaryWeapons() {
            const weapons = {};
            // Get the suggested weapon key to exclude it from the list
            const suggestedKey = this.suggestedSecondaryWeapon?.weaponKey;
            
            for (const [key, weapon] of Object.entries(this.gameData.weapons || {})) {
                if ((weapon.tier || 1) === 1 && weapon.type === 'Secondary' && key !== suggestedKey) {
                    weapons[key] = weapon;
                }
            }
            return weapons;
        },

        get tier1Armor() {
            const armor = {};
            // Get the suggested armor key to exclude it from the list
            const suggestedKey = this.suggestedArmor?.armorKey;
            
            for (const [key, armorPiece] of Object.entries(this.gameData.armor || {})) {
                if ((armorPiece.tier || 1) === 1 && key !== suggestedKey) {
                    armor[key] = armorPiece;
                }
            }
            return armor;
        },

        get classStartingInventory() {
            return this.selectedClassData?.startingInventory || null;
        },

        get allClasses() {
            return this.gameData.classes || {};
        },

        get allSubclasses() {
            return this.gameData.subclasses || {};
        },

        get selectedSubclassData() {
            return this.selected_subclass ? this.allSubclasses[this.selected_subclass] : null;
        },

        get allAncestries() {
            return this.gameData.ancestries || {};
        },

        get selectedAncestryData() {
            return this.selected_ancestry ? this.allAncestries[this.selected_ancestry] : null;
        },

        get allCommunities() {
            return this.gameData.communities || {};
        },

        get selectedCommunityData() {
            return this.selected_community ? this.allCommunities[this.selected_community] : null;
        },

        get traitsData() {
            return {
                'agility': {
                    'name': 'AGILITY',
                    'description': 'Dexterity, speed, and finesse in movement and stealth.',
                    'icon': '🏃'
                },
                'strength': {
                    'name': 'STRENGTH', 
                    'description': 'Physical power, endurance, and raw might.',
                    'icon': '💪'
                },
                'finesse': {
                    'name': 'FINESSE',
                    'description': 'Grace, precision, and fine motor control.',
                    'icon': '🎯'
                },
                'instinct': {
                    'name': 'INSTINCT',
                    'description': 'Intuition, awareness, and gut reactions.',
                    'icon': '👁️'
                },
                'presence': {
                    'name': 'PRESENCE',
                    'description': 'Charisma, leadership, and force of personality.',
                    'icon': '✨'
                },
                'knowledge': {
                    'name': 'KNOWLEDGE',
                    'description': 'Learning, memory, and reasoning ability.',
                    'icon': '📚'
                }
            };
        },

        get backgroundQuestions() {
            return this.selectedClassData?.backgroundQuestions || [];
        },

        get totalQuestions() {
            return this.backgroundQuestions.length;
        },

        get answeredQuestions() {
            if (!this.background_answers) return 0;
            return Object.values(this.background_answers).filter(answer => answer && answer.trim().length > 0).length;
        },

        get progressPercentage() {
            return this.totalQuestions > 0 ? Math.round((this.answeredQuestions / this.totalQuestions) * 100) : 0;
        },

        get canMarkBackgroundComplete() {
            return this.answeredQuestions >= 1;
        },

        get isBackgroundComplete() {
            // This would need to be synced with the completed_steps from Livewire
            // For now, we'll handle this via Livewire methods
            return false;
        },

        get experienceCount() {
            return this.experiences ? this.experiences.length : 0;
        },

        get canAddExperience() {
            return this.experienceCount < 2;
        },

        get isExperienceComplete() {
            return this.experienceCount >= 2;
        },

        get isExperienceInProgress() {
            return this.experienceCount > 0 && this.experienceCount < 2;
        },

        get experiencesRemaining() {
            return Math.max(0, 2 - this.experienceCount);
        },

        get canAddNewExperience() {
            return this.new_experience_name && this.new_experience_name.trim().length > 0;
        },

        getExperienceModifier(experienceName) {
            // Base modifier is always +2
            let modifier = 2;
            
            // Check if this is a Clank bonus experience (gets +1 additional)
            if (this.selected_ancestry === 'clank' && this.clank_bonus_experience === experienceName) {
                modifier += 1;
            }
            
            // Check for advancement bonuses (+1 for each experience_bonus advancement selection)
            if (this.creation_advancements) {
                for (const level in this.creation_advancements) {
                    const advancements = this.creation_advancements[level];
                    if (Array.isArray(advancements)) {
                        advancements.forEach(adv => {
                            if (adv && adv.type === 'experience_bonus' && adv.experiences) {
                                if (adv.experiences.includes(experienceName)) {
                                    modifier += 1;
                                }
                            }
                        });
                    }
                }
            }
            
            return modifier;
        },

        get hasExperienceBonus() {
            // Clank ancestry gets to select one experience for +3 instead of +2
            return this.selected_ancestry === 'clank';
        },

        isEditingExperience(index) {
            return this.editing_experience === index;
        },

        isBonusExperience(experienceName) {
            return this.hasExperienceBonus && this.clank_bonus_experience === experienceName;
        },

        canSelectBonusExperience(experienceName) {
            return this.hasExperienceBonus && !this.clank_bonus_experience;
        },

        get connectionQuestions() {
            return this.selectedClassData?.connections || [];
        },

        get totalConnections() {
            return this.connectionQuestions.length;
        },

        get answeredConnections() {
            if (!this.connection_answers) return 0;
            return Object.values(this.connection_answers).filter(answer => answer && answer.trim().length > 0).length;
        },

        get isConnectionComplete() {
            return this.answeredConnections >= this.totalConnections && this.totalConnections > 0;
        },

        get hasCharacterName() {
            return this.name && this.name.trim().length > 0;
        },

        get hasProfileImage() {
            return this.profile_image || this.profile_image_path;
        },

        get hasBasicCharacterInfo() {
            return this.hasCharacterName || this.hasProfileImage;
        },

        get hasHeritage() {
            return this.selected_ancestry && this.selected_community;
        },

        get hasTraitsAssigned() {
            return this.assigned_traits && Object.keys(this.assigned_traits).length > 0;
        },

        get computedStats() {
            // This would be computed stats from Livewire, but for now we'll rely on the server
            // For client-side display we can use this getter
            return this.$wire.computed_stats || {};
        },

        get hasComputedStats() {
            return this.computedStats && Object.keys(this.computedStats).length > 0;
        },

        get classDomains() {
            if (!this.selectedClassData?.domains) return [];
            return this.selectedClassData.domains;
        },

        get filteredDomainCards() {
            if (!this.classDomains || this.classDomains.length === 0) return {};
            
            const domains = this.gameData.domains || {};
            const abilities = this.gameData.abilities || {};
            const filtered = {};

            this.classDomains.forEach(domainKey => {
                const domainData = domains[domainKey];
                if (domainData && domainData.levels) {
                    filtered[domainKey] = {
                        ...domainData,
                        abilities: {}
                    };

                    // Get abilities for this domain
                    Object.entries(abilities).forEach(([abilityKey, abilityData]) => {
                        if (abilityData.domain === domainKey) {
                            filtered[domainKey].abilities[abilityKey] = abilityData;
                        }
                    });
                }
            });

            return filtered;
        },

        // Note: selectedDomainCards is now an entangled property, not a computed one

        get maxDomainCards() {
            // Base 2 cards + any subclass bonuses
            let max = 2;
            if (this.selectedSubclassData?.domainCardBonus) {
                max += this.selectedSubclassData.domainCardBonus;
            }
            return max;
        },

        /**
         * Equipment selection state and methods
         */
        selected_equipment: [],
        
        /**
         * Equipment progress computed properties
         */
        get selectedPrimary() {
            return this.selected_equipment.some(eq => eq.type === 'weapon' && (eq.data?.type ?? 'Primary') === 'Primary');
        },
        
        get selectedSecondary() {
            return this.selected_equipment.some(eq => eq.type === 'weapon' && (eq.data?.type ?? '') === 'Secondary');
        },
        
        get selectedArmor() {
            return this.selected_equipment.some(eq => eq.type === 'armor');
        },
        
        get equipmentComplete() {
            return this.selectedPrimary && this.selectedArmor;
        },

        /**
         * Unsaved changes tracking methods
         */
        captureCurrentState() {
            this.lastSavedState = JSON.stringify({
                // Step 1: Class selection
                selected_class: this.selected_class,
                selected_subclass: this.selected_subclass,
                
                // Step 2: Heritage (Ancestry + Community)
                selected_ancestry: this.selected_ancestry,
                selected_community: this.selected_community,
                
                // Step 3: Traits
                assigned_traits: this.assigned_traits,
                
                // Step 4: Character Details
                name: this.name,
                pronouns: this.pronouns,
                profile_image_path: this.profile_image_path,
                
                // Step 5: Equipment
                selected_equipment: this.selected_equipment,
                
                // Step 6: Background
                background_answers: this.background_answers,
                physical_description: this.physical_description,
                personality_traits: this.personality_traits,
                personal_history: this.personal_history,
                motivations: this.motivations,
                
                // Step 7: Experiences
                experiences: this.experiences,
                clank_bonus_experience: this.clank_bonus_experience,
                
                // Step 8: Domain Cards
                selected_domain_cards: this.selected_domain_cards,
                
                // Step 9: Connections
                connection_answers: this.connection_answers
            });
        },

        markAsUnsaved() {
            // Only mark as unsaved if we have a baseline to compare against
            if (this.lastSavedState) {
                const currentState = JSON.stringify({
                    // Step 1: Class selection
                    selected_class: this.selected_class,
                    selected_subclass: this.selected_subclass,
                    
                    // Step 2: Heritage (Ancestry + Community)
                    selected_ancestry: this.selected_ancestry,
                    selected_community: this.selected_community,
                    
                    // Step 3: Traits
                    assigned_traits: this.assigned_traits,
                    
                    // Step 4: Character Details
                    name: this.name,
                    pronouns: this.pronouns,
                    profile_image_path: this.profile_image_path,
                    
                    // Step 5: Equipment
                    selected_equipment: this.selected_equipment,
                    
                    // Step 6: Background
                    background_answers: this.background_answers,
                    physical_description: this.physical_description,
                    personality_traits: this.personality_traits,
                    personal_history: this.personal_history,
                    motivations: this.motivations,
                    
                    // Step 7: Experiences
                    experiences: this.experiences,
                    clank_bonus_experience: this.clank_bonus_experience,
                    
                    // Step 8: Domain Cards
                    selected_domain_cards: this.selected_domain_cards,
                    
                    // Step 9: Connections
                    connection_answers: this.connection_answers
                });
                this.hasUnsavedChanges = currentState !== this.lastSavedState;
            }
            
            // Also trigger step completion update
            this.refreshStepCompletion();
        },

        refreshStepCompletion() {
            // Call Livewire method to refresh step completion status
            this.$wire.$refresh();
        },

        /**
         * Character selection methods
         */
        selectClass(classKey) {
            this.selected_class = classKey;
            this.selected_subclass = null; // Reset subclass when class changes
            
            // Mark as unsaved immediately (state-only update, no DB save)
            this.markAsUnsaved();
            
            // NOTE: No Livewire call needed - entangled properties handle server sync automatically
            
            // Scroll to top of content when selecting a class
            if (classKey) {
                document.getElementById('character-builder-content')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        },

        selectSubclass(subclassKey) {
            this.selected_subclass = subclassKey;
            
            // Mark as unsaved immediately
            this.markAsUnsaved();
            
            // NOTE: No Livewire call needed - entangled properties handle server sync automatically
        },

        selectAncestry(ancestryKey) {
            this.selected_ancestry = ancestryKey;
            this.hasSelectedAncestry = !!ancestryKey;
            
            // Mark as unsaved immediately
            this.markAsUnsaved();
            
            // NOTE: No Livewire call needed - entangled properties handle server sync automatically
            
            if (ancestryKey) {
                document.getElementById('character-builder-content')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        },

        selectCommunity(communityKey) {
            this.selected_community = communityKey;
            this.hasSelectedCommunity = !!communityKey;
            
            // Mark as unsaved immediately
            this.markAsUnsaved();
            
            // NOTE: No Livewire call needed - entangled properties handle server sync automatically
            
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
         * Check if a step is complete based on current character state
         * This mirrors the PHP logic in CharacterBuilderData::isStepComplete()
         */
        isStepComplete(step) {
            switch (step) {
                case 1: // Class
                    return !!this.selected_class;
                case 2: // Subclass
                    return !!this.selected_subclass;
                case 3: // Ancestry
                    return !!this.selected_ancestry;
                case 4: // Community
                    return !!this.selected_community;
                case 5: // Traits
                    return Object.values(this.assigned_traits).filter(v => v !== null).length === 6;
                case 6: // Equipment
                    return this.isEquipmentComplete();
                case 7: // Background
                    return this.background_answers && this.background_answers.length >= 3;
                case 8: // Experiences
                    return this.experiences && this.experiences.length >= 2;
                case 9: // Domain Cards
                    return this.selected_domain_cards && this.selected_domain_cards.length >= 2;
                case 11: // Connections
                    return this.connection_answers && this.connection_answers.length >= 2;
                default:
                    return false;
            }
        },
        
        /**
         * Check if equipment step is complete
         * Requires: primary weapon + armor
         * Mirrors PHP EquipmentValidator::isEquipmentComplete()
         */
        isEquipmentComplete() {
            if (!this.selected_equipment || !Array.isArray(this.selected_equipment)) {
                return false;
            }
            
            // Check for primary weapon (type==='weapon' && data.type==='Primary')
            const hasPrimary = this.selected_equipment.some(item => 
                item.type === 'weapon' && (item.data?.type || 'Primary') === 'Primary'
            );
            
            // Check for armor
            const hasArmor = this.selected_equipment.some(item => item.type === 'armor');
            
            // For now, just check primary + armor (starting inventory validation would require class data)
            return hasPrimary && hasArmor;
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
                this.assignTrait(traitKey, this.draggedValue);
                this.draggedValue = null;
            }
        },

        removeValue(traitKey) {
            this.assignTrait(traitKey, null);
        },

        /**
         * Client-side trait assignment logic
         */
        assignTrait(traitKey, value) {
            if (value === null) {
                // Remove trait assignment
                if (this.assigned_traits[traitKey] !== undefined) {
                    delete this.assigned_traits[traitKey];
                }
            } else {
                // Check if value is available
                if (this.canDropValue(traitKey, value)) {
                    // Remove existing assignment for this trait
                    if (this.assigned_traits[traitKey] !== undefined) {
                        delete this.assigned_traits[traitKey];
                    }
                    // Assign new value
                    this.assigned_traits[traitKey] = value;
                }
            }
            this.markAsUnsaved();
        },

        /**
         * Additional trait assignment methods for drop functionality
         */
        canDropValue(traitKey, value) {
            return this.remainingValues.includes(value) || this.assigned_traits[traitKey] === value;
        },

        dropValue(traitKey, value) {
            if (this.canDropValue(traitKey, value)) {
                this.assignTrait(traitKey, value);
            }
        },

        /**
         * Assign selected value to trait, or remove if already assigned
         */
        assignSelectedValue(traitKey) {
            // If trait already has value, remove it
            if (this.assigned_traits[traitKey] !== undefined) {
                this.removeValue(traitKey);
                return;
            }
            
            // Only assign if a value is actually selected
            if (this.selectedValue !== null) {
                this.assignTrait(traitKey, this.selectedValue);
                this.selectedValue = null; // Clear selection after assignment
            }
            // If no value is selected, do nothing (user needs to select a value first)
        },

        /**
         * Equipment selection methods
         */
        selectEquipment(key, type, data) {
            // Check if this item is already selected
            const isAlreadySelected = this.isEquipmentSelected(key, type);
            
            if (isAlreadySelected) {
                // Unselect: Remove the equipment
                if (type === 'weapon') {
                    const weaponType = data.type || 'Primary';
                    this.selected_equipment = this.selected_equipment.filter(eq =>
                        !(eq.type === 'weapon' && (eq.data.type || 'Primary') === weaponType && eq.key === key)
                    );
                } else {
                    this.selected_equipment = this.selected_equipment.filter(eq => !(eq.key === key && eq.type === type));
                }
            } else {
                // Select: Remove existing equipment of the same type, then add new equipment
                if (type === 'weapon') {
                    const weaponType = data.type || 'Primary';
                    this.selected_equipment = this.selected_equipment.filter(eq =>
                        !(eq.type === 'weapon' && (eq.data.type || 'Primary') === weaponType)
                    );
                } else {
                    this.selected_equipment = this.selected_equipment.filter(eq => eq.type !== type);
                }

                // Add new equipment
                this.selected_equipment.push({
                    key: key,
                    type: type,
                    data: data
                });
            }

            // Mark as unsaved (entangled property will automatically sync to server)
            this.markAsUnsaved();

            // NOTE: Equipment now syncs automatically via entangled properties
            // Sidebar completion status is now calculated in real-time by isStepComplete() JavaScript method
        },

        selectInventoryItem(itemName) {
            const itemKey = itemName.toLowerCase();
            
            // Check if this is a chooseOne or chooseExtra item
            const classData = this.selectedClassData;
            const isChooseOne = classData?.startingInventory?.chooseOne?.includes(itemName);
            const isChooseExtra = classData?.startingInventory?.chooseExtra?.includes(itemName);
            
            if (isChooseOne) {
                // For chooseOne items, only allow one selection - replace any existing chooseOne selection
                this.selected_equipment = this.selected_equipment.filter(eq => 
                    !classData.startingInventory.chooseOne.some(chooseOneItem => 
                        eq.key === chooseOneItem.toLowerCase()
                    )
                );
                
                // Add the new selection
                this.selected_equipment.push({
                    key: itemKey,
                    type: 'item',
                    data: { name: itemName, key: itemKey },
                    category: 'chooseOne'
                });
            } else if (isChooseExtra) {
                // For chooseExtra items, allow multiple selections - toggle behavior
                const existingIndex = this.selected_equipment.findIndex(eq => eq.key === itemKey);
                
                if (existingIndex !== -1) {
                    // Remove if already selected
                    this.selected_equipment.splice(existingIndex, 1);
                } else {
                    // Add new selection
                    this.selected_equipment.push({
                        key: itemKey,
                        type: 'item',
                        data: { name: itemName, key: itemKey },
                        category: 'chooseExtra'
                    });
                }
            }

            // Mark as unsaved and refresh sidebar - sync handled via entangled properties
            this.markAsUnsaved();
            this.refreshStepCompletion();
        },

        isEquipmentSelected(key, type) {
            return this.selected_equipment.some(eq => eq.key === key && eq.type === type);
        },

        isInventoryItemSelected(itemName) {
            const itemKey = itemName.toLowerCase();
            return this.selected_equipment.some(eq => eq.key === itemKey);
        },

        // NOTE: debouncedEquipmentSync() removed - equipment now synced via entangled properties automatically

        // Apply all suggested equipment at once
        applySuggestedEquipment() {
            this.selected_equipment = []; // Clear current equipment
            
            // Add suggested primary weapon
            if (this.suggestedPrimaryWeapon) {
                this.selected_equipment.push({
                    key: this.suggestedPrimaryWeapon.weaponKey,
                    type: 'weapon',
                    data: this.suggestedPrimaryWeapon.weaponData
                });
            }
            
            // Add suggested secondary weapon
            if (this.suggestedSecondaryWeapon) {
                this.selected_equipment.push({
                    key: this.suggestedSecondaryWeapon.weaponKey,
                    type: 'weapon',
                    data: this.suggestedSecondaryWeapon.weaponData
                });
            }
            
            // Add suggested armor
            if (this.suggestedArmor) {
                this.selected_equipment.push({
                    key: this.suggestedArmor.armorKey,
                    type: 'armor',
                    data: this.suggestedArmor.armorData
                });
            }
            
            // Force immediate sync when applying multiple items at once
            // Sidebar completion status is now calculated in real-time by isStepComplete() JavaScript method
            
            // NOTE: markAsUnsaved() will be called from the template, entangled properties handle sync
        },

        // Performance optimization: Clear cached data when selections change
        clearDataCaches() {
            this._cachedClassData = null;
            this._cachedAncestryData = null;
            this._cachedCommunityData = null;
        },

        toggleDomainCard(domain, abilityKey, abilityData) {
            // Find if already selected
            const existingIndex = this.selected_domain_cards.findIndex(card => 
                card.domain === domain && card.ability_key === abilityKey
            );

            if (existingIndex !== -1) {
                // Remove if already selected
                this.selected_domain_cards.splice(existingIndex, 1);
            } else if (this.selected_domain_cards.length < this.maxDomainCards) {
                // Add if we have space
                this.selected_domain_cards.push({
                    domain: domain,
                    ability_key: abilityKey,
                    ability_level: abilityData.level || 1,
                    ability_data: abilityData
                });
            }

            this.markAsUnsaved();
            
            // NOTE: No manual sync needed - entangled properties handle server sync automatically
        },

        /**
         * Select a domain card for a specific level (accordion-based selection)
         * Level 1 uses selected_domain_cards (max 2)
         * Levels 2+ use creation_domain_cards[level] (1 per level)
         */
        selectDomainCardForLevel(level, domain, abilityKey, abilityData) {
            if (level === 1) {
                // Level 1: Use existing selected_domain_cards array (max 2 cards)
                const existingIndex = this.selected_domain_cards.findIndex(card => 
                    card.domain === domain && card.ability_key === abilityKey
                );

                if (existingIndex !== -1) {
                    // Remove if already selected
                    this.selected_domain_cards.splice(existingIndex, 1);
                } else if (this.selected_domain_cards.length < 2) {
                    // Add if we have space (max 2 for level 1)
                    this.selected_domain_cards.push({
                        domain: domain,
                        ability_key: abilityKey,
                        ability_level: abilityData.level || 1,
                        name: abilityData.name || abilityKey,
                        ability_data: abilityData
                    });
                }
            } else {
                // Levels 2+: Use creation_domain_cards object
                if (!this.creation_domain_cards) {
                    this.creation_domain_cards = {};
                }

                // Check if this card is already selected for this level
                if (this.creation_domain_cards[level] && 
                    this.creation_domain_cards[level].ability_key === abilityKey) {
                    // Remove if clicking the same card
                    delete this.creation_domain_cards[level];
                } else {
                    // Set/replace card for this level (only 1 card per level)
                    this.creation_domain_cards[level] = {
                        domain: domain,
                        ability_key: abilityKey,
                        ability_level: abilityData.level || 1,
                        name: abilityData.name || abilityKey,
                        ability_data: abilityData
                    };
                }
            }

            this.markAsUnsaved();
            
            // Trigger reactivity for creation_domain_cards object
            this.creation_domain_cards = { ...this.creation_domain_cards };
        },

        /**
         * Select a domain card for an advancement-granted slot
         * Uses creation_advancement_cards[advKey] for storage
         */
        selectAdvancementDomainCard(advKey, domain, abilityKey, abilityData) {
            if (!this.creation_advancement_cards) {
                this.creation_advancement_cards = {};
            }

            // Check if this card is already selected for this advancement
            if (this.creation_advancement_cards[advKey] && 
                this.creation_advancement_cards[advKey].ability_key === abilityKey) {
                // Remove if clicking the same card
                delete this.creation_advancement_cards[advKey];
            } else {
                // Set/replace card for this advancement (only 1 card per advancement)
                this.creation_advancement_cards[advKey] = {
                    domain: domain,
                    ability_key: abilityKey,
                    ability_level: abilityData.level || 1,
                    name: abilityData.name || abilityKey,
                    ability_data: abilityData
                };
            }

            this.markAsUnsaved();
            
            // Trigger reactivity for creation_advancement_cards object
            this.creation_advancement_cards = { ...this.creation_advancement_cards };
        },

        /**
         * Get the traits selected via trait_bonus advancements for a specific tier
         * Returns a flat array of trait names that have been boosted in this tier
         * 
         * @param {number} tier - Tier number (2, 3, or 4)
         * @returns {Array<string>} Array of trait names (e.g., ['agility', 'strength'])
         */
        getTierTraitSelections(tier) {
            if (!this.creation_advancements || !this.starting_level) {
                return [];
            }

            // Determine level range for this tier
            const tierRanges = {
                2: [2, 3, 4],
                3: [5, 6, 7],
                4: [8, 9, 10]
            };

            const levelRange = tierRanges[tier] || [];
            const traitNames = [];

            // Iterate through levels in this tier
            for (const level of levelRange) {
                if (level > this.starting_level) break;

                const levelAdvancements = this.creation_advancements[level] || [];
                
                // Collect traits from trait_bonus advancements
                levelAdvancements.forEach(advancement => {
                    if (advancement && advancement.type === 'trait_bonus' && advancement.traits) {
                        // Add all selected traits from this advancement
                        advancement.traits.forEach(trait => {
                            if (trait && !traitNames.includes(trait)) {
                                traitNames.push(trait);
                            }
                        });
                    }
                });
            }

            return traitNames;
        },

        /**
         * Get trait bonus advancements for a specific tier
         * Returns an array of { level, advIndex, advancement } objects
         * 
         * @param {number} tier - Tier number (2, 3, or 4)
         * @returns {Array<{level: number, advIndex: number, advancement: object}>}
         */
        getTraitBonusAdvancementsForTier(tier) {
            if (!this.creation_advancements || !this.starting_level) {
                return [];
            }

            const tierRanges = {
                2: [2, 3, 4],
                3: [5, 6, 7],
                4: [8, 9, 10]
            };

            const levelRange = tierRanges[tier] || [];
            const bonusAdvancements = [];

            for (const level of levelRange) {
                if (level > this.starting_level) break;

                const levelAdvancements = this.creation_advancements[level] || [];
                
                levelAdvancements.forEach((advancement, advIndex) => {
                    if (advancement && advancement.type === 'trait_bonus') {
                        bonusAdvancements.push({
                            level: level,
                            advIndex: advIndex,
                            advancement: advancement
                        });
                    }
                });
            }

            return bonusAdvancements;
        },

        /**
         * Get the selected trait for a specific advancement slot
         * 
         * @param {number} level - Level of the advancement
         * @param {number} advIndex - Index of the advancement
         * @param {number} slotIndex - Slot index (0 or 1)
         * @returns {string|null} Selected trait name or null
         */
        getTraitBonusSelection(level, advIndex, slotIndex) {
            const advancement = this.creation_advancements[level]?.[advIndex];
            if (!advancement || advancement.type !== 'trait_bonus') {
                return null;
            }
            return advancement.traits?.[slotIndex] || null;
        },

        /**
         * Check if a trait is selected for a specific advancement
         * 
         * @param {number} level - Level of the advancement
         * @param {number} advIndex - Index of the advancement
         * @param {string} traitName - Name of the trait
         * @returns {boolean}
         */
        isTraitSelectedForBonus(level, advIndex, traitName) {
            const advancement = this.creation_advancements[level]?.[advIndex];
            if (!advancement || advancement.type !== 'trait_bonus' || !advancement.traits) {
                return false;
            }
            return advancement.traits.includes(traitName);
        },

        /**
         * Toggle a trait selection for a trait bonus advancement
         * Automatically finds the right slot or deselects if already selected
         * 
         * @param {number} level - Level of the advancement
         * @param {number} advIndex - Index of the advancement
         * @param {string} traitName - Name of the trait to toggle
         */
        toggleTraitBonus(level, advIndex, traitName) {
            const advancement = this.creation_advancements[level]?.[advIndex];
            if (!advancement || advancement.type !== 'trait_bonus') {
                return;
            }

            // Initialize traits array if needed
            if (!advancement.traits) {
                advancement.traits = [null, null];
            }

            // Check if trait is already selected - if so, deselect it
            const currentIndex = advancement.traits.indexOf(traitName);
            if (currentIndex !== -1) {
                this.selectTraitBonus(level, advIndex, currentIndex, traitName);
                return;
            }

            // Find first available slot
            const firstEmptySlot = advancement.traits.indexOf(null);
            if (firstEmptySlot !== -1) {
                // Check if we can select this trait
                if (this.canSelectTraitForBonus(traitName, level, advIndex, firstEmptySlot)) {
                    this.selectTraitBonus(level, advIndex, firstEmptySlot, traitName);
                }
            }
        },

        /**
         * Get marked traits for a specific tier
         * Returns array of trait names that have been marked in this tier
         * 
         * @param {number} tier - Tier number (2, 3, or 4)
         * @returns {Array<string>} Array of trait names that are marked in this tier
         */
        getMarkedTraitsForTier(tier) {
            const markedTraits = [];
            const tierRanges = {
                2: [2, 3, 4],
                3: [5, 6, 7],
                4: [8, 9, 10]
            };

            const levelRange = tierRanges[tier] || [];

            for (const level of levelRange) {
                if (level > this.starting_level) break;

                const levelAdvancements = this.creation_advancements[level] || [];
                
                levelAdvancements.forEach(advancement => {
                    if (advancement && advancement.type === 'trait_bonus' && advancement.traits) {
                        markedTraits.push(...advancement.traits.filter(t => t));
                    }
                });
            }

            return [...new Set(markedTraits)]; // Remove duplicates
        },

        /**
         * Check if a trait can be selected for a specific tier bonus
         * A trait cannot be selected if it's already been used in this tier
         * 
         * @param {string} traitName - Name of the trait (e.g., 'agility', 'strength')
         * @param {number} level - Level where this bonus is being applied
         * @param {number} advIndex - Index of the advancement
         * @param {number} slotIndex - Which slot (0 or 1) in this advancement
         * @returns {boolean} Whether the trait can be selected
         */
        canSelectTraitForBonus(traitName, level, advIndex, slotIndex) {
            // Get tier for this level
            const tier = level <= 4 ? 2 : (level <= 7 ? 3 : 4);
            const markedTraits = this.getMarkedTraitsForTier(tier);
            
            // Get current selection for this specific slot
            const currentAdvancement = this.creation_advancements[level]?.[advIndex];
            const currentTrait = currentAdvancement?.traits?.[slotIndex];
            
            // If this trait is currently selected in this slot, it's selectable (for deselection)
            if (currentTrait === traitName) {
                return true;
            }
            
            // Otherwise, check if it's already marked elsewhere in the tier
            return !markedTraits.includes(traitName);
        },

        /**
         * Select a trait for a specific trait bonus advancement
         * 
         * @param {number} level - Level of the advancement
         * @param {number} advIndex - Index of the advancement at that level
         * @param {number} slotIndex - Which slot (0 or 1) to set
         * @param {string} traitName - Name of the trait to assign
         */
        selectTraitBonus(level, advIndex, slotIndex, traitName) {
            if (!this.creation_advancements[level]) {
                return;
            }

            const advancement = this.creation_advancements[level][advIndex];
            if (!advancement || advancement.type !== 'trait_bonus') {
                return;
            }

            // Check if we can select this trait
            if (!this.canSelectTraitForBonus(traitName, level, advIndex, slotIndex)) {
                return;
            }

            // Initialize traits array if needed
            if (!advancement.traits) {
                advancement.traits = [null, null];
            }

            // Toggle selection - if clicking the same trait, deselect it
            if (advancement.traits[slotIndex] === traitName) {
                advancement.traits[slotIndex] = null;
            } else {
                advancement.traits[slotIndex] = traitName;
            }

            // Trigger reactivity
            this.creation_advancements = { ...this.creation_advancements };
            this.markAsUnsaved();
        },

        isDomainCardSelected(domain, abilityKey) {
            return this.selected_domain_cards.some(card => 
                card.domain === domain && card.ability_key === abilityKey
            );
        },

        isCardSelectedAtAnyLevel(abilityKey) {
            // Check if card is selected at level 1
            const inLevel1 = this.selected_domain_cards.some(card => 
                card.ability_key === abilityKey
            );
            
            // Check if card is selected at any other level
            const inOtherLevels = this.creation_domain_cards && 
                Object.values(this.creation_domain_cards).some(card => 
                    card && card.ability_key === abilityKey
                );
            
            return inLevel1 || inOtherLevels;
        },

        canSelectMoreDomainCards() {
            return this.selected_domain_cards.length < this.maxDomainCards;
        },

        countSelectedInDomain(domain) {
            return this.selected_domain_cards.filter(card => card.domain === domain).length;
        },

        getDomainColor(domainKey) {
            const domainColors = {
                'valor': '#e2680e',
                'splendor': '#b8a342', 
                'sage': '#244e30',
                'midnight': '#1e201f',
                'grace': '#8d3965',
                'codex': '#24395d',
                'bone': '#a4a9a8',
                'blade': '#af231c',
                'arcana': '#4e345b',
                'dread': '#1e201f'
            };
            return domainColors[domainKey] || '#24395d';
        },

        applySuggestedTraits() {
            if (!this.selectedClassData?.suggestedTraits) {
                console.warn('No suggested traits available for selected class');
                return;
            }

            // Apply the suggested traits
            this.assigned_traits = { ...this.selectedClassData.suggestedTraits };
            this.selectedValue = null; // Clear any selected value
            this.markAsUnsaved();

            // NOTE: No manual sync needed - entangled properties handle server sync automatically
            
            // Show notification
            this.$dispatch('notify', {
                type: 'success',
                message: `Applied suggested traits for ${this.selectedClassData.name}!`
            });
        },

        // Experience management methods
        addExperience() {
            if (!this.new_experience_name || this.new_experience_name.trim().length === 0) {
                return;
            }

            if (this.experienceCount >= 2) {
                return;
            }

            // Add experience locally for instant UI feedback
            const newExperience = {
                name: this.new_experience_name.trim(),
                description: this.new_experience_description.trim(),
                modifier: 2
            };

            if (!this.experiences) {
                this.experiences = [];
            }
            this.experiences.push(newExperience);

            // Clear form fields
            this.new_experience_name = '';
            this.new_experience_description = '';

            this.markAsUnsaved();

            // No server sync needed - handled by entangled properties
        },

        clearAllExperiences() {
            if (confirm('Are you sure you want to remove all experiences?')) {
                this.experiences = [];
                this.markAsUnsaved();
            }
        },

        removeExperience(index) {
            const experienceToRemove = this.experiences[index];
            
            // If this experience has the clank bonus, clear it
            if (experienceToRemove && this.clank_bonus_experience === experienceToRemove.name) {
                this.clank_bonus_experience = null;
            }
            
            this.experiences.splice(index, 1);
            this.markAsUnsaved();
        },

        selectClankBonusExperience(experienceName) {
            this.clank_bonus_experience = experienceName;
            this.markAsUnsaved();
        },

        removeClankBonus(experienceName) {
            if (this.clank_bonus_experience === experienceName) {
                this.clank_bonus_experience = null;
                this.markAsUnsaved();
            }
        },

        startEditingExperience(index) {
            this.editing_experience = index;
            this.edit_experience_description = this.experiences[index]?.description || '';
        },

        saveExperienceEdit(index) {
            if (this.experiences[index]) {
                this.experiences[index].description = this.edit_experience_description.trim();
                this.editing_experience = null;
                this.edit_experience_description = '';
                this.markAsUnsaved();
            }
        },

        cancelExperienceEdit() {
            this.editing_experience = null;
            this.edit_experience_description = '';
        },

        // Experience Bonus Advancement methods
        hasExperienceBonusAdvancements() {
            if (!this.creation_advancements || typeof this.creation_advancements !== 'object') {
                return false;
            }
            
            // Check if any level has experience_bonus type advancements
            for (const level in this.creation_advancements) {
                const advancements = this.creation_advancements[level];
                if (Array.isArray(advancements)) {
                    const hasBonus = advancements.some(adv => adv && adv.type === 'experience_bonus');
                    if (hasBonus) return true;
                }
            }
            return false;
        },

        getLevelsWithExperienceBonuses() {
            const levels = [];
            if (!this.creation_advancements) return levels;
            
            for (const level in this.creation_advancements) {
                const advancements = this.creation_advancements[level];
                if (Array.isArray(advancements)) {
                    const hasBonus = advancements.some(adv => adv && adv.type === 'experience_bonus');
                    if (hasBonus) {
                        levels.push(parseInt(level));
                    }
                }
            }
            return levels.sort((a, b) => a - b);
        },

        getExperienceBonusAdvancementIndices(level) {
            const indices = [];
            const advancements = this.creation_advancements[level];
            if (!Array.isArray(advancements)) return indices;
            
            advancements.forEach((adv, index) => {
                if (adv && adv.type === 'experience_bonus') {
                    indices.push(index);
                }
            });
            return indices;
        },

        getAllAvailableExperiences() {
            const allExperiences = [];
            
            // Add base experiences
            if (Array.isArray(this.experiences)) {
                allExperiences.push(...this.experiences);
            }
            
            // Add tier achievement experiences
            if (this.creation_tier_experiences) {
                [2, 5, 8].forEach(level => {
                    const tierExp = this.creation_tier_experiences[level];
                    if (tierExp && tierExp.name) {
                        allExperiences.push(tierExp);
                    }
                });
            }
            
            return allExperiences;
        },

        isExperienceSelectedForBonus(level, advIndex, experienceName) {
            if (!this.creation_advancements[level]) return false;
            const advancement = this.creation_advancements[level][advIndex];
            if (!advancement || advancement.type !== 'experience_bonus') return false;
            
            const selectedExperiences = advancement.experiences || [];
            return selectedExperiences.includes(experienceName);
        },

        toggleExperienceBonus(level, advIndex, experienceName) {
            // Ensure structure exists
            if (!this.creation_advancements[level]) {
                this.creation_advancements[level] = [];
            }
            if (!this.creation_advancements[level][advIndex]) {
                return;
            }
            
            const advancement = this.creation_advancements[level][advIndex];
            if (!advancement.experiences) {
                advancement.experiences = [];
            }
            
            const index = advancement.experiences.indexOf(experienceName);
            if (index > -1) {
                // Remove if already selected
                advancement.experiences.splice(index, 1);
            } else {
                // Add if not selected and limit not reached
                if (advancement.experiences.length < 2) {
                    advancement.experiences.push(experienceName);
                }
            }
            
            // Trigger reactivity
            this.creation_advancements = { ...this.creation_advancements };
            this.markAsUnsaved();
        },

        canSelectExperienceBonus(level, advIndex, experienceName) {
            // Can select if:
            // 1. Already selected (to allow deselection)
            // 2. Less than 2 experiences selected for this advancement
            if (this.isExperienceSelectedForBonus(level, advIndex, experienceName)) {
                return true;
            }
            
            const count = this.getExperienceBonusCount(level, advIndex);
            return count < 2;
        },

        getExperienceBonusCount(level, advIndex) {
            if (!this.creation_advancements[level]) return 0;
            const advancement = this.creation_advancements[level][advIndex];
            if (!advancement || advancement.type !== 'experience_bonus') return 0;
            
            return (advancement.experiences || []).length;
        },

        // Trait advancement methods
        getTierTraitSelections(tier) {
            // Get all trait selections for a specific tier
            const traits = [];
            const tierStartLevel = tier === 2 ? 2 : tier === 3 ? 5 : 8;
            const tierEndLevel = tier === 2 ? 4 : tier === 3 ? 7 : 10;
            
            for (let level = tierStartLevel; level <= Math.min(tierEndLevel, this.starting_level); level++) {
                const levelAdvancements = this.creation_advancements[level] || [];
                levelAdvancements.forEach(advancement => {
                    if (advancement.type === 'trait_bonus' && advancement.traits) {
                        traits.push(...advancement.traits);
                    }
                });
            }
            
            return traits;
        },

        // Background management
        markBackgroundComplete() {
            this.$wire.markBackgroundComplete();
        },

        // Character saving
        async saveCharacter() {
            this.isSaving = true;
            
            try {
                // Sync data to Livewire before saving
                this.$wire.character.selected_equipment = this.selected_equipment;
                this.$wire.character.name = this.name;
                this.$wire.pronouns = this.pronouns;
                
                // Save character data first
                await this.$wire.saveToDatabase();
                
                // Then save advancements if character is above level 1
                if (this.starting_level > 1) {
                    const advancementsData = {
                        creation_advancements: this.creation_advancements,
                        creation_tier_experiences: this.creation_tier_experiences,
                        creation_domain_cards: this.creation_domain_cards,
                        creation_advancement_cards: this.creation_advancement_cards
                    };
                    
                    console.log('Saving advancements:', advancementsData);
                    await this.$wire.saveCharacterAdvancements(advancementsData);
                }
                
                // Success is handled by the character-saved event listener
            } catch (error) {
                console.error('Save failed:', error);
                this.isSaving = false;
            }
        },

        /**
         * Load advancement selections from database when editing existing character
         */
        async loadAdvancementsFromDatabase() {
            try {
                console.log('Loading advancements from database...');
                const data = await this.$wire.loadCharacterAdvancements();
                
                if (data) {
                    // Populate client-side state with loaded data
                    this.creation_advancements = data.creation_advancements || {};
                    this.creation_tier_experiences = data.creation_tier_experiences || {};
                    this.creation_domain_cards = data.creation_domain_cards || {};
                    this.creation_advancement_cards = data.creation_advancement_cards || {};
                    
                    console.log('Advancements loaded:', {
                        advancements: Object.keys(this.creation_advancements).length,
                        tierExperiences: Object.keys(this.creation_tier_experiences).length,
                        domainCards: Object.keys(this.creation_domain_cards).length,
                        advancementCards: Object.keys(this.creation_advancement_cards).length
                    });
                }
            } catch (error) {
                console.error('Failed to load advancements from database:', error);
            }
        },

        // Profile image management
        openImageUpload() {
            if (this.imageUploader) {
                this.imageUploader.openFileDialog();
            }
        },
        
        clearProfileImage() {
            if (this.imageUploader) {
                this.imageUploader.clearImage();
            }
            this.markAsUnsaved(); // Clearing image should trigger unsaved state
            this.$wire.clearProfileImage();
        },
        
        // Cleanup when component is destroyed
        destroy() {
            if (this.imageUploader) {
                this.imageUploader.destroy();
            }
        }
    };
}
