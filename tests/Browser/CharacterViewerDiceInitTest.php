<?php

declare(strict_types=1);

use Domain\Character\Models\Character;

uses()->group('browser');

it('hides loading screen on character viewer even if dice API is unavailable', function () {
    $character = Character::factory()->create([
        'character_data' => [
            'selected_class' => 'wizard',
            'selected_ancestry' => 'human',
            'selected_community' => 'loreborne',
            'assigned_traits' => ['agility' => 1, 'strength' => 0, 'finesse' => 0, 'instinct' => 2, 'presence' => 1, 'knowledge' => -1],
            'selected_equipment' => [],
            'experiences' => [],
            'selected_domain_cards' => [],
        ],
        'name' => 'Dice Init Check',
    ]);

    $page = visit('/character/' . $character->character_key);

    // Regardless of dice API availability, UI should not remain blocked
    $page->wait(2.5);

    // Ensure loading screen goes away (either hidden or removed)
    $isHidden = $page->script('(() => { const el = document.getElementById("character-loading-screen"); if(!el){return true;} const style = getComputedStyle(el); return style.display === "none" || parseFloat(style.opacity || "1") === 0; })()');
    expect((bool) $isHidden)->toBeTrue();
});


