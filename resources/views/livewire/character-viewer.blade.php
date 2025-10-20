<div x-data="characterViewerState({
    canEdit: @js($can_edit),
    isAuthenticated: @js(auth()->check()),
    characterKey: @js($character_key),
    final_hit_points: @js($computed_stats['final_hit_points'] ?? 6),
    stress_len: @js($computed_stats['stress'] ?? 6),
    armor_score: @js($computed_stats['armor_score'] ?? 0),
    hope: @js($computed_stats['hope'] ?? 2),
    initialStatus: @js($character_status ? $character_status->toAlpineState() : null),
    showLoadingScreen: true
})" class="bg-slate-950 text-slate-100/95 antialiased min-h-screen relative"
    style="font-family: Inter, ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, 'Helvetica Neue', Arial, 'Apple Color Emoji', 'Segoe UI Emoji';">

    <!-- LOADING SCREEN -->
    <div x-show="showLoadingScreen" 
         x-transition:leave="transition ease-out duration-500"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         id="character-loading-screen" 
         class="fixed inset-0 bg-slate-950 z-50 flex items-center justify-center">
        <div class="text-center">
            <div class="mb-6">
                <div class="inline-block animate-spin rounded-full h-16 w-16 border-b-2 border-amber-500"></div>
            </div>
            <h2 class="text-2xl font-outfit font-bold text-slate-100 mb-2">Loading Character</h2>
            <p class="text-slate-400 mb-4">Initializing dice system and character data...</p>
            <div class="flex items-center justify-center space-x-2">
                <div class="w-2 h-2 bg-amber-500 rounded-full animate-bounce"></div>
                <div class="w-2 h-2 bg-amber-500 rounded-full animate-bounce" style="animation-delay: 0.1s;"></div>
                <div class="w-2 h-2 bg-amber-500 rounded-full animate-bounce" style="animation-delay: 0.2s;"></div>
            </div>
        </div>
    </div>

    <main class="max-w-7xl mx-auto p-6 md:p-8 space-y-6">

        <!-- TOP BANNER -->
        <x-character-viewer.top-banner
            :character="$character"
            :pronouns="$pronouns"
            :class-data="$class_data"
            :subclass-data="$subclass_data"
            :ancestry-data="$ancestry_data"
            :community-data="$community_data"
            :computed-stats="$computed_stats"
            :can-edit="$can_edit"
            :trait-info="$trait_info"
            :trait-values="$trait_values"
            :can-level-up="$can_level_up"
            :character-key="$character_key"
        />

        <!-- MAIN: Left = Damage & Health, Right = Hope + Gold -->
        <section class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left: DAMAGE & HEALTH -->
            <div class="lg:col-span-7">
                <x-character-viewer.damage-health :computed-stats="$computed_stats" />

                <x-character-viewer.active-weapons 
                    :organized-equipment="$organized_equipment" 
                    :character="$character" 
                    :trait-values="$trait_values"
                    :weaponDamageCount="$weapon_damage_count"
                    :primaryWeapon="$primary_weapon"
                    :primaryWeaponFeature="$primary_weapon_feature" />

                <x-character-viewer.active-armor :organized-equipment="$organized_equipment" />
            </div>

            <!-- Right: HOPE + GOLD -->
            <div class="lg:col-span-5 space-y-6">
                <x-character-viewer.hope :class-data="$class_data" />

                <x-character-viewer.gold />

                <x-character-viewer.experience :character="$character" />
            </div>
        </section>

        <!-- FEATURES -->
        <section class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="lg:col-span-12 grid grid-cols-1 gap-6">
                <!-- Subclass Features -->
                <x-character-viewer.subclass-features :subclass-data="$subclass_data" />
                
                <!-- Domain Effects as Cards -->
                <x-character-viewer.domain-cards :domain-card-details="$domain_card_details" />
                
                <!-- Advancement Summary (for characters level 2+) -->
                @if($character->level > 1)
                    <x-character.advancement-summary 
                        :advancements="$this->getFormattedAdvancements()"
                        :tierExperiences="$this->getFormattedTierExperiences()"
                        :domainCards="$this->getFormattedDomainCards()"
                        :startingLevel="$character->level"
                    />
                @endif
                
                <!-- Ancestry and Community Features (side-by-side on xl screens) -->
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                    <!-- Ancestry Features -->
                    <x-character-viewer.ancestry-features :ancestry-data="$ancestry_data" />
                    
                    <!-- Community Features -->
                    <x-character-viewer.community-features :community-data="$community_data" />
                </div>
            </div>
        </section>

        <!-- JOURNAL -->
        <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <x-character-viewer.equipment :organized-equipment="$organized_equipment" />
            <x-character-viewer.journal :character="$character" />
        </section>
    </main>

    <!-- DICE CONTAINER -->
    <div id="dice-container" class="fixed inset-0" style="pointer-events: none; width: 100vw; height: 100vh; z-index: 9999;" wire:ignore>
        <!-- Canvas will be inserted here by dice-box -->
    </div>

    <!-- FLOATING DICE SELECTOR -->
    <x-dice-selector />
    
    <style>
        #dice-container canvas {
            width: 100vw !important;
            height: 100vh !important;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            pointer-events: none !important; /* No pointer events on dice canvas */
            z-index: 9999 !important;
        }
    </style>

    <!-- DICE INITIALIZATION SCRIPT -->
    <script>
        // Provide graceful fallbacks so UI doesn't stall if dice bundle loads late
        window.initDiceBox = window.initDiceBox || function() {
            console.warn('Dice library not loaded yet; initDiceBox stub invoked.');
            return null;
        };
        window.setupDiceCallbacks = window.setupDiceCallbacks || function() {
            console.warn('Dice library not loaded yet; setupDiceCallbacks stub invoked.');
        };
        
        // Create a callback that Alpine can register
        window.hideLoadingScreenCallback = null;
        
        // Function to hide loading screen via callback
        function hideLoadingScreen() {
            console.log('Attempting to hide loading screen...');
            if (window.hideLoadingScreenCallback) {
                window.hideLoadingScreenCallback();
                console.log('Loading screen hidden via callback');
            } else {
                console.warn('Loading screen callback not yet registered');
            }
        }

        // Wait for both DOM and Livewire to be ready
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Character viewer DOM loaded');
            // Early safety: never block UI for long if dice libs are late/missing
            setTimeout(() => {
                hideLoadingScreen();
            }, 2000);
            
            // Wait for Livewire to be fully loaded
            document.addEventListener('livewire:navigated', function() {
                console.log('Livewire navigated - initializing dice');
                initializeDiceSystem();
            });
            
            // Also try after a delay in case livewire:navigated doesn't fire
            setTimeout(() => {
                console.log('Fallback dice initialization');
                initializeDiceSystem();
            }, 1000);
            
            function initializeDiceSystem() {
                // Check if dice container exists
                const container = document.getElementById('dice-container');
                if (!container) {
                    console.error('Dice container not found!');
                    hideLoadingScreen(); // Hide loading screen even if dice fails
                    return;
                }
                console.log('Dice container found:', container);
                
                // Wait for dice functions to be available
                let retryCount = 0;
                const maxRetries = 50; // 10 seconds max
                
                const initDice = () => {
                    console.log('Checking for dice functions...');
                    console.log('initDiceBox available:', typeof window.initDiceBox);
                    console.log('setupDiceCallbacks available:', typeof window.setupDiceCallbacks);
                    
                    if (typeof window.initDiceBox !== 'undefined') {
                        console.log('Initializing DiceBox for character viewer...');
                        console.log('Viewport:', window.innerWidth + 'x' + window.innerHeight);
                        
                        try {
                            // Initialize dice box
                            let diceBox = window.initDiceBox('#dice-container');
                            console.log('DiceBox instance:', diceBox);
                            
                            // Set up roll completion callback
                            if (typeof window.setupDiceCallbacks === 'function') {
                                window.setupDiceCallbacks((rollResult) => {
                                    console.log('Roll completed:', rollResult);
                                });
                            }
                            
                            // Check for canvas creation after a delay
                            setTimeout(() => {
                                const canvas = document.querySelector('#dice-container canvas');
                                if (canvas) {
                                    console.log('Canvas found in character viewer:', canvas.width + 'x' + canvas.height);
                                } else {
                                    console.error('No canvas found in character viewer dice container!');
                                    console.log('Dice container contents:', document.getElementById('dice-container').innerHTML);
                                    console.log('All canvas elements on page:', document.querySelectorAll('canvas'));
                                }
                                
                                // Hide loading screen once dice system is ready
                                hideLoadingScreen();
                            }, 1000);
                            
                            console.log('Character viewer dice system ready');
                            
                        } catch (error) {
                            console.error('Error initializing dice system:', error);
                            hideLoadingScreen(); // Hide loading screen even if dice fails
                        }
                    } else {
                        retryCount++;
                        if (retryCount >= maxRetries) {
                            console.warn('Dice functions not available after maximum retries, hiding loading screen');
                            hideLoadingScreen();
                        } else {
                            console.log(`Dice functions not ready, retrying... (${retryCount}/${maxRetries})`);
                            setTimeout(initDice, 200);
                        }
                    }
                };
                
                initDice();
            }
        });
    </script>
</div>
