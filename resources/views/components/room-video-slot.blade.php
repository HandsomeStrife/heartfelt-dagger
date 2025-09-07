@props(['slotId', 'participant' => null, 'isHost' => false])

<div class="video-slot h-full w-full bg-gradient-to-br from-slate-800 to-slate-900 border {{ $isHost ? 'border-emerald-500/30' : 'border-amber-500/30' }} overflow-hidden hover:border-{{ $isHost ? 'emerald' : 'amber' }}-400/60 transition-all duration-300" data-slot-id="{{ $slotId }}" data-testid="video-slot">
    <div class="h-full w-full bg-gradient-to-br from-slate-700 via-slate-800 to-slate-900 flex items-center justify-center relative">
        <video class="local-video hidden w-full h-full object-cover" autoplay muted playsinline></video>
        <div class="remote-videos absolute inset-0 gap-1 p-1 hidden">
            <!-- Remote videos will be added here dynamically -->
        </div>
        
        <div class='border-corners'>
            <!-- Decorative corner elements -->
            <div class="absolute top-2 left-2 w-4 h-4 border-l-2 border-t-2 border-{{ $isHost ? 'emerald' : 'amber' }}-400/50"></div>
            <div class="absolute top-2 right-2 w-4 h-4 border-r-2 border-t-2 border-{{ $isHost ? 'emerald' : 'amber' }}-400/50"></div>
            <div class="absolute bottom-2 left-2 w-4 h-4 border-l-2 border-b-2 border-{{ $isHost ? 'emerald' : 'amber' }}-400/50"></div>
            <div class="absolute bottom-2 right-2 w-4 h-4 border-r-2 border-b-2 border-{{ $isHost ? 'emerald' : 'amber' }}-400/50"></div>
        </div>
        
        <!-- Character Sheet Overlay (hidden by default, shown when occupied) -->
        <div class="character-overlay hidden absolute inset-0 pointer-events-none">
            <!-- Character Name Panel (Bottom Left) -->
            <div class="absolute bottom-2 left-0 w-auto pointer-events-auto overflow-visible">
                <div class="relative h-[54px] min-w-[45%]">
                    <!-- Banner Background -->
                    <div class="absolute right-0 h-full z-1" style="transform: translateX(68px); top: -0.5px;">
                        <x-banner.right style="width: 72px; height: 55px;" />
                    </div>
                    
                    <!-- Main Content Container -->
                    <div class="relative z-10 bg-daggerheart-blue border border-daggerheart-gold border-l-0 border-r-0 h-full flex items-center">
                        @if($participant && $participant->character)
                            <!-- Character Banner -->
                            <div class="absolute -top-1 left-4">
                                <img src="{{ asset('images/banners/' . strtolower($participant->character->class) . '.png') }}" alt="{{ $participant->character->class }}" class="w-8 h-16 object-cover rounded">
                            </div>
                        @endif
                        
                        <!-- Character Info -->
                        <div class="text-left {{ ($participant && $participant->character) ? 'ml-11 pl-4' : 'px-4' }} py-3">
                            <div class="character-name font-fantasy text-amber-300 text-lg tracking-wide leading-tight">
                                @if($participant)
                                    @if($participant->character)
                                        {{ $participant->character->name }}
                                    @elseif($participant->character_name)
                                        {{ $participant->character_name }}
                                    @else
                                        {{ $participant->user ? $participant->user->username : 'Anonymous' }}
                                    @endif
                                @else
                                    Empty Slot
                                @endif
                                @if($isHost)
                                    <span class="text-emerald-400 text-sm ml-2">(Host)</span>
                                @endif
                            </div>
                            <div class="text-xs text-gray-400 flex gap-1 flex-wrap">
                                @if($participant)
                                    <span class="character-class uppercase tracking-wide">
                                        @if($participant->character)
                                            {{ $participant->character->class }}
                                        @elseif($participant->character_class)
                                            {{ $participant->character_class }}
                                        @elseif($isHost)
                                            GM
                                        @else
                                            NO CLASS
                                        @endif
                                    </span>
                                    @if($participant->character && $participant->character->subclass)
                                        <span>/</span>
                                        <span class="character-subclass uppercase tracking-wide">
                                            {{ $participant->character->subclass }}
                                        </span>
                                    @endif
                                @else
                                    <span class="uppercase tracking-wide">WAITING FOR PARTICIPANT</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        @if($participant)
            <!-- Join button for existing participants -->
            <button class="join-btn bg-gradient-to-r from-{{ $isHost ? 'emerald' : 'amber' }}-500 to-{{ $isHost ? 'teal' : 'orange' }}-500 hover:from-{{ $isHost ? 'emerald' : 'amber' }}-400 hover:to-{{ $isHost ? 'teal' : 'orange' }}-400 text-black font-bold py-3 px-6 rounded-lg text-lg transition-all duration-300 shadow-lg hover:shadow-{{ $isHost ? 'emerald' : 'amber' }}-500/50 transform hover:scale-105">
                <span class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2L13.5 8.5L20 10L13.5 11.5L12 18L10.5 11.5L4 10L10.5 8.5L12 2Z"/>
                    </svg>
                    {{ $isHost ? 'Join Room' : 'Join Quest' }}
                </span>
            </button>
        @else
            <!-- Empty slot message -->
            <div class="text-center text-slate-400">
                <svg class="w-12 h-12 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <p class="text-sm font-semibold opacity-75">Waiting for participant</p>
            </div>
        @endif
        
        
        <div class="loading-spinner hidden absolute inset-0 items-center justify-center bg-black bg-opacity-75">
            <div class="flex flex-col items-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-{{ $isHost ? 'emerald' : 'amber' }}-400"></div>
                <p class="text-{{ $isHost ? 'emerald' : 'amber' }}-300 mt-3 text-sm">Entering the realm...</p>
            </div>
        </div>
    </div>
</div>
