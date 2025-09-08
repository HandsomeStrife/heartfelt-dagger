@props(['participants', 'room', 'userIsCreator' => false])

<!-- 5-6 Participants - Responsive Grid Layout -->
<div class="h-full w-full grid gap-1 grid-cols-2 grid-rows-3 xl:grid-cols-3 xl:grid-rows-2" 
     x-data="{}"
     x-init="$watch('sidebarVisible', () => console.log('Grid layout - sidebar visible:', sidebarVisible))">
    <!-- Render all slots (up to room capacity) -->
    @for($i = 1; $i <= $room->getTotalCapacity(); $i++)
        <x-room-video-slot 
            :slot-id="$i" 
            :participant="null" 
            :isHost="$i === 1" 
            :userIsCreator="$userIsCreator"
            :isGmReservedSlot="$i === 1" />
    @endfor
</div>
