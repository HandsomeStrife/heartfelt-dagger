@props(['slotId', 'participant' => null, 'isHost' => false, 'userIsCreator' => false, 'isGmReservedSlot' => false, 'viewerMode' => false])

<div class="video-slot group h-full w-full bg-gradient-to-br from-slate-800 to-slate-900 border {{ $isHost ? 'border-emerald-500/30' : 'border-amber-500/30' }} overflow-hidden hover:border-{{ $isHost ? 'emerald' : 'amber' }}-400/60 transition-all duration-300" data-slot-id="{{ $slotId }}" data-testid="video-slot">
    <div class="h-full w-full bg-gradient-to-br from-slate-700 via-slate-800 to-slate-900 flex items-center justify-center relative">
        <video class="local-video hidden w-full h-full object-cover" autoplay muted playsinline></video>
        <div class="remote-videos absolute inset-0 gap-1 p-1 hidden">
            <!-- Remote videos will be added here dynamically -->
        </div>
        
        <div class='border-corners'>
            <!-- Decorative corner elements -->
            <div class="absolute top-2 left-2 w-4 h-4 border-l-2 border-t-2 border-{{ $isHost ? 'emerald' : 'amber' }}-400/50"></div>
            <div class="absolute top-2 right-2 w-4 h-4 border-r-2 border-t-2 border-{{ $isHost ? 'emerald' : 'amber' }}-400/50"></div>
            <div class="absolute bottom-2 left-2 w-4 h-4 border-l-2 border-b-2 border-{{ $isHost ? 'emerald' : 'amber' }}-400/50"></div>
            <div class="absolute bottom-2 right-2 w-4 h-4 border-r-2 border-b-2 border-{{ $isHost ? 'emerald' : 'amber' }}-400/50"></div>
        </div>
        
        <!-- Fear and Countdown Trackers (visible to all when GM is actively joined) -->
        <div class="game-state-overlay hidden absolute inset-0 pointer-events-none" data-game-state-overlay>
            <!-- Fear Tracker (Bottom Right) -->
            <div class="absolute bottom-2 right-2 w-12 h-16 flex items-center justify-center">
                <div class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="w-12 h-16 text-slate-800 fill-current">
                        <path fill-rule="evenodd" d="m411.677 395.157 36.615-276.404L359.842 0H148.431L59.979 118.753l36.615 276.404H56.929V512h398.142V395.157h-43.394zM91.081 126.836l72.292-97.064h181.526l72.292 97.064-35.539 268.321H126.627L91.081 126.836zm-4.38 298.084h338.598v57.317H86.701V424.92z"/>
                        <path fill="#37474F" d="m91.081 126.836 72.292-97.064h181.526l72.292 97.064-35.539 268.321H126.627L91.081 126.836zM86.701 424.92h338.598v57.317H86.701z"/>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="text-white font-bold text-xl -mt-1" data-fear-display="indicator">0</div>
                    </div>
                </div>
            </div>

            <!-- Countdown Trackers (Top Right) -->
            <div class="absolute top-2 right-2 max-w-[200px]">
                <div class="flex flex-col space-y-2" data-countdown-display="container">
                    <!-- Countdown trackers will be dynamically inserted here by JavaScript -->
                </div>
            </div>
        </div>

        <!-- Character Sheet Overlay (hidden by default, shown when user joins this slot) -->
        <div class="character-overlay hidden absolute inset-0 pointer-events-none" data-participant-id="{{ $participant ? $participant->id : '' }}" data-participant-name="{{ $participant ? ($participant->character ? $participant->character->name : ($participant->character_name ?: ($participant->user ? $participant->user->username : 'Anonymous'))) : '' }}">
            <!-- Character Name Panel (Bottom Left) -->
            <div class="absolute bottom-2 left-0 w-auto pointer-events-auto overflow-visible">
                <div class="relative h-[54px] min-w-[45%]">
                    <!-- Banner Background -->
                    <div class="absolute right-0 h-full z-1" style="transform: translateX(68px); top: -0.5px;">
                        <x-banner.right style="width: 72px; height: 55px;" />
                    </div>
                    
                    <!-- Main Content Container -->
                    <div class="relative z-10 bg-daggerheart-blue border border-daggerheart-gold border-l-0 border-r-0 h-full flex items-center">
                        <!-- Character Banner Container (always present for dynamic banner injection) -->
                        <div class="character-banner-container absolute scale-80 -top-2 -left-2 w-8 h-16 rounded overflow-visible">
                            <!-- Banner will be dynamically injected here by JavaScript -->
                        </div>
                        
                        <!-- Character Info -->
                        <div class="character-info text-left px-3 py-3">
                            <div class="character-name font-fantasy text-amber-300 text-lg tracking-wide leading-tight">
                                <!-- Name will be populated by JavaScript -->
                            </div>
                            <div class="text-xs text-gray-400 flex gap-1 flex-wrap">
                                <span class="character-class uppercase tracking-wide">
                                    <!-- Class will be populated by JavaScript -->
                                </span>
                                <span class="character-subclass-separator hidden">/</span>
                                <span class="character-subclass uppercase tracking-wide hidden">
                                    <!-- Subclass will be populated by JavaScript -->
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Video Controls (Top Right) - Always present, shown/hidden by JavaScript based on slot occupancy -->
            <div class="video-controls absolute top-2 right-2 pointer-events-auto gap-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200 hidden">
                <!-- Refresh Connection Button - Always available when slot is occupied -->
                <button 
                    class="refresh-connection-btn bg-blue-600/90 hover:bg-blue-500 text-white text-xs px-2 py-1 rounded transition-all duration-200 flex items-center gap-1 shadow-lg backdrop-blur-sm"
                    data-peer-id="{{ $participant->peer_id ?? '' }}"
                    data-participant-name="{{ $participant ? ($participant->character ? $participant->character->name : ($participant->character_name ?: ($participant->user ? $participant->user->username : 'Unknown'))) : '' }}"
                    title="Refresh video connection">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span class="hidden sm:inline">Refresh</span>
                </button>

                <!-- GM Kick Button - Only visible to room creator when someone else is in the slot -->
                @if($userIsCreator)
                <button 
                    class="kick-participant-btn bg-red-600/90 hover:bg-red-500 text-white text-xs px-2 py-1 rounded transition-all duration-200 flex items-center gap-1 shadow-lg backdrop-blur-sm {{ !$participant ? 'hidden' : '' }}"
                    data-participant-id="{{ $participant->id ?? '' }}"
                    data-participant-name="{{ $participant ? ($participant->character ? $participant->character->name : ($participant->character_name ?: ($participant->user ? $participant->user->username : 'Unknown'))) : '' }}"
                    title="Kick participant from room">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <span class="hidden sm:inline">Kick</span>
                </button>
                @endif
            </div>
        </div>
        
        <!-- ALL POSSIBLE SLOT STATES (shown/hidden dynamically by JavaScript) -->
        
        <!-- State 1: GM Reserved Slot (for non-GM users) - Hidden in viewer mode -->
        <div class="slot-state slot-gm-reserved {{ $viewerMode ? 'hidden' : ($isGmReservedSlot && !$userIsCreator ? '' : 'hidden') }} text-center text-slate-500">
            <svg class="w-12 h-12 mx-auto mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            <p class="text-sm font-semibold opacity-75">Reserved for GM</p>
        </div>

        <!-- State 2: GM Join Button (for GM users) - Hidden in viewer mode -->
        <button class="slot-state slot-gm-join {{ $viewerMode ? 'hidden' : ($isGmReservedSlot && $userIsCreator ? '' : 'hidden') }} join-btn bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-black font-bold py-3 px-6 rounded-lg text-lg transition-all duration-300 shadow-lg hover:shadow-emerald-500/50 transform hover:scale-105">
            <span class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2L13.5 8.5L20 10L13.5 11.5L12 18L10.5 11.5L4 10L10.5 8.5L12 2Z"/>
                </svg>
                Join Room
            </span>
        </button>

        <!-- State 3: Player Join Button (for non-GM slots, when user can join) - Hidden in viewer mode -->
        <button class="slot-state slot-player-join {{ $viewerMode ? 'hidden' : (!$isGmReservedSlot && !$userIsCreator ? '' : 'hidden') }} join-btn player-join-btn bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-black font-bold py-3 px-6 rounded-lg text-lg transition-all duration-300 shadow-lg hover:shadow-amber-500/50 transform hover:scale-105">
            <span class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2L13.5 8.5L20 10L13.5 11.5L12 18L10.5 11.5L4 10L10.5 8.5L12 2Z"/>
                </svg>
                Join
            </span>
        </button>

        <!-- State 4: Waiting for Player (for empty non-GM slots, shown when no join button is available) - Hidden in viewer mode -->
        <div class="slot-state slot-waiting {{ $viewerMode ? 'hidden' : (!$isGmReservedSlot && $userIsCreator ? '' : 'hidden') }} text-center text-slate-400">
            <svg class="w-12 h-12 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <p class="text-sm font-semibold opacity-75">Waiting for Player</p>
        </div>

        <!-- State 5: Viewer Mode - Empty Slot Display -->
        <div class="slot-state slot-viewer-empty {{ $viewerMode && !$participant ? '' : 'hidden' }} text-center text-slate-500">
            <svg class="w-12 h-12 mx-auto mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <p class="text-sm font-semibold opacity-75">{{ $isGmReservedSlot ? 'GM Slot' : 'Player Slot' }}</p>
        </div>

        
        
        <div class="loading-spinner hidden absolute inset-0 items-center justify-center bg-black bg-opacity-75">
            <div class="flex flex-col items-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-{{ $isHost ? 'emerald' : 'amber' }}-400"></div>
                <p class="text-{{ $isHost ? 'emerald' : 'amber' }}-300 mt-3 text-sm">Joining...</p>
            </div>
        </div>
    </div>
</div>
