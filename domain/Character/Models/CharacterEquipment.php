<?php

declare(strict_types=1);

namespace Domain\Character\Models;

use Database\Factories\CharacterEquipmentFactory;
use Domain\Character\Enums\EquipmentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CharacterEquipment extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): CharacterEquipmentFactory
    {
        return CharacterEquipmentFactory::new();
    }

    protected $casts = [
        'equipment_data' => 'array',
        'is_equipped' => 'boolean',
    ];

    /**
     * Get the character that owns this equipment
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * Get the equipment type as an enum
     */
    public function getEquipmentTypeEnum(): EquipmentType
    {
        return EquipmentType::from($this->equipment_type);
    }

    /**
     * Get the equipment type label
     */
    public function getEquipmentTypeLabel(): string
    {
        return $this->getEquipmentTypeEnum()->label();
    }

    /**
     * Get the equipment name from data or key
     */
    public function getEquipmentName(): string
    {
        return $this->equipment_data['name'] ?? ucwords(str_replace('-', ' ', $this->equipment_key));
    }

    /**
     * Get equipment damage information (for weapons)
     */
    public function getDamage(): ?array
    {
        return $this->equipment_data['damage'] ?? null;
    }

    /**
     * Get equipment armor information (for armor)
     */
    public function getArmor(): ?array
    {
        return [
            'score' => $this->equipment_data['baseScore'] ?? null,
            'thresholds' => $this->equipment_data['baseThresholds'] ?? null,
        ];
    }

    /**
     * Get equipment feature information
     */
    public function getFeature(): ?array
    {
        return $this->equipment_data['feature'] ?? null;
    }

    /**
     * Get equipment tier
     */
    public function getTier(): int
    {
        return $this->equipment_data['tier'] ?? 1;
    }

    /**
     * Check if this is a weapon
     */
    public function isWeapon(): bool
    {
        return $this->equipment_type === EquipmentType::WEAPON->value;
    }

    /**
     * Check if this is armor
     */
    public function isArmor(): bool
    {
        return $this->equipment_type === EquipmentType::ARMOR->value;
    }

    /**
     * Check if this is an item
     */
    public function isItem(): bool
    {
        return $this->equipment_type === EquipmentType::ITEM->value;
    }

    /**
     * Check if this is a consumable
     */
    public function isConsumable(): bool
    {
        return $this->equipment_type === EquipmentType::CONSUMABLE->value;
    }

    /**
     * Scope for specific equipment type
     */
    public function scopeOfType($query, EquipmentType $type)
    {
        return $query->where('equipment_type', $type->value);
    }

    /**
     * Scope for equipped items
     */
    public function scopeEquipped($query)
    {
        return $query->where('is_equipped', true);
    }

    /**
     * Scope for unequipped items
     */
    public function scopeUnequipped($query)
    {
        return $query->where('is_equipped', false);
    }

    /**
     * Scope for weapons
     */
    public function scopeWeapons($query)
    {
        return $query->where('equipment_type', EquipmentType::WEAPON->value);
    }

    /**
     * Scope for armor
     */
    public function scopeArmor($query)
    {
        return $query->where('equipment_type', EquipmentType::ARMOR->value);
    }

    /**
     * Scope for items
     */
    public function scopeItems($query)
    {
        return $query->where('equipment_type', EquipmentType::ITEM->value);
    }

    /**
     * Scope for consumables
     */
    public function scopeConsumables($query)
    {
        return $query->where('equipment_type', EquipmentType::CONSUMABLE->value);
    }
}
