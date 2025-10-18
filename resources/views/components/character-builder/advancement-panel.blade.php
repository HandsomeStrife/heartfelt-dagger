@props([
    'starting_level' => 1,
])

<!-- Advancement Panel - Only show for characters starting at level 2+ -->
<div x-show="starting_level > 1" x-data="advancementPanelComponent()" class="mb-6">
    <div class="bg-gradient-to-br from-purple-900/30 via-slate-900/50 to-indigo-900/30 backdrop-blur-xl border border-purple-500/30 rounded-xl overflow-hidden">
        <!-- Panel Header -->
        <div 
            @click="togglePanel()"
            class="flex items-center justify-between p-4 cursor-pointer hover:bg-purple-500/10 transition-all duration-200"
        >
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 bg-purple-500/20 rounded-lg">
                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-white font-outfit">Level Advancements</h3>
                    <p class="text-sm text-slate-300">
                        Configure your character's advancement selections (Levels 2-<span x-text="starting_level"></span>)
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-right">
                    <div class="text-sm text-purple-300 font-medium" x-text="advancementProgress"></div>
                    <div class="text-xs text-slate-400">Progress</div>
                </div>
                <svg 
                    class="w-5 h-5 text-purple-400 transition-transform duration-200" 
                    :class="{ 'rotate-180': isPanelExpanded }"
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </div>

        <!-- Panel Content -->
        <div 
            x-show="isPanelExpanded" 
            x-collapse
            class="border-t border-purple-500/20"
        >
            <div class="p-4 space-y-4">
                <!-- Info Box -->
                <div class="bg-blue-500/10 border border-blue-500/20 rounded-lg p-3">
                    <p class="text-sm text-slate-300">
                        <span class="text-blue-300 font-semibold">How it works:</span> 
                        Make your advancement selections here, then they'll appear in the relevant tabs. 
                        Trait bonuses show in the Traits tab, experiences in the Experiences tab, and domain cards in the Domain Cards tab.
                    </p>
                </div>

                <!-- Level-by-Level Advancements -->
                <div class="space-y-3">
                    <template x-for="level in levelsRequiringAdvancements" :key="level">
                        <div class="bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-lg p-4">
                            <!-- Level Header -->
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl font-black text-white font-outfit" x-text="'Level ' + level"></span>
                                    <span 
                                        class="px-2 py-1 text-xs font-bold uppercase tracking-wider rounded"
                                        :class="getTierColorClass(level)"
                                        x-text="getTierName(level)"
                                    ></span>
                                </div>
                                <div x-show="isLevelComplete(level)" class="text-emerald-400 flex items-center gap-1">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-sm font-medium">Complete</span>
                                </div>
                            </div>

                            <!-- Tier Achievement (Levels 2, 5, 8) -->
                            <template x-if="isTierAchievementLevel(level)">
                                <div class="mb-4 bg-amber-500/10 border border-amber-500/20 rounded-lg p-3">
                                    <div class="flex items-start gap-2">
                                        <svg class="w-5 h-5 text-amber-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                        <div class="flex-1">
                                            <h5 class="text-amber-300 font-bold text-sm mb-1">Tier Achievement (Automatic)</h5>
                                            <ul class="text-xs text-slate-300 space-y-1">
                                                <li>• Gain a new Experience at +2</li>
                                                <li>• Gain +1 Proficiency</li>
                                                <li x-show="level === 5 || level === 8">• Clear all marked traits</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- Domain Card Selection -->
                            <div class="mb-4 bg-indigo-500/10 border border-indigo-500/20 rounded-lg p-3">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex items-start gap-2 flex-1">
                                        <svg class="w-5 h-5 text-indigo-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                                        </svg>
                                        <div class="flex-1">
                                            <h5 class="text-indigo-300 font-bold text-sm">Domain Card (Required)</h5>
                                            <p class="text-xs text-slate-400 mt-1">Select in the Domain Cards tab</p>
                                        </div>
                                    </div>
                                    <div x-show="hasDomainCardForLevel(level)" class="text-emerald-400">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Advancement Selections (2 required) -->
                            <div class="space-y-3">
                                <h5 class="text-white font-semibold text-sm">Advancement Selections (Choose 2)</h5>
                                
                                <template x-for="advNumber in [1, 2]" :key="'adv-' + level + '-' + advNumber">
                                    <div class="bg-slate-900/50 border border-slate-600/50 rounded-lg p-3">
                                        <label class="block text-xs font-medium text-slate-400 mb-2" x-text="'Advancement ' + advNumber"></label>
                                        <select 
                                            :id="'adv-level-' + level + '-' + advNumber"
                                            :value="getAdvancementSelection(level, advNumber)"
                                            @change="updateAdvancementSelection(level, advNumber, $event.target.value)"
                                            class="w-full px-3 py-2 bg-slate-800 border border-slate-600 rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                        >
                                            <option value="">-- Select Advancement --</option>
                                            <option value="trait_bonus">Trait Bonus (+1 to two unmarked traits)</option>
                                            <option value="hit_point">Hit Point Slot (+1 HP)</option>
                                            <option value="stress_slot">Stress Slot (+1 Stress)</option>
                                            <option value="experience_bonus">Experience Bonus (+1 to two experiences)</option>
                                            <option value="domain_card">Additional Domain Card</option>
                                            <option value="evasion">Evasion (+1)</option>
                                            <option value="subclass_upgrade" x-show="canTakeSubclassUpgrade(level)">Subclass Upgrade</option>
                                            <option value="proficiency" x-show="canTakeProficiency(level, advNumber)">Proficiency (+1, requires 2 slots)</option>
                                            <option value="multiclass" x-show="canTakeMulticlass(level, advNumber)">Multiclass (requires 2 slots)</option>
                                        </select>
                                        
                                        <!-- Additional selections for trait_bonus -->
                                        <div x-show="getAdvancementSelection(level, advNumber) === 'trait_bonus'" class="mt-3 space-y-2">
                                            <p class="text-xs text-slate-400">Select 2 traits to increase:</p>
                                            <div class="grid grid-cols-3 gap-2">
                                                <template x-for="trait in availableTraits" :key="trait">
                                                    <label class="flex items-center gap-2 p-2 bg-slate-800/50 rounded cursor-pointer hover:bg-slate-700/50">
                                                        <input 
                                                            type="checkbox"
                                                            :checked="isTraitSelectedForAdvancement(level, advNumber, trait)"
                                                            @change="toggleTraitForAdvancement(level, advNumber, trait, $event.target.checked)"
                                                            :disabled="!canSelectTraitForLevel(level, trait)"
                                                            class="rounded text-purple-500 focus:ring-purple-500"
                                                        >
                                                        <span class="text-sm text-white capitalize" x-text="trait"></span>
                                                    </label>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function advancementPanelComponent() {
    return {
        isPanelExpanded: true,
        
        // Access parent component data
        get starting_level() {
            return this.$wire.get('character.starting_level');
        },
        
        get creation_advancements() {
            return this.$wire.get('character.creation_advancements') || {};
        },
        
        get creation_domain_cards() {
            return this.$wire.get('character.creation_domain_cards') || {};
        },
        
        get creation_tier_experiences() {
            return this.$wire.get('character.creation_tier_experiences') || {};
        },
        
        get levelsRequiringAdvancements() {
            const levels = [];
            for (let i = 2; i <= this.starting_level; i++) {
                levels.push(i);
            }
            return levels;
        },
        
        get advancementProgress() {
            const total = this.levelsRequiringAdvancements.length;
            const complete = this.levelsRequiringAdvancements.filter(level => this.isLevelComplete(level)).length;
            return `${complete}/${total} levels complete`;
        },
        
        availableTraits: ['agility', 'strength', 'finesse', 'instinct', 'presence', 'knowledge'],
        
        togglePanel() {
            this.isPanelExpanded = !this.isPanelExpanded;
        },
        
        isTierAchievementLevel(level) {
            return [2, 5, 8].includes(level);
        },
        
        getTierForLevel(level) {
            if (level >= 8) return 4;
            if (level >= 5) return 3;
            if (level >= 2) return 2;
            return 1;
        },
        
        getTierName(level) {
            return `Tier ${this.getTierForLevel(level)}`;
        },
        
        getTierColorClass(level) {
            const tier = this.getTierForLevel(level);
            return {
                2: 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30',
                3: 'bg-purple-500/20 text-purple-300 border border-purple-500/30',
                4: 'bg-amber-500/20 text-amber-300 border border-amber-500/30'
            }[tier] || 'bg-slate-500/20 text-slate-300 border border-slate-500/30';
        },
        
        isLevelComplete(level) {
            // Check domain card
            const hasDomainCard = this.hasDomainCardForLevel(level);
            
            // Check tier achievement experience (for levels 2, 5, 8)
            const hasTierExperience = !this.isTierAchievementLevel(level) || 
                (this.creation_tier_experiences[level] && this.creation_tier_experiences[level].name);
            
            // Check advancements (exactly 2 required)
            const levelAdvancements = this.creation_advancements[level] || [];
            const hasAdvancements = levelAdvancements.length === 2;
            
            return hasDomainCard && hasTierExperience && hasAdvancements;
        },
        
        hasDomainCardForLevel(level) {
            return !!(this.creation_domain_cards[level]);
        },
        
        getAdvancementSelection(level, advNumber) {
            const levelAdvancements = this.creation_advancements[level] || [];
            const advancement = levelAdvancements[advNumber - 1];
            return advancement ? advancement.type : '';
        },
        
        updateAdvancementSelection(level, advNumber, type) {
            // Update via Livewire entangle
            const levelAdvancements = [...(this.creation_advancements[level] || [])];
            
            // Ensure array has correct length
            while (levelAdvancements.length < advNumber) {
                levelAdvancements.push({type: '', traits: []});
            }
            
            // Update the specific advancement
            levelAdvancements[advNumber - 1] = {type, traits: []};
            
            // Update via Wire
            this.$wire.set(`character.creation_advancements.${level}`, levelAdvancements);
        },
        
        isTraitSelectedForAdvancement(level, advNumber, trait) {
            const levelAdvancements = this.creation_advancements[level] || [];
            const advancement = levelAdvancements[advNumber - 1];
            return advancement && advancement.traits && advancement.traits.includes(trait);
        },
        
        toggleTraitForAdvancement(level, advNumber, trait, checked) {
            const levelAdvancements = [...(this.creation_advancements[level] || [])];
            const advancement = levelAdvancements[advNumber - 1] || {type: 'trait_bonus', traits: []};
            
            let traits = [...(advancement.traits || [])];
            
            if (checked) {
                // Only allow 2 traits maximum
                if (traits.length < 2 && !traits.includes(trait)) {
                    traits.push(trait);
                }
            } else {
                traits = traits.filter(t => t !== trait);
            }
            
            advancement.traits = traits;
            levelAdvancements[advNumber - 1] = advancement;
            
            this.$wire.set(`character.creation_advancements.${level}`, levelAdvancements);
        },
        
        canSelectTraitForLevel(level, trait) {
            // Get all trait selections for this tier
            const tier = this.getTierForLevel(level);
            const tierStartLevel = tier === 2 ? 2 : tier === 3 ? 5 : 8;
            const tierEndLevel = tier === 2 ? 4 : tier === 3 ? 7 : 10;
            
            // Check if trait is already marked in this tier
            for (let l = tierStartLevel; l <= Math.min(tierEndLevel, this.starting_level); l++) {
                if (l === level) continue; // Skip current level
                const levelAdvs = this.creation_advancements[l] || [];
                for (const adv of levelAdvs) {
                    if (adv.type === 'trait_bonus' && adv.traits && adv.traits.includes(trait)) {
                        return false;
                    }
                }
            }
            
            return true;
        },
        
        canTakeSubclassUpgrade(level) {
            // Check if already taken in this tier
            const tier = this.getTierForLevel(level);
            return !this.hasAdvancementTypeInTier('subclass_upgrade', tier);
        },
        
        canTakeProficiency(level, advNumber) {
            // Proficiency requires 2 slots - only show if this is slot 1 or if slot 1 is also proficiency
            if (advNumber === 2) {
                const adv1 = this.getAdvancementSelection(level, 1);
                return adv1 === 'proficiency';
            }
            return true;
        },
        
        canTakeMulticlass(level, advNumber) {
            // Multiclass requires 2 slots and can only be taken once
            if (advNumber === 2) {
                const adv1 = this.getAdvancementSelection(level, 1);
                return adv1 === 'multiclass';
            }
            return !this.hasAdvancementTypeAnywhere('multiclass');
        },
        
        hasAdvancementTypeInTier(type, tier) {
            const tierStartLevel = tier === 2 ? 2 : tier === 3 ? 5 : 8;
            const tierEndLevel = tier === 2 ? 4 : tier === 3 ? 7 : 10;
            
            for (let l = tierStartLevel; l <= Math.min(tierEndLevel, this.starting_level); l++) {
                const levelAdvs = this.creation_advancements[l] || [];
                if (levelAdvs.some(adv => adv.type === type)) {
                    return true;
                }
            }
            return false;
        },
        
        hasAdvancementTypeAnywhere(type) {
            for (let l = 2; l <= this.starting_level; l++) {
                const levelAdvs = this.creation_advancements[l] || [];
                if (levelAdvs.some(adv => adv.type === type)) {
                    return true;
                }
            }
            return false;
        }
    };
}
</script>

