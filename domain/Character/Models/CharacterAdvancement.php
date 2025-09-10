<?php

declare(strict_types=1);

namespace Domain\Character\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CharacterAdvancement extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\CharacterAdvancementFactory::new();
    }

    protected $fillable = [
        'character_id',
        'tier',
        'advancement_number',
        'advancement_type',
        'advancement_data',
        'description',
    ];

    protected $casts = [
        'advancement_data' => 'array',
        'tier' => 'integer',
        'advancement_number' => 'integer',
    ];

    /**
     * Get the character that owns this advancement
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * Get advancement data for a specific type
     */
    public function getDataForType(string $key): mixed
    {
        return $this->advancement_data[$key] ?? null;
    }

    /**
     * Get trait bonuses if this is a trait advancement
     */
    public function getTraitBonuses(): array
    {
        if ($this->advancement_type !== 'trait_bonus') {
            return [];
        }

        $traits = $this->advancement_data['traits'] ?? [];
        $bonus = $this->advancement_data['bonus'] ?? 0;

        $bonuses = [];
        foreach ($traits as $trait) {
            $bonuses[$trait] = $bonus;
        }

        return $bonuses;
    }

    /**
     * Check if this advancement is of a specific type
     */
    public function isAdvancementType(string $type): bool
    {
        return $this->advancement_type === $type;
    }

    /**
     * Check if this advancement affects a specific trait
     */
    public function affectsTrait(string $trait_name): bool
    {
        if ($this->advancement_type !== 'trait_bonus') {
            return false;
        }

        $traits = $this->advancement_data['traits'] ?? [];

        return in_array($trait_name, $traits);
    }

    /**
     * Get the bonus value for a specific trait
     */
    public function getTraitBonus(string $trait_name): int
    {
        if (! $this->affectsTrait($trait_name)) {
            return 0;
        }

        return $this->advancement_data['bonus'] ?? 1;
    }

    /**
     * Check if this advancement provides an evasion bonus
     */
    public function providesEvasionBonus(): bool
    {
        return $this->advancement_type === 'evasion';
    }

    /**
     * Get the evasion bonus amount
     */
    public function getEvasionBonus(): int
    {
        if (! $this->providesEvasionBonus()) {
            return 0;
        }

        return $this->advancement_data['bonus'] ?? 1;
    }

    /**
     * Check if this advancement provides hit points
     */
    public function providesHitPoints(): bool
    {
        return $this->advancement_type === 'hit_point';
    }

    /**
     * Get the hit points bonus
     */
    public function getHitPointBonus(): int
    {
        if (! $this->providesHitPoints()) {
            return 0;
        }

        return $this->advancement_data['bonus'] ?? 1;
    }

    /**
     * Check if this advancement provides stress slots
     */
    public function providesStress(): bool
    {
        return $this->advancement_type === 'stress';
    }

    /**
     * Get the stress bonus
     */
    public function getStressBonus(): int
    {
        if (! $this->providesStress()) {
            return 0;
        }

        return $this->advancement_data['bonus'] ?? 1;
    }

    /**
     * Check if this advancement provides proficiency bonus
     */
    public function providesProficiencyBonus(): bool
    {
        return $this->advancement_type === 'proficiency';
    }

    /**
     * Get the proficiency bonus
     */
    public function getProficiencyBonus(): int
    {
        if (! $this->providesProficiencyBonus()) {
            return 0;
        }

        return $this->advancement_data['bonus'] ?? 1;
    }

    /**
     * Check if this advancement is a multiclass selection
     */
    public function isMulticlass(): bool
    {
        return $this->advancement_type === 'multiclass';
    }

    /**
     * Get the multiclass selection
     */
    public function getMulticlassSelection(): ?string
    {
        if (! $this->isMulticlass()) {
            return null;
        }

        return $this->advancement_data['class'] ?? null;
    }
}
