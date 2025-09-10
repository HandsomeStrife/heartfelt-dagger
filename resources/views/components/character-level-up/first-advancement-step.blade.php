@props(['tierOptions', 'character'])

<!-- First Advancement Selection Step -->
<div x-show="currentStep === 'first_advancement'" x-cloak>
    <div class="space-y-6">
        <div>
            <h3 class="font-outfit font-bold text-slate-100 text-xl mb-3">Choose Your First Advancement</h3>
            <p class="text-slate-400">Select your first advancement from the options below.</p>
        </div>

        @if(isset($tierOptions['options']))
        <div class="space-y-4">
            @foreach($tierOptions['options'] as $index => $option)
            <div x-data="{
                    isSelected: false,
                    optionIndex: {{ $index }}
                 }"
                 x-init="
                    $watch('firstAdvancement', () => {
                        isSelected = firstAdvancement === optionIndex;
                    });
                    isSelected = firstAdvancement === optionIndex;
                 "
                 class="border rounded-lg transition-all cursor-pointer"
                 :class="{
                    'border-amber-500 bg-amber-500/5': isSelected,
                    'border-slate-600 bg-slate-800/50 hover:border-slate-500': !isSelected
                 }"
                 @click="selectAdvancement({{ $index }}, 'first')">

                <div class="p-4">
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
                    selected="firstAdvancement" />
            </div>
            @endforeach
        </div>
        
        {{-- Domain Card Selection for First Advancement --}}
        @foreach($tierOptions['options'] as $index => $option)
        <x-character-level-up.advancement-domain-card-selection 
            :index="$index" 
            :option="$option" 
            selected="firstAdvancement"
            :character="$character" />
        @endforeach
        
        {{-- Experience Bonus Selection for First Advancement --}}
        @foreach($tierOptions['options'] as $index => $option)
        <x-character-level-up.experience-bonus-selection 
            :index="$index" 
            :option="$option" 
            selected="firstAdvancement" />
        @endforeach
        @endif
    </div>
</div>
