<div>
    @if($this->hasVisibleContent())
        <!-- Campaign Frame Content Section -->
        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-gradient-to-br from-purple-500/20 to-indigo-500/20 rounded-xl flex items-center justify-center border border-purple-500/30 mr-3">
                        <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-outfit text-xl font-bold text-white">{{ $campaign->campaignFrame->name }}</h2>
                        <p class="text-slate-400 text-sm">Campaign Setting Guide</p>
                    </div>
                </div>
                @if($this->isCreator())
                    <div class="text-xs text-slate-500 bg-slate-800/50 px-3 py-1 rounded-full border border-slate-600/30">
                        GM View: Showing {{ count($visibleSections) }} sections
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @foreach($visibleSections as $section)
                    @php $content = $this->getSectionContent($section); @endphp
                    @if($content)
                        <div class="bg-slate-800/50 border border-slate-600/30 rounded-xl p-5">
                            <div class="flex items-start mb-4">
                                <div class="w-8 h-8 bg-purple-500/20 rounded-lg flex items-center justify-center border border-purple-500/30 mr-3 flex-shrink-0">
                                    <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $this->getSectionIcon($section) }}"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-outfit font-semibold text-white text-lg mb-2">
                                        {{ $availableSections[$section] }}
                                    </h3>
                                    
                                    <div class="text-slate-300 leading-relaxed">
                                        @if(in_array($section, ['pitch', 'touchstones', 'tone', 'themes', 'player_principles', 'gm_principles', 'setting_distinctions']) && is_array($content))
                                            @if($section === 'pitch')
                                                @foreach($content as $pitchPoint)
                                                    <div class="mb-3 last:mb-0">
                                                        <div class="flex items-start">
                                                            <span class="inline-block w-2 h-2 bg-purple-400 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                                                            <p class="text-sm">{{ $pitchPoint }}</p>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @elseif(in_array($section, ['tone', 'themes', 'touchstones']))
                                                <div class="flex flex-wrap gap-2">
                                                    @foreach($content as $item)
                                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-500/20 text-purple-300 border border-purple-500/30">
                                                            {{ $item }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @else
                                                @foreach($content as $item)
                                                    <div class="mb-3 last:mb-0">
                                                        <div class="flex items-start">
                                                            <span class="inline-block w-1.5 h-1.5 bg-purple-400 rounded-full mt-2.5 mr-3 flex-shrink-0"></span>
                                                            <p class="text-sm">{{ $item }}</p>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        @elseif($section === 'setting_guidance' && is_array($content))
                                            <div class="space-y-4">
                                                @foreach($content as $key => $values)
                                                    <div>
                                                        <h4 class="text-sm font-semibold text-purple-300 mb-2 capitalize">{{ str_replace('_', ' ', $key) }}</h4>
                                                        <div class="flex flex-wrap gap-2">
                                                            @if(is_array($values))
                                                                @foreach($values as $value)
                                                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-slate-700/50 text-slate-300 border border-slate-600/30">
                                                                        {{ $value }}
                                                                    </span>
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @elseif(in_array($section, ['special_mechanics', 'campaign_mechanics']) && is_array($content))
                                            <div class="space-y-3">
                                                @if(isset($content['name']))
                                                    <h4 class="text-purple-300 font-semibold">{{ $content['name'] }}</h4>
                                                @endif
                                                @if(isset($content['description']))
                                                    <p class="text-sm">{{ $content['description'] }}</p>
                                                @endif
                                            </div>
                                        @elseif($section === 'session_zero_questions' && is_array($content))
                                            @foreach($content as $index => $question)
                                                <div class="mb-3 last:mb-0">
                                                    <div class="flex items-start">
                                                        <span class="inline-flex items-center justify-center w-6 h-6 bg-purple-500/20 text-purple-300 rounded-full text-xs font-semibold mr-3 flex-shrink-0 mt-0.5">
                                                            {{ $index + 1 }}
                                                        </span>
                                                        <p class="text-sm">{{ $question }}</p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <p class="text-sm">{{ $this->formatSectionContent($content) }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            @if($this->isCreator() && count($visibleSections) < count($availableSections))
                <div class="mt-6 p-4 bg-slate-800/30 border border-slate-600/30 rounded-lg">
                    <div class="flex items-center text-slate-400 text-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Some sections are hidden from players. Use the visibility manager above to control what players can see.
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>