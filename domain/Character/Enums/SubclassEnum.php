<?php

declare(strict_types=1);

namespace Domain\Character\Enums;

enum SubclassEnum: string
{
    case BEASTBOUND = 'beastbound';
    case CALL_OF_THE_BRAVE = 'call of the brave';
    case CALL_OF_THE_SLAYER = 'call of the slayer';
    case DIVINE_WIELDER = 'divine wielder';
    case ELEMENTAL_ORIGIN = 'elemental origin';
    case EXECUTIONERS_GUILD = 'executioners guild';
    case HEDGE = 'hedge';
    case JUGGERNAUT = 'juggernaut';
    case MARTIAL_ARTIST = 'martial artist';
    case MOON = 'moon';
    case NIGHTWALKER = 'nightwalker';
    case PACT_OF_THE_ENDLESS = 'pact of the endless';
    case PACT_OF_THE_WRATHFUL = 'pact of the wrathful';
    case POISONERS_GUILD = 'poisoners guild';
    case PRIMAL_ORIGIN = 'primal origin';
    case SCHOOL_OF_KNOWLEDGE = 'school of knowledge';
    case SCHOOL_OF_WAR = 'school of war';
    case STALWART = 'stalwart';
    case SYNDICATE = 'syndicate';
    case TROUBADOUR = 'troubadour';
    case VENGEANCE = 'vengeance';
    case WARDEN_OF_RENEWAL = 'warden of renewal';
    case WARDEN_OF_THE_ELEMENTS = 'warden of the elements';
    case WAYFINDER = 'wayfinder';
    case WINGED_SENTINEL = 'winged sentinel';
    case WORDSMITH = 'wordsmith';

    /**
     * Get subclasses that have numerical effects (HP, Evasion, Stress, Thresholds)
     */
    public function hasNumericalEffects(): bool
    {
        return match ($this) {
            self::SCHOOL_OF_KNOWLEDGE => true,  // +1 domain card
            self::SCHOOL_OF_WAR => true,        // +1 HP
            self::NIGHTWALKER => true,          // +1 Evasion
            self::STALWART => true,             // +1/+2/+3 damage thresholds
            self::VENGEANCE => true,            // +1 Stress
            self::WINGED_SENTINEL => true,      // +4 Severe threshold
            default => false
        };
    }

    /**
     * Get the numerical effects this subclass provides
     */
    public function getNumericalEffects(): array
    {
        return match ($this) {
            self::SCHOOL_OF_KNOWLEDGE => [
                ['type' => 'domain_card_bonus', 'value' => 1, 'timing' => 'permanent']
            ],
            self::SCHOOL_OF_WAR => [
                ['type' => 'hit_point_bonus', 'value' => 1, 'timing' => 'permanent']
            ],
            self::NIGHTWALKER => [
                ['type' => 'evasion_bonus', 'value' => 1, 'timing' => 'permanent']
            ],
            self::STALWART => [
                ['type' => 'damage_threshold_bonus', 'value' => 1, 'timing' => 'permanent'], // Foundation
                ['type' => 'damage_threshold_bonus', 'value' => 2, 'timing' => 'permanent'], // Specialization
                ['type' => 'damage_threshold_bonus', 'value' => 3, 'timing' => 'permanent']  // Mastery
            ],
            self::VENGEANCE => [
                ['type' => 'stress_bonus', 'value' => 1, 'timing' => 'permanent']
            ],
            self::WINGED_SENTINEL => [
                ['type' => 'severe_threshold_bonus', 'value' => 4, 'timing' => 'permanent']
            ],
            default => []
        };
    }

    /**
     * Check if this subclass is from playtest content
     */
    public function isPlaytest(): bool
    {
        return match ($this) {
            self::EXECUTIONERS_GUILD,
            self::HEDGE,
            self::JUGGERNAUT,
            self::MARTIAL_ARTIST,
            self::MOON,
            self::PACT_OF_THE_ENDLESS,
            self::PACT_OF_THE_WRATHFUL,
            self::POISONERS_GUILD => true,
            default => false
        };
    }

    /**
     * Get the playtest version for playtest subclasses
     */
    public function getPlaytestVersion(): ?string
    {
        return $this->isPlaytest() ? 'v1.5' : null;
    }

    /**
     * Get all subclasses for a specific class
     */
    public static function getForClass(ClassEnum $class): array
    {
        return match ($class) {
            ClassEnum::ASSASSIN => [
                self::EXECUTIONERS_GUILD,
                self::POISONERS_GUILD
            ],
            ClassEnum::BARD => [
                self::TROUBADOUR,
                self::WORDSMITH
            ],
            ClassEnum::BRAWLER => [
                self::JUGGERNAUT,
                self::MARTIAL_ARTIST
            ],
            ClassEnum::DRUID => [
                self::WARDEN_OF_RENEWAL,
                self::WARDEN_OF_THE_ELEMENTS
            ],
            ClassEnum::GUARDIAN => [
                self::STALWART,
                self::CALL_OF_THE_BRAVE
            ],
            ClassEnum::RANGER => [
                self::BEASTBOUND,
                self::WAYFINDER
            ],
            ClassEnum::ROGUE => [
                self::NIGHTWALKER,
                self::SYNDICATE
            ],
            ClassEnum::SERAPH => [
                self::DIVINE_WIELDER,
                self::WINGED_SENTINEL
            ],
            ClassEnum::SORCERER => [
                self::ELEMENTAL_ORIGIN,
                self::PRIMAL_ORIGIN
            ],
            ClassEnum::WARLOCK => [
                self::PACT_OF_THE_ENDLESS,
                self::PACT_OF_THE_WRATHFUL
            ],
            ClassEnum::WARRIOR => [
                self::CALL_OF_THE_SLAYER,
                self::VENGEANCE
            ],
            ClassEnum::WITCH => [
                self::HEDGE,
                self::MOON
            ],
            ClassEnum::WIZARD => [
                self::SCHOOL_OF_KNOWLEDGE,
                self::SCHOOL_OF_WAR
            ],
        };
    }
}
