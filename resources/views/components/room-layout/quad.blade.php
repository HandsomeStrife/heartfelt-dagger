@props(['participants', 'room', 'userIsCreator' => false, 'viewerMode' => false])

<!-- 4 Participants - 2x2 Grid -->
<div class="h-full w-full grid grid-cols-2 grid-rows-2 gap-1">
    @foreach($participants->take(4) as $index => $participant)
        <x-room-video-slot 
            :slot-id="$index + 1" 
            :participant="$participant" 
            :isHost="$loop->first" 
            :userIsCreator="$userIsCreator"
            :isGmReservedSlot="$index === 0"
            :viewerMode="$viewerMode" />
    @endforeach
    @for($i = $participants->count(); $i < 4; $i++)
        <x-room-video-slot 
            :slot-id="$i + 1" 
            :participant="null" 
            :isHost="false" 
            :userIsCreator="$userIsCreator"
            :isGmReservedSlot="($i + 1) === 1"
            :viewerMode="$viewerMode" />
    @endfor
</div>
