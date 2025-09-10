<?php

declare(strict_types=1);

namespace Domain\Character\Models;

use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CharacterNotes extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'notes' => 'string',
    ];

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get or create notes for a specific character and user
     */
    public static function getOrCreateForCharacterAndUser(Character $character, User $user): self
    {
        return static::firstOrCreate(
            [
                'character_id' => $character->id,
                'user_id' => $user->id,
            ],
            [
                'notes' => '',
            ]
        );
    }

    /**
     * Update notes for a specific character and user
     */
    public static function updateNotesForCharacterAndUser(Character $character, User $user, string $notes): self
    {
        $characterNotes = static::getOrCreateForCharacterAndUser($character, $user);
        $characterNotes->update(['notes' => $notes]);

        return $characterNotes;
    }

    protected static function newFactory()
    {
        return \Database\Factories\CharacterNotesFactory::new();
    }
}
