@props(['participants', 'room'])

<!-- 2 Participants - Side by Side -->
<div class="h-full w-full grid grid-cols-2 gap-1">
    @foreach($participants->take(2) as $index => $participant)
        <x-room-video-slot :slot-id="$index + 1" :participant="$participant" :is-host="$loop->first" />
    @endforeach
    @for($i = $participants->count(); $i < 2; $i++)
        <x-empty-room-slot :slot-id="$i + 1" />
    @endfor
</div>
