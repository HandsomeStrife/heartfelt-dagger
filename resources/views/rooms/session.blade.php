<x-layout.minimal>
    <div class="h-screen w-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-800 p-1">
        <!-- Dynamic Grid Layout Based on Total Capacity (Creator + Guests) -->
        @if($room->getTotalCapacity() == 1)
            <x-room-layout.single :participants="$participants" :room="$room" />
        @elseif($room->getTotalCapacity() == 2)
            <x-room-layout.dual :participants="$participants" :room="$room" />
        @elseif($room->getTotalCapacity() == 3)
            <x-room-layout.triangle :participants="$participants" :room="$room" />
        @elseif($room->getTotalCapacity() == 4)
            <x-room-layout.quad :participants="$participants" :room="$room" />
        @else
            <x-room-layout.grid :participants="$participants" :room="$room" />
        @endif
    </div>

    <!-- Room Controls -->
    <div class="absolute top-4 right-4 flex items-center space-x-4">
        <!-- Room Info -->
        <div data-testid="room-info" class="bg-slate-900/90 backdrop-blur-xl border border-slate-700/50 rounded-xl px-4 py-2 relative">
            <h3 data-testid="room-name" class="text-white font-semibold text-sm">{{ $room->name }}</h3>
            @if($room->isCreator(auth()->user()))
                <button onclick="toggleParticipantsList()" data-testid="participant-count" class="text-slate-400 hover:text-slate-300 text-xs cursor-pointer transition-colors">
                    {{ $participants->count() }}/{{ $room->getTotalCapacity() }} participants
                    <svg class="w-3 h-3 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
            @else
                <p data-testid="participant-count" class="text-slate-400 text-xs">{{ $participants->count() }}/{{ $room->getTotalCapacity() }} participants</p>
            @endif
            
            @if($room->isCreator(auth()->user()))
                <!-- Participants List Dropdown -->
                <div id="participantsList" class="hidden absolute top-full right-0 mt-2 w-80 bg-slate-900/95 backdrop-blur-xl border border-slate-700/50 rounded-xl shadow-xl z-50">
                    <div class="p-4">
                        <h4 class="text-white font-semibold text-sm mb-3 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                            </svg>
                            Manage Participants
                        </h4>
                        <div class="space-y-2 max-h-64 overflow-y-auto">
                            @foreach($participants as $participant)
                                <div class="flex items-center justify-between p-2 bg-slate-800/50 rounded-lg">
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
                                            @endif
                                            • {{ $participant->user ? $participant->user->username : 'Anonymous' }}
                                        </div>
                                    </div>
                                    @if($participant->user_id !== $room->creator_id)
                                        <form action="{{ route('rooms.kick', [$room, $participant->id]) }}" method="POST" onsubmit="return confirm('Remove {{ $participant->character_name ?: ($participant->user ? $participant->user->username : 'this participant') }} from the room?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-400 hover:text-red-300 hover:bg-red-500/10 p-1 rounded transition-colors">
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
            @endif
        </div>

        <!-- Room Actions -->
        @if(false)
            <!-- Leave Button for Authenticated Users -->
            <form action="{{ route('rooms.leave', $room) }}" method="POST" onsubmit="return confirm('Are you sure you want to leave this room?')" class="inline">
                @csrf
                @method('DELETE')
                <button data-testid="leave-room-button" type="submit" class="bg-red-500/20 hover:bg-red-500/30 text-red-400 border border-red-500/30 font-semibold py-2 px-4 rounded-xl transition-all duration-300">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Leave Session
                </button>
            </form>
        @endif
    </div>

    <!-- Include our Room WebRTC JavaScript with room context -->
    <script>
        // Pass room and participant data to the WebRTC script
        window.roomData = {
            id: {{ $room->id }},
            name: "{{ $room->name }}",
            guest_count: {{ $room->guest_count }},
            total_capacity: {{ $room->getTotalCapacity() }},
            stt_enabled: {{ ($room->recordingSettings && $room->recordingSettings->isSttEnabled()) ? 'true' : 'false' }},
            recording_enabled: {{ ($room->recordingSettings && $room->recordingSettings->isRecordingEnabled()) ? 'true' : 'false' }},
            participants: [
                @foreach($participants as $p)
                {
                    user_id: {{ $p->user_id ?? 'null' }},
                    username: "{{ $p->user ? $p->user->username : 'Unknown' }}",
                    character_name: "{{ $p->character ? $p->character->name : ($p->character_name ?? ($p->user ? $p->user->username : 'Unknown')) }}",
                    character_class: "{{ $p->character ? $p->character->class : ($p->character_class ?? 'Unknown') }}",
                    is_host: {{ $p->user_id === $room->creator_id ? 'true' : 'false' }}
                }{{ !$loop->last ? ',' : '' }}
                @endforeach
            ]
        };
        
        // Pass current user ID for participant identification
        window.currentUserId = {{ auth()->id() ?? 'null' }};
        
        function toggleParticipantsList() {
            const list = document.getElementById('participantsList');
            list.classList.toggle('hidden');
        }
        
        // Close participants list when clicking outside
        document.addEventListener('click', function(event) {
            const list = document.getElementById('participantsList');
            const button = event.target.closest('[onclick="toggleParticipantsList()"]');
            const dropdown = event.target.closest('#participantsList');
            
            if (!button && !dropdown && !list.classList.contains('hidden')) {
                list.classList.add('hidden');
            }
        });
        
        // Initialize RoomWebRTC when DOM is ready
        if (window.roomData && window.RoomWebRTC) {
            console.log('🚀 Starting Room WebRTC system');
            window.roomWebRTC = new window.RoomWebRTC(window.roomData);
        } else if (window.roomData && !window.RoomWebRTC) {
            console.warn('🎬 RoomWebRTC not available - ensure it\'s included in the main bundle');
        } else {
            console.warn('⚠️ No room data found, WebRTC not initialized');
        }
    </script>
    
    <!-- Uppy Integration for Video Recording -->
    <script>
        // Initialize Uppy for video recording when DOM is ready
        document.addEventListener('DOMContentLoaded', () => {
            if (window.roomData && window.roomData.recording_enabled && window.RoomUppy) {
                try {
                    // Initialize Uppy with room data and recording settings
                    window.roomUppy = new window.RoomUppy(window.roomData, {
                        storage_provider: 'local', // TODO: Get from room recording settings
                        // Add other recording settings here
                    });
                    
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
</x-layout.minimal>
