<?php

declare(strict_types=1);

namespace App\View\Components\Character;

use Domain\Character\Enums\DomainColor;
use Illuminate\View\Component;

class DomainCardSelector extends Component
{
    public array $groupedCards;
    public array $domainColors;
    public ?string $selectedCard;
    public int $level;
    public bool $groupByDomain;

    /**
     * Create a new component instance.
     */
    public function __construct(
        array $cards = [],
        ?string $selectedCard = null,
        int $level = 1,
        bool $groupByDomain = true
    ) {
        $this->selectedCard = $selectedCard;
        $this->level = $level;
        $this->groupByDomain = $groupByDomain;
        $this->domainColors = DomainColor::all();
        $this->groupedCards = $groupByDomain ? $this->groupCardsByDomain($cards) : ['ungrouped' => $cards];
    }

    /**
     * Group cards by their domain
     *
     * @param array $cards
     * @return array<string, array>
     */
    private function groupCardsByDomain(array $cards): array
    {
        $grouped = [];

        foreach ($cards as $card) {
            $domain = $card['domain'] ?? 'unknown';
            if (! isset($grouped[$domain])) {
                $grouped[$domain] = [];
            }
            $grouped[$domain][] = $card;
        }

        return $grouped;
    }

    /**
     * Get the domain color for a given domain key
     *
     * @param string $domain
     * @return string
     */
    public function getDomainColor(string $domain): string
    {
        return DomainColor::fromDomain($domain);
    }

    /**
     * Get display name for a domain
     *
     * @param string $domain
     * @return string
     */
    public function getDomainDisplayName(string $domain): string
    {
        return ucfirst($domain);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.character.domain-card-selector');
    }
}


