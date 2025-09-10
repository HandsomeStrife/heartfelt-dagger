<div class="p-6" 
     x-data="characterLevelUpComponent($wire, { availableSlots: {{ count($available_slots) }} })">
    
    <!-- Progress Steps -->
    <x-character-level-up.progress-steps />

    <!-- Content Area -->
    <div class="min-h-[400px]">
        <!-- Tier Achievements Step -->
        <x-character-level-up.tier-achievements-step 
            :character="$character" 
            :advancementChoices="$advancement_choices" />

        <!-- First Advancement Selection Step -->
        <x-character-level-up.first-advancement-step 
            :tierOptions="$tier_options" 
            :character="$character" />

        <!-- Second Advancement Selection Step -->
        <x-character-level-up.second-advancement-step 
            :tierOptions="$tier_options" 
            :character="$character" />

        <!-- Confirmation Step -->
        <x-character-level-up.confirmation-step 
            :tierOptions="$tier_options" />
    </div>

    <!-- Validation Error Notification -->
    <x-character-level-up.validation-error />

    <!-- Footer Actions -->
    <x-character-level-up.footer-actions 
        :character="$character" 
        :characterKey="$character_key" />
</div>
