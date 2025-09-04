@if (!empty($subclassData))
    <div pest="subclass-features-section" class="bg-slate-900/60 border border-slate-800 rounded-xl shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-amber-500/20 to-orange-500/20 border-b border-amber-500/30 p-4">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <h2 class="text-xl font-bold text-white font-outfit mb-1">{{ $subclassData['name'] ?? 'Subclass' }} Features</h2>
                    <p class="text-slate-300 text-sm">{{ $subclassData['description'] ?? '' }}</p>
                </div>
            </div>
        </div>

        <div class="p-6">
            <!-- Responsive Grid: 3 columns on xl, stacked on lg and below -->
            <div class="grid grid-cols-1 lg:grid-cols-1 xl:grid-cols-3 gap-6">
                <!-- Foundation Features -->
                @if (!empty($subclassData['foundationFeatures']))
                    <div>
                        <h3 class="text-emerald-400 font-semibold text-base font-outfit mb-3">Foundation Features</h3>
                        <div class="space-y-3">
                            @foreach ($subclassData['foundationFeatures'] as $feature)
                                <div pest="foundation-feature-{{ $loop->index }}" class="bg-slate-800/30 border border-slate-700/50 rounded-lg p-4">
                                    <h4 class="text-white font-semibold text-sm mb-2">{{ $feature['name'] ?? '' }}</h4>
                                    <div class="text-slate-300 text-sm leading-relaxed prose prose-slate prose-sm max-w-none">
                                        {!! $feature['description'] ?? '' !!}
                                    </div>
                                    @if (!empty($feature['hopeCost']))
                                        <div class="mt-2 text-xs text-blue-400">
                                            Hope Cost: {{ $feature['hopeCost'] }}
                                        </div>
                                    @endif
                                    @if (!empty($feature['stressCost']))
                                        <div class="mt-2 text-xs text-orange-400">
                                            Stress Cost: {{ $feature['stressCost'] }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Specialization Features -->
                @if (!empty($subclassData['specializationFeatures']))
                    <div>
                        <h3 class="text-amber-400 font-semibold text-base font-outfit mb-3">Specialization Features</h3>
                        <div class="space-y-3">
                            @foreach ($subclassData['specializationFeatures'] as $feature)
                                <div pest="specialization-feature-{{ $loop->index }}" class="bg-gradient-to-r from-amber-500/10 to-orange-500/10 border border-amber-500/30 rounded-lg p-4">
                                    <h4 class="text-white font-semibold text-sm mb-2">{{ $feature['name'] ?? '' }}</h4>
                                    <div class="text-slate-300 text-sm leading-relaxed prose prose-slate prose-sm max-w-none">
                                        {!! $feature['description'] ?? '' !!}
                                    </div>
                                    @if (!empty($feature['hopeCost']))
                                        <div class="mt-2 text-xs text-blue-400">
                                            Hope Cost: {{ $feature['hopeCost'] }}
                                        </div>
                                    @endif
                                    @if (!empty($feature['stressCost']))
                                        <div class="mt-2 text-xs text-orange-400">
                                            Stress Cost: {{ $feature['stressCost'] }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Mastery Features -->
                @if (!empty($subclassData['masteryFeatures']))
                    <div>
                        <h3 class="text-purple-400 font-semibold text-base font-outfit mb-3">Mastery Features</h3>
                        <div class="space-y-3">
                            @foreach ($subclassData['masteryFeatures'] as $feature)
                                <div pest="mastery-feature-{{ $loop->index }}" class="bg-gradient-to-r from-purple-500/10 to-indigo-500/10 border border-purple-500/30 rounded-lg p-4">
                                    <h4 class="text-white font-semibold text-sm mb-2">{{ $feature['name'] ?? '' }}</h4>
                                    <div class="text-slate-300 text-sm leading-relaxed prose prose-slate prose-sm max-w-none">
                                        {!! $feature['description'] ?? '' !!}
                                    </div>
                                    @if (!empty($feature['hopeCost']))
                                        <div class="mt-2 text-xs text-blue-400">
                                            Hope Cost: {{ $feature['hopeCost'] }}
                                        </div>
                                    @endif
                                    @if (!empty($feature['stressCost']))
                                        <div class="mt-2 text-xs text-orange-400">
                                            Stress Cost: {{ $feature['stressCost'] }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif
