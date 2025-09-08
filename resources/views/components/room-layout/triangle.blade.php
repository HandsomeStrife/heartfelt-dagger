@props(['participants', 'room', 'userIsCreator' => false])

<!-- 3 Participants - Triangle Layout -->
<div class="h-full w-full">
    <!-- Top Row - 1 centered -->
    <div class="h-1/2 w-full flex justify-center p-1">
        <div class="w-1/2">
            @if($participants->count() >= 1)
                <x-room-video-slot 
                    :slot-id="1" 
                    :participant="$participants->first()" 
                    :isHost="true" 
                    :userIsCreator="$userIsCreator"
                    :isGmReservedSlot="true" />
            @else
                <x-room-video-slot 
                    :slot-id="1" 
                    :participant="null" 
                    :isHost="false" 
                    :userIsCreator="$userIsCreator"
                    :isGmReservedSlot="true" />
            @endif
        </div>
    </div>
    <!-- Bottom Row - 2 side by side -->
    <div class="h-1/2 w-full grid grid-cols-2 gap-1">
        @foreach($participants->skip(1)->take(2) as $index => $participant)
            <x-room-video-slot 
                :slot-id="$index + 2" 
                :participant="$participant" 
                :isHost="false" 
                :userIsCreator="$userIsCreator"
                :isGmReservedSlot="false" />
        @endforeach
        @for($i = $participants->skip(1)->count(); $i < 2; $i++)
            <x-room-video-slot 
                :slot-id="$i + 2" 
                :participant="null" 
                :isHost="false" 
                :userIsCreator="$userIsCreator"
                :isGmReservedSlot="false" />
        @endfor
    </div>
</div>
