// Alpine state module for the Character Viewer page
// Exports a factory function that returns the state object used by x-data

export function characterViewerState(options = {}) {
    const canEditInitial = Boolean(options.canEdit);
    const isAuthenticated = Boolean(options.isAuthenticated);
    const characterKey = String(options.characterKey || '');
    const initialStatus = options.initialStatus;

    const hitPointsLen = Number(options.final_hit_points ?? 6);
    const stressLen = Number(options.stress_len ?? 6);
    const armorScore = Math.max(1, Number(options.armor_score ?? 0));
    const hopeValue = Number(options.hope ?? 2);

    // Initialize arrays - use initialStatus if available, otherwise defaults
    let hitPoints, stress, hope, armorSlots, goldHandfuls, goldBags, goldChest;
    
    if (initialStatus) {
        hitPoints = initialStatus.hitPoints || Array(hitPointsLen).fill(false);
        stress = initialStatus.stress || Array(stressLen).fill(false);
        hope = initialStatus.hope || Array(6).fill(false);
        armorSlots = initialStatus.armorSlots || Array(armorScore).fill(false);
        goldHandfuls = initialStatus.goldHandfuls || Array(9).fill(false);
        goldBags = initialStatus.goldBags || Array(9).fill(false);
        goldChest = initialStatus.goldChest || false;
    } else {
        // Default initialization
        hitPoints = Array(hitPointsLen).fill(false);
        stress = Array(stressLen).fill(false);
        hope = Array(6).fill(false);
        // Set hope based on computed value
        for (let i = 0; i < Math.min(hopeValue, 6); i++) {
            hope[i] = true;
        }
        armorSlots = Array(armorScore).fill(false);
        goldHandfuls = Array(9).fill(false);
        goldBags = Array(9).fill(false);
        goldChest = false;
    }

    return {
        canEdit: canEditInitial,
        characterKey: characterKey,
        hitPoints: hitPoints,
        stress: stress,
        hope: hope,
        armorSlots: armorSlots,
        goldHandfuls: goldHandfuls,
        goldBags: goldBags,
        goldChest: goldChest,

        init() {
            if (!isAuthenticated) {
                try {
                    const storedKeys = JSON.parse(localStorage.getItem('daggerheart_characters') || '[]');
                    this.canEdit = storedKeys.includes(this.characterKey);
                } catch (e) {
                    // no-op: keep default canEdit
                }
            }

            if (!this.canEdit) {
                document.body.dataset.anonLocked = '1';
            } else {
                delete document.body.dataset.anonLocked;
            }

            this.loadCharacterState();
        },

        toggleHitPoint(index) {
            if (!this.canEdit) return;
            this.hitPoints[index] = !this.hitPoints[index];
            this.saveCharacterState();
        },

        toggleStress(index) {
            if (!this.canEdit) return;
            this.stress[index] = !this.stress[index];
            this.saveCharacterState();
        },

        toggleHope(index) {
            if (!this.canEdit) return;
            this.hope[index] = !this.hope[index];
            this.saveCharacterState();
        },

        toggleArmorSlot(index) {
            if (!this.canEdit) return;
            this.armorSlots[index] = !this.armorSlots[index];
            this.saveCharacterState();
        },

        toggleGoldHandfuls(index) {
            if (!this.canEdit) return;
            this.goldHandfuls[index] = !this.goldHandfuls[index];
            this.saveCharacterState();
        },

        toggleGoldBags(index) {
            if (!this.canEdit) return;
            this.goldBags[index] = !this.goldBags[index];
            this.saveCharacterState();
        },

        toggleGoldChest() {
            if (!this.canEdit) return;
            this.goldChest = !this.goldChest;
            this.saveCharacterState();
        },

        isValidState(state) {
            if (!state || typeof state !== 'object') return false;
            const keys = ['hitPoints', 'stress', 'hope', 'goldHandfuls', 'goldBags', 'goldChest', 'armorSlots'];
            return keys.some(k => Object.prototype.hasOwnProperty.call(state, k));
        },

        refresh() {
            if (this.$wire) {
                this.$wire.$refresh();
            }
        },

        async saveCharacterState() {
            // Save to database via Livewire (no localStorage)
            if (this.$wire && this.canEdit) {
                const state = {
                    hitPoints: this.hitPoints,
                    stress: this.stress,
                    hope: this.hope,
                    armorSlots: this.armorSlots,
                    goldHandfuls: this.goldHandfuls,
                    goldBags: this.goldBags,
                    goldChest: this.goldChest
                };
                
                try {
                    await this.$wire.saveCharacterState(state);
                    window.__saveSeq = (window.__saveSeq || 0) + 1;
                } catch (error) {
                    console.error('Failed to save character state:', error);
                }
            }
        },

        async loadCharacterState() {
            // Load from database via Livewire (no localStorage)
            if (this.$wire && this.canEdit) {
                try {
                    const state = await this.$wire.getCharacterState();
                    if (state && this.isValidState(state)) {
                        // Apply loaded state
                        Object.assign(this, state);
                    }
                } catch (error) {
                    console.error('Failed to load character state:', error);
                }
            }
            
            document.body.dataset.hydrated = '1';
        },
    };
}


