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
use Illuminate\Support\Facades\Storage;

class Character extends Model
{
    use HasFactory;

    protected $fillable = [
        'character_key',
        'user_id',
        'name',
        'class',
        'subclass',
        'ancestry',
        'community',
        'level',
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
    ];

    /**
     * Generate a unique 8-character key for sharing
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Character $character) {
            if (empty($character->character_key)) {
                $character->character_key = static::generateUniqueKey();
            }
        });
    }

    /**
     * Generate a unique 8-character key
     */
    public static function generateUniqueKey(): string
    {
        do {
            $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $key = '';
            for ($i = 0; $i < 8; $i++) {
                $key .= $characters[random_int(0, strlen($characters) - 1)];
            }
        } while (static::where('character_key', $key)->exists());

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
        return asset('img/banners/'.strtolower($this->class).'.webp');
    }

    /**
     * Get the profile image URL
     */
    public function getProfileImage(): string
    {
        if ($this->profile_image_path) {
            // For S3, construct the URL manually or use Laravel's URL generation
            $s3Disk = Storage::disk('s3');
            if ($s3Disk->exists($this->profile_image_path)) {
                // Use config values to construct the URL
                $bucket = config('filesystems.disks.s3.bucket');
                $region = config('filesystems.disks.s3.region');
                $url = config('filesystems.disks.s3.url');

                // If custom URL is set, use it, otherwise construct standard S3 URL
                if ($url) {
                    return rtrim($url, '/').'/'.ltrim($this->profile_image_path, '/');
                } else {
                    return "https://{$bucket}.s3.{$region}.amazonaws.com/{$this->profile_image_path}";
                }
            }
        }

        return asset('img/default-avatar.png');
    }

    /**
     * Get the character's sharing URL
     */
    public function getShareUrl(): string
    {
        return route('character.show', ['characterKey' => $this->character_key]);
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
     * Get the route key for the model
     */
    public function getRouteKeyName(): string
    {
        return 'character_key';
    }
}
