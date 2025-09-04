<!-- Ancestry Selection Step -->
<div x-cloak>
    <!-- Step Header -->
    <div class="mb-6 sm:mb-8">
        <h2 class="text-xl sm:text-2xl font-bold text-white mb-2 font-outfit">Choose Your Ancestry</h2>
        <p class="text-slate-300 font-roboto text-sm sm:text-base">Your ancestry determines your physical traits and innate abilities.</p>
    </div>

    <!-- Step Completion Indicator -->
    <div x-show="selected_ancestry" class="my-6 p-4 bg-gradient-to-r from-emerald-500/10 to-green-500/10 border border-emerald-500/20 rounded-xl">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="bg-emerald-500 rounded-full p-2 mr-3">
                    <svg class="w-5 h-5 text-black" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div>
                    <p class="text-emerald-400 font-semibold">Ancestry Selection Complete!</p>
                    <p class="text-slate-300 text-sm">
                        You are a <strong x-text="selectedAncestryData?.name || ''"></strong>
                    </p>
                </div>
            </div>
            <button 
                pest="change-ancestry-button"
                @click="selectAncestry(null)"
                class="px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-white rounded-lg border border-slate-600 hover:border-slate-500 transition-all duration-200 text-sm font-medium"
            >
                Change Ancestry
            </button>
        </div>
    </div>

    <!-- Ancestry Selection Grid -->
    <div x-show="!hasSelectedAncestry">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 lg:gap-6">
            <template x-for="[ancestryKey, ancestryData] in Object.entries(allAncestries)" :key="ancestryKey">
                <div 
                    :pest="`ancestry-card-${ancestryKey}`"
                    @click="selectAncestry(ancestryKey)"
                    class="relative group cursor-pointer transition-all duration-300 transform hover:scale-[1.02] active:scale-[0.98] bg-gradient-to-br from-slate-800/90 to-slate-900/90 backdrop-blur border border-slate-700/50 hover:border-slate-600/70 hover:shadow-lg hover:shadow-slate-500/10 rounded-2xl p-4 sm:p-6 touch-manipulation"
                >
                    <!-- Playtest Badge -->
                    <template x-if="ancestryData.playtest?.isPlaytest">
                        <div class="absolute -top-2 -right-2 bg-gradient-to-r from-purple-600 to-violet-600 text-white text-xs font-bold px-3 py-1 rounded-full border-2 border-slate-800 shadow-lg">
                            <div class="flex items-center gap-1">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span x-text="`Void - Playtest ${ancestryData.playtest?.version || 'v2.0'}`"></span>
                            </div>
                        </div>
                    </template>

                    <div class="flex flex-col h-full">
                        <!-- Header -->
                        <div class="mb-3 sm:mb-4">
                            <h3 class="text-lg sm:text-xl font-bold text-white font-outfit mb-2" x-text="ancestryData.name"></h3>
                            <p class="text-slate-300 text-xs sm:text-sm line-clamp-3 leading-relaxed mb-3" x-text="ancestryData.description"></p>
                        </div>

                        <!-- Features Preview -->
                        <template x-if="ancestryData.features && ancestryData.features.length > 0">
                            <div class="space-y-2 flex-grow">
                                <h4 class="text-emerald-400 font-semibold text-xs sm:text-sm font-outfit">Features</h4>
                                <template x-for="(feature, index) in ancestryData.features.slice(0, 2)" :key="feature.name">
                                    <div class="bg-slate-800/50 rounded-lg p-2 sm:p-3">
                                        <div class="text-white font-medium text-xs sm:text-sm mb-1" x-text="feature.name"></div>
                                        <div class="text-slate-300 text-xs line-clamp-2" x-text="feature.description"></div>
                                    </div>
                                </template>
                                <template x-if="ancestryData.features.length > 2">
                                    <p class="text-slate-400 text-xs" x-text="`+${ancestryData.features.length - 2} more features`"></p>
                                </template>
                            </div>
                        </template>

                        <!-- Tap indicator -->
                        <div class="mt-3 sm:mt-4 text-center opacity-0 group-hover:opacity-100 transition-opacity">
                            <span class="text-amber-400 text-xs sm:text-sm font-medium">Tap to select</span>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Selected Ancestry Details -->
    <template x-if="hasSelectedAncestry && selectedAncestryData">
        <div class="bg-gradient-to-br from-slate-800/90 to-slate-900/90 backdrop-blur border border-slate-700/50 rounded-2xl p-4 sm:p-6">
            <!-- Header with Change Button -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 sm:mb-6">
                <div>
                    <h3 class="text-xl sm:text-2xl font-bold text-white font-outfit mb-2" x-text="selectedAncestryData.name"></h3>
                    <p class="text-slate-300 text-sm sm:text-base" x-text="selectedAncestryData.description"></p>
                </div>
            </div>

            <!-- Features -->
            <template x-if="selectedAncestryData.features && selectedAncestryData.features.length > 0">
                <div class="mb-4 sm:mb-6">
                    <h4 class="text-base sm:text-lg font-semibold text-white font-outfit mb-3">Ancestry Features</h4>
                    <div class="space-y-2 sm:space-y-3">
                        <template x-for="feature in selectedAncestryData.features" :key="feature.name">
                            <div class="bg-slate-800/50 border border-slate-700/50 rounded-lg p-3 sm:p-4">
                                <h5 class="text-white font-semibold text-sm mb-2" x-text="feature.name"></h5>
                                <p class="text-slate-300 text-sm leading-relaxed" x-text="feature.description"></p>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </template>
</div>
