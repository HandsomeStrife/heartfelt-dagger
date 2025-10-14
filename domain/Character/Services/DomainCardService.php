<?php

declare(strict_types=1);

namespace Domain\Character\Services;

use Domain\Character\Models\Character;
use Domain\Character\Models\CharacterAdvancement;
use Domain\Character\Models\CharacterDomainCard;
use Illuminate\Support\Facades\Log;

/**
 * Service for managing domain card selection and validation.
 * 
 * Handles:
 * - Getting available domain cards based on character class and level
 * - Multiclass domain restrictions (cards limited to half character level)
 * - Validation that cards are from character's accessible domains
 * - Checking if character already has a card
 * 
 * Per DaggerHeart SRD Step Four: "Acquire a new domain card at your level or lower
 * from one of your class's domains"
 * 
 * This service is shared between CharacterBuilder and CharacterLevelUp.
 */
class DomainCardService
{
    public function __construct(
        private GameDataLoader $gameDataLoader,
    ) {}

    /**
     * Get available domain cards for a character at a specific level
     *
     * Returns all domain cards from the character's accessible domains
     * (class domains + multiclass domains) that are at or below the specified level.
     * Includes already-selected cards and marks them as such.
     *
     * @param Character $character The character to get cards for (determines accessible domains)
     * @param int $maxLevel Maximum level of cards to include (based on character level or half for multiclass)
     * 
     * @return array<int, array{
     *     key: string,
     *     name: string,
     *     domain: string,
     *     domain_name: string,
     *     domain_color: string,
     *     level: int,
     *     type: string,
     *     recall_cost: int,
     *     descriptions: array<int, string>,
     *     already_selected: bool,
     *     is_multiclass?: bool
     * }> Array of domain cards with all card details and selection status
     */
    public function getAvailableCards(Character $character, int $maxLevel): array
    {
        $classData = $this->loadClassData($character->class);
        $availableDomains = $classData['domains'] ?? [];

        if (empty($availableDomains)) {
            Log::warning('No class domains found for character', [
                'character_id' => $character->id,
                'class' => $character->class,
                'has_class_data' => ! empty($classData),
            ]);
        }

        // Get multiclass domains if applicable
        $multiclassDomains = $this->getMulticlassDomains($character);
        $allDomains = array_merge($availableDomains, $multiclassDomains);

        $abilities = $this->loadAbilities();
        
        if (empty($abilities)) {
            Log::error('No abilities data loaded - domain cards unavailable', [
                'character_id' => $character->id,
                'class' => $character->class,
                'max_level' => $maxLevel,
            ]);

            return [];
        }

        $domainCards = [];

        foreach ($abilities as $abilityKey => $abilityData) {
            $cardDomain = $abilityData['domain'] ?? '';
            $cardLevel = $abilityData['level'] ?? 1;

            // Card must be from available domains and at or below max level
            if (in_array($cardDomain, $allDomains)) {
                // Check if multiclass card (has level restriction)
                $isMulticlass = in_array($cardDomain, $multiclassDomains);
                $effectiveMaxLevel = $isMulticlass
                    ? (int) floor($character->level / 2)
                    : $maxLevel;

                if ($cardLevel <= $effectiveMaxLevel) {
                    $domainCards[] = [
                        'key' => $abilityKey,
                        'name' => $abilityData['name'] ?? ucwords(str_replace('-', ' ', $abilityKey)),
                        'domain' => $cardDomain,
                        'level' => $cardLevel,
                        'type' => $abilityData['type'] ?? 'Ability',
                        'recall_cost' => $abilityData['recallCost'] ?? 0,
                        'descriptions' => $abilityData['descriptions'] ?? [],
                        'is_multiclass' => $isMulticlass,
                    ];
                }
            }
        }

        return $domainCards;
    }

    /**
     * Validate that a card selection is legal for a character
     *
     * @param Character $character The character selecting the card
     * @param string $cardKey The ability key of the card
     * @param int $level The level at which the card is being selected
     * @return bool True if the selection is valid
     */
    public function validateCardSelection(Character $character, string $cardKey, int $level): bool
    {
        $availableCards = $this->getAvailableCards($character, $level);
        $cardKeys = array_column($availableCards, 'key');

        return in_array($cardKey, $cardKeys);
    }

    /**
     * Check if character already has a specific domain card
     *
     * @param Character $character The character to check
     * @param string $cardKey The ability key to check for
     * @return bool True if character has this card
     */
    public function hasCard(Character $character, string $cardKey): bool
    {
        return $character->domainCards()
            ->where('ability_key', $cardKey)
            ->exists();
    }

    /**
     * Get multiclass domains for a character
     * 
     * Returns domains from any multiclass advancement the character has taken.
     *
     * @param Character $character The character to check
     * @return array Array of domain keys from multiclass
     */
    private function getMulticlassDomains(Character $character): array
    {
        $multiclassAdvancements = CharacterAdvancement::where('character_id', $character->id)
            ->where('advancement_type', 'multiclass')
            ->get();

        $domains = [];

        foreach ($multiclassAdvancements as $advancement) {
            $multiclassKey = $advancement->advancement_data['class'] ?? null;

            if ($multiclassKey) {
                $multiclassData = $this->loadClassData($multiclassKey);
                $multiclassDomains = $multiclassData['domains'] ?? [];

                // Per SRD: "choose one of its domains" (singular)
                // The advancement should store which domain was selected
                $selectedDomain = $advancement->advancement_data['selected_domain'] ?? null;

                if ($selectedDomain && in_array($selectedDomain, $multiclassDomains)) {
                    $domains[] = $selectedDomain;
                }
            }
        }

        return $domains;
    }

    /**
     * Get domain cards grouped by domain
     *
     * @param Character $character The character to get cards for
     * @param int $maxLevel Maximum level of cards to include
     * @return array Domain cards grouped by domain key
     */
    public function getCardsByDomain(Character $character, int $maxLevel): array
    {
        $availableCards = $this->getAvailableCards($character, $maxLevel);
        $grouped = [];

        foreach ($availableCards as $card) {
            $domain = $card['domain'];

            if (! isset($grouped[$domain])) {
                $domainData = $this->getDomainData($domain);
                $grouped[$domain] = [
                    'domain_info' => $domainData,
                    'cards' => [],
                ];
            }

            $grouped[$domain]['cards'][] = $card;
        }

        // Sort cards within each domain by level
        foreach ($grouped as &$domainGroup) {
            usort($domainGroup['cards'], fn ($a, $b) => $a['level'] <=> $b['level']);
        }

        return $grouped;
    }

    /**
     * Get domain information using GameDataLoader service
     *
     * @param string $domainKey The domain key
     * @return array Domain data including name, color, description
     */
    private function getDomainData(string $domainKey): array
    {
        $domainData = $this->gameDataLoader->loadDomainData($domainKey);

        if (empty($domainData)) {
            return [
                'name' => ucwords($domainKey),
                'color' => '#000000',
            ];
        }

        return $domainData;
    }

    /**
     * Get all domains available to a character
     * 
     * Includes both class domains and multiclass domains.
     *
     * @param Character $character The character to get domains for
     * @return array Array of domain keys
     */
    public function getAvailableDomains(Character $character): array
    {
        $classData = $this->loadClassData($character->class);
        $classDomains = $classData['domains'] ?? [];
        $multiclassDomains = $this->getMulticlassDomains($character);

        return array_merge($classDomains, $multiclassDomains);
    }

    /**
     * Get count of domain cards character currently has
     *
     * @param Character $character The character to count for
     * @return int Number of domain cards
     */
    public function getDomainCardCount(Character $character): int
    {
        return $character->domainCards()->count();
    }

    /**
     * Get cards filtered by domain
     *
     * @param Character $character The character to get cards for
     * @param int $maxLevel Maximum level of cards
     * @param string $domainKey The domain to filter by
     * @return array Array of cards from specified domain
     */
    public function getCardsBySpecificDomain(
        Character $character,
        int $maxLevel,
        string $domainKey
    ): array {
        $allCards = $this->getAvailableCards($character, $maxLevel);

        return array_filter($allCards, fn ($card) => $card['domain'] === $domainKey);
    }

    /**
     * Load class data using GameDataLoader service
     *
     * @param string $classKey The class key
     * @return array The class data array
     */
    private function loadClassData(string $classKey): array
    {
        return $this->gameDataLoader->loadClassData($classKey);
    }

    /**
     * Load abilities data using GameDataLoader service
     *
     * @return array Abilities data keyed by ability key
     */
    private function loadAbilities(): array
    {
        return $this->gameDataLoader->loadAbilities();
    }
}

