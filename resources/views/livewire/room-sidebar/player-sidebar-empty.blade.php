<div class="h-full flex flex-col items-center justify-center p-8 text-center">
    <svg class="w-16 h-16 text-slate-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
    </svg>
    
    <h3 class="text-white font-outfit text-lg mb-2">No Character Linked</h3>
    <p class="text-slate-400 text-sm mb-6 max-w-xs">
        You haven't linked a character to this room session. Character details will appear here when you join with a character.
    </p>
    
    <div class="bg-slate-800/30 rounded-lg p-4 border border-slate-700/50 max-w-xs">
        <h4 class="text-white font-medium text-sm mb-2">Temporary Character Info</h4>
        @if($current_participant)
            <div class="text-slate-300 text-xs space-y-1">
                <div>
                    <span class="text-slate-400">Name:</span> 
                    {{ $current_participant->character_name ?? 'Unknown' }}
                </div>
                <div>
                    <span class="text-slate-400">Class:</span> 
                    {{ $current_participant->character_class ?? 'Unknown' }}
                </div>
                @if($current_participant->user)
                    <div>
                        <span class="text-slate-400">Player:</span> 
                        {{ $current_participant->user->username }}
                    </div>
                @endif
            </div>
        @else
            <p class="text-slate-400 text-xs">No participant information available</p>
        @endif
    </div>
</div>
