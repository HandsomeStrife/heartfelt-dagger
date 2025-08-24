<?php

declare(strict_types=1);

namespace Domain\CampaignFrame\Models;

use Domain\Campaign\Models\Campaign;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CampaignFrame extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_public' => 'boolean',
        'complexity_rating' => 'integer',
        'pitch' => 'array',
        'tone_and_themes' => 'array',
        'setting_guidance' => 'array',
        'principles' => 'array',
        'setting_distinctions' => 'array',
        'special_mechanics' => 'array',
        'session_zero_questions' => 'array',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeByUser($query, User $user)
    {
        return $query->where('creator_id', $user->id);
    }

    public function canBeEditedBy(User $user): bool
    {
        return $this->creator_id === $user->id;
    }

    public function canBeViewedBy(?User $user): bool
    {
        // Public frames can be viewed by anyone
        if ($this->is_public) {
            return true;
        }

        // Private frames can only be viewed by their creator
        return $user && $this->creator_id === $user->id;
    }
}
