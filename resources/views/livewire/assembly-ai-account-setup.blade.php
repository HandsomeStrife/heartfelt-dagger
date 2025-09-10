<div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950">
    <!-- Compact Navigation -->
    <x-sub-navigation>
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a 
                    href="{{ route('storage-accounts') }}"
                    class="p-1.5 text-slate-400 hover:text-white hover:bg-slate-700/50 rounded-md transition-colors"
                    title="Back to storage accounts"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="flex items-center gap-2">
                    <x-icons.brands.assembly-ai class="w-6 h-6" />
                    <div>
                        <h1 class="font-outfit text-lg font-bold text-white tracking-wide">
                            Connect AssemblyAI Account
                        </h1>
                        <p class="text-slate-400 text-xs">
                            Add your API key for professional speech-to-text transcription
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </x-sub-navigation>

    <div class="px-4 sm:px-6 lg:px-8 pt-8 pb-12">
        <div class="max-w-2xl mx-auto space-y-6">

            <!-- Main Form -->
            <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700 rounded-xl p-4">
                
                <!-- Instructions -->
                <div class="bg-blue-500/10 border border-blue-500/20 rounded-lg p-4 mb-6">
                    <div class="flex items-start space-x-3">
                        <div class="w-6 h-6 bg-blue-500/20 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-3 h-3 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-blue-400 font-semibold text-sm mb-1">How to get your AssemblyAI API Key</h3>
                            <ol class="text-slate-300 text-sm space-y-1 list-decimal list-inside">
                                <li>Visit <a href="https://www.assemblyai.com/" target="_blank" class="text-blue-400 hover:text-blue-300 underline">AssemblyAI.com</a> and create an account</li>
                                <li>Go to your dashboard and navigate to the API Keys section</li>
                                <li>Copy your API key and paste it below</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <form wire:submit="save" class="space-y-6">
                    <!-- Display Name -->
                    <div>
                        <label for="display_name" class="block text-sm font-medium text-slate-300 mb-2">
                            Account Name
                        </label>
                        <input type="text" 
                               id="display_name" 
                               wire:model="form.display_name"
                               placeholder="e.g., My AssemblyAI Account"
                               class="w-full bg-slate-800 border border-slate-600 rounded-lg px-3 py-2 text-white placeholder-slate-400 focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                        @error('form.display_name')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- API Key -->
                    <div>
                        <label for="api_key" class="block text-sm font-medium text-slate-300 mb-2">
                            API Key
                        </label>
                        <input type="password" 
                               id="api_key" 
                               wire:model="form.api_key"
                               placeholder="Your AssemblyAI API Key"
                               class="w-full bg-slate-800 border border-slate-600 rounded-lg px-3 py-2 text-white placeholder-slate-400 focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                        @error('form.api_key')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Connection Test -->
                    <div class="flex items-center justify-between pt-4 border-t border-slate-700">
                        <button type="button" 
                                wire:click="testConnection"
                                wire:loading.attr="disabled"
                                wire:target="testConnection"
                                class="inline-flex items-center px-4 py-2 border border-slate-600 text-sm font-medium rounded-md text-slate-300 bg-slate-800 hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 focus:ring-offset-slate-900 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">
                            <svg wire:loading wire:target="testConnection" class="animate-spin -ml-1 mr-2 h-4 w-4 text-slate-300" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="testConnection">Test Connection</span>
                            <span wire:loading wire:target="testConnection">Testing...</span>
                        </button>

                        <!-- Connection Result -->
                        @if($connectionResult === 'success')
                            <div class="flex items-center text-emerald-400">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Connection successful!
                            </div>
                        @elseif($connectionResult === 'error')
                            <div class="flex items-center text-red-400">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Connection failed
                            </div>
                        @endif
                    </div>

                    @error('connection')
                        <div class="bg-red-500/10 border border-red-500/20 rounded-lg p-4">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-red-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-red-400 text-sm">{{ $message }}</p>
                            </div>
                        </div>
                    @enderror

                    @error('form')
                        <div class="bg-red-500/10 border border-red-500/20 rounded-lg p-4">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-red-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-red-400 text-sm">{{ $message }}</p>
                            </div>
                        </div>
                    @enderror

                    <!-- Submit Button -->
                    <div class="flex items-center justify-between pt-6">
                        <a href="{{ $redirectTo ?: '/dashboard' }}" 
                           class="text-slate-400 hover:text-slate-300 text-sm font-medium transition-colors">
                            ← Back
                        </a>
                        
                        <button type="submit" 
                                wire:loading.attr="disabled"
                                wire:target="save"
                                class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 focus:ring-offset-slate-900 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">
                            <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="save">Connect Account</span>
                            <span wire:loading wire:target="save">Connecting...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>



