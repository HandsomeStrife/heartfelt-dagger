<div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950">
    <!-- Compact Navigation -->
    <x-sub-navigation>
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a 
                    href="{{ route('dashboard') }}"
                    class="p-1.5 text-slate-400 hover:text-white hover:bg-slate-700/50 rounded-md transition-colors"
                    title="Back to dashboard"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h1 class="font-outfit text-lg font-bold text-white tracking-wide">
                        Storage & Services
                    </h1>
                    <p class="text-slate-400 text-xs">
                        Manage your cloud storage and transcription services
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <button wire:click="showAddAccount('wasabi')" 
                        class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white text-sm font-medium rounded-md transition-all duration-200">
                    <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add Wasabi
                </button>
                <button wire:click="showAddAccount('google_drive')" 
                        class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 text-white text-sm font-medium rounded-md transition-all duration-200">
                    <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add Google Drive
                </button>
                <button wire:click="showAddAccount('assemblyai')" 
                        class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white text-sm font-medium rounded-md transition-all duration-200">
                    <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add AssemblyAI
                </button>
            </div>
        </div>
    </x-sub-navigation>

    <div class="px-4 sm:px-6 lg:px-8 pt-8 pb-12">
        <div class="max-w-5xl mx-auto space-y-6">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="p-3 bg-emerald-500/20 border border-emerald-500/30 rounded-lg text-emerald-400 text-sm">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="p-3 bg-red-500/20 border border-red-500/30 rounded-lg text-red-400 text-sm">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ session('error') }}
                </div>
            @endif

            <!-- Tabbed Interface -->
            <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-xl overflow-hidden" x-data="{ activeTab: 'storage' }">
                <!-- Tab Navigation -->
                <div class="border-b border-slate-700/50">
                    <nav class="flex space-x-0">
                        <button @click="activeTab = 'storage'" 
                                :class="{ 'bg-slate-800 text-white border-amber-500': activeTab === 'storage', 'text-slate-400 hover:text-slate-300': activeTab !== 'storage' }"
                                class="px-4 py-3 text-sm font-medium border-b-2 border-transparent transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                            </svg>
                            Cloud Storage
                        </button>
                        <button @click="activeTab = 'transcription'" 
                                :class="{ 'bg-slate-800 text-white border-amber-500': activeTab === 'transcription', 'text-slate-400 hover:text-slate-300': activeTab !== 'transcription' }"
                                class="px-4 py-3 text-sm font-medium border-b-2 border-transparent transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                            </svg>
                            Transcription
                        </button>
                        <button @click="activeTab = 'stats'" 
                                :class="{ 'bg-slate-800 text-white border-amber-500': activeTab === 'stats', 'text-slate-400 hover:text-slate-300': activeTab !== 'stats' }"
                                class="px-4 py-3 text-sm font-medium border-b-2 border-transparent transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            Statistics
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="p-4">
                    <!-- Cloud Storage Tab -->
                    <div x-show="activeTab === 'storage'" x-transition x-cloak>
                        <div class="space-y-4">
                            {{-- Wasabi Accounts --}}
                            <div class="bg-slate-800/50 border border-slate-600/50 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 flex items-center justify-center mr-3">
                                            <x-icons.brands.wasabi class="w-6 h-6" />
                                        </div>
                                        <div>
                                            <h2 class="font-outfit text-lg font-bold text-white">Wasabi</h2>
                                            <p class="text-slate-400 text-xs">S3-compatible storage</p>
                                        </div>
                                    </div>
                                    @if(!$wasabiAccounts->isEmpty())
                                        <span class="px-2 py-0.5 bg-emerald-500/20 text-emerald-400 text-xs rounded-full border border-emerald-500/30">
                                            {{ $wasabiAccounts->count() }} connected
                                        </span>
                                    @endif
                                </div>

                                @if($wasabiAccounts->isEmpty())
                                    <div class="text-center py-6">
                                        <h3 class="text-white font-medium mb-1 text-sm">No accounts connected</h3>
                                        <p class="text-slate-400 text-xs mb-3">Connect your Wasabi account</p>
                                        <button wire:click="showAddAccount('wasabi')" 
                                                class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white text-xs font-medium rounded-md transition-all duration-200">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                            Connect
                                        </button>
                                    </div>
                                @else
                                    <div class="space-y-2">
                                        @foreach($wasabiAccounts as $account)
                                            <div class="bg-slate-700/50 border border-slate-600/50 rounded-md p-3 hover:bg-slate-700/70 transition-colors">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex items-center mb-1">
                                                            <h3 class="font-medium text-white mr-2 text-sm truncate">{{ $account->display_name }}</h3>
                                                            @if($account->is_active)
                                                                <span class="px-1.5 py-0.5 bg-emerald-500/20 text-emerald-400 text-xs rounded-full border border-emerald-500/30">
                                                                    Active
                                                                </span>
                                                            @else
                                                                <span class="px-1.5 py-0.5 bg-slate-500/20 text-slate-400 text-xs rounded-full border border-slate-500/30">
                                                                    Inactive
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <div class="text-xs text-slate-300 truncate">
                                                            <span class="inline-flex items-center mr-3">
                                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                                                                </svg>
                                                                {{ $account->encrypted_credentials['bucket'] ?? 'Unknown' }}
                                                            </span>
                                                            <span class="inline-flex items-center">
                                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>
                                                                {{ $account->encrypted_credentials['region'] ?? 'Unknown' }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center space-x-1 ml-2">
                                                        <button wire:click="testConnection({{ $account->id }})" 
                                                                class="px-2 py-1 bg-blue-600 hover:bg-blue-500 text-white rounded text-xs transition-colors duration-200">
                                                            Test
                                                        </button>
                                                        <button wire:click="toggleAccountStatus({{ $account->id }})" 
                                                                class="px-2 py-1 {{ $account->is_active ? 'bg-slate-600 hover:bg-slate-500' : 'bg-emerald-600 hover:bg-emerald-500' }} text-white rounded text-xs transition-colors duration-200">
                                                            {{ $account->is_active ? 'Deactivate' : 'Activate' }}
                                                        </button>
                                                        <button wire:click="deleteAccount({{ $account->id }})" 
                                                                wire:confirm="Are you sure you want to delete this storage account? This action cannot be undone."
                                                                class="px-2 py-1 bg-red-600 hover:bg-red-500 text-white rounded text-xs transition-colors duration-200">
                                                            Delete
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            {{-- Google Drive Accounts --}}
                            <div class="bg-slate-800/50 border border-slate-600/50 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 flex items-center justify-center mr-3">
                                            <x-icons.brands.google-drive class="w-6 h-6" />
                                        </div>
                                        <div>
                                            <h2 class="font-outfit text-lg font-bold text-white">Google Drive</h2>
                                            <p class="text-slate-400 text-xs">Cloud storage via Google</p>
                                        </div>
                                    </div>
                                    @if(!$googleDriveAccounts->isEmpty())
                                        <span class="px-2 py-0.5 bg-emerald-500/20 text-emerald-400 text-xs rounded-full border border-emerald-500/30">
                                            {{ $googleDriveAccounts->count() }} connected
                                        </span>
                                    @endif
                                </div>

                                @if($googleDriveAccounts->isEmpty())
                                    <div class="text-center py-6">
                                        <h3 class="text-white font-medium mb-1 text-sm">No accounts connected</h3>
                                        <p class="text-slate-400 text-xs mb-3">Connect your Google Drive</p>
                                        <button wire:click="showAddAccount('google_drive')" 
                                                class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 text-white text-xs font-medium rounded-md transition-all duration-200">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                            Connect
                                        </button>
                                    </div>
                                @else
                                    <div class="space-y-2">
                                        @foreach($googleDriveAccounts as $account)
                                            <div class="bg-slate-700/50 border border-slate-600/50 rounded-md p-3 hover:bg-slate-700/70 transition-colors">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex items-center mb-1">
                                                            <h3 class="font-medium text-white mr-2 text-sm truncate">{{ $account->display_name }}</h3>
                                                            @if($account->is_active)
                                                                <span class="px-1.5 py-0.5 bg-emerald-500/20 text-emerald-400 text-xs rounded-full border border-emerald-500/30">
                                                                    Active
                                                                </span>
                                                            @else
                                                                <span class="px-1.5 py-0.5 bg-slate-500/20 text-slate-400 text-xs rounded-full border border-slate-500/30">
                                                                    Inactive
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <div class="text-xs text-slate-300 truncate">
                                                            <span class="inline-flex items-center mr-3">
                                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                                </svg>
                                                                {{ $account->encrypted_credentials['email'] ?? 'Connected Account' }}
                                                            </span>
                                                            <span class="inline-flex items-center">
                                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 4v10a2 2 0 002 2h4a2 2 0 002-2V11m-6 0h6m-6 0a2 2 0 00-2 2v8a2 2 0 002 2h4a2 2 0 002-2v-8a2 2 0 00-2-2" />
                                                                </svg>
                                                                {{ $account->created_at->diffForHumans() }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center space-x-1 ml-2">
                                                        <button wire:click="testConnection({{ $account->id }})" 
                                                                class="px-2 py-1 bg-blue-600 hover:bg-blue-500 text-white rounded text-xs transition-colors duration-200">
                                                            Test
                                                        </button>
                                                        <button wire:click="toggleAccountStatus({{ $account->id }})" 
                                                                class="px-2 py-1 {{ $account->is_active ? 'bg-slate-600 hover:bg-slate-500' : 'bg-emerald-600 hover:bg-emerald-500' }} text-white rounded text-xs transition-colors duration-200">
                                                            {{ $account->is_active ? 'Deactivate' : 'Activate' }}
                                                        </button>
                                                        <button wire:click="deleteAccount({{ $account->id }})" 
                                                                wire:confirm="Are you sure you want to disconnect this Google Drive account? This action cannot be undone."
                                                                class="px-2 py-1 bg-red-600 hover:bg-red-500 text-white rounded text-xs transition-colors duration-200">
                                                            Disconnect
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Transcription Tab -->
                    <div x-show="activeTab === 'transcription'" x-transition  x-cloak>
                        <div class="space-y-4">
                            {{-- AssemblyAI Accounts --}}
                            <div class="bg-slate-800/50 border border-slate-600/50 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 flex items-center justify-center mr-3">
                                            <x-icons.brands.assembly-ai class="w-6 h-6" />
                                        </div>
                                        <div>
                                            <h2 class="font-outfit text-lg font-bold text-white">AssemblyAI</h2>
                                            <p class="text-slate-400 text-xs">Speech-to-text transcription</p>
                                        </div>
                                    </div>
                                    @if(!$assemblyaiAccounts->isEmpty())
                                        <span class="px-2 py-0.5 bg-emerald-500/20 text-emerald-400 text-xs rounded-full border border-emerald-500/30">
                                            {{ $assemblyaiAccounts->count() }} connected
                                        </span>
                                    @endif
                                </div>

                                @if($assemblyaiAccounts->isEmpty())
                                    <div class="text-center py-6">
                                        <h3 class="text-white font-medium mb-1 text-sm">No accounts connected</h3>
                                        <p class="text-slate-400 text-xs mb-3">Connect AssemblyAI for transcription</p>
                                        <button wire:click="showAddAccount('assemblyai')" 
                                                class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white text-xs font-medium rounded-md transition-all duration-200">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                            Connect
                                        </button>
                                    </div>
                                @else
                                    <div class="space-y-2">
                                        @foreach($assemblyaiAccounts as $account)
                                            <div class="bg-slate-700/50 border border-slate-600/50 rounded-md p-3 hover:bg-slate-700/70 transition-colors">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex items-center mb-1">
                                                            <h3 class="font-medium text-white mr-2 text-sm truncate">{{ $account->display_name }}</h3>
                                                            @if($account->is_active)
                                                                <span class="px-1.5 py-0.5 bg-emerald-500/20 text-emerald-400 text-xs rounded-full border border-emerald-500/30">
                                                                    Active
                                                                </span>
                                                            @else
                                                                <span class="px-1.5 py-0.5 bg-slate-500/20 text-slate-400 text-xs rounded-full border border-slate-500/30">
                                                                    Inactive
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <div class="text-xs text-slate-400">
                                                            Connected {{ $account->created_at->diffForHumans() }}
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center space-x-1 ml-2">
                                                        <button wire:click="testConnection({{ $account->id }})" 
                                                                class="px-2 py-1 bg-blue-600 hover:bg-blue-500 text-white rounded text-xs transition-colors duration-200">
                                                            Test
                                                        </button>
                                                        <button wire:click="toggleAccountStatus({{ $account->id }})" 
                                                                class="px-2 py-1 {{ $account->is_active ? 'bg-slate-600 hover:bg-slate-500' : 'bg-emerald-600 hover:bg-emerald-500' }} text-white rounded text-xs transition-colors duration-200">
                                                            {{ $account->is_active ? 'Deactivate' : 'Activate' }}
                                                        </button>
                                                        <button wire:click="deleteAccount({{ $account->id }})" 
                                                                wire:confirm="Are you sure you want to delete this AssemblyAI account? This action cannot be undone."
                                                                class="px-2 py-1 bg-red-600 hover:bg-red-500 text-white rounded text-xs transition-colors duration-200">
                                                            Delete
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <!-- Future Transcription Services Placeholder -->
                            <div class="bg-slate-800/50 border border-slate-600/50 rounded-lg p-4 opacity-60">
                                <div class="flex items-center mb-4">
                                        <div class="w-8 h-8 flex items-center justify-center mr-3">
                                            <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                                            </svg>
                                        </div>
                                    <div>
                                        <h2 class="font-outfit text-lg font-bold text-white">More Services</h2>
                                        <p class="text-slate-400 text-xs">Additional transcription services coming soon</p>
                                    </div>
                                </div>
                                <div class="text-center py-6">
                                    <p class="text-slate-400 text-xs">Future transcription services will appear here</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Tab -->
                    <div x-show="activeTab === 'stats'" x-transition  x-cloak>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div class="bg-slate-800/50 border border-slate-600/50 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-slate-400 text-xs font-medium">Wasabi Storage</h3>
                                        <p class="text-white text-xl font-bold">{{ $wasabiAccounts->count() }}</p>
                                    </div>
                                    <div class="w-8 h-8 flex items-center justify-center">
                                        <x-icons.brands.wasabi class="w-5 h-5" />
                                    </div>
                                </div>
                            </div>

                            <div class="bg-slate-800/50 border border-slate-600/50 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-slate-400 text-xs font-medium">Google Drive</h3>
                                        <p class="text-white text-xl font-bold">{{ $googleDriveAccounts->count() }}</p>
                                    </div>
                                    <div class="w-8 h-8 flex items-center justify-center">
                                        <x-icons.brands.google-drive class="w-5 h-5" />
                                    </div>
                                </div>
                            </div>

                            <div class="bg-slate-800/50 border border-slate-600/50 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-slate-400 text-xs font-medium">AssemblyAI</h3>
                                        <p class="text-white text-xl font-bold">{{ $assemblyaiAccounts->count() }}</p>
                                    </div>
                                    <div class="w-8 h-8 flex items-center justify-center">
                                        <x-icons.brands.assembly-ai class="w-5 h-5" />
                                    </div>
                                </div>
                            </div>

                            <div class="bg-slate-800/50 border border-slate-600/50 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-slate-400 text-xs font-medium">Total Active</h3>
                                        <p class="text-white text-xl font-bold">{{ $wasabiAccounts->where('is_active', true)->count() + $googleDriveAccounts->where('is_active', true)->count() + $assemblyaiAccounts->where('is_active', true)->count() }}</p>
                                    </div>
                                    <div class="w-8 h-8 bg-emerald-500/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Service Status Overview -->
                        <div class="mt-6 bg-slate-800/50 border border-slate-600/50 rounded-lg p-4">
                            <h3 class="font-medium text-white mb-4 text-sm">Service Status Overview</h3>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between py-2 border-b border-slate-600/50 last:border-b-0">
                                    <div class="flex items-center">
                                        <x-icons.brands.wasabi class="w-4 h-4 mr-2" />
                                        <span class="text-slate-300 text-sm">Wasabi Storage</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-slate-400 text-xs">{{ $wasabiAccounts->where('is_active', true)->count() }}/{{ $wasabiAccounts->count() }} active</span>
                                        @if($wasabiAccounts->where('is_active', true)->count() > 0)
                                            <span class="w-2 h-2 bg-emerald-400 rounded-full"></span>
                                        @else
                                            <span class="w-2 h-2 bg-slate-400 rounded-full"></span>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex items-center justify-between py-2 border-b border-slate-600/50 last:border-b-0">
                                    <div class="flex items-center">
                                        <x-icons.brands.google-drive class="w-4 h-4 mr-2" />
                                        <span class="text-slate-300 text-sm">Google Drive</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-slate-400 text-xs">{{ $googleDriveAccounts->where('is_active', true)->count() }}/{{ $googleDriveAccounts->count() }} active</span>
                                        @if($googleDriveAccounts->where('is_active', true)->count() > 0)
                                            <span class="w-2 h-2 bg-emerald-400 rounded-full"></span>
                                        @else
                                            <span class="w-2 h-2 bg-slate-400 rounded-full"></span>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex items-center justify-between py-2">
                                    <div class="flex items-center">
                                        <x-icons.brands.assembly-ai class="w-4 h-4 mr-2" />
                                        <span class="text-slate-300 text-sm">AssemblyAI</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-slate-400 text-xs">{{ $assemblyaiAccounts->where('is_active', true)->count() }}/{{ $assemblyaiAccounts->count() }} active</span>
                                        @if($assemblyaiAccounts->where('is_active', true)->count() > 0)
                                            <span class="w-2 h-2 bg-emerald-400 rounded-full"></span>
                                        @else
                                            <span class="w-2 h-2 bg-slate-400 rounded-full"></span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Add Account Modal --}}
            @if($showAddAccountModal)
                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50">
                    <div class="bg-slate-900 border border-slate-700 rounded-xl p-4 w-full max-w-sm">
                        @if($selectedProvider === 'assemblyai')
                            {{-- AssemblyAI Form --}}
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-lg font-outfit font-bold text-white">
                                    Add AssemblyAI
                                </h3>
                                <button wire:click="hideAddAccountModal" 
                                        class="text-slate-400 hover:text-white transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            
                            <form wire:submit="saveAssemblyAIAccount" class="space-y-3">
                                <!-- Display Name -->
                                <div>
                                    <label for="display_name" class="block text-sm font-medium text-slate-300 mb-1">
                                        Account Name
                                    </label>
                                    <input type="text" 
                                           id="display_name" 
                                           wire:model="assemblyaiForm.display_name"
                                           placeholder="e.g., My AssemblyAI Account"
                                           class="w-full bg-slate-800 border border-slate-600 rounded-md px-3 py-2 text-white placeholder-slate-400 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 text-sm">
                                    @error('assemblyaiForm.display_name')
                                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- API Key -->
                                <div>
                                    <label for="api_key" class="block text-sm font-medium text-slate-300 mb-1">
                                        API Key
                                    </label>
                                    <input type="password" 
                                           id="api_key" 
                                           wire:model="assemblyaiForm.api_key"
                                           placeholder="Your AssemblyAI API Key"
                                           class="w-full bg-slate-800 border border-slate-600 rounded-md px-3 py-2 text-white placeholder-slate-400 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 text-sm">
                                    @error('assemblyaiForm.api_key')
                                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Connection Test -->
                                <div class="flex items-center justify-between pt-3 border-t border-slate-700">
                                    <button type="button" 
                                            wire:click="testAssemblyAIFormConnection"
                                            wire:loading.attr="disabled"
                                            wire:target="testAssemblyAIFormConnection"
                                            class="inline-flex items-center px-3 py-1.5 border border-slate-600 text-sm font-medium rounded-md text-slate-300 bg-slate-800 hover:bg-slate-700 transition-colors duration-200 disabled:opacity-50">
                                        <svg wire:loading wire:target="testAssemblyAIFormConnection" class="animate-spin -ml-1 mr-1.5 h-3 w-3 text-slate-300" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span wire:loading.remove wire:target="testAssemblyAIFormConnection">Test</span>
                                        <span wire:loading wire:target="testAssemblyAIFormConnection">Testing...</span>
                                    </button>

                                    <!-- Connection Result -->
                                    @if($connectionResult === 'success')
                                        <div class="flex items-center text-emerald-400 text-xs">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Success!
                                        </div>
                                    @elseif($connectionResult === 'error')
                                        <div class="flex items-center text-red-400 text-xs">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Failed
                                        </div>
                                    @endif
                                </div>

                                @error('assemblyai_connection')
                                    <div class="bg-red-500/10 border border-red-500/20 rounded-md p-2">
                                        <p class="text-red-400 text-xs">{{ $message }}</p>
                                    </div>
                                @enderror

                                @error('assemblyai_form')
                                    <div class="bg-red-500/10 border border-red-500/20 rounded-md p-2">
                                        <p class="text-red-400 text-xs">{{ $message }}</p>
                                    </div>
                                @enderror

                                <div class="flex justify-end space-x-2 pt-3">
                                    <button type="button"
                                            wire:click="hideAddAccountModal" 
                                            class="px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-white rounded-md transition-colors duration-200 text-sm">
                                        Cancel
                                    </button>
                                    <button type="submit"
                                            wire:loading.attr="disabled"
                                            wire:target="saveAssemblyAIAccount"
                                            class="px-3 py-1.5 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white rounded-md transition-all duration-200 font-medium disabled:opacity-50 text-sm">
                                        <span wire:loading.remove wire:target="saveAssemblyAIAccount">Connect</span>
                                        <span wire:loading wire:target="saveAssemblyAIAccount">Connecting...</span>
                                    </button>
                                </div>
                            </form>
                        @else
                            {{-- External Provider Redirect --}}
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-lg font-outfit font-bold text-white">
                                    Add {{ $selectedProvider === 'wasabi' ? 'Wasabi' : 'Google Drive' }}
                                </h3>
                                <button wire:click="hideAddAccountModal" 
                                        class="text-slate-400 hover:text-white transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            
                            <p class="text-slate-300 mb-4 text-sm">
                                @if($selectedProvider === 'wasabi')
                                    You'll be redirected to set up your Wasabi credentials securely.
                                @else
                                    You'll be redirected to authenticate with Google Drive.
                                @endif
                            </p>

                            <div class="flex justify-end space-x-2">
                                <button wire:click="hideAddAccountModal" 
                                        class="px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-white rounded-md transition-colors duration-200 text-sm">
                                    Cancel
                                </button>
                                @if($selectedProvider === 'wasabi')
                                    <a href="{{ route('wasabi.connect') }}" 
                                       class="px-3 py-1.5 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white rounded-md transition-all duration-200 font-medium inline-block text-sm">
                                        Connect
                                    </a>
                                @else
                                    <a href="{{ route('google-drive.authorize') }}" 
                                       class="px-3 py-1.5 bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 text-white rounded-md transition-all duration-200 font-medium inline-block text-sm">
                                        Connect
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>