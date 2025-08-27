<x-layout.minimal>
    <div class="h-screen w-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-800 p-1">
        <!-- Dynamic Grid Layout Based on Total Capacity (Creator + Guests) -->
        @if($room->getTotalCapacity() == 1)
            <!-- Single Participant Layout -->
            <div class="h-full w-full">
                <x-room-video-slot :slot-id="1" :participant="$participants->first()" :is-host="true" />
            </div>
        @elseif($room->getTotalCapacity() == 2)
            <!-- 2 Participants - Side by Side -->
            <div class="h-full w-full grid grid-cols-2 gap-1">
                @foreach($participants->take(2) as $index => $participant)
                    <x-room-video-slot :slot-id="$index + 1" :participant="$participant" :is-host="$loop->first" />
                @endforeach
                @for($i = $participants->count(); $i < 2; $i++)
                    <x-empty-room-slot :slot-id="$i + 1" />
                @endfor
            </div>
        @elseif($room->getTotalCapacity() == 3)
            <!-- 3 Participants - Triangle Layout -->
            <div class="h-full w-full">
                <!-- Top Row - 1 centered -->
                <div class="h-1/2 w-full flex justify-center p-1">
                    <div class="w-1/2">
                        @if($participants->count() >= 1)
                            <x-room-video-slot :slot-id="1" :participant="$participants->first()" :is-host="true" />
                        @else
                            <x-empty-room-slot :slot-id="1" />
                        @endif
                    </div>
                </div>
                <!-- Bottom Row - 2 side by side -->
                <div class="h-1/2 w-full grid grid-cols-2 gap-1">
                    @foreach($participants->skip(1)->take(2) as $index => $participant)
                        <x-room-video-slot :slot-id="$index + 2" :participant="$participant" :is-host="false" />
                    @endforeach
                    @for($i = max(1, $participants->count() - 1); $i < 2; $i++)
                        <x-empty-room-slot :slot-id="$i + 2" />
                    @endfor
                </div>
            </div>
        @elseif($room->getTotalCapacity() == 4)
            <!-- 4 Participants - 2x2 Grid -->
            <div class="h-full w-full grid grid-cols-2 grid-rows-2 gap-1">
                @foreach($participants->take(4) as $index => $participant)
                    <x-room-video-slot :slot-id="$index + 1" :participant="$participant" :is-host="$loop->first" />
                @endforeach
                @for($i = $participants->count(); $i < 4; $i++)
                    <x-empty-room-slot :slot-id="$i + 1" />
                @endfor
            </div>
        @else
            <!-- 5+ Participants - 2x3 Grid -->
            <div class="h-full w-full grid grid-cols-3 grid-rows-2 gap-1">
                <!-- Top Row - 3 Video Slots -->
                @foreach($participants->take(3) as $index => $participant)
                    <x-room-video-slot :slot-id="$index + 1" :participant="$participant" :is-host="$loop->first" />
                @endforeach
                @for($i = min(3, $participants->count()); $i < 3; $i++)
                    <x-empty-room-slot :slot-id="$i + 1" />
                @endfor
                
                <!-- Bottom Row - Empty + 2 Video Slots -->
                <!-- Decorative empty slot (bottom left) -->
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 border border-purple-500/30 overflow-hidden">
                    <div class="h-full w-full bg-gradient-to-br from-slate-700 via-purple-900/30 to-slate-800 flex items-center justify-center relative">
                        <div class="text-center text-purple-300/50">
                            <svg class="w-16 h-16 mx-auto mb-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2L13.5 8.5L20 10L13.5 11.5L12 18L10.5 11.5L4 10L10.5 8.5L12 2Z"/>
                                <path d="M8 2L8.5 4.5L11 5L8.5 5.5L8 8L7.5 5.5L5 5L7.5 4.5L8 2Z"/>
                                <path d="M16 16L16.5 18.5L19 19L16.5 19.5L16 22L15.5 19.5L13 19L15.5 18.5L16 16Z"/>
                            </svg>
                            <p class="text-sm font-semibold">Reserved</p>
                            <p class="text-xs">For the Void</p>
                        </div>
                        <div class="absolute inset-0 bg-gradient-to-r from-purple-500/10 to-amber-500/10 animate-pulse"></div>
                    </div>
                </div>
                
                @foreach($participants->skip(3)->take(2) as $index => $participant)
                    <x-room-video-slot :slot-id="$index + 4" :participant="$participant" :is-host="false" />
                @endforeach
                
                {{-- Fill remaining bottom row slots with empty slots --}}
                @for($i = $participants->skip(3)->count(); $i < min(2, $room->getTotalCapacity() - 3); $i++)
                    <x-empty-room-slot :slot-id="$i + 4" />
                @endfor
            </div>
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
    </script>
    <script src="{{ asset('js/room-webrtc.js') }}" defer></script>
</x-layout.minimal>
