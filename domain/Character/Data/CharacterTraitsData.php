<?php

declare(strict_types=1);

namespace Domain\Character\Data;

use Domain\Character\Enums\TraitName;
use Domain\Character\Models\Character;
use Livewire\Wireable;
use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;

class CharacterTraitsData extends Data implements Wireable
{
    use WireableData;
    
    public function __construct(
        public int $agility,
        public int $strength,
        public int $finesse,
        public int $instinct,
        public int $presence,
        public int $knowledge,
    ) {}

    public static function fromModel(Character $character): self
    {
        $traits = $character->getTraitsArray();

        return new self(
            agility: $traits['agility'] ?? 0,
            strength: $traits['strength'] ?? 0,
            finesse: $traits['finesse'] ?? 0,
            instinct: $traits['instinct'] ?? 0,
            presence: $traits['presence'] ?? 0,
            knowledge: $traits['knowledge'] ?? 0,
        );
    }

    public static function fromArray(array $traits): self
    {
        return new self(
            agility: $traits['agility'] ?? 0,
            strength: $traits['strength'] ?? 0,
            finesse: $traits['finesse'] ?? 0,
            instinct: $traits['instinct'] ?? 0,
            presence: $traits['presence'] ?? 0,
            knowledge: $traits['knowledge'] ?? 0,
        );
    }

    public function getTrait(TraitName $trait): int
    {
        return match ($trait) {
            TraitName::AGILITY => $this->agility,
            TraitName::STRENGTH => $this->strength,
            TraitName::FINESSE => $this->finesse,
            TraitName::INSTINCT => $this->instinct,
            TraitName::PRESENCE => $this->presence,
            TraitName::KNOWLEDGE => $this->knowledge,
        };
    }

    public function getModifierString(TraitName $trait): string
    {
        $value = $this->getTrait($trait);

        return $value > 0 ? "+{$value}" : (string) $value;
    }

    public function toArray(): array
    {
        return [
            'agility' => $this->agility,
            'strength' => $this->strength,
            'finesse' => $this->finesse,
            'instinct' => $this->instinct,
            'presence' => $this->presence,
            'knowledge' => $this->knowledge,
        ];
    }

    public function isComplete(): bool
    {
        $values = array_values($this->toArray());
        $expectedValues = [-1, 0, 0, 1, 1, 2];
        sort($values);
        sort($expectedValues);

        return $values === $expectedValues;
    }

    public function getPositiveTraits(): array
    {
        $positive = [];
        foreach (TraitName::cases() as $trait) {
            if ($this->getTrait($trait) > 0) {
                $positive[$trait->value] = $this->getTrait($trait);
            }
        }

        return $positive;
    }

    public function getNegativeTraits(): array
    {
        $negative = [];
        foreach (TraitName::cases() as $trait) {
            if ($this->getTrait($trait) < 0) {
                $negative[$trait->value] = $this->getTrait($trait);
            }
        }

        return $negative;
    }

    public function getHighestTrait(): TraitName
    {
        $highest = TraitName::AGILITY;
        $highestValue = $this->agility;

        foreach (TraitName::cases() as $trait) {
            if ($this->getTrait($trait) > $highestValue) {
                $highest = $trait;
                $highestValue = $this->getTrait($trait);
            }
        }

        return $highest;
    }

    public function getLowestTrait(): TraitName
    {
        $lowest = TraitName::AGILITY;
        $lowestValue = $this->agility;

        foreach (TraitName::cases() as $trait) {
            if ($this->getTrait($trait) < $lowestValue) {
                $lowest = $trait;
                $lowestValue = $this->getTrait($trait);
            }
        }

        return $lowest;
    }
}
