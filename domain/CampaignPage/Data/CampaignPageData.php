<?php

declare(strict_types=1);

namespace Domain\CampaignPage\Data;

use Domain\Campaign\Data\CampaignData;
use Domain\CampaignPage\Enums\PageAccessLevel;
use Domain\CampaignPage\Models\CampaignPage;
use Domain\User\Data\UserData;
use Illuminate\Support\Collection;
use Livewire\Wireable;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;

class CampaignPageData extends Data implements Wireable
{
    use WireableData;

    public function __construct(
        public ?int $id,
        public int $campaign_id,
        public ?int $parent_id,
        public int $creator_id,
        public string $title,
        public ?string $content,
        public array $category_tags,
        public PageAccessLevel $access_level,
        public int $display_order,
        public bool $is_published,
        public ?string $created_at = null,
        public ?string $updated_at = null,
        public ?CampaignData $campaign = null,
        public ?UserData $creator = null,
        public ?CampaignPageData $parent = null,
        /** @var \Illuminate\Support\Collection<int, self>|null */
        #[DataCollectionOf(CampaignPageData::class)]
        public ?Collection $children = null,
        public ?array $authorized_users = null,
        public ?int $depth_level = null,
        public ?array $breadcrumbs = null,
    ) {}



    public static function fromModel(CampaignPage $page): self
    {
        return self::from([
            'id' => $page->id,
            'campaign_id' => $page->campaign_id,
            'parent_id' => $page->parent_id,
            'creator_id' => $page->creator_id,
            'title' => $page->title,
            'content' => $page->content,
            'category_tags' => $page->category_tags ?? [],
            'access_level' => $page->access_level,
            'display_order' => $page->display_order,
            'is_published' => $page->is_published,
            'created_at' => $page->created_at?->toISOString(),
            'updated_at' => $page->updated_at?->toISOString(),
            'campaign' => $page->relationLoaded('campaign') ? CampaignData::from($page->campaign) : null,
            'creator' => $page->relationLoaded('creator') ? UserData::from($page->creator) : null,
            'parent' => $page->relationLoaded('parent') && $page->parent ? self::fromModel($page->parent) : null,
            'children' => $page->relationLoaded('children') ? 
                $page->children->map(fn ($child) => self::fromModel($child)) : null,
            'authorized_users' => $page->relationLoaded('authorizedUsers') ? 
                $page->authorizedUsers->map(fn ($user) => UserData::from($user))->toArray() : null,
            'depth_level' => $page->getDepthLevel(),
            'breadcrumbs' => array_map(fn ($ancestor) => self::fromModel($ancestor), $page->ancestors()),
        ]);
    }

    public function hasChildren(): bool
    {
        return $this->children && $this->children->isNotEmpty();
    }

    public function isRootLevel(): bool
    {
        return $this->parent_id === null;
    }

    public function getCategoryTagsAsString(): string
    {
        return implode(', ', $this->category_tags);
    }

    public function getAccessLevelLabel(): string
    {
        return $this->access_level->label();
    }

    public function getAccessLevelDescription(): string
    {
        return $this->access_level->description();
    }
}
