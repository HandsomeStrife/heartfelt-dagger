<?php

declare(strict_types=1);

namespace Domain\Character\Data;

use Domain\Character\Enums\EquipmentType;
use Domain\Character\Models\Character;
use Spatie\LaravelData\Data;

class CharacterEquipmentData extends Data
{
    public function __construct(
        public ?array $primary_weapon,
        public ?array $secondary_weapon,
        public ?array $armor,
        public array $items,
        public array $consumables,
    ) {}

    public static function fromModel(Character $character): self
    {
        $equipment = $character->equipment()->get()->groupBy('equipment_type');

        $weapons = $equipment->get(EquipmentType::WEAPON->value, collect());
        $armor = $equipment->get(EquipmentType::ARMOR->value, collect())->first();
        $items = $equipment->get(EquipmentType::ITEM->value, collect());
        $consumables = $equipment->get(EquipmentType::CONSUMABLE->value, collect());

        return new self(
            primary_weapon: $weapons->first()?->equipment_data,
            secondary_weapon: $weapons->skip(1)->first()?->equipment_data,
            armor: $armor?->equipment_data,
            items: $items->pluck('equipment_data')->toArray(),
            consumables: $consumables->pluck('equipment_data')->toArray(),
        );
    }

    public static function fromBuilderData(array $selected_equipment): self
    {
        $weapons = array_filter($selected_equipment, fn ($item) => $item['type'] === 'weapon');
        $armor = array_filter($selected_equipment, fn ($item) => $item['type'] === 'armor');
        $items = array_filter($selected_equipment, fn ($item) => $item['type'] === 'item');
        $consumables = array_filter($selected_equipment, fn ($item) => $item['type'] === 'consumable');

        $weapons_list = array_values($weapons);

        return new self(
            primary_weapon: $weapons_list[0]['data'] ?? null,
            secondary_weapon: $weapons_list[1]['data'] ?? null,
            armor: array_values($armor)[0]['data'] ?? null,
            items: array_column($items, 'data'),
            consumables: array_column($consumables, 'data'),
        );
    }

    public function hasMinimumEquipment(): bool
    {
        return ! is_null($this->primary_weapon) || ! is_null($this->armor);
    }

    public function getPrimaryWeaponName(): ?string
    {
        return $this->primary_weapon['name'] ?? null;
    }

    public function getSecondaryWeaponName(): ?string
    {
        return $this->secondary_weapon['name'] ?? null;
    }

    public function getArmorName(): ?string
    {
        return $this->armor['name'] ?? null;
    }

    public function getTotalArmorScore(): int
    {
        return $this->armor['baseScore'] ?? 0;
    }

    public function getMajorThreshold(): int
    {
        $armor = $this->armor['baseThresholds']['lower'] ?? 0;

        return max(1, $armor);
    }

    public function getSevereThreshold(): int
    {
        $armor = $this->armor['baseThresholds']['higher'] ?? 0;

        return max(1, $armor);
    }

    public function getPrimaryWeaponDamage(): ?string
    {
        if (! $this->primary_weapon || ! isset($this->primary_weapon['damage'])) {
            return null;
        }

        $damage = $this->primary_weapon['damage'];

        return "d{$damage['dice']} + {$damage['bonus']}";
    }

    public function getWeaponCount(): int
    {
        $count = 0;
        if ($this->primary_weapon) {
            $count++;
        }
        if ($this->secondary_weapon) {
            $count++;
        }

        return $count;
    }

    public function getItemCount(): int
    {
        return count($this->items);
    }

    public function getConsumableCount(): int
    {
        return count($this->consumables);
    }

    public function getTotalEquipmentCount(): int
    {
        $count = $this->getWeaponCount() + $this->getItemCount() + $this->getConsumableCount();
        if ($this->armor) {
            $count++;
        }

        return $count;
    }

    public function hasArmor(): bool
    {
        return ! is_null($this->armor);
    }

    public function hasPrimaryWeapon(): bool
    {
        return ! is_null($this->primary_weapon);
    }

    public function hasSecondaryWeapon(): bool
    {
        return ! is_null($this->secondary_weapon);
    }

    public function getEquipmentSummary(): array
    {
        return [
            'weapons' => $this->getWeaponCount(),
            'armor' => $this->hasArmor() ? 1 : 0,
            'items' => $this->getItemCount(),
            'consumables' => $this->getConsumableCount(),
            'total' => $this->getTotalEquipmentCount(),
        ];
    }
}
