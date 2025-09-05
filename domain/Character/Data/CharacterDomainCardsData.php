<?php

declare(strict_types=1);

namespace Domain\Character\Data;

use Domain\Character\Models\Character;
use Livewire\Wireable;
use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;

class CharacterDomainCardsData extends Data implements Wireable
{
    use WireableData;
    
    public function __construct(
        public array $domainCards,
    ) {}

    public static function fromModel(Character $character): self
    {
        $cards = $character->domainCards()->get()->map(function ($card) {
            return [
                'domain' => $card->domain,
                'ability_key' => $card->ability_key,
                'ability_level' => $card->ability_level,
                'ability_name' => $card->getAbilityName(),
                'recall_cost' => $card->getRecallCost(),
            ];
        })->toArray();

        return new self(domainCards: $cards);
    }

    public static function fromBuilderData(array $selectedCards): self
    {
        return new self(domainCards: $selectedCards);
    }

    public function hasMinimumCards(int $minimumRequired = 2): bool
    {
        return count($this->domainCards) >= $minimumRequired;
    }

    public function getCardCount(): int
    {
        return count($this->domainCards);
    }

    public function getCardsByDomain(string $domain): array
    {
        return array_filter($this->domainCards, function ($card) use ($domain) {
            return $card['domain'] === $domain;
        });
    }

    public function getDomains(): array
    {
        return array_unique(array_column($this->domainCards, 'domain'));
    }

    public function getCardsByLevel(int $level): array
    {
        return array_filter($this->domainCards, function ($card) use ($level) {
            return ($card['ability_level'] ?? 1) === $level;
        });
    }

    public function getLevel1Cards(): array
    {
        return $this->getCardsByLevel(1);
    }

    public function getTotalRecallCost(): int
    {
        return array_sum(array_column($this->domainCards, 'recall_cost'));
    }

    public function getAverageRecallCost(): float
    {
        if (empty($this->domainCards)) {
            return 0;
        }

        return $this->getTotalRecallCost() / count($this->domainCards);
    }

    public function getCardNames(): array
    {
        return array_column($this->domainCards, 'ability_name');
    }

    public function hasCard(string $abilityKey): bool
    {
        foreach ($this->domainCards as $card) {
            if ($card['ability_key'] === $abilityKey) {
                return true;
            }
        }

        return false;
    }

    public function getCard(string $abilityKey): ?array
    {
        foreach ($this->domainCards as $card) {
            if ($card['ability_key'] === $abilityKey) {
                return $card;
            }
        }

        return null;
    }

    public function getFormattedCards(): array
    {
        return array_map(function ($card) {
            return [
                'domain' => ucfirst($card['domain']),
                'name' => ucwords(str_replace('-', ' ', $card['ability_key'])),
                'key' => $card['ability_key'],
                'level' => $card['ability_level'] ?? 1,
                'recallCost' => $card['recall_cost'] ?? 1,
                'isSpell' => $this->isSpell($card),
                'isAbility' => ! $this->isSpell($card),
            ];
        }, $this->domainCards);
    }

    public function getSpells(): array
    {
        return array_filter($this->domainCards, [$this, 'isSpell']);
    }

    public function getAbilities(): array
    {
        return array_filter($this->domainCards, function ($card) {
            return ! $this->isSpell($card);
        });
    }

    public function getDomainDistribution(): array
    {
        $distribution = [];
        foreach ($this->domainCards as $card) {
            $domain = $card['domain'];
            $distribution[$domain] = ($distribution[$domain] ?? 0) + 1;
        }

        return $distribution;
    }

    public function addCard(string $domain, string $abilityKey, int $level = 1, int $recallCost = 1): self
    {
        $cards = $this->domainCards;
        $cards[] = [
            'domain' => $domain,
            'ability_key' => $abilityKey,
            'ability_level' => $level,
            'ability_name' => ucwords(str_replace('-', ' ', $abilityKey)),
            'recall_cost' => $recallCost,
        ];

        return new self(domainCards: $cards);
    }

    public function removeCard(string $abilityKey): self
    {
        $cards = array_filter($this->domainCards, function ($card) use ($abilityKey) {
            return $card['ability_key'] !== $abilityKey;
        });

        return new self(domainCards: array_values($cards));
    }

    private function isSpell(array $card): bool
    {
        // This would typically check against the abilities JSON data
        // For now, we'll use naming conventions
        $name = strtolower($card['ability_key']);

        return str_contains($name, 'spell') ||
               str_contains($name, 'magic') ||
               str_contains($name, 'enchant') ||
               str_contains($name, 'ward') ||
               str_contains($name, 'blast') ||
               str_contains($name, 'bolt') ||
               ($card['ability_level'] ?? 1) > 1; // Higher level abilities are often spells
    }
}
