<div class="space-y-6">
    {{-- Header Section --}}
    <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700 rounded-xl p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-2xl font-outfit font-bold text-white">Storage Accounts</h1>
                <p class="text-slate-300 mt-2">Manage your cloud storage connections for video recording</p>
            </div>
            <div class="flex space-x-3">
                <button wire:click="showAddAccount('wasabi')" 
                        class="px-4 py-2 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-lg hover:from-orange-600 hover:to-red-600 transition-all duration-200 font-medium">
                    <i class="fas fa-plus mr-2"></i>Add Wasabi Account
                </button>
                <button wire:click="showAddAccount('google_drive')" 
                        class="px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-500 text-white rounded-lg hover:from-blue-600 hover:to-indigo-600 transition-all duration-200 font-medium">
                    <i class="fas fa-plus mr-2"></i>Add Google Drive
                </button>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-4 p-4 bg-emerald-500/20 border border-emerald-500/30 rounded-lg text-emerald-400">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-4 bg-red-500/20 border border-red-500/30 rounded-lg text-red-400">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            </div>
        @endif
    </div>

    {{-- Storage Accounts Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Wasabi Accounts Section --}}
        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700 rounded-xl p-6">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-red-500 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-cloud text-white"></i>
                </div>
                <div>
                    <h2 class="text-xl font-outfit font-bold text-white">Wasabi Accounts</h2>
                    <p class="text-slate-400 text-sm">S3-compatible cloud storage</p>
                </div>
            </div>

            @if($wasabiAccounts->isEmpty())
                <div class="text-center py-8 text-slate-400">
                    <i class="fas fa-cloud-upload text-4xl mb-4 opacity-50"></i>
                    <p class="text-lg mb-2">No Wasabi accounts connected</p>
                    <p class="text-sm mb-4">Connect your Wasabi account to store recordings</p>
                    <button wire:click="showAddAccount('wasabi')" 
                            class="px-4 py-2 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-lg hover:from-orange-600 hover:to-red-600 transition-all duration-200 font-medium">
                        <i class="fas fa-plus mr-2"></i>Add First Account
                    </button>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($wasabiAccounts as $account)
                        <div class="bg-slate-800/50 border border-slate-600/50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center mb-2">
                                        <h3 class="font-medium text-white mr-3">{{ $account->display_name }}</h3>
                                        @if($account->is_active)
                                            <span class="px-2 py-1 bg-emerald-500/20 text-emerald-400 text-xs rounded-full border border-emerald-500/30">
                                                <i class="fas fa-check-circle mr-1"></i>Active
                                            </span>
                                        @else
                                            <span class="px-2 py-1 bg-slate-500/20 text-slate-400 text-xs rounded-full border border-slate-500/30">
                                                <i class="fas fa-pause-circle mr-1"></i>Inactive
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-sm text-slate-300">
                                        <i class="fas fa-server mr-2"></i>{{ $account->encrypted_credentials['bucket'] ?? 'Unknown bucket' }}
                                        <span class="ml-4">
                                            <i class="fas fa-globe mr-2"></i>{{ $account->encrypted_credentials['region'] ?? 'Unknown region' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <button wire:click="testConnection({{ $account->id }})" 
                                            class="px-3 py-1 bg-blue-600 hover:bg-blue-500 text-white rounded transition-colors duration-200 text-sm">
                                        <i class="fas fa-plug mr-1"></i>Test
                                    </button>
                                    <button wire:click="toggleAccountStatus({{ $account->id }})" 
                                            class="px-3 py-1 {{ $account->is_active ? 'bg-slate-600 hover:bg-slate-500' : 'bg-emerald-600 hover:bg-emerald-500' }} text-white rounded transition-colors duration-200 text-sm">
                                        @if($account->is_active)
                                            <i class="fas fa-pause mr-1"></i>Deactivate
                                        @else
                                            <i class="fas fa-play mr-1"></i>Activate
                                        @endif
                                    </button>
                                    <button wire:click="deleteAccount({{ $account->id }})" 
                                            wire:confirm="Are you sure you want to delete this storage account? This action cannot be undone."
                                            class="px-3 py-1 bg-red-600 hover:bg-red-500 text-white rounded transition-colors duration-200 text-sm">
                                        <i class="fas fa-trash mr-1"></i>Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Google Drive Accounts Section --}}
        <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700 rounded-xl p-6">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-lg flex items-center justify-center mr-3">
                    <i class="fab fa-google-drive text-white"></i>
                </div>
                <div>
                    <h2 class="text-xl font-outfit font-bold text-white">Google Drive Accounts</h2>
                    <p class="text-slate-400 text-sm">Cloud storage via Google</p>
                </div>
            </div>

            @if($googleDriveAccounts->isEmpty())
                <div class="text-center py-8 text-slate-400">
                    <i class="fab fa-google-drive text-4xl mb-4 opacity-50"></i>
                    <p class="text-lg mb-2">No Google Drive accounts connected</p>
                    <p class="text-sm mb-4">Connect your Google Drive to store recordings</p>
                    <button wire:click="showAddAccount('google_drive')" 
                            class="px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-500 text-white rounded-lg hover:from-blue-600 hover:to-indigo-600 transition-all duration-200 font-medium">
                        <i class="fas fa-plus mr-2"></i>Add First Account
                    </button>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($googleDriveAccounts as $account)
                        <div class="bg-slate-800/50 border border-slate-600/50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center mb-2">
                                        <h3 class="font-medium text-white mr-3">{{ $account->display_name }}</h3>
                                        @if($account->is_active)
                                            <span class="px-2 py-1 bg-emerald-500/20 text-emerald-400 text-xs rounded-full border border-emerald-500/30">
                                                <i class="fas fa-check-circle mr-1"></i>Active
                                            </span>
                                        @else
                                            <span class="px-2 py-1 bg-slate-500/20 text-slate-400 text-xs rounded-full border border-slate-500/30">
                                                <i class="fas fa-pause-circle mr-1"></i>Inactive
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-sm text-slate-300">
                                        <i class="fas fa-user mr-2"></i>{{ $account->encrypted_credentials['email'] ?? 'Connected Account' }}
                                        <span class="ml-4">
                                            <i class="fas fa-calendar mr-2"></i>Connected {{ $account->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <button wire:click="testConnection({{ $account->id }})" 
                                            class="px-3 py-1 bg-blue-600 hover:bg-blue-500 text-white rounded transition-colors duration-200 text-sm">
                                        <i class="fas fa-plug mr-1"></i>Test
                                    </button>
                                    <button wire:click="toggleAccountStatus({{ $account->id }})" 
                                            class="px-3 py-1 {{ $account->is_active ? 'bg-slate-600 hover:bg-slate-500' : 'bg-emerald-600 hover:bg-emerald-500' }} text-white rounded transition-colors duration-200 text-sm">
                                        @if($account->is_active)
                                            <i class="fas fa-pause mr-1"></i>Deactivate
                                        @else
                                            <i class="fas fa-play mr-1"></i>Activate
                                        @endif
                                    </button>
                                    <button wire:click="deleteAccount({{ $account->id }})" 
                                            wire:confirm="Are you sure you want to disconnect this Google Drive account? This action cannot be undone."
                                            class="px-3 py-1 bg-red-600 hover:bg-red-500 text-white rounded transition-colors duration-200 text-sm">
                                        <i class="fas fa-unlink mr-1"></i>Disconnect
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
                        <i class="fas fa-times"></i>
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
                            <i class="fas fa-external-link-alt mr-2"></i>Connect Wasabi
                        </a>
                    @else
                        <a href="{{ route('google-drive.authorize') }}" 
                           class="px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 text-white rounded-lg transition-all duration-200 font-medium inline-block">
                            <i class="fab fa-google-drive mr-2"></i>Connect Google Drive
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
