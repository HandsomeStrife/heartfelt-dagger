<?php

declare(strict_types=1);

namespace Domain\Character\Actions;

use Domain\Character\Models\CharacterAdvancement;

class DeleteCharacterAdvancementAction
{
    /**
     * Delete a specific character advancement
     *
     * @param int $characterId
     * @param int $level
     * @param int $advancementNumber
     * @return bool True if deleted, false if not found
     */
    public function execute(
        int $characterId,
        int $level,
        int $advancementNumber
    ): bool {
        $deleted = CharacterAdvancement::where('character_id', $characterId)
            ->where('level', $level)
            ->where('advancement_number', $advancementNumber)
            ->delete();

        return $deleted > 0;
    }

    /**
     * Delete all advancements for a specific level
     *
     * @param int $characterId
     * @param int $level
     * @return int Number of advancements deleted
     */
    public function executeForLevel(int $characterId, int $level): int
    {
        return CharacterAdvancement::where('character_id', $characterId)
            ->where('level', $level)
            ->delete();
    }

    /**
     * Delete all advancements above a specific level (for character level reduction)
     *
     * @param int $characterId
     * @param int $maxLevel Keep advancements up to and including this level
     * @return int Number of advancements deleted
     */
    public function executeAboveLevel(int $characterId, int $maxLevel): int
    {
        return CharacterAdvancement::where('character_id', $characterId)
            ->where('level', '>', $maxLevel)
            ->delete();
    }
}


