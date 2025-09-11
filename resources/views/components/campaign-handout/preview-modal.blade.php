@props(['handoutId'])

@php
$handout = (new \Domain\CampaignHandout\Repositories\CampaignHandoutRepository)->findById($handoutId);
@endphp

@if($handout)
<x-modal.popup>
    <div class="bg-slate-900 rounded-lg max-w-4xl w-full max-h-[90vh] overflow-hidden">
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b border-slate-700">
            <div>
                <h3 class="text-lg font-outfit font-semibold text-white">{{ $handout->title }}</h3>
                <div class="flex items-center space-x-2 mt-1">
                    <x-badge variant="secondary" class="text-xs">
                        {{ strtoupper($handout->file_type->value) }}
                    </x-badge>
                    <span class="text-slate-400 text-sm">{{ $handout->formatted_file_size }}</span>
                </div>
            </div>
            
            <div class="flex items-center space-x-2">
                <x-button variant="secondary" size="sm" @click="window.open('{{ $handout->file_url }}', '_blank')">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Download
                </x-button>
                
                <button @click="$wire.closePreview()" 
                        class="text-slate-400 hover:text-white p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Content -->
        <div class="p-4 max-h-[70vh] overflow-auto">
            @if($handout->isPreviewableImage())
                <div class="text-center">
                    <img src="{{ $handout->file_url }}" 
                         alt="{{ $handout->title }}"
                         class="max-w-full h-auto rounded-lg shadow-lg"
                         style="max-height: 60vh;">
                    
                    @if($handout->image_dimensions)
                        <p class="text-slate-400 text-sm mt-2">
                            {{ $handout->image_dimensions['width'] }} × {{ $handout->image_dimensions['height'] }} pixels
                        </p>
                    @endif
                </div>
            @elseif($handout->isPdf())
                <div class="w-full h-96">
                    <iframe src="{{ $handout->file_url }}" 
                            class="w-full h-full border-0 rounded-lg"
                            title="{{ $handout->title }}">
                        <p class="text-center text-slate-400 py-8">
                            Unable to display PDF. 
                            <a href="{{ $handout->file_url }}" 
                               target="_blank" 
                               class="text-amber-400 hover:text-amber-300">
                                Click here to open in new tab
                            </a>
                        </p>
                    </iframe>
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="w-16 h-16 mx-auto text-slate-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $handout->file_type->icon() }}" />
                    </svg>
                    <h4 class="text-white font-medium mb-2">{{ $handout->title }}</h4>
                    <p class="text-slate-400 mb-4">This file type cannot be previewed in the browser.</p>
                    <x-button variant="primary" @click="window.open('{{ $handout->file_url }}', '_blank')">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Download File
                    </x-button>
                </div>
            @endif
        </div>

        <!-- Description (if available) -->
        @if($handout->description)
            <div class="px-4 pb-4 border-t border-slate-700">
                <h4 class="text-white font-medium mb-2 mt-4">Description</h4>
                <p class="text-slate-300 text-sm">{{ $handout->description }}</p>
            </div>
        @endif
    </div>
</x-modal.popup>
@endif
