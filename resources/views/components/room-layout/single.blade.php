@props(['participants', 'room', 'userIsCreator' => false, 'viewerMode' => false])

<!-- Single Participant Layout -->
<div class="h-full w-full">
    @if($participants->count() >= 1)
        <x-room-video-slot 
            :slot-id="1" 
            :participant="$participants->first()" 
            :isHost="true" 
            :userIsCreator="$userIsCreator"
            :isGmReservedSlot="true"
            :viewerMode="$viewerMode" />
    @else
        <x-room-video-slot 
            :slot-id="1" 
            :participant="null" 
            :isHost="false" 
            :userIsCreator="$userIsCreator"
            :isGmReservedSlot="true"
            :viewerMode="$viewerMode" />
    @endif
</div>
