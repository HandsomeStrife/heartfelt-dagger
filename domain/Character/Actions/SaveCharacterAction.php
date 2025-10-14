<?php

declare(strict_types=1);

namespace Domain\Character\Actions;

use Domain\Character\Data\CharacterAdvancementData;
use Domain\Character\Data\CharacterBuilderData;
use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterAdvancement;
use Domain\Character\Models\CharacterDomainCard;
use Domain\Character\Models\CharacterEquipment;
use Domain\Character\Models\CharacterExperience;
use Domain\Character\Models\CharacterTrait;
use Domain\Character\Services\TierAchievementService;
use Domain\User\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SaveCharacterAction
{
    public function execute(CharacterBuilderData $builder_data, ?User $user = null, ?string $pronouns = null): Character
    {
        return DB::transaction(function () use ($builder_data, $user, $pronouns) {
            // Create the main character record
            $character = Character::create([
                'character_key' => Character::generateUniqueKey(),
                'user_id' => $user?->id,
                'name' => $builder_data->name,
                'pronouns' => $pronouns,
                'class' => $builder_data->selected_class,
                'subclass' => $builder_data->selected_subclass,
                'ancestry' => $builder_data->selected_ancestry,
                'community' => $builder_data->selected_community,
                'level' => 1,
                'proficiency' => 1, // DaggerHeart SRD: characters start with proficiency 1
                'profile_image_path' => $builder_data->profile_image_path,
                'character_data' => [
                    'background' => [
                        'answers' => $builder_data->background_answers,
                        'physicalDescription' => $builder_data->physical_description,
                        'personalityTraits' => $builder_data->personality_traits,
                        'personalHistory' => $builder_data->personal_history,
                        'motivations' => $builder_data->motivations,
                    ],
                    'connections' => $builder_data->connection_answers,
                    'manualStepCompletions' => $builder_data->manual_step_completions,
                    'clank_bonus_experience' => $builder_data->clank_bonus_experience,
                    'creation_date' => now()->toISOString(),
                    'builder_version' => '1.0',
                ],
                'is_public' => false,
            ]);

            // Save character traits
            foreach ($builder_data->assigned_traits as $trait_name => $trait_value) {
                CharacterTrait::create([
                    'character_id' => $character->id,
                    'trait_name' => $trait_name,
                    'trait_value' => $trait_value,
                ]);
            }

            // Save character equipment
            foreach ($builder_data->selected_equipment as $equipment) {
                CharacterEquipment::create([
                    'character_id' => $character->id,
                    'equipment_type' => $equipment['type'],
                    'equipment_key' => $equipment['key'],
                    'equipment_data' => $equipment['data'],
                    'is_equipped' => true,
                ]);
            }

            // Save character domain cards
            foreach ($builder_data->selected_domain_cards as $card) {
                CharacterDomainCard::create([
                    'character_id' => $character->id,
                    'domain' => $card['domain'],
                    'ability_key' => $card['ability_key'],
                    'ability_level' => $card['ability_level'] ?? 1,
                ]);
            }

            // Save character experiences
            foreach ($builder_data->experiences as $experience) {
                CharacterExperience::create([
                    'character_id' => $character->id,
                    'experience_name' => $experience['name'],
                    'experience_description' => $experience['description'] ?? '',
                    'modifier' => $experience['modifier'] ?? 2,
                ]);
            }

            return $character;
        });
    }

    public function updateCharacter(Character $character, CharacterBuilderData $builder_data, ?string $pronouns = null): Character
    {
        return DB::transaction(function () use ($character, $builder_data, $pronouns) {
            // Update the main character record
            $character->update([
                'name' => $builder_data->name,
                'pronouns' => $pronouns,
                'class' => $builder_data->selected_class,
                'subclass' => $builder_data->selected_subclass,
                'ancestry' => $builder_data->selected_ancestry,
                'community' => $builder_data->selected_community,
                'level' => $builder_data->starting_level,
                'profile_image_path' => $builder_data->profile_image_path,
                'character_data' => array_merge($character->character_data ?? [], [
                    'background' => [
                        'answers' => $builder_data->background_answers,
                        'physicalDescription' => $builder_data->physical_description,
                        'personalityTraits' => $builder_data->personality_traits,
                        'personalHistory' => $builder_data->personal_history,
                        'motivations' => $builder_data->motivations,
                    ],
                    'connections' => $builder_data->connection_answers,
                    'manualStepCompletions' => $builder_data->manual_step_completions,
                    'clank_bonus_experience' => $builder_data->clank_bonus_experience,
                    'last_updated' => now()->toISOString(),
                ]),
            ]);

            // Clear and recreate traits
            $character->traits()->delete();
            foreach ($builder_data->assigned_traits as $trait_name => $trait_value) {
                CharacterTrait::create([
                    'character_id' => $character->id,
                    'trait_name' => $trait_name,
                    'trait_value' => $trait_value,
                ]);
            }

            // Clear and recreate equipment
            $character->equipment()->delete();

            // Debug: Log equipment data being saved
            logger('=== SaveCharacterAction Equipment Debug ===');
            logger('Builder data selected_equipment count: '.count($builder_data->selected_equipment));
            foreach ($builder_data->selected_equipment as $equipment) {
                logger("  - Saving equipment: {$equipment['type']} - {$equipment['key']}");
                CharacterEquipment::create([
                    'character_id' => $character->id,
                    'equipment_type' => $equipment['type'],
                    'equipment_key' => $equipment['key'],
                    'equipment_data' => $equipment['data'],
                    'is_equipped' => true,
                ]);
            }

            // Clear and recreate domain cards
            $character->domainCards()->delete();
            foreach ($builder_data->selected_domain_cards as $card) {
                CharacterDomainCard::create([
                    'character_id' => $character->id,
                    'domain' => $card['domain'],
                    'ability_key' => $card['ability_key'],
                    'ability_level' => $card['ability_level'] ?? 1,
                ]);
            }

            // Clear and recreate experiences
            $character->experiences()->delete();
            foreach ($builder_data->experiences as $experience) {
                CharacterExperience::create([
                    'character_id' => $character->id,
                    'experience_name' => $experience['name'],
                    'experience_description' => $experience['description'] ?? '',
                    'modifier' => $experience['modifier'] ?? 2,
                ]);
            }

            return $character->fresh();
        });
    }

    /**
     * Create a character at a higher level with all advancements applied
     *
     * @param CharacterBuilderData $builder_data Character builder data with advancement selections
     * @param User|null $user User to associate with character (optional)
     * @param string|null $pronouns Character pronouns
     * @return Character The created character at the specified level
     * @throws \Exception If character creation fails
     */
    public function createCharacterWithAdvancements(
        CharacterBuilderData $builder_data,
        ?User $user = null,
        ?string $pronouns = null
    ): Character {
        // Standard logging context for this operation
        $context = [
            'user_id' => $user?->id,
            'session_id' => session()->getId(),
            'starting_level' => $builder_data->starting_level,
            'class' => $builder_data->selected_class,
            'ancestry' => $builder_data->selected_ancestry,
        ];

        try {
            // PRE-TRANSACTION VALIDATIONS: Validate all client selections before starting transaction
            Log::info('[CharacterCreation] Starting pre-transaction validation', $context);
            
            // Validate all advancement selections against game rules
            $this->validateAdvancementSelections($builder_data);
            
            // Re-validate all client-provided data (don't trust client state)
            $this->revalidateClientSelections($builder_data);
            
            Log::info('[CharacterCreation] Pre-transaction validation complete', $context);

            // PRE-TRANSACTION: Load all domain card data to avoid file I/O during transaction
            $domainCardDataCache = $this->preloadDomainCardData($builder_data);

            return DB::transaction(function () use ($builder_data, $user, $pronouns, $domainCardDataCache, $context) {
                Log::info('[CharacterCreation] Starting transaction', $context);

                // Step 1: Create base character at level 1
                $character = $this->createBaseCharacter($builder_data, $user, $pronouns);

                $context['character_id'] = $character->id;
                $context['character_key'] = $character->character_key;
                Log::info('[CharacterCreation] Base character created', $context);

                // If starting at level 1, no further advancement needed
                if ($builder_data->starting_level === 1) {
                    return $character;
                }

                // Step 2: Apply tier achievements for levels 2, 5, 8 (if applicable)
                $this->applyCreationTierAchievements($character, $builder_data);

                // Step 3: Apply all advancement selections (2 per level for levels 2+)
                $this->applyCreationAdvancements($character, $builder_data);

                // Step 4: Apply domain cards for all levels (1 per level) using pre-loaded data
                $this->applyCreationDomainCards($character, $builder_data, $domainCardDataCache);

                // Step 5: Verify final level and set proficiency
                // Note: Character level has been updated incrementally during advancement application
                // Here we verify it matches expectations and set the final proficiency
                $tier_achievement_service = app(TierAchievementService::class);
                $final_proficiency = $tier_achievement_service->getProficiencyForLevel($builder_data->starting_level);

                // Verify character is at correct level (should have been updated incrementally)
                $character->refresh();
                if ($character->level !== $builder_data->starting_level) {
                    Log::error('Character level mismatch after incremental updates', [
                        'character_id' => $character->id,
                        'current_level' => $character->level,
                        'expected_level' => $builder_data->starting_level,
                    ]);
                    throw new \RuntimeException(
                        "Character level error: expected {$builder_data->starting_level}, got {$character->level}"
                    );
                }

                // Verify proficiency calculation matches tier achievements applied
                $expected_proficiency = 1 + count(array_filter([2, 5, 8], fn($l) => $l <= $builder_data->starting_level));
                if ($final_proficiency !== $expected_proficiency) {
                    Log::error('Proficiency calculation mismatch', [
                        'character_id' => $character->id,
                        'calculated' => $final_proficiency,
                        'expected' => $expected_proficiency,
                        'starting_level' => $builder_data->starting_level,
                    ]);
                    throw new \RuntimeException(
                        "Proficiency calculation error: expected {$expected_proficiency}, got {$final_proficiency}"
                    );
                }

                // Update proficiency (level is already correct from incremental updates)
                $character->update([
                    'proficiency' => $final_proficiency,
                ]);

                $context['final_level'] = $builder_data->starting_level;
                $context['final_proficiency'] = $final_proficiency;
                $context['tier_achievements_count'] = count(array_filter([2, 5, 8], fn($l) => $l <= $builder_data->starting_level));
                $context['total_advancements'] = ($builder_data->starting_level - 1) * 2;
                $context['total_domain_cards'] = $builder_data->starting_level;
                Log::info('[CharacterCreation] Transaction complete', $context);

                return $character->fresh();
            });
        } catch (\Exception $e) {
            $context['error'] = $e->getMessage();
            $context['exception_class'] = get_class($e);
            $context['trace'] = $e->getTraceAsString();
            Log::error('[CharacterCreation] Transaction failed', $context);

            // Re-throw with user-friendly message
            throw new \Exception(
                'Failed to create character. Please verify all required selections are complete and try again. ' .
                'If the problem persists, please contact support.',
                0,
                $e
            );
        }
    }

    /**
     * Create the base character record at level 1
     *
     * @param CharacterBuilderData $builder_data
     * @param User|null $user
     * @param string|null $pronouns
     * @return Character
     */
    private function createBaseCharacter(
        CharacterBuilderData $builder_data,
        ?User $user,
        ?string $pronouns
    ): Character {
        // Create the main character record at level 1
        $character = Character::create([
            'character_key' => Character::generateUniqueKey(),
            'user_id' => $user?->id,
            'name' => $builder_data->name,
            'pronouns' => $pronouns,
            'class' => $builder_data->selected_class,
            'subclass' => $builder_data->selected_subclass,
            'ancestry' => $builder_data->selected_ancestry,
            'community' => $builder_data->selected_community,
            'level' => 1,
            'proficiency' => 1,
            'profile_image_path' => $builder_data->profile_image_path,
            'character_data' => [
                'background' => [
                    'answers' => $builder_data->background_answers,
                    'physicalDescription' => $builder_data->physical_description,
                    'personalityTraits' => $builder_data->personality_traits,
                    'personalHistory' => $builder_data->personal_history,
                    'motivations' => $builder_data->motivations,
                ],
                'connections' => $builder_data->connection_answers,
                'manualStepCompletions' => $builder_data->manual_step_completions,
                'clank_bonus_experience' => $builder_data->clank_bonus_experience,
                'starting_level' => $builder_data->starting_level,
                'creation_date' => now()->toISOString(),
                'builder_version' => '1.1',
            ],
            'is_public' => false,
        ]);

        // Save character traits
        foreach ($builder_data->assigned_traits as $trait_name => $trait_value) {
            CharacterTrait::create([
                'character_id' => $character->id,
                'trait_name' => $trait_name,
                'trait_value' => $trait_value,
            ]);
        }

        // Save character equipment
        foreach ($builder_data->selected_equipment as $equipment) {
            CharacterEquipment::create([
                'character_id' => $character->id,
                'equipment_type' => $equipment['type'],
                'equipment_key' => $equipment['key'],
                'equipment_data' => $equipment['data'] ?? [],
                'is_equipped' => true,
            ]);
        }

        // Save starting experiences (2 required at creation)
        foreach ($builder_data->experiences as $experience) {
            CharacterExperience::create([
                'character_id' => $character->id,
                'experience_name' => $experience['name'],
                'experience_description' => $experience['description'] ?? '',
                'modifier' => $experience['modifier'] ?? 2,
            ]);
        }

        // Save level 1 domain card
        if (isset($builder_data->creation_domain_cards[1])) {
            $card_key = $builder_data->creation_domain_cards[1];
            $card_data = $this->loadDomainCardData($card_key);
            
            if ($card_data) {
                CharacterDomainCard::create([
                    'character_id' => $character->id,
                    'domain' => $card_data['domain'],
                    'ability_key' => $card_key,
                    'ability_level' => $card_data['level'] ?? 1,
                ]);
            }
        }

        return $character;
    }

    /**
     * Validate all advancement selections before applying them
     *
     * @param CharacterBuilderData $builder_data
     * @return void
     * @throws \RuntimeException If validation fails
     */
    private function validateAdvancementSelections(CharacterBuilderData $builder_data): void
    {
        $validTypes = ['trait_bonus', 'hit_point', 'stress_slot', 'stress', 'experience_bonus', 
                       'domain_card', 'evasion', 'subclass_upgrade', 'proficiency', 'multiclass'];

        for ($level = 2; $level <= $builder_data->starting_level; $level++) {
            $level_advancements = $builder_data->creation_advancements[$level] ?? [];

            // Validate advancement count
            if (count($level_advancements) !== 2) {
                throw new \RuntimeException(
                    "Invalid advancement count for level {$level}. " .
                    "Expected 2, found " . count($level_advancements)
                );
            }

            foreach ($level_advancements as $index => $advancement) {
                // Validate type exists
                if (! isset($advancement['type'])) {
                    throw new \RuntimeException(
                        "Advancement at level {$level}, index {$index} is missing 'type' field"
                    );
                }

                $type = $advancement['type'];

                // Validate type is recognized
                if (! in_array($type, $validTypes)) {
                    throw new \RuntimeException(
                        "Invalid advancement type '{$type}' at level {$level}, index {$index}"
                    );
                }

                // Validate type-specific requirements
                if ($type === 'trait_bonus') {
                    if (! isset($advancement['traits']) || ! is_array($advancement['traits'])) {
                        throw new \RuntimeException(
                            "Trait bonus advancement at level {$level} is missing 'traits' array"
                        );
                    }
                    if (count($advancement['traits']) !== 2) {
                        throw new \RuntimeException(
                            "Trait bonus advancement at level {$level} must select exactly 2 traits"
                        );
                    }
                }

                if ($type === 'multiclass') {
                    if (! isset($advancement['class']) || empty($advancement['class'])) {
                        throw new \RuntimeException(
                            "Multiclass advancement at level {$level} is missing 'class' field"
                        );
                    }
                }
            }
        }

        Log::debug('All advancement selections validated successfully', [
            'starting_level' => $builder_data->starting_level,
            'levels_validated' => range(2, $builder_data->starting_level),
        ]);
    }

    /**
     * Apply tier achievements for levels 2, 5, 8
     *
     * @param Character $character
     * @param CharacterBuilderData $builder_data
     * @return void
     */
    private function applyCreationTierAchievements(
        Character $character,
        CharacterBuilderData $builder_data
    ): void {
        $tier_achievement_service = app(TierAchievementService::class);

        foreach ([2, 5, 8] as $level) {
            if ($level > $builder_data->starting_level) {
                break;
            }

            $experience_data = $builder_data->creation_tier_experiences[$level] ?? null;
            if (! $experience_data) {
                Log::error('Missing required tier achievement experience', [
                    'character_id' => $character->id,
                    'level' => $level,
                    'starting_level' => $builder_data->starting_level,
                ]);
                throw new \RuntimeException(
                    "Missing required tier achievement experience for level {$level}. " .
                    "Character creation cannot continue with incomplete tier achievements."
                );
            }

            // Apply tier achievements (creates experience, updates proficiency, clears marks)
            $tier_achievement_service->applyTierAchievements(
                $character,
                $level,
                $experience_data
            );

            Log::debug('Tier achievement applied', [
                'character_id' => $character->id,
                'level' => $level,
                'experience_name' => $experience_data['name'],
            ]);
        }
    }

    /**
     * Apply all advancement selections
     *
     * @param Character $character
     * @param CharacterBuilderData $builder_data
     * @return void
     */
    private function applyCreationAdvancements(
        Character $character,
        CharacterBuilderData $builder_data
    ): void {
        $apply_advancement_action = new ApplyAdvancementAction();

        for ($level = 2; $level <= $builder_data->starting_level; $level++) {
            $level_advancements = $builder_data->creation_advancements[$level] ?? [];

            if (count($level_advancements) !== 2) {
                Log::error('Invalid advancement count for level', [
                    'character_id' => $character->id,
                    'level' => $level,
                    'expected' => 2,
                    'actual' => count($level_advancements),
                ]);
                throw new \RuntimeException(
                    "Invalid advancement count for level {$level}. Expected exactly 2 advancements, " .
                    "but found " . count($level_advancements) . ". SRD requires 2 advancements per level."
                );
            }

            foreach ($level_advancements as $advancement_number => $advancement) {
                // Create advancement data from builder selection
                $advancement_data = CharacterAdvancementData::fromCreationSelection(
                    $level,
                    $advancement_number + 1, // Convert 0-based to 1-based
                    $advancement
                );

                // Apply the advancement
                $apply_advancement_action->execute($character, $advancement_data, $level);

                Log::debug('Advancement applied', [
                    'character_id' => $character->id,
                    'level' => $level,
                    'advancement_number' => $advancement_number + 1,
                    'type' => $advancement['type'],
                ]);
            }

            // Update character level incrementally after completing all advancements for this level
            // This ensures $character->level is accurate for subsequent operations
            $character->update(['level' => $level]);
            $character->refresh(); // Reload from database to ensure fresh state
            
            Log::debug('Character level updated incrementally', [
                'character_id' => $character->id,
                'new_level' => $level,
            ]);
        }
    }

    /**
     * Pre-load all domain card data before transaction to avoid file I/O during transaction
     *
     * @param CharacterBuilderData $builder_data
     * @return array<int, array> Map of level => card data
     * @throws \RuntimeException If validation fails
     */
    private function preloadDomainCardData(CharacterBuilderData $builder_data): array
    {
        $cache = [];
        $game_data_loader = app(\Domain\Character\Services\GameDataLoader::class);
        
        // Load class data to get accessible domains
        $class_data = $game_data_loader->loadClassData($builder_data->selected_class);
        $class_domains = $class_data['domains'] ?? [];
        
        if (empty($class_domains)) {
            throw new \RuntimeException(
                "Unable to determine accessible domains for class: {$builder_data->selected_class}"
            );
        }

        // Track selected cards to detect duplicates
        $selected_cards = [];

        // Load domain card data for levels 2+ (level 1 is handled in createBaseCharacter)
        for ($level = 2; $level <= $builder_data->starting_level; $level++) {
            $card_key = $builder_data->creation_domain_cards[$level] ?? null;

            if (! $card_key) {
                Log::error('Missing required domain card for level during preload', [
                    'level' => $level,
                    'starting_level' => $builder_data->starting_level,
                ]);
                throw new \RuntimeException(
                    "Missing required domain card for level {$level}. " .
                    "SRD requires 1 domain card per level for all characters."
                );
            }

            // Check for duplicates
            if (in_array($card_key, $selected_cards)) {
                throw new \RuntimeException(
                    "Domain card '{$card_key}' selected multiple times. " .
                    "Each domain card can only be selected once."
                );
            }
            $selected_cards[] = $card_key;

            // Load card data from JSON
            $card_data = $this->loadDomainCardData($card_key);

            if (! $card_data) {
                Log::error('Domain card data not found during preload', [
                    'level' => $level,
                    'card_key' => $card_key,
                ]);
                throw new \RuntimeException(
                    "Domain card '{$card_key}' not found in abilities.json for level {$level}. " .
                    "Unable to create character with invalid domain card selection."
                );
            }

            // Validate card is from an accessible domain
            $card_domain = $card_data['domain'] ?? null;
            if (! $card_domain || ! in_array($card_domain, $class_domains)) {
                throw new \RuntimeException(
                    "Domain card '{$card_key}' is from domain '{$card_domain}', " .
                    "which is not accessible by class '{$builder_data->selected_class}'. " .
                    "Accessible domains: " . implode(', ', $class_domains)
                );
            }

            // Validate card level is appropriate for character level
            $card_level = $card_data['level'] ?? 1;
            if ($card_level > $level) {
                throw new \RuntimeException(
                    "Domain card '{$card_key}' is level {$card_level}, " .
                    "but can only be selected at character level {$level}. " .
                    "Characters can only select domain cards of their current level or lower."
                );
            }

            $cache[$level] = $card_data;
        }

        Log::debug('Domain card data pre-loaded and validated', [
            'levels' => array_keys($cache),
            'starting_level' => $builder_data->starting_level,
            'accessible_domains' => $class_domains,
        ]);

        return $cache;
    }

    /**
     * Apply all domain card selections using pre-loaded data
     *
     * @param Character $character
     * @param CharacterBuilderData $builder_data
     * @param array<int, array> $domainCardDataCache Pre-loaded domain card data
     * @return void
     */
    private function applyCreationDomainCards(
        Character $character,
        CharacterBuilderData $builder_data,
        array $domainCardDataCache
    ): void {
        // Note: Level 1 domain cards are already created in createBaseCharacter()
        // We only need to create cards for levels 2+

        for ($level = 2; $level <= $builder_data->starting_level; $level++) {
            $card_key = $builder_data->creation_domain_cards[$level] ?? null;
            $card_data = $domainCardDataCache[$level] ?? null;

            if (! $card_key || ! $card_data) {
                // This should never happen since preload validates everything
                Log::error('Missing domain card data in transaction', [
                    'character_id' => $character->id,
                    'level' => $level,
                    'card_key' => $card_key,
                ]);
                throw new \RuntimeException(
                    "Domain card data missing for level {$level} during character creation"
                );
            }

            CharacterDomainCard::create([
                'character_id' => $character->id,
                'domain' => $card_data['domain'],
                'ability_key' => $card_key,
                'ability_level' => $card_data['level'] ?? 1,
            ]);
        }
    }

    /**
     * Re-validate all client selections against server-side game rules
     * Don't trust client-side state - validate everything
     *
     * @param CharacterBuilderData $builder_data
     * @return void
     * @throws \RuntimeException If any validation fails
     */
    private function revalidateClientSelections(CharacterBuilderData $builder_data): void
    {
        // Validate level bounds
        if ($builder_data->starting_level < 1 || $builder_data->starting_level > 10) {
            throw new \RuntimeException('Invalid starting level: must be between 1 and 10');
        }

        // Validate domain cards for ALL levels (including level 1)
        for ($level = 1; $level <= $builder_data->starting_level; $level++) {
            if (! isset($builder_data->creation_domain_cards[$level]) || empty($builder_data->creation_domain_cards[$level])) {
                throw new \RuntimeException("Missing required domain card for level {$level}");
            }
        }

        // For level 1 characters, no advancement/tier validation needed
        if ($builder_data->starting_level === 1) {
            // Still need to run cross-level validation (duplicate domain cards check)
            $cross_level_errors = $builder_data->validateCrossLevelSelections();
            if (! empty($cross_level_errors)) {
                throw new \RuntimeException(
                    'Cross-level validation failed: ' . implode('; ', $cross_level_errors)
                );
            }
            return;
        }

        // Validate tier achievements for levels 2, 5, 8
        foreach ([2, 5, 8] as $tier_level) {
            if ($tier_level <= $builder_data->starting_level) {
                if (! isset($builder_data->creation_tier_experiences[$tier_level])) {
                    throw new \RuntimeException("Missing required tier achievement experience for level {$tier_level}");
                }

                $experience = $builder_data->creation_tier_experiences[$tier_level];
                if (empty($experience['name']) || strlen($experience['name']) > 255) {
                    throw new \RuntimeException("Invalid experience name at level {$tier_level}");
                }
            }
        }

        // Validate advancement count for each level
        for ($level = 2; $level <= $builder_data->starting_level; $level++) {
            $advancements = $builder_data->creation_advancements[$level] ?? [];
            if (count($advancements) !== 2) {
                throw new \RuntimeException(
                    "Invalid advancement count for level {$level}: expected 2, got " . count($advancements)
                );
            }
        }

        // Use CharacterBuilderData's cross-level validation
        $cross_level_errors = $builder_data->validateCrossLevelSelections();
        if (! empty($cross_level_errors)) {
            Log::error('[CharacterCreation] Cross-level validation failed', [
                'errors' => $cross_level_errors,
                'starting_level' => $builder_data->starting_level,
                'domain_cards' => $builder_data->creation_domain_cards,
                'max_cards' => $builder_data->getMaxDomainCards(),
            ]);
            throw new \RuntimeException(
                'Cross-level validation failed: ' . implode('; ', $cross_level_errors)
            );
        }
    }

    /**
     * Load domain card data from JSON
     *
     * @param string $card_key
     * @return array|null
     */
    private function loadDomainCardData(string $card_key): ?array
    {
        $abilities_path = resource_path('json/abilities.json');

        if (! file_exists($abilities_path)) {
            return null;
        }

        $abilities = json_decode(file_get_contents($abilities_path), true);

        return $abilities[$card_key] ?? null;
    }
}
