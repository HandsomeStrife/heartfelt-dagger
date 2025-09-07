<x-layout>
    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 flex items-center justify-center">
        <div class="max-w-md w-full mx-4">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-outfit font-bold text-white mb-2">Viewer Access</h1>
                <p class="text-slate-400">Enter the viewer password to watch this room</p>
            </div>

            <!-- Room Info Card -->
            <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6 mb-6">
                <div class="text-center">
                    <h2 class="text-xl font-outfit font-bold text-white mb-2">{{ $room->name }}</h2>
                    <p class="text-slate-300 text-sm">{{ $room->description }}</p>
                </div>
            </div>

            <!-- Password Form -->
            <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6">
                <form action="{{ route('rooms.viewer.password', $room->viewer_code) }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-300 mb-1">Viewer Password</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required 
                            class="w-full px-3 py-2 mt-1 bg-slate-800 border border-slate-600 text-white placeholder-slate-400 focus:border-blue-500 focus:ring-blue-500 rounded-lg"
                            placeholder="Enter viewer password"
                        />
                        @error('password')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="w-full px-4 py-3 bg-gradient-to-r from-blue-500 to-cyan-500 hover:from-blue-400 hover:to-cyan-400 text-white font-semibold rounded-lg transition-all duration-300">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Access Viewer
                    </button>
                </form>
            </div>

            <!-- Help Text -->
            <div class="text-center mt-6">
                <p class="text-slate-500 text-sm">
                    This password was set by the room creator to control viewer access.
                </p>
            </div>
        </div>
    </div>
</x-layout>
