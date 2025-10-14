<?php

declare(strict_types=1);

namespace Domain\Character\Enums;

/**
 * Represents the different types of character advancements in DaggerHeart.
 *
 * Per DaggerHeart SRD, characters gain 2 advancements per level (2-10),
 * and each advancement has a specific type that determines what it provides.
 *
 * @see docs-dev/contents/Leveling Up.md
 */
enum AdvancementType: string
{
    /**
     * Increase one or more character traits (Agility, Strength, Finesse, etc.)
     * Usually provides +1 to selected traits.
     * Requires trait selection from player.
     */
    case TRAIT_BONUS = 'trait_bonus';

    /**
     * Increase maximum Hit Points.
     * Typically provides +1 HP per selection.
     */
    case HIT_POINT = 'hit_point';

    /**
     * Increase maximum Stress capacity.
     * Typically provides +1 Stress slot per selection.
     */
    case STRESS_SLOT = 'stress_slot';

    /**
     * Gain a new Experience or enhance an existing one.
     * Experiences provide +2 modifiers to related rolls.
     * Requires experience selection/creation from player.
     */
    case EXPERIENCE_BONUS = 'experience_bonus';

    /**
     * Learn a new domain card (ability/spell).
     * Must be from character's available domains and at/below current level.
     * Requires domain card selection from player.
     */
    case DOMAIN_CARD = 'domain_card';

    /**
     * Increase Evasion score.
     * Typically provides +1 Evasion per selection.
     */
    case EVASION = 'evasion';

    /**
     * Increase Proficiency bonus.
     * Per SRD: Costs 2 advancement slots (mutually exclusive with other advancements).
     */
    case PROFICIENCY = 'proficiency';

    /**
     * Upgrade to a more powerful version of character's subclass.
     * Available at specific tier thresholds.
     * Requires subclass selection from player.
     */
    case SUBCLASS_UPGRADE = 'subclass_upgrade';

    /**
     * Gain access to a second class's domains and abilities.
     * Available at Tier 3+ (level 5+).
     * Requires multiclass selection from player.
     */
    case MULTICLASS = 'multiclass';

    /**
     * Generic/unknown advancement type.
     * Used as fallback when type cannot be determined.
     */
    case GENERIC = 'generic';

    /**
     * Check if this advancement type requires player choices/selections.
     *
     * Some advancements (like hit point increases) are straightforward,
     * while others (like trait bonuses) require the player to choose
     * which specific option to take.
     *
     * @return bool True if this advancement requires additional player input
     */
    public function requiresChoices(): bool
    {
        return match ($this) {
            self::TRAIT_BONUS,
            self::EXPERIENCE_BONUS,
            self::DOMAIN_CARD,
            self::MULTICLASS,
            self::SUBCLASS_UPGRADE => true,
            self::HIT_POINT,
            self::STRESS_SLOT,
            self::EVASION,
            self::PROFICIENCY,
            self::GENERIC => false,
        };
    }

    /**
     * Check if this advancement type has special rules or restrictions.
     *
     * Some advancement types have special validation rules:
     * - Proficiency costs 2 slots
     * - Multiclass only available at Tier 3+
     * - Trait bonuses can be marked (preventing re-selection)
     *
     * @return bool True if special validation is required
     */
    public function hasSpecialRules(): bool
    {
        return match ($this) {
            self::PROFICIENCY,     // Costs 2 slots
            self::MULTICLASS,      // Tier 3+ requirement
            self::TRAIT_BONUS,     // Can be marked
            self::SUBCLASS_UPGRADE => true, // Tier-specific
            default => false,
        };
    }

    /**
     * Get the number of advancement slots this type consumes.
     *
     * Most advancements cost 1 slot, but some (like Proficiency) cost more.
     *
     * @return int Number of advancement slots consumed (typically 1 or 2)
     */
    public function getSlotCost(): int
    {
        return match ($this) {
            self::PROFICIENCY => 2,
            default => 1,
        };
    }

    /**
     * Check if multiple selections of this type are allowed per level.
     *
     * Some advancement types can be taken multiple times (like hit points),
     * while others are restricted.
     *
     * @return bool True if this advancement can be selected multiple times per level
     */
    public function allowsMultipleSelections(): bool
    {
        return match ($this) {
            self::HIT_POINT,
            self::STRESS_SLOT,
            self::TRAIT_BONUS,
            self::EVASION,
            self::DOMAIN_CARD => true,
            self::EXPERIENCE_BONUS,
            self::PROFICIENCY,
            self::MULTICLASS,
            self::SUBCLASS_UPGRADE,
            self::GENERIC => false,
        };
    }

    /**
     * Get a human-readable display name for this advancement type.
     *
     * @return string Display name for UI
     */
    public function getDisplayName(): string
    {
        return match ($this) {
            self::TRAIT_BONUS => 'Trait Bonus',
            self::HIT_POINT => 'Hit Point',
            self::STRESS_SLOT => 'Stress Slot',
            self::EXPERIENCE_BONUS => 'Experience Bonus',
            self::DOMAIN_CARD => 'Domain Card',
            self::EVASION => 'Evasion',
            self::PROFICIENCY => 'Proficiency',
            self::SUBCLASS_UPGRADE => 'Subclass Upgrade',
            self::MULTICLASS => 'Multiclass',
            self::GENERIC => 'Advancement',
        };
    }

    /**
     * Get a brief description of what this advancement provides.
     *
     * @return string Description for tooltips/help text
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::TRAIT_BONUS => 'Increase one or more character traits by +1',
            self::HIT_POINT => 'Increase maximum Hit Points by +1',
            self::STRESS_SLOT => 'Increase maximum Stress capacity by +1',
            self::EXPERIENCE_BONUS => 'Gain a new Experience with +2 modifier',
            self::DOMAIN_CARD => 'Learn a new domain card (ability or spell)',
            self::EVASION => 'Increase Evasion by +1 (harder to hit)',
            self::PROFICIENCY => 'Increase Proficiency by +1 (costs 2 slots)',
            self::SUBCLASS_UPGRADE => 'Upgrade to a more powerful subclass variant',
            self::MULTICLASS => 'Gain access to a second class\'s domains',
            self::GENERIC => 'Character advancement',
        };
    }

    /**
     * Parse an advancement type from a description string.
     *
     * This helps maintain backward compatibility with existing code
     * that uses string matching to determine advancement types.
     *
     * @param string $description The advancement description to parse
     * @return self The matched advancement type or GENERIC if no match
     */
    public static function fromDescription(string $description): self
    {
        $lower = strtolower($description);

        // Trait bonus patterns
        if (str_contains($lower, 'trait') && (str_contains($lower, 'increase') || str_contains($lower, 'bonus'))) {
            return self::TRAIT_BONUS;
        }

        // Hit point patterns
        if (str_contains($lower, 'hit point') || str_contains($lower, 'hp')) {
            return self::HIT_POINT;
        }

        // Stress patterns
        if (str_contains($lower, 'stress')) {
            return self::STRESS_SLOT;
        }

        // Experience patterns
        if (str_contains($lower, 'experience')) {
            return self::EXPERIENCE_BONUS;
        }

        // Evasion patterns
        if (str_contains($lower, 'evasion')) {
            return self::EVASION;
        }

        // Domain card patterns
        if (str_contains($lower, 'domain card') || str_contains($lower, 'learn a card')) {
            return self::DOMAIN_CARD;
        }

        // Multiclass patterns
        if (str_contains($lower, 'multiclass') || str_contains($lower, 'second class')) {
            return self::MULTICLASS;
        }

        // Proficiency patterns
        if (str_contains($lower, 'proficiency')) {
            return self::PROFICIENCY;
        }

        // Subclass patterns
        if (str_contains($lower, 'subclass') && str_contains($lower, 'upgrade')) {
            return self::SUBCLASS_UPGRADE;
        }

        return self::GENERIC;
    }

    /**
     * Get icon name for this advancement type (for UI rendering).
     *
     * @return string Icon identifier (can be mapped to SVG icons, Font Awesome, etc.)
     */
    public function getIcon(): string
    {
        return match ($this) {
            self::TRAIT_BONUS => 'arrow-trending-up',
            self::HIT_POINT => 'heart',
            self::STRESS_SLOT => 'shield-exclamation',
            self::EXPERIENCE_BONUS => 'academic-cap',
            self::DOMAIN_CARD => 'sparkles',
            self::EVASION => 'shield-check',
            self::PROFICIENCY => 'star',
            self::SUBCLASS_UPGRADE => 'arrow-up-circle',
            self::MULTICLASS => 'squares-plus',
            self::GENERIC => 'plus-circle',
        };
    }
}



