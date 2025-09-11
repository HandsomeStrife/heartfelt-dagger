<div>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-outfit font-bold text-white">Campaign Handouts</h2>
                <p class="text-slate-400 text-sm">Manage documents, images, and files for your campaign</p>
            </div>
            <x-button variant="primary" @click="$wire.showCreateForm()">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Upload Handout
            </x-button>
        </div>

        <!-- Filters and Search -->
        <div class="bg-slate-800/50 rounded-lg p-4 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div class="md:col-span-2">
                    <x-form.label for="search">Search Handouts</x-form.label>
                    <x-form.input 
                        id="search" 
                        wire:model.live="search_query"
                        placeholder="Search by title, description, or filename..." />
                </div>

                <!-- Access Level Filter -->
                <div>
                    <x-form.label for="access_filter">Access Level</x-form.label>
                    <x-form.select id="access_filter" wire:model.live="filter_access_level">
                        @foreach($access_level_options as $option)
                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                        @endforeach
                    </x-form.select>
                </div>

                <!-- View Mode Toggle -->
                <div class="flex items-end">
                    <div class="flex rounded-lg bg-slate-700 p-1">
                        <button @click="$wire.set('view_mode', 'grid')" 
                                :class="$wire.view_mode === 'grid' ? 'bg-amber-500 text-white' : 'text-slate-300 hover:text-white'"
                                class="px-3 py-1 rounded text-sm transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                        </button>
                        <button @click="$wire.set('view_mode', 'list')" 
                                :class="$wire.view_mode === 'list' ? 'bg-amber-500 text-white' : 'text-slate-300 hover:text-white'"
                                class="px-3 py-1 rounded text-sm transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Handouts Grid/List -->
        <div class="min-h-[400px]">
            @if($filtered_handouts->count() > 0)
                @if($view_mode === 'grid')
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($filtered_handouts as $handout)
                            <x-campaign-handout.handout-card 
                                :handout="$handout"
                                :can-edit="true" />
                        @endforeach
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($filtered_handouts as $handout)
                            <x-campaign-handout.handout-list-item 
                                :handout="$handout"
                                :can-edit="true" />
                        @endforeach
                    </div>
                @endif
            @else
                <div class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-slate-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="text-lg font-medium text-white mb-2">No handouts found</h3>
                    <p class="text-slate-400 mb-4">
                        @if(!empty($search_query) || !empty($filter_access_level))
                            Try adjusting your search or filters
                        @else
                            Get started by uploading your first handout
                        @endif
                    </p>
                    @if(empty($search_query) && empty($filter_access_level))
                        <x-button variant="primary" @click="$wire.showCreateForm()">
                            Upload First Handout
                        </x-button>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Upload/Edit Form Modal -->
    @if($show_form)
        <x-modal.slideover wire:model="show_form">
            <x-slot name="title">
                {{ $editing_handout_id ? 'Edit Handout' : 'Upload New Handout' }}
            </x-slot>

            <form wire:submit="save" class="space-y-6">
                <!-- File Upload (Only for new handouts) -->
                @if(!$editing_handout_id)
                    <div>
                        <x-form.label for="file">File *</x-form.label>
                        <input type="file" 
                               wire:model="form.file" 
                               id="file"
                               class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-amber-500 file:text-white file:cursor-pointer hover:file:bg-amber-600"
                               accept="image/*,.pdf,.doc,.docx,.txt,.mp3,.wav,.mp4,.webm">
                        @error('form.file') 
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p> 
                        @enderror
                        <p class="text-slate-400 text-xs mt-1">Max file size: 10MB. Supported: Images, PDFs, Documents, Audio, Video</p>
                    </div>
                @endif

                <!-- Title -->
                <div>
                    <x-form.label for="title">Title *</x-form.label>
                    <x-form.input wire:model="form.title" id="title" placeholder="Enter handout title..." />
                    @error('form.title') 
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <x-form.label for="description">Description</x-form.label>
                    <textarea wire:model="form.description" 
                              id="description"
                              rows="3"
                              placeholder="Optional description for this handout..."
                              class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500"></textarea>
                    @error('form.description') 
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Access Level -->
                <div>
                    <x-form.label for="access_level">Access Level *</x-form.label>
                    <x-form.select wire:model.live="form.access_level" id="access_level">
                        <option value="gm_only">GM Only</option>
                        <option value="all_players">All Players</option>
                        <option value="specific_players">Specific Players</option>
                    </x-form.select>
                    @error('form.access_level') 
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Specific Players (when access_level is specific_players) -->
                @if($form->requiresSpecificAccess())
                    <div>
                        <x-form.label>Select Players</x-form.label>
                        <div class="space-y-2 max-h-32 overflow-y-auto">
                            @foreach($campaign_members as $member)
                                @if($member->user)
                                    <label class="flex items-center">
                                        <input type="checkbox" 
                                               wire:model="form.authorized_user_ids" 
                                               value="{{ $member->user->id }}"
                                               class="rounded bg-slate-700 border-slate-600 text-amber-500 focus:ring-amber-500 focus:ring-offset-slate-800">
                                        <span class="ml-2 text-sm text-white">{{ $member->user->username }}</span>
                                    </label>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Sidebar Visibility -->
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" 
                               wire:model="form.is_visible_in_sidebar"
                               class="rounded bg-slate-700 border-slate-600 text-amber-500 focus:ring-amber-500 focus:ring-offset-slate-800">
                        <span class="ml-2 text-sm text-white">Show in room sidebar</span>
                    </label>
                    <p class="text-slate-400 text-xs mt-1">When enabled, this handout will appear in the GM and player room sidebars</p>
                </div>

                <!-- Publishing Status (Edit only) -->
                @if($editing_handout_id)
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   wire:model="form.is_published"
                                   class="rounded bg-slate-700 border-slate-600 text-amber-500 focus:ring-amber-500 focus:ring-offset-slate-800">
                            <span class="ml-2 text-sm text-white">Published</span>
                        </label>
                        <p class="text-slate-400 text-xs mt-1">Unpublished handouts are only visible to you</p>
                    </div>
                @endif

                <!-- Form Actions -->
                <div class="flex justify-end space-x-3 pt-4 border-t border-slate-700">
                    <x-button variant="secondary" @click="$wire.cancelForm()" type="button">
                        Cancel
                    </x-button>
                    <x-button variant="primary" type="submit">
                        {{ $editing_handout_id ? 'Update Handout' : 'Upload Handout' }}
                    </x-button>
                </div>
            </form>
        </x-modal.slideover>
    @endif

    <!-- Preview Modal -->
    @if($show_preview_modal && $preview_handout_id)
        <x-campaign-handout.preview-modal 
            :handout-id="$preview_handout_id" 
            wire:model="show_preview_modal" />
    @endif
</div>