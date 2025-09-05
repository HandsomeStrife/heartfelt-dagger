<?php

declare(strict_types=1);

namespace App\Livewire;

use Domain\Character\Actions\LoadCharacterAction;
use Domain\Character\Models\Character;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CharacterGrid extends Component
{
    public array $character_keys = [];
    public Collection $characters;
    public bool $loading = true;

    public function mount(): void
    {
        $this->characters = collect();
        $this->loadCharactersForCurrentUser();
    }

    public function loadCharactersForCurrentUser(): void
    {
        // For authenticated users, load characters server-side by user
        if (Auth::check()) {
            $keys = Character::where('user_id', Auth::id())
                ->orderBy('updated_at', 'desc')
                ->pluck('character_key')
                ->all();
            $this->loadCharacters($keys);
        } else {
            // For guests, load from localStorage (handled on the client)
            $this->dispatch('load-characters-from-storage');
        }
    }

    public function loadCharacters(array $character_keys): void
    {
        $this->character_keys = $character_keys;
        $this->loading = true;
        
        if (empty($character_keys)) {
            $this->characters = collect();
            $this->loading = false;
            return;
        }

        $action = new LoadCharacterAction();
        $characters = collect();
        
        foreach ($character_keys as $key) {
            try {
                $character = $action->execute($key);
                if ($character) {
                    // Get the character model for ownership validation
                    $character_model = Character::where('character_key', $key)->first();
                    if ($character_model && $this->userCanAccessCharacter($character_model)) {
                        // Add model data to character data
                        $character->character_key = $key;
                        $character->public_key = $character_model->public_key;
                        $characters->push($character);
                    }
                }
            } catch (\Exception $e) {
                // Skip characters that can't be loaded
                continue;
            }
        }
        
        $this->characters = $characters;
        $this->loading = false;
    }

    /**
     * Determine if the current user can access a character in the character grid.
     * - Authenticated users can only see characters they own
     * - Guest users can only see characters with no user_id (anonymous characters)
     */
    private function userCanAccessCharacter(Character $character): bool
    {
        if (Auth::check()) {
            // Authenticated users can only see their own characters
            return $character->user_id === Auth::id();
        } else {
            // Guest users can only see anonymous characters (no user_id)
            return $character->user_id === null;
        }
    }

    public function refreshCharacters(): void
    {
        $this->loadCharactersForCurrentUser();
    }



    public function viewCharacter(string $character_key): void
    {
        $character = $this->characters->firstWhere('character_key', $character_key);
        if ($character && $character->public_key) {
            $this->redirect("/character/{$character->public_key}");
        }
    }

    public function editCharacter(string $character_key): void
    {
        $this->redirect("/character-builder/{$character_key}");
    }

    public function deleteCharacter(string $character_key): void
    {
        // This will be handled by Alpine.js on the frontend for confirmation
        // The actual deletion happens via the existing API endpoint
        $this->dispatch('delete-character', character_key: $character_key);
    }

    public function render()
    {
        return view('livewire.character-grid');
    }
}