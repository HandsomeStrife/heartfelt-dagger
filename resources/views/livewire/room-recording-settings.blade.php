<div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700 rounded-xl overflow-hidden" x-data="{ activeTab: 'general' }">
    <!-- Header -->
    <div class="flex items-center justify-between p-6 border-b border-slate-700">
        <div>
            <h3 class="text-xl font-outfit font-bold text-white">Room Settings</h3>
            <p class="text-slate-400 text-sm mt-1">Configure recording, speech-to-text, and participant management</p>
        </div>
        <div class="flex items-center space-x-2">
            @if($form->recording_enabled)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-400/10 text-emerald-400 border border-emerald-400/20">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <circle cx="10" cy="10" r="3"/>
                    </svg>
                    Recording Enabled
                </span>
            @endif
            @if($form->stt_enabled)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-400/10 text-blue-400 border border-blue-400/20">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                    </svg>
                    STT Enabled
                </span>
            @endif
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="border-b border-slate-700">
        <nav class="flex space-x-8 px-6" aria-label="Tabs">
            <button @click="activeTab = 'general'" 
                    :class="activeTab === 'general' ? 'border-amber-500 text-amber-400' : 'border-transparent text-slate-400 hover:text-slate-300 hover:border-slate-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                General
            </button>
            <button @click="activeTab = 'speech'" 
                    :class="activeTab === 'speech' ? 'border-amber-500 text-amber-400' : 'border-transparent text-slate-400 hover:text-slate-300 hover:border-slate-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                </svg>
                Speech-to-Text
            </button>
            <button @click="activeTab = 'recording'" 
                    :class="activeTab === 'recording' ? 'border-amber-500 text-amber-400' : 'border-transparent text-slate-400 hover:text-slate-300 hover:border-slate-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                Recording
            </button>
            <button @click="activeTab = 'participants'" 
                    :class="activeTab === 'participants' ? 'border-amber-500 text-amber-400' : 'border-transparent text-slate-400 hover:text-slate-300 hover:border-slate-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                </svg>
                Participants
                @if($participants->count() > 0)
                    <span class="ml-1 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-amber-100 bg-amber-600 rounded-full">{{ $participants->count() }}</span>
                @endif
            </button>
        </nav>
    </div>

    <!-- Tab Content -->
    <form wire:submit="save" id="settings-form">
        <!-- General Tab -->
        <div x-show="activeTab === 'general'" class="p-6 space-y-6">
            <div>
                <h4 class="text-lg font-outfit font-semibold text-white mb-2">Room Information</h4>
                <p class="text-slate-400 text-sm mb-4">Basic room settings and overview</p>
                
                <div class="bg-slate-800/50 rounded-lg p-4 border border-slate-600/30">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Room Name</label>
                            <p class="text-white">{{ $room->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Max Participants</label>
                            <p class="text-white">{{ $room->getTotalCapacity() }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Current Participants</label>
                            <p class="text-white">{{ $participants->count() }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Room Status</label>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-400/10 text-emerald-400 border border-emerald-400/20">
                                {{ ucfirst($room->status->value) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div class="bg-slate-800/50 rounded-lg p-4 border border-slate-600/30">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                                </svg>
                            </div>
                            <div>
                                <h5 class="font-semibold text-white">Speech-to-Text</h5>
                                <p class="text-sm text-slate-400">
                                    @if($form->stt_enabled)
                                        Enabled ({{ $form->getSttProviderDisplayName() }})
                                    @else
                                        Disabled
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-slate-800/50 rounded-lg p-4 border border-slate-600/30">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-red-500/20 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <h5 class="font-semibold text-white">Video Recording</h5>
                                <p class="text-sm text-slate-400">
                                    @if($form->recording_enabled)
                                        Enabled ({{ $form->getStorageProviderDisplayName() }})
                                    @else
                                        Disabled
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Speech-to-Text Tab -->
        <div x-show="activeTab === 'speech'" class="p-6 space-y-6">
            <div>
                <h4 class="text-lg font-outfit font-semibold text-white mb-2">Speech-to-Text Settings</h4>
                <p class="text-slate-400 text-sm mb-4">Configure automatic transcription of participant speech</p>
            </div>
            
            <div class="flex items-start space-x-3">
                <input type="checkbox" 
                       id="stt_enabled" 
                       wire:model.live="form.stt_enabled"
                       class="mt-1 h-4 w-4 text-amber-500 focus:ring-amber-500 border-slate-600 rounded bg-slate-800">
                <div class="flex-1">
                    <label for="stt_enabled" class="text-white font-medium">Enable Speech-to-Text</label>
                    <p class="text-slate-400 text-sm mt-1">
                        Automatically transcribe participant speech during sessions. Requires participant consent. Can be enabled independently of video recording.
                    </p>
                </div>
            </div>

            @if($form->stt_enabled)
                <div class="ml-7 space-y-6 border-l-2 border-amber-500/20 pl-4">
                    <div>
                        <label class="block text-white font-medium mb-3">Speech-to-Text Provider</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Browser Speech Recognition --}}
                            <label class="relative cursor-pointer">
                                <input type="radio" 
                                       name="stt_provider" 
                                       value="browser" 
                                       wire:model.live="form.stt_provider"
                                       class="sr-only">
                                <div class="border-2 rounded-lg p-4 transition-all duration-200 @if($form->stt_provider === 'browser' || !$form->stt_provider) border-amber-500 bg-amber-500/10 @else border-slate-600 hover:border-slate-500 @endif">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 rounded-full @if($form->stt_provider === 'browser' || !$form->stt_provider) bg-amber-500 @else bg-slate-600 @endif flex items-center justify-center">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 class="font-medium text-white">Browser Recognition</h4>
                                            <p class="text-xs text-slate-400">Built-in browser speech API</p>
                                        </div>
                                    </div>
                                </div>
                            </label>

                            {{-- AssemblyAI --}}
                            <label class="relative cursor-pointer">
                                <input type="radio" 
                                       name="stt_provider" 
                                       value="assemblyai" 
                                       wire:model.live="form.stt_provider"
                                       class="sr-only">
                                <div class="border-2 rounded-lg p-4 transition-all duration-200 @if($form->stt_provider === 'assemblyai') border-amber-500 bg-amber-500/10 @else border-slate-600 hover:border-slate-500 @endif">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 rounded-full @if($form->stt_provider === 'assemblyai') bg-amber-500 @else bg-slate-600 @endif flex items-center justify-center">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 class="font-medium text-white">AssemblyAI</h4>
                                            <p class="text-xs text-slate-400">Professional AI transcription</p>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- AssemblyAI Account Selection --}}
                    @if($form->requiresSttAccount())
                        <div class="space-y-3">
                            @php
                                $sttAccounts = $this->getSttAccountsForProvider($form->stt_provider);
                            @endphp

                            <label class="block text-white font-medium">
                                AssemblyAI Account
                            </label>

                            @if($sttAccounts->count() > 0)
                                <select wire:model.live="form.stt_account_id" 
                                        class="w-full bg-slate-800 border border-slate-600 rounded-lg px-3 py-2 text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                                    <option value="">Select AssemblyAI account...</option>
                                    @foreach($sttAccounts as $account)
                                        <option value="{{ $account->id }}">{{ $account->display_name }}</option>
                                    @endforeach
                                </select>
                            @else
                                <div class="bg-slate-800/50 border border-slate-600 rounded-lg p-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-amber-500/20 rounded-lg flex items-center justify-center">
                                            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <h4 class="text-white font-medium">No AssemblyAI Account Found</h4>
                                            <p class="text-slate-400 text-sm">You need to add an AssemblyAI account to use this provider.</p>
                                        </div>
                                        <a href="{{ route('assemblyai.connect', ['redirect_to' => request()->url()]) }}" 
                                           class="inline-flex items-center px-3 py-2 border border-amber-500/30 text-xs font-medium rounded-md text-amber-400 bg-amber-500/10 hover:bg-amber-500/20 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 focus:ring-offset-slate-900 transition-all duration-200">
                                            Add Account
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <div>
                        <label class="block text-white font-medium mb-2">Speech-to-Text Consent Requirement</label>
                        <div class="space-y-2">
                            <label class="flex items-center space-x-3">
                                <input type="radio" 
                                       name="stt_consent_requirement" 
                                       value="optional" 
                                       wire:model.live="form.stt_consent_requirement"
                                       class="h-4 w-4 text-amber-500 focus:ring-amber-500 border-slate-600 bg-slate-800">
                                <div>
                                    <span class="text-white text-sm font-medium">Optional</span>
                                    <p class="text-slate-400 text-xs">Participants can decline and still join the room</p>
                                </div>
                            </label>
                            <label class="flex items-center space-x-3">
                                <input type="radio" 
                                       name="stt_consent_requirement" 
                                       value="required" 
                                       wire:model.live="form.stt_consent_requirement"
                                       class="h-4 w-4 text-amber-500 focus:ring-amber-500 border-slate-600 bg-slate-800">
                                <div>
                                    <span class="text-white text-sm font-medium">Required</span>
                                    <p class="text-slate-400 text-xs">Participants must consent or will be redirected</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Recording Tab -->
        <div x-show="activeTab === 'recording'" class="p-6 space-y-6">
            <div>
                <h4 class="text-lg font-outfit font-semibold text-white mb-2">Video Recording Settings</h4>
                <p class="text-slate-400 text-sm mb-4">Configure video recording and storage for participant sessions</p>
            </div>
            
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

            {{-- Storage Provider Selection --}}
            @if($form->recording_enabled)
                <div class="space-y-4">
                    {{-- Recording Consent Requirement --}}
                <div>
                    <label class="block text-white font-medium mb-2">Video Recording Consent Requirement</label>
                    <div class="space-y-2 mb-4">
                        <label class="flex items-center space-x-3">
                            <input type="radio" 
                                   name="recording_consent_requirement" 
                                   value="optional" 
                                   wire:model.live="form.recording_consent_requirement"
                                   class="h-4 w-4 text-amber-500 focus:ring-amber-500 border-slate-600 bg-slate-800">
                            <div>
                                <span class="text-white text-sm font-medium">Optional</span>
                                <p class="text-slate-400 text-xs">Participants can decline and still join the room</p>
                            </div>
                        </label>
                        <label class="flex items-center space-x-3">
                            <input type="radio" 
                                   name="recording_consent_requirement" 
                                   value="required" 
                                   wire:model.live="form.recording_consent_requirement"
                                   class="h-4 w-4 text-amber-500 focus:ring-amber-500 border-slate-600 bg-slate-800">
                            <div>
                                <span class="text-white text-sm font-medium">Required</span>
                                <p class="text-slate-400 text-xs">Participants must consent or will be redirected</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-white font-medium mb-3">Storage Provider</label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        {{-- Local Device Storage --}}
                        <label class="relative cursor-pointer">
                            <input type="radio" 
                                   name="storage_provider" 
                                   value="local_device" 
                                   wire:model.live="form.storage_provider"
                                   class="sr-only">
                            <div class="border-2 rounded-lg p-4 transition-all duration-200 @if($form->storage_provider === 'local_device') border-amber-500 bg-amber-500/10 @else border-slate-600 hover:border-slate-500 @endif">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-full @if($form->storage_provider === 'local_device') bg-amber-500 @else bg-slate-600 @endif flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v2M7 7h10"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-white">Local Device</h4>
                                        <p class="text-xs text-slate-400">Save to your computer</p>
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
        </div>

        <!-- Participants Tab -->
        <div x-show="activeTab === 'participants'" class="p-6 space-y-6">
            @if($participants->count() > 0)
                <div>
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h4 class="text-lg font-outfit font-semibold text-white mb-2">Participant Consent Status</h4>
                            <p class="text-slate-400 text-sm">View and manage consent decisions for room participants</p>
                        </div>
                        <div class="flex items-center space-x-3">
                            @if($form->stt_enabled)
                                <button type="button" 
                                        wire:click="resetAllSttConsent" 
                                        wire:confirm="Are you sure you want to reset all STT consent decisions? Participants will need to consent again."
                                        class="inline-flex items-center px-3 py-2 border border-amber-500/30 text-xs font-medium rounded-md text-amber-400 bg-amber-500/10 hover:bg-amber-500/20 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 focus:ring-offset-slate-900 transition-all duration-200">
                                    Reset All STT
                                </button>
                            @endif
                            @if($form->recording_enabled)
                                <button type="button" 
                                        wire:click="resetAllRecordingConsent" 
                                        wire:confirm="Are you sure you want to reset all recording consent decisions? Participants will need to consent again."
                                        class="inline-flex items-center px-3 py-2 border border-red-500/30 text-xs font-medium rounded-md text-red-400 bg-red-500/10 hover:bg-red-500/20 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:ring-offset-slate-900 transition-all duration-200">
                                    Reset All Recording
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-4">
                        @foreach($participants as $participant)
                            <div class="bg-slate-800/50 rounded-lg p-4 border border-slate-600/30">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-slate-600 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-white">{{ $participant['display_name'] }}</h4>
                                            <p class="text-sm text-slate-400">
                                                @if($participant['character_class'])
                                                    {{ ucfirst($participant['character_class']) }}
                                                @else
                                                    No class
                                                @endif
                                                • Joined {{ \Carbon\Carbon::parse($participant['joined_at'])->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center space-x-4">
                                        {{-- STT Consent Status --}}
                                        @if($form->stt_enabled)
                                            <div class="text-center">
                                                <div class="text-xs text-slate-400 mb-1">STT Consent</div>
                                                @if(is_null($participant['stt_consent_given']))
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-500/10 text-yellow-400 border border-yellow-500/20">
                                                        Pending
                                                    </span>
                                                @elseif($participant['stt_consent_given'])
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                                        ✓ Granted
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-500/10 text-red-400 border border-red-500/20">
                                                        ✗ Denied
                                                    </span>
                                                @endif
                                                @if(!is_null($participant['stt_consent_given']))
                                                    <button type="button" 
                                                            wire:click="resetSttConsent({{ $participant['id'] }})"
                                                            wire:confirm="Reset STT consent for {{ $participant['display_name'] }}?"
                                                            class="block mt-1 text-xs text-amber-400 hover:text-amber-300 underline">
                                                        Reset
                                                    </button>
                                                @endif
                                            </div>
                                        @endif

                                        {{-- Recording Consent Status --}}
                                        @if($form->recording_enabled)
                                            <div class="text-center">
                                                <div class="text-xs text-slate-400 mb-1">Recording Consent</div>
                                                @if(is_null($participant['recording_consent_given']))
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-500/10 text-yellow-400 border border-yellow-500/20">
                                                        Pending
                                                    </span>
                                                @elseif($participant['recording_consent_given'])
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                                        ✓ Granted
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-500/10 text-red-400 border border-red-500/20">
                                                        ✗ Denied
                                                    </span>
                                                @endif
                                                @if(!is_null($participant['recording_consent_given']))
                                                    <button type="button" 
                                                            wire:click="resetRecordingConsent({{ $participant['id'] }})"
                                                            wire:confirm="Reset recording consent for {{ $participant['display_name'] }}?"
                                                            class="block mt-1 text-xs text-red-400 hover:text-red-300 underline">
                                                        Reset
                                                    </button>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-slate-300">No participants yet</h3>
                    <p class="mt-1 text-sm text-slate-500">Participants will appear here once they join the room.</p>
                </div>
            @endif
        </div>

    </form>

    <!-- Footer with Messages and Save Button -->
    <div class="border-t border-slate-700 p-6 space-y-4">
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
        <div class="flex items-center justify-between">
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
                    form="settings-form"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 focus:ring-offset-slate-900 transition-all duration-200">
                <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span wire:loading.remove wire:target="save">Save Settings</span>
                <span wire:loading wire:target="save">Saving...</span>
            </button>
        </div>
    </div>
</div>
