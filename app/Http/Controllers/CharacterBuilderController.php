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
     * Show a character for viewing (read-only).
     */
    public function show(string $character_key): View
    {
        $action = new LoadCharacterAction;
        $character = $action->execute($character_key);

        if (! $character) {
            abort(404, 'Character not found');
        }

        return view('characters.show', [
            'character_key' => $character_key,
            'character' => $character,
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

        // Return character data as JSON
        return response()->json([
            'character_key' => $character_key,
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

        // Find and delete the character from the database
        $characterModel = \Domain\Character\Models\Character::where('character_key', $character_key)->first();

        if ($characterModel) {
            $characterModel->delete();

            return response()->json(['message' => 'Character deleted successfully']);
        }

        return response()->json(['error' => 'Character not found'], 404);
    }
}
