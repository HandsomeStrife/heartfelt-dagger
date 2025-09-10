<x-layouts.app>
    <x-slot name="title">Level Up Character</x-slot>
<div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950">
    <div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="bg-slate-900/80 backdrop-blur-xl rounded-2xl border border-slate-700/50 shadow-2xl p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="font-outfit text-3xl font-bold text-slate-100">Level Up Character</h1>
                    <p class="text-slate-400 mt-1">
                        {{ $character->name }} • Level {{ $character->level }} → {{ $character->level + 1 }}
                    </p>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('character.show', ['public_key' => $public_key, 'character_key' => $character_key]) }}" 
                       class="px-4 py-2 bg-slate-700 text-slate-200 hover:bg-slate-600 rounded-lg transition-colors">
                        ← Back to Character
                    </a>
                </div>
            </div>
        </div>

        <!-- Level Up Component -->
        <div class="bg-slate-900/80 backdrop-blur-xl rounded-2xl border border-slate-700/50 shadow-2xl">
            <livewire:character-level-up 
                :character-key="$character_key"
                :can-edit="$can_edit" />
        </div>
    </div>
</div>
</x-layouts.app>
