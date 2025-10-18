<!-- Character Information Step -->
<div class="space-y-6 sm:space-y-8">
    <!-- Step Header -->
    <div class="mb-6 sm:mb-8">
        <h2 class="text-xl sm:text-2xl font-bold text-white mb-2 font-outfit">Character Information</h2>
        <p class="text-slate-300 font-roboto text-sm sm:text-base">Set your character's name and upload a profile image.</p>
    </div>
    <!-- Character Name -->
    <div class="bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-xl p-4 sm:p-6">
        <h3 class="text-lg sm:text-xl font-bold text-white font-outfit mb-4">Character Name</h3>
        <p class="text-slate-300 text-xs sm:text-sm mb-4 sm:mb-6">
            Give your character a memorable name that fits their background and personality.
        </p>
        
        <div class="max-w-md">
            <label for="character-name" class="block text-sm font-medium text-slate-300 mb-2">Name</label>
            <input 
                pest="character-name-input"
                type="text" 
                id="character-name"
                x-model="name"
                @input="markAsUnsaved()"
                placeholder="Enter character name..."
                maxlength="100"
                class="w-full px-3 sm:px-4 py-2.5 sm:py-3 bg-slate-900/50 border border-slate-600 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all duration-200 text-sm sm:text-base"
            >
            
            <template x-if="hasCharacterName">
                <div class="mt-2 flex items-center text-emerald-400 text-sm">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    Character name set!
                </div>
            </template>
        </div>
    </div>

    <!-- Character Portrait -->
    <div class="bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-xl p-4 sm:p-6">
        <h3 class="text-lg sm:text-xl font-bold text-white font-outfit mb-4">Character Portrait</h3>
        <p class="text-slate-300 text-xs sm:text-sm mb-4 sm:mb-6">
            Upload an image to represent your character. This is optional but helps bring your character to life.
        </p>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 sm:gap-8">
            <!-- Upload Area -->
            <div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-300 mb-2">Upload Portrait</label>
                    
                    <!-- File Upload Input -->
                    <div class="relative">
                        <input 
                            pest="profile-image-input"
                            type="file" 
                            wire:model="profileImage"
                            accept="image/*"
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                        >
                        
                        <!-- Upload UI -->
                        <div 
                            :class="{
                                'border-2 border-dashed rounded-xl p-6 sm:p-8 text-center transition-all duration-200': true,
                                'border-slate-600 hover:border-slate-500 bg-slate-900/30': !profile_image,
                                'border-amber-500 bg-amber-500/10': profile_image
                            }"
                        >
                            <template x-if="profile_image">
                                <div>
                                    <svg class="w-10 h-10 sm:w-12 sm:h-12 text-amber-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                    <p class="text-amber-400 font-semibold text-sm sm:text-base">Image selected!</p>
                                    <p class="text-slate-300 text-xs sm:text-sm mt-1" x-text="profile_image?.name || 'Selected image'"></p>
                                    <p class="text-slate-400 text-xs mt-2">Tap here to select a different image</p>
                                </div>
                            </template>
                            <template x-if="!profile_image">
                                <div>
                                    <svg class="w-10 h-10 sm:w-12 sm:h-12 text-slate-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                    <p class="text-white font-semibold text-sm sm:text-base">Upload Character Portrait</p>
                                    <p class="text-slate-300 text-xs sm:text-sm mt-1">Drag and drop or tap to select</p>
                                    <p class="text-slate-400 text-xs mt-2">PNG, JPG up to 2MB</p>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Upload Progress -->
                    <div wire:loading wire:target="profileImage" class="mt-3">
                        <div class="flex items-center text-amber-400 text-sm">
                            <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Uploading image...
                        </div>
                    </div>

                    <!-- Error Display -->
                    @error('profileImage')
                        <div class="mt-3 flex items-center text-red-400 text-sm">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            {{ $message }}
                        </div>
                    @enderror

                    <!-- Clear Image Button -->
                    @if($profile_image || $character->profile_image_path)
                        <button 
                            @click="clearProfileImage()"
                            class="mt-3 inline-flex items-center px-3 py-2 bg-red-600/20 hover:bg-red-600/30 text-red-300 border border-red-500/30 hover:border-red-500/50 rounded-lg text-sm font-medium transition-all duration-200"
                        >
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1-1H8a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Remove Image
                        </button>
                    @endif
                </div>
            </div>

            <!-- Preview Area -->
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">Preview</label>
                
                <div class="bg-slate-900/50 border border-slate-600 rounded-xl p-6">
                    @if($profile_image)
                        <!-- Temporary Preview -->
                        <div class="aspect-square max-w-sm mx-auto">
                            <img pest="profile-image-preview" src="{{ $profile_image->temporaryUrl() }}" alt="Character Preview" class="w-full h-full object-cover rounded-xl">
                        </div>
                        <p class="text-center text-slate-400 text-xs mt-3">Image preview</p>
                    @elseif($character->profile_image_path)
                        <!-- Saved Image -->
                        <div class="aspect-square max-w-sm mx-auto">
                            <img pest="profile-image-saved" src="{{ $character->getProfileImage() }}" alt="Character Portrait" class="w-full h-full object-cover rounded-xl">
                        </div>
                        <p class="text-center text-emerald-400 text-xs mt-3">✓ Saved portrait</p>
                    @else
                        <!-- Placeholder -->
                        <div class="aspect-square max-w-sm mx-auto bg-slate-800 border-2 border-dashed border-slate-600 rounded-xl flex items-center justify-center">
                            <div class="text-center text-slate-500">
                                <svg class="w-16 h-16 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <p class="text-sm">No portrait selected</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Character Overview -->
    <template x-if="hasBasicCharacterInfo">
        <div class="bg-gradient-to-r from-emerald-500/10 to-amber-500/10 border border-emerald-500/20 rounded-xl p-6">
            <h3 class="text-lg font-bold text-white font-outfit mb-4">Character Overview</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Portrait -->
                <div class="text-center">
                    <template x-if="profile_image">
                        <div class="w-24 h-24 mx-auto mb-3">
                            <img :src="profile_image?.temporaryUrl || ''" alt="Character" class="w-full h-full object-cover rounded-full border-2 border-amber-500">
                        </div>
                    </template>
                    <template x-if="!profile_image && profile_image_path">
                        <div class="w-24 h-24 mx-auto mb-3">
                            <img :src="$wire.getProfileImageUrl()" alt="Character" class="w-full h-full object-cover rounded-full border-2 border-emerald-500">
                        </div>
                    </template>
                    <template x-if="!profile_image && !profile_image_path">
                        <div class="w-24 h-24 mx-auto mb-3 bg-slate-700 rounded-full border-2 border-slate-600 flex items-center justify-center">
                            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                    </template>
                    <p class="text-slate-400 text-sm">Portrait</p>
                </div>

                <!-- Basic Info -->
                <div class="col-span-2 space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400">Name:</span>
                        <span class="text-white font-medium">{{ $character->name ?: 'Unnamed Character' }}</span>
                    </div>
                    
                    @if($character->selected_class)
                        <div class="flex justify-between items-center">
                            <span class="text-slate-400">Class:</span>
                            <span class="text-white font-medium">
                                {{ ucfirst($character->selected_class) }}
                                @if($character->selected_subclass)
                                    - {{ ucwords(str_replace('-', ' ', $character->selected_subclass)) }}
                                @endif
                            </span>
                        </div>
                    @endif

                    @if($character->selected_ancestry && $character->selected_community)
                        <div class="flex justify-between items-center">
                            <span class="text-slate-400">Heritage:</span>
                            <span class="text-white font-medium">
                                {{ ucfirst($character->selected_ancestry) }} from {{ ucfirst($character->selected_community) }}
                            </span>
                        </div>
                    @endif

                    @if(count($character->assigned_traits) > 0)
                        <div class="flex justify-between items-center">
                            <span class="text-slate-400">Traits:</span>
                            <span class="text-white font-medium">{{ count($character->assigned_traits) }}/6 assigned</span>
                        </div>
                    @endif
                </div>
            </div>

            @if(!empty($character->name))
                <div class="mt-4 pt-4 border-t border-slate-700/50 flex items-center text-emerald-400">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    <span class="font-semibold">Character information complete!</span>
                </div>
            @endif
        </div>
    @endif

    <!-- Character Stats Summary -->
    @if(!empty($computed_stats))
        <div class="bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-xl p-6">
            <h3 class="text-xl font-bold text-white font-outfit mb-4">Character Statistics</h3>
            <p class="text-slate-300 text-sm mb-6">
                Your current character statistics based on class, traits, ancestry, and equipment.
            </p>

            <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Evasion -->
                @if(isset($computed_stats['evasion']))
                    <div class="bg-slate-900/50 border border-slate-600/50 rounded-lg p-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-amber-400 font-outfit">{{ $computed_stats['evasion']['total'] }}</div>
                            <div class="text-xs text-slate-400 uppercase tracking-wide">Evasion</div>
                            @if($computed_stats['evasion']['ancestry_bonus'] > 0)
                                <div class="text-xs text-emerald-400 mt-1">
                                    +{{ $computed_stats['evasion']['ancestry_bonus'] }} Ancestry
                                </div>
                            @endif
                            @if($computed_stats['evasion']['advancement_bonus'] > 0)
                                <div class="text-xs text-purple-400 mt-1">
                                    +{{ $computed_stats['evasion']['advancement_bonus'] }} Advancements
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Hit Points -->
                @if(isset($computed_stats['hit_points']))
                    <div class="bg-slate-900/50 border border-slate-600/50 rounded-lg p-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-red-400 font-outfit">{{ $computed_stats['hit_points']['total'] }}</div>
                            <div class="text-xs text-slate-400 uppercase tracking-wide">Hit Points</div>
                            @if($computed_stats['hit_points']['ancestry_bonus'] > 0)
                                <div class="text-xs text-emerald-400 mt-1">
                                    +{{ $computed_stats['hit_points']['ancestry_bonus'] }} Ancestry
                                </div>
                            @endif
                            @if($computed_stats['hit_points']['advancement_bonus'] > 0)
                                <div class="text-xs text-purple-400 mt-1">
                                    +{{ $computed_stats['hit_points']['advancement_bonus'] }} Advancements
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Stress -->
                @if(isset($computed_stats['stress']))
                    <div class="bg-slate-900/50 border border-slate-600/50 rounded-lg p-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-400 font-outfit">{{ $computed_stats['stress']['total'] }}</div>
                            <div class="text-xs text-slate-400 uppercase tracking-wide">Stress Slots</div>
                            @if($computed_stats['stress']['ancestry_bonus'] > 0)
                                <div class="text-xs text-emerald-400 mt-1">
                                    +{{ $computed_stats['stress']['ancestry_bonus'] }} Ancestry
                                </div>
                            @endif
                            @if($computed_stats['stress']['advancement_bonus'] > 0)
                                <div class="text-xs text-purple-400 mt-1">
                                    +{{ $computed_stats['stress']['advancement_bonus'] }} Advancements
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Hope -->
                @if(isset($computed_stats['hope']))
                    <div class="bg-slate-900/50 border border-slate-600/50 rounded-lg p-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-400 font-outfit">{{ $computed_stats['hope'] }}</div>
                            <div class="text-xs text-slate-400 uppercase tracking-wide">Hope</div>
                        </div>
                    </div>
                @endif

                <!-- Proficiency -->
                @if(isset($computed_stats['proficiency']))
                    <div class="bg-slate-900/50 border border-slate-600/50 rounded-lg p-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-cyan-400 font-outfit">+{{ $computed_stats['proficiency']['total'] }}</div>
                            <div class="text-xs text-slate-400 uppercase tracking-wide">Proficiency</div>
                            @if($computed_stats['proficiency']['level_proficiency'] > 0)
                                <div class="text-xs text-slate-300 mt-1">
                                    +{{ $computed_stats['proficiency']['level_proficiency'] }} Level
                                </div>
                            @endif
                            @if($computed_stats['proficiency']['advancement_bonus'] > 0)
                                <div class="text-xs text-purple-400 mt-1">
                                    +{{ $computed_stats['proficiency']['advancement_bonus'] }} Advancements
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Damage Thresholds -->
            @if(isset($computed_stats['damage_thresholds']))
                <div class="mt-6 bg-slate-900/50 border border-slate-600/50 rounded-lg p-4">
                    <h4 class="text-lg font-bold text-white font-outfit mb-3">Damage Thresholds</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center">
                            <div class="text-xl font-bold text-yellow-400">{{ $computed_stats['damage_thresholds']['major'] }}</div>
                            <div class="text-xs text-slate-400 uppercase tracking-wide">Major Damage</div>
                        </div>
                        <div class="text-center">
                            <div class="text-xl font-bold text-orange-400">{{ $computed_stats['damage_thresholds']['severe'] }}</div>
                            <div class="text-xs text-slate-400 uppercase tracking-wide">Severe Damage</div>
                        </div>
                    </div>
                    @if($computed_stats['damage_thresholds']['ancestry_bonus'] > 0)
                        <div class="text-center mt-2">
                            <div class="text-xs text-emerald-400">
                                +{{ $computed_stats['damage_thresholds']['ancestry_bonus'] }} from Ancestry
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    @endif
</div>
