<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Domain\Character\Actions\LoadCharacterAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CharacterBuilderController extends Controller
{
    /**
     * Show the characters list page.
     * Characters are loaded via JavaScript from localStorage.
     */
    public function index(): View
    {
        return view('characters.index');
    }

    /**
     * Show the character builder interface.
     * Creates a new character and redirects to the edit route.
     */
    public function create()
    {
        // Create a new character in the database
        $character = \Domain\Character\Models\Character::create([
            'name' => null,
            'character_key' => \Domain\Character\Models\Character::generateUniqueKey(),
            'user_id' => Auth::check() ? Auth::user()->id : null, // null if not logged in
            'class' => null,
            'subclass' => null,
            'ancestry' => null,
            'community' => null,
            'character_data' => [],
            'is_public' => false,
        ]);

        // Redirect to the edit route with the new character key
        return redirect()->route('character-builder.edit', ['character_key' => $character->character_key]);
    }

    /**
     * Show the character builder for editing an existing character.
     */
    public function edit(string $character_key): View
    {
        $action = new LoadCharacterAction;
        $character = $action->execute($character_key);

        if (! $character) {
            abort(404, 'Character not found');
        }

        return view('characters.edit', [
            'character_key' => $character_key,
            'character' => $character,
        ]);
    }

    /**
     * Show a character for viewing (read-only) using public_key.
     */
    public function show(string $public_key): View
    {
        // Find character by public_key instead of character_key
        $character_model = \Domain\Character\Models\Character::where('public_key', $public_key)->first();

        if (! $character_model) {
            abort(404, 'Character not found');
        }

        // Load character data using the character_key
        $action = new LoadCharacterAction;
        $character = $action->execute($character_model->character_key);

        if (! $character) {
            abort(404, 'Character not found');
        }

        // Determine if user can edit this character
        $can_edit = $this->userCanEditCharacter($character_model);

        return view('characters.show', [
            'public_key' => $public_key,
            'character' => $character,
            'can_edit' => $can_edit,
            'character_key' => $character_model->character_key, // For edit link if allowed
        ]);
    }

    /**
     * Return character data as JSON for API requests.
     */
    public function apiShow(string $character_key): JsonResponse
    {
        $action = new LoadCharacterAction;
        $character = $action->execute($character_key);

        if (! $character) {
            return response()->json(['error' => 'Character not found'], 404);
        }

        // Get the public_key from the character model
        $character_model = \Domain\Character\Models\Character::where('character_key', $character_key)->first();

        // Return character data as JSON
        return response()->json([
            'character_key' => $character_key,
            'public_key' => $character_model->public_key ?? null,
            'name' => $character->name,
            'selected_class' => $character->selected_class,
            'selected_subclass' => $character->selected_subclass,
            'selected_ancestry' => $character->selected_ancestry,
            'selected_community' => $character->selected_community,
            'assigned_traits' => $character->assigned_traits,
            'selected_equipment' => $character->selected_equipment,
            'background' => $character->background_answers,
            'experiences' => $character->experiences,
            'selected_domain_cards' => $character->selected_domain_cards,
            'connections' => $character->connection_answers,
            'profile_image' => $character->profile_image_path, // This might need special handling for file URLs
        ]);
    }

    /**
     * Delete a character from the database.
     */
    public function apiDestroy(string $character_key): JsonResponse
    {
        $action = new LoadCharacterAction;
        $character = $action->execute($character_key);

        if (! $character) {
            return response()->json(['error' => 'Character not found'], 404);
        }

        // Find the character model for permission checking
        $characterModel = \Domain\Character\Models\Character::where('character_key', $character_key)->first();

        if (! $characterModel) {
            return response()->json(['error' => 'Character not found'], 404);
        }

        // Check if user has permission to delete this character
        if (! $this->userCanEditCharacter($characterModel)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Delete the character
        $characterModel->delete();

        return response()->json(['message' => 'Character deleted successfully']);
    }

    /**
     * Determine if the current user can edit a character.
     * Users can edit if:
     * 1. They are logged in and own the character
     * 2. The character_key is in their browser's localStorage (for anonymous users)
     */
    private function userCanEditCharacter(\Domain\Character\Models\Character $character): bool
    {
        // If user is logged in and owns the character
        if (Auth::check() && $character->user_id === Auth::user()->id) {
            return true;
        }

        // For anonymous users or characters not owned by current user,
        // check if character_key is in localStorage (this will be handled by JavaScript)
        // For server-side, we'll need to rely on session or pass this check to the frontend

        // For now, we'll allow editing if the character has no user_id (anonymous character)
        // The frontend will need to verify localStorage access
        if (! $character->user_id) {
            return true; // Frontend will validate localStorage access
        }

        return false;
    }
}
