<div x-data="characterViewerState({
    canEdit: @js($can_edit),
    isAuthenticated: @js(auth()->check()),
    characterKey: @js($character_key),
    final_hit_points: @js($computed_stats['final_hit_points'] ?? 6),
    stress_len: @js($computed_stats['stress'] ?? 6),
    armor_score: @js($computed_stats['armor_score'] ?? 0),
    hope: @js($computed_stats['hope'] ?? 2),
    initialStatus: @js($character_status ? $character_status->toAlpineState() : null)
})" class="bg-slate-950 text-slate-100/95 antialiased min-h-screen"
    style="font-family: Inter, ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, 'Helvetica Neue', Arial, 'Apple Color Emoji', 'Segoe UI Emoji';">

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
            :trait-info="$this->getTraitInfo()"
            :trait-values="$trait_values"
        />

        <!-- MAIN: Left = Damage & Health, Right = Hope + Gold -->
        <section class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left: DAMAGE & HEALTH -->
            <div class="lg:col-span-7">
                <x-character-viewer.damage-health :computed-stats="$computed_stats" />

                <x-character-viewer.active-weapons :organized-equipment="$organized_equipment" :character="$character" />

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

        <button pest="refresh-button" wire:click='$refresh'>Refresh</button>
    </main>

    <!-- DICE CONTAINER -->
    <div id="dice-container" class="fixed inset-0" style="pointer-events: none; width: 100vw; height: 100vh; z-index: 9999;" wire:ignore>
        <!-- Canvas will be inserted here by dice-box -->
    </div>
    
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
        // Wait for both DOM and Livewire to be ready
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Character viewer DOM loaded');
            
            // Wait for Livewire to be fully loaded
            document.addEventListener('livewire:navigated', function() {
                console.log('Livewire navigated - initializing dice');
                initializeDiceSystem();
            });
            
            // Also try after a delay in case livewire:navigated doesn't fire
            setTimeout(() => {
                console.log('Fallback dice initialization');
                initializeDiceSystem();
            }, 3000);
            
            function initializeDiceSystem() {
                // Check if dice container exists
                const container = document.getElementById('dice-container');
                if (!container) {
                    console.error('Dice container not found!');
                    return;
                }
                console.log('Dice container found:', container);
                
                // Wait for dice functions to be available
                const initDice = () => {
                    console.log('Checking for dice functions...');
                    console.log('initDiceBox available:', typeof window.initDiceBox);
                    console.log('setupDiceCallbacks available:', typeof window.setupDiceCallbacks);
                    
                    if (typeof window.initDiceBox !== 'undefined') {
                        console.log('Initializing DiceBox for character viewer...');
                        console.log('Viewport:', window.innerWidth + 'x' + window.innerHeight);
                        
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
                        }, 3000);
                        
                        console.log('Character viewer dice system ready');
                    } else {
                        console.log('Dice functions not ready, retrying...');
                        setTimeout(initDice, 200);
                    }
                };
                
                initDice();
            }
        });
    </script>
</div>
