@props(['participants', 'room'])

<!-- Single Participant Layout -->
<div class="h-full w-full">
    <x-room-video-slot :slot-id="1" :participant="$participants->first()" :is-host="true" />
</div>
