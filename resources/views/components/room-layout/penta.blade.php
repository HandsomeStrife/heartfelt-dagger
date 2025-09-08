@props(['participants', 'room', 'userIsCreator' => false])

<!-- 5 Participants - Responsive Grid Layout -->
<div class="h-full w-full grid gap-1 grid-cols-2 grid-rows-3 xl:grid-cols-3 xl:grid-rows-2" 
     x-data="{}"
     x-init="$watch('sidebarVisible', () => console.log('Penta layout - sidebar visible:', sidebarVisible))">
    <!-- Participants (5 total) -->
    @foreach($participants->take(5) as $index => $participant)
        <x-room-video-slot 
            :slot-id="$index + 1" 
            :participant="$participant" 
            :isHost="$loop->first" 
            :userIsCreator="$userIsCreator"
            :isGmReservedSlot="$index === 0" />
    @endforeach
    
    <!-- Fill remaining slots with empty slots -->
    @for($i = $participants->count(); $i < 5; $i++)
        <x-room-video-slot 
            :slot-id="$i + 1" 
            :participant="null" 
            :isHost="false" 
            :userIsCreator="$userIsCreator"
            :isGmReservedSlot="($i + 1) === 1" />
    @endfor
</div>
