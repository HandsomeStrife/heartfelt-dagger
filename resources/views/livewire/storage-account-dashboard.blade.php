<div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950">
    <div class="px-4 sm:px-6 lg:px-8 pt-12 pb-16">
        <div class="max-w-6xl mx-auto space-y-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="font-outfit text-3xl text-white tracking-wide">
                        Storage Accounts
                    </h1>
                    <p class="text-slate-300 text-lg">
                        Manage your cloud storage connections for video recording
                    </p>
                </div>
                <div class="flex space-x-3">
                    <button wire:click="showAddAccount('wasabi')" 
                            class="inline-flex items-center bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-400 hover:to-red-400 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 shadow-lg hover:shadow-orange-500/25">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Add Wasabi Account
                    </button>
                    <button wire:click="showAddAccount('google_drive')" 
                            class="inline-flex items-center bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-400 hover:to-indigo-400 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 shadow-lg hover:shadow-blue-500/25">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Add Google Drive
                    </button>
                </div>
            </div>

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="p-4 bg-emerald-500/20 border border-emerald-500/30 rounded-xl text-emerald-400">
                    <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 bg-red-500/20 border border-red-500/30 rounded-xl text-red-400">
                    <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ session('error') }}
                </div>
            @endif

            {{-- Storage Accounts Grid --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                {{-- Wasabi Accounts Section --}}
                <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-gradient-to-br from-orange-500/20 to-red-500/20 rounded-xl flex items-center justify-center border border-orange-500/30 mr-3">
                            <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="font-outfit text-xl font-bold text-white">Wasabi Accounts</h2>
                            <p class="text-slate-400 text-sm">S3-compatible cloud storage</p>
                        </div>
                    </div>

                    @if($wasabiAccounts->isEmpty())
                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-slate-800 rounded-xl flex items-center justify-center border border-slate-600 mb-4 mx-auto">
                                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                                </svg>
                            </div>
                            <h3 class="text-white font-semibold mb-2">No Wasabi accounts connected</h3>
                            <p class="text-slate-400 text-sm mb-4">Connect your Wasabi account to store recordings</p>
                            <button wire:click="showAddAccount('wasabi')" 
                                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-400 hover:to-red-400 text-white text-sm font-semibold rounded-lg transition-all duration-300">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Add First Account
                            </button>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($wasabiAccounts as $account)
                                <div class="bg-slate-800/50 border border-slate-600/50 rounded-xl p-4 hover:bg-slate-800/70 transition-colors">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center mb-2">
                                                <h3 class="font-semibold text-white mr-3">{{ $account->display_name }}</h3>
                                                @if($account->is_active)
                                                    <span class="px-2 py-1 bg-emerald-500/20 text-emerald-400 text-xs rounded-full border border-emerald-500/30">
                                                        <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        Active
                                                    </span>
                                                @else
                                                    <span class="px-2 py-1 bg-slate-500/20 text-slate-400 text-xs rounded-full border border-slate-500/30">
                                                        <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        Inactive
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-sm text-slate-300">
                                                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                                                </svg>
                                                {{ $account->encrypted_credentials['bucket'] ?? 'Unknown bucket' }}
                                                <span class="ml-4">
                                                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    {{ $account->encrypted_credentials['region'] ?? 'Unknown region' }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <button wire:click="testConnection({{ $account->id }})" 
                                                    class="px-3 py-1 bg-blue-600 hover:bg-blue-500 text-white rounded transition-colors duration-200 text-sm">
                                                <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                                                </svg>
                                                Test
                                            </button>
                                            <button wire:click="toggleAccountStatus({{ $account->id }})" 
                                                    class="px-3 py-1 {{ $account->is_active ? 'bg-slate-600 hover:bg-slate-500' : 'bg-emerald-600 hover:bg-emerald-500' }} text-white rounded transition-colors duration-200 text-sm">
                                                @if($account->is_active)
                                                    <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    Deactivate
                                                @else
                                                    <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293H15M9 10V9a2 2 0 012-2h2a2 2 0 012 2v1M9 10v5a2 2 0 002 2h2a2 2 0 002-2v-5" />
                                                    </svg>
                                                    Activate
                                                @endif
                                            </button>
                                            <button wire:click="deleteAccount({{ $account->id }})" 
                                                    wire:confirm="Are you sure you want to delete this storage account? This action cannot be undone."
                                                    class="px-3 py-1 bg-red-600 hover:bg-red-500 text-white rounded transition-colors duration-200 text-sm">
                                                <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Google Drive Accounts Section --}}
                <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500/20 to-indigo-500/20 rounded-xl flex items-center justify-center border border-blue-500/30 mr-3">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="font-outfit text-xl font-bold text-white">Google Drive Accounts</h2>
                            <p class="text-slate-400 text-sm">Cloud storage via Google</p>
                        </div>
                    </div>

                    @if($googleDriveAccounts->isEmpty())
                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-slate-800 rounded-xl flex items-center justify-center border border-slate-600 mb-4 mx-auto">
                                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <h3 class="text-white font-semibold mb-2">No Google Drive accounts connected</h3>
                            <p class="text-slate-400 text-sm mb-4">Connect your Google Drive to store recordings</p>
                            <button wire:click="showAddAccount('google_drive')" 
                                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-400 hover:to-indigo-400 text-white text-sm font-semibold rounded-lg transition-all duration-300">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Add First Account
                            </button>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($googleDriveAccounts as $account)
                                <div class="bg-slate-800/50 border border-slate-600/50 rounded-xl p-4 hover:bg-slate-800/70 transition-colors">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center mb-2">
                                                <h3 class="font-semibold text-white mr-3">{{ $account->display_name }}</h3>
                                                @if($account->is_active)
                                                    <span class="px-2 py-1 bg-emerald-500/20 text-emerald-400 text-xs rounded-full border border-emerald-500/30">
                                                        <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        Active
                                                    </span>
                                                @else
                                                    <span class="px-2 py-1 bg-slate-500/20 text-slate-400 text-xs rounded-full border border-slate-500/30">
                                                        <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        Inactive
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-sm text-slate-300">
                                                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                                {{ $account->encrypted_credentials['email'] ?? 'Connected Account' }}
                                                <span class="ml-4">
                                                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 4v10a2 2 0 002 2h4a2 2 0 002-2V11m-6 0h6m-6 0a2 2 0 00-2 2v8a2 2 0 002 2h4a2 2 0 002-2v-8a2 2 0 00-2-2" />
                                                    </svg>
                                                    Connected {{ $account->created_at->diffForHumans() }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <button wire:click="testConnection({{ $account->id }})" 
                                                    class="px-3 py-1 bg-blue-600 hover:bg-blue-500 text-white rounded transition-colors duration-200 text-sm">
                                                <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                                                </svg>
                                                Test
                                            </button>
                                            <button wire:click="toggleAccountStatus({{ $account->id }})" 
                                                    class="px-3 py-1 {{ $account->is_active ? 'bg-slate-600 hover:bg-slate-500' : 'bg-emerald-600 hover:bg-emerald-500' }} text-white rounded transition-colors duration-200 text-sm">
                                                @if($account->is_active)
                                                    <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    Deactivate
                                                @else
                                                    <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293H15M9 10V9a2 2 0 012-2h2a2 2 0 012 2v1M9 10v5a2 2 0 002 2h2a2 2 0 002-2v-5" />
                                                    </svg>
                                                    Activate
                                                @endif
                                            </button>
                                            <button wire:click="deleteAccount({{ $account->id }})" 
                                                    wire:confirm="Are you sure you want to disconnect this Google Drive account? This action cannot be undone."
                                                    class="px-3 py-1 bg-red-600 hover:bg-red-500 text-white rounded transition-colors duration-200 text-sm">
                                                <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                                </svg>
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

            {{-- Add Account Modal --}}
            @if($showAddAccountModal)
                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50">
                    <div class="bg-slate-900 border border-slate-700 rounded-xl p-6 w-full max-w-md">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-outfit font-bold text-white">
                                Add {{ $selectedProvider === 'wasabi' ? 'Wasabi' : 'Google Drive' }} Account
                            </h3>
                            <button wire:click="hideAddAccountModal" 
                                    class="text-slate-400 hover:text-white transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        
                        <p class="text-slate-300 mb-6">
                            @if($selectedProvider === 'wasabi')
                                You'll be redirected to set up your Wasabi credentials securely.
                            @else
                                You'll be redirected to authenticate with Google Drive.
                            @endif
                        </p>

                        <div class="flex justify-end space-x-3">
                            <button wire:click="hideAddAccountModal" 
                                    class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg transition-colors duration-200">
                                Cancel
                            </button>
                            @if($selectedProvider === 'wasabi')
                                <a href="{{ route('wasabi.connect') }}" 
                                   class="px-4 py-2 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white rounded-lg transition-all duration-200 font-medium inline-block">
                                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                    Connect Wasabi
                                </a>
                            @else
                                <a href="{{ route('google-drive.authorize') }}" 
                                   class="px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 text-white rounded-lg transition-all duration-200 font-medium inline-block">
                                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    Connect Google Drive
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
