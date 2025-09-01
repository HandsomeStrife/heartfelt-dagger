// Alpine state module for the Character Viewer page
// Exports a factory function that returns the state object used by x-data

export function characterViewerState(options = {}) {
    const canEditInitial = Boolean(options.canEdit);
    const isAuthenticated = Boolean(options.isAuthenticated);
    const characterKey = String(options.characterKey || '');

    const hitPointsLen = Number(options.final_hit_points ?? 6);
    const stressLen = Number(options.stress_len ?? 6);
    const armorScore = Math.max(1, Number(options.armor_score ?? 0));

    return {
        canEdit: canEditInitial,
        characterKey: characterKey,
        hitPoints: Array(hitPointsLen).fill(false),
        stress: Array(stressLen).fill(false),
        hope: [false, false, false, false, false, false],
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
            const state = {
                hitPoints: this.hitPoints,
                stress: this.stress,
                hope: this.hope,
                armorSlots: this.armorSlots,
                goldHandfuls: this.goldHandfuls,
                goldBags: this.goldBags,
                goldChest: this.goldChest,
            };

            try {
                localStorage.setItem(`character_state_` + this.characterKey, JSON.stringify(state));
            } catch (e) {
                // ignore storage errors
            }

            if (isAuthenticated && this.$wire) {
                try {
                    await this.$wire.saveCharacterState(state);
                } finally {
                    window.__saveSeq = (window.__saveSeq || 0) + 1;
                }
            } else {
                window.__saveSeq = (window.__saveSeq || 0) + 1;
            }
        },

        async loadCharacterState() {
            let state = null;

            if (isAuthenticated && this.$wire) {
                try {
                    state = await this.$wire.getCharacterState();
                } catch (error) {
                    console.warn('Failed to load character state from database:', error);
                }
                if (!this.isValidState(state)) {
                    state = null;
                }
            }

            if (!state) {
                try {
                    const saved = localStorage.getItem(`character_state_` + this.characterKey);
                    if (saved) {
                        state = JSON.parse(saved);
                    }
                } catch (e) {
                    // ignore parse errors
                }
            }

            if (this.isValidState(state)) {
                // normalize goldBags length to 9
                if (Array.isArray(state.goldBags) && state.goldBags.length !== 9) {
                    const currentBags = state.goldBags.slice();
                    state.goldBags = Array(9).fill(false);
                    for (let i = 0; i < Math.min(currentBags.length, 9); i++) {
                        state.goldBags[i] = currentBags[i];
                    }
                }
                // normalize goldHandfuls length to 9
                if (Array.isArray(state.goldHandfuls) && state.goldHandfuls.length !== 9) {
                    const currentHandfuls = state.goldHandfuls.slice();
                    state.goldHandfuls = Array(9).fill(false);
                    for (let i = 0; i < Math.min(currentHandfuls.length, 9); i++) {
                        state.goldHandfuls[i] = currentHandfuls[i];
                    }
                }
                // normalize armorSlots length to current armorScore
                const desiredArmorLen = Math.max(1, armorScore);
                if (!Array.isArray(state.armorSlots)) {
                    state.armorSlots = Array(desiredArmorLen).fill(false);
                } else if (state.armorSlots.length !== desiredArmorLen) {
                    const currentArmor = state.armorSlots.slice();
                    state.armorSlots = Array(desiredArmorLen).fill(false);
                    for (let i = 0; i < Math.min(currentArmor.length, desiredArmorLen); i++) {
                        state.armorSlots[i] = currentArmor[i];
                    }
                }

                Object.assign(this, state);
            }

            document.body.dataset.hydrated = '1';
        },
    };
}


