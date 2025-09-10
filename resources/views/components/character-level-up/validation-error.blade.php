<!-- Validation Error Notification (positioned near Continue button) -->
<template x-if="showValidationError && currentStep === 'tier_achievements'">
    <div class="mt-6 mb-4" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform translate-y-2">
        <div class="bg-gradient-to-r from-red-500/20 to-orange-500/20 border-2 border-red-400/50 rounded-lg p-4 shadow-lg">
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center animate-pulse">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01"></path>
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <p class="text-red-300 font-bold text-base">Cannot Continue</p>
                    <div class="text-red-200 text-sm mt-1">
                        <p>Missing required tier achievements:</p>
                        <ul class="mt-2 space-y-1 ml-4">
                            <li x-show="!advancementChoices?.tier_experience?.name" class="flex items-center">
                                <span class="w-2 h-2 bg-red-400 rounded-full mr-2"></span>
                                Create your new experience
                            </li>
                            <li x-show="!advancementChoices?.tier_domain_card" class="flex items-center">
                                <span class="w-2 h-2 bg-red-400 rounded-full mr-2"></span>
                                Select your domain card
                            </li>
                        </ul>
                    </div>
                    <div class="mt-3 flex items-center text-xs text-red-300">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                        </svg>
                        <span>Complete the sections above</span>
                    </div>
                    
                </div>
                <button @click="showValidationError = false" 
                        class="flex-shrink-0 text-red-300 hover:text-red-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>
