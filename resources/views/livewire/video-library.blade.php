<div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950" x-data="{ slideoverOpen: false }">
    {{-- Dark Header --}}
    <div class="bg-slate-900/80 backdrop-blur-xl border-b border-slate-700/50 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                {{-- Title --}}
                <div>
                    <h1 class="text-xl font-outfit font-bold text-white">Video Library</h1>
                    <p class="text-slate-400 text-xs mt-0.5">Manage your recorded sessions</p>
                </div>

                {{-- View Toggle & Actions --}}
                <div class="flex items-center space-x-3">
                    {{-- View Mode Toggle --}}
                    <div class="bg-slate-800/50 rounded-lg p-1 flex items-center">
                        <button wire:click="setViewMode('list')" 
                                class="px-3 py-1.5 rounded-md text-xs font-medium transition-all duration-200 flex items-center space-x-1.5
                                    {{ $viewMode === 'list' ? 'bg-gradient-to-r from-amber-500 to-orange-500 text-white shadow-lg' : 'text-slate-300 hover:text-white hover:bg-slate-700/50' }}">
                            <x-icons.video class="w-3 h-3" />
                            <span>List</span>
                        </button>
                        
                        <button wire:click="setViewMode('grid')" 
                                class="px-3 py-1.5 rounded-md text-xs font-medium transition-all duration-200 flex items-center space-x-1.5
                                    {{ $viewMode === 'grid' ? 'bg-gradient-to-r from-amber-500 to-orange-500 text-white shadow-lg' : 'text-slate-300 hover:text-white hover:bg-slate-700/50' }}">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                            <span>Grid</span>
                        </button>
                        
                        <button wire:click="setViewMode('rooms')" 
                                class="px-3 py-1.5 rounded-md text-xs font-medium transition-all duration-200 flex items-center space-x-1.5
                                    {{ $viewMode === 'rooms' ? 'bg-gradient-to-r from-amber-500 to-orange-500 text-white shadow-lg' : 'text-slate-300 hover:text-white hover:bg-slate-700/50' }}">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <span>Rooms</span>
                        </button>
                    </div>

                    {{-- Analytics Button --}}
                    <button wire:click="toggleAnalytics" 
                            class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all duration-200 flex items-center space-x-1.5 border
                                {{ $showAnalytics ? 'bg-gradient-to-r from-violet-500 to-purple-500 text-white shadow-lg border-violet-500/30' : 'bg-slate-800/50 text-slate-300 hover:text-white hover:bg-slate-700/50 border-slate-600/50' }}">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <span>Analytics</span>
                        </button>

                    {{-- Filters Button --}}
                    <button wire:click="toggleFilters" 
                            class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all duration-200 flex items-center space-x-1.5 border
                                {{ $showFilters ? 'bg-gradient-to-r from-emerald-500 to-teal-500 text-white shadow-lg border-emerald-500/30' : 'bg-slate-800/50 text-slate-300 hover:text-white hover:bg-slate-700/50 border-slate-600/50' }}">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            <span>Filters</span>
                        </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        {{-- Search & Filters --}}
        <div class="mb-6">
            {{-- Search Bar --}}
            <div class="relative mb-4">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" 
                       wire:model.live.debounce.300ms="searchQuery"
                       placeholder="Search recordings by room name or description..."
                       class="block w-full pl-10 pr-4 py-2.5 bg-slate-800/50 border border-slate-600 rounded-lg text-white placeholder-slate-400 focus:ring-2 focus:ring-amber-500 focus:border-transparent text-sm">
            </div>

            {{-- Filters Panel --}}
            @if($showFilters)
                <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-xl p-4 mb-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                        {{-- Provider Filter --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1">Provider</label>
                            <select wire:model.live="selectedProvider" 
                                    class="block w-full px-3 py-2 bg-slate-800/50 border border-slate-600 rounded-lg text-white focus:ring-2 focus:ring-amber-500 focus:border-transparent text-sm">
                                <option value="all">All Providers</option>
                                <option value="wasabi">Wasabi Cloud</option>
                                <option value="google_drive">Google Drive</option>
                            </select>
                        </div>

                        {{-- Status Filter --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1">Status</label>
                            <select wire:model.live="selectedStatus" 
                                    class="block w-full px-3 py-2 bg-slate-800/50 border border-slate-600 rounded-lg text-white focus:ring-2 focus:ring-amber-500 focus:border-transparent text-sm">
                                <option value="all">All Status</option>
                                <option value="ready">Ready</option>
                                <option value="processing">Processing</option>
                                <option value="failed">Failed</option>
                            </select>
                        </div>

                        {{-- Date Range Filter --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1">Date Range</label>
                            <select wire:model.live="selectedDateRange" 
                                    class="block w-full px-3 py-2 bg-slate-800/50 border border-slate-600 rounded-lg text-white focus:ring-2 focus:ring-amber-500 focus:border-transparent text-sm">
                                <option value="all">All Time</option>
                                <option value="today">Today</option>
                                <option value="week">This Week</option>
                                <option value="month">This Month</option>
                                <option value="custom">Custom Range</option>
                            </select>
                        </div>

                        {{-- Sort Order --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1">Sort By</label>
                            <select wire:model.live="sortBy" 
                                    class="block w-full px-3 py-2 bg-slate-800/50 border border-slate-600 rounded-lg text-white focus:ring-2 focus:ring-amber-500 focus:border-transparent text-sm">
                                <option value="newest">Newest First</option>
                                <option value="oldest">Oldest First</option>
                                <option value="largest">Largest First</option>
                                <option value="smallest">Smallest First</option>
                            </select>
                        </div>
                    </div>

                    {{-- Custom Date Range --}}
                    @if($selectedDateRange === 'custom')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-slate-600/50">
                            <div>
                                <label class="block text-xs font-medium text-slate-300 mb-1">Start Date</label>
                                <input type="date" wire:model.live="customStartDate" 
                                       class="block w-full px-3 py-2 bg-slate-800/50 border border-slate-600 rounded-lg text-white focus:ring-2 focus:ring-amber-500 focus:border-transparent text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-300 mb-1">End Date</label>
                                <input type="date" wire:model.live="customEndDate" 
                                       class="block w-full px-3 py-2 bg-slate-800/50 border border-slate-600 rounded-lg text-white focus:ring-2 focus:ring-amber-500 focus:border-transparent text-sm">
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Analytics Panel --}}
        @if($showAnalytics)
            @include('livewire.video-library.analytics')
        @endif

        {{-- Content Views --}}
        @if($viewMode === 'list')
            @include('livewire.video-library.list-view')
        @elseif($viewMode === 'grid')
            @include('livewire.video-library.grid-view')
        @elseif($viewMode === 'rooms')
            @include('livewire.video-library.rooms-view')
        @endif

        {{-- Empty State --}}
        @if($recordings->isEmpty() && !$showAnalytics)
            <div class="text-center py-12">
                <div class="w-16 h-16 bg-slate-800/50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <x-icons.video class="w-8 h-8 text-slate-400" />
                </div>
                <h3 class="text-lg font-medium text-white mb-2">No recordings found</h3>
                <p class="text-slate-400 mb-4 text-sm">Start recording in your rooms to see them here</p>
                <a href="{{ route('rooms.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-500 text-white rounded-lg hover:from-amber-600 hover:to-orange-600 transition-all duration-200 font-medium text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    Browse Rooms
                </a>
            </div>
        @endif
    </div>

    {{-- Recording Detail Slideover --}}
    @include('livewire.video-library.recording-modal')
</div>

@script
<script>
    // Format duration helper
    window.formatDuration = function(milliseconds) {
        const totalSeconds = Math.floor(milliseconds / 1000);
        const hours = Math.floor(totalSeconds / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;

        if (hours > 0) {
            return hours + ':' + minutes.toString().padStart(2, '0') + ':' + seconds.toString().padStart(2, '0');
        } else {
            return minutes + ':' + (seconds % 60).toString().padStart(2, '0');
        }
    };

    // Thumbnail component function for Alpine.js
    window.thumbnailComponent = function(recordingId, hasThumbnail, canGenerate) {
        // Initialize the tracking object if it doesn't exist
        if (!window.thumbnailAutoGenerated) {
            window.thumbnailAutoGenerated = new Set();
        }

        const component = {
            recordingId: recordingId,
            generating: false,
            hasThumbnail: hasThumbnail,
            canGenerate: canGenerate,

            init() {
                console.log('Thumbnail component initialized for recording:', this.recordingId, '- hasThumbnail:', this.hasThumbnail, 'canGenerate:', this.canGenerate);
                
                // Auto-generate thumbnail only if we don't have one, can generate, and haven't already started generation
                if (!this.hasThumbnail && this.canGenerate && !window.thumbnailAutoGenerated.has(this.recordingId)) {
                    console.log('Auto-generating thumbnail for recording:', this.recordingId);
                    window.thumbnailAutoGenerated.add(this.recordingId);
                    this.autoGenerateThumbnail();
                } else if (this.hasThumbnail) {
                    console.log('Thumbnail already exists for recording:', this.recordingId, '- skipping generation');
                } else if (!this.canGenerate) {
                    console.log('Cannot generate thumbnail for recording:', this.recordingId, '- status not ready');
                } else if (window.thumbnailAutoGenerated.has(this.recordingId)) {
                    console.log('Thumbnail auto-generation already started for recording:', this.recordingId, '- skipping duplicate');
                }
            },

            async generateThumbnail() {
                if (this.generating) {
                    console.log('Already generating thumbnail for recording:', this.recordingId);
                    return;
                }
                
                console.log('Starting thumbnail generation for recording:', this.recordingId);
                this.generating = true;
                
                try {
                    console.log('Getting stream URL for recording:', this.recordingId);
                    
                    // Get stream URL from Livewire
                    const streamUrl = await $wire.generateThumbnailForRecording(this.recordingId);
                    
                    if (!streamUrl) {
                        throw new Error('Failed to get stream URL');
                    }
                    
                    console.log('Got stream URL, generating thumbnail...');
                    
                    // Generate thumbnail using the global generator
                    if (window.videoThumbnailGenerator) {
                        const thumbnailBase64 = await window.videoThumbnailGenerator.generateThumbnail(streamUrl);
                        
                        console.log('Thumbnail generated, uploading...');
                        
                        // Upload thumbnail
                        const response = await fetch('/api/recordings/thumbnail', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                recording_id: this.recordingId,
                                thumbnail: thumbnailBase64
                            })
                        });

                        if (response.ok) {
                            const result = await response.json();
                            console.log('Thumbnail uploaded successfully:', result.thumbnail_url);
                            
                            // Update the component state immediately
                            this.hasThumbnail = true;
                            
                            // Update the thumbnail image in the DOM immediately
                            const thumbnailContainer = this.$el;
                            const existingImg = thumbnailContainer.querySelector('img');
                            if (existingImg) {
                                existingImg.src = result.thumbnail_url;
                                existingImg.style.display = 'block';
                            } else {
                                // Create new image element
                                const img = document.createElement('img');
                                img.src = result.thumbnail_url;
                                img.alt = 'Recording thumbnail';
                                img.className = 'w-full h-full object-cover';
                                thumbnailContainer.appendChild(img);
                            }
                            
                            // Also refresh the component to update server state
                            $wire.$refresh();
                        } else {
                            const errorText = await response.text();
                            console.error('Upload response error:', errorText);
                            throw new Error('Failed to upload thumbnail: ' + response.status);
                        }
                    } else {
                        throw new Error('Video thumbnail generator not available');
                    }
                } catch (error) {
                    console.error('Failed to generate thumbnail:', error);
                    // Don't show alert for auto-generation failures, but log the error
                } finally {
                    console.log('Thumbnail generation completed for recording:', this.recordingId);
                    this.generating = false;
                }
            },

            async autoGenerateThumbnail() {
                console.log('Auto-generating thumbnail for recording:', this.recordingId);
                // Add a small delay to avoid overwhelming the server
                setTimeout(() => {
                    this.generateThumbnail();
                }, Math.random() * 2000 + 500); // Random delay between 500ms and 2.5s
            }
        };

        return component;
    };
</script>
@endscript