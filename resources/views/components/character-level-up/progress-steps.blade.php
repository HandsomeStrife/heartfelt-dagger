<!-- Progress Steps -->
<div class="mb-8">
    <div class="flex items-center space-x-2 sm:space-x-4 overflow-x-auto">
        <!-- Step 1: Tier Achievements -->
        <div class="flex items-center space-x-2 whitespace-nowrap" 
             :class="currentStep === 'tier_achievements' ? 'text-amber-400' : (['first_advancement', 'second_advancement', 'confirmation'].includes(currentStep) ? 'text-green-400' : 'text-slate-400')">
            <div class="w-8 h-8 rounded-full border-2 flex items-center justify-center text-sm font-bold"
                 :class="currentStep === 'tier_achievements' ? 'border-amber-400 bg-amber-400/20' : (['first_advancement', 'second_advancement', 'confirmation'].includes(currentStep) ? 'border-green-400 bg-green-400/20' : 'border-slate-400')">
                1
            </div>
            <span class="font-medium hidden sm:inline">Tier Achievements</span>
            <span class="font-medium sm:hidden">Tier</span>
        </div>
        
        <div class="w-6 sm:w-12 h-px bg-slate-600"></div>
        
        <!-- Step 2: First Advancement -->
        <div class="flex items-center space-x-2 whitespace-nowrap" 
             :class="currentStep === 'first_advancement' ? 'text-amber-400' : (['second_advancement', 'confirmation'].includes(currentStep) ? 'text-green-400' : 'text-slate-400')">
            <div class="w-8 h-8 rounded-full border-2 flex items-center justify-center text-sm font-bold"
                 :class="currentStep === 'first_advancement' ? 'border-amber-400 bg-amber-400/20' : (['second_advancement', 'confirmation'].includes(currentStep) ? 'border-green-400 bg-green-400/20' : 'border-slate-400')">
                2
            </div>
            <span class="font-medium hidden sm:inline">First Advancement</span>
            <span class="font-medium sm:hidden">First</span>
        </div>
        
        <div class="w-6 sm:w-12 h-px bg-slate-600"></div>
        
        <!-- Step 3: Second Advancement -->
        <div class="flex items-center space-x-2 whitespace-nowrap" 
             :class="currentStep === 'second_advancement' ? 'text-amber-400' : (currentStep === 'confirmation' ? 'text-green-400' : 'text-slate-400')">
            <div class="w-8 h-8 rounded-full border-2 flex items-center justify-center text-sm font-bold"
                 :class="currentStep === 'second_advancement' ? 'border-amber-400 bg-amber-400/20' : (currentStep === 'confirmation' ? 'border-green-400 bg-green-400/20' : 'border-slate-400')">
                3
            </div>
            <span class="font-medium hidden sm:inline">Second Advancement</span>
            <span class="font-medium sm:hidden">Second</span>
        </div>
        
        <div class="w-6 sm:w-12 h-px bg-slate-600"></div>
        
        <!-- Step 4: Confirm -->
        <div class="flex items-center space-x-2 whitespace-nowrap" 
             :class="currentStep === 'confirmation' ? 'text-amber-400' : 'text-slate-400'">
            <div class="w-8 h-8 rounded-full border-2 flex items-center justify-center text-sm font-bold"
                 :class="currentStep === 'confirmation' ? 'border-amber-400 bg-amber-400/20' : 'border-slate-400'">
                4
            </div>
            <span class="font-medium">Confirm</span>
        </div>
    </div>
</div>
