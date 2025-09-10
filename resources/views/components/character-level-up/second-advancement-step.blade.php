@props(['tierOptions', 'character'])

<!-- Second Advancement Selection Step -->
<div x-show="currentStep === 'second_advancement'" x-cloak>
    <div class="space-y-6">
        <div>
            <h3 class="font-outfit font-bold text-slate-100 text-xl mb-3">Choose Your Second Advancement</h3>
            <p class="text-slate-400">Select your second advancement from the available options below.</p>
        </div>
        
        @if(count($this->getAvailableAdvancementsForStep('second')) > 0)
        <div class="space-y-4">
            @foreach($this->getAvailableAdvancementsForStep('second') as $index => $option)
            <div x-data="{ 
                    isSelected: false,
                    optionIndex: {{ $index }}
                 }"
                 x-init="
                    $watch('secondAdvancement', () => {
                        isSelected = secondAdvancement === optionIndex;
                    });
                    isSelected = secondAdvancement === optionIndex;
                 "
                 class="border rounded-lg transition-all"
                 :class="{
                    'border-amber-500 bg-amber-500/5': isSelected,
                    'border-slate-600 bg-slate-800/50 hover:border-slate-500': !isSelected
                 }">
                
                <div class="p-4 cursor-pointer"
                     @click="selectAdvancement({{ $index }}, 'second')">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="font-medium text-slate-100">{{ $option['description'] }}</p>
                            <p class="text-slate-400 text-sm mt-1">
                                Max selections: {{ $option['maxSelections'] ?? 1 }}
                            </p>
                        </div>
                        <div class="flex-shrink-0 ml-4">
                            <div x-show="isSelected" class="w-6 h-6 bg-amber-500 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div x-show="!isSelected" class="w-6 h-6 border-2 border-slate-500 rounded-full"></div>
                        </div>
                    </div>
                </div>
                
                {{-- Show selection UI for advancements that require choices --}}
                <x-character-level-up.trait-selection 
                    :index="$index" 
                    :option="$option" 
                    selected="secondAdvancement" />
            </div>
            @endforeach
        </div>
        
        {{-- Domain Card Selection for Second Advancement --}}
        @foreach($this->getAvailableAdvancementsForStep('second') as $index => $option)
        <x-character-level-up.advancement-domain-card-selection 
            :index="$index" 
            :option="$option" 
            selected="secondAdvancement"
            :character="$character" />
        @endforeach
        
        {{-- Experience Bonus Selection for Second Advancement --}}
        @foreach($this->getAvailableAdvancementsForStep('second') as $index => $option)
        <x-character-level-up.experience-bonus-selection 
            :index="$index" 
            :option="$option" 
            selected="secondAdvancement" />
        @endforeach
        @else
        <div class="bg-slate-800 border border-slate-600 rounded-lg p-6 text-center">
            <p class="text-slate-400">No additional advancement options available.</p>
            <p class="text-slate-500 text-sm mt-1">All available advancements have reached their selection limits.</p>
        </div>
        @endif
    </div>
</div>
