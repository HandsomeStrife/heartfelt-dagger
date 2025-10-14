@props([
    'level' => null,
    'show_experience_form' => false,
    'show_domain_card_selector' => false,
    'experience_name' => '',
    'experience_description' => '',
    'is_experience_created' => false,
])

@inject('tierAchievementService', 'Domain\Character\Services\TierAchievementService')

{{-- Calculate tier and benefits using service --}}
@once
    @push('scripts')
        {{-- No JavaScript needed --}}
    @endpush
@endonce

<div class="space-y-6">
    @if($tierAchievementService->isTierAchievementLevel($level))
        <!-- Tier Benefits Header -->
        <div>
            <h3 class="font-outfit font-bold text-slate-100 text-xl mb-3">
                @if($level === 2)
                    Tier 2 Entry Benefits
                @elseif($level === 5)
                    Tier 3 Entry Benefits
                @elseif($level === 8)
                    Tier 4 Entry Benefits
                @endif
            </h3>
            <p class="text-slate-400">These benefits are applied when you reach level {{ $level }}.</p>
        </div>

        <!-- Benefits List -->
        <div class="bg-emerald-500/10 border border-emerald-500/20 rounded-lg p-6">
            <h4 class="font-semibold text-emerald-400 mb-3 text-lg">
                @if($level === 2)
                    Level 2 Benefits
                @elseif($level === 5)
                    Level 5 Benefits (Tier 3 Entry)
                @elseif($level === 8)
                    Level 8 Benefits (Tier 4 Entry)
                @endif
            </h4>
            <p class="text-slate-400 text-sm mb-4">These benefits are applied when you level up.</p>
            <ul class="text-slate-300 space-y-2">
                <li class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>Gain a new Experience at +2 modifier</span>
                </li>
                <li class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>
                        Permanently increase Proficiency by +1
                        <span class="text-slate-500 text-sm italic">(automatic)</span>
                    </span>
                </li>
                @if(in_array($level, [5, 8]))
                    <li class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>
                            Clear all marked character traits
                            <span class="text-slate-500 text-sm italic">(automatic)</span>
                        </span>
                    </li>
                @endif
            </ul>
        </div>

        <!-- Experience Form (if enabled) -->
        @if($show_experience_form)
            <x-character.experience-form
                :level="$level"
                :experience_name="$experience_name"
                :experience_description="$experience_description"
                :is_created="$is_experience_created"
            />
        @endif
    @else
        <!-- No Tier Achievement -->
        <div class="bg-slate-800 border border-slate-600 rounded-lg p-6 text-center">
            <p class="text-slate-400">No tier achievements at level {{ $level }}.</p>
            <p class="text-slate-500 text-sm mt-1">Tier achievements occur at levels 2, 5, and 8.</p>
        </div>
    @endif

    <!-- Always Applied Benefits -->
    <div class="bg-blue-500/10 border border-blue-500/20 rounded-lg p-6">
        <h4 class="font-semibold text-blue-400 mb-3 text-lg">Also Applied Automatically</h4>
        <ul class="text-slate-300 space-y-2">
            <li class="flex items-center space-x-2">
                <svg class="w-5 h-5 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>All damage thresholds increase by +1</span>
            </li>
            @if($level >= 2)
                <li class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>Your level increases to {{ $level }}</span>
                </li>
            @endif
        </ul>
    </div>

    <!-- Helpful Note -->
    @if($tierAchievementService->isTierAchievementLevel($level))
        <div class="bg-amber-500/10 border border-amber-500/20 rounded-lg p-4">
            <div class="flex items-start space-x-3">
                <svg class="w-5 h-5 text-amber-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
                <div>
                    <p class="text-amber-300 font-semibold text-sm">Tier Achievement Level</p>
                    <p class="text-amber-200/80 text-sm mt-1">
                        Tier achievements represent significant growth in your character's capabilities. 
                        @if(in_array($level, [5, 8]))
                            At this level, you also clear any marked traits, allowing you to improve them again later.
                        @endif
                    </p>
                </div>
            </div>
        </div>
    @endif
</div>

