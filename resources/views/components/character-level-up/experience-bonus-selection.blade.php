@props(['index', 'option', 'selected'])

{{-- Experience Bonus Selection for Advancement --}}
@if(str_contains(strtolower($option['description'] ?? ''), 'bonus to') && str_contains(strtolower($option['description'] ?? ''), 'experiences'))
<div class="mt-6 border-t border-slate-600 pt-6" 
     x-show="{{ $selected }} === {{ $index }}"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 transform scale-95"
     x-transition:enter-end="opacity-100 transform scale-100">
    <h5 class="text-slate-200 font-medium mb-3">Select Experiences to Boost</h5>
    <p class="text-slate-400 text-sm mb-4">Choose 2 experiences to receive a +1 bonus.</p>
    
    @if($this->getAllCharacterExperiences()->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        @foreach($this->getAllCharacterExperiences() as $experience)
        <div wire:click="selectExperienceBonus({{ $index }}, '{{ $experience->name }}')"
             x-bind:class="advancementChoices[{{ $index }}]?.experience_bonuses?.includes('{{ $experience->name }}') ? 'border-amber-500 bg-amber-500/10' : 'border-slate-600 hover:border-slate-500'"
             class="border rounded-lg p-3 cursor-pointer transition-all">
            <div class="flex items-start justify-between">
                <div>
                    <h6 class="text-slate-200 font-medium text-sm">{{ $experience->name }}</h6>
                    @if($experience->description)
                    <p class="text-slate-400 text-xs mt-1">{{ $experience->description }}</p>
                    @endif
                    @if(isset($experience->is_pending) && $experience->is_pending)
                    <span class="inline-block mt-1 px-2 py-1 text-xs bg-amber-500/20 text-amber-400 rounded">
                        New Experience (Pending)
                    </span>
                    @endif
                </div>
                <div class="flex items-center">
                    <span class="text-xs px-2 py-1 rounded bg-green-600 text-white">+{{ $experience->modifier }}</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    
    <div class="mt-3">
        <span class="text-amber-400 text-sm" 
              x-text="(advancementChoices[{{ $index }}]?.experience_bonuses?.length || 0) + '/2 selected'">
        </span>
    </div>
    @else
    <div class="bg-slate-800 border border-slate-600 rounded-lg p-4 text-center">
        <p class="text-slate-400 text-sm">No experiences available. Create some experiences first to use this advancement.</p>
    </div>
    @endif
</div>
@endif
