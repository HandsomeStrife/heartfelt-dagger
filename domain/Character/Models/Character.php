<?php

declare(strict_types=1);

namespace Domain\Character\Models;

use Database\Factories\CharacterFactory;
use Domain\Character\Enums\EquipmentType;
use Domain\Character\Enums\TraitName;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class Character extends Model
{
    use HasFactory;

    protected $fillable = [
        'character_key',
        'public_key',
        'user_id',
        'name',
        'pronouns',
        'class',
        'subclass',
        'ancestry',
        'community',
        'level',
        'proficiency',
        'profile_image_path',
        'character_data',
        'is_public',
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): CharacterFactory
    {
        return CharacterFactory::new();
    }

    protected $casts = [
        'character_data' => 'array',
        'is_public' => 'boolean',
        'level' => 'integer',
        'proficiency' => 'integer',
    ];

    /**
     * Generate unique keys for sharing and public access
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Character $character) {
            if (empty($character->character_key)) {
                $character->character_key = static::generateUniqueKey();
            }
            if (empty($character->public_key)) {
                $character->public_key = static::generateUniquePublicKey();
            }
        });
    }

    /**
     * Generate a unique 10-character key for character identification
     */
    public static function generateUniqueKey(): string
    {
        do {
            $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $key = '';
            for ($i = 0; $i < 10; $i++) {
                $key .= $characters[random_int(0, strlen($characters) - 1)];
            }
        } while (static::where('character_key', $key)->exists());

        return $key;
    }

    /**
     * Generate a unique 10-character public key for sharing
     */
    public static function generateUniquePublicKey(): string
    {
        do {
            $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $key = '';
            for ($i = 0; $i < 10; $i++) {
                $key .= $characters[random_int(0, strlen($characters) - 1)];
            }
        } while (static::where('public_key', $key)->exists());

        return $key;
    }

    /**
     * Get the user that owns the character
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the character's traits
     */
    public function traits(): HasMany
    {
        return $this->hasMany(CharacterTrait::class);
    }

    /**
     * Get the character's equipment
     */
    public function equipment(): HasMany
    {
        return $this->hasMany(CharacterEquipment::class);
    }

    /**
     * Get the character's domain cards
     */
    public function domainCards(): HasMany
    {
        return $this->hasMany(CharacterDomainCard::class);
    }

    /**
     * Get the character's experiences
     */
    public function experiences(): HasMany
    {
        return $this->hasMany(CharacterExperience::class);
    }

    /**
     * Get the character's advancements
     */
    public function advancements(): HasMany
    {
        return $this->hasMany(CharacterAdvancement::class);
    }

    /**
     * Get the character's interactive status (HP marks, stress marks, etc.)
     */
    public function status(): HasOne
    {
        return $this->hasOne(CharacterStatus::class);
    }

    /**
     * Get equipped weapons
     */
    public function weapons(): HasMany
    {
        return $this->equipment()->where('equipment_type', EquipmentType::WEAPON->value)->where('is_equipped', true);
    }

    /**
     * Get equipped armor
     */
    public function armor(): HasMany
    {
        return $this->equipment()->where('equipment_type', EquipmentType::ARMOR->value)->where('is_equipped', true);
    }

    /**
     * Get equipped items
     */
    public function items(): HasMany
    {
        return $this->equipment()->where('equipment_type', EquipmentType::ITEM->value);
    }

    /**
     * Get consumable items
     */
    public function consumables(): HasMany
    {
        return $this->equipment()->where('equipment_type', EquipmentType::CONSUMABLE->value);
    }

    /**
     * Get a specific trait value
     */
    public function getTraitValue(TraitName $trait): int
    {
        return $this->traits()
            ->where('trait_name', $trait->value)
            ->value('trait_value') ?? 0;
    }

    /**
     * Get effective trait value including advancement bonuses
     */
    public function getEffectiveTraitValue(TraitName $trait): int
    {
        $base_value = $this->getTraitValue($trait);

        // Add trait advancement bonuses
        $trait_advancements = $this->advancements
            ->where('advancement_type', 'trait_bonus')
            ->filter(function ($advancement) use ($trait) {
                $traits = $advancement->advancement_data['traits'] ?? [];

                return in_array($trait->value, $traits);
            });

        $advancement_bonus = $trait_advancements->sum(function ($advancement) {
            return $advancement->advancement_data['bonus'] ?? 0;
        });

        return $base_value + $advancement_bonus;
    }

    /**
     * Get all traits as an associative array
     */
    public function getTraitsArray(): array
    {
        return $this->traits()
            ->pluck('trait_value', 'trait_name')
            ->toArray();
    }

    /**
     * Get the banner image for this character's class
     */
    public function getBanner(): string
    {
        $class = $this->class ?: 'warrior'; // Default to warrior if class is null

        return asset('img/banners/'.strtolower($class).'.webp');
    }

    /**
     * Get the profile image URL using signed URL for security
     */
    public function getProfileImage(): string
    {
        if ($this->profile_image_path) {
            // Try S3 first if configured, otherwise fall back to local
            try {
                // Check if S3 is properly configured
                if (config('filesystems.disks.s3.key') && config('filesystems.disks.s3.secret')) {
                    $s3Disk = Storage::disk('s3');

                    // For MinIO/S3, always try to generate temporaryUrl
                    // even if we can't check existence (exists() might fail on MinIO)
                    return $s3Disk->temporaryUrl(
                        $this->profile_image_path,
                        now()->addHours(24) // URLs valid for 24 hours
                    );
                }
            } catch (\Exception $e) {
                // S3 not configured or error, fall back to local
                \Illuminate\Support\Facades\Log::warning('S3 not available for profile image, falling back to local storage: '.$e->getMessage());
            }

            // Fall back to local storage (use 'public' disk for local development)
            return Storage::disk('public')->url($this->profile_image_path);
        }

        return asset('img/default-avatar.png');
    }

    /**
     * Get the character's private sharing URL (for character owner)
     */
    public function getShareUrl(): string
    {
        return route('character.show', ['public_key' => $this->character_key]);
    }

    /**
     * Get the character's public sharing URL (for public viewing)
     */
    public function getPublicShareUrl(): string
    {
        return route('character.show', ['public_key' => $this->public_key]);
    }

    /**
     * Scope for public characters
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope for characters by class
     */
    public function scopeByClass($query, string $class)
    {
        return $query->where('class', $class);
    }

    /**
     * Scope for characters by ancestry
     */
    public function scopeByAncestry($query, string $ancestry)
    {
        return $query->where('ancestry', $ancestry);
    }

    /**
     * Scope for finding character by public key
     */
    public function scopeByPublicKey($query, string $publicKey)
    {
        return $query->where('public_key', $publicKey);
    }

    /**
     * Get the route key for the model
     */
    public function getRouteKeyName(): string
    {
        return 'character_key';
    }

    /**
     * Get ancestry bonus for evasion from effects
     */
    public function getAncestryEvasionBonus(): int
    {
        $effects = $this->getAncestryEffects('evasion_bonus');
        $bonus = 0;
        foreach ($effects as $effect) {
            $bonus += $effect['value'] ?? 0;
        }

        return $bonus;
    }

    /**
     * Get ancestry bonus for hit points from effects
     */
    public function getAncestryHitPointBonus(): int
    {
        $effects = $this->getAncestryEffects('hit_point_bonus');
        $bonus = 0;
        foreach ($effects as $effect) {
            $bonus += $effect['value'] ?? 0;
        }

        return $bonus;
    }

    /**
     * Get ancestry bonus for stress from effects
     */
    public function getAncestryStressBonus(): int
    {
        $effects = $this->getAncestryEffects('stress_bonus');
        $bonus = 0;
        foreach ($effects as $effect) {
            $bonus += $effect['value'] ?? 0;
        }

        return $bonus;
    }

    /**
     * Get ancestry bonus for damage thresholds from effects
     */
    public function getAncestryDamageThresholdBonus(): int
    {
        $effects = $this->getAncestryEffects('damage_threshold_bonus');
        $bonus = 0;
        foreach ($effects as $effect) {
            $value = $effect['value'] ?? 0;
            if ($value === 'proficiency') {
                $bonus += $this->getProficiencyBonus();
            } else {
                $bonus += (int) $value;
            }
        }

        return $bonus;
    }

    /**
     * Get subclass bonus for evasion from effects
     */
    public function getSubclassEvasionBonus(): int
    {
        $effects = $this->getSubclassEffects('evasion_bonus');
        $bonus = 0;
        foreach ($effects as $effect) {
            $bonus += $effect['value'] ?? 0;
        }

        return $bonus;
    }

    /**
     * Get subclass bonus for hit points from effects
     */
    public function getSubclassHitPointBonus(): int
    {
        $effects = $this->getSubclassEffects('hit_point_bonus');
        $bonus = 0;
        foreach ($effects as $effect) {
            $bonus += $effect['value'] ?? 0;
        }

        return $bonus;
    }

    /**
     * Get subclass bonus for stress from effects
     */
    public function getSubclassStressBonus(): int
    {
        $effects = $this->getSubclassEffects('stress_bonus');
        $bonus = 0;
        foreach ($effects as $effect) {
            $bonus += $effect['value'] ?? 0;
        }

        return $bonus;
    }

    /**
     * Get subclass bonus for damage thresholds from effects
     */
    public function getSubclassDamageThresholdBonus(): int
    {
        $effects = $this->getSubclassEffects('damage_threshold_bonus');
        $bonus = 0;
        foreach ($effects as $effect) {
            $value = $effect['value'] ?? 0;
            $bonus += (int) $value;
        }

        return $bonus;
    }

    /**
     * Get subclass bonus for severe damage threshold from effects
     */
    public function getSubclassSevereThresholdBonus(): int
    {
        $effects = $this->getSubclassEffects('severe_threshold_bonus');
        $bonus = 0;
        foreach ($effects as $effect) {
            $value = $effect['value'] ?? 0;
            $bonus += (int) $value;
        }

        return $bonus;
    }

    /**
     * Get subclass bonus for domain cards from effects
     */
    public function getSubclassDomainCardBonus(): int
    {
        $effects = $this->getSubclassEffects('domain_card_bonus');
        $bonus = 0;
        foreach ($effects as $effect) {
            $bonus += $effect['value'] ?? 0;
        }

        return $bonus;
    }

    /**
     * Get the maximum number of domain cards this character can have
     */
    public function getMaxDomainCards(): int
    {
        // Base starting domain cards for all characters
        $base_cards = 2;

        // Add subclass bonuses
        $subclass_bonus = $this->getSubclassDomainCardBonus();

        return $base_cards + $subclass_bonus;
    }

    /**
     * Get subclass effects by type
     */
    public function getSubclassEffects(string $effectType): array
    {
        if (! $this->subclass) {
            return [];
        }

        $subclassData = $this->getSubclassData();
        if (! $subclassData) {
            return [];
        }

        $effects = [];
        $allFeatures = array_merge(
            $subclassData['foundationFeatures'] ?? [],
            $subclassData['specializationFeatures'] ?? [],
            $subclassData['masteryFeatures'] ?? []
        );

        foreach ($allFeatures as $feature) {
            $featureEffects = $feature['effects'] ?? [];
            foreach ($featureEffects as $effect) {
                if (($effect['type'] ?? '') === $effectType) {
                    $effects[] = $effect;
                }
            }
        }

        return $effects;
    }

    /**
     * Get subclass data from JSON file
     */
    private function getSubclassData(): ?array
    {
        $path = resource_path('json/subclasses.json');
        if (! file_exists($path)) {
            return null;
        }

        $subclasses = json_decode(file_get_contents($path), true);

        return $subclasses[$this->subclass] ?? null;
    }

    /**
     * Get ancestry effects by type
     */
    public function getAncestryEffects(string $effectType): array
    {
        if (! $this->ancestry) {
            return [];
        }

        $ancestriesData = $this->getAncestryData();
        if (! $ancestriesData) {
            return [];
        }

        $effects = [];
        $features = $ancestriesData['features'] ?? [];

        foreach ($features as $feature) {
            $featureEffects = $feature['effects'] ?? [];
            foreach ($featureEffects as $effect) {
                if (($effect['type'] ?? '') === $effectType) {
                    $effects[] = $effect;
                }
            }
        }

        return $effects;
    }

    /**
     * Get ancestry data from JSON file
     */
    private function getAncestryData(): ?array
    {
        $path = resource_path('json/ancestries.json');
        if (! file_exists($path)) {
            return null;
        }

        $ancestries = json_decode(file_get_contents($path), true);

        return $ancestries[$this->ancestry] ?? null;
    }

    /**
     * Check if the ancestry has experience bonus selection effect
     */
    public function hasExperienceBonusSelection(): bool
    {
        return ! empty($this->getAncestryEffects('experience_bonus_selection'));
    }

    /**
     * Get the experience that receives bonus from experience_bonus_selection effect
     */
    public function getClankBonusExperience(): ?string
    {
        if (! $this->hasExperienceBonusSelection()) {
            return null;
        }

        return $this->character_data['clank_bonus_experience'] ?? null;
    }

    /**
     * Get the modifier for a specific experience (including ancestry bonuses)
     */
    public function getExperienceModifier(string $experienceName): int
    {
        $baseModifier = 2; // All experiences start with +2

        // Check if this experience gets experience bonus selection effect
        if ($this->hasExperienceBonusSelection() &&
            $this->getClankBonusExperience() === $experienceName) {

            $effects = $this->getAncestryEffects('experience_bonus_selection');
            $bonus = 0;
            foreach ($effects as $effect) {
                $bonus += $effect['value'] ?? 0;
            }

            return $baseModifier + $bonus;
        }

        return $baseModifier;
    }

    /**
     * Get total proficiency bonus (base + advancements)
     */
    public function getProficiencyBonus(): int
    {
        // Base proficiency increases with character level
        // Level 1: +0, Level 2-4: +1, Level 5-7: +2, Level 8-10: +3
        $base_proficiency = match (true) {
            $this->level <= 1 => 0,
            $this->level <= 4 => 1,
            $this->level <= 7 => 2,
            default => 3,
        };

        // Add advancement bonuses
        $advancement_bonus = $this->advancements
            ->where('advancement_type', 'proficiency')
            ->sum(function ($advancement) {
                return $advancement->advancement_data['bonus'] ?? 0;
            });

        return $base_proficiency + $advancement_bonus;
    }

    /**
     * Get total evasion bonuses from all sources
     */
    public function getTotalEvasionBonuses(): array
    {
        $bonuses = [];

        // Ancestry bonus
        $ancestry_bonus = $this->getAncestryEvasionBonus();
        if ($ancestry_bonus > 0) {
            $bonuses['ancestry'] = $ancestry_bonus;
        }

        // Subclass bonus
        $subclass_bonus = $this->getSubclassEvasionBonus();
        if ($subclass_bonus > 0) {
            $bonuses['subclass'] = $subclass_bonus;
        }

        // Advancement bonuses
        $advancement_bonus = $this->advancements
            ->where('advancement_type', 'evasion')
            ->sum(function ($advancement) {
                return $advancement->advancement_data['bonus'] ?? 0;
            });

        if ($advancement_bonus > 0) {
            $bonuses['advancements'] = $advancement_bonus;
        }

        return $bonuses;
    }

    /**
     * Get total hit point bonuses from all sources
     */
    public function getTotalHitPointBonuses(): array
    {
        $bonuses = [];

        // Ancestry bonus
        $ancestry_bonus = $this->getAncestryHitPointBonus();
        if ($ancestry_bonus > 0) {
            $bonuses['ancestry'] = $ancestry_bonus;
        }

        // Subclass bonus
        $subclass_bonus = $this->getSubclassHitPointBonus();
        if ($subclass_bonus > 0) {
            $bonuses['subclass'] = $subclass_bonus;
        }

        // Advancement bonuses
        $advancement_bonus = $this->advancements
            ->where('advancement_type', 'hit_point')
            ->sum(function ($advancement) {
                return $advancement->advancement_data['bonus'] ?? 0;
            });

        if ($advancement_bonus > 0) {
            $bonuses['advancements'] = $advancement_bonus;
        }

        return $bonuses;
    }

    /**
     * Get total stress bonuses from all sources
     */
    public function getTotalStressBonuses(): array
    {
        $bonuses = [];

        // Ancestry bonus
        $ancestry_bonus = $this->getAncestryStressBonus();
        if ($ancestry_bonus > 0) {
            $bonuses['ancestry'] = $ancestry_bonus;
        }

        // Subclass bonus
        $subclass_bonus = $this->getSubclassStressBonus();
        if ($subclass_bonus > 0) {
            $bonuses['subclass'] = $subclass_bonus;
        }

        // Advancement bonuses
        $advancement_bonus = $this->advancements
            ->where('advancement_type', 'stress')
            ->sum(function ($advancement) {
                return $advancement->advancement_data['bonus'] ?? 0;
            });

        if ($advancement_bonus > 0) {
            $bonuses['advancements'] = $advancement_bonus;
        }

        return $bonuses;
    }

    /**
     * Get character's tier based on level
     */
    public function getTier(): int
    {
        return match (true) {
            $this->level >= 8 => 4,
            $this->level >= 5 => 3,
            $this->level >= 2 => 2,
            default => 1,
        };
    }

    /**
     * Get multiclass selections from advancements
     */
    public function getMulticlassSelections(): array
    {
        return $this->advancements
            ->where('advancement_type', 'multiclass')
            ->pluck('advancement_data')
            ->map(function ($data) {
                return $data['class'] ?? null;
            })
            ->filter()
            ->toArray();
    }

    /**
     * Get computed character stats for display in viewer
     */
    public function getComputedStats(array $class_data = []): array
    {
        // For saved characters, we need to reconstruct the CharacterBuilderData from the database
        // The class/subclass are stored as separate columns, not in character_data
        if (! empty($this->character_data)) {
            // Build a complete character data array that matches CharacterBuilderData structure
            $builder_data_array = [
                'selected_class' => $this->class,
                'selected_subclass' => $this->subclass,
                'selected_ancestry' => $this->ancestry,
                'selected_community' => $this->community,
                'name' => $this->name,
                'assigned_traits' => $this->getTraitsArray(),
                'background_answers' => $this->character_data['background']['answers'] ?? [],
                'connections' => $this->character_data['connections'] ?? [],
                'experiences' => $this->experiences()->get()->map(function ($exp) {
                    return [
                        'name' => $exp->experience_name,
                        'description' => $exp->experience_description,
                    ];
                })->toArray(),
                'selected_equipment' => $this->equipment()->get()->map(function ($equip) {
                    return [
                        'type' => $equip->equipment_type,
                        'key' => $equip->equipment_key,
                        'data' => $equip->equipment_data,
                    ];
                })->toArray(),
                'selected_domain_cards' => $this->domainCards()->get()->map(function ($card) {
                    return [
                        'domain' => $card->domain,
                        'ability_key' => $card->ability_key,
                        'ability_level' => $card->ability_level,
                    ];
                })->toArray(),
            ];

            $character_builder_data = \Domain\Character\Data\CharacterBuilderData::from($builder_data_array);

            return $character_builder_data->getComputedStats($class_data);
        }

        // Fallback for empty character data - use CharacterStatsData
        return \Domain\Character\Data\CharacterStatsData::fromModel($this)->toArray();
    }
}
