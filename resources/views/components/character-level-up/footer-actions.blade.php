@props(['character', 'characterKey'])

<!-- Footer Actions -->
<div class="flex items-center justify-between pt-6 border-t border-slate-700 mt-8">
    <div>
        <button type="button" @click="previousStep()"
                x-show="currentStep !== 'tier_achievements'"
                class="px-6 py-3 bg-slate-600 text-slate-200 hover:bg-slate-500 rounded-lg transition-colors font-medium">
            Previous
        </button>
    </div>
    
    <div class="flex space-x-4">
        <a href="{{ route('character.show', ['public_key' => $character->public_key ?? '', 'character_key' => $characterKey]) }}" 
           class="px-6 py-3 bg-slate-700 text-slate-300 hover:bg-slate-600 rounded-lg transition-colors font-medium">
            Cancel
        </a>
        
        <button type="button" @click="$wire.confirmLevelUp()"
                x-show="currentStep === 'confirmation'"
                class="px-6 py-3 bg-gradient-to-r from-emerald-500 to-green-500 text-white hover:from-emerald-600 hover:to-green-600 rounded-lg transition-colors font-semibold">
            Confirm Level Up
        </button>
        
        <button type="button" @click="nextStep()"
                data-test="level-up-continue"
                x-show="currentStep !== 'confirmation'"
                :disabled="!canGoNext()"
                :class="!canGoNext() ? 'opacity-50 cursor-not-allowed' : ''"
                class="px-6 py-3 bg-gradient-to-r from-amber-500 to-orange-500 text-white hover:from-amber-600 hover:to-orange-600 rounded-lg transition-colors font-semibold">
            <span x-text="currentStep === 'tier_achievements' ? 'Continue' : 'Next'"></span>
        </button>
    </div>
</div>
