<div class="space-y-6">
    {{-- Header Section --}}
    <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700 rounded-xl p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-2xl font-outfit font-bold text-white">Video Library</h1>
                <p class="text-slate-300 mt-2">Your recorded room sessions and video content</p>
            </div>
            <div class="flex items-center space-x-3">
                {{-- View Mode Toggle --}}
                <div class="flex bg-slate-800 rounded-lg p-1">
                    <button wire:click="setViewMode('list')" 
                            class="px-3 py-1 text-sm font-medium rounded transition-all duration-200 {{ $viewMode === 'list' ? 'bg-amber-500 text-black' : 'text-slate-400 hover:text-white' }}">
                        <i class="fas fa-list mr-1"></i>List
                    </button>
                    <button wire:click="setViewMode('grid')" 
                            class="px-3 py-1 text-sm font-medium rounded transition-all duration-200 {{ $viewMode === 'grid' ? 'bg-amber-500 text-black' : 'text-slate-400 hover:text-white' }}">
                        <i class="fas fa-th mr-1"></i>Grid
                    </button>
                    <button wire:click="setViewMode('rooms')" 
                            class="px-3 py-1 text-sm font-medium rounded transition-all duration-200 {{ $viewMode === 'rooms' ? 'bg-amber-500 text-black' : 'text-slate-400 hover:text-white' }}">
                        <i class="fas fa-door-open mr-1"></i>Rooms
                    </button>
                </div>
                
                {{-- Analytics Toggle --}}
                <button wire:click="toggleAnalytics" 
                        class="px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-500 hover:from-indigo-600 hover:to-purple-600 text-white rounded-lg transition-all duration-200 font-medium">
                    <i class="fas fa-chart-bar mr-2"></i>Analytics
                </button>
                
                {{-- Filters Toggle --}}
                <button wire:click="toggleFilters" 
                        class="px-4 py-2 bg-gradient-to-r from-slate-600 to-slate-700 hover:from-slate-500 hover:to-slate-600 text-white rounded-lg transition-all duration-200 font-medium">
                    <i class="fas fa-filter mr-2"></i>Filters
                </button>
            </div>
        </div>

        {{-- Search Bar --}}
        <div class="relative mb-4">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-slate-400"></i>
            </div>
            <input type="text" 
                   wire:model.live.debounce.300ms="searchQuery"
                   placeholder="Search recordings by room name or description..."
                   class="block w-full pl-10 pr-4 py-3 bg-slate-800/50 border border-slate-600 rounded-lg text-white placeholder-slate-400 focus:ring-2 focus:ring-amber-500 focus:border-transparent">
        </div>

        {{-- Filters Panel --}}
        @if($showFilters)
            <div class="border-t border-slate-600 pt-4 mt-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    {{-- Provider Filter --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Provider</label>
                        <select wire:model.live="selectedProvider" 
                                class="w-full bg-slate-800 border border-slate-600 rounded-lg text-white focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                            @foreach($this->providerOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Status Filter --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Status</label>
                        <select wire:model.live="selectedStatus" 
                                class="w-full bg-slate-800 border border-slate-600 rounded-lg text-white focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                            @foreach($this->statusOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Date Range Filter --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Date Range</label>
                        <select wire:model.live="selectedDateRange" 
                                class="w-full bg-slate-800 border border-slate-600 rounded-lg text-white focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                            @foreach($this->dateRangeOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Clear Filters --}}
                    <div class="flex items-end">
                        <button wire:click="clearFilters" 
                                class="w-full px-4 py-2 bg-slate-600 hover:bg-slate-500 text-white rounded-lg transition-colors duration-200 font-medium">
                            <i class="fas fa-times mr-2"></i>Clear Filters
                        </button>
                    </div>
                </div>

                {{-- Custom Date Range --}}
                @if($selectedDateRange === 'custom')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Start Date</label>
                            <input type="date" 
                                   wire:model.live="customStartDate"
                                   class="w-full bg-slate-800 border border-slate-600 rounded-lg text-white focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">End Date</label>
                            <input type="date" 
                                   wire:model.live="customEndDate"
                                   class="w-full bg-slate-800 border border-slate-600 rounded-lg text-white focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- Analytics Panel --}}
    @if($showAnalytics && !empty($storageAnalytics))
        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700 rounded-xl p-6">
            <h2 class="text-xl font-outfit font-bold text-white mb-4">Storage Analytics</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                {{-- Total Recordings --}}
                <div class="bg-gradient-to-br from-blue-500/20 to-indigo-500/20 border border-blue-500/30 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-300 text-sm font-medium">Total Recordings</p>
                            <p class="text-white text-2xl font-bold">{{ number_format($storageAnalytics['total_recordings']) }}</p>
                        </div>
                        <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center">
                            <i class="fas fa-video text-blue-400"></i>
                        </div>
                    </div>
                </div>

                {{-- Total Size --}}
                <div class="bg-gradient-to-br from-emerald-500/20 to-green-500/20 border border-emerald-500/30 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-emerald-300 text-sm font-medium">Total Size</p>
                            <p class="text-white text-2xl font-bold">{{ $this->formatBytes($storageAnalytics['total_size_bytes']) }}</p>
                        </div>
                        <div class="w-10 h-10 bg-emerald-500/20 rounded-lg flex items-center justify-center">
                            <i class="fas fa-hdd text-emerald-400"></i>
                        </div>
                    </div>
                </div>

                {{-- Total Duration --}}
                <div class="bg-gradient-to-br from-purple-500/20 to-pink-500/20 border border-purple-500/30 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-300 text-sm font-medium">Total Duration</p>
                            <p class="text-white text-2xl font-bold">{{ $this->formatDuration($storageAnalytics['total_duration_ms']) }}</p>
                        </div>
                        <div class="w-10 h-10 bg-purple-500/20 rounded-lg flex items-center justify-center">
                            <i class="fas fa-clock text-purple-400"></i>
                        </div>
                    </div>
                </div>

                {{-- Average Size --}}
                <div class="bg-gradient-to-br from-amber-500/20 to-orange-500/20 border border-amber-500/30 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-amber-300 text-sm font-medium">Average Size</p>
                            <p class="text-white text-2xl font-bold">{{ $this->formatBytes($storageAnalytics['average_size_bytes']) }}</p>
                        </div>
                        <div class="w-10 h-10 bg-amber-500/20 rounded-lg flex items-center justify-center">
                            <i class="fas fa-balance-scale text-amber-400"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Provider Breakdown --}}
            @if(!empty($storageAnalytics['by_provider']))
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-medium text-white mb-3">By Provider</h3>
                        <div class="space-y-2">
                            @foreach($storageAnalytics['by_provider'] as $provider => $stats)
                                <div class="flex items-center justify-between p-3 bg-slate-800/50 rounded-lg">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3
                                            @if($provider === 'wasabi') bg-orange-500/20
                                            @elseif($provider === 'google_drive') bg-blue-500/20  
                                            @else bg-slate-500/20 @endif">
                                            @if($provider === 'wasabi')
                                                <i class="fas fa-cloud text-orange-400"></i>
                                            @elseif($provider === 'google_drive')
                                                <i class="fab fa-google-drive text-blue-400"></i>
                                            @else
                                                <i class="fas fa-server text-slate-400"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="text-white font-medium">{{ ucfirst(str_replace('_', ' ', $provider)) }}</p>
                                            <p class="text-slate-400 text-sm">{{ $stats['count'] }} recordings</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-white font-medium">{{ $this->formatBytes($stats['size_bytes']) }}</p>
                                        <p class="text-slate-400 text-sm">{{ $this->formatDuration($stats['duration_ms']) }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-medium text-white mb-3">By Status</h3>
                        <div class="space-y-2">
                            @foreach($storageAnalytics['by_status'] as $status => $stats)
                                <div class="flex items-center justify-between p-3 bg-slate-800/50 rounded-lg">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3
                                            @if($status === 'ready' || $status === 'uploaded') bg-emerald-500/20
                                            @elseif($status === 'processing') bg-blue-500/20  
                                            @else bg-red-500/20 @endif">
                                            @if($status === 'ready' || $status === 'uploaded')
                                                <i class="fas fa-check text-emerald-400"></i>
                                            @elseif($status === 'processing')
                                                <i class="fas fa-spinner text-blue-400"></i>
                                            @else
                                                <i class="fas fa-exclamation text-red-400"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="text-white font-medium">{{ ucfirst($status) }}</p>
                                            <p class="text-slate-400 text-sm">{{ $stats['count'] }} recordings</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-white font-medium">{{ $this->formatBytes($stats['size_bytes']) }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- Main Content --}}
    <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700 rounded-xl p-6">
        @if($recordings->isEmpty())
            {{-- Empty State --}}
            <div class="text-center py-12 text-slate-400">
                <i class="fas fa-video text-6xl mb-4 opacity-50"></i>
                @if(!empty($searchQuery))
                    <h3 class="text-xl font-medium text-white mb-2">No recordings found</h3>
                    <p class="mb-4">No recordings match your search criteria</p>
                    <button wire:click="clearFilters" 
                            class="px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-500 text-white rounded-lg hover:from-amber-600 hover:to-orange-600 transition-all duration-200 font-medium">
                        Clear Search
                    </button>
                @else
                    <h3 class="text-xl font-medium text-white mb-2">No recordings yet</h3>
                    <p class="mb-4">Start recording in your rooms to see videos here</p>
                    <a href="{{ route('rooms.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-500 text-white rounded-lg hover:from-amber-600 hover:to-orange-600 transition-all duration-200 font-medium">
                        <i class="fas fa-door-open mr-2"></i>Browse Rooms
                    </a>
                @endif
            </div>
        @else
            @if($viewMode === 'list')
                @include('livewire.video-library.list-view')
            @elseif($viewMode === 'grid')
                @include('livewire.video-library.grid-view')
            @elseif($viewMode === 'rooms')
                @include('livewire.video-library.rooms-view')
            @endif
        @endif
    </div>

    {{-- Recording Detail Modal --}}
    @if($this->selectedRecording)
        @include('livewire.video-library.recording-modal')
    @endif
</div>

@script
<script>
    // Format bytes helper
    $wire.formatBytes = function(bytes) {
        if (bytes === 0) return '0 B';
        const units = ['B', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(1024));
        return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + units[i];
    };

    // Format duration helper
    $wire.formatDuration = function(ms) {
        if (ms === 0) return '0:00';
        const seconds = Math.floor(ms / 1000);
        const minutes = Math.floor(seconds / 60);
        const hours = Math.floor(minutes / 60);
        
        if (hours > 0) {
            return hours + ':' + (minutes % 60).toString().padStart(2, '0') + ':' + (seconds % 60).toString().padStart(2, '0');
        } else {
            return minutes + ':' + (seconds % 60).toString().padStart(2, '0');
        }
    };
</script>
@endscript
