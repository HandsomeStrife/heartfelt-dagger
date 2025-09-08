@props(['participants', 'room', 'userIsCreator' => false])

<!-- 2 Participants - Side by Side -->
<div class="h-full w-full grid grid-cols-2 gap-1">
    @foreach($participants->take(2) as $index => $participant)
        <x-room-video-slot 
            :slot-id="$index + 1" 
            :participant="$participant" 
            :isHost="$loop->first" 
            :userIsCreator="$userIsCreator"
            :isGmReservedSlot="$index === 0" />
    @endforeach
    @for($i = $participants->count(); $i < 2; $i++)
        <x-room-video-slot 
            :slot-id="$i + 1" 
            :participant="null" 
            :isHost="false" 
            :userIsCreator="$userIsCreator"
            :isGmReservedSlot="($i + 1) === 1" />
    @endfor
</div>
