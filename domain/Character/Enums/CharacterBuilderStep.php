<?php

declare(strict_types=1);

namespace Domain\Character\Enums;

enum CharacterBuilderStep: string
{
    case CLASS_SELECTION = 'class_selection';
    case SUBCLASS_SELECTION = 'subclass_selection';
    case ANCESTRY = 'ancestry';
    case COMMUNITY = 'community';
    case TRAITS = 'traits';
    case EQUIPMENT = 'equipment';
    case BACKGROUND = 'background';
    case EXPERIENCES = 'experiences';
    case DOMAIN_CARDS = 'domain_cards';
    case CONNECTIONS = 'connections';

    public function getDisplayName(): string
    {
        return match ($this) {
            self::CLASS_SELECTION => 'Class',
            self::SUBCLASS_SELECTION => 'Subclass',
            self::ANCESTRY => 'Ancestry',
            self::COMMUNITY => 'Community',
            self::TRAITS => 'Traits',
            self::EQUIPMENT => 'Equipment',
            self::BACKGROUND => 'Background',
            self::EXPERIENCES => 'Experiences',
            self::DOMAIN_CARDS => 'Domain Cards',
            self::CONNECTIONS => 'Connections',
        };
    }

    public function getStepNumber(): int
    {
        $cases = self::getAllInOrder();

        return array_search($this, $cases, true) + 1;
    }

    public static function fromStepNumber(int $stepNumber): ?self
    {
        $cases = self::getAllInOrder();

        return $cases[$stepNumber - 1] ?? null;
    }

    /**
     * @return array<self>
     */
    public static function getAllInOrder(): array
    {
        return [
            self::CLASS_SELECTION,
            self::SUBCLASS_SELECTION,
            self::ANCESTRY,
            self::COMMUNITY,
            self::TRAITS,
            self::EQUIPMENT,
            self::BACKGROUND,
            self::EXPERIENCES,
            self::DOMAIN_CARDS,
            self::CONNECTIONS,
        ];
    }
}
