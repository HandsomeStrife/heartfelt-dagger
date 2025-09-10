<?php

declare(strict_types=1);

namespace Domain\CampaignPage\Data;

use Domain\CampaignPage\Enums\PageAccessLevel;
use Livewire\Wireable;
use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;

class SearchCampaignPagesData extends Data implements Wireable
{
    use WireableData;

    public function __construct(
        public ?string $query = null,
        public ?array $category_tags = null,
        public ?PageAccessLevel $access_level = null,
        public ?int $parent_id = null,
        public bool $include_content = true,
        public bool $root_pages_only = false,
        public string $sort_by = 'relevance', // relevance, title, created_at, updated_at
        public string $sort_direction = 'desc',
        public int $limit = 50,
    ) {}

    public function hasFilters(): bool
    {
        return ! empty($this->query) ||
               ! empty($this->category_tags) ||
               $this->access_level !== null ||
               $this->parent_id !== null;
    }

    public function getSearchTerms(): array
    {
        if (empty($this->query)) {
            return [];
        }

        return array_filter(
            array_map('trim', explode(' ', $this->query)),
            fn ($term) => strlen($term) >= 2
        );
    }
}
