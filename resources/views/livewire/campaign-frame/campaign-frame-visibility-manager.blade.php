<div>
    @if($this->hasCampaignFrame() && $this->canManageVisibility())
        <!-- Visibility Manager Toggle -->
        <div class="mb-4">
            <button 
                wire:click="toggleManager"
                class="inline-flex items-center px-3 py-2 bg-slate-700 hover:bg-slate-600 text-white text-sm font-medium rounded-lg transition-colors border border-slate-600"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    @if($showManager)
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L12 12m0 0l2.122 2.122M12 12L9 9" />
                    @else
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    @endif
                </svg>
                {{ $showManager ? 'Hide' : 'Manage' }} Player Visibility
            </button>
        </div>

        <!-- Visibility Settings Panel -->
        @if($showManager)
            <div class="bg-slate-800/50 border border-slate-600/50 rounded-xl p-6 mb-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-outfit font-semibold text-white">Campaign Frame Visibility</h3>
                        <p class="text-slate-400 text-sm">Control which sections players can see</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($availableSections as $sectionKey => $sectionLabel)
                        <div class="bg-slate-700/50 border border-slate-600/30 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <h4 class="font-medium text-white text-sm">{{ $sectionLabel }}</h4>
                                    <p class="text-slate-400 text-xs mt-1">
                                        {{ $visibilitySettings[$sectionKey] ?? false ? 'Visible to players' : 'GM only' }}
                                    </p>
                                </div>
                                <button 
                                    wire:click="toggleSectionVisibility('{{ $sectionKey }}')"
                                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 focus:ring-offset-slate-800 {{ ($visibilitySettings[$sectionKey] ?? false) ? 'bg-amber-500' : 'bg-slate-600' }}"
                                    role="switch"
                                    aria-checked="{{ $visibilitySettings[$sectionKey] ?? false ? 'true' : 'false' }}"
                                >
                                    <span class="sr-only">Toggle {{ $sectionLabel }} visibility</span>
                                    <span 
                                        aria-hidden="true"
                                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ ($visibilitySettings[$sectionKey] ?? false) ? 'translate-x-5' : 'translate-x-0' }}"
                                    ></span>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 flex items-center justify-between">
                    <div class="text-sm text-slate-400">
                        <div class="flex items-center space-x-4">
                            <span class="flex items-center">
                                <span class="w-3 h-3 bg-amber-500 rounded-full mr-2"></span>
                                Visible to players
                            </span>
                            <span class="flex items-center">
                                <span class="w-3 h-3 bg-slate-600 rounded-full mr-2"></span>
                                GM only
                            </span>
                        </div>
                    </div>
                    <button 
                        wire:click="saveVisibilitySettings"
                        class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-white text-sm font-semibold rounded-lg transition-colors"
                    >
                        Save Changes
                    </button>
                </div>
            </div>
        @endif

        <!-- Flash Messages -->
        @if(session()->has('success'))
            <div 
                x-data="{ show: true }" 
                x-show="show" 
                x-transition
                x-init="setTimeout(() => show = false, 3000)"
                class="mb-4 bg-emerald-500/10 border border-emerald-500/30 rounded-lg p-4"
            >
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-emerald-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-emerald-400 text-sm">{{ session('success') }}</p>
                </div>
            </div>
        @endif
    @endif
</div>