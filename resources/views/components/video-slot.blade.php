<div class="video-slot h-full w-full bg-gradient-to-br from-slate-800 to-slate-900 border border-amber-500/30 overflow-hidden hover:border-amber-400/60 transition-all duration-300" data-slot-id="{{ $slotId }}">
    <div class="h-full w-full bg-gradient-to-br from-slate-700 via-slate-800 to-slate-900 flex items-center justify-center relative">
        <video class="local-video hidden w-full h-full object-cover" autoplay muted playsinline></video>
        <div class="remote-videos absolute inset-0 gap-1 p-1 hidden">
            <!-- Remote videos will be added here dynamically -->
        </div>
        
        <div class='border-corners'>
            <!-- Decorative corner elements -->
            <div class="absolute top-2 left-2 w-4 h-4 border-l-2 border-t-2 border-amber-400/50"></div>
            <div class="absolute top-2 right-2 w-4 h-4 border-r-2 border-t-2 border-amber-400/50"></div>
            <div class="absolute bottom-2 left-2 w-4 h-4 border-l-2 border-b-2 border-amber-400/50"></div>
            <div class="absolute bottom-2 right-2 w-4 h-4 border-r-2 border-b-2 border-amber-400/50"></div>
        </div>
        
        <!-- Character Sheet Overlay (hidden by default, shown when occupied) -->
        <div class="character-overlay hidden absolute inset-0 pointer-events-none">
            @if($slotId == 1)
                <div class="absolute bottom-2 left-0 w-auto pointer-events-auto overflow-visible">
                    <div class="relative h-[54px] min-w-[45%]">
                        <!-- Banner Background (Positioned absolutely behind, extending to the right) -->
                        <div class="absolute right-0 h-full z-1" style="transform: translateX(68px); top: -0.5px;">
                            <x-banner.right style="width: 72px; height: 55px;" />
                        </div>
                        
                        <!-- Main Content Container (Above banner) -->
                        <div class="relative z-10 bg-daggerheart-blue border border-daggerheart-gold border-l-0 border-r-0 h-full flex items-center">                            
                            <!-- Character Info -->
                            <div class="text-left pl-4 py-3">
                                <div class="character-name font-fantasy text-amber-300 text-lg tracking-wide cursor-text leading-tight" contenteditable="true">
                                    GAME MASTER
                                </div>
                                <div class="text-xs text-gray-400 flex gap-1 flex-wrap">
                                    <span class="character-class cursor-text uppercase tracking-wide" contenteditable="true">
                                        NARRATOR OF TALES
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- Character Name Panel (Bottom Left - 45% width) -->
                <div class="absolute bottom-2 left-0 w-auto pointer-events-auto overflow-visible">
                    <div class="relative h-[54px] min-w-[45%]">
                        <!-- Banner Background (Positioned absolutely behind, extending to the right) -->
                        <div class="absolute right-0 h-full z-1" style="transform: translateX(68px); top: -0.5px;">
                            <x-banner.right style="width: 72px; height: 55px;" />
                        </div>
                        
                        <!-- Main Content Container (Above banner) -->
                        <div class="relative z-10 bg-daggerheart-blue border border-daggerheart-gold border-l-0 border-r-0 h-full flex items-center">
                            @if(isset($character))
                                <!-- Character Banner (Absolute positioned) -->
                                <div class="absolute -top-1 left-4">
                                    <img src="{{ $character->getBanner() }}" alt="{{ $character->class }}" class="w-8 h-16 object-cover rounded">
                                </div>
                            @endif
                            
                            <!-- Character Info -->
                            <div class="text-left {{ isset($character) ? 'ml-11 pl-4' : 'px-4' }} py-3">
                                <div class="character-name font-fantasy text-amber-300 text-lg tracking-wide cursor-text leading-tight" contenteditable="true">
                                    {{ isset($character) ? $character->name : 'Unknown Hero' }}
                                </div>
                                <div class="text-xs text-gray-400 flex gap-1 flex-wrap">
                                    <span class="character-class cursor-text uppercase tracking-wide" contenteditable="true">
                                        {{ isset($character) ? $character->class : 'TBC' }}
                                    </span>
                                    <span>/</span>
                                    <span class="character-subclass cursor-text uppercase tracking-wide" contenteditable="true">
                                        {{ isset($character) ? $character->subclass : 'TBC' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
        
        <button class="join-btn bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-black font-bold py-3 px-6 rounded-lg text-lg transition-all duration-300 shadow-lg hover:shadow-amber-500/50 transform hover:scale-105">
            <span class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2L13.5 8.5L20 10L13.5 11.5L12 18L10.5 11.5L4 10L10.5 8.5L12 2Z"/>
                </svg>
                Join Quest
            </span>
        </button>
        
        <!-- Leave button hidden as requested -->
        <button class="leave-btn hidden absolute top-4 right-4 bg-gradient-to-r from-red-600 to-red-500 hover:from-red-500 hover:to-red-400 text-white font-medium py-2 px-4 rounded-lg text-sm transition-all duration-300 shadow-lg z-10" style="display: none;">
            Retreat
        </button>
        
        <div class="loading-spinner hidden absolute inset-0 items-center justify-center bg-black bg-opacity-75">
            <div class="flex flex-col items-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-amber-400"></div>
                <p class="text-amber-300 mt-3 text-sm">Entering the realm...</p>
            </div>
        </div>
    </div>
</div>