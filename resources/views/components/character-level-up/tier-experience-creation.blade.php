@props(['character', 'advancementChoices'])

<!-- Experience Creation Section -->
<div class="bg-slate-800/30 border border-slate-600/50 rounded-lg p-6 mb-6"
     x-data="{ 
        experienceName: $wire.entangle('new_experience_name'),
        experienceDescription: $wire.entangle('new_experience_description'),
        isCreated: @entangle('advancement_choices.tier_experience').live,
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
            
            // Store locally without server call
            this.isCreated = {
                name: this.experienceName.trim(),
                description: this.experienceDescription.trim(),
                modifier: 2
            };
        },
        
        removeExperience() {
            this.isCreated = null;
            this.experienceName = '';
            this.experienceDescription = '';
            this.validationError = '';
        }
     }">
    <div class="flex items-center mb-4">
        <div class="flex items-center space-x-2">
            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <h4 class="font-semibold text-slate-100 text-lg">Create Your New Experience</h4>
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
    
    <template x-if="isCreated">
        <!-- Show Created Experience -->
        <div class="bg-slate-800/50 border border-slate-600 rounded-lg p-4">
            <div class="flex items-start justify-between">
                <div>
                    <h5 class="text-slate-200 font-medium" x-text="isCreated.name"></h5>
                    <template x-if="isCreated.description">
                        <p class="text-slate-400 text-sm mt-1" x-text="isCreated.description"></p>
                    </template>
                    <p class="text-emerald-400 text-sm mt-2">+2 modifier</p>
                </div>
                <button type="button" 
                        @click="removeExperience()"
                        class="px-3 py-1 bg-slate-600 hover:bg-slate-500 text-slate-200 rounded-lg transition-colors text-sm">
                    Change
                </button>
            </div>
        </div>
    </template>
    
    <template x-if="!isCreated">
        <!-- Experience Creation Form -->
        <div>
            <p class="text-slate-400 text-sm mb-4">
                Define a new Experience that represents skills, background, or expertise your character has gained.
                <span class="text-slate-500 italic">If you can't think of one right now, you can write something temporarily and change it later.</span>
            </p>
            
            <template x-if="validationError">
                <div class="bg-red-500/10 border border-red-500/20 rounded-lg p-3 mb-4">
                    <p class="text-red-400 text-sm" x-text="validationError"></p>
                </div>
            </template>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="tier-experience-name" class="block text-sm font-medium text-slate-300 mb-2">Experience Name</label>
                    <input 
                        type="text" 
                        id="tier-experience-name"
                        x-model="experienceName"
                        placeholder="e.g., Blacksmith, Silver Tongue, Fast Learner"
                        maxlength="100"
                        class="w-full px-3 py-2 bg-slate-900/50 border border-slate-600 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                    >
                </div>
                
                <div>
                    <label for="tier-experience-description" class="block text-sm font-medium text-slate-300 mb-2">Description (optional)</label>
                    <input 
                        type="text" 
                        id="tier-experience-description"
                        x-model="experienceDescription"
                        placeholder="Brief description"
                        maxlength="100"
                        class="w-full px-3 py-2 bg-slate-900/50 border border-slate-600 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                    >
                </div>
            </div>
            
            <div class="flex justify-end mt-4">
                <button type="button" 
                        @click="createExperience()"
                        :disabled="!experienceName || experienceName.trim() === ''"
                        :class="(!experienceName || experienceName.trim() === '') ? 'opacity-50 cursor-not-allowed' : ''"
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition-colors">
                    Create Experience
                </button>
            </div>
        </div>
    </template>
</div>
