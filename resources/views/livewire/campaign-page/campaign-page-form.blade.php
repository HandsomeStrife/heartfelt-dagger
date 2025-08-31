<div class="h-full flex flex-col">
    <!-- Form Content - Scrollable -->
    <div class="flex-1 overflow-y-auto p-6">

        <form wire:submit="save" id="campaign-page-form" class="space-y-6">
            <!-- Basic Information -->
            <div class="space-y-6">
                <!-- Title -->
                <div>
                    <label for="title" class="block text-sm font-medium text-slate-300 mb-2">
                        Page Title <span class="text-red-400">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="title"
                        wire:model="form.title"
                        placeholder="Enter page title..."
                        class="w-full px-4 py-3 bg-slate-800 border border-slate-600 text-white placeholder-slate-400 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors"
                    />
                    @error('form.title') 
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p> 
                    @enderror
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Parent Page -->
                    <div>
                        <label for="parent_id" class="block text-sm font-medium text-slate-300 mb-2">
                            Parent Page
                        </label>
                        <select 
                            id="parent_id"
                            wire:model="form.parent_id"
                            class="w-full px-4 py-3 bg-slate-800 border border-slate-600 text-white rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors"
                        >
                    @foreach($parentPageOptions as $option)
                        <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                    @endforeach
                </select>
                        @error('form.parent_id') 
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p> 
                        @enderror
                    </div>

                    <!-- Access Level -->
                    <div>
                        <label for="access_level" class="block text-sm font-medium text-slate-300 mb-2">
                            Access Level <span class="text-red-400">*</span>
                        </label>
                        <select 
                            id="access_level"
                            wire:model.live="form.access_level"
                            class="w-full px-4 py-3 bg-slate-800 border border-slate-600 text-white rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors"
                        >
                            @foreach($form->getAccessLevelOptions() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('form.access_level') 
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p> 
                        @enderror
                    </div>
                </div>

            <!-- Specific Player Access (shown when access_level is specific_players) -->
            @if($form->access_level === 'specific_players')
                <div class="bg-amber-500/10 border border-amber-500/30 rounded-lg p-4">
                    <label class="block text-sm font-medium text-slate-300 mb-3">
                    Select Players Who Can Access This Page
                </label>
                    <div class="space-y-2 max-h-40 overflow-y-auto">
                        @foreach($campaignMembers as $member)
                            <label class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    wire:model="form.authorized_user_ids"
                                    value="{{ $member['value'] }}"
                                    class="rounded border-slate-600 bg-slate-800 text-amber-500 focus:ring-amber-500"
                                />
                                <span class="ml-2 text-sm text-slate-300">{{ $member['label'] }}</span>
                            </label>
                        @endforeach
                    </div>
                    @if(empty($campaignMembers))
                        <p class="text-sm text-slate-400 italic">No campaign members to select.</p>
                    @endif
            </div>
        @endif

            <!-- Categories -->
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-3">
                    Category Tags
                </label>
                <div class="space-y-3">
                    <!-- Current Tags -->
                    @if(!empty($form->category_tags))
                        <div class="flex flex-wrap gap-2">
                            @foreach($form->category_tags as $index => $tag)
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                    {{ $tag }}
                                    <button 
                                        type="button"
                                        wire:click="removeCategoryTag({{ $index }})"
                                        class="ml-2 text-amber-400 hover:text-amber-200"
                                    >
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                </span>
                            @endforeach
                        </div>
                    @endif

                    <!-- Add New Tag -->
                    <div class="flex gap-2">
                        <input 
                            type="text" 
                            id="new_tag"
                            placeholder="Add a category tag..."
                            class="flex-1 px-4 py-2 bg-slate-800 border border-slate-600 text-white placeholder-slate-400 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors"
                            x-data="{ value: '' }"
                            x-model="value"
                            @keydown.enter.prevent="
                                if (value.trim()) {
                                    $wire.addCategoryTag(value.trim());
                                    value = '';
                                }
                            "
                        />
                        <button 
                            type="button"
                            @click="
                                if ($el.previousElementSibling.value.trim()) {
                                    $wire.addCategoryTag($el.previousElementSibling.value.trim());
                                    $el.previousElementSibling.value = '';
                                }
                            "
                            class="px-4 py-2 bg-slate-700 text-white rounded-lg hover:bg-slate-600 transition-colors"
                        >
                            Add
                        </button>
                    </div>

                    <!-- Existing Categories -->
                    @if(!empty($categoryTags))
                        <div class="border-t border-slate-600 pt-3">
                            <p class="text-xs text-slate-400 mb-2">Existing categories in this campaign:</p>
                            <div class="flex flex-wrap gap-1">
                                @foreach($categoryTags as $tag)
                                    <button 
                                        type="button"
                                        wire:click="addCategoryTag('{{ $tag }}')"
                                        class="px-2 py-1 text-xs rounded-md bg-slate-700 text-slate-300 hover:bg-slate-600 transition-colors"
                                    >
                                        + {{ $tag }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif
            </div>
        </div>

            <!-- Content Editor -->
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-3">
                    Page Content
                </label>
                <x-tiptap-editor 
                    wire:model="form.content" 
                    placeholder="Start writing your campaign content..."
                    height="400px"
                    class="w-full"
                />
                @error('form.content') 
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p> 
                @enderror
            </div>

            <!-- Advanced Options -->
            <div class="bg-slate-800/50 border border-slate-700 rounded-lg p-4">
                <h3 class="text-sm font-medium text-slate-300 mb-3">Publishing Options</h3>
                <div class="flex items-center gap-6">
                    <!-- Published Status -->
                    <label class="flex items-center">
                        <input 
                            type="checkbox" 
                            wire:model="form.is_published"
                            class="rounded border-slate-600 bg-slate-800 text-amber-500 focus:ring-amber-500"
                        />
                        <span class="ml-2 text-sm text-slate-300">Published</span>
                        <span class="ml-2 text-xs text-slate-400">(Unpublished pages are only visible to you)</span>
                    </label>

                    <!-- Display Order -->
                    <div class="flex items-center gap-2">
                        <label for="display_order" class="text-sm text-slate-300">Display Order:</label>
                        <input 
                            type="number" 
                            id="display_order"
                            wire:model="form.display_order"
                            min="0"
                            class="w-20 px-2 py-1 bg-slate-800 border border-slate-600 text-white rounded text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                        />
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Form Actions Footer -->
    <div class="border-t border-slate-700 p-6 bg-slate-900">
        <div class="flex items-center justify-between">
            <button 
                type="button"
                wire:click="cancel"
                class="px-6 py-3 border border-slate-600 text-slate-300 rounded-lg hover:bg-slate-700 hover:text-white transition-colors font-medium"
            >
                Cancel
            </button>
            
            <div class="flex items-center gap-3">
                @if($errors->any())
                    <p class="text-sm text-red-400">Please fix the errors above</p>
                @endif
                
                <button 
                    type="submit"
                    form="campaign-page-form"
                    class="px-6 py-3 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md disabled:opacity-50"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove>
                        {{ $this->mode === 'create' ? 'Create Page' : 'Update Page' }}
                    </span>
                    <span wire:loading class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ $this->mode === 'create' ? 'Creating...' : 'Updating...' }}
                    </span>
                </button>
            </div>
        </div>
    </div>


</div>