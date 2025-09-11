@php
    // Default content for all JSON sources
    $pageTitle = $title;
@endphp

@switch($source)
    @case('domains')
        @include('reference.partials.domains', ['domains' => $data, 'title' => $pageTitle])
        @break
    
    @case('classes')
        @include('reference.partials.classes', ['classes' => $data, 'title' => $pageTitle])
        @break
    
    @case('ancestries')
        @include('reference.partials.ancestries', ['ancestries' => $data, 'title' => $pageTitle])
        @break
    
    @case('communities')
        @include('reference.partials.communities', ['communities' => $data, 'title' => $pageTitle])
        @break
    
    @case('weapons')
        @include('reference.partials.weapons', ['weapons' => $data, 'title' => $pageTitle])
        @break
    
    @case('armor')
        @include('reference.partials.armor', ['armor' => $data, 'title' => $pageTitle])
        @break
    
    @case('consumables')
        @include('reference.partials.consumables', ['consumables' => $data, 'title' => $pageTitle])
        @break
    
    @case('items')
        @include('reference.partials.items', ['items' => $data, 'title' => $pageTitle])
        @break
    
    @case('adversaries')
        @include('reference.partials.adversaries', ['adversaries' => $data, 'title' => $pageTitle])
        @break
    
    @case('abilities')
        @include('reference.partials.abilities', ['abilities' => $data, 'title' => $pageTitle])
        @break
    
    @default
        <h1 class="font-outfit text-2xl font-bold text-white border-b border-slate-700 pb-3 mb-6 mt-0">{{ $pageTitle }}</h1>
        <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-4">
            <p class="text-amber-300">Content template for "{{ $source }}" is not yet implemented.</p>
            <p class="text-slate-400 text-sm mt-2">This page will display {{ $source }} data from the JSON files.</p>
        </div>
@endswitch

