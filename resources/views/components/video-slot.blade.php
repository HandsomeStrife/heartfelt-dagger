<div class="video-slot bg-white rounded-lg shadow-lg overflow-hidden" data-slot-id="{{ $slotId }}">
    <div class="aspect-video bg-gray-900 flex items-center justify-center relative">
        <video class="local-video hidden w-full h-full object-cover" autoplay muted playsinline></video>
        <div class="remote-videos absolute inset-0 grid grid-cols-2 gap-1 p-1 hidden">
            <!-- Remote videos will be added here dynamically -->
        </div>
        <button class="join-btn bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-6 rounded-lg text-lg transition-colors">
            Join
        </button>
        <div class="loading-spinner hidden absolute inset-0 flex items-center justify-center bg-black bg-opacity-50">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-white"></div>
        </div>
    </div>
    <div class="p-4">
        <div class="flex justify-between items-center mb-2">
            <h3 class="text-lg font-semibold text-gray-800">Slot {{ $slotId }}</h3>
            <button class="leave-btn hidden bg-red-500 hover:bg-red-600 text-white font-medium py-1 px-3 rounded text-sm transition-colors">
                Leave
            </button>
        </div>
        <p class="text-sm text-gray-600 slot-status">Available</p>
    </div>
</div>