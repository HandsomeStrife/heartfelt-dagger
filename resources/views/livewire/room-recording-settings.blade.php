<div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700 rounded-xl p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-xl font-outfit font-bold text-white">Recording Settings</h3>
            <p class="text-slate-400 text-sm mt-1">Configure video recording and storage for this room</p>
        </div>
        <div class="flex items-center space-x-2">
            @if($form->recording_enabled)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-400/10 text-emerald-400 border border-emerald-400/20">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <circle cx="10" cy="10" r="3"/>
                    </svg>
                    Recording Enabled
                </span>
            @else
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-400/10 text-slate-400 border border-slate-400/20">
                    Recording Disabled
                </span>
            @endif
        </div>
    </div>

    <form wire:submit="save" class="space-y-6">
        {{-- Recording Enable/Disable --}}
        <div class="space-y-4">
            <div class="flex items-start space-x-3">
                <input type="checkbox" 
                       id="recording_enabled" 
                       wire:model.live="form.recording_enabled"
                       class="mt-1 h-4 w-4 text-amber-500 focus:ring-amber-500 border-slate-600 rounded bg-slate-800">
                <div class="flex-1">
                    <label for="recording_enabled" class="text-white font-medium">Enable Video Recording</label>
                    <p class="text-slate-400 text-sm mt-1">
                        Allow participants to record video during sessions. Recordings will be saved to your chosen storage provider.
                    </p>
                </div>
            </div>

            @if($form->recording_enabled)
                <div class="ml-7 space-y-4 border-l-2 border-amber-500/20 pl-4">
                    {{-- Speech-to-Text Option --}}
                    <div class="flex items-start space-x-3">
                        <input type="checkbox" 
                               id="stt_enabled" 
                               wire:model.live="form.stt_enabled"
                               class="mt-1 h-4 w-4 text-amber-500 focus:ring-amber-500 border-slate-600 rounded bg-slate-800">
                        <div class="flex-1">
                            <label for="stt_enabled" class="text-white font-medium">Enable Speech-to-Text</label>
                            <p class="text-slate-400 text-sm mt-1">
                                Automatically transcribe participant speech during sessions. Requires participant consent.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Storage Provider Selection --}}
        @if($form->recording_enabled)
            <div class="space-y-4">
                <div>
                    <label class="block text-white font-medium mb-3">Storage Provider</label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        {{-- Local Storage --}}
                        <label class="relative cursor-pointer">
                            <input type="radio" 
                                   name="storage_provider" 
                                   value="local" 
                                   wire:model.live="form.storage_provider"
                                   class="sr-only">
                            <div class="border-2 rounded-lg p-4 transition-all duration-200 @if($form->storage_provider === 'local') border-amber-500 bg-amber-500/10 @else border-slate-600 hover:border-slate-500 @endif">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-full @if($form->storage_provider === 'local') bg-amber-500 @else bg-slate-600 @endif flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-white">Local Server</h4>
                                        <p class="text-xs text-slate-400">Store on this server</p>
                                    </div>
                                </div>
                            </div>
                        </label>

                        {{-- Wasabi Storage --}}
                        <label class="relative cursor-pointer">
                            <input type="radio" 
                                   name="storage_provider" 
                                   value="wasabi" 
                                   wire:model.live="form.storage_provider"
                                   class="sr-only">
                            <div class="border-2 rounded-lg p-4 transition-all duration-200 @if($form->storage_provider === 'wasabi') border-amber-500 bg-amber-500/10 @else border-slate-600 hover:border-slate-500 @endif">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-full @if($form->storage_provider === 'wasabi') bg-amber-500 @else bg-slate-600 @endif flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-white">Wasabi Cloud</h4>
                                        <p class="text-xs text-slate-400">S3-compatible storage</p>
                                    </div>
                                </div>
                            </div>
                        </label>

                        {{-- Google Drive --}}
                        <label class="relative cursor-pointer">
                            <input type="radio" 
                                   name="storage_provider" 
                                   value="google_drive" 
                                   wire:model.live="form.storage_provider"
                                   class="sr-only">
                            <div class="border-2 rounded-lg p-4 transition-all duration-200 @if($form->storage_provider === 'google_drive') border-amber-500 bg-amber-500/10 @else border-slate-600 hover:border-slate-500 @endif">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-full @if($form->storage_provider === 'google_drive') bg-amber-500 @else bg-slate-600 @endif flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-white">Google Drive</h4>
                                        <p class="text-xs text-slate-400">Your Google account</p>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Storage Account Selection --}}
                @if($form->requiresStorageAccount())
                    <div class="space-y-3">
                        @php
                            $accounts = $this->getAccountsForProvider($form->storage_provider);
                            $providerName = $form->storage_provider === 'wasabi' ? 'Wasabi' : 'Google Drive';
                        @endphp

                        <label class="block text-white font-medium">
                            {{ $providerName }} Account
                        </label>

                        @if($accounts->count() > 0)
                            <select wire:model.live="form.storage_account_id" 
                                    class="w-full bg-slate-800 border border-slate-600 rounded-lg px-3 py-2 text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                                <option value="">Select {{ $providerName }} account...</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->display_name }}</option>
                                @endforeach
                            </select>
                        @else
                            <div class="border border-slate-600 rounded-lg p-4 bg-slate-800/50">
                                <p class="text-slate-400 text-sm mb-3">
                                    No {{ $providerName }} accounts connected. Connect an account to use this storage option.
                                </p>
                                @if($form->storage_provider === 'wasabi')
                                    <button type="button" 
                                            wire:click="connectWasabi"
                                            class="inline-flex items-center px-3 py-2 border border-amber-500 text-sm font-medium rounded-md text-amber-400 bg-amber-500/10 hover:bg-amber-500/20 focus:outline-none focus:ring-2 focus:ring-amber-500 transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                        Connect Wasabi Account
                                    </button>
                                @else
                                    <button type="button" 
                                            wire:click="connectGoogleDrive"
                                            class="inline-flex items-center px-3 py-2 border border-amber-500 text-sm font-medium rounded-md text-amber-400 bg-amber-500/10 hover:bg-amber-500/20 focus:outline-none focus:ring-2 focus:ring-amber-500 transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                        Connect Google Drive
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @endif

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

        {{-- Success Message --}}
        @if(session()->has('success'))
            <div class="rounded-md bg-emerald-500/10 border border-emerald-500/20 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-emerald-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-emerald-400">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Save Button --}}
        <div class="flex items-center justify-between pt-4 border-t border-slate-700">
            <div class="text-sm text-slate-400">
                @if($form->recording_enabled && $form->storage_provider)
                    Current: {{ $form->getStorageProviderDisplayName() }}
                    @if($form->storage_account_id)
                        ({{ $this->getStorageAccountName($form->storage_account_id) }})
                    @endif
                @else
                    Recording is currently disabled
                @endif
            </div>
            
            <button type="submit" 
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 focus:ring-offset-slate-900 transition-all duration-200">
                <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span wire:loading.remove wire:target="save">Save Settings</span>
                <span wire:loading wire:target="save">Saving...</span>
            </button>
        </div>
    </form>
</div>
