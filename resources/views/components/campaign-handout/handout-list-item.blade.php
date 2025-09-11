@props(['handout', 'canEdit' => false])

<div class="bg-slate-800/50 rounded-lg border border-slate-700 hover:border-amber-400 transition-colors p-4">
    <div class="flex items-center justify-between">
        <!-- File Info -->
        <div class="flex items-center space-x-4 flex-1">
            <!-- File Icon -->
            <div class="w-12 h-12 bg-slate-700 rounded-lg flex items-center justify-center">
                @if($handout->isPreviewableImage())
                    <img src="{{ $handout->file_url }}" 
                         alt="{{ $handout->title }}"
                         class="w-full h-full object-cover rounded-lg cursor-pointer"
                         @click="$wire.showPreview({{ $handout->id }})">
                @else
                    <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $handout->file_type->icon() }}" />
                    </svg>
                @endif
            </div>

            <!-- File Details -->
            <div class="flex-1 min-w-0">
                <div class="flex items-center space-x-2 mb-1">
                    <h3 class="font-medium text-white truncate">{{ $handout->title }}</h3>
                    
                    <!-- Badges -->
                    <x-badge variant="secondary" class="text-xs">
                        {{ strtoupper($handout->file_type->value) }}
                    </x-badge>
                    
                    <x-badge :variant="$handout->access_level === \Domain\CampaignHandout\Enums\HandoutAccessLevel::GM_ONLY ? 'danger' : 
                                      ($handout->access_level === \Domain\CampaignHandout\Enums\HandoutAccessLevel::ALL_PLAYERS ? 'success' : 'warning')" 
                             class="text-xs">
                        {{ $handout->access_level->label() }}
                    </x-badge>

                    @if($handout->is_visible_in_sidebar)
                        <x-badge variant="info" class="text-xs">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Sidebar
                        </x-badge>
                    @endif
                </div>
                
                @if($handout->description)
                    <p class="text-slate-400 text-sm mb-2 line-clamp-1">{{ $handout->description }}</p>
                @endif
                
                <div class="flex items-center space-x-4 text-xs text-slate-500">
                    <span>{{ $handout->formatted_file_size }}</span>
                    <span>{{ $handout->original_file_name }}</span>
                    <span>{{ \Carbon\Carbon::parse($handout->created_at)->format('M j, Y') }}</span>
                </div>
            </div>
        </div>

        <!-- Actions -->
        @if($canEdit)
            <div class="flex items-center space-x-2">
                @if($handout->isPreviewable())
                    <x-button variant="secondary" size="sm" @click="$wire.showPreview({{ $handout->id }})" x-tooltip="Preview">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </x-button>
                @endif
                
                <x-button variant="secondary" size="sm" @click="window.open('{{ $handout->file_url }}', '_blank')" x-tooltip="Download">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </x-button>
                
                <x-button variant="warning" size="sm" @click="$wire.showEditForm({{ $handout->id }})" x-tooltip="Edit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </x-button>

                <x-button variant="{{ $handout->is_visible_in_sidebar ? 'success' : 'secondary' }}" 
                          size="sm" 
                          @click="$wire.toggleSidebarVisibility({{ $handout->id }})"
                          x-tooltip="Toggle sidebar visibility">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </x-button>
                
                <x-button variant="danger" size="sm" 
                          @click="confirm('Are you sure you want to delete this handout?') && $wire.deleteHandout({{ $handout->id }})"
                          x-tooltip="Delete">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </x-button>
            </div>
        @endif
    </div>
</div>
