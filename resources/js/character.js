// Alpine state module for the Character Viewer page
// Exports a factory function that returns the state object used by x-data

export function characterViewerState(options = {}) {
    const canEditInitial = Boolean(options.canEdit);
    const isAuthenticated = Boolean(options.isAuthenticated);
    const characterKey = String(options.characterKey || '');

    const hitPointsLen = Number(options.final_hit_points ?? 6);
    const stressLen = Number(options.stress_len ?? 6);
    const armorScore = Math.max(1, Number(options.armor_score ?? 0));
    const hopeValue = Number(options.hope ?? 2);

    // Initialize hope array with correct number of filled slots
    const hopeArray = Array(6).fill(false);
    for (let i = 0; i < Math.min(hopeValue, 6); i++) {
        hopeArray[i] = true;
    }

    return {
        canEdit: canEditInitial,
        characterKey: characterKey,
        hitPoints: Array(hitPointsLen).fill(false),
        stress: Array(stressLen).fill(false),
        hope: hopeArray,
        armorSlots: Array(armorScore).fill(false),
        goldHandfuls: Array(9).fill(false),
        goldBags: Array(9).fill(false),
        goldChest: false,

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

        async saveCharacterState() {
            // Character viewer should NEVER save state anywhere
            // Interactive changes (marking HP, stress, etc.) are temporary and lost on refresh
            // This is intentional - the viewer shows computed stats from database
            
            // Just update the sequence number for any UI that might depend on it
            window.__saveSeq = (window.__saveSeq || 0) + 1;
        },

        async loadCharacterState() {
            // Character viewer should NEVER load saved state from localStorage or database
            // All state should be derived from computed stats passed from PHP
            // Interactive changes are temporary and lost on refresh
            
            document.body.dataset.hydrated = '1';
        },
    };
}


