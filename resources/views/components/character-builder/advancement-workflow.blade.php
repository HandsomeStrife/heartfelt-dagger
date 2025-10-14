@props([
    'character_key' => null,
    'starting_level' => 1,
    'current_level' => 2,
    'creation_advancements' => [],
    'creation_tier_experiences' => [],
    'creation_domain_cards' => [],
])

<div x-data="{
    currentLevel: @js($current_level),
    startingLevel: @js($starting_level),
    advancementStep: 'tier_achievements', // tier_achievements, domain_card, advancements, confirmation
    isTierAchievementLevel(level) {
        return [2, 5, 8].includes(level);
    },
    getTierForLevel(level) {
        if (level >= 8) return 4;
        if (level >= 5) return 3;
        if (level >= 2) return 2;
        return 1;
    },
    getTierColor(tier) {
        return {
            1: 'blue',
            2: 'emerald',
            3: 'purple',
            4: 'amber'
        }[tier] || 'slate';
    },
    isLevelComplete(level) {
        // Check if this level has all required selections
        const hasTierExperience = !this.isTierAchievementLevel(level) || @js($creation_tier_experiences)[level];
        const hasDomainCard = @js($creation_domain_cards)[level];
        const hasAdvancements = (@js($creation_advancements)[level] || []).length === 2;
        
        return hasTierExperience && hasDomainCard && hasAdvancements;
    },
    canProceedToNextLevel() {
        return this.isLevelComplete(this.currentLevel);
    },
    goToNextLevel() {
        if (this.currentLevel < this.startingLevel) {
            this.currentLevel++;
            this.advancementStep = this.isTierAchievementLevel(this.currentLevel) ? 'tier_achievements' : 'domain_card';
        }
    },
    goToPreviousLevel() {
        if (this.currentLevel > 2) {
            this.currentLevel--;
            this.advancementStep = 'confirmation';
        }
    },
    goToLevel(level) {
        if (level >= 2 && level <= this.startingLevel) {
            this.currentLevel = level;
            this.advancementStep = this.isTierAchievementLevel(level) ? 'tier_achievements' : 'domain_card';
        }
    }
}" class="space-y-8">

    <!-- Progress Header -->
    <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700 rounded-2xl p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-outfit font-bold text-white">
                Character Advancement Selection
            </h2>
            <div class="text-sm text-slate-400">
                <span x-text="`Level ${currentLevel} of ${startingLevel}`"></span>
                <span class="mx-2">•</span>
                <span x-text="`Tier ${getTierForLevel(currentLevel)}`"></span>
            </div>
        </div>

        <!-- Level Progress Pills -->
        <div class="flex flex-wrap gap-2">
            @for ($level = 2; $level <= 10; $level++)
                <button 
                    type="button"
                    x-show="{{ $level }} <= startingLevel"
                    @click="goToLevel({{ $level }})"
                    :class="{
                        'border-amber-400 bg-amber-400/20 text-amber-400': currentLevel === {{ $level }},
                        'border-green-400 bg-green-400/20 text-green-400': currentLevel !== {{ $level }} && isLevelComplete({{ $level }}),
                        'border-slate-600 bg-slate-800/50 text-slate-300 hover:border-slate-500': currentLevel !== {{ $level }} && !isLevelComplete({{ $level }})
                    }"
                    class="px-4 py-2 rounded-lg border-2 font-medium transition-all duration-200 text-sm"
                >
                    <span class="hidden sm:inline">Level </span>{{ $level }}
                    <template x-if="isLevelComplete({{ $level }}) && currentLevel !== {{ $level }}">
                        <svg class="inline-block w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </template>
                </button>
            @endfor
        </div>

        <!-- Overall Progress Bar -->
        <div class="mt-4">
            <div class="flex items-center justify-between text-sm text-slate-400 mb-2">
                <span>Overall Progress</span>
                <span x-text="`${Array.from({length: startingLevel - 1}, (_, i) => i + 2).filter(level => isLevelComplete(level)).length} / ${startingLevel - 1} levels complete`"></span>
            </div>
            <div class="w-full bg-slate-800 rounded-full h-2">
                <div 
                    class="bg-gradient-to-r from-amber-500 to-orange-500 h-2 rounded-full transition-all duration-300"
                    :style="`width: ${(Array.from({length: startingLevel - 1}, (_, i) => i + 2).filter(level => isLevelComplete(level)).length / (startingLevel - 1)) * 100}%`"
                ></div>
            </div>
        </div>
    </div>

    <!-- Current Level Content -->
    <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700 rounded-2xl p-8">
        
        <!-- Level Header -->
        <div class="mb-8">
            <div class="flex items-center space-x-4 mb-4">
                <div 
                    :class="`bg-${getTierColor(getTierForLevel(currentLevel))}-500/20 border-2 border-${getTierColor(getTierForLevel(currentLevel))}-500 text-${getTierColor(getTierForLevel(currentLevel))}-400`"
                    class="w-16 h-16 rounded-full flex items-center justify-center font-bold text-xl"
                >
                    <span x-text="currentLevel"></span>
                </div>
                <div>
                    <h3 class="text-xl font-outfit font-bold text-white">
                        <span x-text="`Level ${currentLevel}`"></span>
                        <span class="text-slate-400 ml-2">•</span>
                        <span x-text="`Tier ${getTierForLevel(currentLevel)}`" class="text-slate-400 ml-2"></span>
                    </h3>
                    <p class="text-sm text-slate-400 mt-1">
                        Complete all requirements to proceed to the next level
                    </p>
                </div>
            </div>

            <!-- Step Progress Indicators -->
            <div class="flex items-center space-x-2 sm:space-x-4 overflow-x-auto">
                <!-- Tier Achievement Step (if applicable) -->
                <template x-if="isTierAchievementLevel(currentLevel)">
                    <div class="flex items-center space-x-2">
                        <div 
                            class="flex items-center space-x-2 whitespace-nowrap"
                            :class="advancementStep === 'tier_achievements' ? 'text-amber-400' : (['domain_card', 'advancements', 'confirmation'].includes(advancementStep) ? 'text-green-400' : 'text-slate-400')"
                        >
                            <div 
                                class="w-8 h-8 rounded-full border-2 flex items-center justify-center text-sm font-bold"
                                :class="advancementStep === 'tier_achievements' ? 'border-amber-400 bg-amber-400/20' : (['domain_card', 'advancements', 'confirmation'].includes(advancementStep) ? 'border-green-400 bg-green-400/20' : 'border-slate-400')"
                            >
                                1
                            </div>
                            <span class="font-medium hidden sm:inline">Tier Achievement</span>
                            <span class="font-medium sm:hidden">Tier</span>
                        </div>
                        <div class="w-6 sm:w-12 h-px bg-slate-600"></div>
                    </div>
                </template>

                <!-- Domain Card Step -->
                <div class="flex items-center space-x-2">
                    <div 
                        class="flex items-center space-x-2 whitespace-nowrap"
                        :class="advancementStep === 'domain_card' ? 'text-amber-400' : (['advancements', 'confirmation'].includes(advancementStep) ? 'text-green-400' : 'text-slate-400')"
                    >
                        <div 
                            class="w-8 h-8 rounded-full border-2 flex items-center justify-center text-sm font-bold"
                            :class="advancementStep === 'domain_card' ? 'border-amber-400 bg-amber-400/20' : (['advancements', 'confirmation'].includes(advancementStep) ? 'border-green-400 bg-green-400/20' : 'border-slate-400')"
                        >
                            <span x-text="isTierAchievementLevel(currentLevel) ? '2' : '1'"></span>
                        </div>
                        <span class="font-medium">Domain Card</span>
                    </div>
                    <div class="w-6 sm:w-12 h-px bg-slate-600"></div>
                </div>

                <!-- Advancements Step -->
                <div class="flex items-center space-x-2">
                    <div 
                        class="flex items-center space-x-2 whitespace-nowrap"
                        :class="advancementStep === 'advancements' ? 'text-amber-400' : (advancementStep === 'confirmation' ? 'text-green-400' : 'text-slate-400')"
                    >
                        <div 
                            class="w-8 h-8 rounded-full border-2 flex items-center justify-center text-sm font-bold"
                            :class="advancementStep === 'advancements' ? 'border-amber-400 bg-amber-400/20' : (advancementStep === 'confirmation' ? 'border-green-400 bg-green-400/20' : 'border-slate-400')"
                        >
                            <span x-text="isTierAchievementLevel(currentLevel) ? '3' : '2'"></span>
                        </div>
                        <span class="font-medium">Advancements</span>
                    </div>
                    <div class="w-6 sm:w-12 h-px bg-slate-600"></div>
                </div>

                <!-- Confirmation Step -->
                <div class="flex items-center space-x-2 whitespace-nowrap"
                     :class="advancementStep === 'confirmation' ? 'text-amber-400' : 'text-slate-400'">
                    <div 
                        class="w-8 h-8 rounded-full border-2 flex items-center justify-center text-sm font-bold"
                        :class="advancementStep === 'confirmation' ? 'border-amber-400 bg-amber-400/20' : 'border-slate-400'"
                    >
                        <span x-text="isTierAchievementLevel(currentLevel) ? '4' : '3'"></span>
                    </div>
                    <span class="font-medium">Confirm</span>
                </div>
            </div>
        </div>

        <!-- Step Content -->
        <div class="min-h-[400px]">
            <!-- Tier Achievement Content -->
            <div x-show="advancementStep === 'tier_achievements'" x-cloak>
                <template x-if="isTierAchievementLevel(currentLevel)">
                    <div>
                        {{-- Tier Achievement Component will be rendered here dynamically --}}
                        <div class="tier-achievement-placeholder">
                            <x-character.tier-achievement
                                :level="$current_level"
                                :show_experience_form="true"
                                :experience_name="$creation_tier_experiences[$current_level]['name'] ?? ''"
                                :experience_description="$creation_tier_experiences[$current_level]['description'] ?? ''"
                                :is_experience_created="isset($creation_tier_experiences[$current_level])"
                                wire:experience_name="creation_tier_experiences.{{ $current_level }}.name"
                                wire:experience_description="creation_tier_experiences.{{ $current_level }}.description"
                                wire:is_created="creation_tier_experiences.{{ $current_level }}"
                            />
                        </div>
                        
                        <div class="flex justify-end mt-8">
                            <button 
                                type="button"
                                @click="advancementStep = 'domain_card'"
                                wire:disabled="!isset($creation_tier_experiences[$current_level])"
                                class="px-6 py-3 bg-gradient-to-r from-amber-500 to-orange-500 text-white font-semibold rounded-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed enabled:hover:from-amber-600 enabled:hover:to-orange-600"
                            >
                                Continue to Domain Card Selection
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Domain Card Content -->
            <div x-show="advancementStep === 'domain_card'" x-cloak>
                {{-- Domain Card Selector Component --}}
                @php
                    $currentLevelDomainCard = $creation_domain_cards[$current_level] ?? null;
                @endphp
                
                <div 
                    x-data="{
                        selectedDomainCard: @js($currentLevelDomainCard),
                        domainCardComplete: @js(!empty($currentLevelDomainCard)),
                        
                        handleDomainCardSelection(event) {
                            this.selectedDomainCard = event.detail.cardKey;
                            this.domainCardComplete = true;
                            
                            // Sync with Livewire
                            @this.set('creation_domain_cards.{{ $current_level }}', event.detail.cardKey);
                        }
                    }"
                    @domain-card-selected.window="handleDomainCardSelection($event)"
                >
                    <x-character.domain-card-selector
                        :cards="$this->getAvailableDomainCards()"
                        :selected-card="$currentLevelDomainCard"
                        :level="$current_level"
                        :group-by-domain="true"
                    />
                    
                    <div class="flex justify-between mt-8">
                        <button 
                            type="button"
                            @click="advancementStep = isTierAchievementLevel(currentLevel) ? 'tier_achievements' : null"
                            x-show="isTierAchievementLevel(currentLevel)"
                            class="px-6 py-3 bg-slate-700 text-white font-semibold rounded-lg hover:bg-slate-600 transition-all duration-200"
                        >
                            Back
                        </button>
                        <button 
                            type="button"
                            @click="advancementStep = 'advancements'"
                            x-bind:disabled="!domainCardComplete"
                            x-bind:class="!domainCardComplete ? 'opacity-50 cursor-not-allowed' : 'hover:from-amber-600 hover:to-orange-600'"
                            class="px-6 py-3 bg-gradient-to-r from-amber-500 to-orange-500 text-white font-semibold rounded-lg transition-all duration-200"
                            :class="!isTierAchievementLevel(currentLevel) ? 'ml-auto' : ''"
                        >
                            Continue to Advancements
                        </button>
                    </div>
                </div>
            </div>

            <!-- Advancements Content -->
            <div x-show="advancementStep === 'advancements'" x-cloak>
                {{-- Advancement Option Grid Component --}}
                @php
                    $currentLevelAdvancements = $creation_advancements[$current_level] ?? [];
                @endphp
                
                <div 
                    x-data="{
                        advancementSelections: @js($currentLevelAdvancements),
                        advancementsComplete: false,
                        
                        handleAdvancementSelection(event) {
                            this.advancementSelections = event.detail.selections;
                            this.advancementsComplete = event.detail.selections.length === 2;
                            
                            // Sync with Livewire
                            @this.set('creation_advancements.{{ $current_level }}', event.detail.selections);
                        }
                    }"
                    @advancement-selection-changed.window="handleAdvancementSelection($event)"
                >
                    <x-character.advancement-option-grid
                        :level="$current_level"
                        :options="$this->getAvailableAdvancementOptions()"
                        :selections="$currentLevelAdvancements"
                        :required-count="2"
                    />
                    
                    {{-- Special Selection Modals --}}
                    <x-character.advancement-trait-selector
                        :show="false"
                        :level="$current_level"
                        :marked-traits="$this->getMarkedTraitsForLevel($current_level)"
                    />
                    
                    <x-character.advancement-choice-modal
                        :show="false"
                        title="Select Domain"
                        description="Choose a domain for your multiclass advancement"
                    />
                    
                    <div class="flex justify-between mt-8">
                        <button 
                            type="button"
                            @click="advancementStep = 'domain_card'"
                            class="px-6 py-3 bg-slate-700 text-white font-semibold rounded-lg hover:bg-slate-600 transition-all duration-200"
                        >
                            Back
                        </button>
                        <button 
                            type="button"
                            @click="advancementStep = 'confirmation'"
                            x-bind:disabled="!advancementsComplete"
                            x-bind:class="!advancementsComplete ? 'opacity-50 cursor-not-allowed' : 'hover:from-amber-600 hover:to-orange-600'"
                            class="px-6 py-3 bg-gradient-to-r from-amber-500 to-orange-500 text-white font-semibold rounded-lg transition-all duration-200"
                        >
                            Review Level <span x-text="currentLevel"></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Confirmation Content -->
            <div x-show="advancementStep === 'confirmation'" x-cloak>
                {{-- Real-time Validation for Current Level --}}
                <div 
                    x-data="{
                        validationErrors: [],
                        isValidating: false,
                        hasValidated: false,
                        validationTimeout: null,
                        
                        // Debounced validation - waits 500ms after last change
                        debouncedValidate() {
                            clearTimeout(this.validationTimeout);
                            this.validationTimeout = setTimeout(() => {
                                this.validateLevel();
                            }, 500);
                        },
                        
                        async validateLevel() {
                            this.isValidating = true;
                            this.hasValidated = false;
                            try {
                                const errors = await @this.call('validateCurrentLevel');
                                this.validationErrors = errors || [];
                                this.hasValidated = true;
                            } catch (error) {
                                console.error('Validation error:', error);
                                this.validationErrors = ['An error occurred during validation. Please try again.'];
                                this.hasValidated = true;
                            } finally {
                                this.isValidating = false;
                            }
                        }
                    }"
                    x-init="validateLevel()"
                    @advancement-selection-changed.window="debouncedValidate()"
                    @domain-card-selected.window="debouncedValidate()"
                    @tier-experience-created.window="debouncedValidate()"
                >
                    <!-- Validation Loading -->
                    <div x-show="isValidating && !hasValidated" class="mb-6">
                        <div class="p-4 rounded-lg bg-blue-500/10 border border-blue-500/30">
                            <div class="flex items-center space-x-3">
                                <svg class="w-5 h-5 text-blue-400 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-sm text-blue-300">Validating selections...</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Validation Errors -->
                    <template x-if="hasValidated && validationErrors.length > 0">
                        <div class="mb-6">
                            <x-character.validation-errors
                                x-bind:errors="validationErrors"
                                title="Level Incomplete"
                                severity="warning"
                            />
                        </div>
                    </template>
                    
                    <!-- Success Message -->
                    <template x-if="hasValidated && validationErrors.length === 0">
                        <div class="mb-6">
                            <div class="p-4 rounded-lg bg-green-500/10 border-2 border-green-500/30">
                                <div class="flex items-center space-x-3">
                                    <svg class="w-6 h-6 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <div>
                                        <h5 class="text-sm font-bold text-green-400">Level Complete!</h5>
                                        <p class="text-xs text-green-300 mt-0.5">All required selections have been made for this level.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                
                    <div class="space-y-6">
                        <div class="text-center py-4">
                            <h4 class="text-xl font-outfit font-bold text-white mb-2">
                                Level <span x-text="currentLevel"></span> Summary
                            </h4>
                            <p class="text-slate-400">Review your selections before continuing</p>
                        </div>

                    <!-- Tier Achievement Summary (if applicable) -->
                    <template x-if="isTierAchievementLevel(currentLevel)">
                        <div class="bg-slate-800/50 border border-slate-700 rounded-lg p-4">
                            <h5 class="font-semibold text-emerald-400 mb-2">✓ Tier Achievement</h5>
                            <ul class="text-sm text-slate-300 space-y-1 ml-4">
                                <li>• New Experience created (+2 modifier)</li>
                                <li>• Proficiency increased</li>
                                <template x-if="[5, 8].includes(currentLevel)">
                                    <li>• Marked traits cleared</li>
                                </template>
                            </ul>
                        </div>
                    </template>

                    <!-- Domain Card Summary -->
                    <div class="bg-slate-800/50 border border-slate-700 rounded-lg p-4">
                        <h5 class="font-semibold text-blue-400 mb-2">Domain Card Selected</h5>
                        <p class="text-sm text-slate-300 ml-4">Card selection will be shown here</p>
                    </div>

                    <!-- Advancements Summary -->
                    <div class="bg-slate-800/50 border border-slate-700 rounded-lg p-4">
                        <h5 class="font-semibold text-purple-400 mb-2">Advancements Selected</h5>
                        <p class="text-sm text-slate-300 ml-4">2 advancement options will be shown here</p>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-between pt-4">
                        <button 
                            type="button"
                            @click="advancementStep = 'advancements'"
                            class="px-6 py-3 bg-slate-700 text-white font-semibold rounded-lg hover:bg-slate-600 transition-all duration-200"
                        >
                            Edit Selections
                        </button>
                        <button 
                            type="button"
                            @click="goToNextLevel()"
                            x-show="currentLevel < startingLevel"
                            x-bind:disabled="validationErrors.length > 0"
                            x-bind:class="validationErrors.length > 0 ? 'opacity-50 cursor-not-allowed' : 'hover:from-green-600 hover:to-emerald-700'"
                            class="px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white font-semibold rounded-lg transition-all duration-200"
                        >
                            Confirm & Continue to Level <span x-text="currentLevel + 1"></span>
                        </button>
                        <button 
                            type="button"
                            @click="$dispatch('complete-advancements')"
                            x-show="currentLevel === startingLevel"
                            x-bind:disabled="validationErrors.length > 0"
                            x-bind:class="validationErrors.length > 0 ? 'opacity-50 cursor-not-allowed' : 'hover:from-green-600 hover:to-emerald-700'"
                            class="px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white font-semibold rounded-lg transition-all duration-200"
                        >
                            Complete Advancement Selection
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Footer -->
    <div class="flex items-center justify-between">
        <button 
            type="button"
            @click="goToPreviousLevel()"
            x-show="currentLevel > 2"
            class="px-6 py-3 bg-slate-700 text-white font-semibold rounded-lg hover:bg-slate-600 transition-all duration-200"
        >
            ← Previous Level
        </button>
        <div class="text-sm text-slate-400">
            <span x-text="`${currentLevel - 1} of ${startingLevel - 1} levels complete`"></span>
        </div>
    </div>

</div>

<style>
    [x-cloak] {
        display: none !important;
    }
</style>



