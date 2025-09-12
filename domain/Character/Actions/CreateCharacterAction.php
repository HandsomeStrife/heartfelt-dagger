<?php

declare(strict_types=1);

namespace Domain\Character\Actions;

use Domain\Character\Models\Character;
use Domain\User\Models\User;

class CreateCharacterAction
{
    public function execute(User $user, ?string $name = null, ?string $pronouns = null): Character
    {
        return Character::create([
            'character_key' => Character::generateUniqueKey(),
            'user_id' => $user->id,
            'name' => $name ?: 'New Character',
            'pronouns' => $pronouns,
            'class' => null,
            'subclass' => null,
            'ancestry' => null,
            'community' => null,
            'level' => 1,
            'proficiency' => 1, // DaggerHeart SRD: characters start with proficiency 1
            'profile_image_path' => null,
            'character_data' => [
                'background' => ['answers' => []],
                'connections' => [],
                'creation_date' => now()->toISOString(),
                'builder_version' => '1.0',
            ],
            'is_public' => false,
        ]);
    }
}
