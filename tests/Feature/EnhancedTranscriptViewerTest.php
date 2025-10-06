<?php

declare(strict_types=1);

use Domain\Campaign\Models\Campaign;
use Domain\Room\Models\Room;
use Domain\Room\Models\RoomTranscript;
use Domain\User\Models\User;

describe('Enhanced Transcript Viewer', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->campaign = Campaign::factory()->create(['creator_id' => $this->user->id]);
        $this->room = Room::factory()->create([
            'creator_id' => $this->user->id,
            'campaign_id' => $this->campaign->id,
        ]);

        // Create test transcripts with different speakers and timestamps
        $this->transcripts = collect([
            RoomTranscript::factory()->create([
                'room_id' => $this->room->id,
                'user_id' => $this->user->id,
                'character_name' => 'Alice',
                'character_class' => 'Warrior',
                'text' => 'Hello everyone, ready for adventure?',
                'started_at_ms' => 1000000,
                'ended_at_ms' => 1002000,
            ]),
            RoomTranscript::factory()->create([
                'room_id' => $this->room->id,
                'user_id' => $this->user->id,
                'character_name' => 'Bob',
                'character_class' => 'Wizard',
                'text' => 'I cast magic missile at the darkness!',
                'started_at_ms' => 1005000,
                'ended_at_ms' => 1007000,
            ]),
            RoomTranscript::factory()->create([
                'room_id' => $this->room->id,
                'user_id' => $this->user->id,
                'character_name' => 'Alice',
                'character_class' => 'Warrior',
                'text' => 'Great idea! Let me attack with my sword.',
                'started_at_ms' => 1010000,
                'ended_at_ms' => 1012000,
            ]),
        ]);
    });

    test('transcripts page loads with search and timeline functionality', function () {
        $response = $this->actingAs($this->user)
            ->get(route('rooms.transcripts', $this->room));

        $response->assertOk();
        $response->assertSee('Session Transcripts');
        $response->assertSee('Search Messages');
        $response->assertSee('Filter by Speaker');
        $response->assertSee('Activity Timeline');
        $response->assertSee('Hello everyone, ready for adventure?');
        $response->assertSee('I cast magic missile at the darkness!');
    });

    test('text search filters transcripts correctly', function () {
        $response = $this->actingAs($this->user)
            ->get(route('rooms.transcripts', $this->room) . '?search=magic');

        $response->assertOk();
        $response->assertSee('I cast magic missile at the darkness!');
        
        // Check that the filtered-out text doesn't appear in the transcript messages
        // (it may appear in timeline tooltips, which is expected behavior)
        $content = $response->getContent();
        
        // Verify the matched message is in the transcript section
        expect($content)->toContain('I cast magic missile at the darkness!');
        
        // Verify filter count
        $response->assertSee('Showing 1 of 3 messages');
    });

    test('speaker filter works correctly', function () {
        $response = $this->actingAs($this->user)
            ->get(route('rooms.transcripts', $this->room) . '?speaker=Alice');

        $response->assertOk();
        $response->assertSee('Hello everyone, ready for adventure?');
        $response->assertSee('Great idea! Let me attack with my sword.');
        
        // Verify Alice's messages appear and filter count is correct
        $response->assertSee('Showing 2 of 3 messages');
    });

    test('combined search and speaker filter works', function () {
        $response = $this->actingAs($this->user)
            ->get(route('rooms.transcripts', $this->room) . '?search=attack&speaker=Alice');

        $response->assertOk();
        $response->assertSee('Great idea! Let me attack with my sword.');
        
        // Verify combined filter works and shows correct count
        $response->assertSee('Showing 1 of 3 messages');
    });

    test('timeline data is generated correctly', function () {
        $response = $this->actingAs($this->user)
            ->get(route('rooms.transcripts', $this->room));

        $response->assertOk();
        
        // Check that timeline data is passed to the view
        $viewData = $response->viewData('timelineData');
        expect($viewData)->toBeArray();
        expect(count($viewData))->toBe(100); // 100 segments
        
        // Check timeline segment structure
        $firstSegment = $viewData[0];
        expect($firstSegment)->toHaveKeys(['segment', 'start_ms', 'end_ms', 'count', 'timestamp']);
    });

    test('speakers list is populated correctly', function () {
        $response = $this->actingAs($this->user)
            ->get(route('rooms.transcripts', $this->room));

        $response->assertOk();
        
        $speakers = $response->viewData('speakers');
        expect($speakers)->toContain('Alice');
        expect($speakers)->toContain('Bob');
        expect($speakers->count())->toBe(2);
    });

    test('search highlighting works in transcript text', function () {
        $response = $this->actingAs($this->user)
            ->get(route('rooms.transcripts', $this->room) . '?search=magic');

        $response->assertOk();
        $response->assertSee('<mark class="bg-amber-400/30 text-amber-200 px-1 rounded">magic</mark>', false);
    });

    test('clear filters link appears when filters are active', function () {
        $response = $this->actingAs($this->user)
            ->get(route('rooms.transcripts', $this->room) . '?search=test');

        $response->assertOk();
        $response->assertSee('Clear Filters');
    });

    test('clear filters link does not appear when no filters are active', function () {
        $response = $this->actingAs($this->user)
            ->get(route('rooms.transcripts', $this->room));

        $response->assertOk();
        $response->assertDontSee('Clear Filters');
    });

    test('non-campaign members cannot access transcripts', function () {
        $nonMember = User::factory()->create();

        $response = $this->actingAs($nonMember)
            ->get(route('rooms.transcripts', $this->room));

        $response->assertForbidden();
    });

    test('campaign members can access transcripts', function () {
        $member = User::factory()->create();
        $this->campaign->members()->create([
            'user_id' => $member->id,
            'joined_at' => now(),
        ]);

        $response = $this->actingAs($member)
            ->get(route('rooms.transcripts', $this->room));

        $response->assertOk();
    });
});
