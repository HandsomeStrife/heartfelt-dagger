<div class="min-h-screen" x-data="{
    selected_class: $wire.entangle('character.selected_class'),
    selected_subclass: $wire.entangle('character.selected_subclass'),
    selected_ancestry: $wire.entangle('character.selected_ancestry'),
    selected_community: $wire.entangle('character.selected_community'),
    currentStep: 1,
    
    // Trait Assignment Data
    draggedValue: null,
    availableValues: [-1, 0, 0, 1, 1, 2],
    assigned_traits: $wire.entangle('character.assigned_traits'),
    
    // Heritage Selection Data
    hasSelectedAncestry: false,
    hasSelectedCommunity: false,
    
    get hasSelectedClass() {
        return !!this.selected_class;
    },
    
    init() {
        this.hasSelectedAncestry = !!this.selected_ancestry;
        this.hasSelectedCommunity = !!this.selected_community;
        
        this.$watch('selected_ancestry', (value) => {
            this.hasSelectedAncestry = !!value;
        });
        
        this.$watch('selected_community', (value) => {
            this.hasSelectedCommunity = !!value;
        });
    },
    
    get remainingValues() {
        let remaining = [...this.availableValues];
        Object.values(this.assigned_traits).forEach(value => {
            const index = remaining.indexOf(value);
            if (index > -1) remaining.splice(index, 1);
        });
        return remaining;
    },
    
    selectClass(classKey) {
        this.selected_class = classKey;
        this.selected_subclass = null;
        $wire.selectClass(classKey);
        
        // Scroll to top of content when selecting a class
        if (classKey) {
            document.getElementById('character-builder-content')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    },
    
    selectSubclass(subclassKey) {
        this.selected_subclass = subclassKey;
        $wire.selectSubclass(subclassKey);
    },
    
    selectAncestry(ancestryKey) {
        this.selected_ancestry = ancestryKey;
        $wire.selectAncestry(ancestryKey);
        
        if (ancestryKey) {
            document.getElementById('character-builder-content')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    },
    
    selectCommunity(communityKey) {
        this.selected_community = communityKey;
        $wire.selectCommunity(communityKey);
        
        if (communityKey) {
            document.getElementById('character-builder-content')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    },
    
    goToStep(step) {
        this.currentStep = step;
    },
    
    // Trait Assignment Methods
    canDropValue(traitKey, value) {
        return this.remainingValues.includes(value) || this.assigned_traits[traitKey] === value;
    },
    
    dropValue(traitKey, value) {
        if (this.canDropValue(traitKey, value)) {
            $wire.assignTrait(traitKey, value);
        }
    },
    
    removeValue(traitKey) {
        $wire.assignTrait(traitKey, null);
    }
}">
    <!-- Minimal Full-Width Sub-Header -->
    <x-sub-navigation>
        <div class="flex items-center justify-between">
            <!-- Left: Title and Last Saved -->
            <div class="flex items-center gap-4">
                <h1 class="font-outfit text-lg font-semibold text-white">
                    Character Builder
                </h1>
                
                @if($last_saved_timestamp)
                    <div 
                        class="flex items-center gap-1.5 text-xs text-slate-500"
                        x-data="{ 
                            lastSavedTimestamp: {{ $last_saved_timestamp }},
                            timeAgoText: '',
                            updateTimer: null,
                            
                            init() {
                                this.updateTimeAgo();
                                this.startTimer();
                                
                                // Listen for save events from Livewire
                                this.$wire.$on('character-saved-timestamp', (data) => {
                                    this.lastSavedTimestamp = data.timestamp;
                                    this.updateTimeAgo();
                                    this.restartTimer();
                                });
                            },
                            
                            updateTimeAgo() {
                                // Validate that we have a valid timestamp
                                if (!this.lastSavedTimestamp || isNaN(this.lastSavedTimestamp)) {
                                    this.timeAgoText = '';
                                    return;
                                }
                                
                                const now = Math.floor(Date.now() / 1000);
                                const diffInSeconds = now - this.lastSavedTimestamp;
                                
                                // Handle negative values (future dates)
                                if (diffInSeconds < 0) {
                                    this.timeAgoText = 'just now';
                                } else if (diffInSeconds < 60) {
                                    this.timeAgoText = 'just now';
                                } else if (diffInSeconds < 3600) {
                                    const minutes = Math.floor(diffInSeconds / 60);
                                    this.timeAgoText = minutes + (minutes === 1 ? ' minute ago' : ' minutes ago');
                                } else if (diffInSeconds < 86400) {
                                    const hours = Math.floor(diffInSeconds / 3600);
                                    this.timeAgoText = hours + (hours === 1 ? ' hour ago' : ' hours ago');
                                } else {
                                    const days = Math.floor(diffInSeconds / 86400);
                                    this.timeAgoText = days + (days === 1 ? ' day ago' : ' days ago');
                                }
                            },
                            
                            startTimer() {
                                this.updateTimer = setInterval(() => {
                                    this.updateTimeAgo();
                                }, 30000); // Update every 30 seconds
                            },
                            
                            restartTimer() {
                                if (this.updateTimer) {
                                    clearInterval(this.updateTimer);
                                }
                                this.startTimer();
                            }
                        }"
                        x-init="init()"
                    >
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span x-text="'Saved ' + timeAgoText"></span>
                    </div>
                @endif
            </div>
            
            <!-- Right: Actions -->
            <div class="flex items-center gap-2">
                <!-- Save Button -->
                <button 
                    dusk="save-character-button"
                    wire:click="saveToDatabase"
                    class="inline-flex items-center justify-center px-3 py-1.5 bg-gradient-to-r from-emerald-500 to-green-500 hover:from-emerald-400 hover:to-green-400 text-white text-sm font-medium rounded-lg transition-all duration-300 shadow-lg hover:shadow-emerald-500/25"
                >
                    <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    Save
                </button>

                <!-- Preview Button -->
                @if($character->selected_class)
                    <button 
                        dusk="preview-character-button"
                        onclick="viewCharacterInNewWindow()"
                        class="inline-flex items-center justify-center px-3 py-1.5 bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-400 hover:to-indigo-400 text-white text-sm font-medium rounded-lg transition-all duration-300 shadow-lg hover:shadow-blue-500/25"
                    >
                        <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Preview
                    </button>
                @endif
            </div>
        </div>
    </x-sub-navigation>

    <div class="container mx-auto px-4 sm:px-6 pb-4 sm:pb-8">

        <!-- Character Information Section -->
        <div class="p-4 sm:p-8 mb-6 sm:mb-8">
            <div class="flex flex-col sm:flex-row gap-4 sm:gap-8">
                <!-- Profile Image Upload -->
                <div class="flex flex-col items-center justify-center sm:flex-shrink-0">
                    <div class="relative">
                        @if($profile_image || $character->profile_image_path)
                            <!-- Image Preview -->
                            <div class="relative w-24 h-24 sm:w-32 sm:h-32 rounded-full overflow-hidden border-4 border-slate-600 shadow-lg">
                                <img 
                                    src="{{ $this->getImageUrl() }}" 
                                    alt="Profile preview" 
                                    class="w-full h-full object-cover"
                                >
                                <button 
                                    wire:click="clearProfileImage"
                                    class="absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full p-1 shadow-lg transition-colors duration-200 z-50"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        @else
                            <!-- Upload Area -->
                            <div 
                                x-data="{ dragOver: false }"
                                @dragover.prevent="dragOver = true"
                                @dragleave.prevent="dragOver = false"
                                @drop.prevent="dragOver = false; $refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change'))"
                                :class="{ 'border-amber-500 bg-amber-500/10': dragOver }"
                                class="relative w-24 h-24 sm:w-32 sm:h-32 rounded-full border-2 border-dashed border-slate-600 hover:border-slate-500 bg-slate-800/50 hover:bg-slate-800 flex flex-col items-center justify-center cursor-pointer transition-all duration-200 group"
                                @click="$refs.fileInput.click()"
                            >
                                <svg class="w-8 h-8 text-slate-400 group-hover:text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                <span class="text-xs text-slate-400 group-hover:text-slate-300 text-center">Upload Image</span>
                                
                                <input 
                                    x-ref="fileInput"
                                    dusk="profile-image-upload"
                                    type="file" 
                                    wire:model="profile_image"
                                    accept="image/*"
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                    style="pointer-events: none;"
                                >
                            </div>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 mt-2 text-center">Click or drag to upload<br>character portrait</p>
                </div>

                <!-- Character Name & Pronouns -->
                <div class="space-y-6 w-full">
                    <div>
                        <label for="character-name" class="block text-sm font-medium text-slate-300 mb-2">Character Name</label>
                        <input 
                            dusk="character-name-input"
                            type="text" 
                            id="character-name"
                            wire:model.live.debounce.500ms="character.name"
                            placeholder="Enter your character's name..."
                            class="w-full px-4 py-3 bg-slate-800/50 border border-slate-600 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all duration-200"
                        >
                    </div>
                    
                    <div>
                        <label for="character-pronouns" class="block text-sm font-medium text-slate-300 mb-2">Pronouns</label>
                        <input 
                            type="text" 
                            id="character-pronouns"
                            wire:model.live.debounce.500ms="pronouns"
                            placeholder="e.g., they/them, she/her, he/him..."
                            class="w-full px-4 py-3 bg-slate-800/50 border border-slate-600 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all duration-200"
                        >
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Layout with Sidebar -->
        <div class="flex flex-col lg:flex-row gap-6 lg:gap-8">
            <!-- Left Sidebar Navigation -->
            <div class="lg:w-80 flex-shrink-0">
                <!-- Mobile Dropdown -->
                <div class="lg:hidden mb-6" x-data="{ dropdownOpen: false }">
                    <div class="bg-slate-900/50 backdrop-blur-xl border border-slate-700/50 rounded-2xl">
                        <button 
                            @click="dropdownOpen = !dropdownOpen"
                            class="w-full flex items-center justify-between p-4 text-white hover:bg-slate-800/50 rounded-2xl transition-colors"
                        >
                            <div class="flex items-center gap-3">
                                @foreach($tabs as $step => $title)
                                    <div x-show="currentStep === {{ $step }}" class="flex items-center gap-2">
                                        <span class="text-sm font-medium">
                                            Step {{ $step }}: {{ $title }}
                                        </span>
                                        @if(in_array($step, $completed_steps))
                                            <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            <svg class="w-5 h-5 transition-transform" :class="{ 'rotate-180': dropdownOpen }" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        
                        <div x-show="dropdownOpen" 
                             x-cloak
                             @click.away="dropdownOpen = false"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="mt-2 mx-4 mb-4 bg-slate-800 border border-slate-600 rounded-lg shadow-xl">
                            @foreach($tabs as $step => $title)
                                <button 
                                    dusk="mobile-tab-{{ $step }}"
                                    @click="goToStep({{ $step }}); dropdownOpen = false"
                                    :class="{
                                        'w-full flex items-center gap-3 px-4 py-3 text-left border-b border-slate-600 last:border-b-0': true,
                                        'bg-slate-700/50 text-white border-l-2 border-l-amber-500': currentStep === {{ $step }},
                                        'bg-emerald-500/10 text-white border-emerald-500/20': {{ in_array($step, $completed_steps) ? 'true' : 'false' }} && currentStep !== {{ $step }},
                                        'text-white hover:bg-slate-700': currentStep !== {{ $step }} && !{{ in_array($step, $completed_steps) ? 'true' : 'false' }}
                                    }"
                                >
                                    <!-- Step Icon -->
                                    <div class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold"
                                         :class="{
                                             'bg-amber-500/20 text-amber-400': currentStep === {{ $step }},
                                             'bg-emerald-500 text-white': {{ in_array($step, $completed_steps) ? 'true' : 'false' }} && currentStep !== {{ $step }},
                                             'bg-slate-600 text-slate-400': currentStep !== {{ $step }} && !{{ in_array($step, $completed_steps) ? 'true' : 'false' }}
                                         }">
                                        @if(in_array($step, $completed_steps))
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        @else
                                            {{ $step }}
                                        @endif
                                    </div>
                                    
                                    <!-- Step Content -->
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-medium">{{ $title }}</div>
                                        
                                        <!-- Selected Option Display -->
                                        @if($step === 1 && !empty($character->selected_class))
                                            <div class="text-xs mt-0.5 opacity-75">
                                                {{ $game_data['classes'][$character->selected_class]['name'] ?? 'Selected' }}
                                            </div>
                                        @elseif($step === 2 && !empty($character->selected_subclass))
                                            <div class="text-xs mt-0.5 opacity-75">
                                                {{ $game_data['subclasses'][$character->selected_subclass]['name'] ?? 'Selected' }}
                                            </div>
                                        @elseif($step === 3 && !empty($character->selected_ancestry))
                                            <div class="text-xs mt-0.5 opacity-75">
                                                {{ $game_data['ancestries'][$character->selected_ancestry]['name'] ?? 'Selected' }}
                                            </div>
                                        @elseif($step === 4 && !empty($character->selected_community))
                                            <div class="text-xs mt-0.5 opacity-75">
                                                {{ $game_data['communities'][$character->selected_community]['name'] ?? 'Selected' }}
                                            </div>
                                        @elseif($step === 5 && !empty($character->assigned_traits))
                                            <div class="text-xs mt-0.5 opacity-75">
                                                {{ count(array_filter($character->assigned_traits)) }}/6 assigned
                                            </div>
                                        @elseif($step === 6 && !empty($character->selected_equipment))
                                            <div class="text-xs mt-0.5 opacity-75">
                                                {{ count($character->selected_equipment) }} items
                                            </div>
                                        @elseif($step === 7 && !empty($character->background_answers))
                                            <div class="text-xs mt-0.5 opacity-75">
                                                {{ count(array_filter($character->background_answers)) }} answered
                                            </div>
                                        @elseif($step === 8 && !empty($character->experiences))
                                            <div class="text-xs mt-0.5 opacity-75">
                                                {{ count($character->experiences) }} experiences
                                            </div>
                                        @elseif($step === 9 && !empty($character->selected_domain_cards))
                                            <div class="text-xs mt-0.5 opacity-75">
                                                {{ count($character->selected_domain_cards) }} cards
                                            </div>
                                        @elseif($step === 10 && !empty($character->connection_answers))
                                            <div class="text-xs mt-0.5 opacity-75">
                                                {{ count(array_filter($character->connection_answers)) }} answered
                                            </div>
                                        @elseif(in_array($step, $completed_steps))
                                            <div class="text-xs mt-0.5 opacity-75">Completed</div>
                                        @else
                                            <div class="text-xs mt-0.5 opacity-60">Not started</div>
                                        @endif
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Desktop Sidebar -->
                <nav id="character-builder-sidebar" class="hidden lg:block bg-slate-900/50 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                    <h3 class="font-outfit text-xl font-bold text-white mb-6">Character Steps</h3>
                    <div class="space-y-3">
                        @foreach($tabs as $step => $title)
                            <button 
                                dusk="sidebar-tab-{{ $step }}"
                                @click="goToStep({{ $step }})"
                                :class="{
                                    'w-full flex items-center gap-4 px-4 py-4 rounded-xl text-left group': true,
                                    'bg-slate-700/50 text-white border border-amber-500/50 shadow-md': currentStep === {{ $step }},
                                    'bg-emerald-500/10 text-white border border-emerald-500/30 hover:bg-emerald-500/20': {{ in_array($step, $completed_steps) ? 'true' : 'false' }} && currentStep !== {{ $step }},
                                    'text-slate-400 hover:text-white hover:bg-slate-800/50 border border-transparent hover:border-slate-600/50': currentStep !== {{ $step }} && !{{ in_array($step, $completed_steps) ? 'true' : 'false' }}
                                }"
                            >
                                <!-- Step Icon/Number -->
                                <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold"
                                     :class="{
                                         'bg-amber-500/20 text-amber-400': currentStep === {{ $step }},
                                         'bg-emerald-500 text-white': {{ in_array($step, $completed_steps) ? 'true' : 'false' }} && currentStep !== {{ $step }},
                                         'bg-slate-700 text-slate-400 group-hover:bg-slate-600 group-hover:text-slate-300': currentStep !== {{ $step }} && !{{ in_array($step, $completed_steps) ? 'true' : 'false' }}
                                     }">
                                    @if(in_array($step, $completed_steps))
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    @else
                                        {{ $step }}
                                    @endif
                                </div>

                                <!-- Step Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="font-semibold text-sm font-outfit">{{ $title }}</div>
                                    
                                    <!-- Selected Option Display -->
                                    @if($step === 1 && !empty($character->selected_class))
                                        <div class="text-xs mt-1 opacity-80">
                                            {{ $game_data['classes'][$character->selected_class]['name'] ?? 'Selected' }}
                                        </div>
                                    @elseif($step === 2 && !empty($character->selected_subclass))
                                        <div class="text-xs mt-1 opacity-80">
                                            {{ $game_data['subclasses'][$character->selected_subclass]['name'] ?? 'Selected' }}
                                        </div>
                                    @elseif($step === 3 && !empty($character->selected_ancestry))
                                        <div class="text-xs mt-1 opacity-80">
                                            {{ $game_data['ancestries'][$character->selected_ancestry]['name'] ?? 'Selected' }}
                                        </div>
                                    @elseif($step === 4 && !empty($character->selected_community))
                                        <div class="text-xs mt-1 opacity-80">
                                            {{ $game_data['communities'][$character->selected_community]['name'] ?? 'Selected' }}
                                        </div>
                                    @elseif($step === 5 && !empty($character->assigned_traits))
                                        <div class="text-xs mt-1 opacity-80">
                                            {{ count(array_filter($character->assigned_traits)) }}/6 assigned
                                        </div>
                                    @elseif($step === 6 && !empty($character->selected_equipment))
                                        <div class="text-xs mt-1 opacity-80">
                                            {{ count($character->selected_equipment) }} items
                                        </div>
                                    @elseif($step === 7 && !empty($character->background_answers))
                                        <div class="text-xs mt-1 opacity-80">
                                            {{ count(array_filter($character->background_answers)) }} answered
                                        </div>
                                    @elseif($step === 8 && !empty($character->experiences))
                                        <div class="text-xs mt-1 opacity-80">
                                            {{ count($character->experiences) }} experiences
                                        </div>
                                    @elseif($step === 9 && !empty($character->selected_domain_cards))
                                        <div class="text-xs mt-1 opacity-80">
                                            {{ count($character->selected_domain_cards) }} cards
                                        </div>
                                    @elseif($step === 10 && !empty($character->connection_answers))
                                        <div class="text-xs mt-1 opacity-80">
                                            {{ count(array_filter($character->connection_answers)) }} answered
                                        </div>
                                    @elseif(in_array($step, $completed_steps))
                                        <div class="text-xs mt-1 opacity-80">Completed</div>
                                    @else
                                        <div class="text-xs mt-1 opacity-60">Not started</div>
                                    @endif
                                </div>

                                <!-- Current Step Indicator -->
                                <div class="flex-shrink-0"
                                     :class="{
                                         'block': currentStep === {{ $step }},
                                         'hidden': currentStep !== {{ $step }}
                                     }">
                                    <div class="w-2 h-2 bg-amber-400 rounded-full animate-pulse"></div>
                                </div>
                            </button>
                        @endforeach
                    </div>
                </nav>
            </div>

            <!-- Main Content -->
            <div id="character-builder-content" class="flex-1 min-w-0">
            <!-- Step Content Area -->
            <div class="w-full">
                <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-4 sm:p-8">

                    <!-- Step Content -->
                    <div class="step-content">
                        <!-- Step 1: Class Selection -->
                        <div x-show="currentStep === 1" x-cloak>
                            @include('livewire.character-builder.class-selection')
                        </div>
                        
                        <!-- Step 2: Subclass Selection -->
                        <div x-show="currentStep === 2" x-cloak>
                            @include('livewire.character-builder.subclass-selection')
                        </div>
                        
                        <!-- Step 3: Ancestry Selection -->
                        <div x-show="currentStep === 3" x-cloak>
                            @include('livewire.character-builder.ancestry-selection')
                        </div>
                        
                        <!-- Step 4: Community Selection -->
                        <div x-show="currentStep === 4" x-cloak>
                            @include('livewire.character-builder.community-selection')
                        </div>
                        
                        <!-- Step 5: Trait Assignment -->
                        <div x-show="currentStep === 5" x-cloak>
                            @include('livewire.character-builder.trait-assignment')
                        </div>
                        
                        <!-- Step 6: Equipment Selection -->
                        <div x-show="currentStep === 6" x-cloak>
                            @include('livewire.character-builder.equipment-selection')
                        </div>
                        
                        <!-- Step 7: Background Creation -->
                        <div x-show="currentStep === 7" x-cloak>
                            @include('livewire.character-builder.background-creation')
                        </div>
                        
                        <!-- Step 8: Experience Creation -->
                        <div x-show="currentStep === 8" x-cloak>
                            @include('livewire.character-builder.experience-creation')
                        </div>
                        
                        <!-- Step 9: Domain Card Selection -->
                        <div x-show="currentStep === 9" x-cloak>
                            @include('livewire.character-builder.domain-card-selection')
                        </div>
                        
                        <!-- Step 10: Connection Creation -->
                        <div x-show="currentStep === 10" x-cloak>
                            @include('livewire.character-builder.connection-creation')
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="flex flex-col sm:flex-row justify-between gap-3 sm:gap-0 mt-6 sm:mt-8 pt-4 sm:pt-6 border-t border-slate-700/50">
                        <button 
                            dusk="previous-step-button"
                            @click="currentStep > 1 && goToStep(currentStep - 1)"
                            :class="{
                                'inline-flex items-center justify-center px-4 sm:px-6 py-3 rounded-xl transition-all duration-300 font-semibold': true,
                                'bg-slate-700 hover:bg-slate-600 text-white border border-slate-600 hover:border-slate-500': currentStep > 1,
                                'bg-slate-800 text-slate-500 cursor-not-allowed': currentStep <= 1
                            }"
                            :disabled="currentStep <= 1"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Previous
                        </button>

                        <button 
                            dusk="next-step-button"
                            @click="currentStep < {{ count($tabs) }} && goToStep(currentStep + 1)"
                            :class="{
                                'inline-flex items-center justify-center px-4 sm:px-6 py-3 rounded-xl transition-all duration-300 font-semibold': true,
                                'bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-black shadow-lg hover:shadow-amber-500/25': currentStep < {{ count($tabs) }},
                                'bg-slate-800 text-slate-500 cursor-not-allowed': currentStep >= {{ count($tabs) }}
                            }"
                            :disabled="currentStep >= {{ count($tabs) }}"
                        >
                            Continue
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </div>

                    <!-- Character Complete Actions -->
                    @if($is_complete)
                        <div class="mt-6 sm:mt-8 pt-4 sm:pt-6 border-t border-slate-700/50">
                            <div class="text-center">
                                <div class="mb-4">
                                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-500/20 border border-emerald-500/30 rounded-lg text-emerald-300">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        <span class="font-semibold">Character Complete!</span>
                                    </div>
                                </div>
                                
                                <div class="flex flex-col sm:flex-row justify-center gap-3">
                                    <!-- View Character Button -->
                                    <button 
                                        dusk="view-character-button"
                                        onclick="viewCharacter()"
                                        class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-400 hover:to-indigo-400 text-white font-semibold rounded-xl transition-all duration-300 shadow-lg hover:shadow-blue-500/25"
                                    >
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        View Character Sheet
                                    </button>
                                    
                                    <!-- Return to Characters List -->
                                    <a href="{{ route('characters') }}" 
                                       class="inline-flex items-center justify-center px-6 py-3 bg-slate-700 hover:bg-slate-600 text-white font-semibold rounded-xl transition-all duration-300 border border-slate-600 hover:border-slate-500">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                        </svg>
                                        My Characters
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        </div> <!-- Close main layout flex container -->
    </div>


</div>