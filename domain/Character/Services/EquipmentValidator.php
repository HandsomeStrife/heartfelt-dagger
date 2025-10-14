<?php

declare(strict_types=1);

namespace Domain\Character\Services;

/**
 * Service responsible for validating equipment selections
 */
class EquipmentValidator
{
    /**
     * Item name mappings for known mismatches
     */
    private const ITEM_MAPPINGS = [
        'minor healing potion' => 'minor health potion',
        'minor stamina potion' => 'minor stamina potion',
        'healing potion' => 'health potion',
        'major healing potion' => 'major health potion',
    ];

    /**
     * Check if equipment selection is complete
     *
     * @param array $selectedEquipment Array of selected equipment items
     * @param string|null $classKey The class key for starting inventory requirements
     * @return bool True if equipment is complete
     */
    public function isEquipmentComplete(array $selectedEquipment, ?string $classKey): bool
    {
        // Check for primary weapon and armor
        if (! $this->hasPrimaryWeapon($selectedEquipment)) {
            return false;
        }

        if (! $this->hasArmor($selectedEquipment)) {
            return false;
        }

        // Check starting inventory requirements if class is selected
        if ($classKey) {
            $classData = $this->loadClassData($classKey);
            if ($classData && isset($classData['startingInventory'])) {
                return $this->validateStartingInventory(
                    $selectedEquipment,
                    $classData['startingInventory']
                );
            }
        }

        return true;
    }

    /**
     * Check if selected equipment includes a primary weapon
     *
     * @param array $selectedEquipment Array of selected equipment items
     * @return bool True if has primary weapon
     */
    public function hasPrimaryWeapon(array $selectedEquipment): bool
    {
        return collect($selectedEquipment)->contains(
            fn ($eq) => $eq['type'] === 'weapon' &&
                       ($eq['data']['type'] ?? 'Primary') === 'Primary'
        );
    }

    /**
     * Check if selected equipment includes armor
     *
     * @param array $selectedEquipment Array of selected equipment items
     * @return bool True if has armor
     */
    public function hasArmor(array $selectedEquipment): bool
    {
        return collect($selectedEquipment)->contains(
            fn ($eq) => $eq['type'] === 'armor'
        );
    }

    /**
     * Validate starting inventory requirements
     *
     * @param array $selectedEquipment Array of selected equipment items
     * @param array $startingInventory Starting inventory requirements
     * @return bool True if requirements are met
     */
    private function validateStartingInventory(array $selectedEquipment, array $startingInventory): bool
    {
        // Check chooseOne items
        if (isset($startingInventory['chooseOne']) &&
            is_array($startingInventory['chooseOne']) &&
            ! empty($startingInventory['chooseOne'])) {

            if (! $this->hasRequiredItem($selectedEquipment, $startingInventory['chooseOne'])) {
                return false;
            }
        }

        // Check chooseExtra items
        if (isset($startingInventory['chooseExtra']) &&
            is_array($startingInventory['chooseExtra']) &&
            ! empty($startingInventory['chooseExtra'])) {

            if (! $this->hasRequiredItem($selectedEquipment, $startingInventory['chooseExtra'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if selected equipment has at least one item from required items list
     *
     * @param array $selectedEquipment Array of selected equipment items
     * @param array $requiredItems Array of required item names
     * @return bool True if has at least one required item
     */
    private function hasRequiredItem(array $selectedEquipment, array $requiredItems): bool
    {
        foreach ($requiredItems as $item) {
            $item_key = strtolower($item);
            $mapped_key = self::ITEM_MAPPINGS[$item_key] ?? $item_key;

            if (collect($selectedEquipment)->contains(fn ($eq) => $eq['key'] === $mapped_key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Load class data from JSON file
     *
     * @param string $classKey The class key
     * @return array|null The class data or null if not found
     */
    private function loadClassData(string $classKey): ?array
    {
        $path = resource_path('json/classes.json');
        if (! file_exists($path)) {
            return null;
        }

        $classes = json_decode(file_get_contents($path), true);

        return $classes[$classKey] ?? null;
    }
}

