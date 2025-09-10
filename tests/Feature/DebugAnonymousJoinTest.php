<?php

declare(strict_types=1);

use Domain\Room\Models\Room;

use function Pest\Laravel\get;

test('debug anonymous user join page content', function () {
    $room = Room::factory()->passwordless()->create();

    // Anonymous user (not authenticated)
    $response = get("/rooms/join/{$room->invite_code}");

    $response->assertOk();

    $content = $response->getContent();

    // Extract relevant parts
    $lines = explode("\n", $content);
    $relevantLines = [];

    foreach ($lines as $lineNum => $line) {
        if (str_contains($line, 'disabled') ||
            str_contains($line, 'character_name') ||
            str_contains($line, 'character_class') ||
            str_contains($line, 'opacity-50')) {
            $relevantLines[] = 'Line '.($lineNum + 1).': '.trim($line);
        }
    }

    // Print the relevant lines for debugging
    echo "\n\nRelevant lines containing 'disabled', 'character_name', 'character_class', or 'opacity-50':\n";
    foreach ($relevantLines as $line) {
        echo $line."\n";
    }

    // This will help us see what's in the HTML
    expect(true)->toBeTrue();
});
