<?php

declare(strict_types=1);

namespace Domain\Character\Models;

use Database\Factories\CharacterExperienceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CharacterExperience extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): CharacterExperienceFactory
    {
        return CharacterExperienceFactory::new();
    }

    protected $casts = [
        'modifier' => 'integer',
    ];

    /**
     * Get the character that owns this experience
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * Get the experience name formatted for display
     */
    public function getFormattedName(): string
    {
        return ucwords($this->experience_name);
    }

    /**
     * Get the modifier as a formatted string
     */
    public function getModifierString(): string
    {
        return $this->modifier > 0 ? '+'.$this->modifier : (string) $this->modifier;
    }

    /**
     * Get a short description if the full description is too long
     */
    public function getShortDescription(int $maxLength = 100): string
    {
        if (! $this->experience_description) {
            return '';
        }

        if (strlen($this->experience_description) <= $maxLength) {
            return $this->experience_description;
        }

        return substr($this->experience_description, 0, $maxLength).'...';
    }

    /**
     * Check if this experience has a description
     */
    public function hasDescription(): bool
    {
        return ! empty($this->experience_description);
    }

    /**
     * Check if this is a positive modifier
     */
    public function isPositive(): bool
    {
        return $this->modifier > 0;
    }

    /**
     * Check if this is a negative modifier
     */
    public function isNegative(): bool
    {
        return $this->modifier < 0;
    }

    /**
     * Get the experience category based on name patterns
     */
    public function getCategory(): string
    {
        $name = strtolower($this->experience_name);

        if (str_contains($name, 'combat') || str_contains($name, 'fighting') || str_contains($name, 'warrior')) {
            return 'Combat';
        }

        if (str_contains($name, 'magic') || str_contains($name, 'arcane') || str_contains($name, 'spell')) {
            return 'Magic';
        }

        if (str_contains($name, 'social') || str_contains($name, 'noble') || str_contains($name, 'court') || str_contains($name, 'etiquette')) {
            return 'Social';
        }

        if (str_contains($name, 'craft') || str_contains($name, 'smith') || str_contains($name, 'trade')) {
            return 'Crafting';
        }

        if (str_contains($name, 'nature') || str_contains($name, 'wilderness') || str_contains($name, 'survival')) {
            return 'Nature';
        }

        if (str_contains($name, 'lore') || str_contains($name, 'knowledge') || str_contains($name, 'scholar')) {
            return 'Knowledge';
        }

        return 'General';
    }

    /**
     * Scope for experiences with positive modifiers
     */
    public function scopePositive($query)
    {
        return $query->where('modifier', '>', 0);
    }

    /**
     * Scope for experiences with negative modifiers
     */
    public function scopeNegative($query)
    {
        return $query->where('modifier', '<', 0);
    }

    /**
     * Scope for experiences with descriptions
     */
    public function scopeWithDescription($query)
    {
        return $query->whereNotNull('experience_description')
            ->where('experience_description', '!=', '');
    }

    /**
     * Scope for experiences by category
     */
    public function scopeByCategory($query, string $category)
    {
        $patterns = match (strtolower($category)) {
            'combat' => ['combat', 'fighting', 'warrior', 'battle'],
            'magic' => ['magic', 'arcane', 'spell', 'wizard'],
            'social' => ['social', 'noble', 'court', 'etiquette', 'diplomat'],
            'crafting' => ['craft', 'smith', 'trade', 'artisan'],
            'nature' => ['nature', 'wilderness', 'survival', 'druid'],
            'knowledge' => ['lore', 'knowledge', 'scholar', 'academic'],
            default => [],
        };

        if (empty($patterns)) {
            return $query;
        }

        return $query->where(function ($q) use ($patterns) {
            foreach ($patterns as $pattern) {
                $q->orWhere('experience_name', 'LIKE', "%{$pattern}%");
            }
        });
    }
}
