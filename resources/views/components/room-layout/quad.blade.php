@props(['participants', 'room'])

<!-- 4 Participants - 2x2 Grid -->
<div class="h-full w-full grid grid-cols-2 grid-rows-2 gap-1">
    @foreach($participants->take(4) as $index => $participant)
        <x-room-video-slot :slot-id="$index + 1" :participant="$participant" :is-host="$loop->first" />
    @endforeach
    @for($i = $participants->count(); $i < 4; $i++)
        <x-empty-room-slot :slot-id="$i + 1" />
    @endfor
</div>
