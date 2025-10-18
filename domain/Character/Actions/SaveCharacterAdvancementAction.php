<?php

declare(strict_types=1);

namespace Domain\Character\Actions;

use Domain\Character\Models\CharacterAdvancement;

class SaveCharacterAdvancementAction
{
    /**
     * Save or update a character advancement
     *
     * @param int $characterId
     * @param int $level
     * @param int $advancementNumber (1 or 2)
     * @param string $advancementType
     * @param array $advancementData
     * @param string $description
     * @return CharacterAdvancement
     */
    public function execute(
        int $characterId,
        int $level,
        int $advancementNumber,
        string $advancementType,
        array $advancementData = [],
        string $description = ''
    ): CharacterAdvancement {
        // Calculate tier from level
        $tier = $this->calculateTier($level);

        // Find existing or create new
        $advancement = CharacterAdvancement::updateOrCreate(
            [
                'character_id' => $characterId,
                'level' => $level,
                'advancement_number' => $advancementNumber,
            ],
            [
                'tier' => $tier,
                'advancement_type' => $advancementType,
                'advancement_data' => $advancementData,
                'description' => $description,
            ]
        );

        return $advancement;
    }

    /**
     * Calculate tier from level
     * Tier 1 = Level 1
     * Tier 2 = Levels 2-4
     * Tier 3 = Levels 5-7
     * Tier 4 = Levels 8-10
     */
    private function calculateTier(int $level): int
    {
        if ($level === 1) {
            return 1;
        }

        if ($level <= 4) {
            return 2;
        }

        if ($level <= 7) {
            return 3;
        }

        return 4;
    }
}


