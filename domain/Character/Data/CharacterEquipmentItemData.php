<?php

declare(strict_types=1);

namespace Domain\Character\Data;

use Domain\Character\Enums\EquipmentType;
use Livewire\Wireable;
use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;

class CharacterEquipmentItemData extends Data implements Wireable
{
    use WireableData;
    
    public function __construct(
        public int $id,
        public int $character_id,
        public string $equipment_type,
        public string $equipment_key,
        public array $equipment_data,
        public bool $is_equipped,
    ) {}

    /**
     * Get the equipment type as an enum
     */
    public function getEquipmentTypeEnum(): EquipmentType
    {
        return EquipmentType::from($this->equipment_type);
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
     * Get equipment armor score (for armor)
     */
    public function getArmorScore(): int
    {
        return $this->equipment_data['armor_score'] ?? $this->equipment_data['baseScore'] ?? 0;
    }

    /**
     * Get equipment thresholds (for armor)
     */
    public function getThresholds(): ?array
    {
        return $this->equipment_data['thresholds'] ?? $this->equipment_data['baseThresholds'] ?? null;
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
     * Get equipped weapons
     */
    public function isEquippedWeapon(): bool
    {
        return $this->is_equipped && $this->isWeapon();
    }

    /**
     * Get equipped armor
     */
    public function isEquippedArmor(): bool
    {
        return $this->is_equipped && $this->isArmor();
    }
}
