<?php

declare(strict_types=1);

namespace Domain\Character\Models;

use Database\Factories\CharacterTraitFactory;
use Domain\Character\Enums\TraitName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CharacterTrait extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): CharacterTraitFactory
    {
        return CharacterTraitFactory::new();
    }

    protected $casts = [
        'trait_value' => 'integer',
    ];

    /**
     * Get the character that owns this trait
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * Get the trait name as an enum
     */
    public function getTraitNameEnum(): TraitName
    {
        return TraitName::from($this->trait_name);
    }

    /**
     * Get the trait label
     */
    public function getTraitLabel(): string
    {
        return $this->getTraitNameEnum()->label();
    }

    /**
     * Get the trait description
     */
    public function getTraitDescription(): string
    {
        return $this->getTraitNameEnum()->description();
    }

    /**
     * Get the modifier string for display
     */
    public function getModifierString(): string
    {
        $value = $this->trait_value;
        if ($value > 0) {
            return '+'.$value;
        }

        return (string) $value;
    }

    /**
     * Check if this is a positive modifier
     */
    public function isPositive(): bool
    {
        return $this->trait_value > 0;
    }

    /**
     * Check if this is a negative modifier
     */
    public function isNegative(): bool
    {
        return $this->trait_value < 0;
    }

    /**
     * Scope for a specific trait
     */
    public function scopeForTrait($query, TraitName $trait)
    {
        return $query->where('trait_name', $trait->value);
    }

    /**
     * Scope for positive modifiers
     */
    public function scopePositive($query)
    {
        return $query->where('trait_value', '>', 0);
    }

    /**
     * Scope for negative modifiers
     */
    public function scopeNegative($query)
    {
        return $query->where('trait_value', '<', 0);
    }
}
