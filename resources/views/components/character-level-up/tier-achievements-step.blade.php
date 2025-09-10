@props(['character', 'advancementChoices'])

<!-- Tier Achievements Step -->
<div x-show="currentStep === 'tier_achievements'" x-cloak>
    <div class="space-y-6">
        <div>
            <h3 class="font-outfit font-bold text-slate-100 text-xl mb-3">Automatic Tier Benefits</h3>
            <p class="text-slate-400">These benefits are automatically applied when you level up.</p>
        </div>

        @if ($character && $character->level + 1 === 2)
            <!-- Level 2 Benefits -->
            <div class="bg-emerald-500/10 border border-emerald-500/20 rounded-lg p-6 mb-6">
                <h4 class="font-semibold text-emerald-400 mb-3 text-lg">Automatic Level 2 Benefits</h4>
                <p class="text-slate-400 text-sm mb-4">These benefits are automatically applied when you level up.</p>
                <ul class="text-slate-300 space-y-2">
                    <li class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        <span>Permanently increase Proficiency by +1</span>
                    </li>
                </ul>
            </div>

            <x-character-level-up.tier-experience-creation :character="$character" :advancementChoices="$advancementChoices" />

            @php
                $availableCards = $this->getAvailableDomainCards(($character->level ?? 1) + 1);
            @endphp
            <x-character-level-up.tier-domain-card-selection :character="$character" :availableCards="$availableCards" />
        @elseif($character && $character->level + 1 === 5)
            <!-- Level 5 Benefits -->
            <div class="bg-emerald-500/10 border border-emerald-500/20 rounded-lg p-6 mb-6">
                <h4 class="font-semibold text-emerald-400 mb-3 text-lg">Level 5 Benefits (Tier 3 Entry)</h4>
                <p class="text-slate-400 text-sm mb-4">These benefits are automatically applied when you level up.</p>
                <ul class="text-slate-300 space-y-2">
                    <li class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        <span>Gain a new Experience at +2 modifier</span>
                    </li>
                    <li class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        <span>Permanently increase Proficiency by +1 (automatic)</span>
                    </li>
                    <li class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        <span>Clear all marked character traits (automatic)</span>
                    </li>
                </ul>
            </div>

            <x-character-level-up.tier-experience-creation :character="$character" :advancementChoices="$advancementChoices" />

            @php
                $availableCards = $this->getAvailableDomainCards(($character->level ?? 1) + 1);
            @endphp
            <x-character-level-up.tier-domain-card-selection :character="$character" :availableCards="$availableCards" />
        @elseif($character && $character->level + 1 === 8)
            <!-- Level 8 Benefits -->
            <div class="bg-emerald-500/10 border border-emerald-500/20 rounded-lg p-6 mb-6">
                <h4 class="font-semibold text-emerald-400 mb-3 text-lg">Level 8 Benefits (Tier 4 Entry)</h4>
                <p class="text-slate-400 text-sm mb-4">These benefits are automatically applied when you level up.</p>
                <ul class="text-slate-300 space-y-2">
                    <li class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        <span>Gain a new Experience at +2 modifier</span>
                    </li>
                    <li class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        <span>Permanently increase Proficiency by +1 (automatic)</span>
                    </li>
                    <li class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        <span>Clear all marked character traits (automatic)</span>
                    </li>
                </ul>
            </div>

            <x-character-level-up.tier-experience-creation :character="$character" :advancementChoices="$advancementChoices" />

            @php
                $availableCards = $this->getAvailableDomainCards(($character->level ?? 1) + 1);
            @endphp
            <x-character-level-up.tier-domain-card-selection :character="$character" :availableCards="$availableCards" />
        @else
            <div class="bg-slate-800 border border-slate-600 rounded-lg p-6 text-center mb-6">
                <p class="text-slate-400">No tier achievements at this level.</p>
                <p class="text-slate-500 text-sm mt-1">Tier achievements occur at levels 2, 5, and 8.</p>
            </div>

            {{-- Domain Card Selection for non-tier levels --}}
            @php
                $availableCards = $this->getAvailableDomainCards(($character->level ?? 1) + 1);
            @endphp
            <x-character-level-up.tier-domain-card-selection :character="$character" :availableCards="$availableCards" />
        @endif


        <div class="bg-blue-500/10 border border-blue-500/20 rounded-lg p-6">
            <h4 class="font-semibold text-blue-400 mb-3 text-lg">Also Applied Automatically</h4>
            <ul class="text-slate-300 space-y-2">
                <li class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>All damage thresholds increase by +1</span>
                </li>
                <li class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>Acquire a new domain card at your level or lower</span>
                </li>
            </ul>
        </div>
    </div>
</div>
