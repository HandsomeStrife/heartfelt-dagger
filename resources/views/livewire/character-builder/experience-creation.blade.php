<!-- Experience Creation Step -->
<div class="space-y-8">
    <!-- Step Header -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-white mb-2 font-outfit">Add Experiences</h2>
        <p class="text-slate-300 font-roboto">Add experiences that shaped your character's past.</p>
    </div>

    <!-- Step Completion Indicator -->
    <template x-if="isExperienceComplete">
        <div class="my-6 p-4 bg-gradient-to-r from-emerald-500/10 to-green-500/10 border border-emerald-500/20 rounded-xl">
            <div class="flex items-center">
                <div class="bg-emerald-500 rounded-full p-2 mr-3">
                    <svg class="w-5 h-5 text-black" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div>
                    <p class="text-emerald-400 font-semibold">Experience Creation Complete!</p>
                    <p class="text-slate-300 text-sm">Your character has the required 2 experiences for character creation.</p>
                </div>
            </div>
        </div>
    </template>
    <template x-if="isExperienceInProgress">
        <div class="my-6 p-4 bg-blue-500/10 border border-blue-500/20 rounded-xl">
            <div class="flex items-center">
                <div class="bg-blue-500 rounded-full p-2 mr-3">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-blue-400 font-semibold">Experience Creation In Progress</p>
                    <p class="text-slate-300 text-sm">You have <span x-text="experienceCount"></span> of 2 required experiences. Add <span x-text="experiencesRemaining"></span> more to complete this step.</p>
                </div>
            </div>
        </div>
    </template>
    <!-- Instructions -->
    <div class="bg-slate-800/30 rounded-lg p-4">
        <h5 class="text-white font-bold text-lg mb-3 font-outfit">What Is an Experience?</h5>
        <p class="text-slate-300 text-sm mb-3">
            An Experience is a word or phrase used to encapsulate a specific set of skills, personality traits, or aptitudes your character has acquired over the course of their life.
        </p>
        <ul class="text-slate-300 text-sm space-y-1 list-disc list-inside">
            <li>Each PC starts with two Experiences, each with a +2 modifier</li>
            <li>You can spend a Hope to add an Experience modifier to a relevant action and reaction roll</li>
            <li>There's no official list -- Experiences are meant to be unique to your character</li>
            <li>However, they can't be too broad ("Lucky," "Highly Skilled") or too mechanical ("Supersonic Flight", "Invulnerable")</li>
        </ul>
    </div>

    <!-- Add New Experience -->
    <template x-if="canAddExperience">
        <div class="bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-white font-semibold font-outfit">Add New Experience</h4>
                <div class="text-sm text-slate-400">
                    <span x-text="experienceCount"></span> / 2 experiences
                </div>
            </div>
        

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="lg:col-span-1">
                    <label for="new-experience-name" class="block text-sm font-medium text-slate-300 mb-2">Experience Name</label>
                    <input 
                        dusk="new-experience-name"
                        type="text" 
                        id="new-experience-name"
                        x-model="new_experience_name"
                        @input="markAsUnsaved()"
                        placeholder="e.g., Blacksmith, Silver Tongue, Fast Learner"
                        maxlength="50"
                        class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all duration-200"
                    >
                </div>
                
                <div class="lg:col-span-1">
                    <label for="new-experience-description" class="block text-sm font-medium text-slate-300 mb-2">Description</label>
                    <input 
                        dusk="new-experience-description"
                        type="text" 
                        id="new-experience-description"
                        x-model="new_experience_description"
                        @input="markAsUnsaved()"
                        placeholder="Brief description (optional)"
                        maxlength="100"
                        class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all duration-200"
                    >
                    <div class="text-right mt-1">
                        <span class="text-xs text-slate-400" x-text="`${(new_experience_description || '').length}/100`"></span>
                    </div>
                </div>
                
                <div class="lg:col-span-1 flex items-end">
                    <button 
                        dusk="add-experience-button"
                        @click="addExperience()"
                        :disabled="!canAddNewExperience"
                        :class="{
                            'w-full px-4 py-3 rounded-lg font-semibold transition-all duration-200': true,
                            'bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-black': canAddNewExperience,
                            'bg-slate-700 text-slate-400 cursor-not-allowed': !canAddNewExperience
                        }"
                    >
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Add Experience
                    </button>
                </div>
            </div>
        </div>
    </template>

    <!-- Experience List -->
    <template x-if="experienceCount > 0">
        <div class="space-y-4 mt-6">
            <div class="flex items-center justify-between">
                <h4 class="text-white font-semibold font-outfit">Your Experiences</h4>
                <template x-if="experienceCount > 2">
                    <button 
                        @click="clearAllExperiences()"
                        class="inline-flex items-center px-3 py-2 bg-red-600/20 hover:bg-red-600/30 text-red-300 border border-red-500/30 hover:border-red-500/50 rounded-lg text-sm font-medium transition-all duration-200"
                    >
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1-1H8a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Clear All
                    </button>
                </template>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <template x-for="(experience, index) in experiences" :key="index">
                    <div 
                        :dusk="`experience-card-${index}`"
                        @click="canSelectBonusExperience(experience.name) ? selectClankBonusExperience(experience.name) : null"
                        :class="{
                            'relative group bg-gradient-to-br from-slate-800/90 to-slate-900/90 backdrop-blur border transition-all duration-300 rounded-xl p-5': true,
                            'border-purple-400/70 ring-2 ring-purple-400/30': isBonusExperience(experience.name),
                            'border-slate-700/50 hover:border-purple-500/50 hover:bg-purple-500/5 cursor-pointer': canSelectBonusExperience(experience.name),
                            'border-slate-700/50 hover:border-slate-600/70': !isBonusExperience(experience.name) && !canSelectBonusExperience(experience.name)
                        }"
                    >
                        <!-- Experience Header -->
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <h5 class="text-white font-bold font-outfit text-lg" x-text="experience.name"></h5>
                                <template x-if="isEditingExperience(index)">
                                    <!-- Edit mode -->
                                    <div class="mt-2">
                                        <textarea 
                                            :dusk="`edit-experience-description-${index}`"
                                            x-model="edit_experience_description"
                                            @input="markAsUnsaved()"
                                            placeholder="Enter description..."
                                            maxlength="100"
                                            class="w-full px-3 py-2 text-sm bg-slate-800/50 border border-slate-600 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                                            rows="2"
                                        ></textarea>
                                        <div class="flex items-center justify-between mt-2">
                                            <div class="flex gap-2">
                                                <button 
                                                    @click="saveExperienceEdit()"
                                                    class="px-3 py-1 bg-green-600/20 hover:bg-green-600/30 text-green-300 border border-green-500/30 hover:border-green-500/50 rounded text-xs font-medium transition-all duration-200"
                                                >
                                                    Save
                                                </button>
                                                <button 
                                                    @click="cancelExperienceEdit()"
                                                    class="px-3 py-1 bg-slate-600/20 hover:bg-slate-600/30 text-slate-300 border border-slate-500/30 hover:border-slate-500/50 rounded text-xs font-medium transition-all duration-200"
                                                >
                                                    Cancel
                                                </button>
                                            </div>
                                            <span class="text-xs text-slate-400" x-text="`${(edit_experience_description || '').length}/100`"></span>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="!isEditingExperience(index)">
                                    <!-- Display mode -->
                                    <template x-if="experience.description && experience.description.trim()">
                                        <p class="text-slate-300 text-sm mt-1" x-text="experience.description"></p>
                                    </template>
                                    <template x-if="!experience.description || !experience.description.trim()">
                                        <p class="text-slate-400 text-sm mt-1 italic">No description</p>
                                    </template>
                                </template>
                            </div>
                            
                            <div class="flex items-center gap-1 opacity-100 transition-opacity duration-200">
                                <template x-if="!isEditingExperience(index)">
                                    <button 
                                        :dusk="`edit-experience-${index}`"
                                        @click="startEditingExperience(index)"
                                        class="p-2 hover:bg-blue-600/20 rounded-lg"
                                        title="Edit description"
                                    >
                                        <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                </template>
                                <button 
                                    :dusk="`remove-experience-${index}`"
                                    @click="removeExperience(index)"
                                    class="p-2 hover:bg-red-600/20 rounded-lg"
                                    title="Remove experience"
                                >
                                    <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1-1H8a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Experience Modifier -->
                        <div 
                            :class="{
                                'bg-gradient-to-r from-amber-500/10 to-orange-500/10 border border-amber-500/20 rounded-lg p-3': true,
                                'ring-2 ring-purple-400/30': getExperienceModifier(experience.name) > 2
                            }"
                        >
                            <div class="flex items-center justify-between">
                                <span class="text-amber-300 font-medium text-sm">Experience Modifier</span>
                                <div class="flex items-center">
                                    <span class="text-white font-bold text-lg" x-text="`+${getExperienceModifier(experience.name)}`"></span>
                                    <template x-if="getExperienceModifier(experience.name) > 2">
                                        <span class="ml-2 text-xs px-2 py-1 bg-purple-500/20 text-purple-300 rounded" x-text="`${selectedAncestryData?.name || ''} Bonus`">
                                        </span>
                                    </template>
                                </div>
                            </div>
                            <div class="flex items-center justify-between mt-1">
                                <p class="text-slate-300 text-xs">
                                    Added to relevant rolls when this experience applies
                                    <template x-if="getExperienceModifier(experience.name) > 2">
                                        <span class="text-purple-300" x-text="` (includes +1 from ${selectedAncestryData?.name || ''} heritage)`"></span>
                                    </template>
                                </p>
                                <template x-if="hasExperienceBonus && !isBonusExperience(experience.name) && !clank_bonus_experience">
                                    <span class="text-purple-400 text-xs italic" x-text="`Click to select for your ${selectedAncestryData?.name || ''} heritage bonus (+3)`"></span>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </template>

    <!-- Experience Guide -->
    <div class="bg-blue-500/10 border border-blue-500/20 rounded-xl p-6 mt-6" x-data="{ showGuide: false }">
        <div class="flex items-center justify-between">
            <h4 class="text-blue-300 font-semibold font-outfit text-lg">Creating G.R.E.A.T. Experiences <span class="text-slate-400 text-xs italic">by OneBoxyLlama</span></h4>
            <button @click="showGuide = !showGuide" class="text-blue-300 hover:text-blue-200 transition-colors">
                <span x-show="!showGuide">Show Guide</span>
                <span x-show="showGuide">Hide Guide</span>
                <svg class="w-4 h-4 inline ml-1 transition-transform" :class="{'rotate-180': showGuide}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
        </div>
        
        <div x-show="showGuide" x-transition class="space-y-6 mt-4">
            <!-- The G.R.E.A.T. Framework -->
            <div class="bg-gradient-to-br from-amber-500/10 to-orange-500/10 border border-amber-500/20 rounded-lg p-4">
                <h5 class="text-amber-300 font-bold text-lg mb-4 font-outfit">The G.R.E.A.T. Framework</h5>
                
                <div class="flex items-center justify-between">
                    <div class="space-y-4 w-1/2">
                        <!-- Grounded -->
                        <div class="bg-slate-800/40 rounded-lg p-3">
                            <h6 class="text-white font-bold text-sm mb-2"><span class="text-amber-300">G</span> - Grounded</h6>
                            <p class="text-slate-300 text-sm mb-2">Experiences should stand on their own, and not require a specific mechanic or spell to be useful.</p>
                            <div class="text-xs">
                                <span class="text-red-300">❌ "Take Flight"</span> <span class="text-slate-400">- relies on having flight</span><br>
                                <span class="text-green-300">✅ "Acrobat"</span> <span class="text-slate-400">- broadly useful without hinging on a single ability</span>
                            </div>
                        </div>

                        <!-- Relatable -->
                        <div class="bg-slate-800/40 rounded-lg p-3">
                            <h6 class="text-white font-bold text-sm mb-2"><span class="text-amber-300">R</span> - Relatable</h6>
                            <p class="text-slate-300 text-sm mb-2">Does the Experience connect naturally to the character's story, ancestry, class, or community?</p>
                            <div class="text-xs">
                                <span class="text-green-300">✅ "Mage's Apprentice"</span> <span class="text-slate-400">- ties into a clear backstory beat and skillset</span>
                            </div>
                        </div>

                        <!-- Explainable -->
                        <div class="bg-slate-800/40 rounded-lg p-3">
                            <h6 class="text-white font-bold text-sm mb-2"><span class="text-amber-300">E</span> - Explainable</h6>
                            <p class="text-slate-300 text-sm mb-2">Can you describe in plain terms when the Experience will apply to your roll?</p>
                            <div class="text-xs">
                                <span class="text-red-300">❌ "Always Ready"</span> <span class="text-slate-400">- too vague, what does it actually mean?</span><br>
                                <span class="text-green-300">✅ "My Nightmares Warned Me"</span> <span class="text-slate-400">- clearly applies when rolling to avoid being surprised</span>
                            </div>
                        </div>

                        <!-- Adaptable -->
                        <div class="bg-slate-800/40 rounded-lg p-3">
                            <h6 class="text-white font-bold text-sm mb-2"><span class="text-amber-300">A</span> - Adaptable</h6>
                            <p class="text-slate-300 text-sm mb-2">Can the Experience apply in different situations, not just in one hyper-specific moment?</p>
                            <div class="text-xs">
                                <span class="text-red-300">❌ "Breaker of Windows"</span> <span class="text-slate-400">- too narrow, very specific</span><br>
                                <span class="text-green-300">✅ "I've Got Your Back"</span> <span class="text-slate-400">- applies to any situation helping and protecting allies</span>
                            </div>
                        </div>

                        <!-- Transformative -->
                        <div class="bg-slate-800/40 rounded-lg p-3">
                            <h6 class="text-white font-bold text-sm mb-2"><span class="text-amber-300">T</span> - Transformative</h6>
                            <p class="text-slate-300 text-sm mb-2">Does the Experience shape how your character interacts with the world beyond the dice?</p>
                            <div class="text-xs">
                                <span class="text-green-300">✅ "Exploit the Male Gaze"</span> <span class="text-slate-400">- isn't just a roll bonus, it's roleplay fuel that shapes social encounters</span>
                            </div>
                        </div>
                    </div>
                    <div class="w-1/2">
                        <img src="{{ asset('img/experience/chart.webp') }}" alt="Experience Creation Chart" class="mx-auto rounded-lg max-w-full h-auto">
                    </div>
                </div>
            </div>

            <!-- Real World Application -->
            <div class="bg-purple-500/10 border border-purple-500/20 rounded-lg p-4">
                <h6 class="text-purple-300 font-bold text-sm mb-3">Writing Experiences in the Real World</h6>
                <p class="text-slate-300 text-sm mb-3">
                    Because the definitions are fuzzy, it's inevitable that people will disagree about what constitutes "too vague" or "too specific". The important bit is that both the Player and the GM are on the same page.
                </p>
                <div class="bg-slate-800/40 rounded-lg p-3">
                    <p class="text-slate-300 text-xs mb-2">
                        <strong class="text-purple-300">Example:</strong> "One-Hit Killer" might start too broad and at odds with mechanics where most adversaries won't die in one hit. But after discussion between Player and GM, they agree that it represents a reputation the PC strives to uphold, and they play up their one-hit KOs when fighting Minions and Hordes.
                    </p>
                    <p class="text-slate-300 text-xs">
                        This understanding transforms it from an always-on combat boost that conflicts with how combat functions, to a more specific situation that can apply in combat AND be adapted to social situations!
                    </p>
                </div>
            </div>

            <!-- Popular Examples -->
            <div class="bg-gradient-to-r from-blue-500/20 to-purple-500/20 border border-blue-400/30 rounded-lg p-3">
                <p class="text-slate-200 text-sm">
                    <strong class="text-blue-300">Great examples:</strong> 
                    Mage's Apprentice, I've Got Your Back, Silver Tongue, Battle-Hardened, My Nightmares Warned Me, Acrobat, Hold the Line, Blacksmith
                </p>
            </div>

            <!-- Credit -->
            <div class="text-center">
                <p class="text-slate-400 text-xs italic">
                    Experience creation guide by OneBoxyLlama
                </p>
            </div>
        </div>
    </div>
</div>
