<?php

declare(strict_types=1);

namespace Domain\Character\Enums;

enum EquipmentType: string
{
    case WEAPON = 'weapon';
    case ARMOR = 'armor';
    case ITEM = 'item';
    case CONSUMABLE = 'consumable';

    public function label(): string
    {
        return match ($this) {
            self::WEAPON => 'Weapon',
            self::ARMOR => 'Armor',
            self::ITEM => 'Item',
            self::CONSUMABLE => 'Consumable',
        };
    }

    public function pluralLabel(): string
    {
        return match ($this) {
            self::WEAPON => 'Weapons',
            self::ARMOR => 'Armor',
            self::ITEM => 'Items',
            self::CONSUMABLE => 'Consumables',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::WEAPON => 'Combat weapons and tools for dealing damage',
            self::ARMOR => 'Protective gear that provides damage resistance',
            self::ITEM => 'Utility items and magical equipment',
            self::CONSUMABLE => 'Single-use items like potions and scrolls',
        };
    }

    /**
     * Get all equipment type values as an array
     */
    public static function values(): array
    {
        return array_map(fn (EquipmentType $type) => $type->value, self::cases());
    }

    /**
     * Get all equipment types with labels
     */
    public static function options(): array
    {
        return array_map(
            fn (EquipmentType $type) => [
                'value' => $type->value,
                'label' => $type->label(),
                'plural' => $type->pluralLabel(),
            ],
            self::cases()
        );
    }
}
