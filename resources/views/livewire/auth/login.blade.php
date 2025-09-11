<div class="min-h-screen flex items-center justify-center p-4" 
     x-data="{ 
         init() { 
             this.loadCharacterKeys(); 
         },
         loadCharacterKeys() {
             try { 
                 const keys = localStorage.getItem('daggerheart_characters'); 
                 const character_keys = keys ? JSON.parse(keys) : []; 
                 $wire.set('form.character_keys', character_keys); 
             } catch (error) { 
                 console.error('Error reading character keys from localStorage:', error); 
             } 
         },
         clearCharacterKeys() {
             localStorage.removeItem('daggerheart_characters');
             console.log('Character keys cleared from localStorage after login');
         }
     }" 
     x-on:auth-success.window="clearCharacterKeys()">
    <div class="w-full max-w-md">
        <!-- HeartfeltDagger Logo/Title -->
        <div class="text-center mb-8">
            <h1 class="font-federant text-4xl text-white tracking-wide mb-2">HeartfeltDagger</h1>
            <p class="font-roboto text-white/70 text-sm">Enter the Realm</p>
        </div>

        <!-- Login Form -->
        <div class="bg-gradient-to-br from-slate-800 to-slate-900 border border-amber-500/30 rounded-lg p-8 shadow-2xl">
            <form wire:submit="login" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium text-white font-roboto mb-2">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        wire:model="form.email"
                        class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white placeholder-slate-400 focus:border-amber-400 focus:ring-1 focus:ring-amber-400 transition-colors font-roboto"
                        placeholder="Enter your email"
                        autocomplete="email"
                        required
                    >
                    @error('form.email') 
                        <p class="mt-1 text-sm text-red-400 font-roboto">{{ $message }}</p> 
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-white font-roboto mb-2">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        wire:model="form.password"
                        class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white placeholder-slate-400 focus:border-amber-400 focus:ring-1 focus:ring-amber-400 transition-colors font-roboto"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                    >
                    @error('form.password') 
                        <p class="mt-1 text-sm text-red-400 font-roboto">{{ $message }}</p> 
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input 
                            type="checkbox" 
                            id="remember" 
                            wire:model="form.remember"
                            class="h-4 w-4 text-amber-500 focus:ring-amber-400 border-slate-600 rounded bg-slate-700"
                        >
                        <label for="remember" class="ml-2 block text-sm text-white/80 font-roboto">
                            Remember me
                        </label>
                    </div>
                    <div>
                        <a href="/forgot-password" class="text-sm text-amber-400 hover:text-amber-300 font-roboto transition-colors">
                            Forgot your password?
                        </a>
                    </div>
                </div>

                <button 
                    type="submit"
                    data-testid="login-submit-button"
                    class="w-full bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-400 hover:to-yellow-400 text-black font-bold py-3 px-6 rounded-lg text-lg transition-all duration-300 shadow-lg hover:shadow-amber-500/50 transform hover:scale-105 font-roboto"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove>
                        <svg class="w-5 h-5 inline-block mr-2" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2L13.5 8.5L20 10L13.5 11.5L12 18L10.5 11.5L4 10L10.5 8.5L12 2Z"/>
                        </svg>
                        Enter the Realm
                    </span>
                    <span wire:loading>
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-black inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Entering...
                    </span>
                </button>
            </form>

            <!-- Register Link -->
            <div class="mt-6 text-center">
                <p class="text-white/70 text-sm font-roboto">
                    New to the adventure? 
                    <a href="/register" class="text-amber-400 hover:text-amber-300 font-medium transition-colors">
                        Create your legend
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