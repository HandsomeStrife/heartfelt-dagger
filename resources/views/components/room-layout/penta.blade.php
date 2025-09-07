@props(['participants', 'room'])

<!-- 5 Participants - Responsive Grid Layout -->
<div class="h-full w-full grid gap-1" 
     :class="sidebarVisible ? 'grid-cols-2 grid-rows-3' : 'grid-cols-3 grid-rows-2'"
     x-data="{}"
     x-init="$watch('sidebarVisible', () => console.log('Penta layout - sidebar visible:', sidebarVisible))">
    <!-- Participants (5 total) -->
    @foreach($participants->take(5) as $index => $participant)
        <x-room-video-slot :slot-id="$index + 1" :participant="$participant" :is-host="$loop->first" />
    @endforeach
    
    <!-- Fill remaining slots with empty slots -->
    @for($i = $participants->count(); $i < 5; $i++)
        <x-empty-room-slot :slot-id="$i + 1" />
    @endfor
</div>
