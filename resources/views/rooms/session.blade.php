<x-layout.minimal>
    <!-- Loading Screen -->
    <div id="room-loading-screen" class="fixed inset-0 bg-slate-950 z-50 flex items-center justify-center">
        <div class="text-center">
            <div class="mb-6">
                <div class="w-16 h-16 mx-auto text-amber-500 animate-pulse">
                    <x-icons.door class="w-full h-full" />
                </div>
            </div>
            <h2 class="text-2xl font-outfit font-bold text-slate-100 mb-2">Loading Room</h2>
            <p class="text-slate-400 mb-4">Initializing dice system and WebRTC...</p>
            <div class="flex items-center justify-center space-x-2">
                <div class="w-2 h-2 bg-amber-500 rounded-full animate-bounce"></div>
                <div class="w-2 h-2 bg-amber-500 rounded-full animate-bounce" style="animation-delay: 0.1s;"></div>
                <div class="w-2 h-2 bg-amber-500 rounded-full animate-bounce" style="animation-delay: 0.2s;"></div>
            </div>
        </div>
    </div>

    <!-- Main Room Content (hidden during loading) -->
    <div id="room-main-content" class="opacity-0 transition-opacity duration-500">
    @if($room->campaign_id)
        <!-- Campaign Room Layout with Sidebar -->
        <div x-data="{ sidebarVisible: true }" class="h-screen w-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-800 flex flex-col">
            
            <!-- Main Content Area (Sidebar + Video) -->
            <div class="flex-1 flex overflow-hidden">
                <!-- Left Sidebar (1/3 width on smaller screens, 1/4 width on xl screens when visible) -->
                <div x-show="sidebarVisible" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="-translate-x-full"
                     x-transition:enter-end="translate-x-0"
                     x-transition:leave="transition ease-in duration-300"
                     x-transition:leave-start="translate-x-0"
                     x-transition:leave-end="-translate-x-full"
                     class="w-1/3 xl:w-1/4 bg-slate-950/90 backdrop-blur-xl border-r border-slate-700/50 overflow-y-auto">
                    
                    @if($user_is_creator)
                        <!-- GM Sidebar -->
                        <livewire:room-sidebar.gm-sidebar 
                            :room="$room"
                            :campaign="$campaign"
                            :campaign-pages="$campaign_pages"
                            :campaign-handouts="$campaign_handouts"
                            :participants="$participants" />
                    @else
                        <!-- Player Sidebar -->
                        <livewire:room-sidebar.player-sidebar 
                            :current-participant="$current_participant"
                            :campaign="$campaign"
                            :campaign-handouts="$campaign_handouts" />
                    @endif
                </div>
                
                <!-- Right Video Area (2/3 width when sidebar visible on smaller screens, 3/4 on xl screens, full width when hidden) -->
                <div :class="sidebarVisible ? 'w-2/3 xl:w-3/4' : 'w-full'" 
                     class="relative transition-all duration-300 p-1 flex-1">
                
                
                <!-- Video Grid Layout -->
                <div class="h-full w-full">
                    @if($room->getTotalCapacity() == 1)
                        <x-room-layout.single :participants="$participants" :room="$room" :userIsCreator="$user_is_creator" />
                    @elseif($room->getTotalCapacity() == 2)
                        <x-room-layout.dual :participants="$participants" :room="$room" :userIsCreator="$user_is_creator" />
                    @elseif($room->getTotalCapacity() == 3)
                        <x-room-layout.triangle :participants="$participants" :room="$room" :userIsCreator="$user_is_creator" />
                    @elseif($room->getTotalCapacity() == 4)
                        <x-room-layout.quad :participants="$participants" :room="$room" :userIsCreator="$user_is_creator" />
                    @elseif($room->getTotalCapacity() == 5)
                        <x-room-layout.penta :participants="$participants" :room="$room" :userIsCreator="$user_is_creator" />
                    @elseif($room->getTotalCapacity() == 6)
                        <x-room-layout.grid :participants="$participants" :room="$room" :userIsCreator="$user_is_creator" />
                    @else
                        {{-- Fallback for 7+ participants (shouldn't happen with current validation) --}}
                        <x-room-layout.grid :participants="$participants" :room="$room" :userIsCreator="$user_is_creator" />
                    @endif
                </div>
            </div>
            </div>
            
            <!-- Status Bar (Campaign Layout) - Always Visible -->
            <div id="status-bar" class="bg-slate-900/95 backdrop-blur-sm border-t border-slate-700 p-3 w-full">
                <div class="flex items-center justify-between text-sm px-4">
                    <!-- Recording Status (Hidden when not recording) -->
                    <div id="recording-status" class="hidden items-center space-x-3">
                        <div class="flex items-center space-x-2">
                            <div id="recording-indicator" class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
                            <span class="text-white font-medium text-xs">Recording</span>
                        </div>
                        <div class="text-slate-400 text-xs">
                            <span id="recording-duration">00:00</span> • 
                            <span id="recording-chunks">0 segments</span>
                        </div>
                    </div>

                    <!-- Room Info (Shown when not recording) -->
                    <div id="room-info" class="flex items-center space-x-3 text-slate-400 text-xs">
                        <span>{{ $room->name }}</span>
                        <span>•</span>
                        @if($room->isCreator(auth()->user()))
                            <button onclick="toggleParticipantsModal()" class="text-slate-400 hover:text-slate-300 cursor-pointer transition-colors">
                                {{ $participants->count() }}/{{ $room->getTotalCapacity() }} participants
                                <svg class="w-3 h-3 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        @else
                            <span>{{ $participants->count() }}/{{ $room->getTotalCapacity() }} participants</span>
                        @endif
                    </div>

                    <!-- Controls -->
                    <div class="flex items-center space-x-2">
                        <!-- Sidebar Toggle Button -->
                        <button @click="sidebarVisible = !sidebarVisible"
                                class="px-2 py-1 bg-slate-700 hover:bg-slate-600 text-white rounded text-xs transition-colors flex items-center">
                            <svg x-show="sidebarVisible" class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                            </svg>
                            <svg x-show="!sidebarVisible" class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                            </svg>
                            <span x-show="sidebarVisible">Hide Sidebar</span>
                            <span x-show="!sidebarVisible">Show Sidebar</span>
                        </button>

                        <!-- Microphone Toggle Button -->
                        <button id="mic-toggle-btn" class="px-2 py-1 bg-slate-700 hover:bg-slate-600 text-white rounded text-xs transition-colors flex items-center">
                            <svg id="mic-on-icon" class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                            </svg>
                            <svg id="mic-off-icon" class="w-3 h-3 mr-1 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 5.586A2 2 0 015 7v3a9 9 0 0018 0V7a2 2 0 00-2-2h-1M9 12l6 6m0 0l6 6M9 12l-6-6m6 6v3a3 3 0 01-3 3H8a3 3 0 01-3-3v-1m0 0h18" />
                            </svg>
                            <span id="mic-status-text">Mute</span>
                        </button>

                        <!-- Video Toggle Button -->
                        <button id="video-toggle-btn" class="px-2 py-1 bg-slate-700 hover:bg-slate-600 text-white rounded text-xs transition-colors flex items-center">
                            <svg id="video-on-icon" class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            <svg id="video-off-icon" class="w-3 h-3 mr-1 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728" />
                            </svg>
                            <span id="video-status-text">Hide Video</span>
                        </button>

                        <!-- Add Marker Button (only shown when STT or recording enabled) -->
                        @if(($room->recordingSettings && $room->recordingSettings->isSttEnabled()) || ($room->recordingSettings && $room->recordingSettings->isRecordingEnabled()))
                        <div class="relative">
                            <button id="add-marker-btn" class="px-2 py-1 bg-amber-600 hover:bg-amber-500 text-white rounded text-xs transition-colors flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Add Marker
                            </button>
                            
                            <!-- Marker Popup (hidden by default) -->
                            <div id="marker-popup" class="absolute bottom-full mb-2 left-0 bg-slate-800/95 backdrop-blur-xl border border-slate-600/50 rounded-lg shadow-xl p-3 min-w-64 hidden z-50">
                                <div class="text-white text-sm font-medium mb-3">Add Session Marker</div>
                                
                                <!-- Preset Options -->
                                <div class="space-y-2 mb-3">
                                    <button class="marker-preset-btn w-full text-left px-2 py-1 bg-slate-700 hover:bg-slate-600 text-white rounded text-xs transition-colors" data-identifier="Session Start">
                                        Session Start
                                    </button>
                                    <button class="marker-preset-btn w-full text-left px-2 py-1 bg-slate-700 hover:bg-slate-600 text-white rounded text-xs transition-colors" data-identifier="Session Stop">
                                        Session Stop
                                    </button>
                                    <button class="marker-preset-btn w-full text-left px-2 py-1 bg-slate-700 hover:bg-slate-600 text-white rounded text-xs transition-colors" data-identifier="Break Start">
                                        Break Start
                                    </button>
                                    <button class="marker-preset-btn w-full text-left px-2 py-1 bg-slate-700 hover:bg-slate-600 text-white rounded text-xs transition-colors" data-identifier="Break Stop">
                                        Break Stop
                                    </button>
                                </div>
                                
                                <!-- Custom Input -->
                                <div class="mb-3">
                                    <label for="custom-marker-input" class="block text-slate-300 text-xs mb-1">Custom Identifier:</label>
                                    <input type="text" id="custom-marker-input" class="w-full px-2 py-1 bg-slate-700 border border-slate-600 text-white text-xs rounded focus:outline-none focus:ring-2 focus:ring-amber-500" placeholder="Enter custom marker..." maxlength="255">
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="flex space-x-2">
                                    <button id="create-marker-btn" class="flex-1 px-2 py-1 bg-amber-600 hover:bg-amber-500 text-white rounded text-xs transition-colors">
                                        Create Marker
                                    </button>
                                    <button id="cancel-marker-btn" class="px-2 py-1 bg-slate-600 hover:bg-slate-500 text-white rounded text-xs transition-colors">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <!-- Recording Controls (Hidden when not recording) -->
                        <div id="recording-controls" class="hidden items-center space-x-2">
                            @if($room->recordingSettings && $room->recordingSettings->isSttEnabled())
                                <button id="view-transcript-btn" class="px-2 py-1 bg-slate-700 hover:bg-slate-600 text-white rounded text-xs transition-colors">
                                    <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Transcript
                                </button>
                            @endif
                        </div>
                        
                        <!-- Always Visible Leave Button -->
                        <button id="leave-room-btn" class="px-2 py-1 bg-slate-700 hover:bg-slate-600 text-white rounded text-xs transition-colors">
                            <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Leave Room
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Normal Room Layout (no sidebar) -->
        <div class="h-screen w-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-800 flex flex-col">
            <!-- Main Video Area -->
            <div class="flex-1 p-1">
                <!-- Dynamic Grid Layout Based on Total Capacity (Creator + Guests) -->
                @if($room->getTotalCapacity() == 1)
                    <x-room-layout.single :participants="$participants" :room="$room" :userIsCreator="$user_is_creator" />
                @elseif($room->getTotalCapacity() == 2)
                    <x-room-layout.dual :participants="$participants" :room="$room" :userIsCreator="$user_is_creator" />
                @elseif($room->getTotalCapacity() == 3)
                    <x-room-layout.triangle :participants="$participants" :room="$room" :userIsCreator="$user_is_creator" />
                @elseif($room->getTotalCapacity() == 4)
                    <x-room-layout.quad :participants="$participants" :room="$room" :userIsCreator="$user_is_creator" />
                @else
                    <x-room-layout.grid :participants="$participants" :room="$room" :userIsCreator="$user_is_creator" />
                @endif
            </div>
            
            <!-- Status Bar (Normal Layout) - Always Visible -->
            <div id="status-bar-normal" class="bg-slate-900/95 backdrop-blur-sm border-t border-slate-700 p-3 w-full">
                <div class="flex items-center justify-between text-sm px-4">
                    <!-- Recording Status (Hidden when not recording) -->
                    <div id="recording-status-normal" class="hidden items-center space-x-3">
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
                            <span class="text-white font-medium text-xs">Recording</span>
                        </div>
                        <div class="text-slate-400 text-xs">
                            <span id="recording-duration-normal">00:00</span> • 
                            <span id="recording-chunks-normal">0 segments</span>
                        </div>
                    </div>

                    <!-- Room Info (Shown when not recording) -->
                    <div id="room-info-normal" class="flex items-center space-x-3 text-slate-400 text-xs">
                        <span>{{ $room->name }}</span>
                        <span>•</span>
                        @if($room->isCreator(auth()->user()))
                            <button onclick="toggleParticipantsModal()" class="text-slate-400 hover:text-slate-300 cursor-pointer transition-colors">
                                {{ $participants->count() }}/{{ $room->getTotalCapacity() }} participants
                                <svg class="w-3 h-3 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        @else
                            <span>{{ $participants->count() }}/{{ $room->getTotalCapacity() }} participants</span>
                        @endif
                    </div>

                    <!-- Controls -->
                    <div class="flex items-center space-x-2">
                        <!-- Microphone Toggle Button -->
                        <button id="mic-toggle-btn-normal" class="px-2 py-1 bg-slate-700 hover:bg-slate-600 text-white rounded text-xs transition-colors flex items-center">
                            <svg id="mic-on-icon-normal" class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                            </svg>
                            <svg id="mic-off-icon-normal" class="w-3 h-3 mr-1 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 5.586A2 2 0 015 7v3a9 9 0 0018 0V7a2 2 0 00-2-2h-1M9 12l6 6m0 0l6 6M9 12l-6-6m6 6v3a3 3 0 01-3 3H8a3 3 0 01-3-3v-1m0 0h18" />
                            </svg>
                            <span id="mic-status-text-normal">Mute</span>
                        </button>

                        <!-- Video Toggle Button -->
                        <button id="video-toggle-btn-normal" class="px-2 py-1 bg-slate-700 hover:bg-slate-600 text-white rounded text-xs transition-colors flex items-center">
                            <svg id="video-on-icon-normal" class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            <svg id="video-off-icon-normal" class="w-3 h-3 mr-1 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728" />
                            </svg>
                            <span id="video-status-text-normal">Hide Video</span>
                        </button>

                        <!-- Add Marker Button (only shown when STT or recording enabled) -->
                        @if(($room->recordingSettings && $room->recordingSettings->isSttEnabled()) || ($room->recordingSettings && $room->recordingSettings->isRecordingEnabled()))
                        <div class="relative">
                            <button id="add-marker-btn-normal" class="px-2 py-1 bg-amber-600 hover:bg-amber-500 text-white rounded text-xs transition-colors flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Add Marker
                            </button>
                            
                            <!-- Marker Popup (hidden by default) -->
                            <div id="marker-popup-normal" class="absolute bottom-full mb-2 left-0 bg-slate-800/95 backdrop-blur-xl border border-slate-600/50 rounded-lg shadow-xl p-3 min-w-64 hidden z-50">
                                <div class="text-white text-sm font-medium mb-3">Add Session Marker</div>
                                
                                <!-- Preset Options -->
                                <div class="space-y-2 mb-3">
                                    <button class="marker-preset-btn-normal w-full text-left px-2 py-1 bg-slate-700 hover:bg-slate-600 text-white rounded text-xs transition-colors" data-identifier="Session Start">
                                        Session Start
                                    </button>
                                    <button class="marker-preset-btn-normal w-full text-left px-2 py-1 bg-slate-700 hover:bg-slate-600 text-white rounded text-xs transition-colors" data-identifier="Session Stop">
                                        Session Stop
                                    </button>
                                    <button class="marker-preset-btn-normal w-full text-left px-2 py-1 bg-slate-700 hover:bg-slate-600 text-white rounded text-xs transition-colors" data-identifier="Break Start">
                                        Break Start
                                    </button>
                                    <button class="marker-preset-btn-normal w-full text-left px-2 py-1 bg-slate-700 hover:bg-slate-600 text-white rounded text-xs transition-colors" data-identifier="Break Stop">
                                        Break Stop
                                    </button>
                                </div>
                                
                                <!-- Custom Input -->
                                <div class="mb-3">
                                    <label for="custom-marker-input-normal" class="block text-slate-300 text-xs mb-1">Custom Identifier:</label>
                                    <input type="text" id="custom-marker-input-normal" class="w-full px-2 py-1 bg-slate-700 border border-slate-600 text-white text-xs rounded focus:outline-none focus:ring-2 focus:ring-amber-500" placeholder="Enter custom marker..." maxlength="255">
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="flex space-x-2">
                                    <button id="create-marker-btn-normal" class="flex-1 px-2 py-1 bg-amber-600 hover:bg-amber-500 text-white rounded text-xs transition-colors">
                                        Create Marker
                                    </button>
                                    <button id="cancel-marker-btn-normal" class="px-2 py-1 bg-slate-600 hover:bg-slate-500 text-white rounded text-xs transition-colors">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <!-- Recording Controls (Hidden when not recording) -->
                        <div id="recording-controls-normal" class="hidden items-center space-x-2">
                            @if($room->recordingSettings && $room->recordingSettings->isSttEnabled())
                                <button id="view-transcript-btn-normal" class="px-2 py-1 bg-slate-700 hover:bg-slate-600 text-white rounded text-xs transition-colors">
                                    <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Transcript
                                </button>
                            @endif
                        </div>
                        
                        <!-- Always Visible Leave Button -->
                        <button id="leave-room-btn-normal" class="px-2 py-1 bg-slate-700 hover:bg-slate-600 text-white rounded text-xs transition-colors">
                            <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Leave Room
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
    </div> <!-- End of room-main-content -->

    <!-- Hidden class banners for dynamic copying -->
    <div id="hidden-class-banners" class="hidden">
        @foreach(\Domain\Character\Enums\ClassEnum::cases() as $className)
            <div data-class="{{ $className }}" class="hidden-banner-{{ $className }}">
                <x-class-banner className="{{ $className }}" size="xs" />
            </div>
        @endforeach
    </div>

    <!-- Leaving Room Modal (non-dismissible) -->
    <div id="leavingRoomModal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-[60]" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-slate-900/95 backdrop-blur-xl border border-amber-500/50 rounded-xl shadow-2xl w-96 max-w-full mx-4 p-8">
                <div class="text-center">
                    <!-- Animated spinner -->
                    <div class="mb-6">
                        <div class="relative inline-flex">
                            <div class="w-16 h-16 border-4 border-slate-700 border-t-amber-500 rounded-full animate-spin"></div>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <svg class="w-8 h-8 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Title -->
                    <h3 class="text-xl font-outfit font-bold text-white mb-3">Leaving Room</h3>
                    
                    <!-- Status messages -->
                    <div class="space-y-2 text-sm text-slate-300">
                        <p id="leaving-status-main" class="font-medium">Finalizing recording...</p>
                        <p id="leaving-status-sub" class="text-xs text-slate-400">Please wait, this will only take a moment</p>
                    </div>
                    
                    <!-- Progress indicators (optional) -->
                    <div class="mt-6 flex justify-center space-x-2">
                        <div class="w-2 h-2 bg-amber-500 rounded-full animate-pulse"></div>
                        <div class="w-2 h-2 bg-amber-500 rounded-full animate-pulse" style="animation-delay: 0.2s;"></div>
                        <div class="w-2 h-2 bg-amber-500 rounded-full animate-pulse" style="animation-delay: 0.4s;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($room->isCreator(auth()->user()))
        <!-- Participants Management Modal -->
        <div id="participantsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-slate-900/95 backdrop-blur-xl border border-slate-700/50 rounded-xl shadow-xl w-96 max-w-full mx-4">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-white font-semibold text-lg flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                            </svg>
                            Manage Participants
                        </h4>
                        <button onclick="toggleParticipantsModal()" class="text-slate-400 hover:text-slate-300 p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="space-y-3 max-h-80 overflow-y-auto">
                        @foreach($participants as $participant)
                            <div class="flex items-center justify-between p-3 bg-slate-800/50 rounded-lg">
                                <div class="flex-1">
                                    <div class="text-white text-sm font-medium">
                                        @if($participant->character)
                                            {{ $participant->character->name }}
                                        @elseif($participant->character_name)
                                            {{ $participant->character_name }}
                                        @else
                                            {{ $participant->user ? $participant->user->username : 'Anonymous' }}
                                        @endif
                                        @if($participant->user_id === $room->creator_id)
                                            <span class="text-amber-400 text-xs ml-1">(Host)</span>
                                        @endif
                                    </div>
                                    <div class="text-slate-400 text-xs">
                                        @if($participant->character_class)
                                            {{ $participant->character_class }}
                                        @elseif($participant->character)
                                            {{ $participant->character->class }}
                                        @elseif($participant->user_id === $room->creator_id)
                                            GM
                                        @endif
                                        • {{ $participant->user ? $participant->user->username : 'Anonymous' }}
                                    </div>
                                </div>
                                @if($participant->user_id !== $room->creator_id)
                                    <form action="{{ route('rooms.kick', [$room, $participant->id]) }}" method="POST" onsubmit="return confirm('Remove {{ e($participant->character_name ?: ($participant->user ? $participant->user->username : 'this participant')) }} from the room?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-300 hover:bg-red-500/10 p-2 rounded transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            </div>
        </div>
    @endif

    <!-- Room Data Initialization -->
    <script>
        // Pass room and participant data to JavaScript
        window.roomData = {
            id: {{ $room->id }},
            name: @json($room->name),
            invite_code: @json($room->invite_code),
            creator_id: {{ $room->creator_id }},
            campaign_id: {{ $room->campaign_id ?? 'null' }},
            guest_count: {{ $room->guest_count }},
            total_capacity: {{ $room->getTotalCapacity() }},
            stt_enabled: {{ ($room->recordingSettings && $room->recordingSettings->isSttEnabled()) ? 'true' : 'false' }},
            stt_provider: @json($room->recordingSettings ? ($room->recordingSettings->stt_provider ?? 'browser') : 'browser'),
            stt_account_id: {{ $room->recordingSettings && $room->recordingSettings->stt_account_id ? $room->recordingSettings->stt_account_id : 'null' }},
            recording_enabled: {{ ($room->recordingSettings && $room->recordingSettings->isRecordingEnabled()) ? 'true' : 'false' }},
            recording_settings: {
                storage_provider: @json($room->recordingSettings ? $room->recordingSettings->storage_provider : 'local_device'),
                stt_enabled: {{ ($room->recordingSettings && $room->recordingSettings->isSttEnabled()) ? 'true' : 'false' }},
                recording_enabled: {{ ($room->recordingSettings && $room->recordingSettings->isRecordingEnabled()) ? 'true' : 'false' }}
            },
            participants: [
                @foreach($participants as $p)
                {
                    user_id: {{ $p->user_id ?? 'null' }},
                    username: @json($p->user ? $p->user->username : 'Unknown'),
                    character_name: @json($p->character ? $p->character->name : ($p->character_name ?? ($p->user ? $p->user->username : 'Unknown'))),
                    character_class: @json($p->character ? $p->character->class : ($p->character_class ?? 'Unknown')),
                    character_subclass: @json($p->character ? $p->character->subclass : ''),
                    character_ancestry: @json($p->character ? $p->character->ancestry : ''),
                    character_community: @json($p->character ? $p->character->community : ''),
                    is_host: {{ $p->user_id === $room->creator_id ? 'true' : 'false' }}
                }{{ !$loop->last ? ',' : '' }}
                @endforeach
            ]
        };
        
        // Pass current user ID for participant identification
        window.currentUserId = {{ auth()->id() ?? 'null' }};
        
        // Pass initial game state data
        @php
            $gameStateAction = new \Domain\Room\Actions\GetGameStateAction();
            $gameState = $gameStateAction->execute($room);
        @endphp
        window.roomData.game_state = {
            fear_tracker: {
                fear_level: {{ $gameState->fear_tracker->fear_level }},
                can_increase: {{ $gameState->fear_tracker->can_increase ? 'true' : 'false' }},
                can_decrease: {{ $gameState->fear_tracker->can_decrease ? 'true' : 'false' }}
            },
            countdown_trackers: [
                @foreach($gameState->countdown_trackers as $tracker)
                {
                    id: "{{ $tracker->id }}",
                    name: "{{ $tracker->name }}",
                    value: {{ $tracker->value }},
                    updated_at: "{{ $tracker->updated_at->toISOString() }}",
                    can_increase: {{ $tracker->can_increase ? 'true' : 'false' }},
                    can_decrease: {{ $tracker->can_decrease ? 'true' : 'false' }}
                }{{ !$loop->last ? ',' : '' }}
                @endforeach
            ],
            source_type: "{{ $gameState->source_type }}",
            source_id: {{ $gameState->source_id }}
        };
        
        // Initialize room session with the RoomSessionInitializer
        // Wait for all scripts to load before initializing
        function initializeRoomSession() {
            if (window.RoomSessionInitializer) {
                window.roomSessionInitializer = new window.RoomSessionInitializer(
                    window.roomData, 
                    window.currentUserId
                );
            } else {
                console.error('❌ RoomSessionInitializer not loaded. Please refresh the page.');
            }
        }
        
        // If DOMContentLoaded has already fired (script is deferred), initialize immediately
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeRoomSession);
        } else {
            // DOM is already ready, but wait a tick to ensure Vite scripts have executed
            setTimeout(initializeRoomSession, 0);
        }
    </script>

    <!-- DICE CONTAINER -->
    <div id="dice-container" class="fixed inset-0" style="pointer-events: none; width: 100vw; height: 100vh; z-index: 9999;">
        <!-- Canvas will be inserted here by dice-box -->
    </div>
    
    <style>
        #dice-container canvas {
            width: 100vw !important;
            height: 100vh !important;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            pointer-events: none !important;
            z-index: 9999 !important;
        }
    </style>

    <!-- FLOATING DICE SELECTOR -->
    <x-dice-selector />

</x-layout.minimal>

