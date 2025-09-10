<x-layout>
    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950">
        <!-- Compact Navigation -->
        <x-sub-navigation>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <a 
                        href="{{ route('dashboard') }}"
                        class="p-1.5 text-slate-400 hover:text-white hover:bg-slate-700/50 rounded-md transition-colors"
                        title="Back to dashboard"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <div class="flex items-center gap-2">
                        <img src="{{ asset('img/logo.png') }}" alt="Heartfelt Dagger Logo" class="w-auto h-6 drop-shadow-lg">
                        <div>
                            <h1 class="font-outfit text-lg font-bold text-white tracking-wide">
                                Actual Plays
                            </h1>
                            <p class="text-slate-400 text-xs">
                                Discover Amazing Daggerheart Adventures
                            </p>
                        </div>
                    </div>
                </div>

                <div class="text-xs text-slate-400 text-right">
                    <span class="font-medium">Want to be listed? </span>
                    <a href="mailto:thepartywipes@gmail.com" class="text-amber-300 hover:text-amber-200 transition-colors">Email us</a>
                    or <a href="{{ route('discord') }}" class="text-amber-300 hover:text-amber-200 transition-colors">join Discord</a>
                </div>
            </div>
        </x-sub-navigation>

        <div class="px-4 sm:px-6 lg:px-8 pt-8 pb-12">
    
            <!-- Video Actual Plays Section -->
            <section class="relative">
                <div class="text-center mb-8">
                <h2 class="text-xl font-bold text-white mb-2 font-outfit">
                    Video Actual Plays
                </h2>
                <p class="text-sm text-slate-300 max-w-3xl mx-auto">
                    Watch these incredible Daggerheart adventures unfold in video format
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-7xl mx-auto">
                @foreach($actual_plays_data['video_actual_plays'] as $play)
                    <div class="group text-left p-6 rounded-3xl bg-gradient-to-br from-purple-500/10 to-pink-500/10 backdrop-blur-sm border border-purple-400/30 hover:border-purple-400/60 transition-all duration-300 hover:transform hover:-translate-y-2 hover:shadow-2xl hover:shadow-purple-500/20">
                        <div class="flex items-start gap-4 mb-4">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-purple-400 to-pink-400 flex items-center justify-center group-hover:scale-110 transition-transform shadow-lg flex-shrink-0">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-lg font-bold text-white mb-1 font-outfit">{{ $play['creator'] }}</h3>
                                @if($play['campaign'])
                                    <p class="text-amber-300 text-sm font-medium mb-2">{{ $play['campaign'] }}</p>
                                @endif
                            </div>
                        </div>
                        
                        @if($play['description'])
                            <p class="text-slate-300 text-sm leading-relaxed mb-4">
                                {{ $play['description'] }}
                            </p>
                        @endif
                        
                        @if(isset($play['update_schedule']))
                            <div class="mb-4">
                                <span class="inline-flex items-center px-2 py-1 bg-emerald-500/20 text-emerald-300 text-xs rounded-lg border border-emerald-500/30">
                                    {{ $play['update_schedule'] }}
                                </span>
                            </div>
                        @endif
                        
                        @if(isset($play['status']))
                            <div class="mb-4">
                                <span class="inline-flex items-center px-2 py-1 bg-amber-500/20 text-amber-300 text-xs rounded-lg border border-amber-500/30">
                                    {{ $play['status'] }}
                                </span>
                            </div>
                        @endif
                        
                        <div class="flex flex-wrap gap-2">
                            @if(isset($play['links']['youtube']))
                                <a href="{{ $play['links']['youtube'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 px-3 py-2 bg-red-500/20 hover:bg-red-500/30 text-red-300 text-xs rounded-lg border border-red-500/30 transition-colors">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                    </svg>
                                    YouTube
                                </a>
                            @endif
                            @if(isset($play['links']['twitch']))
                                <a href="{{ $play['links']['twitch'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 px-3 py-2 bg-purple-500/20 hover:bg-purple-500/30 text-purple-300 text-xs rounded-lg border border-purple-500/30 transition-colors">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M11.571 4.714h1.715v5.143H11.57zm4.715 0H18v5.143h-1.714zM6 0L1.714 4.286v15.428h5.143V24l4.286-4.286h3.428L22.286 12V0zm14.571 11.143l-3.428 3.428h-3.429l-3 3v-3H6.857V1.714h13.714Z"/>
                                    </svg>
                                    Twitch
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Visual Separator -->
    <div class="h-px bg-gradient-to-r from-transparent via-cyan-500/50 to-transparent"></div>
    
    <!-- Audio Actual Plays Section -->
    <section class="py-20 relative bg-gradient-to-b from-slate-800/30 to-slate-900/80">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4 font-outfit">
                    Audio Actual Plays
                </h2>
                <p class="text-lg text-slate-300 max-w-3xl mx-auto">
                    Listen to these captivating Daggerheart stories in podcast format
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-7xl mx-auto">
                @foreach($actual_plays_data['audio_actual_plays'] as $play)
                    <div class="group text-left p-6 rounded-3xl bg-gradient-to-br from-blue-500/10 to-cyan-500/10 backdrop-blur-sm border border-blue-400/30 hover:border-blue-400/60 transition-all duration-300 hover:transform hover:-translate-y-2 hover:shadow-2xl hover:shadow-blue-500/20">
                        <div class="flex items-start gap-4 mb-4">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-400 to-cyan-400 flex items-center justify-center group-hover:scale-110 transition-transform shadow-lg flex-shrink-0">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-lg font-bold text-white mb-1 font-outfit">{{ $play['creator'] }}</h3>
                                @if($play['campaign'])
                                    <p class="text-amber-300 text-sm font-medium mb-2">{{ $play['campaign'] }}</p>
                                @endif
                            </div>
                        </div>
                        
                        @if($play['description'])
                            <p class="text-slate-300 text-sm leading-relaxed mb-4">
                                {{ $play['description'] }}
                            </p>
                        @endif
                        
                        @if(isset($play['update_schedule']))
                            <div class="mb-4">
                                <span class="inline-flex items-center px-2 py-1 bg-emerald-500/20 text-emerald-300 text-xs rounded-lg border border-emerald-500/30">
                                    {{ $play['update_schedule'] }}
                                </span>
                            </div>
                        @endif
                        
                        <div class="flex flex-wrap gap-2">
                            @if(isset($play['links']['youtube']))
                                <a href="{{ $play['links']['youtube'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 px-3 py-2 bg-red-500/20 hover:bg-red-500/30 text-red-300 text-xs rounded-lg border border-red-500/30 transition-colors">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                    </svg>
                                    YouTube
                                </a>
                            @endif
                            @if(isset($play['links']['podcast']))
                                <a href="{{ $play['links']['podcast'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 px-3 py-2 bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 text-xs rounded-lg border border-emerald-500/30 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                                    </svg>
                                    Podcast
                                </a>
                            @endif
                            @if(isset($play['links']['linktree']))
                                <a href="{{ $play['links']['linktree'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 px-3 py-2 bg-green-500/20 hover:bg-green-500/30 text-green-300 text-xs rounded-lg border border-green-500/30 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                    </svg>
                                    Links
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Visual Separator -->
    <div class="h-px bg-gradient-to-r from-transparent via-purple-500/50 to-transparent"></div>
    
    <!-- Community Section -->
    <section class="py-20 relative bg-gradient-to-b from-slate-900/80 to-slate-950">
        <!-- Background Gradient Orbs -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <div class="absolute top-0 left-1/4 w-96 h-96 bg-gradient-to-r from-purple-500/10 to-pink-500/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-gradient-to-r from-blue-500/10 to-cyan-500/10 rounded-full blur-3xl"></div>
        </div>
        
        <div class="container mx-auto px-4 relative z-10">
            <div class="max-w-3xl mx-auto text-center">
                <h2 class="text-3xl sm:text-4xl font-bold text-white mb-6 font-outfit">
                    Share Your Adventures
                </h2>
                <p class="text-lg text-slate-300 mb-12">
                    Running your own Daggerheart actual play? Join our community and share your epic stories with fellow adventurers!
                </p>
                
                <div class="grid sm:grid-cols-2 gap-6 mb-8">
                    <a 
                        href="{{ route('discord') }}" 
                        target="_blank" 
                        rel="noopener noreferrer" 
                        class="group p-6 bg-gradient-to-br from-slate-800/40 to-slate-700/40 hover:from-slate-700/60 hover:to-slate-600/60 rounded-2xl transition-all duration-300 border border-slate-600/50 hover:border-indigo-400/50 backdrop-blur-sm"
                        x-tooltip="Join our Discord community to share your actual play"
                    >
                        <div class="flex flex-col items-center">
                            <div class="w-12 h-12 mb-4 rounded-xl bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center group-hover:scale-110 transition-transform shadow-lg">
                                <x-icons.discord class="w-6 h-6 text-white" />
                            </div>
                            <h3 class="font-outfit font-bold text-white mb-2">Join Discord</h3>
                            <p class="text-slate-300 text-sm">Share your campaign</p>
                        </div>
                    </a>
                    
                    <a 
                        href="https://www.reddit.com/r/daggerheart/" 
                        target="_blank" 
                        rel="noopener noreferrer" 
                        class="group p-6 bg-gradient-to-br from-slate-800/40 to-slate-700/40 hover:from-slate-700/60 hover:to-slate-600/60 rounded-2xl transition-all duration-300 border border-slate-600/50 hover:border-orange-400/50 backdrop-blur-sm"
                        x-tooltip="Visit the Daggerheart subreddit"
                    >
                        <div class="flex flex-col items-center">
                            <div class="w-12 h-12 mb-4 rounded-xl bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center group-hover:scale-110 transition-transform shadow-lg">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0zm5.01 4.744c.688 0 1.25.561 1.25 1.249a1.25 1.25 0 0 1-2.498.056l-2.597-.547-.8 3.747c1.824.07 3.48.632 4.674 1.488.308-.309.73-.491 1.207-.491.968 0 1.754.786 1.754 1.754 0 .716-.435 1.333-1.01 1.614a3.111 3.111 0 0 1 .042.52c0 2.694-3.13 4.87-7.004 4.87-3.874 0-7.004-2.176-7.004-4.87 0-.183.015-.366.043-.534A1.748 1.748 0 0 1 4.028 12c0-.968.786-1.754 1.754-1.754.463 0 .898.196 1.207.49 1.207-.883 2.878-1.43 4.744-1.487l.885-4.182a.342.342 0 0 1 .14-.197.35.35 0 0 1 .238-.042l2.906.617a1.214 1.214 0 0 1 1.108-.701zM9.25 12C8.561 12 8 12.562 8 13.25c0 .687.561 1.248 1.25 1.248.687 0 1.248-.561 1.248-1.249 0-.688-.561-1.249-1.249-1.249zm5.5 0c-.687 0-1.248.561-1.248 1.25 0 .687.561 1.248 1.249 1.248.688 0 1.249-.561 1.249-1.249 0-.687-.562-1.249-1.25-1.249zm-5.466 3.99a.327.327 0 0 0-.231.094.33.33 0 0 0 0 .463c.842.842 2.484.913 2.961.913.477 0 2.105-.056 2.961-.913a.361.361 0 0 0 .029-.463.33.33 0 0 0-.464 0c-.547.533-1.684.73-2.512.73-.828 0-1.979-.196-2.512-.73a.326.326 0 0 0-.232-.095z"/>
                                </svg>
                            </div>
                            <h3 class="font-outfit font-bold text-white mb-2">r/daggerheart</h3>
                            <p class="text-slate-300 text-sm">Community discussions</p>
                        </div>
                    </a>
                </div>
                
                <div class="max-w-2xl mx-auto">
                    <div class="p-6 bg-gradient-to-r from-emerald-500/10 to-cyan-500/10 rounded-2xl border border-emerald-400/20 backdrop-blur-sm">
                        <p class="text-emerald-300 font-medium mb-2">
                            🎲 Creator Recognition
                        </p>
                        <p class="text-slate-300 text-sm">
                            Special thanks to <strong>DMDanT (u/ShiaLovekraft)</strong> for curating this comprehensive list of Daggerheart actual plays. Their dedication to the community helps showcase the incredible diversity of stories being told in this amazing system!
                        </p>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-layout>
