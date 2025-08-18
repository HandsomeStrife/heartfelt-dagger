<x-layout>
    <div class="min-h-screen bg-gray-100 p-8">
        <div class="max-w-7xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-900 mb-8 text-center">Video Rooms</h1>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <x-video-slot :slot-id="1" />
                <x-video-slot :slot-id="2" />
                <x-video-slot :slot-id="3" />
                <x-video-slot :slot-id="4" />
                <x-video-slot :slot-id="5" />
                <x-video-slot :slot-id="6" />
            </div>
        </div>
    </div>

    <!-- Include our WebRTC JavaScript -->
    <script src="{{ asset('js/webrtc-rooms.js') }}" defer></script>
</x-layout>