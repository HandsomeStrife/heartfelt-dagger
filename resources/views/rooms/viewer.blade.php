<x-layout.minimal>
    <!-- Viewer Room Layout (no sidebar, no status bar) -->
    <div x-data="{ sidebarVisible: false }" class="h-screen w-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-800 flex flex-col">
        
        
        <!-- Main Video Area -->
        <div class="flex-1 p-1">
            <!-- Dynamic Grid Layout Based on Total Capacity (Viewer Mode - No Join Buttons) -->
            @if($room->getTotalCapacity() == 1)
                <x-room-layout.single :participants="$participants" :room="$room" :userIsCreator="false" :viewerMode="true" />
            @elseif($room->getTotalCapacity() == 2)
                <x-room-layout.dual :participants="$participants" :room="$room" :userIsCreator="false" :viewerMode="true" />
            @elseif($room->getTotalCapacity() == 3)
                <x-room-layout.triangle :participants="$participants" :room="$room" :userIsCreator="false" :viewerMode="true" />
            @elseif($room->getTotalCapacity() == 4)
                <x-room-layout.quad :participants="$participants" :room="$room" :userIsCreator="false" :viewerMode="true" />
            @elseif($room->getTotalCapacity() == 5)
                <x-room-layout.penta :participants="$participants" :room="$room" :userIsCreator="false" :viewerMode="true" />
            @elseif($room->getTotalCapacity() == 6)
                <x-room-layout.grid :participants="$participants" :room="$room" :userIsCreator="false" :viewerMode="true" />
            @else
                {{-- Fallback for 7+ participants (shouldn't happen with current validation) --}}
                <x-room-layout.grid :participants="$participants" :room="$room" :userIsCreator="false" :viewerMode="true" />
            @endif
        </div>
    </div>

    <!-- Include Room WebRTC JavaScript for viewer (read-only) -->
    <script>
        // Pass room and participant data to the WebRTC script (viewer mode)
        window.roomData = {
            id: {{ $room->id }},
            name: "{{ $room->name }}",
            creator_id: {{ $room->creator_id }},
            campaign_id: {{ $room->campaign_id ?? 'null' }},
            guest_count: {{ $room->guest_count }},
            total_capacity: {{ $room->getTotalCapacity() }},
            viewer_mode: true, // This is a viewer, not a participant
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
        
        // No current user ID for viewers (they're not participants)
        window.currentUserId = null;
        
        // Initialize RoomWebRTC in viewer mode when DOM and modules are ready
        let webrtcInitAttempts = 0;
        const maxWebrtcInitAttempts = 50; // Max 5 seconds of retries
        
        function initializeRoomWebRTC() {
            if (window.roomData && window.RoomWebRTC) {
                console.log('🚀 Starting Room WebRTC system (Viewer Mode)');
                window.roomWebRTC = new window.RoomWebRTC(window.roomData);
                
                // Viewers don't need consent checks, but can observe the room
                console.log('👁️ Viewer mode - read-only access to room');
            } else if (window.roomData && !window.RoomWebRTC && webrtcInitAttempts < maxWebrtcInitAttempts) {
                webrtcInitAttempts++;
                console.warn(`🎬 RoomWebRTC not available - attempt ${webrtcInitAttempts}/${maxWebrtcInitAttempts}`);
                // Retry after a short delay in case the bundle is still loading
                setTimeout(initializeRoomWebRTC, 100);
            } else if (webrtcInitAttempts >= maxWebrtcInitAttempts) {
                console.error('❌ Failed to initialize RoomWebRTC after maximum attempts. Please refresh the page.');
            } else {
                console.warn('⚠️ No room data found, WebRTC not initialized');
            }
        }
        
        // Wait for DOM and give modules time to load
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                setTimeout(initializeRoomWebRTC, 50);
            });
        } else {
            setTimeout(initializeRoomWebRTC, 50);
        }
    </script>
</x-layout.minimal>
