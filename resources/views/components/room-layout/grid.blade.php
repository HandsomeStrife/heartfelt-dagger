@props(['participants', 'room'])

<!-- 5-6 Participants - Responsive Grid Layout -->
<div class="h-full w-full grid gap-1 grid-cols-2 grid-rows-3 xl:grid-cols-3 xl:grid-rows-2" 
     x-data="{}"
     x-init="$watch('sidebarVisible', () => console.log('Grid layout - sidebar visible:', sidebarVisible))">
    <!-- Participants (up to 6 total) -->
    @foreach($participants->take(6) as $index => $participant)
        <x-room-video-slot :slot-id="$index + 1" :participant="$participant" :is-host="$loop->first" />
    @endforeach
    
    <!-- Fill remaining slots with empty slots -->
    @for($i = $participants->count(); $i < $room->getTotalCapacity(); $i++)
        <x-empty-room-slot :slot-id="$i + 1" />
    @endfor
</div>
