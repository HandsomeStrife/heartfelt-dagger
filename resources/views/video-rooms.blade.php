<x-layout>
    <div class="h-screen w-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-800 p-1">
        <!-- 3x2 Fullscreen Grid Layout -->
        <div class="h-full w-full grid grid-cols-3 grid-rows-2 gap-1">
            <!-- Top Row - 3 Video Slots -->
            <x-video-slot :slot-id="1" :character="$character" />
            <x-video-slot :slot-id="2" :character="$character" />
            <x-video-slot :slot-id="3" :character="$character" />
            
            <!-- Bottom Row - Empty Slot + 2 Video Slots -->
            <!-- Empty Decorative Slot (Bottom Left) -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 border border-purple-500/30 overflow-hidden">
                <div class="h-full w-full bg-gradient-to-br from-slate-700 via-purple-900/30 to-slate-800 flex items-center justify-center relative">
                    <!-- Decorative fantasy elements -->
                    <div class="text-center text-purple-300/50">
                        <svg class="w-16 h-16 mx-auto mb-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2L13.5 8.5L20 10L13.5 11.5L12 18L10.5 11.5L4 10L10.5 8.5L12 2Z"/>
                            <path d="M8 2L8.5 4.5L11 5L8.5 5.5L8 8L7.5 5.5L5 5L7.5 4.5L8 2Z"/>
                            <path d="M16 16L16.5 18.5L19 19L16.5 19.5L16 22L15.5 19.5L13 19L15.5 18.5L16 16Z"/>
                        </svg>
                        <p class="text-sm font-semibold">Reserved</p>
                        <p class="text-xs">For the Void</p>
                    </div>
                    <!-- Subtle animation -->
                    <div class="absolute inset-0 bg-gradient-to-r from-purple-500/10 to-amber-500/10 animate-pulse"></div>
                </div>
            </div>
            
            <x-video-slot :slot-id="4" :character="$character" />
            <x-video-slot :slot-id="5" :character="$character" />
        </div>
    </div>

    <!-- Include our WebRTC JavaScript -->
    <script src="{{ asset('js/webrtc-rooms.js') }}" defer></script>
</x-layout>