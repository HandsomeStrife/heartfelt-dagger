<div class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- HeartfeltDagger Logo/Title -->
        <div class="text-center mb-8">
            <h1 class="font-federant text-4xl text-white tracking-wide mb-2">HeartfeltDagger</h1>
            <p class="font-roboto text-white/70 text-sm">Reset Your Password</p>
        </div>

        <!-- Reset Password Form -->
        <div class="bg-gradient-to-br from-slate-800 to-slate-900 border border-amber-500/30 rounded-lg p-8 shadow-2xl">
            <form wire:submit="resetPassword" class="space-y-6">
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-bold text-white font-outfit mb-2">Choose New Password</h2>
                    <p class="text-white/70 text-sm font-roboto">
                        Enter your new password below.
                    </p>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-white font-roboto mb-2">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        wire:model="form.email"
                        class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white placeholder-slate-400 focus:border-amber-400 focus:ring-1 focus:ring-amber-400 transition-colors font-roboto"
                        placeholder="Enter your email"
                        readonly
                    >
                    @error('form.email') 
                        <p class="mt-1 text-sm text-red-400 font-roboto">{{ $message }}</p> 
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-white font-roboto mb-2">New Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        wire:model="form.password"
                        class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white placeholder-slate-400 focus:border-amber-400 focus:ring-1 focus:ring-amber-400 transition-colors font-roboto"
                        placeholder="Enter your new password"
                        required
                    >
                    @error('form.password') 
                        <p class="mt-1 text-sm text-red-400 font-roboto">{{ $message }}</p> 
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-white font-roboto mb-2">Confirm New Password</label>
                    <input 
                        type="password" 
                        id="password_confirmation" 
                        wire:model="form.password_confirmation"
                        class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white placeholder-slate-400 focus:border-amber-400 focus:ring-1 focus:ring-amber-400 transition-colors font-roboto"
                        placeholder="Confirm your new password"
                        required
                    >
                    @error('form.password_confirmation') 
                        <p class="mt-1 text-sm text-red-400 font-roboto">{{ $message }}</p> 
                    @enderror
                </div>

                @error('form.token') 
                    <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-4">
                        <p class="text-red-400 text-sm font-roboto">{{ $message }}</p>
                    </div>
                @enderror

                <button 
                    type="submit"
                    class="w-full bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-400 hover:to-yellow-400 text-black font-bold py-3 px-6 rounded-lg text-lg transition-all duration-300 shadow-lg hover:shadow-amber-500/50 transform hover:scale-105 font-roboto"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove>
                        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m0 0a2 2 0 012 2m-2-2a2 2 0 00-2 2m2-2V5a2 2 0 00-2-2m0 0H9a2 2 0 00-2 2v0"></path>
                        </svg>
                        Reset Password
                    </span>
                    <span wire:loading>
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-black inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Resetting...
                    </span>
                </button>
            </form>

            <!-- Back to Login Link -->
            <div class="mt-6 text-center">
                <p class="text-white/70 text-sm font-roboto">
                    Remember your password? 
                    <a href="/login" class="text-amber-400 hover:text-amber-300 font-medium transition-colors">
                        Back to login
                    </a>
                </p>
            </div>
        </div>

        <!-- Decorative Elements -->
        <div class="mt-8 text-center">
            <div class="flex justify-center space-x-2 text-purple-400/50">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2L13.5 8.5L20 10L13.5 11.5L12 18L10.5 11.5L4 10L10.5 8.5L12 2Z"/>
                </svg>
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2L13.5 8.5L20 10L13.5 11.5L12 18L10.5 11.5L4 10L10.5 8.5L12 2Z"/>
                </svg>
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2L13.5 8.5L20 10L13.5 11.5L12 18L10.5 11.5L4 10L10.5 8.5L12 2Z"/>
                </svg>
            </div>
        </div>
    </div>
</div>
