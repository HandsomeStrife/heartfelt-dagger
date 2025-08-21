<?php

declare(strict_types=1);

namespace Domain\Character\Models;

use Database\Factories\CharacterDomainCardFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CharacterDomainCard extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): CharacterDomainCardFactory
    {
        return CharacterDomainCardFactory::new();
    }

    protected $casts = [
        'ability_level' => 'integer',
    ];

    /**
     * Get the character that owns this domain card
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * Get the domain name formatted for display
     */
    public function getDomainLabel(): string
    {
        return ucfirst($this->domain);
    }

    /**
     * Get the ability name formatted for display
     */
    public function getAbilityName(): string
    {
        return ucwords(str_replace('-', ' ', $this->ability_key));
    }

    /**
     * Check if this is a spell or ability
     */
    public function isSpell(): bool
    {
        // This would be determined by checking the abilities JSON data
        // For now, we'll assume it's determined by naming convention or ability data
        return str_contains(strtolower($this->ability_key), 'spell') ||
               str_contains(strtolower($this->ability_key), 'magic');
    }

    /**
     * Get the recall cost from ability data
     * This would typically come from the abilities JSON
     */
    public function getRecallCost(): int
    {
        // This would be loaded from the abilities.json file
        // For now, return a default based on level
        return match ($this->ability_level) {
            1 => 1,
            2, 3 => 1,
            4, 5 => 2,
            6, 7 => 2,
            8, 9 => 3,
            10 => 3,
            default => 1,
        };
    }

    /**
     * Scope for specific domain
     */
    public function scopeForDomain($query, string $domain)
    {
        return $query->where('domain', $domain);
    }

    /**
     * Scope for specific ability level
     */
    public function scopeForLevel($query, int $level)
    {
        return $query->where('ability_level', $level);
    }

    /**
     * Scope for low level abilities (1-3)
     */
    public function scopeLowLevel($query)
    {
        return $query->where('ability_level', '<=', 3);
    }

    /**
     * Scope for mid level abilities (4-7)
     */
    public function scopeMidLevel($query)
    {
        return $query->whereBetween('ability_level', [4, 7]);
    }

    /**
     * Scope for high level abilities (8-10)
     */
    public function scopeHighLevel($query)
    {
        return $query->where('ability_level', '>=', 8);
    }

    /**
     * Scope for starting abilities (level 1)
     */
    public function scopeStarting($query)
    {
        return $query->where('ability_level', 1);
    }
}
