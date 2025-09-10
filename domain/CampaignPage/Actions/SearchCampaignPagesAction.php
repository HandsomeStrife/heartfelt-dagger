<?php

declare(strict_types=1);

namespace Domain\CampaignPage\Actions;

use Domain\Campaign\Models\Campaign;
use Domain\CampaignPage\Data\CampaignPageData;
use Domain\CampaignPage\Data\SearchCampaignPagesData;
use Domain\CampaignPage\Models\CampaignPage;
use Domain\User\Models\User;
use Illuminate\Support\Collection;

class SearchCampaignPagesAction
{
    /**
     * Search campaign pages with advanced filtering
     *
     * @return Collection<CampaignPageData>
     */
    public function execute(Campaign $campaign, SearchCampaignPagesData $searchData, ?User $user): Collection
    {
        $query = CampaignPage::with([
            'creator',
            'parent',
            'children' => function ($query) {
                $query->orderBy('display_order');
            },
            'authorizedUsers',
        ])
            ->inCampaign($campaign)
            ->accessibleBy($user);

        // Apply full-text search if query provided
        if (! empty($searchData->query)) {
            $query->search($searchData->query);
        }

        // Filter by category tags
        if (! empty($searchData->category_tags)) {
            foreach ($searchData->category_tags as $tag) {
                $query->whereJsonContains('category_tags', $tag);
            }
        }

        // Filter by access level
        if ($searchData->access_level !== null) {
            $query->where('access_level', $searchData->access_level);
        }

        // Filter by parent (including root pages only)
        if ($searchData->root_pages_only) {
            $query->rootLevel();
        } elseif ($searchData->parent_id !== null) {
            $query->where('parent_id', $searchData->parent_id);
        }

        // Apply sorting
        $this->applySorting($query, $searchData);

        // Apply limit
        $query->limit($searchData->limit);

        $pages = $query->get();

        return $pages->map(fn ($page) => CampaignPageData::fromModel($page));
    }

    /**
     * Get search suggestions based on partial query
     */
    public function getSuggestions(Campaign $campaign, string $partialQuery, ?User $user, int $limit = 5): Collection
    {
        if (strlen($partialQuery) < 2) {
            return collect();
        }

        $pages = CampaignPage::inCampaign($campaign)
            ->accessibleBy($user)
            ->where(function ($query) use ($partialQuery) {
                $query->where('title', 'LIKE', "%{$partialQuery}%")
                    ->orWhere('content', 'LIKE', "%{$partialQuery}%");
            })
            ->select(['id', 'title', 'parent_id'])
            ->with('parent:id,title')
            ->limit($limit)
            ->get();

        return $pages->map(function ($page) {
            return [
                'id' => $page->id,
                'title' => $page->title,
                'breadcrumb' => $page->parent ? $page->parent->title.' > '.$page->title : $page->title,
            ];
        });
    }

    /**
     * Get popular search terms for a campaign
     */
    public function getPopularCategories(Campaign $campaign, ?User $user, int $limit = 10): Collection
    {
        $pages = CampaignPage::inCampaign($campaign)
            ->accessibleBy($user)
            ->whereNotNull('category_tags')
            ->pluck('category_tags')
            ->flatten()
            ->countBy()
            ->sortDesc()
            ->take($limit);

        return $pages->map(function ($count, $tag) {
            return [
                'tag' => $tag,
                'count' => $count,
            ];
        })->values();
    }

    /**
     * Search across multiple campaigns (for global search)
     *
     * @param  Collection<Campaign>  $campaigns
     * @return Collection<CampaignPageData>
     */
    public function searchAcrossCampaigns(Collection $campaigns, SearchCampaignPagesData $searchData, ?User $user): Collection
    {
        $allResults = collect();

        foreach ($campaigns as $campaign) {
            $results = $this->execute($campaign, $searchData, $user);
            $allResults = $allResults->concat($results);
        }

        // Sort results globally if needed
        if ($searchData->sort_by === 'relevance' && ! empty($searchData->query)) {
            return $this->sortByRelevance($allResults, $searchData->query);
        }

        return $allResults->take($searchData->limit);
    }

    private function applySorting($query, SearchCampaignPagesData $searchData): void
    {
        switch ($searchData->sort_by) {
            case 'title':
                $query->orderBy('title', $searchData->sort_direction);
                break;
            case 'created_at':
                $query->orderBy('created_at', $searchData->sort_direction);
                break;
            case 'updated_at':
                $query->orderBy('updated_at', $searchData->sort_direction);
                break;
            case 'relevance':
            default:
                if (! empty($searchData->query)) {
                    // MySQL FULLTEXT score ordering
                    $query->orderByRaw('MATCH(title, content) AGAINST(? IN NATURAL LANGUAGE MODE) DESC', [$searchData->query]);
                } else {
                    // Default to updated_at for non-search queries
                    $query->orderBy('updated_at', 'desc');
                }
                break;
        }
    }

    private function sortByRelevance(Collection $results, string $query): Collection
    {
        $queryWords = array_map('strtolower', explode(' ', $query));

        return $results->sortByDesc(function (CampaignPageData $page) use ($queryWords) {
            $score = 0;
            $title = strtolower($page->title);
            $content = strtolower($page->content ?? '');

            foreach ($queryWords as $word) {
                // Title matches are weighted higher
                $score += substr_count($title, $word) * 10;
                $score += substr_count($content, $word);
            }

            return $score;
        })->values();
    }
}
