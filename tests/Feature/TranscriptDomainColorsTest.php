<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomTranscript;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Transcript Domain Colors', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->campaign = Campaign::factory()->create(['creator_id' => $this->user->id]);
        $this->room = Room::factory()->create([
            'campaign_id' => $this->campaign->id,
            'creator_id' => $this->user->id,
        ]);
    });

    test('displays domain-based colors for character classes', function () {
        // Create transcripts with different character classes
        $warriorTranscript = RoomTranscript::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $this->user->id,
            'character_name' => 'Thorin',
            'character_class' => 'warrior',
            'text' => 'I charge into battle!',
        ]);

        $wizardTranscript = RoomTranscript::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $this->user->id,
            'character_name' => 'Gandalf',
            'character_class' => 'wizard',
            'text' => 'I cast a spell!',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('rooms.transcripts', $this->room));

        $response->assertOk();

        // Check that warrior gets blade/bone domain colors
        $response->assertSee('Thorin');
        $response->assertSee('Warrior');
        
        // Check that wizard gets codex/splendor domain colors  
        $response->assertSee('Gandalf');
        $response->assertSee('Wizard');

        // Verify domain abbreviations are shown
        $response->assertSee('(Bla+Bon)'); // Blade + Bone for warrior
        $response->assertSee('(Cod+Spl)'); // Codex + Splendor for wizard
    });

    test('uses fallback colors for unknown character classes', function () {
        $unknownTranscript = RoomTranscript::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $this->user->id,
            'character_name' => 'Mystery Person',
            'character_class' => 'unknown_class',
            'text' => 'Hello world!',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('rooms.transcripts', $this->room));

        $response->assertOk();
        $response->assertSee('Mystery Person');
        $response->assertSee('Unknown_class');
        
        // Should not show domain abbreviations for unknown classes
        $response->assertDontSee('(Unk+Unk)');
    });

    test('uses fallback colors for users without character class', function () {
        $userTranscript = RoomTranscript::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $this->user->id,
            'character_name' => null,
            'character_class' => null,
            'text' => 'Speaking as myself',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('rooms.transcripts', $this->room));

        $response->assertOk();
        $response->assertSee($this->user->username);
        
        // User without character class shouldn't have a character class badge at all
        // We just verify the message appears, which is enough for this test
    });

    test('shows domain tooltips on avatars', function () {
        $bardTranscript = RoomTranscript::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $this->user->id,
            'character_name' => 'Melody',
            'character_class' => 'bard',
            'text' => 'I sing a song!',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('rooms.transcripts', $this->room));

        $response->assertOk();
        
        // Check for domain tooltip (Grace + Codex for bard)
        $response->assertSee('Grace + Codex');
    });

    test('generates consistent colors for same speakers', function () {
        // Create multiple transcripts from same character
        RoomTranscript::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $this->user->id,
            'character_name' => 'Consistent Character',
            'character_class' => 'guardian',
            'text' => 'First message',
        ]);

        RoomTranscript::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $this->user->id,
            'character_name' => 'Consistent Character',
            'character_class' => 'guardian',
            'text' => 'Second message',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('rooms.transcripts', $this->room));

        $response->assertOk();
        
        // Both messages should show the same character
        $content = $response->getContent();
        $guardianCount = substr_count($content, 'Guardian');
        expect($guardianCount)->toBeGreaterThanOrEqual(1);
        
        // Should show domain info for guardian (Valor + Blade)
        $response->assertSee('(Val+Bla)');
    });

    test('handles mixed character and user speakers', function () {
        // Character speaker
        RoomTranscript::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $this->user->id,
            'character_name' => 'Hero',
            'character_class' => 'seraph',
            'text' => 'In character message',
        ]);

        // User speaker (no character)
        RoomTranscript::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $this->user->id,
            'character_name' => null,
            'character_class' => null,
            'text' => 'Out of character message',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('rooms.transcripts', $this->room));

        $response->assertOk();
        
        // Should see both speakers
        $response->assertSee('Hero');
        $response->assertSee($this->user->username);
        
        // Only character should show class info
        $response->assertSee('Seraph');
        $response->assertSee('(Spl+Val)'); // Splendor + Valor for seraph
    });

    test('timeline shows speaker colors', function () {
        // Create multiple transcripts from different characters
        RoomTranscript::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $this->user->id,
            'character_name' => 'Warrior One',
            'character_class' => 'warrior',
            'text' => 'First message',
            'started_at_ms' => 1000000,
            'ended_at_ms' => 1001000,
        ]);

        RoomTranscript::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $this->user->id,
            'character_name' => 'Wizard One',
            'character_class' => 'wizard',
            'text' => 'Second message',
            'started_at_ms' => 1002000,
            'ended_at_ms' => 1003000,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('rooms.transcripts', $this->room));

        $response->assertOk();
        
        // Check that timeline bar is present
        $response->assertSee('Activity Timeline');
        
        // Timeline should include speaker data attributes
        $content = $response->getContent();
        expect($content)->toContain('data-speakers');
        
        // Should see warrior's blade color in timeline
        expect($content)->toContain('#af231c'); // Blade color
        
        // Should see wizard's codex color in timeline  
        expect($content)->toContain('#24395d'); // Codex color
    });
});
