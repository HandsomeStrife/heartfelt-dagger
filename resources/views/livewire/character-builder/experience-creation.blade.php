<!-- Experience Creation Step -->
<div class="space-y-8">
    <!-- Step Header -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-white mb-2 font-outfit">Add Experiences</h2>
        <p class="text-slate-300 font-roboto">Add experiences that shaped your character's past.</p>
    </div>

    <!-- Step Completion Indicator -->
    @if(count($character->experiences) >= 2)
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
    @elseif(count($character->experiences) > 0)
        <div class="my-6 p-4 bg-blue-500/10 border border-blue-500/20 rounded-xl">
            <div class="flex items-center">
                <div class="bg-blue-500 rounded-full p-2 mr-3">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-blue-400 font-semibold">Experience Creation In Progress</p>
                    <p class="text-slate-300 text-sm">You have {{ count($character->experiences) }} of 2 required experiences. Add {{ 2 - count($character->experiences) }} more to complete this step.</p>
                </div>
            </div>
        </div>
    @endif
    <!-- Instructions -->
    <div class="bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-xl p-6">
        <h3 class="text-lg font-bold text-white font-outfit mb-3">Create Your Experiences</h3>
        <p class="text-slate-300 text-sm mb-4">
            Create exactly 2 experiences that represent specific skills, knowledge, or training your character has gained. 
            During play, you can spend 1 Hope to utilize an experience, adding its modifier to a relevant roll.
        </p>
        <div class="bg-amber-500/10 border border-amber-500/20 rounded-lg p-4">
            <p class="text-amber-300 text-sm mb-2">
                <strong>How Experiences Work:</strong> Each experience is a word or phrase representing specific skills, 
                personality traits, or aptitudes your character has acquired. All experiences have a +2 modifier.
            </p>
            <p class="text-amber-300 text-xs">
                <strong>Guidelines:</strong> Experiences can't be too broadly applicable (avoid "Lucky" or "Highly Skilled") 
                and can't grant specific mechanical benefits like magic spells or special abilities.
            </p>
        </div>

    </div>

    <!-- Add New Experience -->
    <div class="bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-xl p-6" 
         x-data="{ experienceName: '' }"
         @experience-added.window="experienceName = ''">
        <div class="flex items-center justify-between mb-4">
            <h4 class="text-white font-semibold font-outfit">Add New Experience</h4>
            <div class="text-sm text-slate-400">
                {{ count($character->experiences) }} / 2 experiences
            </div>
        </div>
        
        @if(count($character->experiences) >= 2)
            <!-- Experience Limit Reached -->
            <div class="bg-amber-500/10 border border-amber-500/20 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-amber-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    <p class="text-amber-300 font-medium">
                        Experience Limit Reached
                    </p>
                </div>
                <p class="text-amber-200 text-sm mt-1 ml-7">
                    Your character already has the maximum of 2 experiences for character creation. You can edit or remove existing experiences if needed.
                </p>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div class="lg:col-span-1">
                    <label for="new-experience-name" class="block text-sm font-medium text-slate-300 mb-2">Experience Name</label>
                    <input 
                        dusk="new-experience-name"
                        type="text" 
                        id="new-experience-name"
                        wire:model="newExperienceName"
                        x-model="experienceName"
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
                        wire:model="newExperienceDescription"
                        placeholder="Brief description (optional)"
                        maxlength="100"
                        class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all duration-200"
                    >
                </div>
                
                <div class="lg:col-span-1 flex items-end">
                    <button 
                        dusk="add-experience-button"
                        wire:click="addExperience"
                        :disabled="experienceName.trim() === ''"
                        :class="{
                            'w-full px-4 py-3 rounded-lg font-semibold transition-all duration-200': true,
                            'bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-black': experienceName.trim() !== '',
                            'bg-slate-700 text-slate-400 cursor-not-allowed': experienceName.trim() === ''
                        }"
                    >
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Add Experience
                    </button>
                </div>
            </div>
        @endif
    </div>

    <!-- Experience List -->
    @if(count($character->experiences) > 0)
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h4 class="text-white font-semibold font-outfit">Your Experiences</h4>
                @if(count($character->experiences) > 2)
                    <button 
                        wire:click="clearAllExperiences"
                        onclick="return confirm('Are you sure you want to remove all experiences?')"
                        class="inline-flex items-center px-3 py-2 bg-red-600/20 hover:bg-red-600/30 text-red-300 border border-red-500/30 hover:border-red-500/50 rounded-lg text-sm font-medium transition-all duration-200"
                    >
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1-1H8a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Clear All
                    </button>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($character->experiences as $index => $experience)
                    <div 
                        dusk="experience-card-{{ $index }}"
                        class="relative group bg-gradient-to-br from-slate-800/90 to-slate-900/90 backdrop-blur border border-slate-700/50 rounded-xl p-5 transition-all duration-300 hover:border-slate-600/70"
                    >
                        <!-- Experience Header -->
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <h5 class="text-white font-bold font-outfit text-lg">{{ $experience['name'] }}</h5>
                                @if(!empty($experience['description']))
                                    <p class="text-slate-300 text-sm mt-1">{{ $experience['description'] }}</p>
                                @endif
                            </div>
                            
                            <button 
                                dusk="remove-experience-{{ $index }}"
                                wire:click="removeExperience({{ $index }})"
                                class="opacity-0 group-hover:opacity-100 transition-opacity duration-200 p-2 hover:bg-red-600/20 rounded-lg"
                            >
                                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1-1H8a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>

                        <!-- Experience Modifier -->
                        <div class="bg-gradient-to-r from-amber-500/10 to-orange-500/10 border border-amber-500/20 rounded-lg p-3">
                            <div class="flex items-center justify-between">
                                <span class="text-amber-300 font-medium text-sm">Experience Modifier</span>
                                <span class="text-white font-bold text-lg">+{{ $experience['modifier'] ?? 2 }}</span>
                            </div>
                            <p class="text-slate-300 text-xs mt-1">Added to relevant rolls when this experience applies</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Experience Examples -->
    <div class="bg-blue-500/10 border border-blue-500/20 rounded-xl p-8">
        <h4 class="text-blue-300 font-semibold font-outfit text-xl mb-6">Experience Examples</h4>
        
        <!-- Remember Notice -->
        <div class="bg-gradient-to-r from-blue-500/20 to-purple-500/20 border border-blue-400/30 rounded-lg p-4 mb-8">
            <p class="text-slate-200 text-sm">
                <strong class="text-blue-300">Remember:</strong> All experiences provide a +2 modifier when relevant. 
                Choose experiences that reflect your character's background, personality, and unique skills.
            </p>
        </div>
        
        <!-- Main Categories Grid -->
        <div class="grid grid-cols-1 xl:grid-cols-4 lg:grid-cols-2 gap-8">
            <!-- Backgrounds -->
            <div class="bg-slate-800/30 rounded-lg p-6">
                <h5 class="text-white font-bold font-outfit text-lg mb-4 text-center border-b border-slate-600 pb-2">Backgrounds</h5>
                <ul class="space-y-2 text-sm text-slate-300">
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Assassin</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Blacksmith</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Bodyguard</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Bounty Hunter</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Chef to the Royal Family</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Circus Performer</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Con Artist</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Fallen Monarch</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Field Medic</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> High Priestess</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Merchant</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Noble</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Pirate</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Politician</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Runaway</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Scholar</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Sellsword</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Soldier</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Storyteller</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Thief</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> World Traveler</li>
                </ul>
            </div>

            <!-- Characteristics -->
            <div class="bg-slate-800/30 rounded-lg p-6">
                <h5 class="text-white font-bold font-outfit text-lg mb-4 text-center border-b border-slate-600 pb-2">Characteristics</h5>
                <ul class="space-y-2 text-sm text-slate-300">
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Affable</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Battle-Hardened</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Bookworm</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Charming</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Cowardly</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Friend to All</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Helpful</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Intimidating Presence</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Leader</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Lone Wolf</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Loyal</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Observant</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Prankster</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Silver Tongue</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Sticky Fingers</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Stubborn to a Fault</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Survivor</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Young and Naive</li>
                </ul>
            </div>

            <!-- Specialties & Skills -->
            <div class="bg-slate-800/30 rounded-lg p-6">
                <h5 class="text-white font-bold font-outfit text-lg mb-4 text-center border-b border-slate-600 pb-2">Specialties & Skills</h5>
                <div class="mb-4">
                    <h6 class="text-amber-300 font-semibold text-sm mb-2 uppercase tracking-wide">Specialties</h6>
                    <ul class="space-y-2 text-sm text-slate-300">
                        <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Acrobat</li>
                        <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Gambler</li>
                        <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Healer</li>
                        <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Inventor</li>
                        <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Magical Historian</li>
                        <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Mapmaker</li>
                        <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Master of Disguise</li>
                        <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Navigator</li>
                        <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Sharpshooter</li>
                        <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Survivalist</li>
                        <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Swashbuckler</li>
                        <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Tactician</li>
                    </ul>
                </div>
                <div>
                    <h6 class="text-amber-300 font-semibold text-sm mb-2 uppercase tracking-wide">Skills</h6>
                    <ul class="space-y-2 text-sm text-slate-300">
                        <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Animal Whisperer</li>
                        <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Barter</li>
                        <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Deadly Aim</li>
                        <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Fast Learner</li>
                        <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Incredible Strength</li>
                        <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Liar</li>
                        <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Light Feet</li>
                        <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Negotiator</li>
                        <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Photographic Memory</li>
                        <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Quick Hands</li>
                        <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Repair</li>
                        <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Scavenger</li>
                        <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Tracker</li>
                    </ul>
                </div>
            </div>

            <!-- Phrases -->
            <div class="bg-slate-800/30 rounded-lg p-6">
                <h5 class="text-white font-bold font-outfit text-lg mb-4 text-center border-b border-slate-600 pb-2">Phrases</h5>
                <ul class="space-y-2 text-sm text-slate-300">
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Catch Me If You Can</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Fake It Till You Make It</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> First Time's the Charm</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Hold the Line</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> I Won't Let You Down</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> I'll Catch You</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> I've Got Your Back</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Knowledge Is Power</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Nature's Friend</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Never Again</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> No One Left Behind</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Pick on Someone Your Own Size</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> The Show Must Go On</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> This Is Not a Negotiation</li>
                    <li class="flex items-center"><span class="text-amber-400 mr-2">•</span> Wolf in Sheep's Clothing</li>
                </ul>
            </div>
        </div>
    </div>
</div>
