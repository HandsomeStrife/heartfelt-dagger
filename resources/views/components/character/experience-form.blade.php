@props([
    'level' => null,
    'experience_name' => '',
    'experience_description' => '',
    'is_created' => false,
    'context' => 'creation', // 'creation' or 'levelup'
])

<!-- Experience Creation/Selection Section -->
<div class="bg-slate-800/30 border border-slate-600/50 rounded-lg p-6"
     x-data="{ 
        experienceName: @js($experience_name ?? ''),
        experienceDescription: @js($experience_description ?? ''),
        isCreated: @js($is_created),
        validationError: '',
        
        createExperience() {
            this.validationError = '';
            
            if (!this.experienceName || this.experienceName.trim() === '') {
                this.validationError = 'Experience name is required.';
                return;
            }
            
            if (this.experienceName.length > 100) {
                this.validationError = 'Experience name must be 100 characters or less.';
                return;
            }
            
            // Store locally
            this.isCreated = {
                name: this.experienceName.trim(),
                description: this.experienceDescription ? this.experienceDescription.trim() : '',
                modifier: 2
            };
            
            // Dispatch event for parent component
            $dispatch('experience-created', {
                level: {{ $level }},
                name: this.experienceName.trim(),
                description: this.experienceDescription ? this.experienceDescription.trim() : ''
            });
        },
        
        removeExperience() {
            this.isCreated = null;
            this.experienceName = '';
            this.experienceDescription = '';
            this.validationError = '';
            
            // Dispatch event for parent component
            $dispatch('experience-removed', { level: {{ $level }} });
        }
     }">
    
    <!-- Header -->
    <div class="flex items-center mb-4">
        <div class="flex items-center space-x-2">
            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <h4 class="font-semibold text-slate-100 text-lg">
                @if($context === 'creation')
                    Create Tier Achievement Experience
                @else
                    Create Your New Experience
                @endif
            </h4>
        </div>
        <div class="ml-auto">
            <template x-if="isCreated">
                <div class="bg-emerald-500/20 text-emerald-400 px-3 py-1 rounded-full text-sm font-medium">
                    ✓ Created
                </div>
            </template>
            <template x-if="!isCreated">
                <div class="bg-amber-500/20 text-amber-400 px-3 py-1 rounded-full text-sm font-medium">
                    Required
                </div>
            </template>
        </div>
    </div>
    
    <!-- Created Experience Display -->
    <template x-if="isCreated">
        <div class="bg-slate-800/50 border border-slate-600 rounded-lg p-4">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <h5 class="text-slate-200 font-medium" x-text="isCreated.name"></h5>
                    <template x-if="isCreated.description">
                        <p class="text-slate-400 text-sm mt-1" x-text="isCreated.description"></p>
                    </template>
                    <div class="mt-2 flex items-center space-x-2">
                        <span class="text-emerald-400 text-sm font-medium">+2 modifier</span>
                        <span class="text-slate-500 text-sm">•</span>
                        <span class="text-slate-400 text-sm">Level {{ $level }} tier achievement</span>
                    </div>
                </div>
                <button type="button" 
                        @click="removeExperience()"
                        class="px-3 py-1 bg-slate-600 hover:bg-slate-500 text-slate-200 rounded-lg transition-colors text-sm ml-4">
                    Change
                </button>
            </div>
        </div>
    </template>
    
    <!-- Experience Creation Form -->
    <template x-if="!isCreated">
        <div>
            <p class="text-slate-400 text-sm mb-4">
                Define a new Experience that represents skills, background, or expertise your character has gained.
                @if($context === 'creation')
                    This Experience grants a +2 modifier when relevant to a check.
                @else
                    <span class="text-slate-500 italic">If you can't think of one right now, you can write something temporarily and change it later.</span>
                @endif
            </p>
            
            <!-- Validation Error -->
            <template x-if="validationError">
                <div class="bg-red-500/10 border border-red-500/20 rounded-lg p-3 mb-4">
                    <p class="text-red-400 text-sm" x-text="validationError"></p>
                </div>
            </template>
            
            <!-- Form Fields -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="experience-name-{{ $level }}" class="block text-sm font-medium text-slate-300 mb-2">
                        Experience Name <span class="text-red-400">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="experience-name-{{ $level }}"
                        x-model="experienceName"
                        placeholder="e.g., Blacksmith, Silver Tongue, Fast Learner"
                        maxlength="100"
                        @keydown.enter="createExperience()"
                        class="w-full px-3 py-2 bg-slate-900/50 border border-slate-600 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200"
                    >
                    <p class="text-slate-500 text-xs mt-1" x-show="experienceName && experienceName.length > 0">
                        <span x-text="experienceName.length"></span>/100 characters
                    </p>
                </div>
                
                <div>
                    <label for="experience-description-{{ $level }}" class="block text-sm font-medium text-slate-300 mb-2">
                        Description (optional)
                    </label>
                    <input 
                        type="text" 
                        id="experience-description-{{ $level }}"
                        x-model="experienceDescription"
                        placeholder="Brief description of the experience"
                        maxlength="200"
                        @keydown.enter="createExperience()"
                        class="w-full px-3 py-2 bg-slate-900/50 border border-slate-600 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all duration-200"
                    >
                    <p class="text-slate-500 text-xs mt-1" x-show="experienceDescription && experienceDescription.length > 0">
                        <span x-text="experienceDescription.length"></span>/200 characters
                    </p>
                </div>
            </div>
            
            <!-- Examples -->
            <div class="mt-4 p-3 bg-slate-900/30 border border-slate-700 rounded-lg">
                <p class="text-slate-400 text-xs font-medium mb-1">Example Experiences:</p>
                <div class="flex flex-wrap gap-2 mt-2">
                    <button type="button" 
                            @click="experienceName = 'Combat Veteran'; experienceDescription = 'Survived countless battles'"
                            class="px-2 py-1 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs rounded border border-slate-600 transition-colors">
                        Combat Veteran
                    </button>
                    <button type="button" 
                            @click="experienceName = 'Silver Tongue'; experienceDescription = 'Skilled in persuasion and negotiation'"
                            class="px-2 py-1 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs rounded border border-slate-600 transition-colors">
                        Silver Tongue
                    </button>
                    <button type="button" 
                            @click="experienceName = 'Wilderness Survival'; experienceDescription = 'Expert at surviving in harsh environments'"
                            class="px-2 py-1 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs rounded border border-slate-600 transition-colors">
                        Wilderness Survival
                    </button>
                    <button type="button" 
                            @click="experienceName = 'Arcane Scholar'; experienceDescription = 'Extensive knowledge of magical theory'"
                            class="px-2 py-1 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs rounded border border-slate-600 transition-colors">
                        Arcane Scholar
                    </button>
                </div>
            </div>
            
            <!-- Create Button -->
            <div class="flex justify-end mt-4">
                <button type="button" 
                        data-test="create-tier-experience-{{ $level }}"
                        @click="createExperience()"
                        :disabled="!experienceName || (typeof experienceName === 'string' && experienceName.trim() === '')"
                        :class="(!experienceName || (typeof experienceName === 'string' && experienceName.trim() === '')) ? 'opacity-50 cursor-not-allowed' : 'hover:bg-emerald-700'"
                        class="px-4 py-2 bg-emerald-600 text-white font-medium rounded-lg transition-colors">
                    Create Experience
                </button>
            </div>
        </div>
    </template>
</div>


