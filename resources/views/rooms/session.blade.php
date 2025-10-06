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
                            
                            @if($room->recordingSettings && $room->recordingSettings->storage_provider === 'local_device')
                                <button id="stop-recording-btn" class="px-2 py-1 bg-red-600 hover:bg-red-500 text-white rounded text-xs transition-colors">
                                    <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />
                                    </svg>
                                    Stop and Save Recording
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
                            
                            @if($room->recordingSettings && $room->recordingSettings->storage_provider === 'local_device')
                                <button id="stop-recording-btn-normal" class="px-2 py-1 bg-red-600 hover:bg-red-500 text-white rounded text-xs transition-colors">
                                    <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />
                                    </svg>
                                    Stop and Save Recording
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

    <!-- Include our Room WebRTC JavaScript with room context -->
    <script>
        // Pass room and participant data to the WebRTC script
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
        
        function toggleParticipantsModal() {
            const modal = document.getElementById('participantsModal');
            if (modal.classList.contains('hidden')) {
                modal.classList.remove('hidden');
                modal.style.display = 'block';
            } else {
                modal.classList.add('hidden');
                modal.style.display = 'none';
            }
        }
        
        // Leaving room modal helpers
        window.showLeavingModal = function(statusText = 'Finalizing recording...') {
            const modal = document.getElementById('leavingRoomModal');
            const statusMain = document.getElementById('leaving-status-main');
            
            if (statusMain) {
                statusMain.textContent = statusText;
            }
            
            if (modal) {
                modal.classList.remove('hidden');
                modal.style.display = 'block';
            }
        };
        
        window.updateLeavingModalStatus = function(mainText, subText = null) {
            const statusMain = document.getElementById('leaving-status-main');
            const statusSub = document.getElementById('leaving-status-sub');
            
            if (statusMain && mainText) {
                statusMain.textContent = mainText;
            }
            
            if (statusSub && subText) {
                statusSub.textContent = subText;
            }
        };
        
        window.hideLeavingModal = function() {
            const modal = document.getElementById('leavingRoomModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.style.display = 'none';
            }
        };
        
        // Make UIStateManager methods available globally for WebRTC integration
        window.showNameplateForSlot = function(slotId, participantData) {
            if (window.roomWebRTC && window.roomWebRTC.uiStateManager) {
                window.roomWebRTC.uiStateManager.showNameplateForSlot(slotId, participantData);
            }
        };
        
        window.hideNameplateForSlot = function(slotId) {
            if (window.roomWebRTC && window.roomWebRTC.uiStateManager) {
                window.roomWebRTC.uiStateManager.hideNameplateForSlot(slotId);
            }
        };
        
        window.setSlotState = function(slotId, state) {
            if (window.roomWebRTC && window.roomWebRTC.uiStateManager) {
                window.roomWebRTC.uiStateManager.setSlotState(slotId, state);
            }
        };
        
        window.setSlotToOccupied = function(slotId, participantData) {
            if (window.roomWebRTC && window.roomWebRTC.uiStateManager) {
                window.roomWebRTC.uiStateManager.setSlotToOccupied(slotId, participantData);
            }
        };
        
        // Handle when other participants join via Ably
        window.handleRemoteParticipantJoin = function(slotId, participantData) {
            if (window.roomWebRTC && window.roomWebRTC.uiStateManager) {
                window.roomWebRTC.uiStateManager.handleRemoteParticipantJoin(slotId, participantData);
            }
        };
        
        // Handle when participants leave via Ably  
        window.handleRemoteParticipantLeave = function(slotId, isGmSlot, userIsCreator) {
            if (window.roomWebRTC && window.roomWebRTC.uiStateManager) {
                window.roomWebRTC.uiStateManager.handleRemoteParticipantLeave(slotId, isGmSlot, userIsCreator);
            }
        };
        
        // Close participants modal when clicking outside
        document.addEventListener('click', function(event) {
            const modal = document.getElementById('participantsModal');
            const modalContent = event.target.closest('.bg-slate-900\\/95');
            const button = event.target.closest('[onclick="toggleParticipantsModal()"]');
            
            if (modal && !modal.classList.contains('hidden') && !modalContent && !button) {
                modal.classList.add('hidden');
                modal.style.display = 'none';
            }
        });
        
        // Microphone and Video Toggle Functions
        function toggleMicrophone() {
            if (window.roomWebRTC) {
                const isMuted = window.roomWebRTC.toggleMicrophone();
                updateMicrophoneUI(isMuted);
            }
        }
        
        function toggleVideo() {
            if (window.roomWebRTC) {
                const isVideoHidden = window.roomWebRTC.toggleVideo();
                updateVideoUI(isVideoHidden);
            }
        }
        
        function updateMicrophoneUI(isMuted) {
            // Update campaign layout buttons
            const micOnIcon = document.getElementById('mic-on-icon');
            const micOffIcon = document.getElementById('mic-off-icon');
            const micStatusText = document.getElementById('mic-status-text');
            const micToggleBtn = document.getElementById('mic-toggle-btn');
            
            // Update normal layout buttons
            const micOnIconNormal = document.getElementById('mic-on-icon-normal');
            const micOffIconNormal = document.getElementById('mic-off-icon-normal');
            const micStatusTextNormal = document.getElementById('mic-status-text-normal');
            const micToggleBtnNormal = document.getElementById('mic-toggle-btn-normal');
            
            if (isMuted) {
                // Show muted state
                if (micOnIcon) micOnIcon.classList.add('hidden');
                if (micOffIcon) micOffIcon.classList.remove('hidden');
                if (micStatusText) micStatusText.textContent = 'Unmute';
                if (micToggleBtn) micToggleBtn.classList.add('bg-red-600', 'hover:bg-red-500');
                if (micToggleBtn) micToggleBtn.classList.remove('bg-slate-700', 'hover:bg-slate-600');
                
                if (micOnIconNormal) micOnIconNormal.classList.add('hidden');
                if (micOffIconNormal) micOffIconNormal.classList.remove('hidden');
                if (micStatusTextNormal) micStatusTextNormal.textContent = 'Unmute';
                if (micToggleBtnNormal) micToggleBtnNormal.classList.add('bg-red-600', 'hover:bg-red-500');
                if (micToggleBtnNormal) micToggleBtnNormal.classList.remove('bg-slate-700', 'hover:bg-slate-600');
            } else {
                // Show unmuted state
                if (micOnIcon) micOnIcon.classList.remove('hidden');
                if (micOffIcon) micOffIcon.classList.add('hidden');
                if (micStatusText) micStatusText.textContent = 'Mute';
                if (micToggleBtn) micToggleBtn.classList.remove('bg-red-600', 'hover:bg-red-500');
                if (micToggleBtn) micToggleBtn.classList.add('bg-slate-700', 'hover:bg-slate-600');
                
                if (micOnIconNormal) micOnIconNormal.classList.remove('hidden');
                if (micOffIconNormal) micOffIconNormal.classList.add('hidden');
                if (micStatusTextNormal) micStatusTextNormal.textContent = 'Mute';
                if (micToggleBtnNormal) micToggleBtnNormal.classList.remove('bg-red-600', 'hover:bg-red-500');
                if (micToggleBtnNormal) micToggleBtnNormal.classList.add('bg-slate-700', 'hover:bg-slate-600');
            }
        }
        
        function updateVideoUI(isVideoHidden) {
            // Update campaign layout buttons
            const videoOnIcon = document.getElementById('video-on-icon');
            const videoOffIcon = document.getElementById('video-off-icon');
            const videoStatusText = document.getElementById('video-status-text');
            const videoToggleBtn = document.getElementById('video-toggle-btn');
            
            // Update normal layout buttons
            const videoOnIconNormal = document.getElementById('video-on-icon-normal');
            const videoOffIconNormal = document.getElementById('video-off-icon-normal');
            const videoStatusTextNormal = document.getElementById('video-status-text-normal');
            const videoToggleBtnNormal = document.getElementById('video-toggle-btn-normal');
            
            if (isVideoHidden) {
                // Show video hidden state
                if (videoOnIcon) videoOnIcon.classList.add('hidden');
                if (videoOffIcon) videoOffIcon.classList.remove('hidden');
                if (videoStatusText) videoStatusText.textContent = 'Show Video';
                if (videoToggleBtn) videoToggleBtn.classList.add('bg-red-600', 'hover:bg-red-500');
                if (videoToggleBtn) videoToggleBtn.classList.remove('bg-slate-700', 'hover:bg-slate-600');
                
                if (videoOnIconNormal) videoOnIconNormal.classList.add('hidden');
                if (videoOffIconNormal) videoOffIconNormal.classList.remove('hidden');
                if (videoStatusTextNormal) videoStatusTextNormal.textContent = 'Show Video';
                if (videoToggleBtnNormal) videoToggleBtnNormal.classList.add('bg-red-600', 'hover:bg-red-500');
                if (videoToggleBtnNormal) videoToggleBtnNormal.classList.remove('bg-slate-700', 'hover:bg-slate-600');
            } else {
                // Show video visible state
                if (videoOnIcon) videoOnIcon.classList.remove('hidden');
                if (videoOffIcon) videoOffIcon.classList.add('hidden');
                if (videoStatusText) videoStatusText.textContent = 'Hide Video';
                if (videoToggleBtn) videoToggleBtn.classList.remove('bg-red-600', 'hover:bg-red-500');
                if (videoToggleBtn) videoToggleBtn.classList.add('bg-slate-700', 'hover:bg-slate-600');
                
                if (videoOnIconNormal) videoOnIconNormal.classList.remove('hidden');
                if (videoOffIconNormal) videoOffIconNormal.classList.add('hidden');
                if (videoStatusTextNormal) videoStatusTextNormal.textContent = 'Hide Video';
                if (videoToggleBtnNormal) videoToggleBtnNormal.classList.remove('bg-red-600', 'hover:bg-red-500');
                if (videoToggleBtnNormal) videoToggleBtnNormal.classList.add('bg-slate-700', 'hover:bg-slate-600');
            }
        }
        
        // Add event listeners for the toggle buttons
        document.addEventListener('DOMContentLoaded', function() {
            // Campaign layout buttons
            const micToggleBtn = document.getElementById('mic-toggle-btn');
            const videoToggleBtn = document.getElementById('video-toggle-btn');
            
            // Normal layout buttons  
            const micToggleBtnNormal = document.getElementById('mic-toggle-btn-normal');
            const videoToggleBtnNormal = document.getElementById('video-toggle-btn-normal');
            
            if (micToggleBtn) {
                micToggleBtn.addEventListener('click', toggleMicrophone);
            }
            if (videoToggleBtn) {
                videoToggleBtn.addEventListener('click', toggleVideo);
            }
            if (micToggleBtnNormal) {
                micToggleBtnNormal.addEventListener('click', toggleMicrophone);
            }
            if (videoToggleBtnNormal) {
                videoToggleBtnNormal.addEventListener('click', toggleVideo);
            }
        });
        
        // Track initialization status
        let diceInitialized = false;
        let webrtcInitialized = false;
        
        function hideLoadingScreen() {
            console.log('hideLoadingScreen called - dice:', diceInitialized, 'webrtc:', webrtcInitialized);
            if (diceInitialized && webrtcInitialized) {
                const loadingScreen = document.getElementById('room-loading-screen');
                const mainContent = document.getElementById('room-main-content');
                
                console.log('Both systems ready, hiding loading screen');
                if (loadingScreen && mainContent) {
                    setTimeout(() => {
                        loadingScreen.style.opacity = '0';
                        mainContent.style.opacity = '1';
                        
                        setTimeout(() => {
                            loadingScreen.style.display = 'none';
                        }, 500);
                    }, 500); // Small delay to ensure everything is ready
                }
            }
        }
        
        // Recording validation functionality
        async function validateRecordingSession() {
            if (!window.roomData.recording_enabled) {
                console.log('🎥 Recording not enabled, skipping validation');
                return true;
            }
            
            try {
                const response = await fetch(`/api/rooms/${window.roomData.id}/recordings/validate-session`, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });
                
                if (!response.ok) {
                    console.error('🎥 Recording validation failed:', response.status);
                    return false;
                }
                
                const data = await response.json();
                console.log('🎥 Recording validation result:', data);
                
                // If recording is enabled but no entry exists, show error
                if (data.recording_enabled && !data.recording_entry_exists) {
                    showRecordingErrorModal();
                    return false;
                }
                
                return true;
            } catch (error) {
                console.error('🎥 Recording validation error:', error);
                showRecordingErrorModal();
                return false;
            }
        }
        
        function showRecordingErrorModal() {
            const modal = document.createElement('div');
            modal.id = 'recording-error-modal';
            modal.className = 'fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center';
            modal.innerHTML = `
                <div class="bg-slate-900/95 backdrop-blur-xl border border-red-500/50 rounded-xl shadow-xl max-w-md mx-4 p-6">
                    <div class="flex items-center justify-center mb-4">
                        <div class="w-12 h-12 bg-red-500/20 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-xl font-outfit font-bold text-white text-center mb-3">Recording Issue Detected</h3>
                    <p class="text-slate-300 text-center mb-6">
                        Video recording is enabled for this room, but there's an issue with your recording session. 
                        Please refresh the page to try again.
                    </p>
                    <div class="flex space-x-3">
                        <button onclick="window.location.reload()" class="flex-1 px-4 py-2 bg-amber-600 hover:bg-amber-500 text-white rounded-lg font-medium transition-colors">
                            Refresh Page
                        </button>
                        <button onclick="closeRecordingErrorModal()" class="px-4 py-2 bg-slate-600 hover:bg-slate-500 text-white rounded-lg font-medium transition-colors">
                            Continue Anyway
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }
        
        function closeRecordingErrorModal() {
            const modal = document.getElementById('recording-error-modal');
            if (modal) {
                modal.remove();
            }
        }

        // Initialize RoomWebRTC when DOM and modules are ready
        let webrtcInitAttempts = 0;
        const maxWebrtcInitAttempts = 50; // Max 5 seconds of retries
        
        async function initializeRoomWebRTC() {
            if (window.roomData && window.RoomWebRTC) {
                console.log('🚀 Starting Room WebRTC system');
                
                // Validate recording session before initializing WebRTC
                const recordingValid = await validateRecordingSession();
                if (!recordingValid) {
                    console.warn('🎥 Recording validation failed, but continuing with WebRTC initialization');
                }
                
                window.roomWebRTC = new window.RoomWebRTC(window.roomData);
                
                // Make FearCountdownManager debug methods globally accessible for testing
                window.debugFearCountdown = () => window.roomWebRTC.fearCountdownManager.debugGmPresence();
                window.forceShowGameState = () => window.roomWebRTC.fearCountdownManager.forceShowOverlays();
                window.forceHideGameState = () => window.roomWebRTC.fearCountdownManager.forceHideOverlays();
                
                // Check consent requirements immediately upon entering the room
                window.roomWebRTC.checkInitialConsentRequirements();
                
                webrtcInitialized = true;
                hideLoadingScreen();
            } else if (window.roomData && !window.RoomWebRTC && webrtcInitAttempts < maxWebrtcInitAttempts) {
                webrtcInitAttempts++;
                console.warn(`🎬 RoomWebRTC not available - attempt ${webrtcInitAttempts}/${maxWebrtcInitAttempts}`);
                // Retry after a short delay in case the bundle is still loading
                setTimeout(initializeRoomWebRTC, 100);
            } else if (webrtcInitAttempts >= maxWebrtcInitAttempts) {
                console.error('❌ Failed to initialize RoomWebRTC after maximum attempts. Please refresh the page.');
                webrtcInitialized = true; // Mark as "initialized" to allow page to show
                hideLoadingScreen();
            } else {
                console.warn('⚠️ No room data found, WebRTC not initialized');
                webrtcInitialized = true; // Mark as "initialized" to allow page to show
                hideLoadingScreen();
            }
        }
        
        // Fallback timer to ensure page shows even if initialization fails
        setTimeout(() => {
            if (!diceInitialized || !webrtcInitialized) {
                console.warn('⚠️ Forcing page display after timeout');
                diceInitialized = true;
                webrtcInitialized = true;
                hideLoadingScreen();
            }
        }, 5000); // 5 second maximum loading time
        
        // Wait for DOM and give modules time to load
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                setTimeout(initializeRoomWebRTC, 50);
            });
        } else {
            setTimeout(initializeRoomWebRTC, 50);
        }
    </script>
    
    <!-- Uppy Integration for Video Recording -->
    <script>
        // Initialize Uppy for video recording when DOM is ready
        document.addEventListener('DOMContentLoaded', () => {
            if (window.roomData && window.roomData.recording_enabled && window.RoomUppy) {
                try {
                    // Initialize Uppy with room data and recording settings
                    window.roomUppy = new window.RoomUppy(window.roomData, window.roomData.recording_settings);
                    
                    console.log('🎬 Room Uppy initialized for video recording');
                } catch (error) {
                    console.warn('🎬 Failed to initialize Uppy for video recording:', error);
                    console.warn('🎬 Falling back to direct upload method');
                }
            } else if (window.roomData && window.roomData.recording_enabled && !window.RoomUppy) {
                console.warn('🎬 RoomUppy not available - ensure it\'s included in the main bundle');
            }
        });
    </script>

    <!-- Recording Error Event Listeners -->
    <script>
        // Listen for recording upload errors
        document.addEventListener('recording-upload-error', (event) => {
            console.error('🎥 Recording upload error event received:', event.detail);
            const { filename, error, provider } = event.detail;
            
            // Show error in status bar if roomWebRTC is available
            if (window.roomWebRTC && window.roomWebRTC.statusBarManager) {
                window.roomWebRTC.statusBarManager.showUploadError(error, provider);
            } else {
                console.error('🎥 StatusBarManager not available to display error');
            }
        });

        // Listen for recording upload retries
        document.addEventListener('recording-upload-retrying', (event) => {
            console.log('🎥 Recording upload retry event received:', event.detail);
            const { retryCount, maxRetries, provider } = event.detail;
            
            // Show retry status in status bar
            if (window.roomWebRTC && window.roomWebRTC.statusBarManager) {
                window.roomWebRTC.statusBarManager.showUploadRetry(retryCount, maxRetries, provider);
            } else {
                console.warn('🎥 StatusBarManager not available to display retry status');
            }
        });

        // Listen for recording upload success (individual chunks)
        document.addEventListener('recording-upload-chunk-success', (event) => {
            console.log('🎥 Recording chunk upload success:', event.detail);
            const { provider } = event.detail;
            
            // Clear error state in status bar
            if (window.roomWebRTC && window.roomWebRTC.statusBarManager) {
                window.roomWebRTC.statusBarManager.showUploadSuccess(provider);
            }
        });

        // Listen for complete recording upload success
        document.addEventListener('recording-upload-success', (event) => {
            console.log('🎥 Recording upload completed successfully:', event.detail);
            const { recording_id, provider } = event.detail;
            
            // Clear error state and show success
            if (window.roomWebRTC && window.roomWebRTC.statusBarManager) {
                window.roomWebRTC.statusBarManager.showUploadSuccess(provider);
            }
        });
        
        console.log('🎥 Recording error event listeners registered');
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

    <!-- DICE INITIALIZATION SCRIPT -->
    <script>
        // Initialize dice system for room
        document.addEventListener('DOMContentLoaded', function() {
            // Wait a moment for other systems to load
            setTimeout(() => {
                if (typeof window.initDiceBox !== 'undefined') {
                    try {
                        window.initDiceBox('#dice-container');
                        if (typeof window.setupDiceCallbacks === 'function') {
                            window.setupDiceCallbacks((rollResult) => {
                                console.log('Room dice roll completed:', rollResult);
                            });
                        }
                        console.log('Room dice system initialized');
                        diceInitialized = true;
                        hideLoadingScreen();
                    } catch (error) {
                        console.error('Error initializing room dice system:', error);
                        diceInitialized = true; // Mark as initialized even on error
                        hideLoadingScreen();
                    }
                } else {
                    console.warn('Dice functions not available in room');
                    diceInitialized = true; // Mark as initialized even if not available
                    hideLoadingScreen();
                }
            }, 1000);
        });
    </script>
</x-layout.minimal>

