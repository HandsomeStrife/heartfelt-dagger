@props(['participants', 'room'])

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
