<x-layout>
    <div class="min-h-screen">
        <div class="px-4 sm:px-6 lg:px-8 pt-12 pb-16">
            <div class="max-w-4xl mx-auto">
                <!-- Header -->
                <div class="flex items-start justify-between mb-8">
                    <div>
                        <h1 class="font-outfit text-4xl text-white tracking-wide mb-2">
                            {{ $frame->name }}
                        </h1>
                        <p class="text-slate-300 text-lg mb-4">{{ $frame->description }}</p>
                        <div class="flex items-center space-x-4 text-sm text-slate-400">
                            <div class="flex items-center">
                                <div class="w-2 h-2 rounded-full mr-2 {{ $frame->complexity_rating->value === 1 ? 'bg-green-400' : ($frame->complexity_rating->value === 2 ? 'bg-yellow-400' : ($frame->complexity_rating->value === 3 ? 'bg-orange-400' : 'bg-red-400')) }}"></div>
                                {{ $frame->complexity_rating->label() }} Complexity
                            </div>
                            @if($frame->is_public)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Public
                                </span>
                            @endif
                            <span>by {{ $frame->creator?->username ?? 'Unknown' }}</span>
                            @if($usage_count > 0)
                                <span>Used in {{ $usage_count }} campaign{{ $usage_count !== 1 ? 's' : '' }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex space-x-3">
                        @if($can_edit)
                            <a href="{{ route('campaign-frames.edit', $frame->id) }}" class="inline-flex items-center justify-center bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-black font-semibold py-2 px-4 rounded-xl transition-all duration-300">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Edit
                            </a>
                        @endif
                        <a href="{{ route('campaign-frames.index') }}" class="inline-flex items-center justify-center bg-slate-700 hover:bg-slate-600 text-white font-semibold py-2 px-4 rounded-xl transition-all duration-300">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Back
                        </a>
                    </div>
                </div>

                <div class="space-y-8">
                    <!-- Pitch -->
                    @if(!empty($frame->pitch))
                        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                            <h2 class="font-outfit text-xl text-white mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-3 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2M7 4h10M7 4L5.5 6M17 4l1.5 2M9 12l2 2 4-4" />
                                </svg>
                                The Pitch
                            </h2>
                            <ul class="space-y-2">
                                @foreach($frame->pitch as $point)
                                    @if(!empty($point))
                                        <li class="text-slate-300 flex items-start">
                                            <svg class="w-4 h-4 mr-3 mt-0.5 text-purple-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                            {{ $point }}
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Touchstones -->
                    @if(!empty($frame->touchstones))
                        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                            <h2 class="font-outfit text-xl text-white mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-3 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2h3a1 1 0 011 1v1a1 1 0 01-1 1h-1v12a2 2 0 01-2 2H6a2 2 0 01-2-2V7H3a1 1 0 01-1-1V5a1 1 0 011-1h3z" />
                                </svg>
                                Touchstones
                            </h2>
                            <div class="flex flex-wrap gap-2">
                                @foreach($frame->touchstones as $touchstone)
                                    @if(!empty($touchstone))
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                                            {{ $touchstone }}
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Tone & Themes -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Tone & Feel -->
                        @if(!empty($frame->tone))
                            <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                                <h2 class="font-outfit text-xl text-white mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-3 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                                    </svg>
                                    Tone & Feel
                                </h2>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($frame->tone as $tone_item)
                                        @if(!empty($tone_item))
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-500/20 text-blue-300 border border-blue-500/30">
                                                {{ $tone_item }}
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Themes -->
                        @if(!empty($frame->themes))
                            <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                                <h2 class="font-outfit text-xl text-white mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-3 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                    </svg>
                                    Themes
                                </h2>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($frame->themes as $theme)
                                        @if(!empty($theme))
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-500/20 text-green-300 border border-green-500/30">
                                                {{ $theme }}
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Background Overview -->
                    @if(!empty($frame->background_overview))
                        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                            <h2 class="font-outfit text-xl text-white mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                Background Overview
                            </h2>
                            <p class="text-slate-300 leading-relaxed">{{ $frame->background_overview }}</p>
                        </div>
                    @endif

                    <!-- Principles -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Player Principles -->
                        @if(!empty($frame->player_principles))
                            <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                                <h2 class="font-outfit text-xl text-white mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-3 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                                    </svg>
                                    Player Principles
                                </h2>
                                <ul class="space-y-2">
                                    @foreach($frame->player_principles as $principle)
                                        @if(!empty($principle))
                                            <li class="text-slate-300 flex items-start">
                                                <svg class="w-4 h-4 mr-3 mt-0.5 text-cyan-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                </svg>
                                                {{ $principle }}
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- GM Principles -->
                        @if(!empty($frame->gm_principles))
                            <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                                <h2 class="font-outfit text-xl text-white mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-3 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    GM Principles
                                </h2>
                                <ul class="space-y-2">
                                    @foreach($frame->gm_principles as $principle)
                                        @if(!empty($principle))
                                            <li class="text-slate-300 flex items-start">
                                                <svg class="w-4 h-4 mr-3 mt-0.5 text-orange-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                </svg>
                                                {{ $principle }}
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    <!-- Heritage Guidance -->
                    @if(!empty($frame->community_guidance) || !empty($frame->ancestry_guidance) || !empty($frame->class_guidance))
                        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                            <h2 class="font-outfit text-xl text-white mb-6 flex items-center">
                                <svg class="w-5 h-5 mr-3 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                Heritage Guidance
                            </h2>
                            
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                <!-- Communities -->
                                @if(!empty($frame->community_guidance))
                                    <div>
                                        <h3 class="font-semibold text-yellow-300 mb-3 flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                            Communities
                                        </h3>
                                        <ul class="space-y-2">
                                            @foreach($frame->community_guidance as $guidance)
                                                @if(!empty($guidance))
                                                    <li class="text-slate-300 text-sm">{{ $guidance }}</li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <!-- Ancestries -->
                                @if(!empty($frame->ancestry_guidance))
                                    <div>
                                        <h3 class="font-semibold text-yellow-300 mb-3 flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            Ancestries
                                        </h3>
                                        <ul class="space-y-2">
                                            @foreach($frame->ancestry_guidance as $guidance)
                                                @if(!empty($guidance))
                                                    <li class="text-slate-300 text-sm">{{ $guidance }}</li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <!-- Classes -->
                                @if(!empty($frame->class_guidance))
                                    <div>
                                        <h3 class="font-semibold text-yellow-300 mb-3 flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                                            </svg>
                                            Classes
                                        </h3>
                                        <ul class="space-y-2">
                                            @foreach($frame->class_guidance as $guidance)
                                                @if(!empty($guidance))
                                                    <li class="text-slate-300 text-sm">{{ $guidance }}</li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Setting Guidance & Distinctions -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Setting Guidance -->
                        @if(!empty($frame->setting_guidance))
                            <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                                <h2 class="font-outfit text-xl text-white mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-3 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                    </svg>
                                    Setting Guidance
                                </h2>
                                <ul class="space-y-2">
                                    @foreach($frame->setting_guidance as $guidance)
                                        @if(!empty($guidance))
                                            <li class="text-slate-300 flex items-start">
                                                <svg class="w-4 h-4 mr-3 mt-0.5 text-blue-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                                </svg>
                                                {{ $guidance }}
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Setting Distinctions -->
                        @if(!empty($frame->setting_distinctions))
                            <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                                <h2 class="font-outfit text-xl text-white mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-3 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                    </svg>
                                    Setting Distinctions
                                </h2>
                                <ul class="space-y-2">
                                    @foreach($frame->setting_distinctions as $distinction)
                                        @if(!empty($distinction))
                                            <li class="text-slate-300 flex items-start">
                                                <svg class="w-4 h-4 mr-3 mt-0.5 text-pink-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                </svg>
                                                {{ $distinction }}
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    <!-- Inciting Incident -->
                    @if(!empty($frame->inciting_incident))
                        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                            <h2 class="font-outfit text-xl text-white mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-3 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                Inciting Incident
                            </h2>
                            <p class="text-slate-300 leading-relaxed">{{ $frame->inciting_incident }}</p>
                        </div>
                    @endif

                    <!-- Campaign Mechanics -->
                    @if(!empty($frame->campaign_mechanics))
                        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                            <h2 class="font-outfit text-xl text-white mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-3 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Campaign Mechanics
                            </h2>
                            <ul class="space-y-3">
                                @foreach($frame->campaign_mechanics as $mechanic)
                                    @if(!empty($mechanic))
                                        <li class="text-slate-300 flex items-start">
                                            <svg class="w-4 h-4 mr-3 mt-0.5 text-violet-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" />
                                            </svg>
                                            {{ $mechanic }}
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Legacy Special Mechanics (for backwards compatibility) -->
                    @if(!empty($frame->special_mechanics))
                        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                            <h2 class="font-outfit text-xl text-white mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-3 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                </svg>
                                Special Mechanics
                            </h2>
                            <div class="space-y-4">
                                @foreach($frame->special_mechanics as $mechanic)
                                    @if(is_array($mechanic))
                                        @if(!empty($mechanic['name']) || !empty($mechanic['description']))
                                            <div class="border-l-4 border-orange-400/50 pl-4">
                                                @if(!empty($mechanic['name']))
                                                    <h3 class="font-semibold text-orange-300 mb-2">{{ $mechanic['name'] }}</h3>
                                                @endif
                                                @if(!empty($mechanic['description']))
                                                    <p class="text-slate-300">{{ $mechanic['description'] }}</p>
                                                @endif
                                            </div>
                                        @endif
                                    @else
                                        @if(!empty($mechanic))
                                            <div class="border-l-4 border-orange-400/50 pl-4">
                                                <p class="text-slate-300">{{ $mechanic }}</p>
                                            </div>
                                        @endif
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Session Zero Questions -->
                    @if(!empty($frame->session_zero_questions))
                        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                            <h2 class="font-outfit text-xl text-white mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-3 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Session Zero Questions
                            </h2>
                            <ul class="space-y-3">
                                @foreach($frame->session_zero_questions as $question)
                                    @if(!empty($question))
                                        <li class="text-slate-300 flex items-start">
                                            <svg class="w-4 h-4 mr-3 mt-0.5 text-teal-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                            </svg>
                                            {{ $question }}
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layout>