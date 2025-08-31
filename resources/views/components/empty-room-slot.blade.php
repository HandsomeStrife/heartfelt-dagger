@props(['slotId'])

<div class="video-slot h-full w-full bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-600/30 overflow-hidden" data-slot-id="{{ $slotId }}" data-testid="video-slot">
    <div class="h-full w-full bg-gradient-to-br from-slate-700 via-slate-800 to-slate-900 flex items-center justify-center relative">
        <div class='border-corners'>
            <!-- Decorative corner elements -->
            <div class="absolute top-2 left-2 w-4 h-4 border-l-2 border-t-2 border-slate-500/30"></div>
            <div class="absolute top-2 right-2 w-4 h-4 border-r-2 border-t-2 border-slate-500/30"></div>
            <div class="absolute bottom-2 left-2 w-4 h-4 border-l-2 border-b-2 border-slate-500/30"></div>
            <div class="absolute bottom-2 right-2 w-4 h-4 border-r-2 border-b-2 border-slate-500/30"></div>
        </div>
        
        <!-- Empty slot message -->
        <div class="text-center text-slate-500">
            <svg class="w-16 h-16 mx-auto mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <p class="text-sm font-semibold opacity-50">Empty Slot</p>
            <p class="text-xs opacity-30">Waiting for participant</p>
        </div>
        
        <!-- Subtle animation overlay -->
        <div class="absolute inset-0 bg-gradient-to-r from-slate-500/5 to-slate-400/5 animate-pulse"></div>
    </div>
</div>
