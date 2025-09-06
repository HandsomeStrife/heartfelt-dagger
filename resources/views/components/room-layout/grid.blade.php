@props(['participants', 'room'])

<!-- 5-6 Participants - 2x3 Grid -->
<div class="h-full w-full grid grid-cols-3 grid-rows-2 gap-1">
    <!-- Top Row - 3 Video Slots -->
    @foreach($participants->take(3) as $index => $participant)
        <x-room-video-slot :slot-id="$index + 1" :participant="$participant" :is-host="$loop->first" />
    @endforeach
    @for($i = min(3, $participants->count()); $i < 3; $i++)
        <x-empty-room-slot :slot-id="$i + 1" />
    @endfor
    
    <!-- Bottom Row - 3 Video Slots -->
    @foreach($participants->skip(3)->take(3) as $index => $participant)
        <x-room-video-slot :slot-id="$index + 4" :participant="$participant" :is-host="false" />
    @endforeach
    
    {{-- Fill remaining bottom row slots with empty slots --}}
    @for($i = $participants->skip(3)->count(); $i < min(3, $room->getTotalCapacity() - 3); $i++)
        <x-empty-room-slot :slot-id="$i + 4" />
    @endfor
</div>
