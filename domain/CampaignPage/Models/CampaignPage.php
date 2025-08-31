<?php

declare(strict_types=1);

namespace Domain\CampaignPage\Models;

use Domain\Campaign\Models\Campaign;
use Domain\CampaignPage\Enums\PageAccessLevel;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CampaignPage extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\CampaignPageFactory::new();
    }

    protected $guarded = [];

    protected $casts = [
        'category_tags' => 'array',
        'access_level' => PageAccessLevel::class,
        'is_published' => 'boolean',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(CampaignPage::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(CampaignPage::class, 'parent_id')
            ->orderBy('display_order');
    }

    public function authorizedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'campaign_page_access', 'campaign_page_id', 'user_id');
    }

    /**
     * Check if a user can view this page
     */
    public function canBeViewedBy(?User $user): bool
    {
        // Not published pages can only be viewed by creator
        if (!$this->is_published) {
            return $user && $this->creator_id === $user->id;
        }

        // No user means guest access - only allow if no restrictions
        if (!$user) {
            return false;
        }

        // Creator can always view
        if ($this->creator_id === $user->id) {
            return true;
        }

        // Campaign creator can always view
        if ($this->campaign->isCreator($user)) {
            return true;
        }

        // Check access level
        return match ($this->access_level) {
            PageAccessLevel::GM_ONLY => false,
            PageAccessLevel::ALL_PLAYERS => $this->campaign->canUserAccess($user),
            PageAccessLevel::SPECIFIC_PLAYERS => $this->authorizedUsers()->where('users.id', $user->id)->exists(),
        };
    }

    /**
     * Check if a user can edit this page
     */
    public function canBeEditedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        // Creator can always edit
        if ($this->creator_id === $user->id) {
            return true;
        }

        // Campaign creator can always edit
        if (!$this->relationLoaded('campaign')) {
            $this->load('campaign');
        }
        
        return $this->campaign && $this->campaign->isCreator($user);
    }

    /**
     * Get all ancestor pages (breadcrumb trail)
     */
    public function ancestors(): array
    {
        $ancestors = [];
        $current = $this->parent;

        while ($current) {
            array_unshift($ancestors, $current);
            $current = $current->parent;
        }

        return $ancestors;
    }

    /**
     * Get depth level in hierarchy (0 = root level)
     */
    public function getDepthLevel(): int
    {
        return count($this->ancestors());
    }

    /**
     * Check if this page is a descendant of another page
     */
    public function isDescendantOf(CampaignPage $page): bool
    {
        $current = $this->parent;

        while ($current) {
            if ($current->id === $page->id) {
                return true;
            }
            $current = $current->parent;
        }

        return false;
    }

    /**
     * Scope for pages accessible by a user
     */
    public function scopeAccessibleBy($query, ?User $user)
    {
        if (!$user) {
            return $query->where('id', null); // Return empty result
        }

        return $query->where(function ($q) use ($user) {
            // Creator can see all their pages
            $q->where('creator_id', $user->id)
              // Campaign creator can see all pages in their campaigns
              ->orWhereHas('campaign', function ($campaignQuery) use ($user) {
                  $campaignQuery->where('creator_id', $user->id);
              })
              // All players access
              ->orWhere(function ($accessQuery) use ($user) {
                  $accessQuery->where('access_level', PageAccessLevel::ALL_PLAYERS)
                             ->whereHas('campaign', function ($campaignQuery) use ($user) {
                                 $campaignQuery->whereHas('members', function ($memberQuery) use ($user) {
                                     $memberQuery->where('user_id', $user->id);
                                 });
                             });
              })
              // Specific players access
              ->orWhere(function ($accessQuery) use ($user) {
                  $accessQuery->where('access_level', PageAccessLevel::SPECIFIC_PLAYERS)
                             ->whereHas('authorizedUsers', function ($userQuery) use ($user) {
                                 $userQuery->where('users.id', $user->id);
                             });
              });
        })->where('is_published', true);
    }

    /**
     * Scope for root level pages (no parent)
     */
    public function scopeRootLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope for pages in a specific campaign
     */
    public function scopeInCampaign($query, Campaign $campaign)
    {
        return $query->where('campaign_id', $campaign->id);
    }

    /**
     * Full-text search scope
     */
    public function scopeSearch($query, string $searchTerm)
    {
        // Use LIKE search for better test compatibility
        // FULLTEXT search has minimum word length and indexing requirements
        return $query->where(function ($q) use ($searchTerm) {
            $q->where('title', 'LIKE', "%{$searchTerm}%")
              ->orWhere('content', 'LIKE', "%{$searchTerm}%");
        });
    }
}
