<?php

declare(strict_types=1);

namespace App\Livewire;

use Domain\Character\Actions\LoadCharacterAction;
use Domain\Character\Data\CharacterBuilderData;
use Livewire\Component;

class CharacterViewer extends Component
{
    public string $character_key;

    public ?CharacterBuilderData $character = null;

    public function mount(string $characterKey): void
    {
        $this->character_key = $characterKey;

        $action = new LoadCharacterAction;
        $this->character = $action->execute($characterKey);

        if (! $this->character) {
            abort(404, 'Character not found');
        }
    }

    public function render()
    {
        return view('livewire.character-viewer', [
            'character' => $this->character,
        ]);
    }
}
