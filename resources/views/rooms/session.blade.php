<x-layout>
    <div class="h-screen w-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-800 p-1">
        <!-- Dynamic Grid Layout Based on Guest Count -->
        @if($room->guest_count == 1)
            <!-- Single Participant Layout -->
            <div class="h-full w-full">
                <x-room-video-slot :slot-id="1" :participant="$participants->first()" :is-host="true" />
            </div>
        @elseif($room->guest_count == 2)
            <!-- 2 Participants - Side by Side -->
            <div class="h-full w-full grid grid-cols-2 gap-1">
                @foreach($participants->take(2) as $index => $participant)
                    <x-room-video-slot :slot-id="$index + 1" :participant="$participant" :is-host="$loop->first" />
                @endforeach
                @for($i = $participants->count(); $i < 2; $i++)
                    <x-empty-room-slot :slot-id="$i + 1" />
                @endfor
            </div>
        @elseif($room->guest_count == 3)
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
        @elseif($room->guest_count == 4)
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
            <!-- 5 Participants - 2x3 Grid -->
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
                @for($i = max(2, $participants->count() - 3); $i < 2; $i++)
                    <x-empty-room-slot :slot-id="$i + 4" />
                @endfor
            </div>
        @endif
    </div>

    <!-- Room Controls -->
    <div class="absolute top-4 right-4 flex items-center space-x-4">
        <!-- Room Info -->
        <div class="bg-slate-900/90 backdrop-blur-xl border border-slate-700/50 rounded-xl px-4 py-2">
            <h3 class="text-white font-semibold text-sm">{{ $room->name }}</h3>
            <p class="text-slate-400 text-xs">{{ $participants->count() }}/{{ $room->guest_count }} participants</p>
        </div>

        <!-- Leave Button -->
        <form action="{{ route('rooms.leave', $room) }}" method="POST" onsubmit="return confirm('Are you sure you want to leave this room?')" class="inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="bg-red-500/20 hover:bg-red-500/30 text-red-400 border border-red-500/30 font-semibold py-2 px-4 rounded-xl transition-all duration-300">
                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Leave Room
            </button>
        </form>
    </div>

    <!-- Include our Room WebRTC JavaScript with room context -->
    <script>
        // Pass room and participant data to the WebRTC script
        window.roomData = {
            id: {{ $room->id }},
            name: "{{ $room->name }}",
            guest_count: {{ $room->guest_count }},
            participants: [
                @foreach($participants as $p)
                {
                    user_id: {{ $p->user_id }},
                    username: "{{ $p->user ? $p->user->username : 'Unknown' }}",
                    character_name: "{{ $p->character ? $p->character->name : ($p->character_name ?? ($p->user ? $p->user->username : 'Unknown')) }}",
                    character_class: "{{ $p->character ? $p->character->class : ($p->character_class ?? 'Unknown') }}",
                    is_host: {{ $p->user_id === $room->creator_id ? 'true' : 'false' }}
                }{{ !$loop->last ? ',' : '' }}
                @endforeach
            ]
        };
        
        // Pass current user ID for participant identification
        window.currentUserId = {{ auth()->id() }};
    </script>
    <script src="{{ asset('js/room-webrtc.js') }}" defer></script>
</x-layout>
