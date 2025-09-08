@props(['class' => ''])

<div x-data="{ 
    diceMenuOpen: false, 
    selectedDice: {},
    diceTypes: ['d4', 'd6', 'd8', 'd10', 'd12', 'd20'],
    addDie(type) {
        this.selectedDice[type] = (this.selectedDice[type] || 0) + 1;
    },
    removeDie(type) {
        if (this.selectedDice[type] > 0) {
            this.selectedDice[type]--;
            if (this.selectedDice[type] === 0) {
                delete this.selectedDice[type];
            }
        }
    },
    clearDice() {
        this.selectedDice = {};
    },
    rollSelected() {
        if (Object.keys(this.selectedDice).length === 0) return;
        
        // Build dice array for rolling
        let diceToRoll = [];
        Object.entries(this.selectedDice).forEach(([type, count]) => {
            for (let i = 0; i < count; i++) {
                diceToRoll.push({ sides: parseInt(type.substring(1)), theme: 'default' });
            }
        });
        
        if (window.rollCustomDice) {
            window.rollCustomDice(diceToRoll);
        }
        
        // Clear selections after rolling
        this.selectedDice = {};
    },
    rollDuality() {
        if (window.rollDualityDice) {
            window.rollDualityDice(0);
        }
        this.diceMenuOpen = false;
    },
    getTotalDice() {
        return Object.values(this.selectedDice).reduce((sum, count) => sum + count, 0);
    }
}" class="fixed bottom-6 left-6 z-40 {{ $class }}">
    
    <!-- Bottom Row: Main Dice Button, Roll Button, Close Button -->
    <div class="flex items-center gap-2">
        <!-- Main Dice Button -->
        <div class="relative group/main">
            <button @click="diceMenuOpen = !diceMenuOpen" 
                    class="relative w-16 h-16 cursor-pointer bg-slate-900 hover:bg-slate-800 rounded-full shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center group border-2 border-slate-700">
                <!-- D12 Icon -->
                <div class="w-full h-full text-amber-400 group-hover:scale-110 transition-transform">
                    <x-icons.dice.d12 class="w-full h-full" />
                </div>
                
                <!-- Badge for selected dice count -->
                <div x-show="getTotalDice() > 0" 
                     x-text="getTotalDice()"
                     class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center">
                </div>
            </button>
            
            <!-- Hover Pill -->
            <div class="absolute left-20 top-1/2 -translate-y-1/2 opacity-0 group-hover/main:opacity-100 transition-opacity duration-200 pointer-events-none z-50">
                <div class="bg-slate-800 text-white px-3 py-1 rounded-full text-sm font-medium shadow-lg border border-slate-600 whitespace-nowrap">
                    Dice Roller
                </div>
                <!-- Arrow pointing left -->
                <div class="absolute right-full top-1/2 -translate-y-1/2 w-0 h-0 border-t-4 border-b-4 border-r-4 border-transparent border-r-slate-800"></div>
            </div>
        </div>
        
        <!-- Roll Button -->
        <div x-show="getTotalDice() > 0" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="relative group/roll">
            <button @click="rollSelected()"
                    class="w-16 h-16 cursor-pointer bg-red-600 hover:bg-red-500 border-2 border-red-500 rounded-full shadow-lg transition-all duration-200 flex flex-col items-center justify-center text-white font-bold text-xs">
                <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7" />
                </svg>
                <span>ROLL</span>
            </button>
            
            <!-- Hover Pill -->
            <div class="absolute left-20 top-1/2 -translate-y-1/2 opacity-0 group-hover/roll:opacity-100 transition-opacity duration-200 pointer-events-none z-50">
                <div class="bg-slate-800 text-white px-3 py-1 rounded-full text-sm font-medium shadow-lg border border-slate-600 whitespace-nowrap">
                    Roll Selected Dice
                </div>
                <!-- Arrow pointing left -->
                <div class="absolute right-full top-1/2 -translate-y-1/2 w-0 h-0 border-t-4 border-b-4 border-r-4 border-transparent border-r-slate-800"></div>
            </div>
        </div>
        
        <!-- Close Button -->
        <div x-show="diceMenuOpen" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="relative group/close">
            <button @click="diceMenuOpen = false"
                    class="w-16 h-16 cursor-pointer bg-slate-700 hover:bg-slate-600 border-2 border-slate-600 rounded-full shadow-lg transition-all duration-200 flex flex-col items-center justify-center text-white font-bold text-xs">
                <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span>CLOSE</span>
            </button>
            
            <!-- Hover Pill -->
            <div class="absolute left-20 top-1/2 -translate-y-1/2 opacity-0 group-hover/close:opacity-100 transition-opacity duration-200 pointer-events-none z-50">
                <div class="bg-slate-800 text-white px-3 py-1 rounded-full text-sm font-medium shadow-lg border border-slate-600 whitespace-nowrap">
                    Close Dice Menu
                </div>
                <!-- Arrow pointing left -->
                <div class="absolute right-full top-1/2 -translate-y-1/2 w-0 h-0 border-t-4 border-b-4 border-r-4 border-transparent border-r-slate-800"></div>
            </div>
        </div>
    </div>

    <!-- Vertical Dice Stack Menu -->
    <div x-show="diceMenuOpen" 
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95 translate-y-2"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-2"
         @click.away="diceMenuOpen = false"
         class="absolute bottom-20 left-0 flex flex-col gap-2">
        
        <!-- Dice Stack (d20 to d4 from top to bottom) -->
        <template x-for="dieType in ['d20', 'd12', 'd10', 'd8', 'd6', 'd4']" :key="dieType">
            <div class="relative group/dice">
                <!-- Dice Button -->
                <button @click="addDie(dieType)"
                        class="relative w-16 h-16 bg-slate-900 hover:bg-slate-800 border-2 border-slate-700 rounded-full shadow-lg transition-all duration-200 flex items-center justify-center group">
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="w-14 h-14 text-amber-400 group-hover:scale-110 transition-transform cursor-pointer">
                            <template x-if="dieType === 'd4'">
                                <x-icons.dice.d4 class="w-full h-full" />
                            </template>
                            <template x-if="dieType === 'd6'">
                                <x-icons.dice.d6 class="w-full h-full" />
                            </template>
                            <template x-if="dieType === 'd8'">
                                <x-icons.dice.d8 class="w-full h-full" />
                            </template>
                            <template x-if="dieType === 'd10'">
                                <x-icons.dice.d10 class="w-full h-full" />
                            </template>
                            <template x-if="dieType === 'd12'">
                                <x-icons.dice.d12 class="w-full h-full" />
                            </template>
                            <template x-if="dieType === 'd20'">
                                <x-icons.dice.d20 class="w-full h-full" />
                            </template>
                        </div>
                    </div>
                </button>
                
                <!-- Hover Pill -->
                <div class="absolute left-20 top-1/2 -translate-y-1/2 opacity-0 group-hover/dice:opacity-100 transition-opacity duration-200 pointer-events-none z-50">
                    <div class="bg-slate-800 text-white px-3 py-1 rounded-full text-sm font-medium shadow-lg border border-slate-600 whitespace-nowrap"
                         x-text="dieType.toUpperCase()">
                    </div>
                    <!-- Arrow pointing left -->
                    <div class="absolute right-full top-1/2 -translate-y-1/2 w-0 h-0 border-t-4 border-b-4 border-r-4 border-transparent border-r-slate-800"></div>
                </div>
                
                <!-- Count Badge -->
                <div x-show="selectedDice[dieType] > 0" 
                     x-text="selectedDice[dieType]"
                     class="absolute -top-1 -right-1 bg-amber-500 text-black text-sm font-bold rounded-full w-7 h-7 flex items-center justify-center shadow-lg">
                </div>
            </div>
        </template>
        
        <!-- Special Duality Dice -->
        <div class="relative mt-2 group/duality">
            <button @click="rollDuality()"
                    class="w-16 h-16 relative cursor-pointer bg-gradient-to-br from-blue-600 to-purple-600 hover:from-blue-500 hover:to-purple-500 border-2 border-blue-500 rounded-full shadow-lg transition-all duration-200 flex items-center justify-center group">
                <div>
                    <div class="w-16 h-16 text-white absolute left-2 -top-0.5">
                        <x-icons.dice.d12 class="w-full h-full" />
                    </div>
                    <div class="w-16 h-16 text-black absolute right-2 -top-0.5">
                        <x-icons.dice.d12 class="w-full h-full" />
                    </div>
                </div>
            </button>
            
            <!-- Hover Pill -->
            <div class="absolute left-20 top-1/2 -translate-y-1/2 opacity-0 group-hover/duality:opacity-100 transition-opacity duration-200 pointer-events-none z-50">
                <div class="bg-slate-800 text-white px-3 py-1 rounded-full text-sm font-medium shadow-lg border border-slate-600 whitespace-nowrap">
                    Duality Dice (2d12)
                </div>
                <!-- Arrow pointing left -->
                <div class="absolute right-full top-1/2 -translate-y-1/2 w-0 h-0 border-t-4 border-b-4 border-r-4 border-transparent border-r-slate-800"></div>
            </div>
        </div>
    </div>
</div>
