<div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950">
    <div class="px-4 sm:px-6 lg:px-8 pt-12 pb-16">
        <div class="max-w-2xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-gradient-to-br from-amber-500/20 to-orange-500/20 rounded-xl flex items-center justify-center border border-amber-500/30 mb-4 mx-auto">
                    <svg class="w-8 h-8 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
                    </svg>
                </div>
                <h1 class="font-outfit text-3xl font-bold text-white mb-2">Connect Wasabi Account</h1>
                <p class="text-slate-400">Add your Wasabi cloud storage credentials for video recording storage</p>
            </div>

            <!-- Main Form -->
            <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700 rounded-xl p-6">
                <form wire:submit="save" class="space-y-6">
                    {{-- Display Name --}}
                    <div>
                        <label for="display_name" class="block text-white font-medium mb-2">
                            Account Display Name
                        </label>
                        <input type="text" 
                               id="display_name" 
                               wire:model="form.display_name"
                               placeholder="e.g., My Wasabi Account"
                               class="w-full bg-slate-800 border border-slate-600 rounded-lg px-3 py-2 text-white placeholder-slate-400 focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                        @error('form.display_name')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Access Key ID --}}
                    <div>
                        <label for="access_key_id" class="block text-white font-medium mb-2">
                            Access Key ID
                        </label>
                        <input type="text" 
                               id="access_key_id" 
                               wire:model="form.access_key_id"
                               placeholder="AKIAIOSFODNN7EXAMPLE"
                               class="w-full bg-slate-800 border border-slate-600 rounded-lg px-3 py-2 text-white placeholder-slate-400 focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                        @error('form.access_key_id')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Secret Access Key --}}
                    <div>
                        <label for="secret_access_key" class="block text-white font-medium mb-2">
                            Secret Access Key
                        </label>
                        <input type="password" 
                               id="secret_access_key" 
                               wire:model="form.secret_access_key"
                               placeholder="wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY"
                               class="w-full bg-slate-800 border border-slate-600 rounded-lg px-3 py-2 text-white placeholder-slate-400 focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                        @error('form.secret_access_key')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Bucket Name --}}
                    <div>
                        <label for="bucket_name" class="block text-white font-medium mb-2">
                            Bucket Name
                        </label>
                        <input type="text" 
                               id="bucket_name" 
                               wire:model="form.bucket_name"
                               placeholder="my-daggerheart-recordings"
                               class="w-full bg-slate-800 border border-slate-600 rounded-lg px-3 py-2 text-white placeholder-slate-400 focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                        <p class="text-slate-400 text-sm mt-1">Must be lowercase letters, numbers, and hyphens only</p>
                        @error('form.bucket_name')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Region --}}
                    <div>
                        <label for="region" class="block text-white font-medium mb-2">
                            Region
                        </label>
                        <select id="region" 
                                wire:model="form.region"
                                class="w-full bg-slate-800 border border-slate-600 rounded-lg px-3 py-2 text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                            <option value="us-east-1">US East 1 (N. Virginia)</option>
                            <option value="us-east-2">US East 2 (N. Virginia)</option>
                            <option value="us-west-1">US West 1 (Oregon)</option>
                            <option value="us-west-2">US West 2 (Oregon)</option>
                            <option value="ca-central-1">Canada Central 1 (Toronto)</option>
                            <option value="eu-central-1">EU Central 1 (Amsterdam)</option>
                            <option value="eu-central-2">EU Central 2 (Amsterdam)</option>
                            <option value="eu-west-1">EU West 1 (London)</option>
                            <option value="eu-west-2">EU West 2 (Paris)</option>
                            <option value="ap-northeast-1">Asia Pacific (Tokyo)</option>
                            <option value="ap-northeast-2">Asia Pacific (Osaka)</option>
                            <option value="ap-southeast-1">Asia Pacific (Singapore)</option>
                            <option value="ap-southeast-2">Asia Pacific (Sydney)</option>
                        </select>
                        @error('form.region')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Endpoint (Optional) --}}
                    <div>
                        <label for="endpoint" class="block text-white font-medium mb-2">
                            Custom Endpoint (Optional)
                        </label>
                        <input type="url" 
                               id="endpoint" 
                               wire:model="form.endpoint"
                               placeholder="https://s3.us-east-1.wasabisys.com"
                               class="w-full bg-slate-800 border border-slate-600 rounded-lg px-3 py-2 text-white placeholder-slate-400 focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                        <p class="text-slate-400 text-sm mt-1">Leave blank to use default Wasabi endpoint for selected region</p>
                        @error('form.endpoint')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Connection Test --}}
                    <div class="border border-slate-600 rounded-lg p-4 bg-slate-800/50">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-white font-medium">Test Connection</h3>
                            <button type="button" 
                                    wire:click="testConnection"
                                    @if($isTestingConnection) disabled @endif
                                    class="inline-flex items-center px-3 py-2 border border-blue-500 text-sm font-medium rounded-md text-blue-400 bg-blue-500/10 hover:bg-blue-500/20 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg wire:loading.remove wire:target="testConnection" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <svg wire:loading wire:target="testConnection" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span wire:loading.remove wire:target="testConnection">Test Connection</span>
                                <span wire:loading wire:target="testConnection">Testing...</span>
                            </button>
                        </div>
                        
                        @if($connectionResult === 'success')
                            <div class="flex items-center text-emerald-400 text-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Connection successful! Credentials are valid.
                            </div>
                        @endif

                        @error('connection')
                            <div class="flex items-start text-red-400 text-sm">
                                <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $message }}
                            </div>
                        @enderror
                        
                        <p class="text-slate-400 text-xs mt-2">
                            Test your credentials before saving to ensure they work correctly.
                        </p>
                    </div>

                    {{-- Error Messages --}}
                    @error('form')
                        <div class="rounded-md bg-red-500/10 border border-red-500/20 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-red-400">{{ $message }}</p>
                                </div>
                            </div>
                        </div>
                    @enderror

                    {{-- Action Buttons --}}
                    <div class="flex items-center justify-between pt-4 border-t border-slate-700">
                        <a href="{{ $redirectTo ?: '/dashboard' }}" 
                           class="inline-flex items-center px-4 py-2 border border-slate-600 text-sm font-medium rounded-md text-slate-300 bg-slate-800 hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-500 transition-colors">
                            Cancel
                        </a>
                        
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 focus:ring-offset-slate-900 transition-all duration-200">
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

            {{-- Help Section --}}
            <div class="mt-8 bg-slate-900/60 backdrop-blur-xl border border-slate-700 rounded-xl p-6">
                <h3 class="text-white font-medium mb-3">Need Help?</h3>
                <div class="space-y-2 text-sm text-slate-400">
                    <p>• Get your Wasabi credentials from the <a href="https://console.wasabisys.com/" target="_blank" class="text-amber-400 hover:text-amber-300">Wasabi Console</a></p>
                    <p>• Make sure your bucket has the appropriate permissions for file uploads</p>
                    <p>• Test your connection before saving to ensure everything works correctly</p>
                    <p>• Your credentials are encrypted and stored securely</p>
                </div>
            </div>
        </div>
    </div>
</div>
