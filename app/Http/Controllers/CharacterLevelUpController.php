<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Domain\Character\Models\Character;
use Domain\Character\Repositories\CharacterAdvancementRepository;

class CharacterLevelUpController extends Controller
{
    public function __construct(
        private CharacterAdvancementRepository $advancement_repository
    ) {}

    /**
     * Show the level-up page for a character
     */
    public function show(string $publicKey, string $characterKey)
    {
        // Find the character
        $character = Character::where('public_key', $publicKey)
            ->where('character_key', $characterKey)
            ->firstOrFail();

        // Check if user can edit this character
        $can_edit = $this->canEditCharacter($character);

        if (! $can_edit) {
            abort(403, 'You cannot level up this character.');
        }

        // Check if character can level up
        if (! $this->advancement_repository->canLevelUp($character)) {
            return redirect()->route('character.show', [
                'public_key' => $publicKey,
                'character_key' => $characterKey,
            ])->with('error', 'This character has no available advancement slots.');
        }

        return view('character.level-up', [
            'character' => $character,
            'public_key' => $publicKey,
            'character_key' => $characterKey,
            'can_edit' => $can_edit,
        ]);
    }

    /**
     * Check if the current user can edit this character
     */
    private function canEditCharacter(Character $character): bool
    {
        // If user is authenticated and owns the character
        if (auth()->check() && $character->user_id === auth()->id()) {
            return true;
        }

        // If user is not authenticated, check localStorage keys (handled in frontend)
        if (! auth()->check()) {
            // For anonymous users, we'll let the frontend handle this check
            // and pass it as a parameter or session variable
            return true; // This will be refined in the Livewire component
        }

        return false;
    }
}
