<div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950" x-data="{
    selected_class: $wire.entangle('character.selected_class'),
    selected_subclass: $wire.entangle('character.selected_subclass'),
    selected_ancestry: $wire.entangle('character.selected_ancestry'),
    selected_community: $wire.entangle('character.selected_community'),
    currentStep: parseInt(sessionStorage.getItem('characterBuilderCurrentStep') || '1'),
    
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
        sessionStorage.setItem('characterBuilderCurrentStep', step.toString());
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
    <div class="w-full px-4 sm:px-6 py-4 sm:py-8">
        <!-- Header -->
        <div class="text-center mb-6 sm:mb-8">
            <h1 class="font-outfit text-3xl sm:text-4xl text-white tracking-wide mb-2">
                Character Builder
            </h1>
            <p class="font-roboto text-slate-300 text-base sm:text-lg">
                Create your Daggerheart character
            </p>
        </div>

        <!-- Character Information Section -->
        <div class="p-4 sm:p-8 mb-6 sm:mb-8">
            <div class="flex flex-col sm:flex-row gap-4 sm:gap-8">
                <!-- Profile Image Upload -->
                <div class="flex flex-col items-center justify-center sm:flex-shrink-0">
                    <div class="relative">
                        @if($profile_image)
                            <!-- Image Preview -->
                            <div class="relative w-24 h-24 sm:w-32 sm:h-32 rounded-full overflow-hidden border-4 border-slate-600 shadow-lg">
                                <img 
                                    src="{{ $profile_image->temporaryUrl() }}" 
                                    alt="Profile preview" 
                                    class="w-full h-full object-cover"
                                >
                                <button 
                                    wire:click="clearProfileImage"
                                    class="absolute top-1 right-1 bg-red-500 hover:bg-red-600 text-white rounded-full p-1 shadow-lg transition-colors duration-200"
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
                                onclick="this.querySelector('input[type=file]').click()"
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
                            wire:change="updateCharacterName($event.target.value)"
                            placeholder="Enter your character's name..."
                            class="w-full px-4 py-3 bg-slate-800/50 border border-slate-600 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all duration-200"
                        >
                    </div>
                    
                    <div>
                        <label for="character-pronouns" class="block text-sm font-medium text-slate-300 mb-2">Pronouns</label>
                        <input 
                            type="text" 
                            id="character-pronouns"
                            wire:model.live.debounce.500ms="character.pronouns"
                            wire:change="updatePronouns($event.target.value)"
                            placeholder="e.g., they/them, she/her, he/him..."
                            class="w-full px-4 py-3 bg-slate-800/50 border border-slate-600 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all duration-200"
                        >
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div id="character-builder-tabs" class="bg-slate-900/50 backdrop-blur-xl border border-slate-700/50 rounded-2xl mb-6 sm:mb-8">
            <!-- Mobile Dropdown -->
            <div class="md:hidden" x-data="{ dropdownOpen: false }">
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
                                'w-full flex items-center justify-between px-4 py-3 text-left transition-colors border-b border-slate-600 last:border-b-0': true,
                                'bg-gradient-to-r from-amber-500/20 to-orange-500/20 text-amber-300': currentStep === {{ $step }},
                                'text-white hover:bg-slate-700': currentStep !== {{ $step }}
                            }"
                        >
                            <span class="text-sm font-medium">Step {{ $step }}: {{ $title }}</span>
                            @if(in_array($step, $completed_steps))
                                <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Desktop Tabs -->
            <nav class="hidden md:flex p-2 space-x-1">
                @foreach($tabs as $step => $title)
                    <button 
                        dusk="tab-{{ $step }}"
                        @click="goToStep({{ $step }})"
                        :class="{
                            'flex-1 flex gap-2 items-center justify-center py-4 px-3 rounded-xl transition-all duration-300 text-center': true,
                            'bg-gradient-to-r from-amber-500 to-orange-500 text-black font-semibold': currentStep === {{ $step }},
                            'bg-slate-800/50 text-white border border-emerald-500/30': {{ in_array($step, $completed_steps) ? 'true' : 'false' }},
                            'text-slate-400 hover:text-white hover:bg-slate-800/50': currentStep !== {{ $step }} && !{{ in_array($step, $completed_steps) ? 'true' : 'false' }}
                        }"
                    >
                        <span class="text-sm font-medium">{{ $title }}</span>
                        @if(in_array($step, $completed_steps))
                            <svg dusk="completion-checkmark" class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        @endif
                    </button>
                @endforeach
            </nav>
        </div>

        <!-- Main Content -->
        <div id="character-builder-content" class="w-full">
            <!-- Step Content Area -->
            <div class="w-full">
                <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-4 sm:p-8">

                    <!-- Step Content -->
                    <div class="step-content">
                        <!-- Step 1: Class Selection -->
                        <div x-show="currentStep === 1" x-cloak>
                            @include('livewire.character-builder.class-selection')
                        </div>
                        
                        <!-- Step 2: Heritage Selection -->
                        <div x-show="currentStep === 2" x-cloak>
                            @include('livewire.character-builder.heritage-selection')
                        </div>
                        
                        <!-- Step 3: Trait Assignment -->
                        <div x-show="currentStep === 3" x-cloak>
                            @include('livewire.character-builder.trait-assignment')
                        </div>
                        
                        <!-- Step 4: Equipment Selection -->
                        <div x-show="currentStep === 4" x-cloak>
                            @include('livewire.character-builder.equipment-selection')
                        </div>
                        
                        <!-- Step 5: Background Creation -->
                        <div x-show="currentStep === 5" x-cloak>
                            @include('livewire.character-builder.background-creation')
                        </div>
                        
                        <!-- Step 6: Experience Creation -->
                        <div x-show="currentStep === 6" x-cloak>
                            @include('livewire.character-builder.experience-creation')
                        </div>
                        
                        <!-- Step 7: Domain Card Selection -->
                        <div x-show="currentStep === 7" x-cloak>
                            @include('livewire.character-builder.domain-card-selection')
                        </div>
                        
                        <!-- Step 8: Connection Creation -->
                        <div x-show="currentStep === 8" x-cloak>
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
                </div>
            </div>
        </div>
    </div>


</div>